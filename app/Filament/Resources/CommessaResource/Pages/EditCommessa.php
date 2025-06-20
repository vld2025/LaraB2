<?php

namespace App\Filament\Resources\CommessaResource\Pages;

use App\Filament\Resources\CommessaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommessa extends EditRecord
{
    protected static string $resource = CommessaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
