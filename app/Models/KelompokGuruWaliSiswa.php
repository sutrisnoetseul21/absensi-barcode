<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelompokGuruWaliSiswa extends Model
{
    // Tidak pakai SoftDeletes — pengeluaran siswa dilakukan via soft-archive
    // dengan men-set status_aktif = false. Baris tidak pernah dihapus secara fisik
    // agar riwayat pendampingan tetap tersimpan sebagai arsip.
    use HasUuids;

    protected $table = 'kelompok_guru_wali_siswa';

    protected $fillable = [
        'kelompok_id',
        'student_id',
        'tahun_masuk',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'tahun_masuk'  => 'integer',
    ];

    /**
     * Kelompok dampingan tempat siswa ini terdaftar.
     */
    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(KelompokGuruWali::class, 'kelompok_id');
    }

    /**
     * Siswa yang terdaftar di baris keanggotaan ini.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    /**
     * Menghitung total catatan jurnal pendampingan siswa ini dalam kelompok ini.
     */
    public function countJurnals(): int
    {
        return JurnalGuruWali::where('kelompok_id', $this->kelompok_id)
            ->where('student_id', $this->student_id)
            ->count();
    }

    /**
     * Menghitung total sesi konsultasi siswa ini dalam kelompok ini.
     */
    public function countKonsultasis(): int
    {
        return KonsultasiGuruWali::where('kelompok_id', $this->kelompok_id)
            ->where('student_id', $this->student_id)
            ->count();
    }

    /**
     * Memeriksa apakah siswa sudah memiliki riwayat pendampingan (jurnal/konsultasi).
     * Jika sudah ada data, keanggotaan tidak boleh dihapus permanen, hanya boleh diarsipkan.
     */
    public function hasRiwayatPendampingan(): bool
    {
        return $this->countJurnals() > 0 || $this->countKonsultasis() > 0;
    }
}
