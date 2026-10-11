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
     * Cek apakah key atau nama jabatan merujuk ke Guru BK.
     */
    public function isGuruBkKey(string $key): bool
    {
        $clean = strtolower(trim(str_replace(['_', '/', '-'], ' ', $key)));
        return in_array($clean, ['guru bk', 'guru bk bp', 'bk', 'guru bp', 'bimbingan konseling'])
            || str_contains($clean, 'guru bk')
            || str_contains($clean, 'bk bp');
    }

    /**
     * Ambil seluruh nomor HP Guru BK di sekolah (sebagai fallback jika kelas belum dipetakan).
     */
    public function resolveAllGuruBk(): array
    {
        return Guru::whereHas('jabatans', function ($query) {
            $query->where('nama_jabatan', 'like', '%BK%')
                  ->orWhere('nama_jabatan', 'like', '%Konseling%');
        })
        ->whereNotNull('no_hp')
        ->where('no_hp', '!=', '')
        ->pluck('no_hp')
        ->toArray();
    }

    /**
     * Dapatkan nomor HP Guru BK pembina kelas siswa (kelasPantau),
     * atau fallback ke seluruh Guru BK jika kelas belum dipetakan.
     */
    public function resolveGuruBk(Siswa $student): array
    {
        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) {
            return $this->resolveAllGuruBk();
        }

        $enrollment = $student->enrollments()->where('academic_year_id', $currentYear->id)->first();
        if (!$enrollment) {
            return $this->resolveAllGuruBk();
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
            $bkHpNumbers = $this->resolveAllGuruBk();
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
        if ($this->isGuruBkKey($namaJabatan)) {
            return $this->resolveAllGuruBk();
        }

        $normalized = match (strtolower(str_replace('_', ' ', $namaJabatan))) {
            'kepala sekolah' => 'Kepala Sekolah',
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
            } elseif ($key === 'siswa') {
                $hp = trim($student->no_hp ?? '');
                if ($hp && !isset($seenNumbers[$hp])) {
                    $resolved[] = ['number' => $hp, 'type' => 'siswa'];
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
            } elseif ($this->isGuruBkKey($key)) {
                // Khusus Guru BK: Filter cerdas hanya ke Guru BK pembina kelas siswa terkait (kelasPantau).
                // Contoh: Siswa kelas 7 -> hanya dikirim ke Bu Sri (pembina kelas 7).
                // Fallback: Jika kelas siswa belum ada pembina khusus, otomatis fallback ke seluruh Guru BK.
                $bkHpNumbers = $this->resolveGuruBk($student);
                foreach ($bkHpNumbers as $hp) {
                    if ($hp && !isset($seenNumbers[$hp])) {
                        $resolved[] = ['number' => $hp, 'type' => $key];
                        $seenNumbers[$hp] = true;
                    }
                }
            } elseif (str_starts_with($key, 'jabatan:')) {
                // Jabatan custom dari tabel jabatans (prefix 'jabatan:NamaJabatan')
                $namaJabatan = substr($key, strlen('jabatan:'));
                if (!empty($namaJabatan)) {
                    if ($this->isGuruBkKey($namaJabatan)) {
                        $jabatansHp = $this->resolveGuruBk($student);
                    } else {
                        $jabatansHp = $this->resolveByJabatan($namaJabatan);
                    }
                    foreach ($jabatansHp as $hp) {
                        if ($hp && !isset($seenNumbers[$hp])) {
                            $resolved[] = ['number' => $hp, 'type' => 'jabatan_custom'];
                            $seenNumbers[$hp] = true;
                        }
                    }
                }
            } elseif (str_starts_with($key, 'GROUP:')) {
                $groupId = substr($key, 6);
                if (!empty($groupId) && str_ends_with($groupId, '@g.us') && !isset($seenNumbers[$groupId])) {
                    $resolved[] = ['number' => $groupId, 'type' => 'whatsapp_group'];
                    $seenNumbers[$groupId] = true;
                }
            } else {
                // Jabatan tetap lainnya (waka_kesiswaan, dll.)
                if ($this->isGuruBkKey($key)) {
                    $jabatansHp = $this->resolveGuruBk($student);
                } else {
                    $jabatansHp = $this->resolveByJabatan($key);
                }
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
