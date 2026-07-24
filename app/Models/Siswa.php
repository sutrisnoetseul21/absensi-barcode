<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Siswa extends Authenticatable
{
    use HasUuids, SoftDeletes;

    protected static function booted()
    {
        static::saving(function ($siswa) {
            $siswa->username = $siswa->nisn;
        });

        static::created(function ($siswa) {
            $siswa->presensiProfile()->create([
                'barcode_code' => $siswa->nisn,
                'barcode_active' => true,
            ]);
        });
    }
    protected $table = 'students';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nisn',
        'nis',
        'name',
        'birth_place',
        'birth_date',
        'address',
        'photo_path',
        'username',
        'password',
        'must_change_password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date'           => 'date',
        'must_change_password' => 'boolean',
        'password'             => 'hashed',
        'status'               => 'string',
    ];

    public function getBarcodeCodeAttribute()
    {
        return $this->presensiProfile?->barcode_code;
    }

    public function getBarcodeActiveAttribute()
    {
        return $this->presensiProfile?->barcode_active ?? false;
    }


    // Semua riwayat enrollment (kelas per tahun ajaran)
    public function enrollments(): HasMany
    {
        return $this->hasMany(EnrollmentSiswa::class, 'student_id');
    }

    public function enrollmentAktif(): HasOne
    {
        return $this->hasOne(EnrollmentSiswa::class, 'student_id')
            ->whereHas('tahunAjaran', function ($q) {
                $q->where('status', 'aktif');
            })
            ->where('status', 'aktif')
            ->latest();
    }

    // Semua record absensi siswa
    public function absensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'student_id');
    }

    // Profil presensi (barcode dsb)
    public function presensiProfile(): HasOne
    {
        return $this->hasOne(StudentPresensiProfile::class, 'student_id');
    }
}
