<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * Export laporan hasil import siswa baru (PPDB).
 *
 * Kolom: No | NISN | NIS | Nama | Kelas | Status | Keterangan
 *
 * Warna baris:
 *   - Hijau  : Berhasil (siswa + enrollment terbuat)
 *   - Biru   : Berhasil tanpa kelas (siswa masuk, enrollment manual)
 *   - Kuning : Peringatan (siswa masuk, kelas tidak valid / tidak ada TA aktif)
 *   - Merah  : Skip (duplikat NISN, duplikat NIS, duplikat dalam file)
 */
class SiswaImportLaporanExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    /**
     * @param array $rows Array of result rows, each row:
     *   ['nisn', 'nis', 'name', 'kelas', 'status', 'keterangan']
     *   status: 'berhasil' | 'berhasil_tanpa_kelas' | 'warning' | 'skip'
     */
    public function __construct(private array $rows) {}

    public function array(): array
    {
        $result = [];
        foreach ($this->rows as $i => $row) {
            $result[] = [
                $i + 1,
                $row['nisn']       ?? '',
                $row['nis']        ?? '',
                $row['name']       ?? '',
                $row['kelas']      ?? '',
                $row['status_label'] ?? '',
                $row['keterangan'] ?? '',
            ];
        }
        return $result;
    }

    public function headings(): array
    {
        return ['No', 'NISN', 'NIS', 'Nama Siswa', 'Kelas', 'Status', 'Keterangan'];
    }

    public function title(): string
    {
        return 'Laporan Import Siswa';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 20,
            'C' => 20,
            'D' => 35,
            'E' => 12,
            'F' => 30,
            'G' => 55,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Bold header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF374151']],
        ];

        $totalRows = count($this->rows);

        // Style per baris data
        foreach ($this->rows as $i => $row) {
            $excelRow = $i + 2; // +2 karena baris 1 = header
            $status   = $row['status'] ?? '';

            $bgColor = match ($status) {
                'berhasil'           => 'FFD1FAE5', // hijau muda
                'berhasil_tanpa_kelas' => 'FFDBEAFE', // biru muda
                'warning'            => 'FFFEF3C7', // kuning muda
                'skip'               => 'FFFEE2E2', // merah muda
                default              => 'FFFFFFFF',
            };

            $sheet->getStyle("A{$excelRow}:G{$excelRow}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $bgColor],
                ],
            ]);
        }

        return [
            1 => $headerStyle,
        ];
    }
}
