<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PresensiDailyReportSetting extends Model
{
    protected $fillable = [
        'is_active',
        'cutoff_time',
        'template_pesan',
        'recipients',
        'manual_send_date',
        'manual_send_count',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'recipients'        => 'array',
        'manual_send_date'  => 'date',
        'manual_send_count' => 'integer',
    ];

    /**
     * Mengambil instance singleton untuk pengaturan Laporan Harian.
     */
    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'is_active'    => false,
                'cutoff_time'  => '08:00:00',
                'recipients'   => ['wali_kelas'],
                'template_pesan' => "Laporan Kelas {nama_kelas} tanggal {tanggal}\nTotal: {total_siswa} siswa\nRincian: Hadir {jumlah_hadir}, Terlambat {jumlah_terlambat}, Sakit {jumlah_sakit}, Izin {jumlah_izin}, Alpa {jumlah_alpa}.\n\nSiswa belum presensi:\n{daftar_belum_presensi}",
            ]
        );
    }

    /**
     * Cek apakah masih bisa kirim manual hari ini (maks 1x/hari).
     */
    public function canSendManualToday(): bool
    {
        $today = now()->toDateString();

        if (is_null($this->manual_send_date)) {
            return true;
        }

        $lastSendDate = $this->manual_send_date instanceof \Carbon\Carbon
            ? $this->manual_send_date->toDateString()
            : (string) $this->manual_send_date;

        if ($lastSendDate !== $today) {
            return true;
        }

        return $this->manual_send_count < 1;
    }

    /**
     * Catat bahwa kirim manual sudah dilakukan hari ini.
     */
    public function recordManualSend(): void
    {
        $today = now()->toDateString();

        $lastSendDate = $this->manual_send_date instanceof \Carbon\Carbon
            ? $this->manual_send_date->toDateString()
            : (string) $this->manual_send_date;

        $this->update([
            'manual_send_date'  => $today,
            'manual_send_count' => ($lastSendDate === $today)
                ? $this->manual_send_count + 1
                : 1,
        ]);
    }
}
