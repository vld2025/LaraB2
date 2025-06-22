<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Riepilogo Scontrini - Tutti gli Utenti</h2>
        
        <p>Gentile Destinatario,</p>
        
        <p>In allegato troverete il riepilogo completo degli scontrini per:</p>
        
        <ul>
            <li><strong>Periodo:</strong> {{ $mese }} {{ $anno }}</li>
            <li><strong>Totale Utenti:</strong> {{ $totaleUtenti }}</li>
            <li><strong>Totale Scontrini:</strong> {{ $totaleFiles }}</li>
            <li><strong>Data Generazione:</strong> {{ $dataGenerazione }}</li>
        </ul>
        
        <p>Il documento PDF allegato contiene tutti gli scontrini caricati da tutti gli utenti nel periodo specificato, organizzati per utente.</p>
        
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
        
        <p style="font-size: 12px; color: #666;">
            Questo messaggio è stato generato automaticamente dal sistema VLD Service.<br>
            Per qualsiasi chiarimento, contattare l'amministrazione.
        </p>
    </div>
</body>
</html>
