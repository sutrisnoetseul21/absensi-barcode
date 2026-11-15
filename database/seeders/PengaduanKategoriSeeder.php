<?php

namespace Database\Seeders;

use App\Models\PengaduanKategori;
use Illuminate\Database\Seeder;

class PengaduanKategoriSeeder extends Seeder
{
    /**
     * Seed kategori bawaan untuk modul Layanan Aspirasi & Pengaduan (/pengaduan).
     */
    public function run(): void
    {
        $categories = [
            ['nama_kategori' => 'Pelayanan Administrasi & TU', 'urutan' => 1],
            ['nama_kategori' => 'Sarana & Prasarana', 'urutan' => 2],
            ['nama_kategori' => 'Kurikulum & Pembelajaran', 'urutan' => 3],
            ['nama_kategori' => 'Kesiswaan & Kedisiplinan', 'urutan' => 4],
            ['nama_kategori' => 'Saran & Masukan Lainnya', 'urutan' => 5],
        ];

        foreach ($categories as $category) {
            PengaduanKategori::firstOrCreate(
                ['nama_kategori' => $category['nama_kategori']],
                ['urutan' => $category['urutan']]
            );
        }
    }
}

