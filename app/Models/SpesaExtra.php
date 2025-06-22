<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpesaExtra extends Model
{
    use HasFactory;

    protected $table = 'spese_extra';

    protected $fillable = [
        'user_id',
        'commessa_id',
        'data',
        'descrizione',
        'importo',
        'foto_path',
        'importo_ai',
        'risposta_ai',
        'verificato',
        'fatturato',
        'data_fatturazione',
        'numero_fattura',
    ];

    protected $casts = [
        'data' => 'date',
        'importo' => 'decimal:2',
        'importo_ai' => 'decimal:2',
        'verificato' => 'boolean',
        'fatturato' => 'boolean',
        'data_fatturazione' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commessa(): BelongsTo
    {
        return $this->belongsTo(Commessa::class);
    }

    public function scopeNonFatturate($query)
    {
        return $query->where('fatturato', false);
    }

    public function scopeFatturate($query)
    {
        return $query->where('fatturato', true);
    }

    public function scopeVerificate($query)
    {
        return $query->where('verificato', true);
    }

    public function scopeDelMese($query, $mese, $anno)
    {
        return $query->whereMonth('data', $mese)->whereYear('data', $anno);
    }
}
