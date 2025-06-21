<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class AzioniRapide extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    
    protected static ?int $sort = 2;
    protected static string $view = 'filament.widgets.azioni-rapide';
    
    public function creaReportAction(): Action
    {
        return Action::make('creaReport')
            ->label('Aggiungi Report')
            ->icon('heroicon-o-document-plus')
            ->color('primary')
            ->size('lg')
            ->url(route('filament.admin.resources.reports.create'));
    }
    
    public function creaSpeseAction(): Action
    {
        return Action::make('creaSpese')
            ->label('Aggiungi Spese')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->size('lg')
            ->url(route('filament.admin.resources.spesas.create'));
    }
    
    public function creaSpeseExtraAction(): Action
    {
        return Action::make('creaSpeseExtra')
            ->label('Aggiungi Spese Extra')
            ->icon('heroicon-o-receipt-percent')
            ->color('warning')
            ->size('lg')
            ->url(route('filament.admin.resources.spesa-extras.create'));
    }
    
    public static function canView(): bool
    {
        return true;
    }
}
