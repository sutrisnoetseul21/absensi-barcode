<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Pengaduan;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Models\WhatsAppNotificationLog;
use Illuminate\Support\Facades\Log;

class PengaduanNotificationService
{
    public function __construct(
        protected RecipientResolverService $resolver
    ) {}

    /**
     * Kirim notifikasi WA saat ada pengaduan masyarakat baru dari /pengaduan:
     * 1) Ke petugas / pejabat sekolah
     * 2) Tanda terima ke nomor WA pelapor (jika diisi)
     */
    public function sendPengaduanBaruNotifications(Pengaduan $pengaduan): void
    {
        $this->sendToPetugas($pengaduan);
        $this->sendTandaTerimaToPelapor($pengaduan);
    }

    /**
     * Kirim WA ke pejabat/petugas sekolah saat pengaduan baru masuk.
     */
    public function sendToPetugas(Pengaduan $pengaduan): array
    {
        $setting = PresensiNotificationSetting::where('status_presensi', 'pengaduan_publik_petugas')->first();
        if ($setting && !$setting->is_active) {
            return ['status' => 'skipped', 'reason' => 'Notifikasi WA petugas pelayanan publik dinonaktifkan'];
        }

        $pengaduan->loadMissing('kategori');
        $recipientKeys = ($setting && !empty($setting->recipients))
            ? (array) $setting->recipients
            : ['kepala_sekolah'];

        $targets = $this->resolveStaffRecipients($recipientKeys);
        if (empty($targets)) {
            return ['status' => 'skipped', 'reason' => 'Tidak ada nomor HP petugas yang ditemukan'];
        }

        $namaSekolah = PengaturanSekolah::current()?->school_name ?? 'Sekolah';
        $kategori    = $pengaduan->kategori?->nama_kategori ?? 'Umum';
        $waktu       = ($pengaduan->created_at ?? now())->translatedFormat('d F Y, H:i') . ' WIB';

        $defaultTemplate = "📩 *PENGADUAN / ASPIRASI PUBLIK BARU*\n\n"
            . "Dari: *{nama_pelapor}*\n"
            . "No. HP: {no_hp}\n"
            . "Email: {email}\n"
            . "Kategori: *{kategori}*\n"
            . "Waktu: {waktu}\n\n"
            . "📝 *Isi Pesan:*\n\"{isi_pengaduan}\"\n\n"
            . "Silakan buka Portal Web / Admin untuk meninjau dan menindaklanjuti.";

        $template = ($setting && !empty($setting->template_pesan)) ? $setting->template_pesan : $defaultTemplate;
        $pesan = strtr($template, [
            '{nama_sekolah}'  => $namaSekolah,
            '{nama_pelapor}'  => $pengaduan->nama ?? '-',
            '{no_hp}'         => $pengaduan->no_hp ?: '-',
            '{email}'         => $pengaduan->email ?: '-',
            '{kategori}'      => $kategori,
            '{isi_pengaduan}' => $pengaduan->isi_pengaduan ?? '-',
            '{waktu}'         => $waktu,
        ]);

        $dispatched = [];
        foreach ($targets as $target) {
            $toNumber = $target['number'];
            $type     = $target['type'];

            $log = WhatsAppNotificationLog::create([
                'module'           => 'pelayanan_publik',
                'recipient_type'   => $type,
                'recipient_number' => $toNumber,
                'message'          => $pesan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => 'Pengaduan Publik Petugas Notification Queued']),
                'related_type'     => 'pengaduan',
                'related_id'       => (string) $pengaduan->id,
            ]);

            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                'pengaduan',
                (string) $pengaduan->id,
                $type,
                $log->id
            );

            $dispatched[] = ['number' => $toNumber, 'type' => $type, 'log_id' => $log->id];
        }

        return ['status' => 'dispatched', 'count' => count($dispatched)];
    }

    /**
     * Kirim WA tanda terima ke nomor HP pelapor saat pengaduan baru masuk.
     */
    public function sendTandaTerimaToPelapor(Pengaduan $pengaduan): array
    {
        $noHp = trim((string) ($pengaduan->no_hp ?? ''));
        if ($noHp === '') {
            return ['status' => 'skipped', 'reason' => 'Pelapor tidak mengisi nomor HP'];
        }

        $setting = PresensiNotificationSetting::where('status_presensi', 'pengaduan_publik_pelapor_baru')->first();
        if ($setting && !$setting->is_active) {
            return ['status' => 'skipped', 'reason' => 'Notifikasi WA tanda terima pelapor dinonaktifkan'];
        }

        $pengaduan->loadMissing('kategori');
        $namaSekolah = PengaturanSekolah::current()?->school_name ?? 'Sekolah';
        $kategori    = $pengaduan->kategori?->nama_kategori ?? 'Umum';
        $waktu       = ($pengaduan->created_at ?? now())->translatedFormat('d F Y, H:i') . ' WIB';

        $defaultTemplate = "Halo Bapak/Ibu *{nama_pelapor}*,\n\n"
            . "Terima kasih telah menyampaikan aspirasi / pengaduan melalui Layanan Publik *{nama_sekolah}*.\n\n"
            . "📌 *Kategori:* {kategori}\n"
            . "🗓️ *Waktu diterima:* {waktu}\n"
            . "📌 *Status:* Menunggu Tindak Lanjut\n\n"
            . "Pesan Anda telah tercatat di sistem kami dan akan segera ditinjau oleh petugas terkait. Terima kasih.";

        $template = ($setting && !empty($setting->template_pesan)) ? $setting->template_pesan : $defaultTemplate;
        $pesan = strtr($template, [
            '{nama_sekolah}'  => $namaSekolah,
            '{nama_pelapor}'  => $pengaduan->nama ?? '-',
            '{kategori}'      => $kategori,
            '{isi_pengaduan}' => $pengaduan->isi_pengaduan ?? '-',
            '{waktu}'         => $waktu,
        ]);

        $log = WhatsAppNotificationLog::create([
            'module'           => 'pelayanan_publik',
            'recipient_type'   => 'pelapor',
            'recipient_number' => $noHp,
            'message'          => $pesan,
            'status'           => 'pending',
            'response_payload' => json_encode(['info' => 'Pengaduan Publik Tanda Terima Pelapor Queued']),
            'related_type'     => 'pengaduan',
            'related_id'       => (string) $pengaduan->id,
        ]);

        SendWhatsAppNotificationJob::dispatch(
            $noHp,
            $pesan,
            'pengaduan',
            (string) $pengaduan->id,
            'pelapor',
            $log->id
        );

        return ['status' => 'dispatched', 'log_id' => $log->id];
    }

    /**
     * Kirim WA ke pelapor saat status pengaduan diperbarui (diproses / selesai).
     */
    public function sendStatusUpdateToPelapor(Pengaduan $pengaduan): array
    {
        $noHp = trim((string) ($pengaduan->no_hp ?? ''));
        if ($noHp === '') {
            return ['status' => 'skipped', 'reason' => 'Pelapor tidak mengisi nomor HP'];
        }

        $setting = PresensiNotificationSetting::where('status_presensi', 'pengaduan_publik_pelapor_status')->first();
        if ($setting && !$setting->is_active) {
            return ['status' => 'skipped', 'reason' => 'Notifikasi WA update status pengaduan dinonaktifkan'];
        }

        $pengaduan->loadMissing('kategori');
        $namaSekolah = PengaturanSekolah::current()?->school_name ?? 'Sekolah';
        $kategori    = $pengaduan->kategori?->nama_kategori ?? 'Umum';
        $statusLabel = match ($pengaduan->status) {
            'diproses' => 'Sedang Diproses / Ditindaklanjuti',
            'selesai'  => 'Selesai Ditindaklanjuti',
            default    => 'Menunggu Review',
        };

        $defaultTemplate = "Halo Bapak/Ibu *{nama_pelapor}*,\n\n"
            . "Informasi perkembangan terbaru atas aspirasi / pengaduan Anda di Layanan Publik *{nama_sekolah}*:\n\n"
            . "📌 *Kategori:* {kategori}\n"
            . "📌 *Status Terbaru:* *{status_baru}*\n\n"
            . "Terima kasih atas masukan dan partisipasi Anda demi kemajuan layanan *{nama_sekolah}*.";

        $template = ($setting && !empty($setting->template_pesan)) ? $setting->template_pesan : $defaultTemplate;
        $pesan = strtr($template, [
            '{nama_sekolah}'  => $namaSekolah,
            '{nama_pelapor}'  => $pengaduan->nama ?? '-',
            '{kategori}'      => $kategori,
            '{status_baru}'   => $statusLabel,
            '{isi_pengaduan}' => $pengaduan->isi_pengaduan ?? '-',
        ]);

        $log = WhatsAppNotificationLog::create([
            'module'           => 'pelayanan_publik',
            'recipient_type'   => 'pelapor',
            'recipient_number' => $noHp,
            'message'          => $pesan,
            'status'           => 'pending',
            'response_payload' => json_encode(['info' => 'Pengaduan Publik Status Update Queued']),
            'related_type'     => 'pengaduan',
            'related_id'       => (string) $pengaduan->id,
        ]);

        SendWhatsAppNotificationJob::dispatch(
            $noHp,
            $pesan,
            'pengaduan',
            (string) $pengaduan->id,
            'pelapor',
            $log->id
        );

        return ['status' => 'dispatched', 'log_id' => $log->id];
    }

    /**
     * Resolve nomor HP pejabat/jabatan sekolah atau grup WA (tanpa butuh objek Siswa).
     */
    protected function resolveStaffRecipients(array $recipientKeys): array
    {
        $resolved    = [];
        $seenNumbers = [];

        foreach ($recipientKeys as $key) {
            if (str_starts_with($key, 'GROUP:')) {
                $groupId = substr($key, 6);
                if (!empty($groupId) && str_ends_with($groupId, '@g.us') && !isset($seenNumbers[$groupId])) {
                    $resolved[]            = ['number' => $groupId, 'type' => 'whatsapp_group'];
                    $seenNumbers[$groupId] = true;
                }
                continue;
            }

            $namaJabatan = str_starts_with($key, 'jabatan:')
                ? substr($key, strlen('jabatan:'))
                : $key;

            $numbers = $this->resolver->resolveByJabatan($namaJabatan);
            foreach ($numbers as $hp) {
                if ($hp && !isset($seenNumbers[$hp])) {
                    $resolved[]       = ['number' => $hp, 'type' => $key];
                    $seenNumbers[$hp] = true;
                }
            }
        }

        return $resolved;
    }
}

