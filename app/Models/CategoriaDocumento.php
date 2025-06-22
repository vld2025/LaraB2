<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        'attiva' => 'boolean',
        'ordine' => 'integer'
    ];

    public function documenti()
    {
        return $this->hasMany(Documento::class, 'categoria_id');
    }

    public function scopeAttive($query)
    {
        return $query->where('attiva', true)->orderBy('ordine');
    }

    // Scope per categorie accessibili all'utente corrente
    public function scopeAccessibili($query)
    {
        $user = Auth::user();
        
        if (!$user) {
            return $query->where('id', 0);
        }

        // Manager/Admin vedono tutte le categorie
        if ($user->hasRole(['manager', 'admin'])) {
            return $query;
        }

        // User normale vede solo categorie user_upload e buste_paga
        return $query->whereIn('tipo_accesso', ['user_upload', 'buste_paga']);
    }

    // Verifica se l'utente può caricare in questa categoria
    public function canUpload($user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        // Manager/Admin possono caricare ovunque
        if ($user->hasRole(['manager', 'admin'])) {
            return true;
        }

        // User può caricare solo nelle categorie user_upload
        return $this->tipo_accesso === 'user_upload';
    }

    // Verifica se l'utente può vedere questa categoria
    public function canView($user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        // Manager/Admin vedono tutto
        if ($user->hasRole(['manager', 'admin'])) {
            return true;
        }

        // User vede user_upload e buste_paga
        return in_array($this->tipo_accesso, ['user_upload', 'buste_paga']);
    }
}
