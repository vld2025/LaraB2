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

        // Se l'utente non è autenticato, ritorna array vuoto
        if (!$user) {
            return [];
        }

        $meseCorrente = now()->month;
        $annoCorrente = now()->year;

        // Calcola ore del mese corrente usando SEMPRE i dati originali dell'utente
        $reports = Report::where('user_id', $user->id)
            ->whereMonth('data', $meseCorrente)
            ->whereYear('data', $annoCorrente)
            ->get();

        $oreMese = 0;
        $kmMese = 0;
        foreach ($reports as $report) {
            // User vede sempre le sue statistiche basate sui dati originali
            $datiOriginali = $report->getDataForUser();
            $oreMese += $datiOriginali['ore'] ?? 0;
            // Conta km solo se auto privata è true nei dati originali
            if ($datiOriginali['auto_privata'] ?? false) {
                $kmMese += $datiOriginali['km'] ?? 0;
            }
        }

        // Calcola ore normali del mese
        $oreNormali = $user->getOreNormaliMese($meseCorrente, $annoCorrente);

        // Calcola differenza
        $differenza = $oreMese - $oreNormali;
        $differenzaFormattata = ($differenza >= 0 ? '+' : '') . number_format($differenza, 1);

        // Statistiche base per tutti
        $stats = [
            Stat::make('Ore ' . now()->format('F'), number_format($oreMese, 1) . ' h')
                ->description('Su ' . number_format($oreNormali, 0) . ' h previste')
                ->descriptionIcon($differenza >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($differenza >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Differenza Ore', $differenzaFormattata . ' h')
                ->description('Bilancio mensile (dati originali)')
                ->color($differenza >= 0 ? 'success' : 'warning'),

            Stat::make('Km Auto Privata', number_format($kmMese) . ' km')
                ->description('Mese corrente (dati originali)')
                ->icon('heroicon-o-truck'),
        ];

        // Solo admin e manager vedono "Report da Fatturare"
        if ($user->hasRole(['admin', 'manager'])) {
            $reportNonFatturati = Report::where('user_id', $user->id)
                ->where('fatturato', false)
                ->count();

            $stats[] = Stat::make('Report da Fatturare', $reportNonFatturati)
                ->description('In attesa')
                ->color($reportNonFatturati > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-document-text');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->hasRole(['user', 'manager', 'admin']);
    }
}
