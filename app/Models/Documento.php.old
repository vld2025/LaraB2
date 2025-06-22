<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documenti';

    protected $fillable = [
        'documentabile_type',
        'documentabile_id', 
        'nome',
        'descrizione',
        'file_path',
        'mime_type',
        'dimensione',
        'interno',
        'caricato_da',
        'categoria',
        'data_scadenza',
        'notifica_scadenza',
        'giorni_preavviso_scadenza',
        'versione',
        'documento_padre_id',
        'hash_file',
        'backup_nas_completato',
        'backup_cloud_completato',
        'ultima_sincronizzazione',
        'metadati',
        'is_active'
    ];

    protected $casts = [
        'interno' => 'boolean',
        'dimensione' => 'integer',
        'data_scadenza' => 'date',
        'notifica_scadenza' => 'boolean',
        'backup_nas_completato' => 'boolean',
        'backup_cloud_completato' => 'boolean',
        'is_active' => 'boolean',
        'metadati' => 'array',
        'ultima_sincronizzazione' => 'datetime'
    ];

    // Relazioni
    public function documentabile(): MorphTo
    {
        return $this->morphTo();
    }

    public function utente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caricato_da');
    }

    public function documentoPadre(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'documento_padre_id');
    }

    public function versioni(): HasMany
    {
        return $this->hasMany(Documento::class, 'documento_padre_id');
    }

    // Scope utili
    public function scopeAttivi($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInScadenza($query, $giorni = 30)
    {
        return $query->where('notifica_scadenza', true)
                    ->whereDate('data_scadenza', '<=', Carbon::now()->addDays($giorni))
                    ->whereDate('data_scadenza', '>=', Carbon::now());
    }

    public function scopeScaduti($query)
    {
        return $query->where('notifica_scadenza', true)
                    ->whereDate('data_scadenza', '<', Carbon::now());
    }

    public function scopePerCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePublici($query)
    {
        return $query->where('interno', false);
    }

    public function scopeInterni($query)
    {
        return $query->where('interno', true);
    }

    // Accessor e Mutator
    public function getDimensioneFileUmanaAttribute(): string
    {
        $bytes = $this->dimensione;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getGiorniAllaScadenzaAttribute(): ?int
    {
        if (!$this->data_scadenza) {
            return null;
        }
        
        return Carbon::now()->diffInDays($this->data_scadenza, false);
    }

    public function getIsInScadenzaAttribute(): bool
    {
        if (!$this->data_scadenza || !$this->notifica_scadenza) {
            return false;
        }
        
        $giorni = $this->giorni_alla_scadenza;
        return $giorni !== null && $giorni <= $this->giorni_preavviso_scadenza && $giorni >= 0;
    }

    public function getIsScadutoAttribute(): bool
    {
        if (!$this->data_scadenza || !$this->notifica_scadenza) {
            return false;
        }
        
        return $this->data_scadenza < Carbon::now();
    }

    // Metodi utili
    public function calcolaHashFile(string $pathCompleto): string
    {
        return hash_file('sha256', $pathCompleto);
    }

    public function verificaIntegrita(): bool
    {
        $pathCompleto = storage_path('app/' . $this->file_path);
        
        if (!file_exists($pathCompleto)) {
            return false;
        }
        
        return $this->hash_file === $this->calcolaHashFile($pathCompleto);
    }

    public function creaNuovaVersione(array $datiNuovaVersione): self
    {
        $versioneAttuale = $this->versione;
        $numeroVersione = (float) $versioneAttuale + 0.1;
        
        $nuovoDocumento = static::create(array_merge($datiNuovaVersione, [
            'documento_padre_id' => $this->documento_padre_id ?? $this->id,
            'versione' => number_format($numeroVersione, 1),
            'caricato_da' => $this->caricato_da,
            'categoria' => $this->categoria
        ]));
        
        // Disattiva la versione precedente
        $this->update(['is_active' => false]);
        
        return $nuovoDocumento;
    }

    public function canView(User $user): bool
    {
        // Documenti pubblici: tutti possono vedere
        if (!$this->interno) {
            return true;
        }
        
        // Documenti interni: solo manager e admin
        return $user->hasRole(['manager', 'admin']);
    }

    public static function getCategorieDisponibili(): array
    {
        return [
            'personali' => 'Documenti Personali',
            'lavoro' => 'Documenti di Lavoro', 
            'certificazioni' => 'Certificazioni',
            'contratti' => 'Contratti',
            'fiscali' => 'Documenti Fiscali',
            'assicurazioni' => 'Assicurazioni',
            'altro' => 'Altro'
        ];
    }

    public function getFormattedSize(): string
    {
        return $this->dimensione_file_umana;
    }
}
