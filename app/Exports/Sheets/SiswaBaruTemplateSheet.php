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
            'No. HP Siswa',
            'Password (Kosongkan untuk default: NISN)',
            'Jenis Kelamin (L/P)',
            'Agama',
            'Asal Sekolah',
            'Tanggal Masuk (YYYY-MM-DD)',
            'Kelas Masuk',
            'Status Keluarga',
            'Anak Ke-',
            'Nama Ayah',
            'Pekerjaan Ayah',
            'Nama Ibu',
            'Pekerjaan Ibu',
            'Nama Wali',
            'Pekerjaan Wali',
            'No. HP Orang Tua',
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
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(42);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(20);
                $sheet->getColumnDimension('K')->setWidth(25);
                $sheet->getColumnDimension('L')->setWidth(28);
                $sheet->getColumnDimension('M')->setWidth(20);
                $sheet->getColumnDimension('N')->setWidth(20);
                $sheet->getColumnDimension('O')->setWidth(15);
                $sheet->getColumnDimension('P')->setWidth(30);
                $sheet->getColumnDimension('Q')->setWidth(25);
                $sheet->getColumnDimension('R')->setWidth(30);
                $sheet->getColumnDimension('S')->setWidth(25);
                $sheet->getColumnDimension('T')->setWidth(30);
                $sheet->getColumnDimension('U')->setWidth(25);
                $sheet->getColumnDimension('V')->setWidth(25);
                $sheet->getColumnDimension('W')->setWidth(28);

                // Style header baris 1
                $sheet->getStyle('A1:W1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1E3A5F'],
                    ],
                ]);

                // Kolom Kelas (W) beri warna latar berbeda agar menonjol sebagai opsional
                $sheet->getStyle('W1')->applyFromArray([
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF2563EB'],
                    ],
                ]);

                // Baris contoh (baris 2) sebagai placeholder
                $exampleData = [
                    '1234567890', '2024001', 'Budi Santoso',
                    'Jakarta', '2012-05-15', 'Jl. Merdeka No. 1',
                    '081234567890', '', 'L', 'Islam', 'SMP 1', '2024-07-15', '10A', 'Anak Kandung', '1',
                    'Andi', 'PNS', 'Siti', 'Ibu Rumah Tangga', '', '', '081987654321', '10A',
                ];
                foreach ($exampleData as $col => $val) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '2';
                    $sheet->setCellValue($cell, $val);
                }
                $sheet->getStyle('A2:W2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF9CA3AF']],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF9FAFB'],
                    ],
                ]);


            },
        ];
    }
}
