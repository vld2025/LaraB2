<x-filament-widgets::widget>
    <x-filament::card>
        <div class="flex flex-col gap-4">
            <h2 class="text-lg font-medium">Azioni Rapide</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{ $this->creaReportAction }}
                {{ $this->creaSpeseAction }}
                {{ $this->creaSpeseExtraAction }}
            </div>
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
