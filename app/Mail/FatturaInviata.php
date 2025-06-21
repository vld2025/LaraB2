<?php

namespace App\Mail;

use App\Models\Fattura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class FatturaInviata extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Fattura $fattura
    ) {
        $this->fattura->load('cliente');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Fattura ' . $this->fattura->numero_fattura . ' - VLD Service GmbH',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.fattura-inviata',
            with: [
                'fattura' => $this->fattura,
                'cliente' => $this->fattura->cliente,
            ],
        );
    }

    public function attachments(): array
    {
        // Array dei mesi in italiano
        $mesi = [
            1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo',
            4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
            7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre',
            10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
        ];

        // Genera il PDF della fattura
        $pdf = Pdf::loadView('pdf.fattura', [
            'fattura' => $this->fattura,
            'mesi' => $mesi
        ]);

        $pdf->setPaper('A4', 'portrait');

        // Nome del file PDF
        $fileName = 'Fattura_' . str_replace(['/', ' '], ['_', '_'], $this->fattura->numero_fattura) . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $fileName)
                ->withMime('application/pdf'),
        ];
    }
}
