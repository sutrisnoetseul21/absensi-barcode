<?php

namespace Database\Seeders;

use App\Models\KlasifikasiDdc;
use Illuminate\Database\Seeder;

class KlasifikasiDdcSeeder extends Seeder
{
    /**
     * Seed data dasar Klasifikasi DDC (Dewey Decimal Classification) standar.
     */
    public function run(): void
    {
        $ddcData = [
            ['kode_ddc' => '000', 'kategori' => 'Karya Umum / Komputer & Informasi'],
            ['kode_ddc' => '100', 'kategori' => 'Filsafat & Psikologi'],
            ['kode_ddc' => '200', 'kategori' => 'Agama'],
            ['kode_ddc' => '297', 'kategori' => 'Agama Islam'],
            ['kode_ddc' => '300', 'kategori' => 'Ilmu-ilmu Sosial'],
            ['kode_ddc' => '370', 'kategori' => 'Pendidikan'],
            ['kode_ddc' => '400', 'kategori' => 'Bahasa & Linguistik'],
            ['kode_ddc' => '410', 'kategori' => 'Linguistik / Tata Bahasa'],
            ['kode_ddc' => '420', 'kategori' => 'Bahasa Inggris'],
            ['kode_ddc' => '500', 'kategori' => 'Sains & Matematika (Ilmu Murni)'],
            ['kode_ddc' => '510', 'kategori' => 'Matematika'],
            ['kode_ddc' => '530', 'kategori' => 'Fisika'],
            ['kode_ddc' => '540', 'kategori' => 'Kimia'],
            ['kode_ddc' => '570', 'kategori' => 'Biologi'],
            ['kode_ddc' => '600', 'kategori' => 'Teknologi & Ilmu Terapan'],
            ['kode_ddc' => '610', 'kategori' => 'Kesehatan & Kedokteran'],
            ['kode_ddc' => '620', 'kategori' => 'Teknik & Rekayasa'],
            ['kode_ddc' => '700', 'kategori' => 'Kesenian, Hiburan & Olahraga'],
            ['kode_ddc' => '800', 'kategori' => 'Kesusastraan & Sastra'],
            ['kode_ddc' => '810', 'kategori' => 'Sastra Indonesia'],
            ['kode_ddc' => '900', 'kategori' => 'Sejarah & Geografi'],
        ];

        foreach ($ddcData as $item) {
            KlasifikasiDdc::updateOrCreate(
                ['kode_ddc' => $item['kode_ddc']],
                ['kategori' => $item['kategori']]
            );
        }
    }
}
