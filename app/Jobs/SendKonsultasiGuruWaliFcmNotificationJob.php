<?php

namespace App\Jobs;

use App\Models\FcmDeviceToken;
use App\Models\KonsultasiGuruWali;
use App\Models\PesanKonsultasiGuruWali;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Job pengiriman Push Notification (FCM) ke HP Android Guru Wali
 * ketika siswa mengajukan permohonan bimbingan/cerita baru atau mengirim pesan obrolan.
 */
class SendKonsultasiGuruWaliFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $konsultasiId,
        public readonly string $eventType = 'pengajuan_baru', // 'pengajuan_baru' | 'pesan_baru'
        public readonly ?string $pesanId = null
    ) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $konsultasi = KonsultasiGuruWali::with(['siswa.enrollmentAktif.kelas', 'guru.user'])->find($this->konsultasiId);
        if (!$konsultasi) {
            Log::warning("SendKonsultasiGuruWaliFcmNotificationJob: Konsultasi #{$this->konsultasiId} tidak ditemukan.");
            return;
        }

        $guru = $konsultasi->guru;
        $guruUser = $guru?->user;

        if (!$guruUser && $konsultasi->teacher_id) {
            $guruUser = User::where('teacher_id', $konsultasi->teacher_id)
                ->orWhere('id', $guru?->user_id)
                ->first();
        }

        if (!$guruUser) {
            Log::info("SendKonsultasiGuruWaliFcmNotificationJob: Guru Wali #{$konsultasi->teacher_id} belum memiliki akun User terdaftar.");
            return;
        }

        // Ambil token FCM perangkat Android milik Guru Wali
        $tokens = FcmDeviceToken::where('user_id', $guruUser->id)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            Log::info("SendKonsultasiGuruWaliFcmNotificationJob: Guru Wali {$guruUser->name} belum memiliki token FCM perangkat Android terdaftar.");
            return;
        }

        $student = $konsultasi->siswa;
        $studentName = $student?->name ?? 'Siswa Binaan';
        $kelasName = $student?->enrollmentAktif?->kelas?->name ?? 'Rombel';

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
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        $serverKey = config('services.firebase.server_key') ?: env('FCM_SERVER_KEY');

        if (!empty($serverKey)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $tokens,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                        'android_channel_id' => 'konsultasi_guru_wali_channel',
                    ],
                    'data' => $payload,
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    Log::info("SendKonsultasiGuruWaliFcmNotificationJob: Push notification terkirim ke " . count($tokens) . " perangkat Guru Wali {$guruUser->name}.");
                } else {
                    Log::warning("SendKonsultasiGuruWaliFcmNotificationJob: Gagal mengirim FCM. Respons: " . $response->body());
                }
            } catch (Throwable $e) {
                Log::error("SendKonsultasiGuruWaliFcmNotificationJob error: " . $e->getMessage());
            }
        } else {
            // Mode Simulasi / Logging jika FCM Server Key belum di-set
            Log::info("SendKonsultasiGuruWaliFcmNotificationJob (Simulated FCM): Title: [{$title}] Body: [{$body}] Ke " . count($tokens) . " perangkat Guru Wali #{$guruUser->id} ({$guruUser->name}).");
        }
    }
}
