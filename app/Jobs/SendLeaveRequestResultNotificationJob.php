<?php

namespace App\Jobs;

use App\Models\LeaveRequest;
use App\Models\PresensiNotificationSetting;
use App\Models\WhatsAppNotificationLog;
use App\Services\WhatsAppGatewayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendLeaveRequestResultNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $leaveRequestId
    ) {}

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(WhatsAppGatewayService $waService): void
    {
        $record = LeaveRequest::with(['student'])->find($this->leaveRequestId);

        if (!$record) {
            Log::warning("SendLeaveRequestResultNotificationJob: record {$this->leaveRequestId} tidak ditemukan.");
            return;
        }

        // Pastikan status sudah diproses (approved/rejected)
        if (!in_array($record->status, ['approved', 'rejected'], true)) {
            Log::info("SendLeaveRequestResultNotificationJob: ijin {$this->leaveRequestId} belum diproses.");
            return;
        }

        $student = $record->student;
        if (!$student || empty($student->no_hp)) {
            Log::warning("SendLeaveRequestResultNotificationJob: Siswa / HP tidak ditemukan untuk ijin {$this->leaveRequestId}.");
            return;
        }

        $toNumber = $student->no_hp;

        // Cek idempotent: apakah sudah dikirim notifikasi result sebelumnya?
        // Kita bisa tambahkan cek sederhana via log atau biarkan job ini single-run yang aman
        // Karena job di-dispatch di afterCommit, asalkan handler aman, tidak akan duplikat parah.

        $pesan = $this->buildMessage($record);

        $sent = $waService->sendMessage(
            $toNumber,
            $pesan,
            'leave_request_result',
            $record->id,
            'siswa'
        );

        if (!$sent) {
            throw new \RuntimeException(
                "SendLeaveRequestResultNotificationJob: gagal kirim balasan untuk ijin {$this->leaveRequestId} — API WA return false."
            );
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error("SendLeaveRequestResultNotificationJob FINAL FAILED untuk ijin {$this->leaveRequestId}", [
            'exception' => $e->getMessage(),
        ]);

        WhatsAppNotificationLog::create([
            'module'           => 'leave_request_result',
            'recipient_type'   => 'siswa',
            'recipient_number' => '-',
            'message'          => "[Job gagal: {$e->getMessage()}]",
            'status'           => 'failed',
            'response_payload' => json_encode(['error' => $e->getMessage()]),
            'related_type'     => 'leave_request',
            'related_id'       => $this->leaveRequestId,
        ]);
    }

    private function buildMessage(LeaveRequest $record): string
    {
        $student = $record->student;
        $jenis   = ucfirst($record->type);
        $mulai   = \Carbon\Carbon::parse($record->start_date)->translatedFormat('d F Y');
        $selesai = \Carbon\Carbon::parse($record->end_date)->translatedFormat('d F Y');
        $status  = $record->status === 'approved' ? 'DISETUJUI ✅' : 'DITOLAK ❌';

        // Coba cari template dari pengaturan admin ('leave_approval' adalah key di admin)
        $setting = PresensiNotificationSetting::where('status_presensi', 'leave_approval')->first();

        if ($setting && $setting->is_active && !empty($setting->template_pesan)) {
            $replacements = [
                '{nama_siswa}'         => $student->name,
                '{jenis_ijin}'         => $jenis,
                '{tanggal_mulai}'      => $mulai,
                '{tanggal_selesai}'    => $selesai,
                '{status_persetujuan}' => $status,
                '{nama_guru}'          => $record->approved_by_type === 'user' ? ($record->approverUser->name ?? 'Wali Kelas') : ($record->approverGuru->nama_guru ?? 'Wali Kelas'),
                '{alasan_penolakan}'   => $record->status === 'rejected' ? 'Alasan: Ditolak oleh guru.' : '',
            ];

            return strtr($setting->template_pesan, $replacements);
        }

        // Fallback default
        return "Halo *{$student->name}*,\n\n"
             . "Permohonan *{$jenis}* Anda untuk tanggal {$mulai}"
             . ($mulai !== $selesai ? " s/d {$selesai}" : "") . "\n"
             . "telah *{$status}* oleh Wali Kelas.\n\n"
             . "Silakan cek Portal Siswa untuk informasi lebih lanjut.\n"
             . url('/portal-siswa/ijin-kehadiran');
    }
}
