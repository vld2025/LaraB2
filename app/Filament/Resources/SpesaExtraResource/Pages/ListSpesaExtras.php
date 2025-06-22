<?php

namespace App\Filament\Resources\SpesaExtraResource\Pages;

use App\Filament\Resources\SpesaExtraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpesaExtras extends ListRecords
{
    protected static string $resource = SpesaExtraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
