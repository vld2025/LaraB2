<?php

namespace App\Filament\Resources\DocumentoResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Documento;
use App\Models\CategoriaDocumento;

class DocumentiStats extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $isManager = $user->hasRole(['manager', 'admin']);
        
        $documentiQuery = Documento::query()
            ->when(!$isManager, function ($query) use ($user) {
                return $query->where('documentabile_id', $user->id);
            });
        
        $stats = [];
        
        // Stat per le 3 cartelle principali
        $bustePaga = (clone $documentiQuery)
            ->whereHas('categoria', fn($q) => $q->where('slug', 'buste-paga'))
            ->count();
            
        $stats[] = Stat::make('Buste Paga', $bustePaga)
            ->description('Cedolini caricati')
            ->color('success')
            ->icon('heroicon-o-banknotes');
            
        $personali = (clone $documentiQuery)
            ->whereHas('categoria', fn($q) => $q->where('slug', 'documenti-personali'))
            ->count();
            
        $stats[] = Stat::make('Documenti Personali', $personali)
            ->description('Documenti personali')
            ->color('info')
            ->icon('heroicon-o-identification');
            
        $aziendali = (clone $documentiQuery)
            ->whereHas('categoria', fn($q) => $q->where('slug', 'documenti-aziendali'))
            ->count();
            
        $stats[] = Stat::make('Documenti Aziendali', $aziendali)
            ->description('Documenti aziendali')
            ->color('warning')
            ->icon('heroicon-o-building-office');
        
        // Documenti in scadenza
        $inScadenza = (clone $documentiQuery)->inScadenza()->count();
        if ($inScadenza > 0) {
            $stats[] = Stat::make('In Scadenza', $inScadenza)
                ->description('Scadono entro 30 giorni')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle');
        }
        
        return $stats;
    }
}
