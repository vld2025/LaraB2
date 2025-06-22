<?php

namespace App\Filament\Resources\DocumentoResource\Pages;

use App\Filament\Resources\DocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDocumento extends ViewRecord
{
    protected static string $resource = DocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
