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
                                        $set('km', $ultimoReport->km);
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
                Tables\Columns\TextColumn::make('ore')
                    ->label(__('app.hours'))
                    ->numeric()
                    ->sortable()
                    ->suffix(' h'),
                Tables\Columns\TextColumn::make('km')
                    ->label(__('app.km'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('auto_privata')
                    ->label(__('app.car'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('festivo')
                    ->label(__('app.hol'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('notturno')
                    ->label(__('app.night'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('trasferta')
                    ->label(__('app.trip'))
                    ->boolean(),
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
            ->
