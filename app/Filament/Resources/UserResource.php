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
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.user_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->label(__('app.username'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('app.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label(__('app.password'))
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->label(fn (string $operation): string => $operation === 'create' ? __('app.password') : __('app.new_password_hint')),
                        Forms\Components\Select::make('roles')
                            ->label(__('app.role'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.contacts'))
                    ->schema([
                        Forms\Components\TextInput::make('telefono')
                            ->label(__('app.phone'))
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('indirizzo')
                            ->label(__('app.street'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cap')
                            ->label(__('app.postal_code'))
                            ->maxLength(10),
                        Forms\Components\TextInput::make('citta')
                            ->label(__('app.city'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provincia')
                            ->label(__('app.province'))
                            ->maxLength(2),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.clothing_sizes'))
                    ->schema([
                        Forms\Components\TextInput::make('taglia_giacca')
                            ->label(__('app.jacket_size'))
                            ->maxLength(10),
                        Forms\Components\TextInput::make('taglia_pantaloni')
                            ->label(__('app.pants_size'))
                            ->maxLength(10),
                        Forms\Components\TextInput::make('taglia_maglietta')
                            ->label(__('app.shirt_size'))
                            ->maxLength(10),
                        Forms\Components\TextInput::make('taglia_scarpe')
                            ->label(__('app.shoe_size'))
                            ->maxLength(10),
                        Forms\Components\Textarea::make('note_abbigliamento')
                            ->label(__('app.clothing_notes'))
                            ->columnSpanFull(),
                    ])->columns(4),

                Forms\Components\Section::make(__('app.contract_data'))
                    ->schema([
                        Forms\Components\TextInput::make('ore_settimanali')
                            ->label(__('app.weekly_hours'))
                            ->numeric()
                            ->default(40),
                        Forms\Components\TextInput::make('costo_orario')
                            ->label(__('app.hourly_cost'))
                            ->numeric()
                            ->prefix('CHF'),
                        Forms\Components\Toggle::make('attivo')
                            ->label(__('app.active'))
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('app.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label(__('app.username'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('app.roles'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'user' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('app.role_' . $state)),
                Tables\Columns\IconColumn::make('attivo')
                    ->label(__('app.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('app.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label(__('app.role'))
                    ->relationship('roles', 'name')
                    ->options([
                        'admin' => __('app.role_admin'),
                        'manager' => __('app.role_manager'),
                        'user' => __('app.role_user'),
                    ]),
                Tables\Filters\TernaryFilter::make('attivo')
                    ->label(__('app.active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole(['admin', 'manager']);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('app.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.users');
    }

    public static function getModelLabel(): string
    {
        return __('app.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.users');
    }
}
