<?php

namespace App\Filament\Resources\SpesaResource\Pages;

use App\Filament\Resources\SpesaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpesas extends ListRecords
{
    protected static string $resource = SpesaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
