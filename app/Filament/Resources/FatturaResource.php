<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FatturaResource\Pages;
use App\Models\Fattura;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\FatturaInviata;

class FatturaResource extends Resource
{
    protected static ?string $model = Fattura::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Fatture';
    protected static ?string $modelLabel = 'Fattura';
    protected static ?string $pluralModelLabel = 'Fatture';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dettagli Fattura')
                    ->schema([
                        Forms\Components\TextInput::make('numero_fattura')
                            ->label('Numero Fattura')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'nome')
                            ->required()
                            ->searchable(),

                        Forms\Components\DatePicker::make('data_fattura')
                            ->label('Data Fattura')
                            ->required(),

                        Forms\Components\DatePicker::make('data_scadenza')
                            ->label('Data Scadenza')
                            ->required(),

                        Forms\Components\Select::make('stato')
                            ->label('Stato')
                            ->options([
                                'bozza' => 'Bozza',
                                'inviata' => 'Inviata',
                                'pagata' => 'Pagata',
                                'scaduta' => 'Scaduta',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Periodo')
                    ->schema([
                        Forms\Components\Select::make('mese')
                            ->label('Mese')
                            ->options([
                                1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo',
                                4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
                                7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre',
                                10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('anno')
                            ->label('Anno')
                            ->numeric()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Importi')
                    ->schema([
                        Forms\Components\TextInput::make('ore_totali')
                            ->label('Ore Totali')
                            ->numeric()
                            ->default(0.00),

                        Forms\Components\TextInput::make('importo_manodopera')
                            ->label('Manodopera (CHF)')
                            ->numeric()
                            ->default(0.00),

                        Forms\Components\TextInput::make('giorni_trasferta')
                            ->label('Giorni Trasferta')
                            ->numeric()
                            ->default(0.00),

                        Forms\Components\TextInput::make('importo_trasferte')
                            ->label('Trasferte (CHF)')
                            ->numeric()
                            ->default(0.00),

                        Forms\Components\TextInput::make('km_totali')
                            ->label('Km Totali')
                            ->numeric()
                            ->default(0.00),

                        Forms\Components\TextInput::make('importo_km')
                            ->label('Chilometri (CHF)')
                            ->numeric()
                            ->default(0.00),

                        Forms\Components\TextInput::make('importo_spese_extra')
                            ->label('Spese Extra (CHF)')
                            ->numeric()
                            ->default(0.00),

                        Forms\Components\TextInput::make('sconto')
                            ->label('Sconto (CHF)')
                            ->numeric()
                            ->default(0.00),
                    ])->columns(2),

                Forms\Components\Section::make('Totali')
                    ->schema([
                        Forms\Components\TextInput::make('subtotale')
                            ->label('Subtotale (CHF)')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('aliquota_iva')
                            ->label('IVA (%)')
                            ->numeric()
                            ->default(8.10),

                        Forms\Components\TextInput::make('importo_iva')
                            ->label('Importo IVA (CHF)')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('totale_finale')
                            ->label('Totale (CHF)')
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Note e Email')
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->label('Note'),

                        Forms\Components\TextInput::make('email_destinatario')
                            ->label('Email Destinatario')
                            ->email(),

                        Forms\Components\DateTimePicker::make('data_invio_email')
                            ->label('Data Invio Email')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_fattura')
                    ->label('Numero')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_fattura')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mese')
                    ->label('Periodo')
                    ->formatStateUsing(fn($record) => sprintf('%02d/%d', $record->mese, $record->anno))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('stato')
                    ->label('Stato')
                    ->colors([
                        'secondary' => 'bozza',
                        'primary' => 'inviata',
                        'success' => 'pagata',
                        'danger' => 'scaduta',
                    ]),

                Tables\Columns\TextColumn::make('totale_finale')
                    ->label('Totale CHF')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_scadenza')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stato')
                    ->options([
                        'bozza' => 'Bozza',
                        'inviata' => 'Inviata',
                        'pagata' => 'Pagata',
                        'scaduta' => 'Scaduta',
                    ]),

                Tables\Filters\SelectFilter::make('cliente')
                    ->relationship('cliente', 'nome'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('viewPdf')
                    ->label('Visualizza PDF')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Fattura $record): string => route('fatture.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Scarica PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Fattura $record): string => route('fatture.download', $record)),
                Tables\Actions\Action::make('inviaEmail')
                    ->label('Invia Email')
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Invia Fattura via Email')
                    ->modalDescription(fn (Fattura $record): string =>
                        'Vuoi inviare la fattura ' . $record->numero_fattura . ' via email?')
                    ->action(function (Fattura $record) {
                        $emailDestinatario = $record->email_destinatario ?? $record->cliente->email;

                        if (!$emailDestinatario) {
                            \Filament\Notifications\Notification::make()
                                ->title('Errore')
                                ->body('Nessun indirizzo email specificato per il cliente.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            Mail::to($emailDestinatario)->send(new FatturaInviata($record));

                            $record->update([
                                'data_invio_email' => now(),
                                'email_destinatario' => $emailDestinatario,
                                'stato' => 'inviata'
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Email inviata')
                                ->body('Email inviata con successo a: ' . $emailDestinatario)
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Errore invio')
                                ->body('Errore nell\'invio email: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Fattura $record): bool => $record->stato !== 'inviata'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('data_fattura', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFatturas::route('/'),
            'create' => Pages\CreateFattura::route('/create'),
            'edit' => Pages\EditFattura::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user() && Auth::user() && Auth::user()->hasRole(['admin', 'manager']);
    }
}
