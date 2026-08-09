<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiDailyReportSetting extends Model
{
    protected $fillable = [
        'is_active',
        'cutoff_time',
        'template_pesan',
        'recipients',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'recipients' => 'array',
    ];

    /**
     * Mengambil instance singleton untuk pengaturan Laporan Harian.
     */
    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'is_active' => false,
                'cutoff_time' => '08:00:00',
                'recipients' => ['wali_kelas'],
                'template_pesan' => "Laporan Kelas {nama_kelas} tanggal {tanggal}\nTotal: {total_siswa} siswa\nRincian: Hadir {jumlah_hadir}, Terlambat {jumlah_terlambat}, Sakit {jumlah_sakit}, Izin {jumlah_izin}, Alpa {jumlah_alpa}.\n\nSiswa belum presensi:\n{daftar_belum_presensi}",
            ]
        );
    }
}
