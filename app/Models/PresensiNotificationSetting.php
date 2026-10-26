<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiNotificationSetting extends Model
{
    protected $table = 'presensi_notification_settings';

    protected $fillable = [
        'status_presensi',
        'is_active',
        'is_auto_send',
        'auto_send_time',
        'last_auto_send_date',
        'manual_send_date',
        'manual_send_count',
        'recipients',
        'template_pesan',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'is_auto_send'        => 'boolean',
        'last_auto_send_date' => 'date',
        'manual_send_date'    => 'date',
        'manual_send_count'   => 'integer',
        'recipients'          => 'array',
    ];

    /**
     * Cek apakah masih bisa kirim broadcast manual hari ini (maks 5x/hari).
     */
    public function canSendManualToday(): bool
    {
        $today = now('Asia/Jakarta')->toDateString();

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
     * Catat bahwa kirim broadcast manual sudah dilakukan hari ini.
     */
    public function recordManualSend(): void
    {
        $today = now('Asia/Jakarta')->toDateString();

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

    /**
     * Ambil template default berdasarkan status presensi.
     */
    public static function getDefaultTemplate(string $status): string
    {
        return match ($status) {
            'belum' => "Assalamu'alaikum Wr. Wb.\nSelamat Pagi Bpk/Ibu Orang Tua/Wali,\n\nKami informasikan bahwa ananda :\nNama Siswa : *{nama_siswa}*\nKelas : {kelas}\npada tanggal {tanggal} belum tercatat hadir dalam presensi {nama_sekolah}.\n\nMohon konfirmasi keterangan kehadiran ananda. Terima kasih.\n\nWassalamu’alaikum Wr. Wb.\nSalam Spensa,\n*{nama_sekolah}*\n\n_Mohon balas pesan ini dan Simpan Nomor ini_\n🙏🙏🙏",
            'sholat_dhuhur' => "Assalamu'alaikum Wr. Wb.\nBpk/Ibu Orang Tua/Wali,\n\nKami informasikan bahwa ananda :\nNama Siswa : *{nama_siswa}*\nKelas : {kelas}\npada tanggal {tanggal} belum tercatat dalam presensi Sholat Dhuhur berjamaah {nama_sekolah}.\n\nMohon konfirmasi atau perhatiannya. Terima kasih.\n\nWassalamu’alaikum Wr. Wb.\nSalam Spensa,\n*{nama_sekolah}*\n\n_Mohon balas pesan ini dan Simpan Nomor ini_\n🙏🙏🙏",
            'telat' => "Assalamu'alaikum Wr. Wb.\nSelamat Pagi Bpk/Ibu Orang Tua/Wali,\n\nKami informasikan bahwa ananda :\nNama Siswa : *{nama_siswa}*\nKelas : {kelas}\npada tanggal {tanggal} pukul {jam} tercatat berstatus *{status_kehadiran}*.\n\nWassalamu’alaikum Wr. Wb.\nSalam Spensa\n*{nama_sekolah}*\n\n_Mohon balas pesan ini dan Simpan Nomor ini_\n🙏🙏🙏",
            default => "Assalamu'alaikum Wr. Wb.\nSelamat Pagi Bpk/Ibu Orang Tua/Wali,\n\nKami informasikan bahwa ananda :\nNama Siswa : *{nama_siswa}*\nKelas : {kelas}\npada tanggal {tanggal} pukul {jam} tercatat berstatus *{status_kehadiran}*.\n\nWassalamu’alaikum Wr. Wb.\nSalam Spensa,\n*{nama_sekolah}*\n\n_Mohon balas pesan ini dan Simpan Nomor ini_\n🙏🙏🙏",
        };
    }

    /**
     * Render pesan menggunakan template dari database dengan penggantian placeholder dinamis.
     */
    public static function renderTemplate(string $status, array $data, ?string $fallbackTemplate = null): string
    {
        $setting = static::where('status_presensi', $status)->first();
        $template = ($setting && !empty($setting->template_pesan)) 
            ? $setting->template_pesan 
            : ($fallbackTemplate ?: static::getDefaultTemplate($status));

        $sekolah = PengaturanSekolah::current();
        $namaSekolah = $sekolah?->school_name ?: 'SMP Negeri 1 Sampang';

        $replacements = [
            '{nama_siswa}'       => $data['nama_siswa'] ?? '-',
            '{kelas}'            => $data['kelas'] ?? '-',
            '{tanggal}'          => $data['tanggal'] ?? now()->locale('id')->translatedFormat('l, d F Y'),
            '{jam}'              => $data['jam'] ?? '-',
            '{status_kehadiran}' => $data['status_kehadiran'] ?? ucfirst($status),
            '{nama_wali_kelas}'  => $data['nama_wali_kelas'] ?? '-',
            '{nama_sekolah}'     => $namaSekolah,
        ];

        return strtr($template, $replacements);
    }
}
