<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'commessa_id',
        'data',
        'ore',
        'km',
        'auto_privata',
        'festivo',
        'notturno',
        'trasferta',
        'dati_originali',
        'dati_cliente',
        'fatturato',
        'data_fatturazione',
        'numero_fattura',
    ];

    protected $casts = [
        'data' => 'date',
        'ore' => 'decimal:2',
        'auto_privata' => 'boolean',
        'festivo' => 'boolean',
        'notturno' => 'boolean',
        'trasferta' => 'boolean',
        'fatturato' => 'boolean',
        'data_fatturazione' => 'date',
        'dati_originali' => 'array',
        'dati_cliente' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($report) {
            // Salva i dati originali
            $report->dati_originali = $report->only([
                'ore', 'km', 'auto_privata', 'festivo', 'notturno', 'trasferta'
            ]);
            
            // Inizializza dati_cliente con gli stessi valori
            $report->dati_cliente = $report->dati_originali;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commessa(): BelongsTo
    {
        return $this->belongsTo(Commessa::class);
    }

    public function spese(): HasMany
    {
        return $this->hasMany(Spesa::class);
    }

    public function scopeNonFatturati($query)
    {
        return $query->where('fatturato', false);
    }

    public function scopeFatturati($query)
    {
        return $query->where('fatturato', true);
    }

    public function scopeDelMese($query, $mese, $anno)
    {
        return $query->whereMonth('data', $mese)->whereYear('data', $anno);
    }
}
