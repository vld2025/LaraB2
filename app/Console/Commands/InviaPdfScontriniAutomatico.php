<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ScontriniPdfController;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InviaPdfScontriniAutomatico extends Command
{
    protected $signature = 'scontrini:invia-automatico';
    protected $description = 'Invia automaticamente il PDF scontrini unificato';

    public function handle()
    {
        $impostazioni = \DB::table('impostazioni_fattura')->first();
        
        if (!$impostazioni || !$impostazioni->automazione_pdf_attiva) {
            $this->info('Automazione PDF disattivata');
            return;
        }

        $oggi = Carbon::now();
        $oraCorrente = $oggi->format('H:i');
        $giornoCorrente = $oggi->day;

        // Verifica se è il giorno e ora giusti
        if ($giornoCorrente != $impostazioni->giorno_automazione_pdf) {
            $this->info("Non è il giorno giusto. Oggi: {$giornoCorrente}, Configurato: {$impostazioni->giorno_automazione_pdf}");
            return;
        }

        $oraTarget = Carbon::parse($impostazioni->ora_automazione_pdf)->format('H:i');
        if ($oraCorrente != $oraTarget) {
            $this->info("Non è l'ora giusta. Ora: {$oraCorrente}, Configurato: {$oraTarget}");
            return;
        }

        // Determina il periodo
        if ($impostazioni->mese_automazione_pdf === 'previous') {
            $data = $oggi->subMonth();
        } else {
            $data = $oggi;
        }

        $mese = $data->month;
        $anno = $data->year;

        $this->info("Invio PDF per {$mese}/{$anno} a {$impostazioni->email_automazione_pdf}");

        try {
            $controller = new ScontriniPdfController();
            $request = new Request([
                'mese_pdf' => $mese,
                'anno_pdf' => $anno,
                'email_manager' => $impostazioni->email_automazione_pdf
            ]);

            $result = $controller->generaPdfMensile($request);
            $response = $result->getData(true);

            if ($response['success']) {
                $this->info('✅ ' . $response['message']);
                
                // Log dell'invio
                \Log::info("PDF Scontrini inviato automaticamente", [
                    'mese' => $mese,
                    'anno' => $anno,
                    'email' => $impostazioni->email_automazione_pdf,
                    'timestamp' => now()
                ]);
            } else {
                $this->error('❌ ' . $response['message']);
            }

        } catch (\Exception $e) {
            $this->error('Errore: ' . $e->getMessage());
            \Log::error("Errore invio automatico PDF scontrini: " . $e->getMessage());
        }
    }
}
