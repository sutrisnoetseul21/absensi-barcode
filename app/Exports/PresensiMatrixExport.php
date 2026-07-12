<?php

namespace App\Exports;

use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Models\HariLibur;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Models\Kelas;
use App\Models\TahunAjaran;

class PresensiMatrixExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected string $classId;
    protected string $yearId;
    protected string $month;
    protected string $year;
    protected string $periodeLabel;
    
    protected array $daysInMonth;
    protected array $holidays;
    protected array $rows = [];

    public function __construct(string $classId, string $yearId, string $month, string $year, string $periodeLabel = '')
    {
        $this->classId = $classId;
        $this->yearId = $yearId;
        $this->month = $month;
        $this->year = $year;
        $this->periodeLabel = $periodeLabel ?: "BULAN $month-$year";
        
        $daysInMonthCount = Carbon::create($year, $month, 1)->daysInMonth;
        $this->daysInMonth = range(1, $daysInMonthCount);
    }

    public function headings(): array
    {
        $headings = ['No', 'NISN', 'Nama'];
        
        foreach ($this->daysInMonth as $day) {
            $headings[] = (string) $day;
        }
        
        return array_merge($headings, ['Total H', 'Total T', 'Total S', 'Total I', 'Total A']);
    }

    public function array(): array
    {
        // Ambil siswa yang aktif di kelas ini pada tahun ajaran ini
        $enrollments = EnrollmentSiswa::with('siswa')
            ->where('class_id', $this->classId)
            ->where('academic_year_id', $this->yearId)
            ->where('status', 'aktif')
            ->get()
            ->sortBy(function($enrollment) {
                return $enrollment->siswa->name ?? '';
            });

        $matrix = [];
        $no = 1;

        $kalenderService = app(\App\Services\KalenderSekolahService::class);

        foreach ($enrollments as $enrollment) {
            $siswa = $enrollment->siswa;
            if (!$siswa) continue;

            $row = [
                $no++,
                $siswa->nisn,
                $siswa->name,
            ];

            // Ambil semua presensi siswa di bulan dan tahun ini
            $presensiData = Presensi::where('student_id', $siswa->id)
                ->whereYear('date', $this->year)
                ->whereMonth('date', $this->month)
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });

            $totals = [
                'Hadir' => 0,
                'Terlambat' => 0,
                'Sakit' => 0,
                'Izin' => 0,
                'Alpa' => 0,
            ];

            foreach ($this->daysInMonth as $day) {
                $dateStr = sprintf('%04d-%02d-%02d', $this->year, $this->month, $day);
                $carbonDate = Carbon::createFromFormat('Y-m-d', $dateStr);
                
                // Cek hari libur / weekend via service terpusat
                if (!$kalenderService->isHariSekolah($carbonDate, $this->classId)) {
                    $row[] = 'L'; // Menggunakan tanda L sesuai instruksi terbaru
                    continue;
                }

                if (isset($presensiData[$dateStr])) {
                    $status = $presensiData[$dateStr]->status;
                    $initial = match ($status) {
                        'hadir' => 'H',
                        'telat' => 'T',
                        'sakit' => 'S',
                        'izin' => 'I',
                        'alpa' => 'A',
                        default => '?'
                    };
                    $row[] = $initial;
                    
                    if ($status === 'hadir') $totals['Hadir']++;
                    elseif ($status === 'telat') $totals['Terlambat']++;
                    elseif ($status === 'sakit') $totals['Sakit']++;
                    elseif ($status === 'izin') $totals['Izin']++;
                    elseif ($status === 'alpa') $totals['Alpa']++;
                } else {
                    $row[] = '-'; // Jika tidak ada record dan bukan libur, default '-'
                }
            }

            // Append totals
            $row[] = $totals['Hadir'];
            $row[] = $totals['Terlambat'];
            $row[] = $totals['Sakit'];
            $row[] = $totals['Izin'];
            $row[] = $totals['Alpa'];

            $matrix[] = $row;
        }

        $this->rows = $matrix;
        return $matrix;
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $kelas = Kelas::find($this->classId);
                $tahunAjaran = TahunAjaran::find($this->yearId);
                $sekolah = \App\Models\PengaturanSekolah::current();
                $now = now()->format('d/m/Y H:i');

                // Determine last column (3 base cols + days count + 5 total cols)
                $totalCols = 3 + count($this->daysInMonth) + 5;
                $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);

                // ===== INSERT 4 ROWS AT TOP for kop surat =====
                $sheet->insertNewRowBefore(1, 4);

                // Row 1: LAPORAN PRESENSI
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->setCellValue('A1', 'LAPORAN PRESENSI ' . strtoupper($this->periodeLabel));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 2: NAMA SEKOLAH
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->setCellValue('A2', strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 3: KELAS
                $sheet->mergeCells("A3:{$lastColLetter}3");
                $sheet->setCellValue('A3', 'KELAS ' . strtoupper($kelas?->name ?? ''));
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 4: TAHUN AJARAN
                $sheet->mergeCells("A4:{$lastColLetter}4");
                $sheet->setCellValue('A4', 'TAHUN AJARAN ' . strtoupper($tahunAjaran?->name ?? ''));
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 5: heading styling
                $headingRowStyle = [
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ];
                $sheet->getStyle("A5:{$lastColLetter}5")->applyFromArray($headingRowStyle);
                $sheet->getRowDimension(5)->setRowHeight(22);

                // Set default column widths
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(30);
                
                // Days columns
                for ($col = 4; $col <= 3 + count($this->daysInMonth); $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth(4);
                }

                // Total columns
                $startTotal = 4 + count($this->daysInMonth);
                for ($col = $startTotal; $col <= $totalCols; $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth(8);
                }

                // Data rows styling
                $totalDataRows = count($this->rows);
                if ($totalDataRows > 0) {
                    $lastRow = 5 + $totalDataRows;
                    for ($rowIdx = 6; $rowIdx <= $lastRow; $rowIdx++) {
                        $isEven = ($rowIdx % 2 === 0);
                        $bgColor = $isEven ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$rowIdx}:{$lastColLetter}{$rowIdx}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                        ]);

                        // Center days and totals columns
                        $sheet->getStyle("D{$rowIdx}:{$lastColLetter}{$rowIdx}")->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Outer border
                    $sheet->getStyle("A5:{$lastColLetter}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1E3A5F']],
                        ],
                    ]);
                }

                // Freeze pane at row 6
                $sheet->freezePane('A6');
                $sheet->freezePane('D6'); // Also freeze name column if supported by Excel
            },
        ];
    }
}
