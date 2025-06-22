<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        // Tutti gli utenti autenticati possono accedere alla dashboard
        return auth()->check();
    }

    protected function getHeaderWidgets(): array
    {
        $user = auth()->user();
        
        // User normali vedono solo widget personali
        if ($user && !$user->hasRole(['admin', 'manager'])) {
            return [
                // Widget user personalizzati
            ];
        }
        
        // Admin/Manager vedono tutti i widget
        return parent::getHeaderWidgets();
    }
}
