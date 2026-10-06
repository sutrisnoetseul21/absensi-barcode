<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanMbgHarianExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $date;
    protected $tahunAjaran;
    protected $classesSummary;

    public function __construct($date, $tahunAjaran, $classesSummary)
    {
        $this->date = $date;
        $this->tahunAjaran = $tahunAjaran;
        $this->classesSummary = $classesSummary;
    }

    public function view(): View
    {
        return view('pdf.laporan-mbg-harian', [
            'dateFormatted' => \Carbon\Carbon::parse($this->date)->locale('id')->translatedFormat('l, d-m-Y'),
            'tahunAjaran' => $this->tahunAjaran,
            'classesSummary' => collect($this->classesSummary)->sortBy('name')->values()->all()
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = 'A1:' . $highestColumn . $highestRow;

        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
        ]);
        
        // Find table start which is row 5
        $sheet->getStyle('A5:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
}
