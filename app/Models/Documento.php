<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
    ];

    protected $casts = [
        'interno' => 'boolean',
        'dimensione' => 'integer',
    ];

    public function documentabile(): MorphTo
    {
        return $this->morphTo();
    }

    public function utente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caricato_da');
    }

    public function scopePublici($query)
    {
        return $query->where('interno', false);
    }

    public function scopeInterni($query)
    {
        return $query->where('interno', true);
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

    public function getFormattedSize(): string
    {
        $bytes = $this->dimensione;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
