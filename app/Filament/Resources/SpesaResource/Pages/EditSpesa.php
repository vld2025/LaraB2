<?php

namespace App\Filament\Resources\SpesaResource\Pages;

use App\Filament\Resources\SpesaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpesa extends EditRecord
{
    protected static string $resource = SpesaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
