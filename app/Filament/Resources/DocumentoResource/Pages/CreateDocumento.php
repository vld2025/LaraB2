<?php

namespace App\Filament\Resources\DocumentoResource\Pages;

use App\Filament\Resources\DocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumento extends CreateRecord
{
    protected static string $resource = DocumentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Imposta automaticamente i valori obbligatori
        $data['documentabile_type'] = 'App\\Models\\User';
        $data['caricato_da'] = auth()->id();
        
        // Se documentabile_id non è impostato, usa l'utente corrente
        if (!isset($data['documentabile_id']) || !$data['documentabile_id']) {
            $data['documentabile_id'] = auth()->id();
        }
        
        return $data;
    }
}
