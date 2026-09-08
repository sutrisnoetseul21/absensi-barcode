<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebFaq;

class WebFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // SPMB
            [
                'kategori'   => 'SPMB',
                'pertanyaan' => 'Apa saja dokumen yang harus disiapkan untuk pendaftaran SPMB?',
                'jawaban'    => 'Dokumen yang perlu disiapkan antara lain: Fotokopi Akta Kelahiran, Fotokopi Kartu Keluarga (KK), Surat Keterangan Lulus (SKL) / Ijazah SD/MI, Pas foto terbaru ukuran 3x4 (3 lembar), serta sertifikat atau piagam prestasi asli dan fotokopi (khusus pendaftar jalur prestasi).',
                'urutan'     => 1,
                'is_active'  => true,
            ],
            [
                'kategori'   => 'SPMB',
                'pertanyaan' => 'Berapa biaya pendaftaran SPMB?',
                'jawaban'    => 'Pendaftaran peserta didik baru di sekolah negeri tidak dipungut biaya apapun (Gratis 100%). Seluruh rangkaian tahapan seleksi dan pendaftaran dibiayai oleh dana operasional sekolah.',
                'urutan'     => 2,
                'is_active'  => true,
            ],
            [
                'kategori'   => 'SPMB',
                'pertanyaan' => 'Jalur apa saja yang dibuka dalam SPMB?',
                'jawaban'    => 'Jalur penerimaan peserta didik baru terdiri dari 4 jalur: Jalur Zonasi (berdasarkan jarak domisili), Jalur Afirmasi (keluarga ekonomi tidak mampu & penyandang disabilitas), Jalur Prestasi (akademik & non-akademik), serta Jalur Perpindahan Tugas Orang Tua/Wali.',
                'urutan'     => 3,
                'is_active'  => true,
            ],
            [
                'kategori'   => 'SPMB',
                'pertanyaan' => 'Kapan jadwal pelaksanaan SPMB dibuka?',
                'jawaban'    => 'Jadwal pendaftaran biasanya dibuka pada bulan Mei - Juni menjelang tahun ajaran baru. Pengumuman resmi dan petunjuk teknis dapat dipantau melalui portal web sekolah atau media sosial resmi kami.',
                'urutan'     => 4,
                'is_active'  => true,
            ],

            // Layanan Administrasi
            [
                'kategori'   => 'Layanan Administrasi',
                'pertanyaan' => 'Bagaimana prosedur permohonan legalisir ijazah?',
                'jawaban'    => 'Alumni cukup membawa dokumen ijazah asli beserta fotokopi yang hendak dilegalisir langsung ke ruang Pelayanan Tata Usaha (TU) pada hari kerja (Senin - Jumat pukul 07.30 - 14.00 WIB). Proses legalisir tidak dipungut biaya dan selesai dalam estimasi 15-30 menit.',
                'urutan'     => 5,
                'is_active'  => true,
            ],
            [
                'kategori'   => 'Layanan Administrasi',
                'pertanyaan' => 'Bagaimana alur pengajuan surat pindah / mutasi siswa?',
                'jawaban'    => 'Orang tua/wali murid mengajukan surat permohonan mutasi ke bagian Tata Usaha sekolah dengan melampirkan: Surat rekomendasi/keterangan siap menerima dari sekolah tujuan, fotokopi buku rapor terakhir, dan fotokopi Kartu Keluarga.',
                'urutan'     => 6,
                'is_active'  => true,
            ],
            [
                'kategori'   => 'Layanan Administrasi',
                'pertanyaan' => 'Bagaimana cara orang tua memantau kehadiran dan nilai siswa?',
                'jawaban'    => 'Orang tua dapat memantau presensi harian siswa secara transparan melalui sistem barcode sekolah serta menerima laporan berkala dari wali kelas maupun berkonsultasi langsung dengan guru bimbingan konseling (BK).',
                'urutan'     => 7,
                'is_active'  => true,
            ],

            // Ekstrakurikuler
            [
                'kategori'   => 'Ekstrakurikuler',
                'pertanyaan' => 'Apa saja pilihan kegiatan ekstrakurikuler yang tersedia di sekolah?',
                'jawaban'    => 'Sekolah menyediakan kegiatan ekstrakurikuler wajib (Pramuka) serta berbagai ekstrakurikuler pilihan meliputi: PMR, Paskibra, Futsal, Bola Voli, Bola Basket, Seni Tari, Musik & Gamelan, Paduan Suara, Rohis, dan Klub Komputer / TIK.',
                'urutan'     => 8,
                'is_active'  => true,
            ],
            [
                'kategori'   => 'Ekstrakurikuler',
                'pertanyaan' => 'Kapan jadwal latihan kegiatan ekstrakurikuler dilaksanakan?',
                'jawaban'    => 'Latihan ekstrakurikuler dilaksanakan sepulang sekolah pada sore hari (pukul 15.00 - 17.00 WIB) sesuai jadwal bidang masing-masing agar tidak mengganggu jam kegiatan belajar mengajar utama.',
                'urutan'     => 9,
                'is_active'  => true,
            ],

            // Perpustakaan
            [
                'kategori'   => 'Perpustakaan',
                'pertanyaan' => 'Bagaimana syarat meminjam buku di perpustakaan sekolah?',
                'jawaban'    => 'Siswa cukup menunjukkan Kartu Pelajar ber-barcode kepada petugas perpustakaan. Setiap siswa berhak meminjam hingga 3 eksemplar buku pelajaran atau referensi dalam jangka waktu 1 pekan dan dapat diperpanjang.',
                'urutan'     => 10,
                'is_active'  => true,
            ],
            [
                'kategori'   => 'Perpustakaan',
                'pertanyaan' => 'Apakah perpustakaan menyediakan fasilitas komputer dan internet?',
                'jawaban'    => 'Ya, perpustakaan dilengkapi dengan area baca representatif, fasilitas komputer pencarian katalog (OPAC), serta akses internet kecepatan tinggi untuk menunjang kebutuhan belajar mandiri dan e-library siswa.',
                'urutan'     => 11,
                'is_active'  => true,
            ],

            // UKS & Kesehatan
            [
                'kategori'   => 'UKS & Kesehatan',
                'pertanyaan' => 'Layanan apa saja yang disediakan oleh ruang UKS sekolah?',
                'jawaban'    => 'UKS sekolah menyediakan fasilitas pertolongan pertama pada kecelakaan (P3K), obat-obatan esensial, tempat tidur istirahat sementara bagi siswa yang sakit, penimbangan & pengukuran tumbuh kembang berkala, serta rujukan cepat ke Puskesmas terdekat jika memerlukan penanganan medis lanjutan.',
                'urutan'     => 12,
                'is_active'  => true,
            ],
            [
                'kategori'   => 'UKS & Kesehatan',
                'pertanyaan' => 'Bagaimana prosedur jika siswa mengalami sakit saat jam pelajaran?',
                'jawaban'    => 'Siswa yang merasa kurang sehat akan didampingi guru kelas atau petugas piket menuju ruang UKS untuk diperiksa. Jika membutuhkan istirahat intensif di rumah, pihak sekolah akan segera menghubungi orang tua/wali siswa.',
                'urutan'     => 13,
                'is_active'  => true,
            ],
        ];

        foreach ($faqs as $item) {
            WebFaq::updateOrCreate(
                ['pertanyaan' => $item['pertanyaan']],
                $item
            );
        }
    }
}
