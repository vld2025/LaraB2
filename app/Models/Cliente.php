<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clienti';

    protected $fillable = [
        'nome',
        'codice',
        'codice_fiscale',
        'partita_iva',
        'indirizzo',
        'cap',
        'citta',
        'provincia',
        'nazione',
        'telefono',
        'email',
        'note',
        'attivo',
    ];

    protected $casts = [
        'attivo' => 'boolean',
    ];

    public function cantieri(): HasMany
    {
        return $this->hasMany(Cantiere::class);
    }

    public function commesse()
    {
        return $this->hasManyThrough(Commessa::class, Cantiere::class);
    }
}
