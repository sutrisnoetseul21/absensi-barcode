<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpikapLogStatus extends Model
{
    protected $table = 'spikap_log_status';

    protected $guarded = [];

    // ─── Relasi ──────────────────────────────────────────────────────────────

    /**
     * Laporan terkait.
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SpikapLaporan::class, 'laporan_id');
    }

    /**
     * User yang mengubah status.
     * NULL berarti dicatat oleh sistem (bukan manusia), misal fallback wali kelas tidak ditemukan.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    // ─── Helper Statis ───────────────────────────────────────────────────────

    /**
     * Shortcut untuk mencatat perubahan status dari sistem (bukan guru).
     * Dipakai misal saat fallback wali kelas tidak ditemukan.
     *
     * @param int $laporanId
     * @param string $statusBaru
     * @param string $catatan
     */
    public static function catatSistem(int $laporanId, string $statusBaru, string $catatan): self
    {
        return static::create([
            'laporan_id'         => $laporanId,
            'status_lama'        => null,
            'status_baru'        => $statusBaru,
            'catatan'            => $catatan,
            'changed_by_user_id' => null, // sistem, bukan manusia
        ]);
    }

    /**
     * Shortcut untuk mencatat perubahan status dari seorang user (guru).
     *
     * @param int $laporanId
     * @param string $statusLama
     * @param string $statusBaru
     * @param string|null $catatan
     * @param string $userId
     */
    public static function catatGuru(
        int $laporanId,
        string $statusLama,
        string $statusBaru,
        ?string $catatan,
        string $userId
    ): self {
        return static::create([
            'laporan_id'         => $laporanId,
            'status_lama'        => $statusLama,
            'status_baru'        => $statusBaru,
            'catatan'            => $catatan,
            'changed_by_user_id' => $userId,
        ]);
    }
}
