<?php

namespace App\Jobs;

use App\Models\KonsultasiGuruWali;
use App\Models\PesanKonsultasiGuruWali;
use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Job pengiriman Push Notification (FCM HTTP v1) untuk modul SI-WALI:
 * 1. Ke HP Android Guru Wali ketika siswa mengajukan permohonan bimbingan/cerita baru atau mengirim pesan obrolan.
 * 2. Ke HP Android Siswa ketika Guru Wali membalas pesan cerita atau menetapkan jadwal bimbingan.
 */
class SendKonsultasiGuruWaliFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $konsultasiId,
        public readonly string $eventType = 'pengajuan_baru', // 'pengajuan_baru' | 'pesan_baru' | 'balasan_guru' | 'jadwal_bimbingan'
        public readonly ?string $pesanId = null
    ) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(FcmPushService $fcm): void
    {
        $konsultasi = KonsultasiGuruWali::with(['siswa.enrollmentAktif.kelas', 'siswa.user', 'guru.user'])->find($this->konsultasiId);
        if (!$konsultasi) {
            Log::warning("SendKonsultasiGuruWaliFcmNotificationJob: Konsultasi #{$this->konsultasiId} tidak ditemukan.");
            return;
        }

        $student = $konsultasi->siswa;
        $studentName = $student?->name ?? 'Siswa Binaan';
        $kelasName = $student?->enrollmentAktif?->kelas?->name ?? 'Rombel';

        $guru = $konsultasi->guru;
        $guruUser = $guru?->user;
        if (!$guruUser && $konsultasi->teacher_id) {
            $guruUser = User::where('teacher_id', $konsultasi->teacher_id)
                ->orWhere('id', $guru?->user_id)
                ->first();
        }

        // =====================================================================
        // Skenario A: Guru Wali Membalas Pesan atau Menjadwalkan -> Kirim ke Siswa
        // =====================================================================
        if (in_array($this->eventType, ['balasan_guru', 'jadwal_bimbingan'])) {
            $siswaUser = $student?->user;
            if (!$siswaUser) {
                Log::info("SendKonsultasiGuruWaliFcmNotificationJob: Siswa #{$student?->id} belum memiliki user akun.");
                return;
            }

            $namaGuru = $guru?->name ?? 'Guru Wali';

            if ($this->eventType === 'jadwal_bimbingan') {
                $waktu = $konsultasi->usulan_tanggal_waktu
                    ? \Carbon\Carbon::parse($konsultasi->usulan_tanggal_waktu)->translatedFormat('d F Y, H:i') . ' WIB'
                    : 'Segera';
                $title = "📅 Jadwal Bimbingan dari {$namaGuru}";
                $body = "Guru Walimu telah menetapkan jadwal pertemuan bimbingan: {$waktu}.";
            } else {
                // Balasan pesan obrolan
                $pesanRecord = null;
                if ($this->pesanId) {
                    $pesanRecord = PesanKonsultasiGuruWali::find($this->pesanId);
                }
                if (!$pesanRecord) {
                    $pesanRecord = PesanKonsultasiGuruWali::where('konsultasi_id', $konsultasi->id)
                        ->where('sender_type', 'guru')
                        ->latest('created_at')
                        ->first();
                }

                $isiPesan = $pesanRecord?->pesan ?? 'Guru Walimu telah membalas ceritamu.';
                $ringkasan = Str::limit($isiPesan, 95);

                $title = "💬 Pesan dari Guru Wali ({$namaGuru})";
                $body = $ringkasan;
            }

            $payload = [
                'type' => 'konsultasi_guru_wali',
                'event_type' => (string) $this->eventType,
                'konsultasi_id' => (string) $konsultasi->id,
                'student_id' => (string) $konsultasi->student_id,
                'title' => $title,
                'body' => $body,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ];

            try {
                $res = $fcm->sendToUser($siswaUser, $title, $body, $payload);
                Log::info("SendKonsultasiGuruWaliFcmNotificationJob: Balasan Guru dikirim ke Siswa {$siswaUser->name}: " . json_encode($res));
            } catch (Throwable $e) {
                Log::error("SendKonsultasiGuruWaliFcmNotificationJob: Gagal kirim ke siswa: " . $e->getMessage());
            }

            return;
        }

        // =====================================================================
        // Skenario B: Siswa Mengirim Cerita / Permohonan Bimbingan -> Kirim ke Guru Wali
        // =====================================================================
        if (!$guruUser) {
            Log::info("SendKonsultasiGuruWaliFcmNotificationJob: Guru Wali #{$konsultasi->teacher_id} belum memiliki akun User terdaftar.");
            return;
        }

        if ($this->eventType === 'pesan_baru') {
            $pesanRecord = null;
            if ($this->pesanId) {
                $pesanRecord = PesanKonsultasiGuruWali::find($this->pesanId);
            }
            if (!$pesanRecord) {
                $pesanRecord = PesanKonsultasiGuruWali::where('konsultasi_id', $konsultasi->id)
                    ->where('sender_type', 'siswa')
                    ->latest('created_at')
                    ->first();
            }

            $isiPesan = $pesanRecord?->pesan ?? 'Mengirim pesan baru dalam Ruang Aman Cerita.';
            $ringkasanPesan = Str::limit($isiPesan, 95);

            $title = "💬 Pesan Cerita dari {$studentName} ({$kelasName})";
            $body = $ringkasanPesan;
        } else {
            // Pengajuan bimbingan baru
            $topik = $konsultasi->topik_konsultasi;
            $detail = Str::limit($konsultasi->detail_permasalahan, 85);

            $title = "💬 Cerita Baru: {$studentName} ({$kelasName})";
            $body = "[{$konsultasi->kategori_pendampingan}] {$topik}: \"{$detail}\"";
        }

        $payload = [
            'type' => 'konsultasi_guru_wali',
            'event_type' => (string) $this->eventType,
            'konsultasi_id' => (string) $konsultasi->id,
            'student_id' => (string) $konsultasi->student_id,
            'student_name' => (string) $studentName,
            'class_name' => (string) $kelasName,
            'kategori' => (string) $konsultasi->kategori_pendampingan,
            'mode' => (string) $konsultasi->mode_konsultasi,
            'title' => $title,
            'body' => $body,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        try {
            $res = $fcm->sendToUser($guruUser, $title, $body, $payload);
            Log::info("SendKonsultasiGuruWaliFcmNotificationJob: Notifikasi dikirim ke Guru Wali {$guruUser->name}: " . json_encode($res));
        } catch (Throwable $e) {
            Log::error("SendKonsultasiGuruWaliFcmNotificationJob: Gagal kirim ke Guru Wali: " . $e->getMessage());
        }
    }
}
