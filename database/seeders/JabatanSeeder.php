<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\PengaturanSekolah;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolLevel = PengaturanSekolah::current()?->school_level ?? 'SMP';

        foreach (Jabatan::standarByJenjang($schoolLevel) as $jabatan) {
            Jabatan::firstOrCreate(['nama_jabatan' => $jabatan]);
        }
    }
}
