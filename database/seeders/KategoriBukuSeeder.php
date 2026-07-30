<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KategoriBuku;

class KategoriBukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['Referensi', 'Fiksi', 'Non Fiksi'];

        foreach ($kategori as $item) {
            KategoriBuku::firstOrCreate(['nama_kategori' => $item]);
        }
    }
}
