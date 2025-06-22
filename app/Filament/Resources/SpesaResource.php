<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpesaResource\Pages;
use App\Models\Spesa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Http\Controllers\ScontriniPdfController;

class SpesaResource extends Resource
{
    protected static ?string $model = Spesa::class;
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Carica Scontrini')
                    ->description('Carica foto o PDF degli scontrini.')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_scontrino')
                            ->label('Scontrino (Foto o PDF)')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'application/pdf'
                            ])
                            ->directory('scontrini')
                            ->disk('public')
                            ->maxSize(10240)
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth(1200)
                            ->imageResizeTargetHeight(1600)
                            ->helperText('Formati supportati: JPG, PNG, WebP, PDF (max 10MB)')
                            ->required(),

                        Forms\Components\Textarea::make('note')
                            ->label('Note')
                            ->placeholder('Note opzionali per questo scontrino')
                            ->rows(2),
                    ])->columns(1),

                Forms\Components\Section::make('Periodo')
                    ->description('Seleziona il mese e anno di riferimento.')
                    ->schema([
                        Forms\Components\Select::make('mese')
                            ->label('Mese')
                            ->options([
                                1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo',
                                4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
                                7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre',
                                10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
                            ])
                            ->default(now()->month)
                            ->required(),

                        Forms\Components\Select::make('anno')
                            ->label('Anno')
                            ->options(function () {
                                $currentYear = now()->year;
                                return collect(range($currentYear - 1, $currentYear + 1))
                                    ->mapWithKeys(fn ($year) => [$year => $year]);
                            })
                            ->default(now()->year)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('foto_scontrino')
                    ->label('Scontrino')
                    ->view('filament.tables.columns.file-preview')
                    ->width(80),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utente')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),

                Tables\Columns\TextColumn::make('tipo_file')
                    ->label('Tipo')
                    ->getStateUsing(function ($record) {
                        if (!$record->foto_scontrino) return 'N/A';
                        $extension = pathinfo($record->foto_scontrino, PATHINFO_EXTENSION);
                        return strtoupper($extension);
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PDF' => 'danger',
                        'JPG', 'JPEG' => 'success',
                        'PNG' => 'info',
                        'WEBP' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('mese')
                    ->label('Mese')
                    ->formatStateUsing(fn ($state) => match($state) {
                        1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile',
                        5 => 'Maggio', 6 => 'Giugno', 7 => 'Luglio', 8 => 'Agosto',
                        9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre',
                        default => $state
                    }),

                Tables\Columns\TextColumn::make('anno')
                    ->label('Anno'),

                Tables\Columns\TextColumn::make('note')
                    ->label('Note')
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Caricato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_file')
                    ->label('Visualizza')
                    ->icon('heroicon-o-eye')
                    ->url(function ($record) {
                        if (!$record->foto_scontrino) return null;
                        return asset('storage/' . $record->foto_scontrino);
                    })
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Utente')
                    ->relationship('user', 'name')
                    ->visible(fn () => Auth::user()->hasRole(['admin', 'manager'])),

                Tables\Filters\SelectFilter::make('mese')
                    ->label('Mese')
                    ->options([
                        1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo',
                        4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
                        7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre',
                        10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
                    ])
                    ->default(now()->month),

                Tables\Filters\SelectFilter::make('anno')
                    ->label('Anno')
                    ->options(function () {
                        $currentYear = now()->year;
                        return collect(range($currentYear - 2, $currentYear + 1))
                            ->mapWithKeys(fn ($year) => [$year => $year]);
                    })
                    ->default(now()->year),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('+ Aggiungi Scontrino')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),

                Tables\Actions\Action::make('genera_pdf')
                    ->label('📧 PDF Unificato + Email')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn () => Auth::user()->hasRole('admin'))
                    ->form([
                        Forms\Components\Select::make('mese_pdf')
                            ->label('Mese')
                            ->options([
                                1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo',
                                4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
                                7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre',
                                10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
                            ])
                            ->default(now()->month)
                            ->required(),

                        Forms\Components\Select::make('anno_pdf')
                            ->label('Anno')
                            ->options(function () {
                                $currentYear = now()->year;
                                return collect(range($currentYear - 2, $currentYear + 1))
                                    ->mapWithKeys(fn ($year) => [$year => $year]);
                            })
                            ->default(now()->year)
                            ->required(),

                        Forms\Components\TextInput::make('email_manager')
                            ->label('Email Destinatario')
                            ->email()
                            ->default(function() { return \DB::table("impostazioni_fattura")->value("email_automazione_pdf") ?? "vlad@vldservice.ch"; })
                            ->required()
                            ->helperText('Email a cui inviare il PDF unificato'),
                    ])
                    ->action(function (array $data) {
                        $controller = new ScontriniPdfController();
                        $request = request();
                        $request->merge($data);

                        $result = $controller->generaPdfMensile($request);
                        $response = $result->getData(true);

                        if ($response['success']) {
                            Notification::make()
                                ->title($response['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title($response['message'])
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('configura_automazione')
                    ->label('⚙️ Automazione PDF')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn () => Auth::user()->hasRole('admin'))
                    ->form([
                        Forms\Components\Section::make('Configurazione Invio Automatico')
                            ->schema([
                                Forms\Components\Select::make('giorno_mese')
                                    ->label('Giorno del Mese')
                                    ->options(array_combine(range(1, 28), range(1, 28)))
                                    ->default(function() { return \DB::table("impostazioni_fattura")->value("giorno_automazione_pdf") ?? 22; })
                                    ->required()
                                    ->helperText('Giorno del mese in cui inviare il PDF (1-28)'),

                                Forms\Components\TimePicker::make('ora_invio')
                                    ->label('Ora di Invio')
                                    ->default(function() { return \DB::table("impostazioni_fattura")->value("ora_automazione_pdf") ?? "08:00"; })
                                    ->required()
                                    ->helperText('Ora di invio del PDF (formato 24h)'),

                                Forms\Components\TextInput::make('email_destinatario')
                                    ->label('Email Destinatario')
                                    ->email()
                                    ->default(function() { return \DB::table("impostazioni_fattura")->value("email_automazione_pdf") ?? "vlad@vldservice.ch"; })
                                    ->required()
                                    ->helperText('Email a cui inviare automaticamente il PDF'),

                                Forms\Components\Toggle::make('attivo')
                                    ->label('Automazione Attiva')
                                    ->default(function() { return \DB::table("impostazioni_fattura")->value("automazione_pdf_attiva") ?? true; })
                                    ->helperText('Attiva/disattiva l\'invio automatico'),

                                Forms\Components\Select::make('mese_precedente')
                                    ->label('Periodo di Riferimento')
                                    ->options([
                                        'current' => 'Mese Corrente',
                                        'previous' => 'Mese Precedente'
                                    ])
                                    ->default(function() { return \DB::table("impostazioni_fattura")->value("mese_automazione_pdf") ?? "previous"; })
                                    ->required()
                                    ->helperText('Quale mese includere nel PDF'),
                            ])
                    ])
                    ->action(function (array $data) {
                        // Salva configurazione nella tabella impostazioni
                        \DB::table('impostazioni_fattura')->updateOrInsert(
                            ['cliente_id' => 1],
                            [
                                'giorno_automazione_pdf' => $data['giorno_mese'],
                                'ora_automazione_pdf' => $data['ora_invio'],
                                'email_automazione_pdf' => $data['email_destinatario'],
                                'automazione_pdf_attiva' => $data['attivo'],
                                'mese_automazione_pdf' => $data['mese_precedente'],
                                'updated_at' => now()
                            ]
                        );

                        Notification::make()
                            ->title('✅ Automazione configurata!')
                            ->body("PDF sarà inviato automaticamente il giorno {$data['giorno_mese']} alle {$data['ora_invio']} a {$data['email_destinatario']}")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                if (!Auth::user()->hasRole(['admin', 'manager'])) {
                    $query->where('user_id', Auth::id());
                }
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpesas::route('/'),
            'create' => Pages\CreateSpesa::route('/create'),
            'edit' => Pages\EditSpesa::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Lavoro';
    }

    public static function getNavigationLabel(): string
    {
        return 'Scontrini';
    }
}
