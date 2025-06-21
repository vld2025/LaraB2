<?php

namespace App\Filament\Resources\FatturaResource\Pages;

use App\Filament\Resources\FatturaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFatturas extends ListRecords
{
    protected static string $resource = FatturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
