@php
    $file = $getRecord()->foto_scontrino;
    $extension = $file ? strtolower(pathinfo($file, PATHINFO_EXTENSION)) : null;
@endphp

@if($file)
    <div class="flex items-center justify-center w-16 h-16 rounded-lg overflow-hidden border">
        @if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp']))
            <img src="{{ Storage::url($file) }}" alt="Scontrino" class="w-full h-full object-cover">
        @elseif($extension === 'pdf')
            <div class="flex flex-col items-center justify-center text-red-600">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs mt-1">PDF</span>
            </div>
        @endif
    </div>
@else
    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
        <span class="text-gray-400 text-xs">N/A</span>
    </div>
@endif
