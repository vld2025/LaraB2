<div class="space-y-4">
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500">Ore Totali</h3>
            <p class="text-2xl font-bold">{{ number_format($commessa->getTotaleOre(), 1) }} h</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500">Km Totali</h3>
            <p class="text-2xl font-bold">{{ number_format($commessa->getTotaleKm()) }} km</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500">Spese Totali</h3>
            <p class="text-2xl font-bold">CHF {{ number_format($commessa->getTotaleSpese(), 2) }}</p>
        </div>
    </div>
    
    @if($commessa->budget)
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Budget</h3>
        <div class="relative pt-1">
            @php
                $impostazioni = \App\Models\ImpostazioniFattura::getForCliente($commessa->cantiere->cliente_id);
                $costoTotale = ($commessa->getTotaleOre() * $impostazioni->costo_orario) + $commessa->getTotaleSpese();
                $percentuale = $commessa->budget > 0 ? ($costoTotale / $commessa->budget) * 100 : 0;
            @endphp
            <div class="flex mb-2 items-center justify-between">
                <div>
                    <span class="text-xs font-semibold inline-block text-blue-600">
                        CHF {{ number_format($costoTotale, 2) }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold inline-block text-blue-600">
                        {{ number_format($percentuale, 1) }}%
                    </span>
                </div>
            </div>
            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                <div style="width:{{ min($percentuale, 100) }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"></div>
            </div>
        </div>
    </div>
    @endif
</div>
