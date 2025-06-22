<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Fattura;
use App\Models\Report;
use App\Models\ImpostazioniFattura;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GeneraFatture extends Command
{
    protected $signature = 'fatture:genera {--mese=} {--anno=} {--force}';
    protected $description = 'Genera fatture automatiche per il mese precedente';

    public function handle(): int
    {
        $mese = $this->option('mese') ?? Carbon::now()->subMonth()->month;
        $anno = $this->option('anno') ?? Carbon::now()->subMonth()->year;
        $force = $this->option('force');

        $this->info("Generazione fatture per {$mese}/{$anno}");

        // Se --force, cancella fatture esistenti
        if ($force) {
            $fattureEsistenti = Fattura::where('mese', $mese)->where('anno', $anno)->count();
            if ($fattureEsistenti > 0) {
                $this->info("Cancellando {$fattureEsistenti} fatture esistenti...");
                
                // Resetta i report fatturati
                Report::where('fatturato', true)
                    ->whereMonth('data', $mese)
                    ->whereYear('data', $anno)
                    ->update([
                        'fatturato' => false,
                        'fattura_id' => null,
                        'data_fatturazione' => null,
                        'numero_fattura' => null
                    ]);
                
                // Cancella le fatture
                Fattura::where('mese', $mese)->where('anno', $anno)->delete();
            }
        } else {
            // Verifica se esistono già fatture per questo periodo
            if (Fattura::where('mese', $mese)->where('anno', $anno)->exists()) {
                $this->error("Fatture per {$mese}/{$anno} già esistenti. Usa --force per rigenerare.");
                return 1;
            }
        }

        // Ottieni impostazioni fatturazione
        $impostazioni = ImpostazioniFattura::first();
        if (!$impostazioni) {
            $this->error('Impostazioni fatturazione non trovate.');
            return 1;
        }

        // Ottieni report non fatturati del periodo
        $reports = Report::where('fatturato', false)
            ->whereMonth('data', $mese)
            ->whereYear('data', $anno)
            ->with(['user', 'commessa.cantiere.cliente', 'spese'])
            ->get();

        if ($reports->isEmpty()) {
            $this->info('Nessun report da fatturare trovato.');
            return 0;
        }

        // Raggruppa per cliente
        $reportPerCliente = $reports->groupBy(function($report) {
            return $report->commessa->cantiere->cliente->id;
        });

        $fattureCreate = 0;

        DB::transaction(function () use ($reportPerCliente, $mese, $anno, $impostazioni, &$fattureCreate) {
            foreach ($reportPerCliente as $clienteId => $reportsCliente) {
                $cliente = $reportsCliente->first()->commessa->cantiere->cliente;

                $this->info("Generando fattura per: {$cliente->nome}");

                $fattura = $this->creaFattura($cliente, $reportsCliente, $mese, $anno, $impostazioni);

                // Associa i report alla fattura
                foreach ($reportsCliente as $report) {
                    $report->update([
                        'fattura_id' => $fattura->id,
                        'fatturato' => true,
                        'data_fatturazione' => $fattura->data_fattura,
                        'numero_fattura' => $fattura->numero_fattura
                    ]);
                }

                $fattureCreate++;
                $this->info("Fattura {$fattura->numero_fattura} creata per €{$fattura->totale_finale}");
            }
        });

        $this->info("Processo completato. Create {$fattureCreate} fatture.");
        return 0;
    }

    private function creaFattura(Cliente $cliente, $reports, int $mese, int $anno, $impostazioni): Fattura
    {
        $fattura = new Fattura([
            'cliente_id' => $cliente->id,
            'mese' => $mese,
            'anno' => $anno,
            'data_fattura' => Carbon::create($anno, $mese, 22),
            'data_scadenza' => Carbon::create($anno, $mese, 22)->addDays(30),
            'stato' => 'bozza',
            'aliquota_iva' => 8.1,
        ]);

        // Calcola totali usando dati_cliente (versione per fatturazione)
        $ore_totali = 0;
        $km_totali = 0;
        $giorni_trasferta = 0;

        foreach ($reports as $report) {
            $datiBilling = $report->getDataForBilling();
            $ore_totali += $datiBilling['ore'] ?? 0;
            $km_totali += $datiBilling['km'] ?? 0;
            if ($datiBilling['trasferta'] ?? false) {
                $giorni_trasferta++;
            }
        }

        // Calcola spese extra
        $spese_extra = 0;
        foreach ($reports as $report) {
            $spese_extra += $report->spese->sum('importo');
        }

        // Calcola importi
        $fattura->ore_totali = $ore_totali;
        $fattura->importo_manodopera = $ore_totali * $impostazioni->costo_orario;

        $fattura->giorni_trasferta = $giorni_trasferta;
        $fattura->importo_trasferte = $giorni_trasferta * $impostazioni->costo_pranzo;

        $fattura->km_totali = $km_totali;
        $fattura->importo_km = $km_totali * $impostazioni->costo_km;

        $fattura->importo_spese_extra = $spese_extra;

        // Genera numero fattura
        $fattura->numero_fattura = $fattura->generaNumeroFattura();

        // Calcola importi finali
        $fattura->calcolaImporti();
        $fattura->save();

        return $fattura;
    }
}
