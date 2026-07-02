<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeachersListSheet implements FromArray, WithTitle, WithEvents
{
    protected array $gurus;

    public function __construct(array $gurus)
    {
        // If there are no gurus, we must provide at least one empty row 
        // so the sheet is valid and referenceable.
        $this->gurus = empty($gurus) ? ['(Tidak ada guru)'] : $gurus;
    }

    public function array(): array
    {
        // Must return an array of arrays (rows)
        return array_map(fn ($guru) => [$guru], $this->gurus);
    }

    public function title(): string
    {
        return 'TeachersList';
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
