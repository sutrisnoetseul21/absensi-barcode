<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class GuruTemplateExport implements WithHeadings, WithTitle, WithEvents
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Nama Guru',
            'NIP (Opsional)',
            'Password (Opsional - Default: password)',
        ];
    }

    public function title(): string
    {
        return 'Template Guru';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(35);
            },
        ];
    }
}
