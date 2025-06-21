<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Report;
use App\Models\Fattura;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ManagerDashboardWidget extends Widget
{
    protected static string $view = 'filament.widgets.manager-dashboard-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        return $user->hasRole(['admin', 'manager']);
    }

    public function getViewData(): array
    {
        $meseCorrente = Carbon::now()->month;
        $annoCorrente = Carbon::now()->year;
        $mesePassato = Carbon::now()->subMonth()->month;
        $annoPassato = Carbon::now()->subMonth()->year;

        // Ottieni tutti gli utenti con ruolo user
        $utenti = User::role('user')->with(['reports' => function($query) use ($meseCorrente, $annoCorrente) {
            $query->whereMonth('data', $meseCorrente)
                  ->whereYear('data', $annoCorrente);
        }])->get();

        // Statistiche generali
        $statistiche = [
            'totale_utenti' => User::role('user')->count(),
            'ore_totali_mese' => Report::whereMonth('data', $meseCorrente)
                                     ->whereYear('data', $annoCorrente)
                                     ->sum('ore'),
            'fatture_mese' => Fattura::where('mese', $mesePassato)
                                    ->where('anno', $annoPassato)
                                    ->count(),
            'fatturato_mese' => Fattura::where('mese', $mesePassato)
                                      ->where('anno', $annoPassato)
                                      ->sum('totale_finale'),
            'report_non_fatturati' => Report::whereNull('fattura_id')->count(),
        ];

        // Dati per ogni utente
        $datiUtenti = $utenti->map(function ($utente) use ($meseCorrente, $annoCorrente) {
            $reportsMese = $utente->reports;
            
            return [
                'id' => $utente->id,
                'nome' => $utente->name,
                'email' => $utente->email,
                'ore_mese' => $reportsMese->sum('ore'),
                'giorni_lavorati' => $reportsMese->groupBy('data')->count(),
                'km_totali' => $reportsMese->sum('km'),
                'ultimo_report' => $reportsMese->sortByDesc('data')->first()?->data,
                'media_ore_giorno' => $reportsMese->count() > 0 ? 
                    round($reportsMese->sum('ore') / $reportsMese->groupBy('data')->count(), 1) : 0,
            ];
        });

        return [
            'statistiche' => $statistiche,
            'utenti' => $datiUtenti,
            'mese_corrente' => Carbon::now()->format('m/Y'),
        ];
    }
}
