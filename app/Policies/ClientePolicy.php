<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('admin');
    }
}
