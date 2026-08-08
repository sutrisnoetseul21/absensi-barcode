<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Siswa extends Authenticatable
{
    use HasUuids, SoftDeletes;

    protected static function booted()
    {
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
        'user_id',
        'nisn',
        'nis',
        'name',
        'birth_place',
        'birth_date',
        'address',
        'photo_path',
        'status',
        'no_hp',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'status'     => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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
}
