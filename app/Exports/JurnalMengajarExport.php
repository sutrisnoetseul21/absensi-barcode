<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class JurnalMengajarExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected $jurnals;
    protected $guru;
    protected $tahunAjaran;
    protected $mapel;
    protected $kelas;
    protected $sekolah;
    protected $periodeLabel;

    public function __construct(
        $jurnals,
        $guru,
        $tahunAjaran,
        $mapel,
        $kelas,
        $sekolah,
        string $periodeLabel
    ) {
        $this->jurnals      = $jurnals;
        $this->guru         = $guru;
        $this->tahunAjaran  = $tahunAjaran;
        $this->mapel        = $mapel;
        $this->kelas        = $kelas;
        $this->sekolah      = $sekolah;
        $this->periodeLabel = $periodeLabel;
    }

    public function view(): View
    {
        return view('excel.jurnal-mengajar-rekap', [
            'jurnals'      => $this->jurnals,
            'guru'         => $this->guru,
            'tahunAjaran'  => $this->tahunAjaran,
            'mapel'        => $this->mapel,
            'kelas'        => $this->kelas,
            'sekolah'      => $this->sekolah,
            'periodeLabel' => $this->periodeLabel,
        ]);
    }

    public function title(): string
    {
        return 'Rekap Jurnal Mengajar';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1000')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 10,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
}
