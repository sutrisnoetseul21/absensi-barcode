<?php

namespace App\Http\Controllers;

use App\Jobs\SendLeaveRequestResultNotificationJob;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeaveRequestApprovalController extends Controller
{
    /**
     * Tentukan state halaman approval berdasarkan kondisi token & record.
     *
     * State yang dikembalikan:
     * - 'invalid'  : token tidak ditemukan di database
     * - 'expired'  : token kedaluwarsa (token_expires_at <= now())
     * - 'used'     : token sudah dipakai ATAU ijin sudah bukan pending
     * - 'confirm'  : token valid, siap ditampilkan form konfirmasi
     */
    private function resolveState(?LeaveRequest $r): string
    {
        if ($r === null) {
            return 'invalid';
        }

        if ($r->token_expires_at !== null && $r->token_expires_at->isPast()) {
            return 'expired';
        }

        if ($r->token_used_at !== null || $r->status !== 'pending') {
            return 'used';
        }

        return 'confirm';
    }

    /**
     * GET /ijin-approval/{token}
     *
     * Menampilkan halaman konfirmasi atau hasil tindakan via session flash.
     */
    public function show(string $token): \Illuminate\View\View
    {
        $record = LeaveRequest::where('approval_token', $token)
            ->with(['student.enrollmentAktif.kelas'])
            ->first();

        $state = $this->resolveState($record);
        $action = null;

        // Baca flash session hasil dari redirect POST /action
        if (session()->has('action_success')) {
            $state  = 'success';
            $action = session('action_success');
        } elseif (session()->has('action_error')) {
            $state = 'error';
        }

        return view('leave-request-approval', [
            'state'  => $state,
            'record' => $state === 'confirm' ? $record : null,
            'token'  => $token,
            'action' => $action,
        ]);
    }

    /**
     * POST /ijin-approval/{token}/action
     *
     * Eksekusi approve atau reject dengan DB::transaction + update atomik.
     * Token hanya bisa dipakai sekali (single-use guard).
     */
    public function action(string $token, Request $request, LeaveRequestService $service): \Illuminate\View\View
    {
        // Validasi field action (422 jika tidak valid — ini perilaku normal Laravel)
        $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        $action = $request->input('action');

        // Cek state sebelum masuk transaksi (early return jika tidak valid)
        $record = LeaveRequest::where('approval_token', $token)->first();
        $state  = $this->resolveState($record);

        if ($state !== 'confirm') {
            return view('leave-request-approval', [
                'state'  => $state,
                'record' => null,
                'token'  => $token,
            ]);
        }

        try {
            $finalState  = 'success';
            $finalRecord = null;

            DB::transaction(function () use ($token, $action, $service, &$finalState, &$finalRecord) {
                // Update atomik: pastikan token masih valid & belum dipakai (race condition guard)
                $newStatus = $action === 'approve' ? 'approved' : 'rejected';

                $affected = LeaveRequest::where('approval_token', $token)
                    ->whereNull('token_used_at')
                    ->where('status', 'pending')
                    ->where('token_expires_at', '>', now())
                    ->update([
                        'status'            => $newStatus,
                        'token_used_at'     => now(),
                        'approved_at'       => now(),
                        'approved_by'       => null,  // Aksi via public token link, bukan via sesi user
                        'approved_by_type'  => null,
                    ]);

                // Jika 0 baris terpengaruh: ada race condition atau token tidak valid lagi
                if ($affected === 0) {
                    // Reload record untuk menentukan state yang tepat
                    $reloaded    = LeaveRequest::where('approval_token', $token)->first();
                    $finalState  = $this->resolveState($reloaded);
                    $finalRecord = null;
                    return; // Keluar dari closure — transaksi selesai tanpa efek samping
                }

                // Reload record segar pasca update
                $record = LeaveRequest::where('approval_token', $token)->first();

                // Jalankan sync/remove absensi di dalam transaksi yang sama
                if ($action === 'approve') {
                    $service->syncAttendances($record);
                } else {
                    // Hapus absensi hanya jika sebelumnya sudah approved (aman via leave_request_id)
                    $service->removeAttendances($record);
                }

                // Catat log audit: aksi, IP, user agent, keterangan via token
                $record->recordLog(
                    $action === 'approve' ? 'approved' : 'rejected',
                    'Diproses via token link WhatsApp',
                    [
                        'via'        => 'whatsapp_token',
                        'ip'         => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]
                );

                $finalState  = 'success';
                $finalRecord = $record;

                // Dispatch notifikasi balik ke siswa/ortu SETELAH commit berhasil
                DB::afterCommit(function () use ($record) {
                    SendLeaveRequestResultNotificationJob::dispatch($record->id);
                });
            });

            // Redirect ke halaman awal dengan membawa status berhasil (Post-Redirect-Get pattern)
            return redirect()->route('ijin.approval.show', ['token' => $token])
                             ->with('action_success', $action);

        } catch (\Throwable $e) {
            Log::error("LeaveRequestApproval: gagal memproses token [{$token}]", [
                'action'    => $action,
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return redirect()->route('ijin.approval.show', ['token' => $token])
                             ->with('action_error', true);
        }
    }

    /**
     * GET /ijin-media/{leaveRequestId}/{index}  [middleware: signed]
     *
     * Melayani file lampiran ijin. Path diambil dari accessor $record->attachments,
     * bukan dari URL (mencegah path traversal). Hanya dapat diakses via Signed URL valid.
     */
    public function media(string $leaveRequestId, int $index): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Temukan record berdasarkan UUID yang sudah divalidasi regex di route
        $record = LeaveRequest::findOrFail($leaveRequestId);

        // Dapatkan daftar attachment via accessor (sudah deduplikasi & cek eksistensi)
        $attachments = $record->attachments;

        // Cari item berdasarkan index
        $attachment = collect($attachments)->firstWhere('index', $index);

        if (!$attachment) {
            abort(404, 'Lampiran tidak ditemukan.');
        }

        $path = $attachment['path'];
        $mime = $attachment['mime'];
        $name = $attachment['name'];

        // Pastikan file masih ada (accessor sudah cek, tapi double-check sebelum serve)
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File lampiran tidak ditemukan di storage.');
        }

        $fullPath = Storage::disk('public')->path($path);

        return response()->file($fullPath, [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'inline; filename="' . $name . '"',
            'X-Content-Type-Options'    => 'nosniff',
            'Cache-Control'             => 'private, no-store',
        ]);
    }
}
