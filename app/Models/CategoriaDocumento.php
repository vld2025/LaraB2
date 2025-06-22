<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CategoriaDocumento extends Model
{
    use HasFactory;

    protected $table = 'categorie_documenti';

    protected $fillable = [
        'nome',
        'slug',
        'descrizione',
        'colore',
        'tipo_accesso',
        'icona',
        'ordine',
        'attiva'
    ];

    protected $casts = [
        'attiva' => 'boolean'
    ];

    public function documenti(): HasMany
    {
        return $this->hasMany(Documento::class, 'categoria_id');
    }

    public function scopeAttive(Builder $query): Builder
    {
        return $query->where('attiva', true);
    }

    public function scopeAccessibili(Builder $query): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Se è manager/admin, vede tutte le categorie
        if ($user->hasRole(['manager', 'admin'])) {
            return $query;
        }

        // User normale vede:
        // - user_upload (può caricare nei suoi documenti personali)
        // - buste_paga (può visualizzare le sue buste paga)
        // - manager_upload (può visualizzare documenti aziendali assegnati)
        return $query->whereIn('tipo_accesso', ['user_upload', 'buste_paga', 'manager_upload']);
    }
}
