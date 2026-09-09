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
        // Kolom emergency_handlers adalah JSON array of role/jabatan key
        // Contoh: ["wali_kelas", "kepala_sekolah", "guru_bk", "waka_kesiswaan"]
        'emergency_handlers' => 'array',
        'notify_guru_laporan_biasa' => 'boolean',
        'notify_siswa_apresiasi' => 'boolean',
        'notify_siswa_tindak_lanjut' => 'boolean',
        'target_penerima_siswa' => 'array',
    ];

    /**
     * Ambil (atau buat) singleton baris konfigurasi (id=1).
     */
    public static function instance(): self
    {
        $setting = static::firstOrCreate(
            ['id' => 1],
            [
                'recipients' => ['wali_kelas', 'kepala_sekolah'],
                'emergency_handlers' => ['wali_kelas', 'kepala_sekolah'],
                'notify_guru_laporan_biasa' => true,
                'notify_siswa_apresiasi' => true,
                'notify_siswa_tindak_lanjut' => true,
                'target_penerima_siswa' => ['siswa'],
            ]
        );

        $updates = [];
        if ($setting->emergency_handlers === null) {
            $updates['emergency_handlers'] = ['wali_kelas', 'kepala_sekolah'];
        }
        if ($setting->notify_guru_laporan_biasa === null) {
            $updates['notify_guru_laporan_biasa'] = true;
        }
        if ($setting->notify_siswa_apresiasi === null) {
            $updates['notify_siswa_apresiasi'] = true;
        }
        if ($setting->notify_siswa_tindak_lanjut === null) {
            $updates['notify_siswa_tindak_lanjut'] = true;
        }
        if ($setting->target_penerima_siswa === null) {
            $updates['target_penerima_siswa'] = ['siswa'];
        }

        if (!empty($updates)) {
            $setting->update($updates);
        }

        return $setting;
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

    /**
     * Kembalikan array peran/jabatan yang berhak menangani kasus darurat.
     *
     * @return string[] contoh: ['wali_kelas', 'kepala_sekolah']
     */
    public function getActiveEmergencyHandlers(): array
    {
        return $this->emergency_handlers ?? ['wali_kelas', 'kepala_sekolah'];
    }

    public function shouldNotifyGuruLaporanBiasa(): bool
    {
        return (bool) ($this->notify_guru_laporan_biasa ?? true);
    }

    public function shouldNotifySiswaApresiasi(): bool
    {
        return (bool) ($this->notify_siswa_apresiasi ?? true);
    }

    public function shouldNotifySiswaTindakLanjut(): bool
    {
        return (bool) ($this->notify_siswa_tindak_lanjut ?? true);
    }

    public function getTargetPenerimaSiswa(): array
    {
        return $this->target_penerima_siswa ?? ['siswa'];
    }
}
