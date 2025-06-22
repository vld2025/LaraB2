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
    protected static ?string $navigationLabel = 'I Miei Documenti';
    protected static ?int $navigationSort = 3;

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
                                        // User può selezionare solo categorie user_upload
                                        return $query->where('tipo_accesso', 'user_upload');
                                    })
                                    ->pluck('nome', 'id');
                            })
                            ->required()
                            ->preload()
                            ->helperText($isManager ? 'Seleziona la cartella di destinazione' : 'Puoi caricare solo nei tuoi documenti personali'),
                            
                        Forms\Components\Select::make('documentabile_id')
                            ->label('Assegna a dipendente')
                            ->options(\App\Models\User::whereHas('roles', function($q) {
                                $q->where('name', 'user');
                            })->pluck('name', 'id'))
                            ->visible($isManager)
                            ->default(auth()->id())
                            ->searchable()
                            ->preload()
                            ->helperText('Seleziona il dipendente a cui assegnare il documento'),
                            
                        Forms\Components\Textarea::make('descrizione')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                            
                        Forms\Components\DatePicker::make('data_documento')
                            ->label('Data Documento')
                            ->default(now()),
                            
                        Forms\Components\DatePicker::make('data_scadenza')
                            ->label('Data Scadenza')
                            ->helperText('Per documenti con scadenza (patente, certificati, etc)'),
                    ])
                    ->columns(2),
                    
                Section::make('File')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Documento')
                            ->directory('documenti')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/*',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->maxSize(20480)
                            ->required()
                            ->downloadable()
                            ->openable()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $path = Storage::path($state);
                                    if (file_exists($path)) {
                                        $set('dimensione', filesize($path));
                                        $set('hash_sha256', hash_file('sha256', $path));
                                        $set('mime_type', mime_content_type($path));
                                        $set('file_originale', basename($state));
                                    }
                                }
                            }),
                            
                        Forms\Components\Hidden::make('caricato_da')
                            ->default(auth()->id()),
                        Forms\Components\Hidden::make('dimensione'),
                        Forms\Components\Hidden::make('hash_sha256'),
                        Forms\Components\Hidden::make('mime_type'),
                        Forms\Components\Hidden::make('file_originale'),
                        Forms\Components\Hidden::make('documentabile_type')
                            ->default('App\Models\User'),
                        Forms\Components\Hidden::make('documentabile_id')
                            ->default(fn () => !auth()->user()->hasRole(['manager', 'admin']) ? auth()->id() : null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                
                // User normale vede solo i suoi documenti
                if (!$user->hasRole(['manager', 'admin'])) {
                    $query->where('documentabile_id', $user->id);
                }
                
                // Includi sempre la relazione categoria
                $query->with('categoria');
            })
            ->groups([
                Tables\Grouping\Group::make('categoria.nome')
                    ->label('Cartella')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn ($record) => $record->categoria->nome ?? 'Senza categoria')
            ])
            ->defaultGroup('categoria.nome')
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('categoria.nome')
                    ->label('Cartella')
                    ->badge()
                    ->color(fn ($record) => match($record->categoria->slug ?? '') {
                        'buste-paga' => 'success',
                        'documenti-personali' => 'info',
                        'documenti-aziendali' => 'warning',
                        default => 'gray'
                    })
                    ->icon(fn ($record) => $record->categoria->icona ?? 'heroicon-o-folder'),
                    
                Tables\Columns\TextColumn::make('utente.name')
                    ->label('Dipendente')
                    ->visible(fn () => auth()->user()->hasRole(['manager', 'admin']))
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('data_documento')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('data_scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->data_scadenza) return null;
                        $giorni = $record->giorni_alla_scadenza;
                        if ($giorni < 0) return 'danger';
                        if ($giorni <= 30) return 'warning';
                        return null;
                    })
                    ->label('Scadenza'),
                    
                Tables\Columns\TextColumn::make('dimensione_file_umana')
                    ->label('Dimensione'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Caricato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->label('Cartella')
                    ->options(fn () => CategoriaDocumento::accessibili()->pluck('nome', 'id'))
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('documentabile_id')
                    ->label('Dipendente')
                    ->options(\App\Models\User::whereHas('roles', function($q) {
                        $q->where('name', 'user');
                    })->pluck('name', 'id'))
                    ->visible(fn () => auth()->user()->hasRole(['manager', 'admin']))
                    ->searchable(),
                    
                Tables\Filters\Filter::make('scaduti')
                    ->label('Scaduti')
                    ->query(fn (Builder $query): Builder => $query->scaduti()),
                    
                Tables\Filters\Filter::make('in_scadenza')
                    ->label('In scadenza (30gg)')
                    ->query(fn (Builder $query): Builder => $query->inScadenza()),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Scarica')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $path = storage_path('app/' . $record->file_path);
                        
                        if (file_exists($path)) {
                            return response()->download(
                                $path,
                                $record->file_originale ?? $record->nome
                            );
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('File non trovato')
                                ->danger()
                                ->send();
                            return null;
                        }
                    }),
                    
                Tables\Actions\ViewAction::make()
                    ->label('Visualizza'),
                    
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) {
                        $user = auth()->user();
                        
                        // Manager/Admin possono sempre modificare
                        if ($user->hasRole(['manager', 'admin'])) {
                            return true;
                        }
                        
                        // User può modificare SOLO se:
                        // 1. È il creatore del documento
                        // 2. La categoria permette upload agli user (documenti personali)
                        return $record->caricato_da === $user->id && 
                               $record->categoria->tipo_accesso === 'user_upload';
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->visible(function ($record) {
                        $user = auth()->user();
                        
                        // Manager/Admin possono sempre eliminare
                        if ($user->hasRole(['manager', 'admin'])) {
                            return true;
                        }
                        
                        // User può eliminare SOLO se:
                        // 1. È il creatore del documento
                        // 2. La categoria permette upload agli user (documenti personali)
                        return $record->caricato_da === $user->id && 
                               $record->categoria->tipo_accesso === 'user_upload';
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['manager', 'admin'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nessun documento')
            ->emptyStateDescription('Non ci sono documenti in questa sezione.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentos::route('/'),
            'create' => Pages\CreateDocumento::route('/create'),
            'edit' => Pages\EditDocumento::route('/{record}/edit'),
            'view' => Pages\ViewDocumento::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        
        if ($user->hasRole(['manager', 'admin'])) {
            return true;
        }
        
        // User può creare solo se esistono categorie user_upload attive
        return CategoriaDocumento::where('tipo_accesso', 'user_upload')
            ->where('attiva', true)
            ->exists();
    }

    // Policy per edit e delete - override a livello di record
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        
        if ($user->hasRole(['manager', 'admin'])) {
            return true;
        }
        
        // User può modificare solo documenti personali che ha caricato
        return $record->caricato_da === $user->id && 
               $record->categoria->tipo_accesso === 'user_upload';
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        
        $query = static::getModel()::query();
        
        if (!$user->hasRole(['manager', 'admin'])) {
            $query->where('documentabile_id', $user->id);
        }
        
        $count = $query->count();
        
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
