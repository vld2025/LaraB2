<?php

namespace App\Http\Controllers;

use App\Models\Fattura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\FatturaInviata;

class FatturaController extends Controller
{
    public function downloadPdf(Fattura $fattura)
    {
        // Array dei mesi in italiano
        $mesi = [
            1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo',
            4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
            7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre',
            10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
        ];

        // Carica la fattura con le relazioni necessarie
        $fattura->load('cliente');

        // Genera il PDF
        $pdf = Pdf::loadView('pdf.fattura', [
            'fattura' => $fattura,
            'mesi' => $mesi
        ]);

        // Imposta il formato A4 e l'orientamento
        $pdf->setPaper('A4', 'portrait');

        // Nome del file
        $fileName = 'Fattura_' . str_replace(['/', ' '], ['_', '_'], $fattura->numero_fattura) . '.pdf';

        // Scarica il PDF
        return $pdf->download($fileName);
    }

    public function viewPdf(Fattura $fattura)
    {
        // Array dei mesi in italiano
        $mesi = [
            1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo',
            4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
            7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre',
            10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
        ];

        // Carica la fattura con le relazioni necessarie
        $fattura->load('cliente');

        // Genera il PDF
        $pdf = Pdf::loadView('pdf.fattura', [
            'fattura' => $fattura,
            'mesi' => $mesi
        ]);

        // Imposta il formato A4 e l'orientamento
        $pdf->setPaper('A4', 'portrait');

        // Visualizza il PDF nel browser
        return $pdf->stream('Fattura_' . str_replace(['/', ' '], ['_', '_'], $fattura->numero_fattura) . '.pdf');
    }

    public function inviaEmail(Fattura $fattura)
    {
        try {
            // Verifica che ci sia un email destinatario
            $emailDestinatario = $fattura->email_destinatario ?? $fattura->cliente->email;
            
            if (!$emailDestinatario) {
                return back()->with('error', 'Nessun indirizzo email specificato per il cliente.');
            }

            // Invia l'email
            Mail::to($emailDestinatario)->send(new FatturaInviata($fattura));

            // Aggiorna la fattura
            $fattura->update([
                'data_invio_email' => now(),
                'email_destinatario' => $emailDestinatario,
                'stato' => 'inviata'
            ]);

            return back()->with('success', 'Email inviata con successo a: ' . $emailDestinatario);

        } catch (\Exception $e) {
            return back()->with('error', 'Errore nell\'invio email: ' . $e->getMessage());
        }
    }
}
