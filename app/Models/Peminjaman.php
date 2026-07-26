<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * PENTING: Guru::class terdaftar dengan 2 alias morph map ('wali_kelas' dan 'guru').
 * JANGAN PERNAH pakai $peminjaman->peminjam()->associate($guruInstance) untuk assign peminjam Guru —
 * getMorphClass() akan otomatis resolve ke 'wali_kelas', bukan 'guru', karena urutan definisi di morph map.
 * WAJIB set peminjam_type dan peminjam_id secara manual/eksplisit saat membuat record Peminjaman untuk peminjam Guru.
 */
class Peminjaman extends Model
{
    use HasUuids;

    protected $table = 'peminjamans';

    protected $fillable = [
        'eksemplar_id',
        'peminjam_type',
        'peminjam_id',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'status',
        'petugas_id',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_kembali' => 'date',
    ];

    public function eksemplarBuku(): BelongsTo
    {
        return $this->belongsTo(EksemplarBuku::class, 'eksemplar_id');
    }

    public function peminjam(): MorphTo
    {
        return $this->morphTo();
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
