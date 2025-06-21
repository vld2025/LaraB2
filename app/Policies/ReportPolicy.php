<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'user']);
    }

    public function view(User $user, Report $report): bool
    {
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }
        
        return $user->id === $report->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'user']);
    }

    public function update(User $user, Report $report): bool
    {
        if ($user->hasRole(['admin', 'manager'])) {
            return !$report->fatturato;
        }

        return $user->id === $report->user_id && !$report->fatturato;
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->hasRole('admin') && !$report->fatturato;
    }

    public function restore(User $user, Report $report): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return $user->hasRole('admin');
    }
}
