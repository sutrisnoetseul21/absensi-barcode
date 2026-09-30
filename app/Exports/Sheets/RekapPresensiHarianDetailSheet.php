<?php

namespace App\Exports\Sheets;

use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPresensiHarianDetailSheet implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected string $selectedDate,
        protected ?string $academicYearId,
        protected array $allStudents
    ) {}

    public function view(): View
    {
        $ay = TahunAjaran::find($this->academicYearId);
        $sekolah = PengaturanSekolah::current();
        $dateFormatted = Carbon::parse($this->selectedDate)->locale('id')->translatedFormat('l, d F Y');

        return view('exports.rekap-presensi-harian-detail', [
            'selectedDateFormatted' => $dateFormatted,
            'tahunAjaran'           => $ay,
            'sekolah'               => $sekolah,
            'allStudents'           => $this->allStudents,
            'generatedAt'           => now()->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB',
        ]);
    }

    public function title(): string
    {
        return 'Detail Siswa';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['italic' => true, 'size' => 10]],
            5 => ['font' => ['bold' => true, 'size' => 10]],
        ];
    }
}
