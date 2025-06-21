<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpesaResource\Pages;
use App\Models\Spesa;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SpesaResource extends Resource
{
    protected static ?string $model = Spesa::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.expense_information'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('app.user'))
                            ->relationship('user', 'name')
                            ->default(Auth::id())
                            ->disabled(fn () => !Auth::user()->hasRole(['admin', 'manager']))
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Select::make('report_id')
                            ->label(__('app.report'))
                            ->options(function () {
                                $query = Report::with(['commessa.cantiere.cliente'])
                                    ->where('fatturato', false)
                                    ->orderBy('data', 'desc');

                                if (!Auth::user()->hasRole(['admin', 'manager'])) {
                                    $query->where('user_id', Auth::id());
                                }

                                return $query->get()->mapWithKeys(function ($report) {
                                    return [$report->id => $report->data->format('d/m/Y') . ' - ' . $report->commessa->nome . ' (' . $report->ore . 'h)'];
                                });
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $report = Report::find($state);
                                    if ($report) {
                                        $set('data', $report->data);

                                        // Se non è trasferta, può essere solo pranzo
                                        if (!$report->trasferta) {
                                            $set('tipo', 'pranzo');
                                        }
                                    }
                                }
                            }),
                        Forms\Components\DatePicker::make('data')
                            ->label(__('app.date'))
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Select::make('tipo')
                            ->label(__('app.type'))
                            ->options(function (Forms\Get $get) {
                                $reportId = $get('report_id');
                                if ($reportId) {
                                    $report = Report::find($reportId);
                                    if ($report && $report->trasferta) {
                                        return [
                                            'pranzo' => __('app.lunch'),
                                            'pernottamento' => __('app.accommodation'),
                                        ];
                                    }
                                }
                                return ['pranzo' => __('app.lunch')];
                            })
                            ->required(),
                        Forms\Components\TextInput::make('importo')
                            ->label(__('app.amount'))
                            ->numeric()
                            ->prefix('CHF')
                            ->default(function (Forms\Get $get) {
                                $tipo = $get('tipo');
                                $impostazioni = \App\Models\ImpostazioniFattura::getForCliente(null);

                                if ($tipo === 'pranzo') {
                                    return $impostazioni->costo_pranzo;
                                } elseif ($tipo === 'pernottamento') {
                                    return $impostazioni->costo_pernottamento;
                                }
                                return 0;
                            })
                            ->reactive()
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label(__('app.notes'))
                            ->rows(2),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data')
                    ->label(__('app.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('app.user'))
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
                Tables\Columns\TextColumn::make('report.commessa.nome')
                    ->label(__('app.order'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label(__('app.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pranzo' => 'success',
                        'pernottamento' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pranzo' => __('app.lunch'),
                        'pernottamento' => __('app.accommodation'),
                    }),
                Tables\Columns\TextColumn::make('importo')
                    ->label(__('app.amount'))
                    ->money('CHF')
                    ->sortable(),
                Tables\Columns\IconColumn::make('fatturato')
                    ->label(__('app.invoiced'))
                    ->boolean()
                    ->trueColor('danger'),
                Tables\Columns\TextColumn::make('note')
                    ->label(__('app.notes'))
                    ->limit(30)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('app.user'))
                    ->relationship('user', 'name')
                    ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
                Tables\Filters\SelectFilter::make('tipo')
                    ->label(__('app.type'))
                    ->options([
                        'pranzo' => __('app.lunch'),
                        'pernottamento' => __('app.accommodation'),
                    ]),
                Tables\Filters\TernaryFilter::make('fatturato')
                    ->label(__('app.invoiced')),
                Tables\Filters\Filter::make('data')
                    ->form([
                        Forms\Components\DatePicker::make('data_da')
                            ->label(__('app.from')),
                        Forms\Components\DatePicker::make('data_a')
                            ->label(__('app.to')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['data_da'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data', '>=', $date),
                            )
                            ->when(
                                $data['data_a'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Spesa $record) => !$record->fatturato &&
                        (Auth::user()->hasRole(['admin', 'manager']) || $record->user_id === Auth::id())),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Spesa $record) => !$record->fatturato && Auth::user()->hasRole(['admin'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->hasRole(['admin'])),
                ]),
            ])
            ->defaultSort('data', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                if (!Auth::user()->hasRole(['admin', 'manager'])) {
                    $query->where('user_id', Auth::id());
                }
            });
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
            'index' => Pages\ListSpesas::route('/'),
            'create' => Pages\CreateSpesa::route('/create'),
            'edit' => Pages\EditSpesa::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getModel()::query();

        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            $query->where('user_id', Auth::id());
        }

        return $query->whereMonth('data', now()->month)
                    ->whereYear('data', now()->year)
                    ->where('fatturato', false)
                    ->count();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('app.work');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.expenses');
    }

    public static function getPluralLabel(): ?string
    {
        return __('app.expenses');
    }

    public static function getLabel(): ?string
    {
        return __('app.expense');
    }
}
