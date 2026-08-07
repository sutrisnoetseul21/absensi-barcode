<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\SlimsBukuSheet;
use App\Exports\Sheets\SlimsEksemplarSheet;
use App\Services\SlimsConnectionService;

/**
 * Export data katalog buku dari SLiMS ke Excel.
 * Terdiri dari 2 sheet: Buku dan Eksemplar.
 */
class SlimsBukuExport implements WithMultipleSheets
{
    use Exportable;

    protected SlimsConnectionService $slimsConnection;

    public function __construct(SlimsConnectionService $slimsConnection)
    {
        $this->slimsConnection = $slimsConnection;
    }

    public function sheets(): array
    {
        return [
            new SlimsBukuSheet($this->slimsConnection),
            new SlimsEksemplarSheet($this->slimsConnection),
        ];
    }
}
