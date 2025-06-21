<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpesaExtraResource\Pages;
use App\Models\SpesaExtra;
use App\Models\Commessa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SpesaExtraResource extends Resource
{
    protected static ?string $model = SpesaExtra::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?int $navigationSort = 6;

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
                                    // User vede solo le commesse dove ha report
                                    $commesseIds = \App\Models\Report::where('user_id', Auth::id())
                                        ->distinct()
                                        ->pluck('commessa_id');
                                    $query->whereIn('id', $commesseIds);
                                }

                                return $query->get()->mapWithKeys(function ($commessa) {
                                    return [$commessa->id => $commessa->cantiere->cliente->nome . ' - ' . $commessa->nome];
                                });
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('descrizione')
                            ->label(__('app.description'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('importo')
                            ->label(__('app.amount'))
                            ->numeric()
                            ->prefix('CHF')
                            ->required(),
                        Forms\Components\FileUpload::make('foto_path')
                            ->label(__('app.receipt_photo'))
                            ->image()
                            ->directory('spese-extra')
                            ->visibility('private')
                            ->maxSize(5120) // 5MB
                            ->helperText(__('app.upload_receipt_help')),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.ai_analysis'))
                    ->schema([
                        Forms\Components\TextInput::make('importo_ai')
                            ->label(__('app.ai_detected_amount'))
                            ->numeric()
                            ->prefix('CHF')
                            ->disabled()
                            ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
                        Forms\Components\Textarea::make('risposta_ai')
                            ->label(__('app.ai_response'))
                            ->rows(3)
                            ->disabled()
                            ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
                        Forms\Components\Toggle::make('verificato')
                            ->label(__('app.verified'))
                            ->helperText(__('app.confirm_amount_correct'))
                            ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
                    ])->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
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
                Tables\Columns\TextColumn::make('descrizione')
                    ->label(__('app.description'))
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('importo')
                    ->label(__('app.amount'))
                    ->money('CHF')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('foto_path')
                    ->label(__('app.photo'))
                    ->width(50)
                    ->height(50)
                    ->visibility('private'),
                Tables\Columns\IconColumn::make('verificato')
                    ->label(__('app.verif'))
                    ->boolean()
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
                Tables\Filters\TernaryFilter::make('verificato')
                    ->label(__('app.verified'))
                    ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),
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
                    ->visible(fn (SpesaExtra $record) => !$record->fatturato &&
                        (Auth::user()->hasRole(['admin', 'manager']) || $record->user_id === Auth::id())),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('analizzaAI')
                    ->label(__('app.analyze_with_ai'))
                    ->icon('heroicon-o-cpu-chip')
                    ->color('warning')
                    ->visible(fn (SpesaExtra $record) => $record->foto_path && !$record->importo_ai && Auth::user()->hasRole(['admin', 'manager']))
                    ->action(function (SpesaExtra $record) {
                        // TODO: Implementare analisi AI
                        \Filament\Notifications\Notification::make()
                            ->title(__('app.ai_function_development'))
                            ->info()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (SpesaExtra $record) => !$record->fatturato && Auth::user()->hasRole(['admin'])),
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
            'index' => Pages\ListSpesaExtras::route('/'),
            'create' => Pages\CreateSpesaExtra::route('/create'),
            'edit' => Pages\EditSpesaExtra::route('/{record}/edit'),
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
        return __('app.extra_expenses');
    }

    public static function getPluralLabel(): ?string
    {
        return __('app.extra_expenses');
    }

    public static function getLabel(): ?string
    {
        return __('app.extra_expense');
    }
}
