<?php

namespace App\Filament\Resources\FatturaResource\Pages;

use App\Filament\Resources\FatturaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFattura extends EditRecord
{
    protected static string $resource = FatturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
