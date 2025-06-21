<?php

namespace App\Policies;

use App\Models\Spesa;
use App\Models\User;

class SpesaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'user']);
    }

    public function view(User $user, Spesa $spesa): bool
    {
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }
        
        return $user->id === $spesa->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'user']);
    }

    public function update(User $user, Spesa $spesa): bool
    {
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }
        
        return $user->id === $spesa->user_id;
    }

    public function delete(User $user, Spesa $spesa): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        
        if ($user->hasRole('manager')) {
            return true;
        }
        
        return $user->id === $spesa->user_id;
    }

    public function restore(User $user, Spesa $spesa): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function forceDelete(User $user, Spesa $spesa): bool
    {
        return $user->hasRole('admin');
    }
}
