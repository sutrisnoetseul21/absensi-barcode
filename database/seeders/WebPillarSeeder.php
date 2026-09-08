<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebPillar;

class WebPillarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pillars = [
            [
                'title'       => 'Digital Smart School',
                'tag'         => 'Teknologi & Inovasi',
                'desc'        => 'Presensi barcode digital terpadu, e-library modern, dan digitalisasi layanan administrasi sekolah.',
                'icon'        => 'fas fa-laptop-code',
                'color_theme' => 'blue',
                'link'        => null,
                'urutan'      => 1,
                'is_active'   => true,
            ],
            [
                'title'       => 'Penguatan Karakter',
                'tag'         => 'Budi Pekerti Luhur',
                'desc'        => 'Pembiasaan akhlak mulia, literasi pagi, kedisiplinan, dan keteladanan religius berkesinambungan.',
                'icon'        => 'fas fa-hands-praying',
                'color_theme' => 'emerald',
                'link'        => null,
                'urutan'      => 2,
                'is_active'   => true,
            ],
            [
                'title'       => 'Pengembangan Potensi',
                'tag'         => 'Prestasi & Bakat',
                'desc'        => 'Wadah optimalisasi bakat akademik, sains, seni budaya, serta olahraga untuk mencetak generasi juara.',
                'icon'        => 'fas fa-trophy',
                'color_theme' => 'amber',
                'link'        => null,
                'urutan'      => 3,
                'is_active'   => true,
            ],
            [
                'title'       => 'Sekolah Ramah & Asri',
                'tag'         => 'Lingkungan Harmonis',
                'desc'        => 'Ekosistem belajar yang inklusif, aman dari perundungan, ramah anak, dan berwawasan adiwiyata.',
                'icon'        => 'fas fa-seedling',
                'color_theme' => 'teal',
                'link'        => null,
                'urutan'      => 4,
                'is_active'   => true,
            ],
        ];

        foreach ($pillars as $item) {
            WebPillar::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
