<?php

namespace App\Exports;

use App\Exports\Sheets\KelasTemplateSheet;
use App\Exports\Sheets\TeachersListSheet;
use App\Models\Guru;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KelasTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $gurus = Guru::pluck('name')->toArray();

        return [
            new KelasTemplateSheet(max(1, count($gurus))),
            new TeachersListSheet($gurus),
        ];
    }
}
