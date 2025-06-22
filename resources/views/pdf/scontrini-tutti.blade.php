<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Scontrini - {{ $mese_nome }} {{ $anno }}</title>
    <style>
        @page { margin: 15mm; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            color: #2563eb;
            margin: 0;
        }
        .summary {
            background: #f8fafc;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #2563eb;
        }
        .spesa-item {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 10px;
            page-break-inside: avoid;
        }
        .spesa-header {
            background: #f3f4f6;
            padding: 5px;
            margin: -10px -10px 10px -10px;
            font-weight: bold;
        }
        .spesa-image {
            text-align: center;
            margin: 10px 0;
        }
        .spesa-image img {
            max-width: 280px;
            max-height: 200px;
            border: 1px solid #ddd;
        }
        .spesa-details {
            font-size: 9px;
            color: #666;
            margin-bottom: 10px;
        }
        .file-info {
            background: #fff3cd;
            padding: 8px;
            margin: 8px 0;
            border-radius: 4px;
            border: 1px solid #ffeaa7;
        }
        .pdf-notice {
            background: #fecaca;
            padding: 8px;
            margin: 8px 0;
            border-radius: 4px;
            border: 1px solid #f87171;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>VLD Service GmbH - Scontrini</h1>
        <p>{{ $mese_nome }} {{ $anno }}</p>
    </div>

    <div class="summary">
        <strong>Riepilogo:</strong> {{ $numero_scontrini }} scontrini<br>
        Generato il: {{ $data_generazione }}
    </div>

    @foreach($spese as $spesa)
    <div class="spesa-item">
        <div class="spesa-header">
            Scontrino #{{ $spesa->id }} - {{ $spesa->user->name }}
        </div>
        
        <div class="spesa-details">
            <strong>Data caricamento:</strong> {{ $spesa->created_at->format('d/m/Y H:i') }}<br>
            <strong>Periodo:</strong> {{ $mese_nome }} {{ $anno }}<br>
            @if($spesa->note)
                <strong>Note:</strong> {{ $spesa->note }}<br>
            @endif
        </div>

        <div class="file-info">
            <strong>📎 File:</strong> {{ basename($spesa->foto_scontrino) }}<br>
            <strong>Tipo:</strong> {{ strtoupper(pathinfo($spesa->foto_scontrino, PATHINFO_EXTENSION)) }}
        </div>

        @php
            $estensione = strtolower(pathinfo($spesa->foto_scontrino, PATHINFO_EXTENSION));
            $pathCompleto = storage_path('app/public/' . $spesa->foto_scontrino);
            $fileExists = file_exists($pathCompleto);
        @endphp

        @if($fileExists && in_array($estensione, ['jpg', 'jpeg']))
            @php
                try {
                    // Usa GD per ridimensionare
                    $originalImage = imagecreatefromjpeg($pathCompleto);
                    $width = imagesx($originalImage);
                    $height = imagesy($originalImage);
                    
                    // Ridimensiona a max 280px larghezza
                    $maxWidth = 280;
                    if ($width > $maxWidth) {
                        $newWidth = $maxWidth;
                        $newHeight = ($height / $width) * $newWidth;
                    } else {
                        $newWidth = $width;
                        $newHeight = $height;
                    }
                    
                    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    
                    ob_start();
                    imagejpeg($resizedImage, null, 70);
                    $imageData = ob_get_contents();
                    ob_end_clean();
                    
                    $base64 = base64_encode($imageData);
                    $hasImage = true;
                    
                    imagedestroy($originalImage);
                    imagedestroy($resizedImage);
                } catch (\Exception $e) {
                    $hasImage = false;
                    $errorMsg = $e->getMessage();
                }
            @endphp
            
            @if($hasImage)
                <div class="spesa-image">
                    <img src="data:image/jpeg;base64,{{ $base64 }}" alt="Scontrino {{ $spesa->id }}">
                </div>
            @else
                <div class="pdf-notice">
                    <strong>❌ ERRORE CARICAMENTO IMMAGINE</strong><br>
                    {{ $errorMsg ?? 'Errore sconosciuto' }}
                </div>
            @endif
        @elseif($fileExists && $estensione === 'png')
            <div class="pdf-notice">
                <strong>🖼️ IMMAGINE PNG</strong><br>
                {{ basename($spesa->foto_scontrino) }}<br>
                <em>PNG non supportato nel PDF (solo JPG)</em>
            </div>
        @elseif($fileExists && $estensione === 'pdf')
            <div class="pdf-notice">
                <strong>📄 DOCUMENTO PDF</strong><br>
                Il file PDF originale è disponibile separatamente<br>
                <em>{{ basename($spesa->foto_scontrino) }}</em>
            </div>
        @else
            <div class="pdf-notice">
                <strong>❌ FILE NON TROVATO</strong><br>
                Il file potrebbe essere stato spostato o eliminato
            </div>
        @endif
    </div>
    @endforeach
</body>
</html>
