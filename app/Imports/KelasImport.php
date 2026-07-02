<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasAjaran;
use App\Models\PengaturanSekolah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $activeYear = PengaturanSekolah::current()?->academic_year_id_active;

        foreach ($rows as $row) {
            $namaKelas = $row['nama_kelas'] ?? null;
            $tingkat = $row['tingkat_7_8_9'] ?? null;
            $namaGuru = $row['wali_kelas_opsional'] ?? null;

            if (!$namaKelas || !$tingkat) {
                continue; // Skip invalid rows
            }

            // Update or Create the Kelas
            $kelas = Kelas::updateOrCreate(
                ['name' => $namaKelas],
                ['grade_level' => (int) $tingkat]
            );

            // Assign Wali Kelas if provided and there is an active year
            if ($namaGuru && $activeYear) {
                $guru = Guru::where('name', $namaGuru)->first();
                
                if ($guru) {
                    KelasAjaran::updateOrCreate(
                        [
                            'class_id' => $kelas->id,
                            'academic_year_id' => $activeYear,
                        ],
                        [
                            'teacher_id' => $guru->id,
                        ]
                    );
                }
            }
        }
    }
}
