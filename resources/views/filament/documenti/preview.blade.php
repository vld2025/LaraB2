<div class="p-4">
    @if($documento->mime_type === 'application/pdf')
        <embed src="{{ $documento->file_url }}" type="application/pdf" width="100%" height="600px" />
    @elseif(in_array($documento->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']))
        <img src="{{ $documento->file_url }}" alt="{{ $documento->nome }}" class="max-w-full h-auto mx-auto" />
    @else
        <div class="text-center p-8">
            <p class="text-gray-500 mb-4">Anteprima non disponibile per questo tipo di file</p>
            <a href="{{ route('documento.download', $documento->id) }}" 
               class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Scarica documento
            </a>
        </div>
    @endif
    
    <div class="mt-4 border-t pt-4">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Nome file:</dt>
                <dd class="text-sm text-gray-900">{{ $documento->file_originale }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Dimensione:</dt>
                <dd class="text-sm text-gray-900">{{ $documento->dimensione_file_umana }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Data documento:</dt>
                <dd class="text-sm text-gray-900">{{ $documento->data_documento?->format('d/m/Y') ?? 'N/D' }}</dd>
            </div>
            @if($documento->data_scadenza)
            <div>
                <dt class="text-sm font-medium text-gray-500">Scadenza:</dt>
                <dd class="text-sm {{ $documento->is_scaduto ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                    {{ $documento->data_scadenza->format('d/m/Y') }}
                    @if($documento->is_scaduto)
                        (Scaduto)
                    @endif
                </dd>
            </div>
            @endif
        </dl>
    </div>
</div>
