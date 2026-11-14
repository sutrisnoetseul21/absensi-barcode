<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    public function run(): void
    {
        $level = PengaturanSekolah::current()?->school_level ?: 'SMP';
        $items = MataPelajaran::standardByJenjang($level);

        foreach ($items as $item) {
            MataPelajaran::firstOrCreate(
                ['nama_mapel' => $item['nama_mapel']],
                [
                    'kode_mapel'  => $item['kode_mapel'],
                    'kategori'    => $item['kategori'],
                    'kkm_default' => 75.00,
                ]
            );
        }
    }
}

