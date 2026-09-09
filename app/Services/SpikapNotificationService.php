<?php

namespace App\Services;

use App\Models\SpikapLaporan;
use App\Models\SpikapNotifSetting;
use App\Models\SpikapLogStatus;
use App\Models\WhatsAppNotificationLog;
use App\Jobs\SendWhatsAppNotificationJob;
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

        $pesan = "🚨 *LAPORAN DARURAT SPIKAP*\n"
               . "Dari: {$namaSiswa}\n"
               . "Kelas: {$namaKelas}\n"
               . "Jenis: {$jenis}\n"
               . "Waktu Lapor: {$waktu}\n\n"
               . "Silakan buka portal guru untuk detail dan tindak lanjut.";

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

        return [
            'status'     => 'dispatched',
            'count'      => count($dispatched),
            'recipients' => $dispatched,
            'message'    => $pesan,
        ];
    }
}
