<?php

namespace App\Filament\Resources\DocumentoResource\Pages;

use App\Filament\Resources\DocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDocumentos extends ListRecords
{
    protected static string $resource = DocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DocumentoResource\Widgets\DocumentiStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'tutti' => Tab::make()
                ->badge(fn () => \App\Models\Documento::count()),
            'importanti' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->importanti())
                ->badge(fn () => \App\Models\Documento::importanti()->count()),
            'in_scadenza' => Tab::make()
                ->label('In Scadenza')
                ->modifyQueryUsing(fn (Builder $query) => $query->inScadenza())
                ->badge(fn () => \App\Models\Documento::inScadenza()->count())
                ->badgeColor('warning'),
            'scaduti' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->scaduti())
                ->badge(fn () => \App\Models\Documento::scaduti()->count())
                ->badgeColor('danger'),
        ];
    }
}
