<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spesa extends Model
{
    use HasFactory;

    protected $table = 'spese';

    protected $fillable = [
        'user_id',
        'mese',
        'anno',
        'foto_scontrino',
        'note',
        // Campi vecchi opzionali
        'report_id',
        'data',
        'tipo',
        'importo',
        'fatturato',
        'data_fatturazione',
        'numero_fattura',
    ];

    protected $attributes = [
        'report_id' => null,
        'data' => null,
        'tipo' => null,
        'importo' => null,
        'fatturato' => false,
    ];

    protected $casts = [
        'data' => 'date',
        'data_fatturazione' => 'date',
        'importo' => 'decimal:2',
        'fatturato' => 'boolean',
        'mese' => 'integer',
        'anno' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    // Scope per filtrare per mese/anno
    public function scopeDelMese($query, $mese, $anno)
    {
        return $query->where('mese', $mese)->where('anno', $anno);
    }

    // Helper per ottenere il nome del file
    public function getNomeFileAttribute()
    {
        if (!$this->foto_scontrino) {
            return null;
        }
        return basename($this->foto_scontrino);
    }
}
