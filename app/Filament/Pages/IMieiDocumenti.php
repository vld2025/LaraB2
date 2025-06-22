<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Str;

class IMieiDocumenti extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'I Miei Documenti';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.i-miei-documenti';

    #[Url]
    public ?string $activeFolder = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && !$user->hasRole(['admin', 'manager']);
    }

    public function mount(): void
    {
        $this->activeFolder = request()->query('folder', null);
    }

    public function getFolders(): array
    {
        $user = auth()->user();
        $folders = [];

        foreach (CategoriaDocumento::attive()->accessibili()->orderBy('ordine')->get() as $categoria) {
            $count = Documento::where('categoria_id', $categoria->id)
                ->where('documentabile_type', 'App\\Models\\User')
                ->where('documentabile_id', $user->id)
                ->count();

            $folders[] = [
                'nome' => $categoria->nome,
                'slug' => $categoria->slug,
                'descrizione' => $categoria->descrizione,
                'icona' => $categoria->icona,
                'colore' => $categoria->colore,
                'count' => $count,
                'can_upload' => $categoria->tipo_accesso === 'user_upload'
            ];
        }

        return $folders;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if ($this->activeFolder) {
            $categoria = CategoriaDocumento::where('slug', $this->activeFolder)->first();

            $actions[] = Action::make('back')
                ->label('Torna alle cartelle')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getUrl())
                ->color('gray');

            if ($categoria && $categoria->tipo_accesso === 'user_upload') {
                $actions[] = Action::make('upload')
                    ->label('Carica documento')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->modalHeading('Carica nuovo documento')
                    ->modalWidth(MaxWidth::Large)
                    ->form([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome documento')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('descrizione')
                            ->label('Descrizione')
                            ->rows(3),

                        Forms\Components\DatePicker::make('data_documento')
                            ->label('Data documento')
                            ->default(now()),

                        Forms\Components\DatePicker::make('data_scadenza')
                            ->label('Data scadenza (opzionale)'),

                        Forms\Components\FileUpload::make('file')
                            ->label('File')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->directory('documenti')
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                    ])
                    ->action(function (array $data) use ($categoria) {
                        try {
                            $uploadedFile = $data['file'];
                            
                            // Log per debug
                            \Log::info('File upload attempt', [
                                'original_path' => $uploadedFile,
                                'exists_in_storage' => Storage::disk('public')->exists($uploadedFile)
                            ]);
                            
                            // Verifica che il file esista nel disk public
                            if (!Storage::disk('public')->exists($uploadedFile)) {
                                throw new \Exception('File non trovato nel storage: ' . $uploadedFile);
                            }

                            // Il path nel database deve essere relativo al disk public
                            $relativePath = $uploadedFile;
                            $fullPath = Storage::disk('public')->path($uploadedFile);
                            
                            // Crea il documento
                            $documento = new Documento([
                                'nome' => $data['nome'],
                                'descrizione' => $data['descrizione'] ?? null,
                                'categoria_id' => $categoria->id,
                                'documentabile_type' => 'App\\Models\\User',
                                'documentabile_id' => auth()->id(),
                                'caricato_da' => auth()->id(),
                                'file_path' => 'public/' . $relativePath, // Path per Storage::download()
                                'file_originale' => pathinfo($uploadedFile, PATHINFO_BASENAME),
                                'data_documento' => $data['data_documento'],
                                'data_scadenza' => $data['data_scadenza'],
                            ]);

                            // Calcola metadati del file
                            if (file_exists($fullPath)) {
                                $documento->dimensione = filesize($fullPath);
                                $documento->mime_type = Storage::disk('public')->mimeType($uploadedFile);
                                $documento->hash_sha256 = hash_file('sha256', $fullPath);
                            }

                            $documento->save();

                            Notification::make()
                                ->title('Documento caricato con successo!')
                                ->body('File: ' . basename($uploadedFile) . ' (' . number_format($documento->dimensione) . ' bytes)')
                                ->success()
                                ->send();

                            $this->redirect(static::getUrl(['folder' => $this->activeFolder]));

                        } catch (\Exception $e) {
                            \Log::error('Upload error', ['error' => $e->getMessage()]);
                            
                            Notification::make()
                                ->title('Errore durante il caricamento')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    });
            }
        }

        return $actions;
    }

    protected function getTableQuery(): Builder
    {
        if (!$this->activeFolder) {
            return Documento::query()->whereRaw('1 = 0');
        }

        $categoria = CategoriaDocumento::where('slug', $this->activeFolder)->first();
        
        return Documento::query()
            ->where('categoria_id', $categoria->id)
            ->where('documentabile_type', 'App\\Models\\User')
            ->where('documentabile_id', auth()->id());
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nome')
                ->label('Nome documento')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('data_documento')
                ->label('Data')
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
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('preview')
                ->label('Visualizza')
                ->icon('heroicon-o-eye')
                ->modalContent(fn ($record) => view('filament.documenti.preview', ['documento' => $record]))
                ->modalHeading(fn ($record) => $record->nome)
                ->modalWidth(MaxWidth::Large),

            Tables\Actions\Action::make('scarica')
                ->label('Scarica')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function ($record) {
                    try {
                        // Path relativo senza 'public/'
                        $relativePath = str_replace('public/', '', $record->file_path);
                        $fullPath = Storage::disk('public')->path($relativePath);
                        
                        if (!file_exists($fullPath)) {
                            Notification::make()
                                ->title('Errore')
                                ->body('File non trovato: ' . $relativePath)
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        return response()->download(
                            $fullPath,
                            $record->file_originale ?? $record->nome
                        );
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Errore durante il download')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
