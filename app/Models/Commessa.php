<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commessa extends Model
{
    use HasFactory;

    protected $table = 'commesse';

    protected $fillable = [
        'cantiere_id',
        'nome',
        'codice',
        'descrizione',
        'data_inizio',
        'data_fine',
        'budget',
        'attiva',
    ];

    protected $casts = [
        'attiva' => 'boolean',
        'data_inizio' => 'date',
        'data_fine' => 'date',
        'budget' => 'decimal:2',
    ];

    public function cantiere(): BelongsTo
    {
        return $this->belongsTo(Cantiere::class);
    }

    public function cliente()
    {
        return $this->hasOneThrough(Cliente::class, Cantiere::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function getTotaleOre()
    {
        return $this->reports()->sum('ore');
    }
}
