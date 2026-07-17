<?php

namespace App\Exports;

use App\Exports\Sheets\SiswaBaruTemplateSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Export template Excel untuk Import Siswa Baru.
 *
 * ClassesListSheet telah dihapus sesuai arsitektur pisah total (Refactoring Tahap 3).
 * Template ini hanya berisi sheet data identitas siswa tanpa dropdown kelas.
 */
class SiswaBaruTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        return [
            new SiswaBaruTemplateSheet(),
        ];
    }
}
