<?php

namespace App\Exports;

use App\Exports\Sheets\ClassesListSheet;
use App\Exports\Sheets\SiswaBaruTemplateSheet;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SiswaBaruTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $classes = Kelas::pluck('name')->toArray();

        return [
            new SiswaBaruTemplateSheet(count($classes)),
            new ClassesListSheet($classes),
        ];
    }
}
