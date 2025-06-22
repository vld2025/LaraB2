<div class="space-y-6">
    {{-- Informazioni Documento --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome documento</h4>
                <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $documento->nome }}</p>
            </div>
            
            @if($documento->descrizione)
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Descrizione</h4>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $documento->descrizione }}</p>
                </div>
            @endif
            
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Data documento</h4>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ $documento->data_documento?->format('d/m/Y') ?? 'Non specificata' }}
                </p>
            </div>
            
            @if($documento->data_scadenza)
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Data scadenza</h4>
                    <p class="mt-1 text-sm {{ $documento->isScaduto ? 'text-red-600' : ($documento->giorni_alla_scadenza <= 30 ? 'text-orange-600' : 'text-gray-900') }}">
                        {{ $documento->data_scadenza->format('d/m/Y') }}
                        @if($documento->isScaduto)
                            <span class="text-xs font-semibold">(SCADUTO)</span>
                        @elseif($documento->giorni_alla_scadenza <= 30)
                            <span class="text-xs">(Scade tra {{ $documento->giorni_alla_scadenza }} giorni)</span>
                        @endif
                    </p>
                </div>
            @endif
            
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Dimensione file</h4>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $documento->dimensione_file_umana }}</p>
            </div>
            
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Caricato il</h4>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $documento->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
    
    {{-- Anteprima PDF se disponibile --}}
    @if($documento->mime_type === 'application/pdf' && Storage::exists($documento->file_path))
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Anteprima documento</h4>
            <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-100">
                <iframe 
                    src="{{ Storage::url($documento->file_path) }}" 
                    class="w-full h-[600px]"
                    title="Anteprima {{ $documento->nome }}"
                ></iframe>
            </div>
        </div>
    @elseif(str_starts_with($documento->mime_type, 'image/') && Storage::exists($documento->file_path))
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Anteprima immagine</h4>
            <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-100 p-4">
                <img 
                    src="{{ Storage::url($documento->file_path) }}" 
                    alt="{{ $documento->nome }}"
                    class="max-w-full h-auto mx-auto rounded-lg shadow-lg"
                />
            </div>
        </div>
    @endif
    
    {{-- Pulsante Scarica --}}
    <div class="mt-8 flex justify-center pb-4">
        <x-filament::button
            wire:click="mountTableAction('scarica', '{{ $documento->id }}')"
            color="success"
            icon="heroicon-o-arrow-down-tray"
            size="lg"
        >
            Scarica documento
        </x-filament::button>
    </div>
</div>
