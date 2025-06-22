<x-filament-panels::page>
    @if(!$this->activeFolder)
        {{-- Vista Cartelle --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($this->getFolders() as $folder)
                <a href="{{ static::getUrl(['folder' => $folder['slug']]) }}" 
                   class="group relative block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 border border-gray-200 dark:border-gray-700">
                    
                    {{-- Icona e Badge --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-lg bg-gray-100 dark:bg-gray-700">
                            <x-filament::icon 
                                :icon="$folder['icona']" 
                                class="w-6 h-6 text-gray-600 dark:text-gray-400"
                            />
                        </div>
                        
                        @if($folder['count'] > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ $folder['count'] }}
                            </span>
                        @endif
                    </div>
                    
                    {{-- Nome e Descrizione --}}
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                        {{ $folder['nome'] }}
                    </h3>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $folder['descrizione'] }}
                    </p>
                    
                    {{-- Indicatore se può caricare --}}
                    @if($folder['can_upload'])
                        <div class="mt-4 flex items-center text-xs text-gray-500 dark:text-gray-400">
                            <x-filament::icon 
                                icon="heroicon-o-arrow-up-tray" 
                                class="w-4 h-4 mr-1"
                            />
                            Puoi caricare documenti
                        </div>
                    @else
                        <div class="mt-4 flex items-center text-xs text-gray-500 dark:text-gray-400">
                            <x-filament::icon 
                                icon="heroicon-o-eye" 
                                class="w-4 h-4 mr-1"
                            />
                            Solo visualizzazione
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
        
        {{-- Messaggio informativo --}}
        <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-filament::icon 
                        icon="heroicon-o-information-circle" 
                        class="w-5 h-5 text-blue-400"
                    />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Clicca su una cartella per visualizzare i documenti contenuti. 
                        Puoi caricare nuovi documenti solo nella cartella "Documenti Personali".
                    </p>
                </div>
            </div>
        </div>
    @else
        {{-- Vista Lista Documenti --}}
        {{ $this->table }}
    @endif
</x-filament-panels::page>
