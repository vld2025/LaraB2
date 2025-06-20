<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Utente')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->label(fn (string $operation): string => $operation === 'create' ? 'Password' : 'Nuova Password (lascia vuoto per non modificare)'),
                        Forms\Components\Select::make('roles')
                            ->label('Ruolo')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Contatti')
                    ->schema([
                        Forms\Components\TextInput::make('telefono')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('indirizzo')
                            ->label('Indirizzo')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cap')
                            ->label('CAP')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('citta')
                            ->label('Città')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provincia')
                            ->label('Provincia')
                            ->maxLength(2),
                    ])->columns(2),
                
                Forms\Components\Section::make('Taglie Abbigliamento')
                    ->schema([
                        Forms\Components\TextInput::make('taglia_giacca')
                            ->label('Taglia Giacca')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('taglia_pantaloni')
                            ->label('Taglia Pantaloni')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('taglia_maglietta')
                            ->label('Taglia Maglietta')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('taglia_scarpe')
                            ->label('Taglia Scarpe')
                            ->maxLength(10),
                        Forms\Components\Textarea::make('note_abbigliamento')
                            ->label('Note Abbigliamento')
                            ->columnSpanFull(),
                    ])->columns(4),
                
                Forms\Components\Section::make('Dati Contrattuali')
                    ->schema([
                        Forms\Components\TextInput::make('ore_settimanali')
                            ->label('Ore Settimanali')
                            ->numeric()
                            ->default(40),
                        Forms\Components\TextInput::make('costo_orario')
                            ->label('Costo Orario')
                            ->numeric()
                            ->prefix('CHF'),
                        Forms\Components\Toggle::make('attivo')
                            ->label('Attivo')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Ruoli')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'user' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('attivo')
                    ->label('Attivo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Ruolo')
                    ->relationship('roles', 'name'),
                Tables\Filters\TernaryFilter::make('attivo')
                    ->label('Attivo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Utenti';
    }

    public static function getModelLabel(): string
    {
        return 'Utente';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Utenti';
    }
}
