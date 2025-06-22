<?php

namespace App\Policies;

use App\Models\Cantiere;
use App\Models\User;

class CantierePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function view(User $user, Cantiere $cantiere): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, Cantiere $cantiere): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, Cantiere $cantiere): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Cantiere $cantiere): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Cantiere $cantiere): bool
    {
        return $user->hasRole('admin');
    }
}
