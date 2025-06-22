<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $fattura->numero_fattura }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 40px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .company-info h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .company-info p {
            margin: 2px 0;
        }
        .customer-info {
            float: right;
            width: 250px;
            margin-top: -60px;
        }
        .invoice-details {
            clear: both;
            margin: 40px 0;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f5f5f5;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        .items-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        .total-final {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .bank-info {
            clear: both;
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .bank-info h4 {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- Header Azienda -->
    <div class="header">
        <div class="company-info">
            <h2>VLD Service GmbH</h2>
            <p>Via Dufour 4</p>
            <p>6900 Lugano</p>
            <p>IVA Numero: CHE-465.976.345 IVA</p>
        </div>

        <!-- Info Cliente -->
        <div class="customer-info">
            <strong>{{ $fattura->cliente->nome }}</strong><br>
            {{ $fattura->cliente->indirizzo }}<br>
            {{ $fattura->cliente->citta }} {{ $fattura->cliente->cap }}
        </div>
    </div>

    <!-- Dettagli Fattura -->
    <div class="invoice-details">
        <p>Lugano, {{ $fattura->data_fattura->format('d') }} {{ $mesi[$fattura->data_fattura->format('n')] }} {{ $fattura->data_fattura->format('Y') }}</p>
        
        <div class="invoice-number">
            {{ $fattura->numero_fattura }}
        </div>
    </div>

    <!-- Tabella Voci -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%">DESCRIZIONE</th>
                <th style="width: 20%" class="text-right">Prezzo</th>
                <th style="width: 15%" class="text-center">Quantità</th>
                <th style="width: 15%" class="text-right">Totale</th>
            </tr>
        </thead>
        <tbody>
            @if($fattura->ore_totali > 0)
            <tr>
                <td>Lavori commesse varie - Periodo {{ sprintf('%02d/%d', $fattura->mese, $fattura->anno) }}</td>
                <td class="text-right">{{ number_format($fattura->importo_manodopera / $fattura->ore_totali, 0) }}</td>
                <td class="text-center">{{ number_format($fattura->ore_totali, 1) }}</td>
                <td class="text-right">{{ number_format($fattura->importo_manodopera, 2) }}</td>
            </tr>
            @endif

            @if($fattura->giorni_trasferta > 0)
            <tr>
                <td>Spese di trasferta</td>
                <td class="text-right">{{ number_format($fattura->importo_trasferte / $fattura->giorni_trasferta, 0) }}</td>
                <td class="text-center">{{ number_format($fattura->giorni_trasferta, 0) }}</td>
                <td class="text-right">{{ number_format($fattura->importo_trasferte, 2) }}</td>
            </tr>
            @endif

            @if($fattura->km_totali > 0)
            <tr>
                <td>Auto</td>
                <td class="text-right">{{ number_format($fattura->importo_km / $fattura->km_totali, 2) }}</td>
                <td class="text-center">{{ number_format($fattura->km_totali, 0) }}</td>
                <td class="text-right">{{ number_format($fattura->importo_km, 2) }}</td>
            </tr>
            @endif

            @if($fattura->importo_spese_extra > 0)
            <tr>
                <td>Spese Extra</td>
                <td class="text-right">{{ number_format($fattura->importo_spese_extra, 2) }}</td>
                <td class="text-center">1</td>
                <td class="text-right">{{ number_format($fattura->importo_spese_extra, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Totali -->
    <div class="totals">
        <table>
            @if($fattura->sconto > 0)
            <tr>
                <td>SCONTO</td>
                <td class="text-right">{{ number_format($fattura->sconto, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>TOTALE</strong></td>
                <td class="text-right"><strong>CHF {{ number_format($fattura->totale_pre_iva, 2) }}</strong></td>
            </tr>
            <tr>
                <td>IVA {{ number_format($fattura->aliquota_iva, 1) }}%</td>
                <td class="text-right">{{ number_format($fattura->importo_iva, 2) }}</td>
            </tr>
            <tr class="total-final">
                <td><strong>Totale dovuto IVA {{ number_format($fattura->aliquota_iva, 1) }}% compresa</strong></td>
                <td class="text-right"><strong>CHF {{ number_format($fattura->totale_finale, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Coordinate Bancarie -->
    <div class="bank-info">
        <h4>Coordinate bancarie</h4>
        <p>Banca Raiffeisen Morbio-Vacallo</p>
        <p>IBAN: CH1980808008277820973</p>
        <p>Scadenza: 30 gg data fattura</p>
    </div>

    @if($fattura->note)
    <div style="margin-top: 30px;">
        <h4>Note:</h4>
        <p>{{ $fattura->note }}</p>
    </div>
    @endif
</body>
</html>
