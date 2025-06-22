<?php

namespace App\Policies;

use App\Models\SpesaExtra;
use App\Models\User;

class SpesaExtraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'user']);
    }

    public function view(User $user, SpesaExtra $spesaExtra): bool
    {
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }
        
        return $user->id === $spesaExtra->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'user']);
    }

    public function update(User $user, SpesaExtra $spesaExtra): bool
    {
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }
        
        return $user->id === $spesaExtra->user_id;
    }

    public function delete(User $user, SpesaExtra $spesaExtra): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        
        if ($user->hasRole('manager')) {
            return true;
        }
        
        return $user->id === $spesaExtra->user_id;
    }

    public function restore(User $user, SpesaExtra $spesaExtra): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function forceDelete(User $user, SpesaExtra $spesaExtra): bool
    {
        return $user->hasRole('admin');
    }
}
