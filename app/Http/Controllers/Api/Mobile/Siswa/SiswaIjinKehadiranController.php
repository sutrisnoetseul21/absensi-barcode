<?php

namespace App\Http\Controllers\Api\Mobile\Siswa;

use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\TahunAjaran;
use App\Models\KelasAjaran;
use App\Models\PresensiNotificationSetting;
use App\Jobs\SendLeaveRequestWaNotificationJob;
use App\Jobs\SendLeaveRequestFcmNotificationJob;
use App\Services\KalenderSekolahService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SiswaIjinKehadiranController extends BaseSiswaApiController
{
    /**
     * GET /api/v1/siswa/ijin-kehadiran
     * Mengambil riwayat pengajuan ijin siswa dengan filter status & paginasi.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $query = LeaveRequest::where('student_id', $student->id)
            ->with(['approvedBy', 'logs.user'])
            ->orderBy('created_at', 'desc');

        // Filter Status
        $status = strtolower($request->input('status', 'all'));
        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        // Filter Tipe (ijin / sakit)
        $type = strtolower($request->input('type', 'all'));
        if (in_array($type, ['ijin', 'sakit'])) {
            $query->where('type', $type);
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (LeaveRequest $item) {
            return $this->formatLeaveRequest($item);
        });

        // Hitung Counter Status Ringkasan
        $counts = [
            'total' => LeaveRequest::where('student_id', $student->id)->count(),
            'pending' => LeaveRequest::where('student_id', $student->id)->where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('student_id', $student->id)->where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('student_id', $student->id)->where('status', 'rejected')->count(),
            'sakit' => LeaveRequest::where('student_id', $student->id)->where('type', 'sakit')->count(),
            'ijin' => LeaveRequest::where('student_id', $student->id)->where('type', 'ijin')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pengajuan ijin siswa berhasil dimuat.',
            'data' => [
                'counts' => $counts,
                'items' => $items,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'has_more' => $paginated->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/ijin-kehadiran/check-holidays
     * Mengecek apakah rentang tanggal yang dipilih siswa menyentuh hari libur sekolah.
     */
    public function checkHolidays(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $startDateStr = $request->input('start_date');
        $durationDays = (int) ($request->input('duration_days') ?: 1);

        if (!$startDateStr) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter start_date wajib diisi.',
            ], 422);
        }

        $start = Carbon::parse($startDateStr);
        $end = $start->copy()->addDays(max(1, $durationDays) - 1);
        $classId = $student->enrollmentAktif?->class_id;

        $kalenderService = app(KalenderSekolahService::class);
        $holidayMessages = [];
        $holidayDates = [];
        $schoolDaysCount = 0;

        $cur = $start->copy();
        while ($cur->lessThanOrEqualTo($end)) {
            $isSchoolDay = $kalenderService->isHariSekolah($cur, $classId);
            if (!$isSchoolDay) {
                $dateFormatted = $cur->translatedFormat('l, d F Y');
                $holidayMessages[] = "Catatan: {$dateFormatted} adalah hari libur sekolah (otomatis tercatat 'Libur').";
                $holidayDates[] = $cur->toDateString();
            } else {
                $schoolDaysCount++;
            }
            $cur->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'duration_days' => $durationDays,
                'school_days_count' => $schoolDaysCount,
                'holiday_count' => count($holidayDates),
                'holiday_messages' => $holidayMessages,
                'holiday_dates' => $holidayDates,
            ],
        ]);
    }

    /**
     * POST /api/v1/siswa/ijin-kehadiran
     * Menyimpan permohonan ijin/sakit baru dilengkapi lampiran foto/dokumen.
     */
    public function store(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $user = $this->resolveUser($request);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:ijin,sakit',
            'duration_days' => 'required|integer|min:1|max:30',
            'start_date' => 'required|date',
            'reason' => 'required|string|min:3|max:1000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ], [
            'type.required' => 'Pilihan tipe permohonan (Ijin / Sakit) wajib dipilih.',
            'type.in' => 'Tipe permohonan hanya boleh berupa ijin atau sakit.',
            'duration_days.required' => 'Durasi hari wajib diisi.',
            'duration_days.min' => 'Durasi minimal 1 hari.',
            'duration_days.max' => 'Durasi maksimal 30 hari.',
            'start_date.required' => 'Tanggal mulai izin wajib diisi.',
            'reason.required' => 'Alasan izin wajib diisi.',
            'reason.min' => 'Alasan izin minimal 3 karakter.',
            'attachments.max' => 'Lampiran maksimal 5 berkas.',
            'attachments.*.mimes' => 'Format lampiran harus berupa JPG, PNG, WEBP, atau PDF.',
            'attachments.*.max' => 'Ukuran setiap lampiran maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // 1. Validasi Tahun Ajaran Aktif
        $activeYear = $this->getActiveAcademicYear();
        if (!$activeYear) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun ajaran aktif tidak ditemukan pada sistem sekolah.',
            ], 422);
        }

        $startDateStr = $request->input('start_date');
        $durationDays = (int) $request->input('duration_days');
        $startDateObj = Carbon::parse($startDateStr)->startOfDay();
        $endDateObj = $startDateObj->copy()->addDays($durationDays - 1)->endOfDay();
        $endDateStr = $endDateObj->toDateString();

        // 2. Validasi Rentang Waktu (Maksimal 7 hari sebelum / sesudah hari ini)
        $minDate = now()->subDays(7)->startOfDay();
        $maxDate = now()->addDays(7)->endOfDay();

        if ($startDateObj->lessThan($minDate) || $startDateObj->greaterThan($maxDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: Tanggal pengajuan hanya diizinkan maksimal 7 hari sebelum atau sesudah tanggal hari ini.',
            ], 422);
        }

        // 3. Validasi Tumpang Tindih (Overlap Pengajuan)
        $overlapRecord = LeaveRequest::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDateStr, $endDateStr) {
                $query->whereBetween('start_date', [$startDateStr, $endDateStr])
                    ->orWhereBetween('end_date', [$startDateStr, $endDateStr])
                    ->orWhere(function ($q) use ($startDateStr, $endDateStr) {
                        $q->where('start_date', '<=', $startDateStr)
                            ->where('end_date', '>=', $endDateStr);
                    });
            })
            ->first();

        if ($overlapRecord) {
            $stLabel = $overlapRecord->status === 'approved' ? 'Disetujui' : 'Menunggu Verifikasi (Pending)';
            return response()->json([
                'success' => false,
                'message' => "Gagal: Anda sudah memiliki pengajuan yang berstatus [{$stLabel}] pada rentang tanggal ini.",
            ], 422);
        }

        // 4. Validasi Hari Lampau yang Sudah Hadir / Terlambat
        $existingAttendance = Presensi::where('student_id', $student->id)
            ->whereBetween('date', [$startDateStr, $endDateStr])
            ->whereIn('status', ['hadir', 'telat'])
            ->first();

        if ($existingAttendance) {
            $formattedDate = Carbon::parse($existingAttendance->date)->translatedFormat('d F Y');
            return response()->json([
                'success' => false,
                'message' => "Gagal: Anda sudah memiliki catatan presensi (Hadir/Telat) pada tanggal {$formattedDate}. Silakan periksa kembali tanggal pengajuan Anda.",
            ], 422);
        }

        // 5. Simpan Berkas Lampiran
        $paths = [];
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $paths[] = $file->store('leave-requests', 'public');
                }
            }
        }
        $paths = array_values(array_unique(array_filter($paths)));

        // Jika Sakit dan belum ada lampiran, berikan catatan edukatif atau validasi
        $type = $request->input('type');

        $data = [
            'student_id' => $student->id,
            'academic_year_id' => $activeYear->id,
            'type' => $type,
            'duration_days' => $durationDays,
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'reason' => $request->input('reason'),
            'file_path' => count($paths) > 0 ? $paths[0] : null,
            'file_paths' => count($paths) > 1 ? array_slice($paths, 1) : null,
            'status' => 'pending',
        ];

        $record = LeaveRequest::create($data);

        // Audit Trail Log
        try {
            $record->recordLog('created', 'Diajukan oleh siswa via Mobile App', [
                'user_id' => $user?->id,
                'files_count' => count($paths),
            ]);
        } catch (\Throwable $e) {}

        // Integrasi Notifikasi WA & FCM
        try {
            $record->generateApprovalToken();
            DB::afterCommit(function () use ($record) {
                if (class_exists(SendLeaveRequestWaNotificationJob::class)) {
                    SendLeaveRequestWaNotificationJob::dispatch($record->id);
                }
                if (class_exists(SendLeaveRequestFcmNotificationJob::class)) {
                    SendLeaveRequestFcmNotificationJob::dispatch($record->id);
                }
            });
        } catch (\Throwable $e) {
            report($e);
        }

        // Broadcast Notifikasi Setting
        $this->broadcastNotification($record, $student);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan ijin berhasil dikirim dan menunggu verifikasi Wali Kelas.',
            'data' => $this->formatLeaveRequest($record),
        ], 201);
    }

    /**
     * GET /api/v1/siswa/ijin-kehadiran/{id}
     * Menampilkan detail 1 pengajuan izin siswa, termasuk berkas dan catatan verifikasi.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $record = LeaveRequest::where('student_id', $student->id)
            ->with(['approvedBy', 'logs.user'])
            ->find($id);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan ijin tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pengajuan ijin berhasil dimuat.',
            'data' => $this->formatLeaveRequest($record, true),
        ]);
    }

    /**
     * DELETE /api/v1/siswa/ijin-kehadiran/{id}
     * Membatalkan permohonan ijin yang masih berstatus pending.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $record = LeaveRequest::where('student_id', $student->id)
            ->where('id', $id)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ijin tidak ditemukan.',
            ], 404);
        }

        if ($record->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses (' . ucfirst($record->status) . ') tidak dapat dibatalkan.',
            ], 422);
        }

        $user = $this->resolveUser($request);
        try {
            $record->recordLog('deleted', 'Dibatalkan oleh siswa via Mobile App', [
                'user_id' => $user?->id,
            ]);
        } catch (\Throwable $e) {}

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permohonan ijin berhasil dibatalkan.',
        ]);
    }

    /**
     * Format Model LeaveRequest ke Representasi DTO JSON Standar Mobile.
     */
    protected function formatLeaveRequest(LeaveRequest $item, bool $includeDetails = false): array
    {
        $statusLower = strtolower($item->status);
        $statusLabel = match ($statusLower) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Menunggu Verifikasi',
        };

        $typeLower = strtolower($item->type);
        $typeLabel = match ($typeLower) {
            'sakit' => 'Sakit',
            default => 'Izin',
        };

        $startDateCarbon = Carbon::parse($item->start_date);
        $endDateCarbon = Carbon::parse($item->end_date);

        // Ambil Seluruh Berkas Lampiran
        $files = [];
        $rawPaths = [];
        if (!empty($item->file_path)) {
            $rawPaths[] = $item->file_path;
        }
        if (is_array($item->file_paths)) {
            foreach ($item->file_paths as $p) {
                if (!empty($p)) {
                    $rawPaths[] = is_array($p) ? ($p['path'] ?? '') : $p;
                }
            }
        }

        $uniquePaths = array_values(array_filter(array_unique($rawPaths)));
        foreach ($uniquePaths as $idx => $p) {
            $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            $files[] = [
                'index' => $idx,
                'file_name' => basename($p),
                'url' => asset('storage/' . $p),
                'file_path' => $p,
                'extension' => $ext,
                'is_image' => $isImg,
                'mediatype' => $isImg ? 'image' : 'document',
            ];
        }

        // Catatan Penolakan / Alasan Verifikasi
        $verificationNote = null;
        if ($statusLower === 'rejected') {
            $rejectLog = $item->logs->first(fn($l) => in_array($l->action, ['rejected', 'ditolak']));
            $verificationNote = $rejectLog?->reason;
        } elseif ($statusLower === 'approved') {
            $approveLog = $item->logs->first(fn($l) => in_array($l->action, ['approved', 'disetujui']));
            $verificationNote = $approveLog?->reason;
        }

        $formatted = [
            'id' => $item->id,
            'type' => $typeLower,
            'type_label' => $typeLabel,
            'duration_days' => (int) $item->duration_days,
            'start_date' => $startDateCarbon->toDateString(),
            'end_date' => $endDateCarbon->toDateString(),
            'formatted_start_date' => $startDateCarbon->translatedFormat('d F Y'),
            'formatted_end_date' => $endDateCarbon->translatedFormat('d F Y'),
            'formatted_range' => $startDateCarbon->translatedFormat('d M Y') . ($item->duration_days > 1 ? ' - ' . $endDateCarbon->translatedFormat('d M Y') : ''),
            'reason' => $item->reason,
            'status' => $statusLower,
            'status_label' => $statusLabel,
            'has_attachments' => count($files) > 0,
            'attachments_count' => count($files),
            'attachments' => $files,
            'primary_attachment_url' => count($files) > 0 ? $files[0]['url'] : null,
            'is_cancellable' => ($statusLower === 'pending'),
            'verification_note' => $verificationNote,
            'approved_at' => $item->approved_at ? Carbon::parse($item->approved_at)->translatedFormat('d M Y H:i') : null,
            'approver_name' => $item->approvedBy?->name,
            'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
            'formatted_created_at' => $item->created_at ? $item->created_at->translatedFormat('d M Y, H:i') : null,
        ];

        if ($includeDetails) {
            $formatted['logs'] = $item->logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'reason' => $log->reason,
                    'user_name' => $log->user?->name ?? 'Sistem',
                    'created_at' => $log->created_at ? $log->created_at->translatedFormat('d M Y H:i') : null,
                ];
            });
        }

        return $formatted;
    }

    /**
     * Broadcast notifikasi WA ke Wali Kelas sesuai pengaturan.
     */
    protected function broadcastNotification(LeaveRequest $record, $student): void
    {
        try {
            $setting = PresensiNotificationSetting::where('status_presensi', 'leave_request')->first();
            if (!$setting || !$setting->is_active || empty($setting->recipients) || empty($setting->template_pesan)) {
                return;
            }

            $enrollment = $student->enrollmentAktif;
            if (!$enrollment) return;

            $kelas = $enrollment->kelas->name ?? '-';
            $kelasAjaran = KelasAjaran::where('class_id', $enrollment->class_id)
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->with('guru')
                ->first();

            $namaWaliKelas = $kelasAjaran?->guru?->name ?? 'Wali Kelas';

            $replacements = [
                '{nama_siswa}' => $student->name,
                '{kelas}' => $kelas,
                '{jenis_ijin}' => ucfirst($record->type),
                '{tanggal_mulai}' => Carbon::parse($record->start_date)->translatedFormat('d F Y'),
                '{tanggal_selesai}' => Carbon::parse($record->end_date)->translatedFormat('d F Y'),
                '{alasan}' => $record->reason,
                '{nama_wali_kelas}' => $namaWaliKelas,
                '{link_detail}' => url('/portal-guru/ijin-kehadiran/' . $record->id),
            ];

            $pesan = strtr($setting->template_pesan, $replacements);

            if (class_exists(\App\Services\WhatsAppGatewayService::class) && !empty($kelasAjaran?->guru?->no_hp)) {
                app(\App\Services\WhatsAppGatewayService::class)->sendMessage($kelasAjaran->guru->no_hp, $pesan);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

