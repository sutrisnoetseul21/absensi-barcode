<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelasList = [
            ['name' => '7A', 'grade_level' => 7],
            ['name' => '7B', 'grade_level' => 7],
            ['name' => '7C', 'grade_level' => 7],
            ['name' => '8A', 'grade_level' => 8],
            ['name' => '8B', 'grade_level' => 8],
            ['name' => '8C', 'grade_level' => 8],
            ['name' => '9A', 'grade_level' => 9],
            ['name' => '9B', 'grade_level' => 9],
            ['name' => '9C', 'grade_level' => 9],
        ];

        foreach ($kelasList as $kelas) {
            Kelas::firstOrCreate(
                ['name' => $kelas['name']], // Pastikan tidak duplikat
                ['grade_level' => $kelas['grade_level']]
            );
        }
    }
}
