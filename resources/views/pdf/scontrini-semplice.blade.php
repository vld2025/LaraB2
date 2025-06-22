<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Scontrini</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .scontrino { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Test Scontrini - {{ $user->name }}</h1>
        <p>{{ $mese }} {{ $anno }}</p>
        <p>Totale: {{ $totaleFiles }} scontrini</p>
    </div>

    @foreach($scontrini as $index => $scontrino)
        <div class="scontrino">
            <h3>Scontrino #{{ $index + 1 }}</h3>
            <p><strong>Data:</strong> {{ $scontrino->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>File:</strong> {{ basename($scontrino->foto_scontrino) }}</p>
            <p><strong>Tipo:</strong> {{ strtoupper($scontrino->extension) }}</p>
            
            @if($scontrino->note)
                <p><strong>Note:</strong> {{ $scontrino->note }}</p>
            @endif
        </div>
    @endforeach

    <p style="text-align: center; margin-top: 30px;">
        Documento generato il {{ $dataGenerazione }}
    </p>
</body>
</html>
