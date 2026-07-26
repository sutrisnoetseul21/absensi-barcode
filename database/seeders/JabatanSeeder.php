<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            'Kepala Sekolah',
            'Waka Kurikulum',
            'Waka Kesiswaan',
            'Waka Sarpras',
            'Waka Humas',
            'Guru BK',
            'Operator',
            'Tata Usaha',
            'Pustakawan',
            'Pembina Ekstrakurikuler',
            'Kepala Lab',
        ];

        foreach ($jabatans as $jabatan) {
            \App\Models\Jabatan::firstOrCreate(['nama_jabatan' => $jabatan]);
        }
    }
}
