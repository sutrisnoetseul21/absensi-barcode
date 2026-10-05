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
     * Mengambil instance singleton untuk pengaturan Laporan Rekap Sekolah.
     */
    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'is_active'       => false,
                'cutoff_time'     => '08:15:00',
                'recipients'      => ['Kepala Sekolah'],
                'template_header' => "Laporan Absensi Harian {angkatan}\n{nama_sekolah}\n{hari}, {tanggal}\n",
                'template_row'    => "Kelas {nama_kelas}\nTotal Siswa : {total_siswa} Siswa\nHadir : {jumlah_hadir} Siswa\nIzin : {jumlah_izin} Siswa {nama_izin}\nSakit : {jumlah_sakit} Siswa {nama_sakit}\nAlpa : {jumlah_alpa} Siswa {nama_alpa}\nBelum Presensi : {jumlah_belum_presensi} Siswa {nama_belum_presensi}\n=============================",
                'template_footer' => "\nDemikian atas perhatiannya kami ucapkan terimakasih.",
            ]
        );
    }

    /**
     * Cek apakah masih bisa kirim manual hari ini (maks 5x/hari).
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

        return $this->manual_send_count < 5;
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
