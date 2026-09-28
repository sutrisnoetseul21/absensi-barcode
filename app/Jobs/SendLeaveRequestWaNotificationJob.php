<?php

namespace App\Jobs;

use App\Models\LeaveRequest;
use App\Models\KelasAjaran;
use App\Models\PresensiNotificationSetting;
use App\Models\TahunAjaran;
use App\Models\WhatsAppNotificationLog;
use App\Services\WhatsAppGatewayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Throwable;

/**
 * Job pengiriman notifikasi WhatsApp kepada Wali Kelas
 * ketika siswa mengajukan permohonan ijin/sakit.
 *
 * Fitur utama:
 * - Idempoten: teks & tiap file media hanya dikirim sekali (tidak re-kirim saat retry)
 * - Graceful: tidak melempar exception jika HP wali kelas tidak ada
 * - Retry: melempar exception jika API WA gagal (agar Laravel me-retry job)
 * - Timeout: 120 detik (toleransi 3 file × 30 detik + margin)
 */
class SendLeaveRequestWaNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120; // Harus < retry_after config/queue.php (set 150)

    public function __construct(
        public readonly string $leaveRequestId
    ) {}

    public function backoff(): array
    {
        // Retry bertahap: 1 menit, 5 menit
        return [60, 300];
    }

    public function handle(WhatsAppGatewayService $waService): void
    {
        // 1. Muat record
        $record = LeaveRequest::with(['student.enrollmentAktif.kelas'])
            ->find($this->leaveRequestId);

        if (!$record) {
            Log::warning("SendLeaveRequestWaNotificationJob: record {$this->leaveRequestId} tidak ditemukan. Job diabaikan.");
            return;
        }

        // 2. Pastikan ijin masih pending (bisa saja sudah diapprove manual saat job antri)
        if ($record->status !== 'pending' || empty($record->approval_token)) {
            Log::info("SendLeaveRequestWaNotificationJob: {$this->leaveRequestId} sudah diproses atau belum ada token. Job diabaikan.");
            return;
        }

        $student = $record->student;
        if (!$student) {
            Log::warning("SendLeaveRequestWaNotificationJob: siswa tidak ditemukan untuk ijin {$this->leaveRequestId}.");
            return;
        }

        // 3. Cari Wali Kelas (HP + nama) dari relasi KelasAjaran
        $waliKelas = $this->resolveWaliKelas($student);

        if (!$waliKelas || empty($waliKelas['hp'])) {
            Log::warning("SendLeaveRequestWaNotificationJob: HP wali kelas tidak ditemukan untuk siswa {$student->id}. Job dihentikan tanpa retry.");
            return; // Graceful exit — tidak perlu retry
        }

        $toNumber = $waliKelas['hp'];
        $namaWali = $waliKelas['nama'];

        // 4. Siapkan Teks Utama
        $pesan = $this->buildTextMessage($record, $namaWali);
        $attachments = $record->attachments; // Accessor: deduplikasi + cek eksistensi

        if (empty($attachments)) {
            // SKENARIO 1: TIDAK ADA LAMPIRAN -> Kirim Teks Saja
            if ($record->wa_text_sent_at === null) {
                $sent = $waService->sendMessage(
                    $toNumber,
                    $pesan,
                    'leave_request',
                    $record->id,
                    'wali_kelas'
                );

                if (!$sent) {
                    throw new \RuntimeException(
                        "SendLeaveRequestWaNotificationJob: gagal kirim teks untuk ijin {$this->leaveRequestId} — API WA return false."
                    );
                }

                // Tandai teks terkirim agar tidak dikirim ulang saat retry
                $record->forceFill(['wa_text_sent_at' => now()])->saveQuietly();
            }
        } else {
            // SKENARIO 2: ADA LAMPIRAN -> Teks digabung sebagai Caption pada lampiran PERTAMA
            $sentFiles = $record->wa_sent_files ?? [];
            $toSend    = array_slice($attachments, 0, 3); // Batasi maksimal 3 lampiran

            foreach ($toSend as $idx => $attachment) {
                $index     = $attachment['index'];
                $path      = $attachment['path'];
                $name      = $attachment['name'];
                $mime      = $attachment['mime'];
                $mediatype = $attachment['mediatype'];

                // Skip jika file ini sudah terkirim di attempt sebelumnya
                if (in_array($index, $sentFiles, true)) {
                    continue;
                }

                // Gunakan teks UTAMA sebagai caption HANYA untuk lampiran pertama
                $caption = ($idx === 0) ? $pesan : null;

                // Buat Base64 string dari file
                $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
                if (file_exists($fullPath)) {
                    $base64 = base64_encode(file_get_contents($fullPath));
                    $mediaData = $base64; // Raw base64 string tanpa prefix data:mime
                } else {
                    $mediaData = URL::temporarySignedRoute(
                        'ijin.media',
                        now()->addHours(6),
                        ['leaveRequestId' => $record->id, 'index' => $index]
                    );
                }

                $sent = $waService->sendMedia(
                    $toNumber,
                    $mediaData,
                    $mediatype,
                    $mime,
                    $name,
                    $caption, // <--- Memasukkan caption di sini
                    'leave_request',
                    $record->id,
                    'wali_kelas'
                );

                if (!$sent) {
                    throw new \RuntimeException(
                        "SendLeaveRequestWaNotificationJob: gagal kirim media index {$index} untuk ijin {$this->leaveRequestId} — API WA return false."
                    );
                }

                // Tandai file ini terkirim SEGERA setelah berhasil
                $sentFiles[] = $index;
                $record->forceFill(['wa_sent_files' => $sentFiles])->saveQuietly();
                
                // Jika ini adalah lampiran pertama (yang membawa teks), tandai juga bahwa teks sudah terkirim
                if ($idx === 0 && $record->wa_text_sent_at === null) {
                    $record->forceFill(['wa_text_sent_at' => now()])->saveQuietly();
                }
            }
        }
    }

    /**
     * Dipanggil Laravel saat semua retry habis.
     * Catat ke log error dan WhatsAppNotificationLog.
     */
    public function failed(Throwable $e): void
    {
        Log::error("SendLeaveRequestWaNotificationJob FINAL FAILED untuk ijin {$this->leaveRequestId}", [
            'exception' => $e->getMessage(),
        ]);

        WhatsAppNotificationLog::create([
            'module'           => 'leave_request',
            'recipient_type'   => 'wali_kelas',
            'recipient_number' => '-',
            'message'          => "[Job gagal setelah {$this->tries} percobaan: {$e->getMessage()}]",
            'status'           => 'failed',
            'response_payload' => json_encode(['error' => $e->getMessage()]),
            'related_type'     => 'leave_request',
            'related_id'       => $this->leaveRequestId,
        ]);
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Resolve wali kelas siswa dari KelasAjaran untuk mendapatkan HP + nama.
     * Return ['hp' => string, 'nama' => string] atau null jika tidak ditemukan.
     */
    private function resolveWaliKelas($student): ?array
    {
        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) return null;

        $enrollment = $student->enrollments()
            ->where('academic_year_id', $currentYear->id)
            ->first();

        if (!$enrollment) return null;

        $kelasAjaran = KelasAjaran::where('class_id', $enrollment->class_id)
            ->where('academic_year_id', $currentYear->id)
            ->with('guru')
            ->first();

        if (!$kelasAjaran || !$kelasAjaran->guru) return null;

        $guru = $kelasAjaran->guru;
        $hp   = trim($guru->no_hp ?? '');

        if (empty($hp)) return null;

        return [
            'hp'   => $hp,
            'nama' => $guru->name ?? 'Wali Kelas',
        ];
    }

    /**
     * Susun pesan teks untuk dikirim ke Wali Kelas.
     *
     * Urutan prioritas template:
     * 1. Pakai template 'leave_request' dari PresensiNotificationSetting jika aktif.
     * 2. Jika template tidak memiliki {link_konfirmasi}, sisipkan di akhir.
     * 3. Jika tidak ada template, gunakan teks default.
     */
    private function buildTextMessage(LeaveRequest $record, string $namaWaliKelas): string
    {
        $student  = $record->student;
        $kelas    = $student?->enrollmentAktif?->kelas?->name ?? '-';
        $jenis    = ucfirst($record->type);
        $mulai    = \Carbon\Carbon::parse($record->start_date)->translatedFormat('d F Y');
        $selesai  = \Carbon\Carbon::parse($record->end_date)->translatedFormat('d F Y');
        $alasan   = $record->reason ?? '-';

        // Potong alasan maksimal 500 karakter agar tidak melampaui batas pesan WA
        if (mb_strlen($alasan) > 500) {
            $alasan = mb_substr($alasan, 0, 497) . '...';
        }

        $linkKonfirmasi = url('/ijin-approval/' . $record->approval_token);

        $setting = PresensiNotificationSetting::where('status_presensi', 'leave_request')->first();

        if ($setting && $setting->is_active && !empty($setting->template_pesan)) {
            $replacements = [
                '{nama_siswa}'      => $student?->name ?? '-',
                '{kelas}'           => $kelas,
                '{jenis_ijin}'      => $jenis,
                '{tanggal_mulai}'   => $mulai,
                '{tanggal_selesai}' => $selesai,
                '{alasan}'          => $alasan,
                '{nama_wali_kelas}' => $namaWaliKelas,
                '{link_detail}'     => url('/portal-guru/ijin-kehadiran/' . $record->id),
                '{link_konfirmasi}' => $linkKonfirmasi,
            ];

            $pesan = strtr($setting->template_pesan, $replacements);

            // Jika template tidak memuat {link_konfirmasi}, sisipkan di akhir
            // agar link konfirmasi SELALU ada di pesan, tidak bergantung isi template
            if (!str_contains($setting->template_pesan, '{link_konfirmasi}')) {
                $pesan .= "\n\n🔗 *Link Konfirmasi:*\n{$linkKonfirmasi}";
            }

            return $pesan;
        }

        // Fallback: template default jika admin belum mengonfigurasi
        return "📋 *Permohonan Ijin Siswa*\n\n"
            . "Siswa: *{$student?->name}*\n"
            . "Kelas: {$kelas}\n"
            . "Jenis: {$jenis}\n"
            . "Tanggal: {$mulai}"
            . ($mulai !== $selesai ? " s/d {$selesai}" : '') . "\n"
            . "Alasan: {$alasan}\n\n"
            . "Silakan konfirmasi persetujuan Anda melalui link berikut:\n"
            . "🔗 {$linkKonfirmasi}";
    }
}
