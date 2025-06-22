<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scontrini {{ $user->name }} - {{ $mese }} {{ $anno }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .info-table .label {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 30%;
        }
        .scontrino-item {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            break-inside: avoid;
        }
        .scontrino-header {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .scontrino-numero {
            font-weight: bold;
            font-size: 16px;
            float: left;
        }
        .scontrino-data {
            color: #666;
            font-size: 14px;
            float: right;
        }
        .clearfix {
            clear: both;
        }
        .scontrino-image {
            text-align: center;
            margin: 15px 0;
        }
        .scontrino-image img {
            max-width: 90%;
            max-height: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .pdf-placeholder {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 20px;
            text-align: center;
            border-radius: 4px;
            border: 2px dashed #dc2626;
            margin: 15px 0;
        }
        .note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">VLD Service GmbH</div>
        <h1>Riepilogo Scontrini</h1>
        <p>{{ $mese }} {{ $anno }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Dipendente:</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td class="label">Email:</td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <td class="label">Periodo:</td>
            <td>{{ $mese }} {{ $anno }}</td>
        </tr>
        <tr>
            <td class="label">Totale Scontrini:</td>
            <td>{{ $totaleFiles }}</td>
        </tr>
        <tr>
            <td class="label">Data Generazione:</td>
            <td>{{ $dataGenerazione }}</td>
        </tr>
    </table>

    @foreach($scontrini as $index => $scontrino)
        <div class="scontrino-item">
            <div class="scontrino-header">
                <div class="scontrino-numero">Scontrino #{{ $index + 1 }}</div>
                <div class="scontrino-data">{{ $scontrino->created_at->format('d/m/Y H:i') }}</div>
                <div class="clearfix"></div>
            </div>

            <div class="scontrino-image">
                @if($scontrino->base64_image)
                    <img src="{{ $scontrino->base64_image }}" alt="Scontrino {{ $index + 1 }}">
                @elseif($scontrino->extension === 'pdf')
                    <div class="pdf-placeholder">
                        <h3>📄 Documento PDF</h3>
                        <p><strong>{{ basename($scontrino->foto_scontrino) }}</strong></p>
                        <p>Il file PDF originale è disponibile separatamente</p>
                    </div>
                @else
                    <div class="pdf-placeholder">
                        <p>❌ File non visualizzabile o non trovato</p>
                        <p><strong>{{ basename($scontrino->foto_scontrino) }}</strong></p>
                        @if(!$scontrino->file_exists)
                            <p style="font-size: 12px;">File non trovato nel sistema</p>
                        @endif
                    </div>
                @endif
            </div>

            @if($scontrino->note)
                <div class="note">
                    <strong>Note:</strong> {{ $scontrino->note }}
                </div>
            @endif
        </div>

        @if(($index + 1) % 2 == 0 && $index != count($scontrini) - 1)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="footer">
        <p>Documento generato automaticamente dal sistema VLD Service</p>
        <p>Per qualsiasi chiarimento, contattare l'amministrazione</p>
    </div>
</body>
</html>
