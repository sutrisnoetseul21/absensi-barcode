<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasAjaran;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoPembelajaranSeeder extends Seeder
{
    /**
     * Mengisi plotting Pembelajaran (Pengajaran) untuk setiap kelas & mata pelajaran
     * pada Tahun Ajaran aktif berdasarkan guru pengampu di teacher_mata_pelajaran.
     */
    public function run(): void
    {
        $activeYearId = PengaturanSekolah::current()?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->value('id');

        if (!$activeYearId) {
            $this->command?->error('Tahun ajaran aktif tidak ditemukan.');
            return;
        }

        $classes = Kelas::orderBy('grade_level')->orderBy('name')->get();
        $mapels = MataPelajaran::with(['gurus' => fn ($q) => $q->orderBy('name')])
            ->orderBy('id')
            ->get();
        $fallbackGuru = Guru::orderBy('name')->first();

        if ($classes->isEmpty() || $mapels->isEmpty() || !$fallbackGuru) {
            $this->command?->error('Data kelas, mata pelajaran, atau guru belum tersedia.');
            return;
        }

        $createdCount = 0;

        DB::transaction(function () use ($classes, $mapels, $activeYearId, $fallbackGuru, &$createdCount) {
            foreach ($classes as $classIndex => $kelas) {
                $kelasAjaran = KelasAjaran::firstOrCreate(
                    [
                        'class_id'         => $kelas->id,
                        'academic_year_id' => $activeYearId,
                    ],
                    [
                        'teacher_id' => null,
                    ]
                );

                foreach ($mapels as $mapel) {
                    $alreadyExists = Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                        ->where('mata_pelajaran_id', $mapel->id)
                        ->exists();

                    if ($alreadyExists) {
                        continue;
                    }

                    $eligibleGurus = $mapel->gurus;
                    $chosenGuru = $eligibleGurus->isNotEmpty()
                        ? $eligibleGurus->values()->get($classIndex % $eligibleGurus->count())
                        : $fallbackGuru;

                    Pengajaran::create([
                        'class_academic_year_id' => $kelasAjaran->id,
                        'mata_pelajaran_id'      => $mapel->id,
                        'teacher_id'             => $chosenGuru->id,
                    ]);

                    $createdCount++;
                }
            }
        });

        $this->command?->info("DemoPembelajaranSeeder selesai: {$createdCount} penugasan pembelajaran baru ditambahkan (total " . Pengajaran::count() . " data pembelajaran).");
    }
}

