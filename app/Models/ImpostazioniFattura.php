<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpostazioniFattura extends Model
{
    use HasFactory;

    protected $table = 'impostazioni_fattura';

    protected $fillable = [
        'cliente_id',
        'costo_orario',
        'costo_km',
        'costo_pranzo',
        'costo_pernottamento',
        'giorno_fatturazione',
        'email_destinatario',
        'invia_automatico',
    ];

    protected $casts = [
        'costo_orario' => 'decimal:2',
        'costo_km' => 'decimal:2',
        'costo_pranzo' => 'decimal:2',
        'costo_pernottamento' => 'decimal:2',
        'giorno_fatturazione' => 'integer',
        'invia_automatico' => 'boolean',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Ottieni le impostazioni per un cliente o quelle di default
     */
    public static function getForCliente($clienteId): self
    {
        // Prima cerca impostazioni specifiche del cliente
        $impostazioni = self::where('cliente_id', $clienteId)->first();
        
        if ($impostazioni) {
            return $impostazioni;
        }
        
        // Se non esistono, usa quelle di default (cliente_id = null)
        $default = self::whereNull('cliente_id')->first();
        
        if ($default) {
            return $default;
        }
        
        // Se non esistono neanche quelle di default, crea e ritorna valori di default
        return new self([
            'costo_orario' => 80,
            'costo_km' => 0.70,
            'costo_pranzo' => 25,
            'costo_pernottamento' => 120,
            'giorno_fatturazione' => 22,
            'invia_automatico' => false,
        ]);
    }

    /**
     * Crea o aggiorna le impostazioni di default
     */
    public static function creaDefault(): self
    {
        return self::updateOrCreate(
            ['cliente_id' => null],
            [
                'costo_orario' => 80,
                'costo_km' => 0.70,
                'costo_pranzo' => 25,
                'costo_pernottamento' => 120,
                'giorno_fatturazione' => 22,
                'invia_automatico' => false,
            ]
        );
    }
}
