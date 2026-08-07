<?php

namespace App\Support;

/**
 * Helper untuk mapping kode DDC (Dewey Decimal Classification)
 * ke nama kategori Bahasa Indonesia.
 *
 * Dipakai di:
 * - App\Exports\SlimsDdcExport  (saat generate file XLS dari SLiMS)
 * - App\Imports\SlimsDdcImport  (saat baca file XLS dan masukkan ke ERP)
 *
 * Fungsi ini di-extract dari SlimsMigrationService agar tidak ada duplikasi
 * logic di dua tempat yang berbeda.
 */
class DdcHelper
{
    /**
     * Validasi apakah kode DDC murni numerik (boleh 1 titik desimal).
     * Regex: harus diawali digit, boleh ada titik, lanjut digit lagi.
     * Contoh valid  : "510", "420.1", "000", "797.2"
     * Contoh TIDAK valid: "SR 300 SUW s", "150 NOL a", "5516", "2 X 0"
     */
    public static function isValidDdcCode(string $kode): bool
    {
        return (bool) preg_match('/^\d+(\.\d+)?$/', trim($kode));
    }

    /**
     * Mapping kode DDC ke nama kategori Bahasa Indonesia.
     * Logika: cek sub-kelas spesifik dulu, fallback ke kelas utama (kelipatan 100).
     * Jika kode tidak bisa diparsing sebagai angka, kembalikan 'Lain-lain'.
     */
    public static function getNamaKategori(string $kode): string
    {
        $bersih = trim($kode);

        // Ambil angka di depan (hilangkan suffix seperti "SR 300 SUW s")
        preg_match('/^(\d+\.?\d*)/', $bersih, $matches);
        if (empty($matches[1])) {
            return 'Lain-lain';
        }

        $angka    = (float) $matches[1];
        $intAngka = (int) $angka;

        // Sub-kelas spesifik
        $spesifik = [
            297 => 'Agama Islam',
            300 => 'Ilmu Sosial Umum',
            323 => 'Kewarganegaraan',
            370 => 'Pendidikan',
            398 => 'Folklore & Cerita Rakyat',
            410 => 'Bahasa Indonesia',
            413 => 'Kamus & Ensiklopedia Bahasa',
            420 => 'Bahasa Inggris',
            500 => 'Ilmu Pengetahuan Alam',
            507 => 'Prakarya & Sains Terapan',
            510 => 'Matematika',
            520 => 'Astronomi',
            530 => 'Fisika',
            540 => 'Kimia',
            550 => 'Ilmu Bumi & Geologi',
            570 => 'Biologi',
            580 => 'Botani & Tumbuhan',
            590 => 'Zoologi & Hewan',
            610 => 'Kedokteran & Kesehatan',
            613 => 'Kesehatan & Olahraga',
            620 => 'Teknik & Rekayasa',
            630 => 'Pertanian',
            640 => 'Kerumahtanggaan',
            650 => 'Manajemen & Bisnis',
            700 => 'Seni & Hiburan',
            707 => 'Kesenian & Seni Budaya',
            780 => 'Musik',
            790 => 'Olahraga & Rekreasi',
            796 => 'Pendidikan Jasmani & Olahraga',
            800 => 'Kesusastraan',
            810 => 'Sastra Indonesia',
            811 => 'Puisi Indonesia',
            813 => 'Fiksi & Novel',
            820 => 'Sastra Inggris',
            900 => 'Sejarah & Geografi',
            920 => 'Biografi',
        ];

        if (isset($spesifik[$intAngka])) {
            return $spesifik[$intAngka];
        }

        // Fallback ke kelas utama (kelipatan 100)
        $kelas = intdiv($intAngka, 100) * 100;
        return match (true) {
            $kelas === 0   => 'Karya Umum & Komputer',
            $kelas === 100 => 'Filsafat & Psikologi',
            $kelas === 200 => 'Agama',
            $kelas === 300 => 'Ilmu Sosial',
            $kelas === 400 => 'Bahasa & Linguistik',
            $kelas === 500 => 'Ilmu Pengetahuan Murni',
            $kelas === 600 => 'Ilmu Terapan & Teknologi',
            $kelas === 700 => 'Kesenian & Olahraga',
            $kelas === 800 => 'Kesusastraan',
            $kelas === 900 => 'Sejarah, Geografi & Biografi',
            default        => 'Lain-lain',
        };
    }
}
