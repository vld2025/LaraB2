<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $userId;
    protected $mese;
    protected $anno;

    public function __construct($userId = null, $mese = null, $anno = null)
    {
        $this->userId = $userId;
        $this->mese = $mese ?? now()->month;
        $this->anno = $anno ?? now()->year;
    }

    public function query()
    {
        $query = Report::with(['user', 'commessa.cantiere.cliente'])
            ->whereMonth('data', $this->mese)
            ->whereYear('data', $this->anno)
            ->orderBy('data')
            ->orderBy('user_id');

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Data',
            'Utente',
            'Cliente',
            'Cantiere',
            'Commessa',
            'Ore',
            'Km',
            'Auto Privata',
            'Festivo',
            'Notturno',
            'Trasferta',
            'Fatturato',
        ];
    }

    public function map($report): array
    {
        return [
            $report->data->format('d/m/Y'),
            $report->user->name,
            $report->commessa->cantiere->cliente->nome,
            $report->commessa->cantiere->nome,
            $report->commessa->nome,
            $report->ore,
            $report->km,
            $report->auto_privata ? 'Sì' : 'No',
            $report->festivo ? 'Sì' : 'No',
            $report->notturno ? 'Sì' : 'No',
            $report->trasferta ? 'Sì' : 'No',
            $report->fatturato ? 'Sì' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
