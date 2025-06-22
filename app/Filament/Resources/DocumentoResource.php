<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentoResource\Pages;
use App\Models\Documento;
use App\Models\CategoriaDocumento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Storage;

class DocumentoResource extends Resource
{
    protected static ?string $model = Documento::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Documenti';
    protected static ?int $navigationSort = 4;
    
    // Forza il nome corretto senza pluralizzazione automatica
    protected static ?string $slug = 'documenti';
    protected static ?string $pluralModelLabel = 'Documenti';
    protected static ?string $modelLabel = 'Documento';

    // ✅ NASCONDI PER USER NORMALI - SOLO PER ADMIN/MANAGER
    public static function canAccess(): bool
    {
        $user = auth()->user();
        // Solo admin e manager vedono la sezione "Documenti"
        return $user && $user->hasRole(['admin', 'manager']);
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isManager = $user->hasRole(['manager', 'admin']);

        return $form
            ->schema([
                Section::make('Informazioni Documento')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome Documento')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('categoria_id')
                            ->label('Cartella')
                            ->options(function () use ($isManager) {
                                return CategoriaDocumento::attive()
                                    ->accessibili()
                                    ->when(!$isManager, function ($query) {
                                        return $query->where('tipo_accesso', 'user_upload');
                                    })
                                    ->pluck('nome', 'id');
                            })
                            ->required()
                            ->preload(),

                        Forms\Components\Select::make('documentabile_id')
                            ->label('Assegna a dipendente')
                            ->options(\App\Models\User::whereHas('roles', function($q) {
                                $q->where('name', 'user');
                            })->pluck('name', 'id'))
                            ->visible($isManager)
                            ->default(auth()->id())
                            ->searchable()
                            ->preload()
                            ->helperText('Se non selezionato, verrà assegnato a te'),

                        Forms\Components\Textarea::make('descrizione')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('data_documento')
                            ->label('Data Documento')
                            ->default(now()),

                        Forms\Components\DatePicker::make('data_scadenza')
                            ->label('Data Scadenza'),
                    ])
                    ->columns(2),

                Section::make('File')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Documento')
                            ->directory('documenti')
                            ->disk('public')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/*',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->maxSize(20480)
                            ->downloadable()
                            ->required()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isManager = $user->hasRole(['manager', 'admin']);

        return $table
            ->query(function () use ($isManager) {
                return Documento::query()
                    ->with(['categoria', 'documentabile'])
                    ->when(!$isManager, function ($query) {
                        return $query->where('documentabile_id', auth()->id());
                    });
            })
            ->columns([
                Tables\Columns\TextColumn::make('categoria.nome')
                    ->label('Cartella')
                    ->badge()
                    ->color(fn ($record) => $record->categoria->colore ?? 'gray'),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('documentabile.name')
                    ->label('Dipendente')
                    ->visible($isManager),

                Tables\Columns\TextColumn::make('data_documento')
                    ->label('Data Doc.')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_scadenza')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->color(function ($record) {
                        if (!$record->data_scadenza) return 'gray';
                        if ($record->data_scadenza->isPast()) return 'danger';
                        if ($record->data_scadenza->diffInDays() <= 30) return 'warning';
                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('dimensione_file_umana')
                    ->label('Dimensione'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->label('Cartella')
                    ->options(fn () => CategoriaDocumento::pluck('nome', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) use ($isManager) {
                        if ($isManager) return true;
                        return $record->categoria->tipo_accesso === 'user_upload' &&
                               $record->documentabile_id === auth()->id();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(function ($record) use ($isManager) {
                        if ($isManager) return true;
                        return $record->categoria->tipo_accesso === 'user_upload' &&
                               $record->documentabile_id === auth()->id();
                    }),
                Tables\Actions\Action::make('scarica')
                    ->label('Scarica')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $filePath = str_replace('public/', '', $record->file_path);
                        $fullPath = Storage::disk('public')->path($filePath);
                        return response()->download($fullPath, $record->file_originale ?? $record->nome);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumenti::route('/'),
            'create' => Pages\CreateDocumento::route('/create'),
            'view' => Pages\ViewDocumento::route('/{record}'),
            'edit' => Pages\EditDocumento::route('/{record}/edit'),
        ];
    }
}
