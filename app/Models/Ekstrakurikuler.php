<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ekstrakurikuler extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'ekstrakurikulers';

    protected $fillable = [
        'academic_year_id',
        'nama_ekskul',
        'slug',
        'keterangan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    public function generateUniqueSlug(): string
    {
        $ayName = $this->tahunAjaran?->name
            ?? ($this->academic_year_id ? TahunAjaran::where('id', $this->academic_year_id)->value('name') : null);

        $base = Str::slug(trim($this->nama_ekskul . ' ' . ($ayName ?? ''))) ?: 'ekskul';
        $base = Str::limit($base, 145, '');
        $slug = $base;
        $counter = 2;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($this->id, fn ($q) => $q->where('id', '!=', $this->id))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function getKioskUrlAttribute(): ?string
    {
        if (empty($this->slug)) {
            return null;
        }

        return route('kiosk.scan-ekstrakurikuler', ['slug' => $this->slug]);
    }

    /**
     * Cek apakah user diizinkan membuka kiosk scanner barcode ekstrakurikuler ini.
     */
    public function canUserOpenKiosk(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (
            $user->isSuperAdmin()
            || $user->hasRole('super_admin')
            || $user->hasAnyRole(['admin_presensi', 'admin_presensi_editor', 'admin_presensi_viewer', 'admin_akademik_editor', 'admin_akademik_viewer', 'admin_master_editor'])
        ) {
            return true;
        }

        $teacherId = $user->teacher?->id;
        if (!$teacherId) {
            return false;
        }

        return $this->pembinas()->where('teachers.id', $teacherId)->exists();
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    public function pembinas(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'ekstrakurikuler_pembinas', 'ekstrakurikuler_id', 'teacher_id')
            ->withTimestamps();
    }

    public function anggota(): HasMany
    {
        return $this->hasMany(EkstrakurikulerSiswa::class, 'ekstrakurikuler_id');
    }

    public function anggotaAktif(): HasMany
    {
        return $this->hasMany(EkstrakurikulerSiswa::class, 'ekstrakurikuler_id')
            ->where('status_aktif', true);
    }

    public function anggotaArsip(): HasMany
    {
        return $this->hasMany(EkstrakurikulerSiswa::class, 'ekstrakurikuler_id')
            ->where('status_aktif', false);
    }

    public function siswa(): HasManyThrough
    {
        return $this->hasManyThrough(
            Siswa::class,
            EkstrakurikulerSiswa::class,
            'ekstrakurikuler_id',
            'id',
            'id',
            'student_id'
        );
    }

    public function pertemuans(): HasMany
    {
        return $this->hasMany(EkstrakurikulerPertemuan::class, 'ekstrakurikuler_id')
            ->orderBy('tanggal', 'desc')
            ->orderBy('pertemuan_ke', 'desc');
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(EkstrakurikulerPresensi::class, 'ekstrakurikuler_id');
    }
}
