<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JurnalGuruWali extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'jurnal_guru_wali';

    protected $fillable = [
        'teacher_id',
        'kelompok_id',
        'student_id',
        'tanggal_waktu',
        'jenis_pendampingan',
        'kategori_pendampingan',
        'uraian_pembahasan',
        'status_sesi',
        'rujukan_kolaborasi',
        'rencana_tindak_lanjut',
        'is_public_note',
        'academic_year_id',
        'class_id',
        'kategori_sentimen',
    ];

    protected $casts = [
        'tanggal_waktu'  => 'datetime',
        'is_public_note' => 'boolean',
        'kategori_sentimen' => \App\Enums\KategoriSentimen::class,
    ];

    /**
     * Guru Wali yang melaksanakan sesi pendampingan.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    /**
     * Alias relasi: teacher -> Guru Wali
     */
    public function teacher(): BelongsTo
    {
        return $this->guru();
    }

    /**
     * Kelompok dampingan tempat sesi dicatat.
     */
    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(KelompokGuruWali::class, 'kelompok_id');
    }

    /**
     * Siswa yang didampingi dalam sesi ini.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    /**
     * Alias relasi: student -> Siswa
     */
    public function student(): BelongsTo
    {
        return $this->siswa();
    }

    /**
     * Konsultasi murid asal jika sesi ini dikonversi dari pengajuan murid.
     */
    public function konsultasi(): HasOne
    {
        return $this->hasOne(KonsultasiGuruWali::class, 'jurnal_id');
    }

    /**
     * Sesi konseling BK jika kasus ini dirujuk ke Guru BK.
     */
    public function konselingBk(): HasOne
    {
        return $this->hasOne(KonselingBk::class, 'jurnal_guru_wali_id');
    }

    /**
     * Badge karakter yang diberikan dalam jurnal ini.
     */
    public function badge(): MorphOne
    {
        return $this->morphOne(BadgeKarakterSiswa::class, 'source');
    }
}
