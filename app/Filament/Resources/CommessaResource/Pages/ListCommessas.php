<?php

namespace App\Filament\Resources\CommessaResource\Pages;

use App\Filament\Resources\CommessaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommessas extends ListRecords
{
    protected static string $resource = CommessaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
