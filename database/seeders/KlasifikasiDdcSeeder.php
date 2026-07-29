<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KlasifikasiDdcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ddcs = [
            ['kode_ddc' => '000', 'kategori' => 'Karya Umum, Informatika, Ilmu Komputer'],
            ['kode_ddc' => '100', 'kategori' => 'Filsafat dan Psikologi'],
            ['kode_ddc' => '200', 'kategori' => 'Agama'],
            ['kode_ddc' => '300', 'kategori' => 'Ilmu Sosial'],
            ['kode_ddc' => '400', 'kategori' => 'Bahasa'],
            ['kode_ddc' => '500', 'kategori' => 'Sains dan Matematika'],
            ['kode_ddc' => '600', 'kategori' => 'Teknologi dan Ilmu Terapan'],
            ['kode_ddc' => '700', 'kategori' => 'Kesenian dan Rekreasi'],
            ['kode_ddc' => '800', 'kategori' => 'Sastra'],
            ['kode_ddc' => '900', 'kategori' => 'Sejarah dan Geografi'],
        ];

        foreach ($ddcs as $ddc) {
            \App\Models\KlasifikasiDdc::firstOrCreate(
                ['kode_ddc' => $ddc['kode_ddc']],
                ['kategori' => $ddc['kategori']]
            );
        }
    }
}
