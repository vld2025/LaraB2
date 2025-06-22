<?php

namespace App\Http\Controllers;

use App\Models\Spesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class ScontriniPdfController extends Controller
{
    private const MAX_PDF_SIZE_MB = 25;
    private const MAX_PDF_SIZE_BYTES = self::MAX_PDF_SIZE_MB * 1024 * 1024;

    public function generaPdfMensile(Request $request)
    {
        try {
            $mese = $request->input('mese_pdf');
            $anno = $request->input('anno_pdf');
            $email = $request->input('email_manager');

            $spese = Spesa::where('mese', $mese)
                ->where('anno', $anno)
                ->whereNotNull('foto_scontrino')
                ->with('user')
                ->orderBy('created_at')
                ->get();

            if ($spese->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "Nessuno scontrino trovato per {$mese}/{$anno}"
                ]);
            }

            // Dividi gli scontrini in gruppi per dimensione
            $gruppiScontrini = $this->dividiScontriniPerDimensione($spese);
            
            \Log::info("Creati " . count($gruppiScontrini) . " gruppi di scontrini");
            
            $emailInviate = 0;
            foreach ($gruppiScontrini as $indiceGruppo => $gruppoSpese) {
                \Log::info("Gruppo " . ($indiceGruppo + 1) . ": " . $gruppoSpese->count() . " scontrini (IDs: " . $gruppoSpese->pluck('id')->implode(', ') . ")");
                
                \Log::info("Elaborando gruppo " . ($indiceGruppo + 1) . " con scontrini IDs: " . $gruppoSpese->pluck("id")->implode(", "));
                $pdfContent = $this->creaPdfUnificatoConGhostscript(
                    $gruppoSpese, 
                    $mese, 
                    $anno, 
                    $indiceGruppo + 1, 
                    count($gruppiScontrini)
                );
                
                if ($pdfContent) {
                    $this->inviaEmailConPdf(
                        $email, 
                        $pdfContent, 
                        $mese, 
                        $anno, 
                        $gruppoSpese->count(), 
                        $indiceGruppo + 1, 
                        count($gruppiScontrini)
                    );
                    $emailInviate++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "✅ {$emailInviate} PDF inviati a {$email}! Totale: {$spese->count()} scontrini divisi in {$emailInviate} email (max 25MB)"
            ]);

        } catch (\Exception $e) {
            \Log::error("Errore generazione PDF: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Errore: ' . $e->getMessage()
            ]);
        }
    }

    private function dividiScontriniPerDimensione($spese)
    {
        $gruppi = [];
        $gruppoCorrente = [];
        $dimensioneCorrente = 0;
        
        \Log::info("Dividendo " . $spese->count() . " scontrini in gruppi");

        foreach ($spese as $index => $spesa) {
            $pathCompleto = storage_path('app/public/' . $spesa->foto_scontrino);
                \Log::info("=== PROCESSING FILE ===");
                \Log::info("ID: " . $spesa->id . " - File originale: " . $pathCompleto);
                \Log::info("File esiste: " . (file_exists($pathCompleto) ? "SI" : "NO"));
                if(file_exists($pathCompleto)) {
                    \Log::info("Dimensione file originale: " . filesize($pathCompleto) . " bytes");
                    \Log::info("Hash file originale: " . substr(md5_file($pathCompleto), 0, 8));
                }
            $dimensioneFile = file_exists($pathCompleto) ? filesize($pathCompleto) : 0;
            
            // Stima: file originale + overhead PDF (fattore 2 per essere sicuri)
            $dimensioneStimata = $dimensioneFile * 2;

            // Se aggiungendo questo file supereremmo 25MB E non è il primo del gruppo
            if (!empty($gruppoCorrente) && ($dimensioneCorrente + $dimensioneStimata) > self::MAX_PDF_SIZE_BYTES) {
                // Chiudi il gruppo corrente
                $gruppi[] = collect($gruppoCorrente);
                \Log::info("Gruppo " . count($gruppi) . " completato con " . count($gruppoCorrente) . " scontrini (dimensione stimata: " . number_format($dimensioneCorrente / 1024 / 1024, 2) . " MB)");
                
                // Inizia nuovo gruppo
                $gruppoCorrente = [];
                $dimensioneCorrente = 0;
            }

            $gruppoCorrente[] = $spesa;
            $dimensioneCorrente += $dimensioneStimata;
            
            \Log::debug("Aggiunto scontrino ID {$spesa->id} al gruppo (dimensione: " . number_format($dimensioneFile / 1024, 2) . " KB)");
        }

        // Aggiungi l'ultimo gruppo se non vuoto
        if (!empty($gruppoCorrente)) {
            $gruppi[] = collect($gruppoCorrente);
            \Log::info("Ultimo gruppo " . count($gruppi) . " con " . count($gruppoCorrente) . " scontrini");
        }

        \Log::info("Totale gruppi creati: " . count($gruppi));
        return $gruppi;
    }

    private function creaPdfUnificatoConGhostscript($spese, $mese, $anno, $parteNumero = 1, $totaliParti = 1)
    {
        try {
            \Log::info("Creando PDF parte {$parteNumero}/{$totaliParti} con " . $spese->count() . " scontrini");
            \Log::info("DETTAGLIO SPESE RICEVUTE: " . $spese->pluck("id")->implode(", "));
            \Log::info("TIPO VARIABILE SPESE: " . get_class($spese));
            \Log::info("COUNT SPESE: " . $spese->count());
            
            $tempDir = storage_path('app/temp_pdf_' . uniqid());
            mkdir($tempDir, 0755, true);
            
            $pdfFiles = [];
            $counter = 0;

            // Crea pagina di copertina
            $copertinaPdf = $tempDir . "/copertina_parte_{$parteNumero}.pdf";
            //             $this->creaPaginaCopertina($copertinaPdf, $mese, $anno, $spese->count(), $parteNumero, $totaliParti);
            //             $pdfFiles[] = $copertinaPdf;

            foreach ($spese as $spesa) {
                $counter++;
                $pathCompleto = storage_path('app/public/' . $spesa->foto_scontrino);
                \Log::info("=== PROCESSING FILE ===");
                \Log::info("ID: " . $spesa->id . " - File originale: " . $pathCompleto);
                \Log::info("File esiste: " . (file_exists($pathCompleto) ? "SI" : "NO"));
                if(file_exists($pathCompleto)) {
                    \Log::info("Dimensione file originale: " . filesize($pathCompleto) . " bytes");
                    \Log::info("Hash file originale: " . substr(md5_file($pathCompleto), 0, 8));
                }
                $estensione = strtolower(pathinfo($spesa->foto_scontrino, PATHINFO_EXTENSION));
                
                $outputPdf = $tempDir . "/parte_{$parteNumero}_doc_{$counter}_id_{$spesa->id}.pdf";

                \Log::info("=== FOREACH STEP === Elaborando scontrino ID: " . $spesa->id . " Nome file: " . $spesa->foto_scontrino);
                \Log::debug("Elaborando scontrino ID {$spesa->id}: {$spesa->foto_scontrino}");

                if ($estensione === 'pdf') {
                    copy($pathCompleto, $outputPdf);
                } else {
                    // Converti immagine in PDF
                    $command = "convert " . escapeshellarg($pathCompleto) . " " . escapeshellarg($outputPdf) . " 2>/dev/null";
                    $convertReturn = 0;
                    exec($command, $output, $convertReturn);
                    
                    if ($convertReturn !== 0 || !file_exists($outputPdf)) {
                        \Log::warning("Conversione fallita per: " . $pathCompleto);
                        continue;
                    }
                }
                
                if (file_exists($outputPdf)) {
                    \Log::info("File PDF temp creato: " . $outputPdf);
                    \Log::info("Dimensione PDF temp: " . filesize($outputPdf) . " bytes");
                    \Log::info("Hash PDF temp: " . substr(md5_file($outputPdf), 0, 8));
                    $pdfFiles[] = $outputPdf;
                    \Log::info("File aggiunto alla lista: " . basename($outputPdf) . " (dimensione: " . filesize($outputPdf) . " bytes)");
                    \Log::debug("File PDF creato: " . basename($outputPdf));
                }
            }

            if (count($pdfFiles) < 1) {
                throw new \Exception("Nessun PDF generato per la parte {$parteNumero}");
            }

            // Unisci tutti i PDF
            \Log::info("=== PRIMA UNIONE FINALE ===");
            \Log::info("Numero file da unire: " . count($pdfFiles));
            foreach($pdfFiles as $idx => $file) {
                if(file_exists($file)) {
                    \Log::info("File " . $idx . ": " . basename($file) . " - " . filesize($file) . " bytes - Hash: " . substr(md5_file($file), 0, 8));
                } else {
                    \Log::error("File mancante: " . $file);
                }
            }
            \Log::info("=== UNIONE PDF ===");
            \Log::info("File da unire: " . implode(", ", array_map("basename", $pdfFiles)));
            $outputFinal = $tempDir . "/scontrini_unificato_parte_{$parteNumero}.pdf";
            $filesString = implode(' ', array_map('escapeshellarg', $pdfFiles));
            
            \Log::info("PRIMA DI EXEC GS - outputFinal: " . $outputFinal);
            \Log::info("PRIMA DI EXEC GS - filesString: " . $filesString);
            exec("gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=" . escapeshellarg($outputFinal) . " " . $filesString, $output, $returnVar);

            \Log::info("DOPO EXEC GS - returnVar: " . $returnVar);
            \Log::info("DOPO EXEC GS - output: " . implode(", ", $output ?? []));
            if (!file_exists($outputFinal)) {
                throw new \Exception("Unione PDF fallita per parte {$parteNumero}");
            }

            $pdfContent = file_get_contents($outputFinal);
            $dimensioneMB = number_format(strlen($pdfContent) / 1024 / 1024, 2);

            // Pulizia
            array_map('unlink', $pdfFiles);
            unlink($outputFinal);
            rmdir($tempDir);

            \Log::info("PDF parte {$parteNumero}/{$totaliParti} creato: {$dimensioneMB} MB con " . $spese->count() . " scontrini");

            return $pdfContent;

        } catch (\Exception $e) {
            \Log::error("Errore creazione PDF parte {$parteNumero}: " . $e->getMessage());
            return false;
        }
    }

    private function creaPaginaCopertina($outputPath, $mese, $anno, $numScontrini, $parteNumero, $totaliParti)
    {
        $mese_nome = \Carbon\Carbon::create($anno, $mese, 1)->locale("it")->monthName;
        
        // Crea un'immagine per la copertina
        $width = 600;
        $height = 800;
        $image = imagecreatetruecolor($width, $height);
        
        // Colori
        $white = imagecolorallocate($image, 255, 255, 255);
        $blue = imagecolorallocate($image, 37, 99, 235);
        $gray = imagecolorallocate($image, 100, 100, 100);
        
        imagefill($image, 0, 0, $white);
        
        // Testo
        $font_size = 5;
        imagestring($image, $font_size, 50, 100, "VLD Service GmbH", $blue);
        imagestring($image, $font_size, 50, 150, "Scontrini Unificati", $blue);
        imagestring($image, 4, 50, 200, "{$mese_nome} {$anno}", $gray);
        
        if ($totaliParti > 1) {
            imagestring($image, 4, 50, 250, "PARTE {$parteNumero} di {$totaliParti}", $blue);
            imagestring($image, 3, 50, 300, "Scontrini in questa parte: {$numScontrini}", $gray);
        } else {
            imagestring($image, 3, 50, 250, "Totale scontrini: {$numScontrini}", $gray);
        }
        
        imagestring($image, 3, 50, 350, "Generato: " . now()->format('d/m/Y H:i'), $gray);
        
        // Salva come JPEG temporaneo
        $tempJpeg = str_replace('.pdf', '.jpg', $outputPath);
        imagejpeg($image, $tempJpeg, 90);
        imagedestroy($image);
        
        // Converti in PDF
        $command = "convert " . escapeshellarg($tempJpeg) . " " . escapeshellarg($outputPath);
        unlink($tempJpeg);
    }

    private function inviaEmailConPdf($email, $pdfContent, $mese, $anno, $numScontrini, $parteNumero, $totaliParti)
    {
        $dimensioneMB = number_format(strlen($pdfContent) / 1024 / 1024, 2);
        
        $oggetto = $totaliParti > 1 
            ? "PDF Scontrini {$mese}/{$anno} - Parte {$parteNumero}/{$totaliParti} - VLD Service"
            : "PDF Unificato Scontrini {$mese}/{$anno} - VLD Service";
            
        $nomeFile = $totaliParti > 1
            ? "scontrini_{$anno}_{$mese}_parte_{$parteNumero}.pdf"
            : "scontrini_unificato_{$anno}_{$mese}.pdf";

        $corpo = $totaliParti > 1
            ? "PDF Scontrini per {$mese}/{$anno} - PARTE {$parteNumero} di {$totaliParti}\n\n" .
              "Questa email contiene {$numScontrini} scontrini.\n" .
              "Dimensione PDF: {$dimensioneMB} MB\n\n" .
              "Le altre parti sono inviate in email separate."
            : "PDF Scontrini UNIFICATO per {$mese}/{$anno}\n\n" .
              "Totale: {$numScontrini} scontrini\n" .
              "Dimensione: {$dimensioneMB} MB";

        Mail::raw($corpo, function ($message) use ($email, $pdfContent, $oggetto, $nomeFile) {
            $message->to($email)
                ->subject($oggetto)
                ->attachData($pdfContent, $nomeFile, ['mime' => 'application/pdf']);
        });
        
        \Log::info("Email inviata: {$oggetto} (dimensione: {$dimensioneMB} MB)");
    }
}
