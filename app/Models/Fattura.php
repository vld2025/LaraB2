<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fattura extends Model
{
    use HasFactory;

    protected $table = 'fatture';

    protected $fillable = [
        'numero_fattura',
        'data_fattura',
        'data_scadenza',
        'stato',
        'cliente_id',
        'mese',
        'anno',
        'subtotale',
        'sconto',
        'totale_pre_iva',
        'aliquota_iva',
        'importo_iva',
        'totale_finale',
        'ore_totali',
        'importo_manodopera',
        'giorni_trasferta',
        'importo_trasferte',
        'km_totali',
        'importo_km',
        'importo_spese_extra',
        'data_invio_email',
        'email_destinatario',
        'note',
    ];

    protected $casts = [
        'data_fattura' => 'date',
        'data_scadenza' => 'date',
        'data_invio_email' => 'datetime',
        'subtotale' => 'decimal:2',
        'sconto' => 'decimal:2',
        'totale_pre_iva' => 'decimal:2',
        'aliquota_iva' => 'decimal:2',
        'importo_iva' => 'decimal:2',
        'totale_finale' => 'decimal:2',
        'ore_totali' => 'decimal:2',
        'importo_manodopera' => 'decimal:2',
        'giorni_trasferta' => 'decimal:2',
        'importo_trasferte' => 'decimal:2',
        'km_totali' => 'decimal:2',
        'importo_km' => 'decimal:2',
        'importo_spese_extra' => 'decimal:2',
    ];

    // Relazioni
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'fattura_id');
    }

    // Metodi helper
    public function calcolaImporti(): void
    {
        $this->subtotale = $this->importo_manodopera + 
                          $this->importo_trasferte + 
                          $this->importo_km + 
                          $this->importo_spese_extra;
        
        $this->totale_pre_iva = $this->subtotale - $this->sconto;
        $this->importo_iva = $this->totale_pre_iva * ($this->aliquota_iva / 100);
        $this->totale_finale = $this->totale_pre_iva + $this->importo_iva;
    }

    public function generaNumeroFattura(): string
    {
        $ultimaFattura = self::where('anno', $this->anno)
                            ->orderBy('numero_fattura', 'desc')
                            ->first();
        
        if ($ultimaFattura) {
            $numero = intval(explode('-', $ultimaFattura->numero_fattura)[1]) + 1;
        } else {
            $numero = 1;
        }
        
        return "FATTURA {$numero}-{$this->anno}";
    }
}
