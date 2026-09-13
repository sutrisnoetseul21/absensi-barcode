<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonsultasiGuruWali extends Model
{
    use HasUuids;

    protected $table = 'konsultasi_guru_wali';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'kelompok_id',
        'kategori_pendampingan',
        'topik_konsultasi',
        'detail_permasalahan',
        'mode_konsultasi',
        'usulan_tanggal_waktu',
        'jadwal_pasti',
        'status_pengajuan',
        'tanggapan_guru',
        'alasan_penolakan',
        'jurnal_id',
    ];

    protected $casts = [
        'usulan_tanggal_waktu' => 'datetime',
        'jadwal_pasti'         => 'datetime',
    ];

    /**
     * Siswa yang mengajukan permohonan konsultasi.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    /**
     * Guru Wali yang dituju.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    /**
     * Kelompok dampingan tempat siswa terdaftar.
     */
    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(KelompokGuruWali::class, 'kelompok_id');
    }

    /**
     * Jurnal pendampingan hasil konversi dari sesi konsultasi ini.
     */
    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(JurnalGuruWali::class, 'jurnal_id');
    }
}
