<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    /**
     * Check if user can access Filament panel
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->hasRole('admin') || $this->hasRole('manager') || $this->hasRole('user');
    }
}
