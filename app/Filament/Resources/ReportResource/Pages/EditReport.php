<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = Auth::user();
        
        if ($user->hasRole(['admin', 'manager'])) {
            // Admin/Manager vedono e modificano i dati cliente (versione per fatturazione)
            $datiCliente = $this->record->getDataForManager();
            $data['ore'] = $datiCliente['ore'] ?? $data['ore'];
            $data['km'] = $datiCliente['km'] ?? $data['km'];
            $data['auto_privata'] = $datiCliente['auto_privata'] ?? $data['auto_privata'];
            $data['festivo'] = $datiCliente['festivo'] ?? $data['festivo'];
            $data['notturno'] = $datiCliente['notturno'] ?? $data['notturno'];
            $data['trasferta'] = $datiCliente['trasferta'] ?? $data['trasferta'];
        } else {
            // User vede e modifica i dati originali
            $datiOriginali = $this->record->getDataForUser();
            $data['ore'] = $datiOriginali['ore'] ?? $data['ore'];
            $data['km'] = $datiOriginali['km'] ?? $data['km'];
            $data['auto_privata'] = $datiOriginali['auto_privata'] ?? $data['auto_privata'];
            $data['festivo'] = $datiOriginali['festivo'] ?? $data['festivo'];
            $data['notturno'] = $datiOriginali['notturno'] ?? $data['notturno'];
            $data['trasferta'] = $datiOriginali['trasferta'] ?? $data['trasferta'];
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
