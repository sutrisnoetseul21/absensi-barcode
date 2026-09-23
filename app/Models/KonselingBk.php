<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonselingBk extends Model
{
    use HasUuids;

    protected $table = 'konseling_bk';

    protected $fillable = [
        'academic_year_id',
        'class_id',
        'teacher_id',
        'student_id',
        'jurnal_guru_wali_id',
        'konsultasi_guru_wali_id',
        'is_rujukan',
        'alasan_rujukan',
        'tanggal_waktu',
        'jenis_layanan',
        'bidang_bimbingan',
        'topik_masalah',
        'uraian_kasus',
        'pendekatan_teknik',
        'hasil_konseling',
        'rencana_tindak_lanjut',
        'status_kasus',
        'rekomendasi_untuk_guru_wali',
        'is_rahasia',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
        'is_rahasia'    => 'boolean',
        'is_rujukan'    => 'boolean',
        'status_kasus'  => \App\Enums\StatusKasus::class,
    ];

    /**
     * Guru BK pelaksana konseling.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function guru(): BelongsTo
    {
        return $this->teacher();
    }

    /**
     * Siswa yang dikonseling.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function student(): BelongsTo
    {
        return $this->siswa();
    }

    /**
     * Tahun ajaran sesi konseling.
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    /**
     * Jurnal Guru Wali jika berasal dari rujukan.
     */
    public function jurnalGuruWali(): BelongsTo
    {
        return $this->belongsTo(JurnalGuruWali::class, 'jurnal_guru_wali_id');
    }

    public function konsultasiGuruWali(): BelongsTo
    {
        return $this->belongsTo(KonsultasiGuruWali::class, 'konsultasi_guru_wali_id');
    }
}
