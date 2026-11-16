<?php

namespace App\Exports;

use App\Models\PengaturanSekolah;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EkstrakurikulerRekapExport implements WithMultipleSheets
{
    protected array $reportData;
    protected string $periodeLabel;

    public function __construct(array $reportData, string $periodeLabel = 'Semua Pertemuan')
    {
        $this->reportData = $reportData;
        $this->periodeLabel = $periodeLabel;
    }

    public function sheets(): array
    {
        return [
            new EkstrakurikulerAbsensiSheet($this->reportData, $this->periodeLabel),
            new EkstrakurikulerJurnalSheet($this->reportData, $this->periodeLabel),
        ];
    }
}

class EkstrakurikulerAbsensiSheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
    public function __construct(protected array $reportData, protected string $periodeLabel) {}

    public function title(): string
    {
        return 'Rekap Kehadiran';
    }

    public function headings(): array
    {
        $headers = ['No', 'NISN', 'Nama Siswa', 'Kelas'];
        foreach ($this->reportData['meetings'] as $m) {
            $headers[] = "P{$m['pertemuan_ke']}\n({$m['tanggal_short']})";
        }

        return array_merge($headers, ['Hadir', 'Izin', 'Sakit', 'Alpa', '% Hadir']);
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->reportData['members'] as $mem) {
            $row = [$mem['no'], $mem['nisn'], $mem['name'], $mem['kelas']];
            foreach ($this->reportData['meetings'] as $m) {
                $row[] = $mem['daily'][$m['id']] ?? '-';
            }
            $row[] = $mem['hadir'];
            $row[] = $mem['izin'];
            $row[] = $mem['sakit'];
            $row[] = $mem['alpa'];
            $row[] = $mem['pct'] . '%';
            $rows[] = $row;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ekskul = $this->reportData['ekskul'];
                $sekolah = PengaturanSekolah::current();
                $totalCols = 4 + count($this->reportData['meetings']) + 5;
                $lastCol = Coordinate::stringFromColumnIndex($totalCols);

                $sheet->insertNewRowBefore(1, 4);
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN PRESENSI EKSTRAKURIKULER ' . strtoupper($ekskul->nama_ekskul));
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', strtoupper($sekolah?->school_name ?? 'SEKOLAH'));
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'Pembina: ' . ($ekskul->pembinas->pluck('name')->implode(', ') ?: '-') . ' | TA ' . ($ekskul->tahunAjaran?->name ?? '-'));
                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->setCellValue('A4', 'Periode: ' . $this->periodeLabel);
                $sheet->getStyle("A1:{$lastCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(28);

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(28);
                $sheet->getColumnDimension('D')->setWidth(12);
                for ($c = 5; $c <= $totalCols; $c++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(9);
                }

                $dataCount = count($this->reportData['members']);
                if ($dataCount > 0) {
                    $lastRow = 5 + $dataCount;
                    $sheet->getStyle("A6:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("D6:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}

class EkstrakurikulerJurnalSheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
    public function __construct(protected array $reportData, protected string $periodeLabel) {}

    public function title(): string
    {
        return 'Jurnal Pertemuan';
    }

    public function headings(): array
    {
        return ['Pertemuan', 'Tanggal', 'Judul / Materi Kegiatan', 'Catatan Pembina', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Status'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->reportData['meetings'] as $m) {
            $rows[] = [
                'Ke-' . $m['pertemuan_ke'],
                $m['tanggal_label'],
                $m['judul_kegiatan'],
                $m['catatan_pembina'],
                $m['hadir_count'],
                $m['izin_count'],
                $m['sakit_count'],
                $m['alpa_count'],
                ucfirst($m['status_sesi']),
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ekskul = $this->reportData['ekskul'];

                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'JURNAL PERTEMUAN EKSTRAKURIKULER ' . strtoupper($ekskul->nama_ekskul));
                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', 'Pembina: ' . ($ekskul->pembinas->pluck('name')->implode(', ') ?: '-') . ' | ' . $this->periodeLabel);
                $sheet->getStyle('A1:I2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A3:I3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(24);
                $sheet->getColumnDimension('C')->setWidth(32);
                $sheet->getColumnDimension('D')->setWidth(36);
                foreach (['E', 'F', 'G', 'H', 'I'] as $col) {
                    $sheet->getColumnDimension($col)->setWidth(10);
                }

                $dataCount = count($this->reportData['meetings']);
                if ($dataCount > 0) {
                    $lastRow = 3 + $dataCount;
                    $sheet->getStyle("A4:I{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);
                }
            },
        ];
    }
}

