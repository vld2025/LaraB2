<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
                ->label('Esporta Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new ReportsExport(
                            auth()->user() && auth()->user() && auth()->user()->hasRole(['admin', 'manager']) ? null : auth()->id(),
                            request()->get('tableFilters.data.data_da') ? \Carbon\Carbon::parse(request()->get('tableFilters.data.data_da'))->month : now()->month,
                            request()->get('tableFilters.data.data_da') ? \Carbon\Carbon::parse(request()->get('tableFilters.data.data_da'))->year : now()->year
                        ),
                        'report_' . now()->format('Y_m_d') . '.xlsx'
                    );
                }),
        ];
    }
}
