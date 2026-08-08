<?php

namespace App\Exports;

use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapAbsensiSekolahExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected string $academicYearId;
    protected array $classesData;
    protected array $monthsList;

    public function __construct(string $academicYearId, array $classesData, array $monthsList)
    {
        $this->academicYearId = $academicYearId;
        $this->classesData    = $classesData;
        $this->monthsList     = $monthsList;
    }

    public function view(): View
    {
        $tahunAjaran = TahunAjaran::find($this->academicYearId);
        $sekolah     = PengaturanSekolah::current();

        return view('exports.rekap-absensi-sekolah-excel', [
            'tahunAjaran' => $tahunAjaran,
            'sekolah'     => $sekolah,
            'classesData' => $this->classesData,
            'monthsList'  => $this->monthsList,
            'generatedAt' => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ]);
    }

    public function title(): string
    {
        return 'Rekap Presensi Sekolah';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
        ];
    }
}
