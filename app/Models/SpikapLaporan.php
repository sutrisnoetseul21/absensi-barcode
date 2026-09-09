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

        $isKs = $user->isKepalaSekolah();
        $isBk = $user->isGuruBk();
        $isWk = $user->hasAnyRole(['spikap_wali_kelas', 'wali_kelas']);

        // Kepala Sekolah melihat semua laporan darurat + read-only semua laporan
        if ($isKs) {
            return $query;
        }

        return $query->where(function ($q) use ($user, $isBk, $isWk) {
            $hasCondition = false;

            // Guru BK melihat laporan biasa yang ditujukan ke Guru BK
            if ($isBk) {
                $q->where(function ($sub) {
                    $sub->where('sifat_laporan', 'biasa')
                        ->where('tujuan_penerima', 'guru_bk');
                });
                $hasCondition = true;
            }

            // Wali Kelas melihat laporan biasa ke WK + laporan darurat dari siswa kelasnya
            if ($isWk && $user->teacher) {
                $currentYear = \App\Models\TahunAjaran::aktif()->first();
                $classIds = \App\Models\KelasAjaran::where('teacher_id', $user->teacher->id)
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('class_id')
                    ->toArray();

                $studentIds = \App\Models\EnrollmentSiswa::whereIn('class_id', $classIds)
                    ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                    ->pluck('student_id')
                    ->toArray();

                $method = $hasCondition ? 'orWhere' : 'where';
                $q->$method(function ($sub) use ($studentIds) {
                    $sub->whereIn('student_id', $studentIds)
                        ->where(function ($w) {
                            $w->where(function ($b) {
                                $b->where('sifat_laporan', 'biasa')
                                  ->where('tujuan_penerima', 'wali_kelas');
                            })->orWhere('sifat_laporan', 'darurat');
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

        $isKs = $user->isKepalaSekolah();
        $isBk = $user->isGuruBk();
        $isWk = $user->hasAnyRole(['spikap_wali_kelas', 'wali_kelas']);

        if (!$user->can('spikap.update_status') && !$isKs && !$isBk && !$isWk) {
            return false;
        }

        // Kepala sekolah: berhak update status laporan darurat
        if ($isKs && $this->sifat_laporan === 'darurat') {
            return true;
        }

        // Guru BK: berhak update status laporan biasa yang ditujukan ke BK
        if ($isBk && $this->sifat_laporan === 'biasa' && $this->tujuan_penerima === 'guru_bk') {
            return true;
        }

        // Wali Kelas: berhak update laporan darurat atau biasa ke WK dari siswa kelasnya
        if ($isWk && $user->teacher) {
            $currentYear = \App\Models\TahunAjaran::aktif()->first();
            $classIds = \App\Models\KelasAjaran::where('teacher_id', $user->teacher->id)
                ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                ->pluck('class_id')
                ->toArray();

            $isStudentInClass = \App\Models\EnrollmentSiswa::whereIn('class_id', $classIds)
                ->when($currentYear, fn($cy) => $cy->where('academic_year_id', $currentYear->id))
                ->where('student_id', $this->student_id)
                ->exists();

            if ($isStudentInClass) {
                if ($this->sifat_laporan === 'darurat' || ($this->sifat_laporan === 'biasa' && $this->tujuan_penerima === 'wali_kelas')) {
                    return true;
                }
            }
        }

        return false;
    }
}
