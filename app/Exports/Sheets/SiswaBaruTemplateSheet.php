<?php

namespace App\Exports\Sheets;

use App\Models\PengaturanSekolah;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Template Excel untuk Import Siswa Baru (PPDB).
 *
 * Kolom Kelas (opsional) ditaruh di kolom ke-4 (D) tepat setelah Nama Siswa agar mudah diisi.
 * Jika kolom Kelas diisi, siswa akan otomatis didaftarkan ke kelas tersebut
 * pada tahun ajaran aktif. Jika kosong, enrollment dilakukan manual via
 * menu Pendaftaran Kelas.
 *
 * Kolom Kelas Masuk (kolom N / admission_class untuk biodata rapor) disembunyikan (hidden):
 * bila kosong saat import, otomatis diisi sesuai jenjang sekolah (SD: 1, SMP: 7, SMA/SMK: 10).
 *
 * Aturan validasi:
 * - NISN atau NIS yang sudah ada di database → SKIP + laporan warning
 * - NISN kembar dalam satu file → kedua baris di-SKIP + laporan warning
 * - Nama kelas tidak valid → siswa disimpan, enrollment dilewati + laporan warning
 */
class SiswaBaruTemplateSheet implements WithHeadings, WithTitle, WithEvents
{
    protected function exampleKelas(): string
    {
        $level = strtoupper((string) (PengaturanSekolah::current()?->school_level ?? 'SMP'));

        return match ($level) {
            'SD'         => '1A',
            'SMA', 'SMK' => '10A',
            default      => '7A',
        };
    }

    public function headings(): array
    {
        return [
            'NISN *',
            'NIS',
            'Nama Siswa *',
            'Kelas Saat Ini',
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
                $sheet->getColumnDimension('A')->setWidth(20); // NISN
                $sheet->getColumnDimension('B')->setWidth(20); // NIS
                $sheet->getColumnDimension('C')->setWidth(30); // Nama Siswa
                $sheet->getColumnDimension('D')->setWidth(28); // Kelas (Opsional)
                $sheet->getColumnDimension('E')->setWidth(20); // Tempat Lahir
                $sheet->getColumnDimension('F')->setWidth(28); // Tanggal Lahir
                $sheet->getColumnDimension('G')->setWidth(40); // Alamat
                $sheet->getColumnDimension('H')->setWidth(25); // No. HP Siswa
                $sheet->getColumnDimension('I')->setWidth(42); // Password
                $sheet->getColumnDimension('J')->setWidth(20); // Jenis Kelamin
                $sheet->getColumnDimension('K')->setWidth(20); // Agama
                $sheet->getColumnDimension('L')->setWidth(25); // Asal Sekolah
                $sheet->getColumnDimension('M')->setWidth(28); // Tanggal Masuk
                $sheet->getColumnDimension('N')->setWidth(20); // Kelas Masuk (Hidden)
                $sheet->getColumnDimension('N')->setVisible(false);
                $sheet->getColumnDimension('O')->setWidth(20); // Status Keluarga
                $sheet->getColumnDimension('P')->setWidth(15); // Anak Ke-
                $sheet->getColumnDimension('Q')->setWidth(30); // Nama Ayah
                $sheet->getColumnDimension('R')->setWidth(25); // Pekerjaan Ayah
                $sheet->getColumnDimension('S')->setWidth(30); // Nama Ibu
                $sheet->getColumnDimension('T')->setWidth(25); // Pekerjaan Ibu
                $sheet->getColumnDimension('U')->setWidth(30); // Nama Wali
                $sheet->getColumnDimension('V')->setWidth(25); // Pekerjaan Wali
                $sheet->getColumnDimension('W')->setWidth(25); // No. HP Orang Tua

                // Style header baris 1
                $sheet->getStyle('A1:W1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1E3A5F'],
                    ],
                ]);

                // Kolom Kelas (D) beri warna latar berbeda agar menonjol sebagai opsional
                $sheet->getStyle('D1')->applyFromArray([
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF2563EB'],
                    ],
                ]);

                // Baris contoh (baris 2) sebagai placeholder
                $exampleData = [
                    '1234567890', '2024001', 'Budi Santoso', $this->exampleKelas(),
                    'Jakarta', '2012-05-15', 'Jl. Merdeka No. 1',
                    '081234567890', '', 'L', 'Islam', 'SMP 1', '2024-07-15', '',
                    'Anak Kandung', '1',
                    'Andi', 'PNS', 'Siti', 'Ibu Rumah Tangga', '', '', '081987654321',
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
