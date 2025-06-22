<?php

namespace App\Filament\Resources\CantiereResource\Pages;

use App\Filament\Resources\CantiereResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCantieri extends ListRecords
{
    protected static string $resource = CantiereResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
