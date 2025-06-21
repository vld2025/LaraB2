<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.general_information'))
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label(__('app.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('codice')
                            ->label(__('app.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('codice_fiscale')
                            ->label(__('app.tax_code'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('partita_iva')
                            ->label(__('app.vat_number'))
                            ->maxLength(255),
                    ])->columns(2),
                    
                Forms\Components\Section::make(__('app.address'))
                    ->schema([
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
                        Forms\Components\Select::make('nazione')
                            ->label(__('app.country'))
                            ->options([
                                'CH' => __('app.switzerland'),
                                'IT' => __('app.italy'),
                                'DE' => __('app.germany'),
                                'FR' => __('app.france'),
                            ])
                            ->required()
                            ->default('CH'),
                    ])->columns(2),
                    
                Forms\Components\Section::make(__('app.contacts'))
                    ->schema([
                        Forms\Components\TextInput::make('telefono')
                            ->label(__('app.phone'))
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('app.email'))
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),
                    
                Forms\Components\Section::make(__('app.other'))
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->label(__('app.notes'))
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('attivo')
                            ->label(__('app.active'))
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codice')
                    ->label(__('app.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->label(__('app.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('partita_iva')
                    ->label(__('app.vat_number'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('citta')
                    ->label(__('app.city'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label(__('app.phone'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('app.email'))
                    ->searchable()
                    ->toggleable(),
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
                Tables\Filters\TernaryFilter::make('attivo')
                    ->label(__('app.active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nome');
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
            'index' => Pages\ListClienti::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('attivo', true)->count();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('app.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.clients');
    }

    public static function getPluralLabel(): ?string
    {
        return __('app.clients');
    }

    public static function getLabel(): ?string
    {
        return __('app.client');
    }
}
