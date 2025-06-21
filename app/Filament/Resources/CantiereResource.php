<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CantiereResource\Pages;
use App\Models\Cantiere;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CantiereResource extends Resource
{
    protected static ?string $model = Cantiere::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.construction_site_information'))
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->label(__('app.client'))
                            ->relationship('cliente', 'nome')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nome')
                                    ->label(__('app.name'))
                                    ->required(),
                                Forms\Components\TextInput::make('codice')
                                    ->label(__('app.code'))
                                    ->required()
                                    ->unique(),
                            ]),
                        Forms\Components\TextInput::make('nome')
                            ->label(__('app.construction_site_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('codice')
                            ->label(__('app.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('CAN001'),
                    ]),

                Forms\Components\Section::make(__('app.location'))
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
                            ->default('CH'),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.other'))
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->label(__('app.notes'))
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('attivo')
                            ->label(__('app.active'))
                            ->default(true),
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
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label(__('app.client'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('citta')
                    ->label(__('app.city'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('commesse_count')
                    ->label(__('app.orders'))
                    ->counts('commesse')
                    ->badge(),
                Tables\Columns\IconColumn::make('attivo')
                    ->label(__('app.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('app.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cliente_id')
                    ->label(__('app.client'))
                    ->relationship('cliente', 'nome')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListCantieri::route('/'),
            'create' => Pages\CreateCantiere::route('/create'),
            'edit' => Pages\EditCantiere::route('/{record}/edit'),
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
        return __('app.construction_sites');
    }

    public static function getPluralLabel(): ?string
    {
        return __('app.construction_sites');
    }

    public static function getLabel(): ?string
    {
        return __('app.construction_site');
    }
}
