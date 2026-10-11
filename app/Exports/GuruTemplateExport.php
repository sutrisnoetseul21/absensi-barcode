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
            'Jenis Kelamin (L/P - Opsional)',
            'NIP (Opsional)',
            'No. HP (Opsional)',
            'Email (Opsional - Auto jika kosong)',
            'Password (Opsional - Default: password)',
            'Link Facebook (Opsional)',
            'Link Instagram (Opsional)',
            'Link Microsite (Opsional)',
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
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(35);
                $sheet->getColumnDimension('G')->setWidth(35);
                $sheet->getColumnDimension('H')->setWidth(35);
                $sheet->getColumnDimension('I')->setWidth(40);
            },
        ];
    }
}
