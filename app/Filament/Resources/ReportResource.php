<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\Commessa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.report_information'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('app.user'))
                            ->relationship('user', 'name')
                            ->default(Auth::id())
                            ->disabled(fn () => !Auth::user()->hasRole(['admin', 'manager']))
                            ->dehydrated()
                            ->required(),
                        Forms\Components\DatePicker::make('data')
                            ->label(__('app.date'))
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Forms\Components\Select::make('commessa_id')
                            ->label(__('app.order'))
                            ->options(function () {
                                $query = Commessa::with(['cantiere.cliente'])->where('attiva', true);

                                if (!Auth::user()->hasRole(['admin', 'manager'])) {
                                    // User vede solo le sue commesse recenti
                                    $commesseIds = Report::where('user_id', Auth::id())
                                        ->distinct()
                                        ->pluck('commessa_id');
                                    $query->whereIn('id', $commesseIds);
                                }

                                return $query->get()->mapWithKeys(function ($commessa) {
                                    return [$commessa->id => $commessa->cantiere->cliente->nome . ' - ' . $commessa->nome];
                                });
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    // Prendi i km dall'ultimo report per questa commessa
                                    $ultimoReport = Report::where('commessa_id', $state)
                                        ->where('user_id', Auth::id())
                                        ->orderBy('data', 'desc')
                                        ->orderBy('id', 'desc')
                                        ->first();

                                    if ($ultimoReport) {
                                        // User vede sempre i suoi dati originali
                                        $dati = $ultimoReport->getDataForUser();
                                        $set('km', $dati['km'] ?? $ultimoReport->km);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('ore')
                            ->label(__('app.hours'))
                            ->numeric()
                            ->step(0.5)
                            ->minValue(0)
                            ->maxValue(24)
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('km')
                            ->label(__('app.kilometers'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.details'))
                    ->schema([
                        Forms\Components\Toggle::make('auto_privata')
                            ->label(__('app.private_car'))
                            ->default(false),
                        Forms\Components\Toggle::make('festivo')
                            ->label(__('app.holiday'))
                            ->default(false)
                            ->helperText(__('app.holiday_help')),
                        Forms\Components\Toggle::make('notturno')
                            ->label(__('app.night_work'))
                            ->default(false),
                        Forms\Components\Toggle::make('trasferta')
                            ->label(__('app.business_trip'))
                            ->default(false),
                    ])->columns(2),

                // Sezione visibile solo ad admin/manager che mostra le differenze
                Forms\Components\Section::make('Versioni Report')
                    ->schema([
                        Forms\Components\Placeholder::make('versione_originale')
                            ->label('Versione Utente (Originale)')
                            ->content(function ($record) {
                                if (!$record) return 'N/A';
                                $dati = $record->getDataForUser();
                                return "Ore: {$dati['ore']} | Km: {$dati['km']} | Auto: " . 
                                       ($dati['auto_privata'] ? 'Sì' : 'No') . 
                                       " | Festivo: " . ($dati['festivo'] ? 'Sì' : 'No') . 
                                       " | Notturno: " . ($dati['notturno'] ? 'Sì' : 'No') . 
                                       " | Trasferta: " . ($dati['trasferta'] ? 'Sì' : 'No');
                            }),
                        Forms\Components\Placeholder::make('versione_manager')
                            ->label('Versione Manager (Per Fatturazione)')
                            ->content(function ($record) {
                                if (!$record) return 'N/A';
                                $dati = $record->getDataForManager();
                                return "Ore: {$dati['ore']} | Km: {$dati['km']} | Auto: " . 
                                       ($dati['auto_privata'] ? 'Sì' : 'No') . 
                                       " | Festivo: " . ($dati['festivo'] ? 'Sì' : 'No') . 
                                       " | Notturno: " . ($dati['notturno'] ? 'Sì' : 'No') . 
                                       " | Trasferta: " . ($dati['trasferta'] ? 'Sì' : 'No');
                            }),
                    ])
                    ->visible(fn () => Auth::user()->hasRole(['admin', 'manager']))
                    ->collapsed(),
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
                Tables\Columns\TextColumn::make('commessa.nome')
                    ->label(__('app.order'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('commessa.cantiere.cliente.nome')
                    ->label(__('app.client'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ore_display')
                    ->label(__('app.hours'))
                    ->getStateUsing(function (Report $record) {
                        // Mostra i dati giusti in base al ruolo
                        if (Auth::user()->hasRole(['admin', 'manager'])) {
                            $dati = $record->getDataForManager();
                            return $dati['ore'] ?? $record->ore;
                        } else {
                            $dati = $record->getDataForUser();
                            return $dati['ore'] ?? $record->ore;
                        }
                    })
                    ->numeric()
                    ->sortable()
                    ->suffix(' h'),
                Tables\Columns\TextColumn::make('km_display')
                    ->label(__('app.km'))
                    ->getStateUsing(function (Report $record) {
                        // Mostra i dati giusti in base al ruolo
                        if (Auth::user()->hasRole(['admin', 'manager'])) {
                            $dati = $record->getDataForManager();
                            return $dati['km'] ?? $record->km;
                        } else {
                            $dati = $record->getDataForUser();
                            return $dati['km'] ?? $record->km;
                        }
                    })
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('auto_privata_display')
                    ->label(__('app.car'))
                    ->getStateUsing(function (Report $record) {
                        if (Auth::user()->hasRole(['admin', 'manager'])) {
                            $dati = $record->getDataForManager();
                            return $dati['auto_privata'] ?? $record->auto_privata;
                        } else {
                            $dati = $record->getDataForUser();
                            return $dati['auto_privata'] ?? $record->auto_privata;
                        }
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('festivo_display')
                    ->label(__('app.hol'))
                    ->getStateUsing(function (Report $record) {
                        if (Auth::user()->hasRole(['admin', 'manager'])) {
                            $dati = $record->getDataForManager();
                            return $dati['festivo'] ?? $record->festivo;
                        } else {
                            $dati = $record->getDataForUser();
                            return $dati['festivo'] ?? $record->festivo;
                        }
                    })
                    ->boolean(),
                Tables\Columns\IconColumn::make('trasferta_display')
                    ->label(__('app.trip'))
                    ->getStateUsing(function (Report $record) {
                        if (Auth::user()->hasRole(['admin', 'manager'])) {
                            $dati = $record->getDataForManager();
                            return $dati['trasferta'] ?? $record->trasferta;
                        } else {
                            $dati = $record->getDataForUser();
                            return $dati['trasferta'] ?? $record->trasferta;
                        }
                    })
                    ->boolean(),
                Tables\Columns\IconColumn::make('modified_by_manager')
                    ->label('Modificato')
                    ->getStateUsing(fn (Report $record) => $record->isModifiedByManager())
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil-square')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
                Tables\Columns\IconColumn::make('fatturato')
                    ->label(__('app.inv'))
                    ->boolean()
                    ->trueColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('app.user'))
                    ->relationship('user', 'name')
                    ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
                Tables\Filters\SelectFilter::make('commessa_id')
                    ->label(__('app.order'))
                    ->relationship('commessa', 'nome')
                    ->searchable()
                    ->preload(),
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
                    ->visible(fn (Report $record) => Auth::user()->canEditReport($record)),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
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
                    ->count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->canAccessPanel(filament()->getCurrentPanel());
    }

    public static function getNavigationGroup(): ?string
    {
        return __('app.work');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.reports');
    }

    public static function getPluralLabel(): ?string
    {
        return __('app.reports');
    }

    public static function getLabel(): ?string
    {
        return __('app.report');
    }
}
