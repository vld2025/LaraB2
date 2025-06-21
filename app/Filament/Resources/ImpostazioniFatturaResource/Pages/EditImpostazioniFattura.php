<?php

namespace App\Filament\Resources\ImpostazioniFatturaResource\Pages;

use App\Filament\Resources\ImpostazioniFatturaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImpostazioniFattura extends EditRecord
{
    protected static string $resource = ImpostazioniFatturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
