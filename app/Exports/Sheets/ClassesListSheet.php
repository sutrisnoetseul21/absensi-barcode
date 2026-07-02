<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassesListSheet implements FromArray, WithTitle, WithEvents
{
    protected array $classes;

    public function __construct(array $classes)
    {
        $this->classes = empty($classes) ? ['(Tidak ada kelas)'] : $classes;
    }

    public function array(): array
    {
        return array_map(fn ($class) => [$class], $this->classes);
    }

    public function title(): string
    {
        return 'ClassesList';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Hide this sheet
                $event->sheet->getDelegate()->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            },
        ];
    }
}
