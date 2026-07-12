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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Services\PresensiRekapService;

class LaporanPresensiRangeExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected string $classId;
    protected string $yearId;
    protected string $startDate;
    protected string $endDate;
    protected string $periodeLabel;

    protected array $rows = [];
    protected array $monthsList = [];
    protected int $dataStartRow = 6; // Mulai baris data setelah header kop surat (baris 1-3 = kop, 4 = info, 5-6 = heading)

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
        // Heading handled fully in registerEvents to allow for 2-row merged headers
        return [];
    }

    public function array(): array
    {
        $service = app(PresensiRekapService::class);
        $result = $service->getStudentSemesterYearlyData(
            $this->yearId,
            $this->classId,
            $this->startDate,
            $this->endDate
        );

        $this->monthsList = $result['monthsList'];
        $studentsData = $result['studentsData'];

        $matrix = [];

        foreach ($studentsData as $row) {
            $rowData = [
                $row['no'],
                $row['nisn'],
                $row['name'],
            ];

            foreach ($this->monthsList as $m) {
                $key = "{$m['year']}-{$m['month']}";
                $stats = $row['months'][$key] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                
                $rowData[] = $stats['hadir'];
                $rowData[] = $stats['sakit'];
                $rowData[] = $stats['izin'];
                $rowData[] = $stats['alpa'];
            }

            // Total
            $rowData[] = $row['total']['hadir'];
            $rowData[] = $row['total']['telat'];
            $rowData[] = $row['total']['sakit'];
            $rowData[] = $row['total']['izin'];
            $rowData[] = $row['total']['alpa'];
            $rowData[] = $row['total']['late_minutes'];

            $matrix[] = $rowData;
        }

        $this->rows = $matrix;
        return $matrix;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // No
            'B' => 15,  // NISN
            'C' => 35,  // Nama
        ];
        
        $colIdx = 4;
        
        // Month columns
        if (count($this->monthsList) > 0) {
            foreach ($this->monthsList as $m) {
                for ($i = 0; $i < 4; $i++) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx++);
                    $widths[$colLetter] = 6;
                }
            }
        }
        
        // Total columns
        for ($i = 0; $i < 6; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx++);
            $widths[$colLetter] = 8;
        }

        return $widths;
    }
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
        return [];
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

                $totalMonthCols = count($this->monthsList) * 4;
                $totalCols = 3 + $totalMonthCols + 6;
                $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);

                // ===== INSERT ROWS AT TOP for kop surat =====
                // Karena kita menggunakan array data yang langsung dicetak di baris pertama,
                // Kita perlu insert 6 row di atas (1-4 = kop, 5-6 = heading matrix)
                $sheet->insertNewRowBefore(1, 6);

                // Row 1: Nama sekolah
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->setCellValue('A1', strtoupper($sekolah?->school_name ?? 'SEKOLAH'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 2: Alamat
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->setCellValue('A2', $sekolah?->school_address ?? '');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 3: Judul Laporan
                $sheet->mergeCells("A3:{$lastColLetter}3");
                $sheet->setCellValue('A3', 'LAPORAN PRESENSI SISWA - ' . strtoupper($this->periodeLabel));
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'underline' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 4: Info kelas
                $sheet->mergeCells("A4:{$lastColLetter}4");
                $sheet->setCellValue('A4', 'Kelas: ' . ($kelas?->name ?? '-') . '   |   TA: ' . ($tahunAjaran?->name ?? '-') . '   |   Dicetak: ' . $now);
                $sheet->getStyle('A4')->applyFromArray([
                    'font'      => ['size' => 10, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 5 & 6: heading matrix
                $sheet->mergeCells("A5:A6");
                $sheet->setCellValue('A5', 'No');
                
                $sheet->mergeCells("B5:B6");
                $sheet->setCellValue('B5', 'NISN');
                
                $sheet->mergeCells("C5:C6");
                $sheet->setCellValue('C5', 'Nama Siswa');

                $colIdx = 4;
                foreach ($this->monthsList as $m) {
                    $startLetter = Coordinate::stringFromColumnIndex($colIdx);
                    $endLetter = Coordinate::stringFromColumnIndex($colIdx + 3);
                    $sheet->mergeCells("{$startLetter}5:{$endLetter}5");
                    $sheet->setCellValue("{$startLetter}5", $m['label']);
                    
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx) . "6", 'H');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx + 1) . "6", 'S');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx + 2) . "6", 'I');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx + 3) . "6", 'A');
                    
                    $colIdx += 4;
                }

                $startTotal = Coordinate::stringFromColumnIndex($colIdx);
                $endTotal = Coordinate::stringFromColumnIndex($colIdx + 5);
                $sheet->mergeCells("{$startTotal}5:{$endTotal}5");
                $sheet->setCellValue("{$startTotal}5", 'TOTAL');
                
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx) . "6", 'H');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx + 1) . "6", 'T');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx + 2) . "6", 'S');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx + 3) . "6", 'I');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx + 4) . "6", 'A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx + 5) . "6", 'Telat (m)');

                $headingRowStyle = [
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ];
                $sheet->getStyle("A5:{$lastColLetter}6")->applyFromArray($headingRowStyle);

                // Data rows styling
                $totalDataRows = count($this->rows);
                if ($totalDataRows > 0) {
                    $lastRow = 6 + $totalDataRows;
                    for ($rowIdx = 7; $rowIdx <= $lastRow; $rowIdx++) {
                        $isEven = ($rowIdx % 2 === 0);
                        $bgColor = $isEven ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$rowIdx}:{$lastColLetter}{$rowIdx}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                        ]);

                        // Center numeric columns
                        $sheet->getStyle("D{$rowIdx}:{$lastColLetter}{$rowIdx}")->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Outer border for all data
                    $sheet->getStyle("A5:{$lastColLetter}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1E3A5F']],
                        ],
                    ]);
                }

                // Freeze pane at row 7 (after header)
                $sheet->freezePane('A7');
                $sheet->freezePane('D7');
            },
        ];
    }
}
