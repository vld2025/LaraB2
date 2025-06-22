<?php

namespace App\Filament\Resources\DocumentoResource\Pages;

use App\Filament\Resources\DocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumento extends EditRecord
{
    protected static string $resource = DocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Assicurati che documentabile_type sia sempre impostato
        if (!isset($data['documentabile_type']) || !$data['documentabile_type']) {
            $data['documentabile_type'] = 'App\\Models\\User';
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Assicurati che documentabile_type sia sempre impostato
        $data['documentabile_type'] = 'App\\Models\\User';
        
        // Se documentabile_id non è impostato, usa l'utente corrente
        if (!isset($data['documentabile_id']) || !$data['documentabile_id']) {
            $data['documentabile_id'] = auth()->id();
        }
        
        // Mantieni caricato_da se già impostato, altrimenti usa utente corrente
        if (!isset($data['caricato_da']) || !$data['caricato_da']) {
            $data['caricato_da'] = auth()->id();
        }
        
        return $data;
    }
}
