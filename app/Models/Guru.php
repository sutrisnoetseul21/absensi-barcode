<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Guru extends Authenticatable
{
    use HasUuids, SoftDeletes;

    protected $table = 'teachers';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'nip',
    ];

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
}
