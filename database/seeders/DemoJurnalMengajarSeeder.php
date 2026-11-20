<?php

namespace Database\Seeders;

use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\JurnalPembelajaran;
use App\Models\JurnalPembelajaranPresensi;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoJurnalMengajarSeeder extends Seeder
{
    /**
     * Mengisi minimal 2 Jurnal Mengajar KBM untuk setiap guru aktif pada Tahun Ajaran berjalan,
     * lengkap dengan snapshot presensi siswa di kelas.
     */
    public function run(): void
    {
        $schoolSetting = PengaturanSekolah::current();
        $activeYearId = $schoolSetting?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->value('id');

        if (!$activeYearId) {
            $this->command?->error('Tahun ajaran aktif tidak ditemukan.');
            return;
        }

        $gurus = Guru::with(['user', 'pengajarans.kelasAjaran.kelas', 'pengajarans.mataPelajaran'])
            ->orderBy('name')
            ->get();

        if ($gurus->isEmpty()) {
            $this->command?->error('Data guru kosong.');
            return;
        }

        // Ambil 2 hari efektif sekolah terakhir (termasuk hari ini bila hari sekolah)
        $workDays = $this->resolveRecentSchoolDays(2, $schoolSetting?->work_days_type ?? '5_hari');

        $materiBank = [
            'PAI' => [
                ['materi' => 'Meneladani Kejujuran dan Amanah dalam Kehidupan Sehari-hari', 'uraian' => 'Siswa membaca QS. Al-Anfal: 27, berdiskusi studi kasus perilaku amanah di lingkungan sekolah, dan mempresentasikan kesimpulan kelompok.', 'refleksi' => 'Siswa aktif berdiskusi dan mampu memberi contoh nyata penerapan sikap jujur di kelas.'],
                ['materi' => 'Tata Cara Sholat Berjamaah dan Keutamaannya', 'uraian' => 'Penjelasan syarat sah, bacaan niat, serta praktik posisi makmum masbuk dalam sholat berjamaah di mushola sekolah.', 'refleksi' => 'Mayoritas siswa sudah menguasai gerakan dan tata cara makmum masbuk dengan baik.'],
            ],
            'PPKN' => [
                ['materi' => 'Perumusan dan Penetapan Pancasila sebagai Dasar Negara', 'uraian' => 'Mengulas sidang BPUPKI, membandingkan rumusan dasar negara para tokoh nasional, dan menyusun lini masa sejarah perumusan Pancasila.', 'refleksi' => 'Pembelajaran berjalan kondusif; siswa antusias menyusun bagan sejarah BPUPKI.'],
                ['materi' => 'Norma dan Keadilan dalam Kehidupan Bermasyarakat', 'uraian' => 'Identifikasi 4 macam norma (agama, kesusilaan, kesopanan, hukum) melalui studi kasus tata tertib sekolah.', 'refleksi' => 'Siswa memahami perbedaan sanksi tiap norma dan menyepakati komitmen tertib kelas.'],
            ],
            'BIND' => [
                ['materi' => 'Menelaah Struktur dan Kebahasaan Teks Deskripsi', 'uraian' => 'Membaca teks deskripsi objek wisata daerah, mengidentifikasi identifikasi-deskripsi bagian-simpulan, serta berlatih menggunakan majas dan kata konkret.', 'refleksi' => 'Siswa mampu menemukan ciri kebahasaan teks deskripsi dengan tepat.'],
                ['materi' => 'Menyusun Teks Prosedur yang Efektif dan Komunikatif', 'uraian' => 'Menganalisis kalimat perintah, konjungsi temporal, serta praktik menulis teks prosedur pembuatan kerajinan sederhana.', 'refleksi' => 'Hasil tulisan siswa rapi dan runtut sesuai struktur tujuan, alat/bahan, dan langkah.'],
            ],
            'MTK' => [
                ['materi' => 'Operasi Hitung Bilangan Bulat dan Pecahan dalam Soal Cerita', 'uraian' => 'Pembahasan konsep garis bilangan, penjumlahan-pengurangan pecahan campuran, dan latihan 8 soal kontekstual secara berpasangan.', 'refleksi' => 'Perlu penguatan tambahan pada pengurangan bilangan bulat negatif untuk beberapa siswa.'],
                ['materi' => 'Persamaan dan Pertidaksamaan Linear Satu Variabel (PLSV)', 'uraian' => 'Menyelesaikan model matematika dari permasalahan sehari-hari menggunakan sifat kesetaraan persamaan linear satu variabel.', 'refleksi' => 'Siswa cepat memahami konsep timbangan persamaan dan mampu menyelesaikan latihan mandiri.'],
            ],
            'IPA' => [
                ['materi' => 'Besaran Pokok, Besaran Turunan, dan Pengukuran Baku', 'uraian' => 'Praktikum pengukuran panjang benda menggunakan mistar dan jangka sorong, serta konversi satuan SI di ruang kelas.', 'refleksi' => 'Praktikum pengukuran berlangsung tertib; siswa antusias membaca skala jangka sorong.'],
                ['materi' => 'Klasifikasi Makhluk Hidup dan Kunci Determinasi Sederhana', 'uraian' => 'Mengamati ciri morfologi tumbuhan di halaman sekolah dan mengelompokkannya menggunakan kunci dikotomi.', 'refleksi' => 'Siswa berhasil menyusun kunci determinasi sederhana untuk 5 jenis daun.'],
            ],
            'IPS' => [
                ['materi' => 'Kondisi Geografis Indonesia dan Interaksi Antarruang', 'uraian' => 'Membaca peta tematik letak astronomis-geografis Indonesia dan mendiskusikan pengaruhnya terhadap iklim serta aktivitas ekonomi.', 'refleksi' => 'Pemanfaatan peta membantu siswa memahami pembagian zona waktu WIB, WITA, dan WIT.'],
                ['materi' => 'Lembaga Sosial dan Perannya dalam Ketertiban Masyarakat', 'uraian' => 'Diskusi kelompok mengenai fungsi keluarga, sekolah, dan lembaga ekonomi dalam memenuhi kebutuhan masyarakat.', 'refleksi' => 'Setiap kelompok mempresentasikan contoh peran lembaga pendidikan dengan baik.'],
            ],
            'BING' => [
                ['materi' => 'Greeting, Leave-Taking, Thanking, and Apologizing Expressions', 'uraian' => 'Listening dialogue practice, role-play percakapan berpasangan di depan kelas, dan latihan menyusun dialog singkat.', 'refleksi' => 'Siswa percaya diri mempraktikkan ungkapan sapaan dengan pelafalan yang semakin baik.'],
                ['materi' => 'Descriptive Text: Describing People and Favorite Places', 'uraian' => 'Mempelajari kosakata kata sifat (adjectives), simple present tense, dan menulis paragraf deskripsi singkat tentang teman sebangku.', 'refleksi' => 'Kreativitas kosakata siswa meningkat dan struktur kalimat sudah sesuai tense.'],
            ],
            'PJOK' => [
                ['materi' => 'Teknik Dasar Passing Bawah dan Passing Atas Bola Voli', 'uraian' => 'Pemanasan dinamis, demonstrasi sikap tangan dan perkenaan bola, serta latihan passing berpasangan di lapangan.', 'refleksi' => 'Seluruh siswa mengikuti praktik lapangan dengan semangat dan memperhatikan keselamatan.'],
                ['materi' => 'Kebugaran Jasmani: Latihan Daya Tahan dan Kelincahan', 'uraian' => 'Circuit training ringan (lari bolak-balik, sit-up, push-up modifikasi) dan pengukuran denyut nadi sebelum serta sesudah latihan.', 'refleksi' => 'Siswa memahami cara mengukur denyut nadi latihan dan menjaga ritme napas.'],
            ],
            'INF' => [
                ['materi' => 'Berpikir Komputasional: Dekomposisi dan Pengenalan Pola', 'uraian' => 'Memecahkan masalah penjadwalan kegiatan harian ke dalam langkah terstruktur (algoritma sederhana) dan mencari pola berulang.', 'refleksi' => 'Siswa mampu menyusun langkah algoritma yang logis dari kasus sehari-hari.'],
                ['materi' => 'Pengolahan Dokumen dan Kolaborasi Digital Aman', 'uraian' => 'Praktik membuat dokumen terstruktur (heading, tabel, daftar bernomor) serta etika keamanan akun di internet.', 'refleksi' => 'Hasil dokumen siswa memenuhi format tata letak yang ditentukan.'],
            ],
            'SBK' => [
                ['materi' => 'Menggambar Flora, Fauna, dan Alam Benda dengan Arsir', 'uraian' => 'Pengenalan komposisi bidang gambar, teknik arsiran pensil 2B, dan praktik membuat sketsa awal objek daun.', 'refleksi' => 'Sketsa siswa menunjukkan pemahaman proporsi dan pencahayaan yang cukup baik.'],
                ['materi' => 'Ragam Hias Daerah dan Penerapannya pada Media Kertas', 'uraian' => 'Mengidentifikasi motif ragam hias Nusantara (geometris & organis) dan mewarnai pola simetris.', 'refleksi' => 'Siswa tekun berkarya dan menghasilkan komposisi warna yang harmonis.'],
            ],
            'BK' => [
                ['materi' => 'Layanan Klasikal: Manajemen Waktu Belajar dan Kebiasaan Positif', 'uraian' => 'Refleksi jadwal harian siswa, identifikasi distraksi gawai, dan penyusunan rencana belajar mingguan pribadi.', 'refleksi' => 'Siswa terbuka berdiskusi dan menuliskan target perbaikan manajemen waktu masing-masing.'],
                ['materi' => 'Komunikasi Asertif dan Pencegahan Perundungan di Sekolah', 'uraian' => 'Simulasi cara menolak ajakan negatif secara santun, membangun empati antar teman, dan pengenalan kanal bantuan SPIKAP.', 'refleksi' => 'Suasana kelas hangat; siswa memahami langkah melapor bila melihat perilaku perundungan.'],
            ],
        ];

        $targetPerGuru = 2;
        $createdCount = 0;

        DB::transaction(function () use ($gurus, $activeYearId, $workDays, $materiBank, $targetPerGuru, &$createdCount) {
            foreach ($gurus as $guruIndex => $guru) {
                $existingCount = JurnalPembelajaran::where('academic_year_id', $activeYearId)
                    ->where('teacher_id', $guru->id)
                    ->count();

                $needed = max(0, $targetPerGuru - $existingCount);
                if ($needed === 0) {
                    continue;
                }

                $pengajarans = $guru->pengajarans
                    ->filter(fn ($p) => $p->kelasAjaran && $p->kelasAjaran->academic_year_id === $activeYearId && $p->kelasAjaran->kelas && $p->mataPelajaran)
                    ->values();

                if ($pengajarans->isEmpty()) {
                    continue;
                }

                for ($i = 0; $i < $needed; $i++) {
                    $pengajaran = $pengajarans->get($i % $pengajarans->count());
                    $kelas = $pengajaran->kelasAjaran->kelas;
                    $mapel = $pengajaran->mataPelajaran;

                    $tanggal = $workDays[$i % count($workDays)];
                    $jamMulai = (($guruIndex + $i) % 4) * 2 + 1; // jam ke 1, 3, 5, atau 7
                    $durasiJp = 2;
                    $jamSelesai = $jamMulai + $durasiJp - 1;
                    $jamKeLabel = "Jam ke {$jamMulai} - {$jamSelesai} ({$durasiJp} JP)";

                    $bank = $materiBank[$mapel->kode_mapel] ?? [
                        [
                            'materi' => "Materi Pokok {$mapel->nama_mapel} Pertemuan " . ($i + 1),
                            'uraian' => "Penyampaian konsep inti {$mapel->nama_mapel}, diskusi kelompok, dan latihan pemahaman materi bersama siswa kelas {$kelas->name}.",
                            'refleksi' => 'Pembelajaran berjalan tertib dan sebagian besar siswa memahami materi dengan baik.',
                        ],
                    ];
                    $template = $bank[$i % count($bank)];

                    // Ambil siswa aktif di kelas untuk snapshot presensi sesi
                    $studentIds = EnrollmentSiswa::where('academic_year_id', $activeYearId)
                        ->where('class_id', $kelas->id)
                        ->where('status', 'aktif')
                        ->pluck('student_id')
                        ->values();

                    $totalSiswa = $studentIds->count();

                    $jurnal = JurnalPembelajaran::create([
                        'academic_year_id'      => $activeYearId,
                        'teacher_id'            => $guru->id,
                        'class_id'              => $kelas->id,
                        'mata_pelajaran_id'     => $mapel->id,
                        'tanggal'               => $tanggal,
                        'jam_mulai'             => $jamMulai,
                        'durasi_jp'             => $durasiJp,
                        'jam_selesai'           => $jamSelesai,
                        'jam_ke_label'          => $jamKeLabel,
                        'materi'                => $template['materi'],
                        'uraian_materi'         => $template['uraian'],
                        'refleksi'              => $template['refleksi'],
                        'total_siswa'           => $totalSiswa,
                        'total_hadir'           => $totalSiswa,
                        'total_tidak_mengikuti' => 0,
                        'created_by'            => $guru->user_id,
                    ]);

                    foreach ($studentIds as $studentId) {
                        JurnalPembelajaranPresensi::create([
                            'jurnal_pembelajaran_id'      => $jurnal->id,
                            'student_id'                  => $studentId,
                            'status_jurnal'               => 'hadir',
                            'status_gerbang_snapshot'     => 'hadir',
                            'keterangan'                  => null,
                            'diabsenkan_telat_di_gerbang' => false,
                        ]);
                    }

                    $createdCount++;
                }
            }
        });

        $this->command?->info("DemoJurnalMengajarSeeder selesai: {$createdCount} jurnal pembelajaran baru ditambahkan (total " . JurnalPembelajaran::count() . " jurnal).");
    }

    /**
     * Mengembalikan daftar tanggal hari efektif sekolah terakhir (format Y-m-d), dimulai dari hari ini mundur.
     */
    private function resolveRecentSchoolDays(int $count, string $workDaysType): array
    {
        $holidays = HariLibur::pluck('start_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $days = [];
        $cursor = now('Asia/Jakarta')->copy();
        $maxLookback = 30;

        while (count($days) < $count && $maxLookback > 0) {
            $isWeekend = $workDaysType === '5_hari'
                ? $cursor->isWeekend()
                : $cursor->isSunday();

            $dateStr = $cursor->toDateString();
            if (!$isWeekend && !in_array($dateStr, $holidays, true)) {
                $days[] = $dateStr;
            }

            $cursor->subDay();
            $maxLookback--;
        }

        return empty($days) ? [now('Asia/Jakarta')->toDateString()] : $days;
    }
}

