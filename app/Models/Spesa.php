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
        'report_id',
        'data',
        'tipo',
        'importo',
        'note',
        'fatturato',
        'data_fatturazione',
        'numero_fattura',
    ];

    protected $casts = [
        'data' => 'date',
        'importo' => 'decimal:2',
        'fatturato' => 'boolean',
        'data_fatturazione' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function scopeNonFatturate($query)
    {
        return $query->where('fatturato', false);
    }

    public function scopeFatturate($query)
    {
        return $query->where('fatturato', true);
    }

    public function scopeDelMese($query, $mese, $anno)
    {
        return $query->whereMonth('data', $mese)->whereYear('data', $anno);
    }

    public function scopePranzi($query)
    {
        return $query->where('tipo', 'pranzo');
    }

    public function scopePernottamenti($query)
    {
        return $query->where('tipo', 'pernottamento');
    }
}
