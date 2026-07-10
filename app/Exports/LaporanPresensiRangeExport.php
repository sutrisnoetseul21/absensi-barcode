<?php

namespace App\Exports;

use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanPresensiRangeExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected string $classId;
    protected string $yearId;
    protected string $startDate;
    protected string $endDate;
    protected string $periodeLabel;

    protected array $rows = [];
    protected int $dataStartRow = 5; // Mulai baris data setelah header kop surat (baris 1-3 = kop, baris 4 = heading)

    public function __construct(
        string $classId,
        string $yearId,
        string $startDate,
        string $endDate,
        string $periodeLabel
    ) {
        $this->classId      = $classId;
        $this->yearId       = $yearId;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->periodeLabel = $periodeLabel;
    }

    public function title(): string
    {
        return 'Laporan Presensi';
    }

    public function headings(): array
    {
        return ['No', 'NISN', 'Nama Siswa', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Total Telat (Mnt)'];
    }

    public function array(): array
    {
        $enrollments = EnrollmentSiswa::with('siswa')
            ->where('class_id', $this->classId)
            ->where('academic_year_id', $this->yearId)
            ->where('status', 'aktif')
            ->get()
            ->sortBy(fn($e) => $e->siswa->name ?? '');

        $presensiData = Presensi::where('class_id', $this->classId)
            ->where('academic_year_id', $this->yearId)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->get()
            ->groupBy('student_id');

        $matrix = [];
        $no = 1;

        foreach ($enrollments as $enrollment) {
            $siswa = $enrollment->siswa;
            if (!$siswa) continue;

            $atts = $presensiData->get($siswa->id, collect());

            $matrix[] = [
                $no++,
                $siswa->nisn,
                $siswa->name,
                $atts->where('status', 'hadir')->count(),
                $atts->where('status', 'telat')->count(),
                $atts->where('status', 'izin')->count(),
                $atts->where('status', 'sakit')->count(),
                $atts->where('status', 'alpa')->count(),
                $atts->sum('late_minutes'),
            ];
        }

        $this->rows = $matrix;
        return $matrix;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // NISN
            'C' => 35,  // Nama
            'D' => 10,  // Hadir
            'E' => 12,  // Terlambat
            'F' => 8,   // Izin
            'G' => 8,   // Sakit
            'H' => 8,   // Alpa
            'I' => 18,  // Total Telat
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Heading row styles
        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $kelas      = Kelas::find($this->classId);
                $tahunAjaran = TahunAjaran::find($this->yearId);
                $sekolah    = \App\Models\PengaturanSekolah::current();
                $now        = now()->format('d/m/Y H:i');

                // ===== INSERT 4 ROWS AT TOP for kop surat =====
                $sheet->insertNewRowBefore(1, 4);

                // Row 1: Nama sekolah
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', strtoupper($sekolah?->school_name ?? 'SEKOLAH'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 2: Alamat
                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', $sekolah?->school_address ?? '');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 3: Judul Laporan
                $sheet->mergeCells('A3:I3');
                $sheet->setCellValue('A3', 'LAPORAN PRESENSI SISWA - ' . strtoupper($this->periodeLabel));
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'underline' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 4: Info kelas
                $sheet->mergeCells('A4:I4');
                $sheet->setCellValue('A4', 'Kelas: ' . ($kelas?->name ?? '-') . '   |   TA: ' . ($tahunAjaran?->name ?? '-') . '   |   Dicetak: ' . $now);
                $sheet->getStyle('A4')->applyFromArray([
                    'font'      => ['size' => 10, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 5: heading
                $headingRowStyle = [
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ];
                $sheet->getStyle('A5:I5')->applyFromArray($headingRowStyle);
                $sheet->getRowDimension(5)->setRowHeight(22);

                // Data rows styling
                $totalDataRows = count($this->rows);
                if ($totalDataRows > 0) {
                    $lastRow = 5 + $totalDataRows;
                    for ($rowIdx = 6; $rowIdx <= $lastRow; $rowIdx++) {
                        $isEven = ($rowIdx % 2 === 0);
                        $bgColor = $isEven ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$rowIdx}:I{$rowIdx}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                        ]);

                        // Center numeric columns
                        $sheet->getStyle("D{$rowIdx}:I{$rowIdx}")->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Outer border for all data
                    $sheet->getStyle("A5:I{$lastRow}")->applyFromArray([
                        'borders' => [
                            'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1E3A5F']],
                        ],
                    ]);
                }

                // Row tinggi untuk data
                for ($r = 6; $r <= 5 + $totalDataRows; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }

                // Freeze pane at row 6 (after header)
                $sheet->freezePane('A6');
            },
        ];
    }
}
