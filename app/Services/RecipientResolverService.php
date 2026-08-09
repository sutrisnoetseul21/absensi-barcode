<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\KelasAjaran;
use App\Models\Guru;

class RecipientResolverService
{
    public function resolveOrtu(Siswa $student): ?string
    {
        return $student->no_hp;
    }

    public function resolveWaliKelas(Siswa $student): ?string
    {
        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) {
            return null;
        }

        // Cari enrollment siswa aktif (hanya 1 enrollment per tahun ajaran)
        $enrollment = $student->enrollments()->where('academic_year_id', $currentYear->id)->first();
        if (!$enrollment) {
            return null;
        }

        // Cari Wali Kelas khusus dari class_academic_year
        $kelasAjaran = KelasAjaran::where('class_id', $enrollment->class_id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        if ($kelasAjaran && $kelasAjaran->guru) {
            return $kelasAjaran->guru->no_hp;
        }

        return null;
    }

    public function resolveByJabatan(string $namaJabatan): array
    {
        return Guru::whereHas('jabatans', function ($query) use ($namaJabatan) {
            $query->where('nama_jabatan', $namaJabatan);
        })
        ->whereNotNull('no_hp')
        ->where('no_hp', '!=', '')
        ->pluck('no_hp')
        ->toArray();
    }

    /**
     * Kembalikan array berisi pasangan nomor HP dan recipient_type
     * Format kembalian: [['number' => '628...', 'type' => 'ortu'], ...]
     */
    public function resolveRecipients(array $recipientKeys, Siswa $student): array
    {
        $resolved = [];
        $seenNumbers = []; // Untuk dedup

        foreach ($recipientKeys as $key) {
            if ($key === 'ortu') {
                $hp = $this->resolveOrtu($student);
                if ($hp && !isset($seenNumbers[$hp])) {
                    $resolved[] = ['number' => $hp, 'type' => 'ortu'];
                    $seenNumbers[$hp] = true;
                }
            } elseif ($key === 'wali_kelas') {
                $hp = $this->resolveWaliKelas($student);
                if ($hp && !isset($seenNumbers[$hp])) {
                    $resolved[] = ['number' => $hp, 'type' => 'wali_kelas'];
                    $seenNumbers[$hp] = true;
                }
            } else {
                // Jabatan (misal 'Guru BK')
                $jabatansHp = $this->resolveByJabatan($key);
                foreach ($jabatansHp as $hp) {
                    if ($hp && !isset($seenNumbers[$hp])) {
                        $resolved[] = ['number' => $hp, 'type' => $key];
                        $seenNumbers[$hp] = true;
                    }
                }
            }
        }

        return $resolved;
    }
}
