<?php

namespace App\Policies;

use App\Models\Commessa;
use App\Models\User;

class CommessaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function view(User $user, Commessa $commessa): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, Commessa $commessa): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, Commessa $commessa): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Commessa $commessa): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Commessa $commessa): bool
    {
        return $user->hasRole('admin');
    }
}
