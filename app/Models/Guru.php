<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Guru extends Authenticatable
{
    use HasUuids, SoftDeletes;

    protected $table = 'teachers';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'jenis_kelamin',
        'nip',
        'no_hp',
        'photo_path',
        'facebook_url',
        'instagram_url',
        'microsite_url',
    ];

    public function getAvatarUrlAttribute()
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }

        if ($this->jenis_kelamin === 'P') {
            return asset('images/avatar-f.svg');
        }

        return asset('images/avatar-m.svg');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function presensiProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TeacherPresensiProfile::class, 'teacher_id');
    }

    // Kelas yang diampu (bisa > 1 kelas per tahun ajaran)
    public function kelasAjarans(): HasMany
    {
        return $this->hasMany(KelasAjaran::class, 'teacher_id');
    }

    // Kelas tambahan yang bisa dipantau (Akses Portal Pantau)
    public function kelasPantau(): HasMany
    {
        return $this->hasMany(TeacherClassAccess::class, 'teacher_id');
    }

    // Absensi manual yang diinput wali kelas ini (polymorphic)
    public function absensisManual()
    {
        return $this->morphMany(Presensi::class, 'manual_input_by');
    }
    public function jabatans()
    {
        return $this->belongsToMany(Jabatan::class, 'teacher_jabatan', 'teacher_id', 'jabatan_id')
            ->withPivot('tanggal_mulai', 'tanggal_selesai')
            ->withTimestamps();
    }

    public function teacherJabatans(): HasMany
    {
        return $this->hasMany(TeacherJabatan::class, 'teacher_id');
    }

    /**
     * Cek apakah guru ini memegang jabatan tertentu yang masih aktif.
     */
    public function hasJabatan(string|array $jabatanNames): bool
    {
        $names = (array) $jabatanNames;
        return $this->jabatans()
            ->where(function ($q) {
                $q->whereNull('teacher_jabatan.tanggal_selesai')
                  ->orWhere('teacher_jabatan.tanggal_selesai', '>=', now()->toDateString());
            })
            ->where(function ($q) use ($names) {
                $q->where(function ($sub) use ($names) {
                    foreach ($names as $name) {
                        $sub->orWhere('nama_jabatan', 'like', "%{$name}%");
                    }
                });
            })
            ->exists();
    }

    public function pengajarans(): HasMany
    {
        return $this->hasMany(Pengajaran::class, 'teacher_id');
    }

    public function getSemuaJabatanAttribute()
    {
        // 1. Ambil jabatan dari tabel teacher_jabatan (yang belum selesai / tanggal_selesai null atau > now)
        $jabatans = $this->jabatans()
            ->where(function($q) {
                $q->whereNull('teacher_jabatan.tanggal_selesai')
                  ->orWhere('teacher_jabatan.tanggal_selesai', '>=', now()->toDateString());
            })
            ->pluck('nama_jabatan')
            ->toArray();

        // 2. Ambil status Wali Kelas dari class_academic_year untuk tahun ajaran aktif
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        if ($activeYear) {
            $kelasWali = $this->kelasAjarans()
                ->where('academic_year_id', $activeYear->id)
                ->with('kelas')
                ->get();

            foreach ($kelasWali as $kw) {
                $jabatans[] = "Wali Kelas " . ($kw->kelas->name ?? '');
            }
        }

        return $jabatans;
    }
    public function getMapelAktifAttribute()
    {
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            return [];
        }

        return $this->pengajarans()
            ->whereHas('kelasAjaran', function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })
            ->with(['mataPelajaran', 'kelasAjaran.kelas'])
            ->get()
            ->map(function ($pengajaran) {
                $mapel = $pengajaran->mataPelajaran->nama_mapel ?? 'Unknown';
                $kelas = $pengajaran->kelasAjaran->kelas->name ?? 'Unknown';
                return "{$mapel} ({$kelas})";
            })
            ->toArray();
    }

    protected function noHp(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                if (!$value) return null;
                $digits = preg_replace('/\D/', '', $value);
                if (str_starts_with($digits, '0')) {
                    $digits = '62' . substr($digits, 1);
                } elseif (!str_starts_with($digits, '62')) {
                    $digits = '62' . $digits;
                }
                return $digits;
            }
        );
    }

    public function kelompokGuruWali(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\KelompokGuruWali::class, 'teacher_id');
    }

    public function jurnalGuruWalis(): HasMany
    {
        return $this->hasMany(\App\Models\JurnalGuruWali::class, 'teacher_id');
    }

    public function konsultasiGuruWalis(): HasMany
    {
        return $this->hasMany(\App\Models\KonsultasiGuruWali::class, 'teacher_id');
    }

    public function konselingBks(): HasMany
    {
        return $this->hasMany(\App\Models\KonselingBk::class, 'teacher_id');
    }

    /**
     * Dapatkan daftar ID kelas binaan BK untuk guru ini.
     * Mengambil dari kelasPantau untuk tahun ajaran aktif (atau specified).
     */
    public function getKelasBinaanBkIds(?string $academicYearId = null): array
    {
        $yearId = $academicYearId ?? \App\Models\TahunAjaran::where('status', 'aktif')->value('id');
        if (!$yearId) {
            return [];
        }

        return $this->kelasPantau()
            ->where('academic_year_id', $yearId)
            ->pluck('class_id')
            ->toArray();
    }

    /**
     * Dapatkan koleksi kelas binaan BK guru ini untuk tahun ajaran aktif.
     */
    public function getKelasBinaanBk(?string $academicYearId = null)
    {
        $yearId = $academicYearId ?? \App\Models\TahunAjaran::where('status', 'aktif')->value('id');
        if (!$yearId) {
            return collect();
        }

        return $this->kelasPantau()
            ->where('academic_year_id', $yearId)
            ->with('kelas')
            ->get()
            ->pluck('kelas')
            ->filter();
    }

    /**
     * Cek apakah guru memiliki izin bypass untuk akses seluruh kelas di portal.
     */
    public function canAccessAllClasses(): bool
    {
        if (!$this->user) {
            return false;
        }

        return $this->user->hasRole('super_admin')
            || $this->user->can('portal_guru:akses_semua_kelas');
    }

    protected static function booted(): void
    {
        static::created(function (Guru $guru) {
            \App\Models\KelompokGuruWali::firstOrCreate(
                ['teacher_id' => $guru->id],
                [
                    'nama_kelompok' => 'Kelompok ' . $guru->name,
                    'status_aktif'  => true,
                ]
            );
        });
    }
}
