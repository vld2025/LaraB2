<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImpostazioniFatturaResource\Pages;
use App\Models\ImpostazioniFattura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ImpostazioniFatturaResource extends Resource
{
    protected static ?string $model = ImpostazioniFattura::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 10;

    public static function canViewAny(): bool
    {
        return auth()->user() ? auth()->user() ? auth()->user()->can('viewAny', ImpostazioniFattura::class) : false : false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.client'))
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->label(__('app.client'))
                            ->relationship('cliente', 'nome')
                            ->searchable()
                            ->preload()
                            ->placeholder(__('app.default_settings'))
                            ->helperText(__('app.leave_empty_for_default')),
                    ]),
                    
                Forms\Components\Section::make(__('app.costs'))
                    ->schema([
                        Forms\Components\TextInput::make('costo_orario')
                            ->label(__('app.hourly_cost'))
                            ->numeric()
                            ->prefix('CHF')
                            ->required()
                            ->default(80),
                        Forms\Components\TextInput::make('costo_km')
                            ->label(__('app.cost_per_km'))
                            ->numeric()
                            ->prefix('CHF')
                            ->required()
                            ->default(0.70),
                        Forms\Components\TextInput::make('costo_pranzo')
                            ->label(__('app.lunch_cost'))
                            ->numeric()
                            ->prefix('CHF')
                            ->required()
                            ->default(25),
                        Forms\Components\TextInput::make('costo_pernottamento')
                            ->label(__('app.accommodation_cost'))
                            ->numeric()
                            ->prefix('CHF')
                            ->required()
                            ->default(120),
                    ])->columns(2),
                    
                Forms\Components\Section::make(__('app.invoicing'))
                    ->schema([
                        Forms\Components\TextInput::make('giorno_fatturazione')
                            ->label(__('app.invoicing_day'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(28)
                            ->required()
                            ->default(22)
                            ->helperText(__('app.invoicing_day_help')),
                        Forms\Components\TextInput::make('email_destinatario')
                            ->label(__('app.recipient_email'))
                            ->email()
                            ->helperText(__('app.email_invoice_help')),
                        Forms\Components\Toggle::make('invia_automatico')
                            ->label(__('app.automatic_sending'))
                            ->helperText(__('app.automatic_sending_help'))
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label(__('app.client'))
                    ->default(__('app.default_settings'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('costo_orario')
                    ->label('CHF/h')
                    ->money('CHF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('costo_km')
                    ->label('CHF/km')
                    ->money('CHF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('giorno_fatturazione')
                    ->label(__('app.invoicing_day_short'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('invia_automatico')
                    ->label(__('app.auto'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('app.updated_at'))
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->cliente_id !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImpostazioniFatturas::route('/'),
            'create' => Pages\CreateImpostazioniFattura::route('/create'),
            'edit' => Pages\EditImpostazioniFattura::route('/{record}/edit'),
        ];
    }
    
    public static function canAccess(): bool
    {
        return Auth::user() && Auth::user() && Auth::user()->hasRole(['admin', 'manager']);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('app.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.invoice_settings');
    }

    public static function getPluralLabel(): ?string
    {
        return __('app.invoice_settings');
    }

    public static function getLabel(): ?string
    {
        return __('app.invoice_setting');
    }
}
