<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommessaResource\Pages;
use App\Models\Commessa;
use App\Models\Cantiere;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommessaResource extends Resource
{
    protected static ?string $model = Commessa::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.order_information'))
                    ->schema([
                        Forms\Components\Select::make('cantiere_id')
                            ->label(__('app.construction_site'))
                            ->relationship('cantiere', 'nome')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $cantiere = Cantiere::find($state);
                                    if ($cantiere) {
                                        $count = Commessa::where('cantiere_id', $state)->count() + 1;
                                        $codice = sprintf('%s-%03d', substr($cantiere->codice, 0, 3), $count);
                                        $set('codice', $codice);
                                    }
                                }
                            })
                            ->createOptionForm([
                                Forms\Components\Select::make('cliente_id')
                                    ->label(__('app.client'))
                                    ->relationship('cliente', 'nome')
                                    ->required(),
                                Forms\Components\TextInput::make('nome')
                                    ->label(__('app.name'))
                                    ->required(),
                                Forms\Components\TextInput::make('codice')
                                    ->label(__('app.code'))
                                    ->required()
                                    ->unique(),
                            ]),
                        Forms\Components\TextInput::make('nome')
                            ->label(__('app.order_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('codice')
                            ->label(__('app.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder(__('app.auto_generated')),
                        Forms\Components\Textarea::make('descrizione')
                            ->label(__('app.description'))
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make(__('app.details'))
                    ->schema([
                        Forms\Components\DatePicker::make('data_inizio')
                            ->label(__('app.start_date'))
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('data_fine')
                            ->label(__('app.end_date'))
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('budget')
                            ->label(__('app.budget'))
                            ->numeric()
                            ->prefix('CHF')
                            ->maxValue(999999.99),
                        Forms\Components\Toggle::make('attiva')
                            ->label(__('app.active'))
                            ->default(true),
                    ])->columns(2),
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
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('cantiere.nome')
                    ->label(__('app.construction_site'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantiere.cliente.nome')
                    ->label(__('app.client'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('data_inizio')
                    ->label(__('app.start'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget')
                    ->label(__('app.budget'))
                    ->money('CHF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('totale_ore')
                    ->label(__('app.hours'))
                    ->getStateUsing(fn ($record) => $record->getTotaleOre())
                    ->suffix(' h')
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('attiva')
                    ->label(__('app.active'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cantiere_id')
                    ->label(__('app.construction_site'))
                    ->relationship('cantiere', 'nome')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('attiva')
                    ->label(__('app.active')),
                Tables\Filters\Filter::make('con_report')
                    ->label(__('app.with_reports'))
                    ->query(fn (Builder $query): Builder => $query->has('reports')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn ($record) => view('filament.resources.commessa-stats', ['commessa' => $record])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCommesse::route('/'),
            'create' => Pages\CreateCommessa::route('/create'),
            'edit' => Pages\EditCommessa::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('attiva', true)->count();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('app.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.orders');
    }

    public static function getPluralLabel(): ?string
    {
        return __('app.orders');
    }

    public static function getLabel(): ?string
    {
        return __('app.order');
    }
}
