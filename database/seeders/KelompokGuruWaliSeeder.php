<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\KelompokGuruWali;
use App\Models\KelompokGuruWaliSiswa;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class KelompokGuruWaliSeeder extends Seeder
{
    public function run(): void
    {
        $guru1 = Guru::first();
        $guru2 = Guru::skip(1)->first();

        if (!$guru1 || !$guru2) {
            $this->command->error('Dibutuhkan minimal 2 data Guru di database untuk membuat 2 kelompok dampingan berbeda. Seeder dibatalkan.');
            return;
        }

        // PENTING: hanya ambil siswa yang statusnya 'aktif' DAN belum punya kelompok guru wali aktif
        $siswaList = Siswa::where('status', 'aktif')
            ->whereDoesntHave('kelompokGuruWali')
            ->take(10)
            ->get();

        if ($siswaList->count() < 10) {
            $this->command->warn("Hanya ditemukan {$siswaList->count()} siswa aktif tanpa kelompok. Seeder tetap jalan dengan jumlah yang ada.");
        }

        $kelompok1 = KelompokGuruWali::create([
            'nama_kelompok' => 'Kelompok Dandelion',
            'teacher_id'    => $guru1->id,
            'status_aktif'  => true,
        ]);

        foreach ($siswaList->take(5) as $siswa) {
            KelompokGuruWaliSiswa::create([
                'kelompok_id'  => $kelompok1->id,
                'student_id'   => $siswa->id,
                'tahun_masuk'  => 2024,
                'status_aktif' => true,
            ]);
        }

        $kelompok2 = KelompokGuruWali::create([
            'nama_kelompok' => 'Kelompok Mawar',
            'teacher_id'    => $guru2->id,
            'status_aktif'  => true,
        ]);

        foreach ($siswaList->skip(5)->take(5) as $siswa) {
            KelompokGuruWaliSiswa::create([
                'kelompok_id'  => $kelompok2->id,
                'student_id'   => $siswa->id,
                'tahun_masuk'  => 2024,
                'status_aktif' => true,
            ]);
        }

        $totalSiswa = min($siswaList->count(), 10);
        $this->command->info("✅ Seeder KelompokGuruWali selesai: 2 kelompok, {$totalSiswa} siswa.");
    }
}
