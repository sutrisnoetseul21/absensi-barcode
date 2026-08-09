<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiSchoolSummarySetting extends Model
{
    protected $fillable = [
        'is_active',
        'cutoff_time',
        'template_header',
        'template_row',
        'template_footer',
        'recipients',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'recipients' => 'array',
    ];

    /**
     * Mengambil instance singleton untuk pengaturan Laporan Rekap Sekolah.
     */
    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'is_active' => false,
                'cutoff_time' => '08:15:00',
                'recipients' => ['Kepala Sekolah'], // Default jabatan Kepala Sekolah
                'template_header' => "Laporan Absensi Harian Seluruh Kelas\n{nama_sekolah}\n{hari}, {tanggal}\n",
                'template_row' => "Kelas {nama_kelas} Hadir= {jumlah_hadir}, Terlambat= {jumlah_terlambat} {nama_terlambat}, Sakit= {jumlah_sakit} {nama_sakit}, Izin= {jumlah_izin} {nama_izin}, Alfa= {jumlah_alpa} {nama_alpa}, Belum Absen= {jumlah_belum_presensi} {nama_belum_presensi}",
                'template_footer' => "\nDemikian atas perhatiannya kami ucapkan terimakasih",
            ]
        );
    }
}
