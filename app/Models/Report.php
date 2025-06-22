<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

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
        'fattura_id',
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
            // Salva i dati originali (versione utente)
            $report->dati_originali = $report->only([
                'ore', 'km', 'auto_privata', 'festivo', 'notturno', 'trasferta'
            ]);
            // Inizializza dati_cliente con gli stessi valori (versione per fatturazione)
            $report->dati_cliente = $report->dati_originali;
        });

        static::updating(function ($report) {
            $user = Auth::user();
            
            if ($user && $user->hasRole(['admin', 'manager'])) {
                // Admin/Manager modifica: aggiorna solo dati_cliente
                $report->dati_cliente = $report->only([
                    'ore', 'km', 'auto_privata', 'festivo', 'notturno', 'trasferta'
                ]);
                // I dati_originali rimangono immutabili
            } else {
                // User modifica: aggiorna sia originali che cliente
                $nuoviDati = $report->only([
                    'ore', 'km', 'auto_privata', 'festivo', 'notturno', 'trasferta'
                ]);
                $report->dati_originali = $nuoviDati;
                $report->dati_cliente = $nuoviDati;
            }
        });
    }

    // Relazioni
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commessa(): BelongsTo
    {
        return $this->belongsTo(Commessa::class);
    }

    public function fattura(): BelongsTo
    {
        return $this->belongsTo(Fattura::class);
    }

    public function spese(): HasMany
    {
        return $this->hasMany(Spesa::class);
    }

    // Scopes
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

    // Metodi per ottenere i dati corretti in base al ruolo
    public function getDataForUser()
    {
        // User vede sempre i suoi dati originali
        return $this->dati_originali ?? [
            'ore' => $this->ore,
            'km' => $this->km,
            'auto_privata' => $this->auto_privata,
            'festivo' => $this->festivo,
            'notturno' => $this->notturno,
            'trasferta' => $this->trasferta
        ];
    }

    public function getDataForManager()
    {
        // Admin/Manager vede i dati cliente (modificati)
        return $this->dati_cliente ?? [
            'ore' => $this->ore,
            'km' => $this->km,
            'auto_privata' => $this->auto_privata,
            'festivo' => $this->festivo,
            'notturno' => $this->notturno,
            'trasferta' => $this->trasferta
        ];
    }

    public function getDataForBilling()
    {
        // Fatturazione usa sempre dati_cliente
        return $this->getDataForManager();
    }

    // Attributi dinamici in base al ruolo
    public function getOreDisplayAttribute()
    {
        $user = Auth::user();
        if (!$user) return $this->ore;

        if ($user->hasRole(['admin', 'manager'])) {
            $data = $this->getDataForManager();
            return $data['ore'] ?? $this->ore;
        } else {
            $data = $this->getDataForUser();
            return $data['ore'] ?? $this->ore;
        }
    }

    public function getKmDisplayAttribute()
    {
        $user = Auth::user();
        if (!$user) return $this->km;

        if ($user->hasRole(['admin', 'manager'])) {
            $data = $this->getDataForManager();
            return $data['km'] ?? $this->km;
        } else {
            $data = $this->getDataForUser();
            return $data['km'] ?? $this->km;
        }
    }

    // Metodo per verificare se il report è stato modificato da admin/manager
    public function isModifiedByManager()
    {
        return $this->dati_originali != $this->dati_cliente;
    }
}
