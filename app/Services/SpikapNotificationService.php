<?php

namespace App\Services;

use App\Models\SpikapLaporan;
use App\Models\SpikapNotifSetting;
use App\Models\SpikapLogStatus;
use App\Models\WhatsAppNotificationLog;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Jobs\SendSpikapFcmNotificationJob;
use Illuminate\Support\Facades\Log;

class SpikapNotificationService
{
    public function __construct(
        protected RecipientResolverService $resolver
    ) {}

    /**
     * Kirim notifikasi WhatsApp darurat untuk laporan SPIKAP.
     *
     * @param SpikapLaporan $laporan
     * @return array
     */
    public function sendEmergencyNotification(SpikapLaporan $laporan): array
    {
        // Hanya proses jika laporan bersifat darurat
        if ($laporan->sifat_laporan !== 'darurat') {
            return ['status' => 'skipped', 'reason' => 'Bukan laporan darurat'];
        }

        $laporan->loadMissing(['siswa.enrollmentAktif.kelas']);
        $siswa = $laporan->siswa;

        if (!$siswa) {
            Log::warning("SPIKAP: siswa tidak ditemukan untuk laporan #{$laporan->id}");
            return ['status' => 'failed', 'reason' => 'Siswa tidak ditemukan'];
        }

        // 1. Baca konfigurasi penerima darurat
        $setting = SpikapNotifSetting::instance();
        $recipientKeys = $setting->getActiveRecipients(); // e.g. ['wali_kelas', 'kepala_sekolah']

        // 2. Cek fallback jika wali_kelas termasuk penerima namun tidak berhasil di-resolve
        if (in_array('wali_kelas', $recipientKeys)) {
            $hpWaliKelas = $this->resolver->resolveWaliKelas($siswa);

            if (!$hpWaliKelas) {
                Log::warning("SPIKAP: wali kelas tidak ditemukan untuk laporan #{$laporan->id}, siswa #{$siswa->id}");

                // Catat otomatis ke spikap_log_status sebagai audit trail sistem
                SpikapLogStatus::catatSistem(
                    $laporan->id,
                    'diterima',
                    'Wali kelas tidak ditemukan, notifikasi WA hanya terkirim ke penerima lain yang tersedia'
                );
            }
        }

        // 3. Resolve seluruh nomor penerima aktif
        $resolvedRecipients = $this->resolver->resolveRecipients($recipientKeys, $siswa);

        // 4. Susun pesan WA (SELALU tampilkan nama asli siswa, tanpa percabangan anonim)
        $namaSiswa = $siswa->name;
        $namaKelas = $siswa->enrollmentAktif?->kelas?->name ?? '-';
        $jenis     = $laporan->label_jenis;
        $waktuObj  = $laporan->created_at ?? now();
        $waktu     = $waktuObj->translatedFormat('d F Y, H:i') . ' WIB';

        $namaApp   = $setting->getNamaAplikasi();
        $settingWa = \App\Models\PresensiNotificationSetting::where('status_presensi', 'spikap_darurat')->first();
        $defaultTemplate = "🚨 *LAPORAN DARURAT {nama_aplikasi}*\nDari: {nama_siswa}\nKelas: {nama_kelas}\nJenis: {jenis}\nWaktu Lapor: {waktu}\n\nSilakan buka Portal Guru untuk detail dan tindak lanjut segera.";
        $template = ($settingWa && !empty($settingWa->template_pesan)) ? $settingWa->template_pesan : $defaultTemplate;
        $pesan = strtr($template, [
            '{nama_aplikasi}' => $namaApp,
            '{nama_siswa}'    => $namaSiswa,
            '{nama_kelas}'    => $namaKelas,
            '{jenis}'         => $jenis,
            '{waktu}'         => $waktu,
        ]);

        // 5. Dispatch SendWhatsAppNotificationJob per nomor penerima
        $dispatched = [];

        foreach ($resolvedRecipients as $recipient) {
            $toNumber      = $recipient['number'];
            $recipientType = $recipient['type'];

            // Catat log awal di tabel whatsapp_notification_logs
            $log = WhatsAppNotificationLog::create([
                'module'           => 'spikap',
                'recipient_type'   => $recipientType,
                'recipient_number' => $toNumber,
                'message'          => $pesan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => 'SPIKAP Emergency Notification Queued']),
                'related_type'     => 'spikap_laporan',
                'related_id'       => (string) $laporan->id,
            ]);

            // Dispatch job ke antrian queue
            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                'spikap_laporan',
                (string) $laporan->id,
                $recipientType,
                $log->id
            );

            $dispatched[] = [
                'number' => $toNumber,
                'type'   => $recipientType,
                'log_id' => $log->id,
            ];
        }

        SendSpikapFcmNotificationJob::dispatch($laporan->id, 'laporan_baru');

        return [
            'status'     => 'dispatched',
            'count'      => count($dispatched),
            'recipients' => $dispatched,
            'message'    => $pesan,
        ];
    }

    /**
     * Kirim notifikasi WhatsApp laporan biasa ke guru yang dituju (Wali Kelas / Guru BK).
     */
    public function sendBiasaNotificationToGuru(SpikapLaporan $laporan): array
    {
        if ($laporan->sifat_laporan !== 'biasa') {
            return ['status' => 'skipped', 'reason' => 'Bukan laporan biasa'];
        }

        $setting = SpikapNotifSetting::instance();
        if (!$setting->shouldNotifyGuruLaporanBiasa()) {
            return ['status' => 'skipped', 'reason' => 'Notifikasi WA laporan biasa dinonaktifkan'];
        }

        $laporan->loadMissing(['siswa.enrollmentAktif.kelas']);
        $siswa = $laporan->siswa;

        if (!$siswa) {
            return ['status' => 'failed', 'reason' => 'Siswa tidak ditemukan'];
        }

        $recipients = [];

        if ($laporan->tujuan_penerima === 'wali_kelas') {
            $hp = $this->resolver->resolveWaliKelas($siswa);
            if ($hp) {
                $recipients[] = ['number' => $hp, 'type' => 'wali_kelas'];
            } else {
                SpikapLogStatus::catatSistem(
                    $laporan->id,
                    'diterima',
                    'Wali kelas tidak memiliki nomor HP terdaftar, notifikasi WA laporan biasa tidak terkirim'
                );
            }
        } elseif ($laporan->tujuan_penerima === 'guru_bk') {
            $hpNumbers = $this->resolver->resolveGuruBk($siswa);
            foreach ($hpNumbers as $hp) {
                $recipients[] = ['number' => $hp, 'type' => 'guru_bk'];
            }
            if (empty($hpNumbers)) {
                SpikapLogStatus::catatSistem(
                    $laporan->id,
                    'diterima',
                    'Guru BK pembina kelas tidak memiliki nomor HP terdaftar, notifikasi WA laporan biasa tidak terkirim'
                );
            }
        }

        if (empty($recipients)) {
            return ['status' => 'skipped', 'reason' => 'Tidak ada penerima guru yang valid'];
        }

        $namaSiswa   = $siswa->name;
        $namaKelas   = $siswa->enrollmentAktif?->kelas?->name ?? '-';
        $jenis       = $laporan->label_jenis;
        $tujuanLabel = $laporan->tujuan_penerima === 'guru_bk' ? 'Guru BK' : 'Wali Kelas';
        $waktuObj    = $laporan->created_at ?? now();
        $waktu       = $waktuObj->translatedFormat('d F Y, H:i') . ' WIB';

        $namaApp   = $setting->getNamaAplikasi();
        $settingWa = \App\Models\PresensiNotificationSetting::where('status_presensi', 'spikap_biasa')->first();
        $defaultTemplate = "📋 *LAPORAN {nama_aplikasi} BARU*\nTujuan: {tujuan_guru}\nDari: {nama_siswa}\nKelas: {nama_kelas}\nJenis: {jenis}\nWaktu Lapor: {waktu}\n\nSilakan buka Portal Guru untuk meninjau detail aduan dan melakukan tindak lanjut.";
        $template = ($settingWa && !empty($settingWa->template_pesan)) ? $settingWa->template_pesan : $defaultTemplate;
        $pesan = strtr($template, [
            '{nama_aplikasi}' => $namaApp,
            '{tujuan_guru}'   => $tujuanLabel,
            '{nama_siswa}'    => $namaSiswa,
            '{nama_kelas}'    => $namaKelas,
            '{jenis}'         => $jenis,
            '{waktu}'         => $waktu,
        ]);

        $dispatched = [];
        foreach ($recipients as $recipient) {
            $toNumber      = $recipient['number'];
            $recipientType = $recipient['type'];

            $log = WhatsAppNotificationLog::create([
                'module'           => 'spikap',
                'recipient_type'   => $recipientType,
                'recipient_number' => $toNumber,
                'message'          => $pesan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => 'SPIKAP Biasa Notification Queued']),
                'related_type'     => 'spikap_laporan',
                'related_id'       => (string) $laporan->id,
            ]);

            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                'spikap_laporan',
                (string) $laporan->id,
                $recipientType,
                $log->id
            );

            $dispatched[] = ['number' => $toNumber, 'type' => $recipientType, 'log_id' => $log->id];
        }

        SendSpikapFcmNotificationJob::dispatch($laporan->id, 'laporan_baru');

        return ['status' => 'dispatched', 'count' => count($dispatched), 'recipients' => $dispatched];
    }

    /**
     * Kirim notifikasi WhatsApp konfirmasi & apresiasi ke nomor siswa dan/atau orang tua saat laporan dibuat.
     */
    public function sendApresiasiNotificationToSiswaDanOrtu(SpikapLaporan $laporan): array
    {
        $setting = SpikapNotifSetting::instance();
        if (!$setting->shouldNotifySiswaApresiasi()) {
            return ['status' => 'skipped', 'reason' => 'Notifikasi apresiasi siswa dinonaktifkan'];
        }

        $laporan->loadMissing(['siswa.enrollmentAktif.kelas']);
        $siswa = $laporan->siswa;
        if (!$siswa) {
            return ['status' => 'failed', 'reason' => 'Siswa tidak ditemukan'];
        }

        $targetTypes = $setting->getTargetPenerimaSiswa(); // e.g. ['siswa', 'orang_tua']
        $contacts    = $this->resolver->resolveKontakSiswa($siswa, $targetTypes);

        if (empty($contacts)) {
            return ['status' => 'skipped', 'reason' => 'Nomor kontak siswa/orang tua tidak ditemukan'];
        }

        $namaSiswa   = $siswa->name;
        $jenis       = $laporan->label_jenis;
        $tujuanLabel = $laporan->sifat_laporan === 'darurat'
            ? 'Tim Khusus Darurat Sekolah'
            : ($laporan->tujuan_penerima === 'guru_bk' ? 'Guru BK' : 'Wali Kelas');

        $dispatched = [];

        $namaApp = $setting->getNamaAplikasi();

        $settingSiswa = \App\Models\PresensiNotificationSetting::where('status_presensi', 'spikap_apresiasi_siswa')->first();
        $templateSiswa = ($settingSiswa && !empty($settingSiswa->template_pesan))
            ? $settingSiswa->template_pesan
            : "Halo *{nama_siswa}*,\n\nTerima kasih atas keberanianmu telah melapor di {nama_aplikasi}. Laporanmu (*{jenis}*) telah berhasil kami terima dengan aman dan rahasia.\n\nPihak sekolah ({tujuan_guru}) akan segera meninjau dan menindaklanjuti laporan ini. Tetap tenang dan selalu jaga keselamatan dirimu. Kami ada bersamamu.\n\n_Pesan otomatis Layanan {nama_aplikasi} Sekolah_";

        $settingOrtu = \App\Models\PresensiNotificationSetting::where('status_presensi', 'spikap_apresiasi_ortu')->first();
        $templateOrtu = ($settingOrtu && !empty($settingOrtu->template_pesan))
            ? $settingOrtu->template_pesan
            : "Yth. Orang Tua/Wali dari *{nama_siswa}*,\n\nPemberitahuan bahwa ananda telah menyampaikan aduan melalui layanan {nama_aplikasi} sekolah (*{jenis}*). Laporan telah tercatat dengan aman dan sedang dalam penanganan pihak sekolah ({tujuan_guru}).\n\nSekolah berkomitmen menjaga keselamatan dan kenyamanan setiap siswa.\n\n_Layanan Bimbingan & Konseling {nama_aplikasi} Sekolah_";

        foreach ($contacts as $contact) {
            $toNumber = $contact['number'];
            $type     = $contact['type'];

            $rawTemplate = ($type === 'siswa') ? $templateSiswa : $templateOrtu;
            $pesan = strtr($rawTemplate, [
                '{nama_aplikasi}' => $namaApp,
                '{nama_siswa}'    => $namaSiswa,
                '{jenis}'         => $jenis,
                '{tujuan_guru}'   => $tujuanLabel,
            ]);

            $log = WhatsAppNotificationLog::create([
                'module'           => 'spikap',
                'recipient_type'   => $type,
                'recipient_number' => $toNumber,
                'message'          => $pesan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => 'SPIKAP Apresiasi Notification Queued']),
                'related_type'     => 'spikap_laporan',
                'related_id'       => (string) $laporan->id,
            ]);

            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                'spikap_laporan',
                (string) $laporan->id,
                $type,
                $log->id
            );

            $dispatched[] = ['number' => $toNumber, 'type' => $type, 'log_id' => $log->id];
        }

        return ['status' => 'dispatched', 'count' => count($dispatched), 'recipients' => $dispatched];
    }

    /**
     * Kirim notifikasi WhatsApp perkembangan status kasus ke siswa dan/atau orang tua saat status diperbarui.
     */
    public function sendStatusUpdateNotificationToSiswaDanOrtu(SpikapLaporan $laporan, string $statusBaru, ?string $catatan = null): array
    {
        $setting = SpikapNotifSetting::instance();
        if (!$setting->shouldNotifySiswaTindakLanjut()) {
            return ['status' => 'skipped', 'reason' => 'Notifikasi perkembangan status ke siswa dinonaktifkan'];
        }

        $laporan->loadMissing(['siswa.enrollmentAktif.kelas']);
        $siswa = $laporan->siswa;
        if (!$siswa) {
            return ['status' => 'failed', 'reason' => 'Siswa tidak ditemukan'];
        }

        $targetTypes = $setting->getTargetPenerimaSiswa();
        $contacts    = $this->resolver->resolveKontakSiswa($siswa, $targetTypes);

        if (empty($contacts)) {
            return ['status' => 'skipped', 'reason' => 'Nomor kontak siswa/orang tua tidak ditemukan'];
        }

        $namaSiswa = $siswa->name;
        $statusLabel = match ($statusBaru) {
            'dalam_investigasi' => 'Dalam Investigasi / Penanganan',
            'selesai'           => 'Kasus Telah Selesai / Ditutup',
            'diterima'          => 'Laporan Diterima',
            default             => ucfirst($statusBaru),
        };

        $catatanText = $catatan ? "\n📝 *Catatan Petugas:* " . $catatan : "";
        $dispatched  = [];

        $namaApp = $setting->getNamaAplikasi();

        $settingStatusSiswa = \App\Models\PresensiNotificationSetting::where('status_presensi', 'spikap_status_siswa')->first();
        $templateStatusSiswa = ($settingStatusSiswa && !empty($settingStatusSiswa->template_pesan))
            ? $settingStatusSiswa->template_pesan
            : "Halo *{nama_siswa}*,\n\nAda perkembangan terbaru mengenai laporan {nama_aplikasi} yang kamu kirimkan:\n\n📌 *Status:* {status_baru}{catatan}\n\nKamu juga dapat melihat riwayat lengkap perkembangan kasusmu di Portal Siswa.\n\n_Layanan {nama_aplikasi} Sekolah_";

        $settingStatusOrtu = \App\Models\PresensiNotificationSetting::where('status_presensi', 'spikap_status_ortu')->first();
        $templateStatusOrtu = ($settingStatusOrtu && !empty($settingStatusOrtu->template_pesan))
            ? $settingStatusOrtu->template_pesan
            : "Yth. Orang Tua/Wali dari *{nama_siswa}*,\n\nKami menginformasikan perkembangan penanganan atas laporan {nama_aplikasi} ananda *{nama_siswa}*:\n\n📌 *Status:* {status_baru}{catatan}\n\nTerima kasih atas perhatian dan kerja samanya dalam mendampingi ananda.\n\n_Layanan Bimbingan & Konseling {nama_aplikasi} Sekolah_";

        foreach ($contacts as $contact) {
            $toNumber = $contact['number'];
            $type     = $contact['type'];

            $rawTemplate = ($type === 'siswa') ? $templateStatusSiswa : $templateStatusOrtu;
            $pesan = strtr($rawTemplate, [
                '{nama_aplikasi}' => $namaApp,
                '{nama_siswa}'    => $namaSiswa,
                '{status_baru}'   => $statusLabel,
                '{catatan}'       => $catatanText,
            ]);

            $log = WhatsAppNotificationLog::create([
                'module'           => 'spikap',
                'recipient_type'   => $type,
                'recipient_number' => $toNumber,
                'message'          => $pesan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => 'SPIKAP Status Update Notification Queued']),
                'related_type'     => 'spikap_laporan',
                'related_id'       => (string) $laporan->id,
            ]);

            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                'spikap_laporan',
                (string) $laporan->id,
                $type,
                $log->id
            );

            $dispatched[] = ['number' => $toNumber, 'type' => $type, 'log_id' => $log->id];
        }

        SendSpikapFcmNotificationJob::dispatch($laporan->id, 'update_status', $statusBaru, $catatan, auth()->id());

        return ['status' => 'dispatched', 'count' => count($dispatched), 'recipients' => $dispatched];
    }
}
