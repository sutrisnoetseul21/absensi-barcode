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
        return $student->no_hp_orang_tua ?: $student->no_hp;
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

    /**
     * Dapatkan nomor HP Guru BK pembina kelas siswa (kelasPantau),
     * atau fallback ke seluruh Guru BK jika belum dipetakan.
     */
    public function resolveGuruBk(Siswa $student): array
    {
        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) {
            return $this->resolveByJabatan('Guru BK');
        }

        $enrollment = $student->enrollments()->where('academic_year_id', $currentYear->id)->first();
        if (!$enrollment) {
            return $this->resolveByJabatan('Guru BK');
        }

        // Cari Guru BK yang membina kelas siswa (kelasPantau)
        $bkHpNumbers = Guru::whereHas('kelasPantau', function ($q) use ($enrollment, $currentYear) {
            $q->where('class_id', $enrollment->class_id)
              ->where('academic_year_id', $currentYear->id);
        })
        ->whereNotNull('no_hp')
        ->where('no_hp', '!=', '')
        ->pluck('no_hp')
        ->toArray();

        // Jika belum ada pemetaan kelas khusus, fallback ke seluruh Guru BK
        if (empty($bkHpNumbers)) {
            $bkHpNumbers = $this->resolveByJabatan('Guru BK');
        }

        return array_values(array_unique($bkHpNumbers));
    }

    /**
     * Resolve nomor HP siswa dan/atau orang tua berdasarkan target pilihan.
     * Format: [['number' => '08...', 'type' => 'siswa'|'orang_tua']]
     */
    public function resolveKontakSiswa(Siswa $student, array $targets): array
    {
        $contacts = [];
        $seen = [];

        if (in_array('siswa', $targets)) {
            $noHpSiswa = trim($student->no_hp ?? '');
            if ($noHpSiswa && !isset($seen[$noHpSiswa])) {
                $contacts[] = [
                    'number' => $noHpSiswa,
                    'type'   => 'siswa',
                ];
                $seen[$noHpSiswa] = true;
            }
        }

        if (in_array('orang_tua', $targets)) {
            $noHpOrtu = trim($student->no_hp_orang_tua ?? '');
            if ($noHpOrtu && !isset($seen[$noHpOrtu])) {
                $contacts[] = [
                    'number' => $noHpOrtu,
                    'type'   => 'orang_tua',
                ];
                $seen[$noHpOrtu] = true;
            }
        }

        return $contacts;
    }

    public function resolveByJabatan(string $namaJabatan): array
    {
        $normalized = match (strtolower(str_replace('_', ' ', $namaJabatan))) {
            'kepala sekolah' => 'Kepala Sekolah',
            'guru bk'        => 'Guru BK',
            'waka kurikulum' => 'Waka Kurikulum',
            'waka kesiswaan' => 'Waka Kesiswaan',
            'waka sarpras'   => 'Waka Sarpras',
            'waka humas'     => 'Waka Humas',
            default          => $namaJabatan,
        };

        return Guru::whereHas('jabatans', function ($query) use ($namaJabatan, $normalized) {
            $query->where('nama_jabatan', $namaJabatan)
                  ->orWhere('nama_jabatan', $normalized);
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
            } elseif ($key === 'kepala_sekolah') {
                $jabatansHp = $this->resolveByJabatan('Kepala Sekolah');
                foreach ($jabatansHp as $hp) {
                    if ($hp && !isset($seenNumbers[$hp])) {
                        $resolved[] = ['number' => $hp, 'type' => 'kepala_sekolah'];
                        $seenNumbers[$hp] = true;
                    }
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
