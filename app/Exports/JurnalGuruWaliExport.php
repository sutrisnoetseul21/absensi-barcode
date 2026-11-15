<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JurnalGuruWaliExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected $jurnals,
        protected $guru,
        protected $kelompok,
        protected $tahunAjaran,
        protected $sekolah,
        protected string $periodeLabel,
    ) {}

    public function view(): View
    {
        return view('excel.jurnal-guru-wali-rekap', [
            'jurnals'      => $this->jurnals,
            'guru'         => $this->guru,
            'kelompok'     => $this->kelompok,
            'tahunAjaran'  => $this->tahunAjaran,
            'sekolah'      => $this->sekolah,
            'periodeLabel' => $this->periodeLabel,
        ]);
    }

    public function title(): string
    {
        return 'Rekap Jurnal Guru Wali';
    }

    public function styles(Worksheet $sheet): void
    {
        $lastRow = max(12, $this->jurnals->count() + 18);

        $sheet->getStyle("A1:O{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 10,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
    }
}

