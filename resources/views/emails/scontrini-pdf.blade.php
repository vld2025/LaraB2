<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Scontrini Mensili - {{ $user->name }}</h2>
        
        <p>Gentile Manager,</p>
        
        <p>In allegato troverete il riepilogo degli scontrini per:</p>
        
        <ul>
            <li><strong>Dipendente:</strong> {{ $user->name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>Periodo:</strong> {{ $mese }} {{ $anno }}</li>
            <li><strong>Totale Scontrini:</strong> {{ $totaleFiles }}</li>
            <li><strong>Data Generazione:</strong> {{ $dataGenerazione }}</li>
        </ul>
        
        <p>Il documento PDF allegato contiene tutti gli scontrini caricati nel periodo specificato.</p>
        
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
        
        <p style="font-size: 12px; color: #666;">
            Questo messaggio è stato generato automaticamente dal sistema VLD Service.<br>
            Per qualsiasi chiarimento, contattare l'amministrazione.
        </p>
    </div>
</body>
</html>
