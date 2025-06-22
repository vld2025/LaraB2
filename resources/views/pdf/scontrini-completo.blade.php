<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Scontrini Completi - {{ $mese_nome }} {{ $anno }}</title>
    <style>
        @page { 
            margin: 10mm; 
            size: A4;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
        }
        .header h1 {
            font-size: 14px;
            color: #2563eb;
            margin: 0;
        }
        .summary {
            background: #f8fafc;
            padding: 8px;
            margin-bottom: 12px;
            border-left: 3px solid #2563eb;
            font-size: 8px;
        }
        .scontrino-item {
            border: 1px solid #ddd;
            margin-bottom: 12px;
            padding: 8px;
            page-break-inside: avoid;
        }
        .scontrino-header {
            background: #f3f4f6;
            padding: 4px;
            margin: -8px -8px 8px -8px;
            font-weight: bold;
            font-size: 10px;
        }
        .scontrino-image {
            text-align: center;
            margin: 8px 0;
        }
        .scontrino-image img {
            max-width: 100%;
            max-height: 400px;
            border: 1px solid #ddd;
        }
        .scontrino-details {
            font-size: 8px;
            color: #666;
            margin-bottom: 8px;
        }
        .file-info {
            background: #fff3cd;
            padding: 6px;
            margin: 6px 0;
            border-radius: 3px;
            border: 1px solid #ffeaa7;
            font-size: 8px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>VLD Service GmbH - Scontrini Completi</h1>
        <p>{{ $mese_nome }} {{ $anno }} - Documento Unificato</p>
    </div>

    <div class="summary">
        <strong>Riepilogo Completo:</strong> {{ $numero_scontrini }} scontrini<br>
        📸 Immagini: {{ $stats['immagini'] ?? 0 }} | 📄 PDF: {{ $stats['pdf'] ?? 0 }} | ❌ Errori: {{ $stats['errori'] ?? 0 }}<br>
        Generato il: {{ $data_generazione }}
    </div>

    @foreach($spese as $index => $spesa)
    <div class="scontrino-item">
        <div class="scontrino-header">
            Scontrino #{{ $spesa->id }} ({{ $index + 1 }}/{{ count($spese) }}) - {{ $spesa->user->name }}
        </div>
        
        <div class="scontrino-details">
            <strong>Data:</strong> {{ $spesa->created_at->format('d/m/Y H:i') }} |
            <strong>Periodo:</strong> {{ $mese_nome }} {{ $anno }}
            @if($spesa->note)
                | <strong>Note:</strong> {{ $spesa->note }}
            @endif
        </div>

        <div class="file-info">
            📎 <strong>{{ basename($spesa->foto_scontrino) }}</strong> 
            ({{ strtoupper(pathinfo($spesa->foto_scontrino, PATHINFO_EXTENSION)) }})
        </div>

        @if(isset($spesa->immagine_convertita))
            <div class="scontrino-image">
                <img src="data:{{ $spesa->mime_type }};base64,{{ $spesa->immagine_convertita }}" 
                     alt="Scontrino {{ $spesa->id }}">
            </div>
        @else
            <div style="text-align: center; padding: 20px; background: #fee; border: 1px dashed #f87171;">
                ❌ <strong>Impossibile convertire il file</strong>
            </div>
        @endif
    </div>

    @if(($index + 1) % 2 == 0 && $index != count($spese) - 1)
        <div class="page-break"></div>
    @endif
    @endforeach

    <div style="margin-top: 20px; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        Documento generato automaticamente - VLD Service GmbH<br>
        Tutti i scontrini sono stati convertiti in formato immagine per una visualizzazione ottimale
    </div>
</body>
</html>
