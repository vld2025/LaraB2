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
                    <p class="mt-1 text-sm font-medium {{ $documento->isScaduto ? 'text-red-600' : ($documento->giorni_alla_scadenza <= 30 ? 'text-orange-600' : 'text-gray-900') }}">
                        {{ $documento->data_scadenza->format('d/m/Y') }}
                        @if($documento->isScaduto)
                            <span class="text-xs">(SCADUTO)</span>
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

    @php
        // Determina il path corretto del file
        $filePath = $documento->file_path;
        $fileUrl = null;
        $fileExists = false;
        $isPdf = false;
        $isImage = false;

        // Rimuovi 'public/' se presente e costruisci l'URL
        $cleanPath = str_replace('public/', '', $filePath);
        $fileUrl = asset('storage/' . $cleanPath);
        
        // Verifica se il file esiste
        $fullPath = storage_path('app/public/' . $cleanPath);
        $fileExists = file_exists($fullPath);
        
        // Determina il tipo di file
        $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
        $isPdf = $extension === 'pdf';
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    @endphp

    {{-- Anteprima File --}}
    @if($fileExists && $fileUrl)
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Anteprima documento</h3>
            </div>
            
            <div class="p-4">
                @if($isPdf)
                    {{-- Viewer PDF --}}
                    <div class="w-full h-96 border border-gray-300 rounded-lg overflow-hidden">
                        <iframe 
                            src="{{ $fileUrl }}#toolbar=1&navpanes=1&scrollbar=1&page=1&view=FitH" 
                            width="100%" 
                            height="100%" 
                            style="border: none;"
                            title="Anteprima PDF">
                            <p>Il tuo browser non supporta la visualizzazione PDF. 
                               <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 hover:underline">
                                   Clicca qui per aprire il file
                               </a>
                            </p>
                        </iframe>
                    </div>
                    
                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400 flex items-center gap-4">
                        <span>💡 Usa Ctrl+Scroll per zoomare</span>
                        <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Apri in nuova scheda
                        </a>
                    </div>
                @elseif($isImage)
                    {{-- Viewer Immagini --}}
                    <div class="text-center">
                        <img 
                            src="{{ $fileUrl }}" 
                            alt="{{ $documento->nome }}" 
                            class="max-w-full max-h-96 mx-auto rounded-lg shadow-lg"
                            style="object-fit: contain;"
                        />
                    </div>
                @else
                    {{-- Altri tipi di file --}}
                    <div class="text-center py-8">
                        <div class="mb-4">
                            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Anteprima non disponibile per questo tipo di file (.{{ $extension }})
                        </p>
                        <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Apri file
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- File non trovato --}}
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.734 0L5.08 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">File non trovato</h3>
                    <p class="text-sm text-red-600 dark:text-red-300">Il file associato a questo documento non è accessibile.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Pulsante Scarica --}}
    @if($fileExists && $fileUrl)
        <div class="flex justify-center">
            <a href="{{ $fileUrl }}" 
               download="{{ $documento->file_originale ?? $documento->nome }}"
               class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Scarica documento
            </a>
        </div>
    @endif
</div>
