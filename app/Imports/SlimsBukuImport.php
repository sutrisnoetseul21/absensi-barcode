<?php

namespace App\Imports;

use App\Imports\Sheets\SlimsBukuSheetImport;
use App\Imports\Sheets\SlimsEksemplarSheetImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SlimsBukuImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            // Sheet 0: Buku (wajib jalan pertama agar ID bukunya ada)
            0 => new SlimsBukuSheetImport(),
            
            // Sheet 1: Eksemplar (jalan setelah Sheet 0 selesai)
            1 => new SlimsEksemplarSheetImport(),
        ];
    }
}
