<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerPertemuan;
use App\Models\EkstrakurikulerPresensi;
use App\Models\EkstrakurikulerSiswa;
use App\Models\Guru;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Models\Siswa;
use App\Models\WhatsAppNotificationLog;
use Illuminate\Support\Facades\Log;

class UnattendedEkstrakurikulerNotificationService
{
    /**
     * Kirim notifikasi WA Opsi A (per sesi pertemuan) ke orang tua / penerima terkait
     * untuk anggota aktif ekstrakurikuler yang belum tercatat Hadir/Izin/Sakit (belum absen atau Alpa).
     */
    public function dispatchForPertemuan(EkstrakurikulerPertemuan $pertemuan, string $source = 'manual'): array
    {
        $sekolah = PengaturanSekolah::current();
        if ($sekolah && !($sekolah->enable_ekstrakurikuler ?? true)) {
            return [
                'success'          => false,
                'message'          => 'Fitur Ekstrakurikuler sedang dinonaktifkan di Pengaturan Presensi.',
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ['Feature disabled in school settings'],
            ];
        }

        $setting = PresensiNotificationSetting::where('status_presensi', 'ekstrakurikuler')->first();
        if (!$setting || !$setting->is_active) {
            return [
                'success'          => false,
                'message'          => 'Notifikasi WA Ekstrakurikuler belum diaktifkan di Pengaturan Notifikasi WA.',
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ['Setting ekstrakurikuler inactive or missing'],
            ];
        }

        if ($source === 'manual' && !$setting->canSendManualToday()) {
            return [
                'success'          => false,
                'message'          => 'Batas pengiriman manual notifikasi WA Ekstrakurikuler hari ini (5x) telah tercapai.',
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ['Daily manual limit reached'],
            ];
        }

        $pertemuan->loadMissing(['ekstrakurikuler.pembinas']);
        $ekskul = $pertemuan->ekstrakurikuler;
        if (!$ekskul) {
            return [
                'success'          => false,
                'message'          => 'Data ekstrakurikuler tidak ditemukan.',
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ['Ekstrakurikuler missing'],
            ];
        }

        // Anggota aktif ekstrakurikuler
        $members = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskul->id)
            ->where('status_aktif', true)
            ->with([
                'siswa' => fn ($q) => $q->with([
                    'enrollments' => fn ($eq) => $eq->where('academic_year_id', $ekskul->academic_year_id)
                        ->where('status', 'aktif')
                        ->with('kelas'),
                ]),
            ])
            ->get();

        // Siswa yang sudah punya keterangan sah pada pertemuan ini (hadir, izin, sakit)
        $excusedOrPresentIds = EkstrakurikulerPresensi::where('pertemuan_id', $pertemuan->id)
            ->whereIn('status', ['hadir', 'izin', 'sakit'])
            ->pluck('student_id')
            ->all();

        $dateLabel = $pertemuan->tanggal
            ? $pertemuan->tanggal->locale('id')->translatedFormat('l, d F Y')
            : now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y');
        $pembinaNames = $ekskul->pembinas->pluck('name')->implode(', ') ?: '-';
        $judulKegiatan = $pertemuan->judul_kegiatan ?: "Pertemuan ke-{$pertemuan->pertemuan_ke}";

        $recipients = is_array($setting->recipients) && count($setting->recipients) > 0
            ? $setting->recipients
            : ['ortu'];

        $dispatched = 0;
        $skipped = 0;
        $totalUnattended = 0;
        $errors = [];

        foreach ($members as $m) {
            $student = $m->siswa;
            if (!$student) {
                $skipped++;
                continue;
            }

            if (in_array($student->id, $excusedOrPresentIds, true)) {
                $skipped++;
                continue;
            }

            $totalUnattended++;

            // Deduplikasi per pertemuan + siswa via log payload JSON
            $alreadySentForPertemuan = WhatsAppNotificationLog::where('related_type', 'presensi_ekstrakurikuler')
                ->where('related_id', $student->id)
                ->where('response_payload', 'like', '%"pertemuan_id":"' . $pertemuan->id . '"%')
                ->whereIn('status', ['sent', 'pending'])
                ->exists();

            if ($alreadySentForPertemuan) {
                $skipped++;
                continue;
            }

            $kelas = $student->enrollments->first()?->kelas;
            $waliKelas = $kelas ? $kelas->waliKelas($ekskul->academic_year_id) : null;

            $message = PresensiNotificationSetting::renderTemplate('ekstrakurikuler', [
                'nama_siswa'       => $student->name,
                'kelas'            => $kelas?->name ?? '-',
                'tanggal'          => $dateLabel,
                'status_kehadiran' => 'Belum Hadir Ekstrakurikuler',
                'nama_wali_kelas'  => $waliKelas?->name ?? '-',
                'nama_ekskul'      => $ekskul->nama_ekskul,
                'judul_kegiatan'   => $judulKegiatan,
                'nama_pembina'     => $pembinaNames,
            ]);

            $targetNumbers = [];
            if (in_array('ortu', $recipients, true)) {
                $phone = $this->normalizePhone($student->no_hp_orang_tua ?: $student->no_hp);
                if ($phone) {
                    $targetNumbers['ortu'] = $phone;
                }
            }
            if (in_array('wali_kelas', $recipients, true) && $waliKelas) {
                $wkPhone = $this->normalizePhone($waliKelas->no_hp ?? $waliKelas->phone ?? null);
                if ($wkPhone) {
                    $targetNumbers['wali_kelas'] = $wkPhone;
                }
            }

            if (empty($targetNumbers)) {
                $skipped++;
                continue;
            }

            foreach ($targetNumbers as $recipientType => $cleanPhone) {
                $log = WhatsAppNotificationLog::create([
                    'module'           => 'presensi',
                    'recipient_type'   => $recipientType,
                    'recipient_number' => $cleanPhone,
                    'message'          => $message,
                    'status'           => 'pending',
                    'response_payload' => json_encode([
                        'source'             => $source,
                        'pertemuan_id'       => $pertemuan->id,
                        'ekstrakurikuler_id' => $ekskul->id,
                        'student_id'         => $student->id,
                    ]),
                    'related_type'     => 'presensi_ekstrakurikuler',
                    'related_id'       => $student->id,
                ]);

                SendWhatsAppNotificationJob::dispatch(
                    $cleanPhone,
                    $message,
                    'presensi_ekstrakurikuler',
                    $student->id,
                    $recipientType,
                    $log->id
                );

                $dispatched++;
            }
        }

        if ($source === 'manual' && $dispatched > 0) {
            $setting->recordManualSend();
        }

        Log::info("UnattendedEkstrakurikulerNotificationService [{$source}]: Pertemuan {$pertemuan->id} ({$ekskul->nama_ekskul}), Dispatched={$dispatched}, Unattended={$totalUnattended}, Skipped={$skipped}");

        return [
            'success'          => true,
            'message'          => $dispatched > 0
                ? "Berhasil mengantrekan {$dispatched} pesan WA untuk anggota {$ekskul->nama_ekskul} yang belum hadir."
                : "Tidak ada pesan baru yang dikirim (belum hadir: {$totalUnattended}, dilewati/sudah terkirim: {$skipped}).",
            'dispatched'       => $dispatched,
            'skipped'          => $skipped,
            'total_unattended' => $totalUnattended,
            'errors'           => $errors,
        ];
    }

    /**
     * Siapkan payload WA per baris siswa (nomor HP + pesan ter-render) untuk tombol WA Ortu.
     */
    public function buildPersonalWaPayload(
        Siswa $student,
        Ekstrakurikuler $ekskul,
        ?EkstrakurikulerPertemuan $pertemuan,
        string $kelasName,
        ?Guru $waliKelas = null,
        string $statusKehadiran = 'Belum Hadir Ekstrakurikuler',
        ?string $fallbackDate = null
    ): array {
        $rawPhone = (string) ($student->no_hp_orang_tua ?: $student->no_hp ?: '');
        $cleanPhone = $this->normalizePhone($rawPhone) ?? '';

        $dateLabel = $pertemuan?->tanggal
            ? $pertemuan->tanggal->locale('id')->translatedFormat('l, d F Y')
            : ($fallbackDate
                ? \Carbon\Carbon::parse($fallbackDate)->locale('id')->translatedFormat('l, d F Y')
                : now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y'));

        $pembinaNames = $ekskul->pembinas->pluck('name')->implode(', ') ?: '-';
        $judulKegiatan = $pertemuan?->judul_kegiatan
            ?: ($pertemuan ? "Pertemuan ke-{$pertemuan->pertemuan_ke}" : 'Latihan Ekstrakurikuler');

        $waTextRaw = PresensiNotificationSetting::renderTemplate('ekstrakurikuler', [
            'nama_siswa'       => $student->name,
            'kelas'            => $kelasName,
            'tanggal'          => $dateLabel,
            'status_kehadiran' => $statusKehadiran,
            'nama_wali_kelas'  => $waliKelas?->name ?? '-',
            'nama_ekskul'      => $ekskul->nama_ekskul,
            'judul_kegiatan'   => $judulKegiatan,
            'nama_pembina'     => $pembinaNames,
        ]);

        return [
            'raw_phone'   => $rawPhone,
            'clean_phone' => $cleanPhone,
            'wa_text_raw' => $waTextRaw,
        ];
    }

    /**
     * Kirim pesan WA langsung via Gateway dari modal WA Ortu per siswa.
     */
    public function sendDirectMessage(string $studentId, string $targetPhone, string $messageText): array
    {
        $notifSetting = PresensiNotificationSetting::where('status_presensi', 'ekstrakurikuler')->first();
        if (!$notifSetting || !$notifSetting->is_active) {
            return [
                'success' => false,
                'type'    => 'warning',
                'message' => 'Fitur notifikasi WhatsApp Ekstrakurikuler sedang dinonaktifkan di Portal Sistem. Silakan pakai tombol "Buka di WA (Web / HP)".',
            ];
        }

        $cleanPhone = $this->normalizePhone($targetPhone);
        if (!$cleanPhone) {
            return [
                'success' => false,
                'type'    => 'error',
                'message' => 'Nomor WhatsApp orang tua tidak valid atau masih kosong.',
            ];
        }

        $waService = app(WhatsAppGatewayService::class);
        $sent = $waService->sendMessage(
            toNumber: $cleanPhone,
            message: $messageText,
            relatedType: 'presensi_ekstrakurikuler',
            relatedId: $studentId,
            recipientType: 'ortu',
            logId: null,
            ignoreDelay: true
        );

        if ($sent) {
            return [
                'success' => true,
                'type'    => 'success',
                'message' => "Pesan WhatsApp berhasil dikirim ke nomor {$cleanPhone}!",
            ];
        }

        return [
            'success' => false,
            'type'    => 'error',
            'message' => 'Gagal mengirim via sistem gateway WhatsApp. Anda dapat menggunakan tombol "Buka di WA (Web / HP)" sebagai alternatif.',
        ];
    }

    public function normalizePhone(?string $rawPhone): ?string
    {
        $clean = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
        if (empty($clean)) {
            return null;
        }

        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        return $clean;
    }
}

