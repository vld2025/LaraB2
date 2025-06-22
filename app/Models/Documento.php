<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documenti';

    protected $fillable = [
        'categoria_id',
        'documentabile_type', 
        'documentabile_id',
        'nome',
        'descrizione',
        'file_path',
        'file_originale',
        'mime_type',
        'dimensione',
        'hash_sha256',
        'data_documento',
        'data_scadenza',
        'interno',
        'importante',
        'metadata',
        'caricato_da'
    ];

    protected $casts = [
        'data_documento' => 'date',
        'data_scadenza' => 'date', 
        'interno' => 'boolean',
        'importante' => 'boolean',
        'metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documento) {
            // Imposta automaticamente i valori di default
            $documento->caricato_da = auth()->id();
            
            // Se documentabile_type non è impostato, imposta User
            if (!$documento->documentabile_type) {
                $documento->documentabile_type = 'App\\Models\\User';
            }
            
            // Se documentabile_id non è impostato, imposta l'utente corrente
            if (!$documento->documentabile_id) {
                $documento->documentabile_id = auth()->id();
            }
            
            // Calcola metadati del file
            static::calculateFileMetadata($documento);
        });

        static::updating(function ($documento) {
            // Ricalcola metadati se il file_path è cambiato
            if ($documento->isDirty('file_path')) {
                static::calculateFileMetadata($documento);
            }
        });
    }

    protected static function calculateFileMetadata($documento)
    {
        if (!$documento->file_path) {
            return;
        }

        // Prova diversi path possibili
        $paths = [
            storage_path('app/' . $documento->file_path),
            storage_path('app/public/' . str_replace('public/', '', $documento->file_path)),
            storage_path('app/public/' . $documento->file_path)
        ];

        $fullPath = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $fullPath = $path;
                break;
            }
        }

        if ($fullPath && file_exists($fullPath)) {
            $documento->dimensione = filesize($fullPath);
            $documento->mime_type = mime_content_type($fullPath) ?: 'application/octet-stream';
            $documento->hash_sha256 = hash_file('sha256', $fullPath);
            
            // Se file_originale non è impostato, usa il nome del file
            if (!$documento->file_originale) {
                $documento->file_originale = basename($fullPath);
            }
        }
    }

    // Scope methods
    public function scopeImportanti(Builder $query): Builder
    {
        return $query->where('importante', true);
    }

    public function scopeInScadenza(Builder $query): Builder
    {
        return $query->whereNotNull('data_scadenza')
            ->where('data_scadenza', '>', now())
            ->where('data_scadenza', '<=', now()->addDays(30));
    }

    public function scopeScaduti(Builder $query): Builder
    {
        return $query->whereNotNull('data_scadenza')
            ->where('data_scadenza', '<', now());
    }

    // Relationships
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaDocumento::class);
    }

    public function documentabile(): MorphTo
    {
        return $this->morphTo();
    }

    public function caricatoDa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caricato_da');
    }

    // Accessors
    public function getDimensioneFileUmanaAttribute(): string
    {
        if (!$this->dimensione) return 'N/D';
        
        $bytes = $this->dimensione;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsScadutoAttribute(): bool
    {
        return $this->data_scadenza && $this->data_scadenza->isPast();
    }

    public function getGiorniAllaScadenzaAttribute(): ?int
    {
        return $this->data_scadenza ? now()->diffInDays($this->data_scadenza, false) : null;
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) return null;
        
        // Remove 'public/' prefix if present
        $path = str_replace('public/', '', $this->file_path);
        return asset('storage/' . $path);
    }

    public function getFileExistsAttribute(): bool
    {
        if (!$this->file_path) return false;
        
        $paths = [
            storage_path('app/' . $this->file_path),
            storage_path('app/public/' . str_replace('public/', '', $this->file_path)),
            storage_path('app/public/' . $this->file_path)
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return true;
            }
        }
        
        return false;
    }
}
