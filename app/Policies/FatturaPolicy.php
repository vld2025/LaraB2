<?php

namespace App\Policies;

use App\Models\Fattura;
use App\Models\User;

class FatturaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function view(User $user, Fattura $fattura): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, Fattura $fattura): bool
    {
        if ($fattura->inviata) {
            return false;
        }
        
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, Fattura $fattura): bool
    {
        return $user->hasRole('admin') && !$fattura->inviata;
    }

    public function restore(User $user, Fattura $fattura): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Fattura $fattura): bool
    {
        return $user->hasRole('admin');
    }
}
