<?php

namespace App\Filament\Resources\ImpostazioniFatturaResource\Pages;

use App\Filament\Resources\ImpostazioniFatturaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImpostazioniFatturas extends ListRecords
{
    protected static string $resource = ImpostazioniFatturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
