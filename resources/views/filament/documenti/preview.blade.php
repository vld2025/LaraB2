<div class="space-y-4">
    {{-- Informazioni Documento --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome file</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $documento->nome }}</dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dimensione</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $documento->dimensione_file_umana }}</dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data documento</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">
                    {{ $documento->data_documento ? $documento->data_documento->format('d/m/Y') : 'N/D' }}
                </dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Scadenza</dt>
                <dd class="text-sm">
                    @if($documento->data_scadenza)
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                            {{ $documento->is_scaduto ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                               ($documento->giorni_alla_scadenza <= 30 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') }}">
                            {{ $documento->data_scadenza->format('d/m/Y') }}
                            @if($documento->is_scaduto)
                                (Scaduto)
                            @elseif($documento->giorni_alla_scadenza <= 30)
                                ({{ $documento->giorni_alla_scadenza }} giorni)
                            @endif
                        </span>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">Nessuna scadenza</span>
                    @endif
                </dd>
            </div>
            
            @if($documento->descrizione)
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descrizione</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $documento->descrizione }}</dd>
            </div>
            @endif
            
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Caricato da</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">
                    {{ $documento->caricatoDa ? $documento->caricatoDa->name : 'N/D' }} 
                    il {{ $documento->created_at->format('d/m/Y H:i') }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- Preview Documento --}}
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-white dark:bg-gray-900">
        @php
            $mimeType = $documento->mime_type ?? 'application/octet-stream';
            $isImage = str_starts_with($mimeType, 'image/');
            $isPdf = $mimeType === 'application/pdf';
            $fileUrl = $documento->file_url;
        @endphp

        @if($isImage && $fileUrl)
            {{-- Preview Immagine --}}
            <div class="p-4 text-center">
                <img src="{{ $fileUrl }}" 
                     alt="{{ $documento->nome }}" 
                     class="max-w-full h-auto mx-auto rounded-lg shadow-lg"
                     style="max-height: 600px;">
            </div>
        @elseif($isPdf && $fileUrl)
            {{-- Preview PDF --}}
            <div class="relative" style="height: 600px;">
                <iframe src="{{ $fileUrl }}" 
                        class="w-full h-full"
                        frameborder="0">
                </iframe>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800 text-center border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Se il PDF non viene visualizzato correttamente, 
                    <a href="{{ $fileUrl }}" 
                       target="_blank" 
                       class="text-primary-600 hover:text-primary-500 font-medium">
                        aprilo in una nuova scheda
                    </a>
                </p>
            </div>
        @else
            {{-- Altri tipi di file --}}
            <div class="p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 dark:bg-gray-800">
                    <x-filament::icon 
                        icon="heroicon-o-document" 
                        class="h-12 w-12 text-gray-400 dark:text-gray-500"
                    />
                </div>
                <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $documento->nome }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tipo file: {{ $mimeType }}
                </p>
                <div class="mt-6">
                    <a href="{{ $fileUrl }}" 
                       download="{{ $documento->file_originale ?? $documento->nome }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <x-filament::icon 
                            icon="heroicon-o-arrow-down-tray" 
                            class="h-4 w-4 mr-2"
                        />
                        Scarica documento
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Informazioni tecniche (solo per debug/admin) --}}
    @if(auth()->user()->hasRole(['admin', 'manager']))
    <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-800 rounded text-xs text-gray-600 dark:text-gray-400">
        <details>
            <summary class="cursor-pointer font-medium">Informazioni tecniche</summary>
            <dl class="mt-2 space-y-1">
                <div>
                    <dt class="inline font-medium">File path:</dt>
                    <dd class="inline">{{ $documento->file_path }}</dd>
                </div>
                <div>
                    <dt class="inline font-medium">Hash SHA256:</dt>
                    <dd class="inline font-mono">{{ substr($documento->hash_sha256 ?? 'N/D', 0, 16) }}...</dd>
                </div>
                <div>
                    <dt class="inline font-medium">File exists:</dt>
                    <dd class="inline">{{ $documento->file_exists ? 'Sì' : 'No' }}</dd>
                </div>
            </dl>
        </details>
    </div>
    @endif
</div>
