<?php

namespace App\Observers;

use App\Models\Presensi;
use App\Models\PresensiNotificationSetting;
use App\Models\WhatsAppNotificationLog;
use App\Models\PengaturanSekolah;
use App\Services\RecipientResolverService;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;

class PresensiObserver
{
    public function saved(Presensi $presensi): void
    {
        // 1. Cek perubahan di kolom `status` (saat absen datang)
        if ($presensi->wasRecentlyCreated || $presensi->wasChanged('status')) {
            $this->processNotification($presensi, $presensi->status);
        }

        // 2. Cek perubahan di kolom `status_pulang` (saat absen pulang / update manual)
        if ($presensi->wasChanged('status_pulang')) {
            $this->processNotification($presensi, $presensi->status_pulang);
        }
    }

    private function processNotification(Presensi $presensi, ?string $status): void
    {
        if (empty($status)) {
            return;
        }

        // --- Pencegahan Spam Notifikasi Masa Lalu ---
        // Jika admin menginput/mengedit presensi untuk tanggal sebelum atau sesudah hari ini,
        // jangan kirimkan notifikasi WA real-time untuk mencegah kebingungan orang tua.
        if ($presensi->date && !$presensi->date->isToday()) {
            return;
        }

        // Ambil aturan untuk status ini
        $setting = PresensiNotificationSetting::where('status_presensi', $status)->first();
        
        if (!$setting || !$setting->is_active || empty($setting->recipients) || empty($setting->template_pesan)) {
            return;
        }

        $presensi->loadMissing(['siswa', 'kelas', 'enrollment']);
        $student = $presensi->siswa;
        
        if (!$student) {
            return;
        }

        // --- Dedup Guard ---
        // Cek apakah untuk record presensi ini (related_id) HARI INI, dengan STATUS INI,
        // sudah pernah di-dispatch (sent/pending). 
        // Karena kolom status tidak ada di log, kita pakai JSON payload/related_type.
        // Kita gunakan related_type = "presensi_{$status}" supaya akurat.
        $relatedType = "presensi_{$status}";
        
        $alreadyDispatched = WhatsAppNotificationLog::where('related_type', $relatedType)
            ->where('related_id', $presensi->id)
            ->whereIn('status', ['sent', 'pending', 'failed']) // Kalau failed pun jangan loop dobel, tunggu retry dari Job saja
            ->exists();

        if ($alreadyDispatched) {
            return; // Skip, sudah pernah di-trigger
        }
        // --------------------

        // Resolve Penerima
        $resolver = app(RecipientResolverService::class);
        $resolvedRecipients = $resolver->resolveRecipients($setting->recipients, $student);

        if (empty($resolvedRecipients)) {
            return;
        }

        // Render Template
        $pengaturan = PengaturanSekolah::current();
        $namaSekolah = $pengaturan ? $pengaturan->school_name : 'Sekolah';
        $namaKelas = $presensi->kelas ? $presensi->kelas->name : '-';
        $tanggal = $presensi->date ? $presensi->date->format('d-m-Y') : Carbon::today()->format('d-m-Y');
        
        // Jam: gunakan scan_time untuk status, scan_out_time untuk status_pulang, default ke waktu sekarang
        $jam = Carbon::now()->format('H:i');
        if ($status === $presensi->status && $presensi->scan_time) {
            $jam = Carbon::parse($presensi->scan_time)->format('H:i');
        } elseif ($status === $presensi->status_pulang && $presensi->scan_out_time) {
            $jam = Carbon::parse($presensi->scan_out_time)->format('H:i');
        }

        // Cari nama wali kelas untuk template
        $namaWaliKelas = '-';
        $hpWaliKelas = $resolver->resolveWaliKelas($student); // Bisa manfaatkan lagi
        if ($hpWaliKelas) {
            $guru = \App\Models\Guru::where('no_hp', $hpWaliKelas)->first();
            if ($guru) {
                $namaWaliKelas = $guru->name ?? $guru->nama_guru ?? '-';
            }
        }

        $pesanAsli = $setting->template_pesan;
        $pesan = str_replace(
            ['{nama_siswa}', '{kelas}', '{tanggal}', '{jam}', '{status_kehadiran}', '{nama_wali_kelas}', '{nama_sekolah}'],
            [$student->name ?? '-', $namaKelas, $tanggal, $jam, ucfirst($status), $namaWaliKelas, $namaSekolah],
            $pesanAsli
        );

        // Dispatch Job untuk setiap nomor
        foreach ($resolvedRecipients as $recipient) {
            $toNumber = $recipient['number'];
            $recipientType = $recipient['type']; // misal 'ortu', 'wali_kelas', 'Guru BK'

            // Ciptakan log berstatus "pending" SEBELUM job dieksekusi agar dedup bekerja sempurna
            $log = WhatsAppNotificationLog::create([
                'module' => 'presensi',
                'recipient_type' => $recipientType,
                'recipient_number' => $toNumber,
                'message' => $pesan,
                'status' => 'pending',
                'response_payload' => json_encode(['info' => 'Job dispatched']),
                'related_type' => $relatedType,
                'related_id' => $presensi->id,
            ]);

            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                $relatedType,
                $presensi->id,
                $recipientType,
                $log->id
            );
        }
    }
}
