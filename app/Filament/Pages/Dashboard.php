<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }
        
        // Solo admin e manager vedono il Manager Dashboard
        if ($user->hasRole(['admin', 'manager'])) {
            return [
                \App\Filament\Widgets\ManagerDashboardWidget::class,
            ];
        }
        
        // User normale non ha widget speciali
        return [];
    }
}
