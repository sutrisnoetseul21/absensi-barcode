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
                'nama_aplikasi' => 'SPIKAP',
                'sub_judul' => 'Anti-Perundungan & Pengaduan Siswa',
                'penjelasan_aplikasi' => 'Sistem Pelaporan Integratif Konflik & Anti-Perundungan SPENSA',
                'slug_url' => 'spikap',
                'recipients' => ['wali_kelas', 'kepala_sekolah'],
                'emergency_handlers' => ['wali_kelas', 'kepala_sekolah'],
                'notify_guru_laporan_biasa' => true,
                'notify_siswa_apresiasi' => true,
                'notify_siswa_tindak_lanjut' => true,
                'target_penerima_siswa' => ['siswa'],
            ]
        );

        $updates = [];
        if ($setting->nama_aplikasi === null) {
            $updates['nama_aplikasi'] = 'SPIKAP';
        }
        if ($setting->sub_judul === null) {
            $updates['sub_judul'] = 'Anti-Perundungan & Pengaduan Siswa';
        }
        if ($setting->penjelasan_aplikasi === null) {
            $updates['penjelasan_aplikasi'] = 'Sistem Pelaporan Integratif Konflik & Anti-Perundungan SPENSA';
        }
        if ($setting->slug_url === null) {
            $updates['slug_url'] = 'spikap';
        }
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

    protected static function booted(): void
    {
        static::saved(function ($model) {
            if ($model->wasChanged('slug_url') || $model->wasChanged('nama_aplikasi')) {
                try {
                    \Illuminate\Support\Facades\Artisan::call('route:clear');
                } catch (\Throwable $e) {
                    // Ignore CLI/cache failure
                }
            }
        });
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

    public function getNamaAplikasi(): string
    {
        return trim($this->nama_aplikasi ?? '') ?: 'SPIKAP';
    }

    public function getSubJudul(): string
    {
        return trim($this->sub_judul ?? '') ?: 'Anti-Perundungan & Pengaduan Siswa';
    }

    public function getPenjelasanAplikasi(): string
    {
        return trim($this->penjelasan_aplikasi ?? '') ?: 'Sistem Pelaporan Integratif Konflik & Anti-Perundungan SPENSA';
    }

    public function getSlugUrl(): string
    {
        $customSlug = trim($this->slug_url ?? '');
        $namaAppSlug = \Illuminate\Support\Str::slug($this->getNamaAplikasi());

        // Jika slug kosong atau masih bernilai default 'spikap' padahal nama aplikasi sudah diubah
        if (empty($customSlug) || ($customSlug === 'spikap' && $namaAppSlug !== 'spikap')) {
            return $namaAppSlug ?: 'spikap';
        }

        return \Illuminate\Support\Str::slug($customSlug) ?: 'spikap';
    }
}
