<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebSarpra;

class WebSarpraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fasilitas = [
            [
                'name'  => '24 Ruang Kelas',
                'icon'  => 'fas fa-chalkboard-teacher',
                'color' => 'text-blue-500',
                'desc'  => 'Kelas nyaman & kondusif dengan fasilitas pendukung lengkap.',
            ],
            [
                'name'  => 'Perpustakaan (A)',
                'icon'  => 'fas fa-book-open',
                'color' => 'text-emerald-500',
                'desc'  => 'Terakreditasi A, koleksi ribuan buku, area baca nyaman & e-library.',
            ],
            [
                'name'  => 'Laboratorium IPA',
                'icon'  => 'fas fa-flask',
                'color' => 'text-purple-500',
                'desc'  => 'Peralatan praktikum fisika, kimia, biologi modern & aman.',
            ],
            [
                'name'  => 'Laboratorium TIK',
                'icon'  => 'fas fa-desktop',
                'color' => 'text-blue-500',
                'desc'  => 'Unit komputer spesifikasi terkini & akses internet kecepatan tinggi.',
            ],
            [
                'name'  => 'Mushola Luas',
                'icon'  => 'fas fa-mosque',
                'color' => 'text-nature-500',
                'desc'  => 'Pusat kegiatan ibadah, sholat berjamaah & pembinaan karakter islami.',
            ],
            [
                'name'  => 'Lapangan Olahraga',
                'icon'  => 'fas fa-basketball-ball',
                'color' => 'text-orange-500',
                'desc'  => 'Lapangan serbaguna untuk basket, voli, futsal & upacara bendera.',
            ],
            [
                'name'  => 'Taman Bermain & Edukasi',
                'icon'  => 'fas fa-tree',
                'color' => 'text-nature-500',
                'desc'  => 'Area terbuka hijau yang asri, sejuk, dan ramah lingkungan.',
            ],
            [
                'name'  => 'Kolam Ikan',
                'icon'  => 'fas fa-water',
                'color' => 'text-cyan-500',
                'desc'  => 'Sarana edukasi ekosistem air tawar & relaksasi siswa.',
            ],
            [
                'name'  => 'Ruang Kesenian',
                'icon'  => 'fas fa-music',
                'color' => 'text-pink-500',
                'desc'  => 'Alat musik tradisional & modern untuk pengembangan bakat seni.',
            ],
            [
                'name'  => 'Kantin Sehat',
                'icon'  => 'fas fa-utensils',
                'color' => 'text-red-500',
                'desc'  => 'Menyediakan jajanan bergizi, higienis & terstandarisasi kesehatan.',
            ],
            [
                'name'  => 'Ruang Guru & TU',
                'icon'  => 'fas fa-users',
                'color' => 'text-gray-500',
                'desc'  => 'Pusat pelayanan akademik, administrasi & konsultasi orang tua siswa.',
            ],
            [
                'name'  => 'Ruang BK',
                'icon'  => 'fas fa-comments',
                'color' => 'text-teal-500',
                'desc'  => 'Layanan bimbingan konseling dan konsultasi minat bakat siswa.',
            ],
        ];

        foreach ($fasilitas as $index => $item) {
            WebSarpra::updateOrCreate(
                ['nama_fasilitas' => $item['name']],
                [
                    'icon'       => $item['icon'],
                    'color'      => $item['color'],
                    'deskripsi'  => $item['desc'],
                    'urutan'     => $index + 1,
                ]
            );
        }
    }
}
