<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Template Excel untuk Import Siswa Baru (PPDB).
 *
 * Kolom Kelas ditambahkan sebagai kolom ke-8 (opsional).
 * Jika kolom Kelas diisi, siswa akan otomatis didaftarkan ke kelas tersebut
 * pada tahun ajaran aktif. Jika kosong, enrollment dilakukan manual via
 * menu Pendaftaran Kelas.
 *
 * Aturan validasi:
 * - NISN atau NIS yang sudah ada di database → SKIP + laporan warning
 * - NISN kembar dalam satu file → kedua baris di-SKIP + laporan warning
 * - Nama kelas tidak valid → siswa disimpan, enrollment dilewati + laporan warning
 */
class SiswaBaruTemplateSheet implements WithHeadings, WithTitle, WithEvents
{
    public function headings(): array
    {
        return [
            'NISN',
            'NIS',
            'Nama Siswa',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Alamat',
            'Password (Kosongkan untuk default: NISN)',
            'Kelas (Opsional, contoh: 7A)',
        ];
    }

    public function title(): string
    {
        return 'Template Siswa Baru';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(28);
                $sheet->getColumnDimension('F')->setWidth(40);
                $sheet->getColumnDimension('G')->setWidth(42);
                $sheet->getColumnDimension('H')->setWidth(28);

                // Style header baris 1
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1E3A5F'],
                    ],
                ]);

                // Kolom Kelas (H) beri warna latar berbeda agar menonjol sebagai opsional
                $sheet->getStyle('H1')->applyFromArray([
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF2563EB'],
                    ],
                ]);

                // Baris contoh (baris 2) sebagai placeholder
                $exampleData = [
                    '1234567890', '2024001', 'Budi Santoso',
                    'Jakarta', '2012-05-15', 'Jl. Merdeka No. 1',
                    '', '7A',
                ];
                foreach ($exampleData as $col => $val) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '2';
                    $sheet->setCellValue($cell, $val);
                }
                $sheet->getStyle('A2:H2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF9CA3AF']],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF9FAFB'],
                    ],
                ]);

                // Catatan di baris 4
                $sheet->setCellValue('A4', '⚠ CATATAN: Baris ke-2 adalah contoh, hapus sebelum import.');
                $sheet->setCellValue('A5', '⚠ Kolom Kelas (H) opsional. Isi nama kelas persis seperti di sistem (contoh: 7A, 8B, 9C).');
                $sheet->setCellValue('A6', '⚠ Jika NISN atau NIS sudah ada di database, baris tersebut akan dilewati (skip).');
                $sheet->getStyle('A4:A6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFB45309']],
                ]);
            },
        ];
    }
}
