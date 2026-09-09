<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpikapLaporan extends Model
{
    protected $table = 'spikap_laporan';

    protected $guarded = [];

    protected $casts = [
        'waktu_kejadian' => 'datetime',
    ];

    // ─── Relasi ──────────────────────────────────────────────────────────────

    /**
     * Siswa yang membuat laporan.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    /**
     * Guru yang terakhir menangani (informatif, bukan kunci kepemilikan).
     */
    public function lastHandledBy(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'last_handled_by');
    }

    /**
     * Lampiran bukti (foto & video).
     */
    public function lampiran(): HasMany
    {
        return $this->hasMany(SpikapLampiran::class, 'laporan_id');
    }

    /**
     * Log perubahan status (audit trail).
     */
    public function logStatus(): HasMany
    {
        return $this->hasMany(SpikapLogStatus::class, 'laporan_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'diterima'         => 'Diterima',
            'dalam_investigasi'=> 'Dalam Investigasi',
            'selesai'          => 'Selesai',
            default            => $this->status,
        };
    }

    /**
     * Label sifat laporan.
     */
    public function getLabelSifatAttribute(): string
    {
        return match ($this->sifat_laporan) {
            'biasa'   => 'Biasa',
            'darurat' => '🚨 Darurat',
            default   => $this->sifat_laporan,
        };
    }

    /**
     * Label jenis perundungan.
     */
    public function getLabelJenisAttribute(): string
    {
        return match ($this->jenis_perundungan) {
            'fisik'   => 'Fisik',
            'verbal'  => 'Verbal',
            'sosial'  => 'Sosial',
            'digital' => 'Digital / Cyberbullying',
            'lainnya' => 'Lainnya',
            default   => $this->jenis_perundungan,
        };
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Scope: hanya laporan yang belum selesai.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', '!=', 'selesai');
    }

    /**
     * Scope: laporan darurat saja.
     */
    public function scopeDarurat($query)
    {
        return $query->where('sifat_laporan', 'darurat');
    }

    /**
     * Scope laporan berdasarkan role & assignment user guru.
     */
    public function scopeForUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Super Admin & SPIKAP Admin punya akses penuh ke semua laporan
        if ($user->hasAnyRole(['super_admin', 'spikap_admin'])) {
            return $query;
        }

        $emergencyHandlers = SpikapNotifSetting::instance()->getActiveEmergencyHandlers();
        $allowWkDarurat   = in_array('wali_kelas', $emergencyHandlers);
        $allowKsDarurat   = in_array('kepala_sekolah', $emergencyHandlers);
        $allowBkDarurat   = in_array('guru_bk', $emergencyHandlers);
        $allowWakaDarurat = in_array('waka_kesiswaan', $emergencyHandlers);

        $isKs   = $user->isKepalaSekolah();
        $isBk   = $user->isGuruBk();
        $isWk   = $user->isWaliKelasMurni();
        $isWaka = $user->isWakaKesiswaan();

        // Kepala Sekolah melihat semua laporan (supervisory monitoring)
        if ($isKs) {
            return $query;
        }

        return $query->where(function ($q) use ($user, $isBk, $isWk, $isWaka, $allowWkDarurat, $allowBkDarurat, $allowWakaDarurat) {
            $hasCondition = false;

            // Guru BK melihat:
            // 1) Laporan biasa yang ditujukan ke Guru BK (dari siswa di kelas binaan BK / pantau)
            // 2) Laporan darurat (jika guru_bk diaktifkan di emergency_handlers, dari siswa kelas binaan BK)
            if ($isBk && $user->teacher) {
                $hasBkRestriction = !$user->bypass_semua_kelas;
                $currentYear = \App\Models\TahunAjaran::aktif()->first();

                $bkClassIds = $user->teacher->kelasPantau()
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('class_id')
                    ->toArray();
                $wkClassIds = $user->teacher->kelasAjarans()
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('class_id')
                    ->toArray();
                $accessibleClassIds = array_unique(array_merge($bkClassIds, $wkClassIds));

                $method = $hasCondition ? 'orWhere' : 'where';
                $q->$method(function ($sub) use ($allowBkDarurat, $hasBkRestriction, $accessibleClassIds, $currentYear) {
                    if ($hasBkRestriction) {
                        if (empty($accessibleClassIds)) {
                            $sub->whereRaw('1 = 0');
                            return;
                        }
                        $studentIds = \App\Models\EnrollmentSiswa::whereIn('class_id', $accessibleClassIds)
                            ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                            ->pluck('student_id')
                            ->toArray();
                        $sub->whereIn('student_id', $studentIds);
                    }

                    $sub->where(function ($b) use ($allowBkDarurat) {
                        $b->where(function ($normal) {
                            $normal->where('sifat_laporan', 'biasa')
                                   ->where('tujuan_penerima', 'guru_bk');
                        });
                        if ($allowBkDarurat) {
                            $b->orWhere('sifat_laporan', 'darurat');
                        }
                    });
                });
                $hasCondition = true;
            }

            // Waka Kesiswaan melihat:
            // Laporan darurat (jika waka_kesiswaan diaktifkan di emergency_handlers)
            if ($isWaka && $allowWakaDarurat) {
                $method = $hasCondition ? 'orWhere' : 'where';
                $q->$method(function ($sub) {
                    $sub->where('sifat_laporan', 'darurat');
                });
                $hasCondition = true;
            }

            // Wali Kelas melihat:
            // 1) Laporan biasa ke WK dari siswa kelas binaannya
            // 2) Laporan darurat dari siswa kelas binaannya (jika wali_kelas diaktifkan di emergency_handlers)
            if ($isWk && $user->teacher) {
                $hasWkRestriction = !$user->bypass_semua_kelas;
                $currentYear = \App\Models\TahunAjaran::aktif()->first();
                $classIds = $user->teacher->kelasAjarans()
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('class_id')
                    ->toArray();

                $studentIds = \App\Models\EnrollmentSiswa::whereIn('class_id', $classIds)
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('student_id')
                    ->toArray();

                $method = $hasCondition ? 'orWhere' : 'where';
                $q->$method(function ($sub) use ($studentIds, $allowWkDarurat, $hasWkRestriction) {
                    if ($hasWkRestriction) {
                        $sub->whereIn('student_id', $studentIds);
                    }

                    $sub->where(function ($w) use ($allowWkDarurat) {
                        $w->where(function ($b) {
                            $b->where('sifat_laporan', 'biasa')
                              ->where('tujuan_penerima', 'wali_kelas');
                        });
                        if ($allowWkDarurat) {
                            $w->orWhere('sifat_laporan', 'darurat');
                        }
                    });
                });
                $hasCondition = true;
            }

            if (!$hasCondition) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Cek apakah user berhak mengubah status laporan ini.
     */
    public function canUpdateStatusBy($user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasAnyRole(['super_admin', 'spikap_admin'])) {
            return true;
        }

        $isKs   = $user->isKepalaSekolah();
        $isBk   = $user->isGuruBk();
        $isWk   = $user->isWaliKelasMurni();
        $isWaka = $user->isWakaKesiswaan();

        // 1. PENANGANAN LAPORAN DARURAT (Mengikuti konfigurasi emergency_handlers)
        if ($this->sifat_laporan === 'darurat') {
            $emergencyHandlers = SpikapNotifSetting::instance()->getActiveEmergencyHandlers();

            // Kepala Sekolah
            if (in_array('kepala_sekolah', $emergencyHandlers) && $isKs) {
                return true;
            }

            // Guru BK (sesuai kelas binaan BK / kelas pantau)
            if (in_array('guru_bk', $emergencyHandlers) && $isBk && $user->teacher) {
                if ($user->bypass_semua_kelas) {
                    return true;
                }
                $currentYear = \App\Models\TahunAjaran::aktif()->first();
                $bkClassIds = $user->teacher->kelasPantau()
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('class_id')
                    ->toArray();
                $wkClassIds = $user->teacher->kelasAjarans()
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('class_id')
                    ->toArray();
                $accessibleClassIds = array_unique(array_merge($bkClassIds, $wkClassIds));

                if (!empty($accessibleClassIds)) {
                    $isStudentInClass = \App\Models\EnrollmentSiswa::whereIn('class_id', $accessibleClassIds)
                        ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                        ->where('student_id', $this->student_id)
                        ->exists();

                    if ($isStudentInClass) {
                        return true;
                    }
                }
            }

            // Waka Kesiswaan
            if (in_array('waka_kesiswaan', $emergencyHandlers) && $isWaka) {
                return true;
            }

            // Wali Kelas (wajib merupakan wali kelas dari rombel siswa pelapor)
            if (in_array('wali_kelas', $emergencyHandlers) && $isWk && $user->teacher) {
                if ($user->bypass_semua_kelas) {
                    return true;
                }
                $currentYear = \App\Models\TahunAjaran::aktif()->first();
                $classIds = $user->teacher->kelasAjarans()
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('class_id')
                    ->toArray();

                $isStudentInClass = \App\Models\EnrollmentSiswa::whereIn('class_id', $classIds)
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->where('student_id', $this->student_id)
                    ->exists();

                if ($isStudentInClass) {
                    return true;
                }
            }

            return false;
        }

        // 2. PENANGANAN LAPORAN BIASA
        // Guru BK: berhak update status laporan biasa yang ditujukan ke BK (sesuai kelas binaan BK)
        if ($isBk && $user->teacher && $this->sifat_laporan === 'biasa' && $this->tujuan_penerima === 'guru_bk') {
            if ($user->bypass_semua_kelas) {
                return true;
            }
            $currentYear = \App\Models\TahunAjaran::aktif()->first();
            $bkClassIds = $user->teacher->kelasPantau()
                ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                ->pluck('class_id')
                ->toArray();
            $wkClassIds = $user->teacher->kelasAjarans()
                ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                ->pluck('class_id')
                ->toArray();
            $accessibleClassIds = array_unique(array_merge($bkClassIds, $wkClassIds));

            if (!empty($accessibleClassIds)) {
                return \App\Models\EnrollmentSiswa::whereIn('class_id', $accessibleClassIds)
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->where('student_id', $this->student_id)
                    ->exists();
            }
            return false;
        }

        // Wali Kelas: berhak update laporan biasa ke WK dari siswa kelasnya
        if ($isWk && $user->teacher && $this->sifat_laporan === 'biasa' && $this->tujuan_penerima === 'wali_kelas') {
            if ($user->bypass_semua_kelas) {
                return true;
            }
            $currentYear = \App\Models\TahunAjaran::aktif()->first();
            $classIds = $user->teacher->kelasAjarans()
                ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                ->pluck('class_id')
                ->toArray();

            return \App\Models\EnrollmentSiswa::whereIn('class_id', $classIds)
                ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                ->where('student_id', $this->student_id)
                ->exists();
        }

        return false;
    }
}
