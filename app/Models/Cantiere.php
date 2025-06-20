<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cantiere extends Model
{
    use HasFactory;

    protected $table = 'cantieri';

    protected $fillable = [
        'cliente_id',
        'nome',
        'codice',
        'indirizzo',
        'cap',
        'citta',
        'provincia',
        'nazione',
        'note',
        'attivo',
    ];

    protected $casts = [
        'attivo' => 'boolean',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function commesse(): HasMany
    {
        return $this->hasMany(Commessa::class);
    }
}
