<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class OreStatistiche extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        $meseCorrente = now()->month;
        $annoCorrente = now()->year;
        
        // Calcola ore del mese corrente
        $oreMese = Report::where('user_id', $user->id)
            ->whereMonth('data', $meseCorrente)
            ->whereYear('data', $annoCorrente)
            ->sum('ore');
            
        // Calcola ore normali del mese
        $oreNormali = $user->getOreNormaliMese($meseCorrente, $annoCorrente);
        
        // Calcola differenza
        $differenza = $oreMese - $oreNormali;
        $differenzaFormattata = ($differenza >= 0 ? '+' : '') . number_format($differenza, 1);
        
        // Calcola km del mese
        $kmMese = Report::where('user_id', $user->id)
            ->whereMonth('data', $meseCorrente)
            ->whereYear('data', $annoCorrente)
            ->where('auto_privata', true)
            ->sum('km');
            
        // Calcola report non fatturati
        $reportNonFatturati = Report::where('user_id', $user->id)
            ->where('fatturato', false)
            ->count();
        
        return [
            Stat::make('Ore ' . now()->format('F'), number_format($oreMese, 1) . ' h')
                ->description('Su ' . number_format($oreNormali, 0) . ' h previste')
                ->descriptionIcon($differenza >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($differenza >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5]),
                
            Stat::make('Differenza Ore', $differenzaFormattata . ' h')
                ->description('Bilancio mensile')
                ->color($differenza >= 0 ? 'success' : 'warning'),
                
            Stat::make('Km Auto Privata', number_format($kmMese) . ' km')
                ->description('Mese corrente')
                ->icon('heroicon-o-truck'),
                
            Stat::make('Report da Fatturare', $reportNonFatturati)
                ->description('In attesa')
                ->color($reportNonFatturati > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        return Auth::user()->hasRole(['user', 'manager', 'admin']);
    }
}
