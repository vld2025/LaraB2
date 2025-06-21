<x-mail::message>
# Fattura {{ $fattura->numero_fattura }}

Gentile {{ $cliente->nome }},

In allegato troverete la fattura **{{ $fattura->numero_fattura }}** del **{{ $fattura->data_fattura->format('d/m/Y') }}** per un importo di **CHF {{ number_format($fattura->totale_finale, 2) }}**.

## Dettagli Fattura

- **Numero:** {{ $fattura->numero_fattura }}
- **Data Fattura:** {{ $fattura->data_fattura->format('d/m/Y') }}
- **Data Scadenza:** {{ $fattura->data_scadenza->format('d/m/Y') }}
- **Periodo di riferimento:** {{ sprintf('%02d/%d', $fattura->mese, $fattura->anno) }}
- **Importo totale:** CHF {{ number_format($fattura->totale_finale, 2) }}

## Modalità di Pagamento

**Coordinate bancarie:**
- Banca Raiffeisen Morbio-Vacallo
- IBAN: CH1980808008277820973
- Scadenza: 30 giorni dalla data fattura

@if($fattura->note)
## Note
{{ $fattura->note }}
@endif

La preghiamo di effettuare il pagamento entro la data di scadenza indicata.

Per qualsiasi chiarimento, non esiti a contattarci.

Cordiali saluti,

**VLD Service GmbH**  
Via Dufour 4  
6900 Lugano  
Svizzera

---
*Questa è una comunicazione automatica. Si prega di non rispondere a questa email.*
</x-mail::message>
