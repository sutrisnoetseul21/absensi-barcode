<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UjianAkademik extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'ujian_akademiks';

    protected $fillable = [
        'academic_year_id',
        'semester',
        'mata_pelajaran_id',
        'teacher_id',
        'cbt_ujian_id',
        'cbt_event_nama',
        'nama_ujian',
        'jenis_ujian_id',
        'kkm',
        'durasi_menit',
        'total_soal',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'keterangan',
        'published_classes',
    ];

    protected $casts = [
        'kkm'               => 'decimal:2',
        'durasi_menit'      => 'integer',
        'total_soal'        => 'integer',
        'tanggal_mulai'     => 'datetime',
        'tanggal_selesai'   => 'datetime',
        'published_classes' => 'array',
    ];

    /**
     * Helper eksplisit untuk menentukan KKM default berdasarkan mata pelajaran.
     * Digunakan secara transparan pada Form / Factory / Controller (bukan magic implicit di model).
     */
    public static function resolveDefaultKkm(?int $mataPelajaranId): float
    {
        if ($mataPelajaranId) {
            $mapel = MataPelajaran::find($mataPelajaranId);
            if ($mapel && $mapel->kkm_default !== null) {
                return (float) $mapel->kkm_default;
            }
        }

        return 75.00;
    }

    public function jenisUjian(): BelongsTo
    {
        return $this->belongsTo(JenisUjian::class, 'jenis_ujian_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'ujian_akademik_classes', 'ujian_akademik_id', 'class_id')
            ->using(UjianAkademikClass::class)
            ->withTimestamps();
    }

    public function nilaiUjians(): HasMany
    {
        return $this->hasMany(NilaiUjian::class, 'ujian_akademik_id');
    }
}
