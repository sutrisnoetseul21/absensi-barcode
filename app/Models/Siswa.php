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
            $siswa->barcode_code = $siswa->nisn;
            $siswa->username = $siswa->nisn;
        });
    }
    protected $table = 'students';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nisn',
        'name',
        'birth_place',
        'birth_date',
        'address',
        'photo_path',
        'barcode_code',
        'barcode_active',
        'username',
        'password',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date'           => 'date',
        'barcode_active'       => 'boolean',
        'must_change_password' => 'boolean',
        'password'             => 'hashed',
    ];

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
}
