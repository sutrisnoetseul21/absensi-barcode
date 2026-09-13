<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
    ];

    protected $casts = [
        'tanggal_waktu'  => 'datetime',
        'is_public_note' => 'boolean',
    ];

    /**
     * Guru Wali yang melaksanakan sesi pendampingan.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
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
     * Konsultasi murid asal jika sesi ini dikonversi dari pengajuan murid.
     */
    public function konsultasi(): HasOne
    {
        return $this->hasOne(KonsultasiGuruWali::class, 'jurnal_id');
    }
}
