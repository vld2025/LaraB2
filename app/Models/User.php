<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'telefono',
        'indirizzo',
        'cap',
        'citta',
        'provincia',
        'taglia_giacca',
        'taglia_pantaloni',
        'taglia_maglietta',
        'taglia_scarpe',
        'note_abbigliamento',
        'ore_settimanali',
        'costo_orario',
        'attivo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'attivo' => 'boolean',
            'ore_settimanali' => 'integer',
            'costo_orario' => 'decimal:2',
        ];
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->attivo && $this->hasRole(['admin', 'manager', 'user']);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function spese(): HasMany
    {
        return $this->hasMany(Spesa::class);
    }

    public function speseExtra(): HasMany
    {
        return $this->hasMany(SpesaExtra::class);
    }

    public function documenti(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentabile');
    }

    public function documentiCaricati(): HasMany
    {
        return $this->hasMany(Documento::class, 'caricato_da');
    }

    public function getOreNormaliMese($mese, $anno): float
    {
        $giorniLavorativi = $this->calcolaGiorniLavorativi($mese, $anno);
        return ($this->ore_settimanali / 5) * $giorniLavorativi;
    }

    private function calcolaGiorniLavorativi($mese, $anno): int
    {
        $giorni = 0;
        $ultimoGiorno = date('t', mktime(0, 0, 0, $mese, 1, $anno));
        
        for ($giorno = 1; $giorno <= $ultimoGiorno; $giorno++) {
            $timestamp = mktime(0, 0, 0, $mese, $giorno, $anno);
            $giornoSettimana = date('N', $timestamp);
            
            // Escludi sabato (6) e domenica (7)
            if ($giornoSettimana < 6) {
                $giorni++;
            }
        }
        
        // TODO: Sottrarre festivi del Canton Ticino
        return $giorni;
    }

    public function canEditReport(Report $report): bool
    {
        // Admin e manager possono sempre modificare
        if ($this->hasRole(['admin', 'manager'])) {
            return !$report->fatturato;
        }
        
        // User può modificare solo i propri report non fatturati
        return $report->user_id === $this->id && !$report->fatturato;
    }
}
