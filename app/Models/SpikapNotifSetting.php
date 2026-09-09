<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpikapNotifSetting extends Model
{
    protected $table = 'spikap_notif_settings';

    protected $guarded = [];

    protected $casts = [
        // Kolom recipients adalah JSON array of string key
        // Contoh: ["wali_kelas", "kepala_sekolah"]
        // Key di-resolve via RecipientResolverService yang sudah ada
        'recipients' => 'array',
    ];

    /**
     * Ambil (atau buat) singleton baris konfigurasi (id=1).
     * Default: notifikasi ke wali_kelas dan kepala_sekolah.
     */
    public static function instance(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'recipients' => ['wali_kelas', 'kepala_sekolah'],
            ]
        );
    }

    /**
     * Kembalikan array recipient keys yang aktif.
     * Ini satu-satunya sumber kebenaran penerima notifikasi WA darurat.
     *
     * @return string[]  contoh: ['wali_kelas', 'kepala_sekolah']
     */
    public function getActiveRecipients(): array
    {
        return $this->recipients ?? ['wali_kelas', 'kepala_sekolah'];
    }
}
