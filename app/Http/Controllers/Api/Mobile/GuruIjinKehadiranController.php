<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\LeaveRequest;
use App\Models\PresensiNotificationSetting;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\LeaveRequestService;
use App\Services\RecipientResolverService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GuruIjinKehadiranController extends Controller
{
    /**
     * Resolusi user yang terautentikasi (Sanctum Token / Fallback Base64 Token / Identifier Header / Query).
     */
    protected function resolveUser(Request $request): ?User
    {
        try {
            $sanctumUser = auth('sanctum')->user();
            if ($sanctumUser instanceof User) {
                return $sanctumUser;
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel personal_access_tokens belum dimigrasi
        }

        $bearer = $request->bearerToken() ?: (string) $request->input('token', '');
        if (!empty($bearer) && !str_contains($bearer, '|')) {
            $decoded = base64_decode($bearer, true);
            if ($decoded && str_contains($decoded, '|')) {
                $parts = explode('|', $decoded);
                $userId = $parts[0] ?? null;
                if ($userId) {
                    $user = User::find($userId);
                    if ($user) {
                        return $user;
                    }
                }
            }
        }

        $identifier = trim((string) ($request->header('X-User-Identifier') ?: $request->input('identifier', '')));
        if ($identifier !== '') {
            $guru = Guru::where('nip', $identifier)
                ->orWhere('name', $identifier)
                ->first();
            if ($guru) {
                if ($guru->user_id) {
                    $user = User::find($guru->user_id);
                    if ($user) {
                        return $user;
                    }
                }
                $userByTeacher = User::where('teacher_id', $guru->id)->first();
                if ($userByTeacher) {
                    return $userByTeacher;
                }

                // Fallback objek user jika profil guru belum ter-link ke akun user
                $virtualUser = new User();
                $virtualUser->id = $guru->id;
                $virtualUser->name = $guru->name;
                $virtualUser->email = ($guru->nip ?: 'guru') . '@sekolah.id';
                $virtualUser->teacher_id = $guru->id;
                $virtualUser->setRelation('teacher', $guru);
                $virtualUser->setRelation('guru', $guru);
                return $virtualUser;
            }

            $user = User::where('email', $identifier)
                ->orWhere('name', $identifier)
                ->orWhere('email', 'like', $identifier . '@%')
                ->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Ambil satu-satunya Tahun Ajaran Aktif (terkunci).
     */
    protected function getActiveAcademicYear(): ?TahunAjaran
    {
        return TahunAjaran::where('status', 'aktif')
            ->orderBy('start_year', 'desc')
            ->first()
            ?: TahunAjaran::orderBy('start_year', 'desc')->first();
    }

    /**
     * Tentukan daftar kelas yang berhak diakses oleh Guru pada menu Ijin Kehadiran:
     * 1. Wali Kelas Utama (tabel class_academic_year / relasi kelasAjarans)
     * 2. Kelas Binaan BK (tabel teacher_class_accesses / relasi kelasPantau)
     * 3. Bypass Semua Kelas (Super Admin / Admin Presensi / permission portal_guru:akses_semua_kelas)
     */
    protected function getAccessibleClasses(?User $user, ?string $activeYearId): array
    {
        if (!$user || !$activeYearId) {
            return [
                'classes' => collect(),
                'wali_class_ids' => [],
                'pantau_class_ids' => [],
                'all_class_ids' => [],
                'is_wali_kelas' => false,
                'is_bk' => false,
                'is_bypass' => false,
                'role_summary' => 'Guru',
            ];
        }

        $isAdminMode = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || $user->hasAnyRole(['super_admin', 'admin_presensi', 'admin_portal_presensi']);
        $hasBypass = false;
        try {
            $hasBypass = method_exists($user, 'can') && $user->can('portal_guru:akses_semua_kelas');
        } catch (\Throwable $e) {}

        $isGuruBk = false;
        try {
            $isGuruBk = method_exists($user, 'isGuruBk') && $user->isGuruBk();
        } catch (\Throwable $e) {}

        $actor = $user->teacher
            ?: ($user->teacher_id ? Guru::find($user->teacher_id) : null)
            ?: Guru::where('user_id', $user->id)->first();

        if (!$actor) {
            $reqIdent = trim((string) (request()->header('X-User-Identifier') ?: request()->input('identifier', '')));
            if ($reqIdent !== '') {
                $actor = Guru::where('nip', $reqIdent)->orWhere('name', $reqIdent)->first();
            }
        }

        if ($isAdminMode || $hasBypass) {
            $rawClasses = Kelas::whereHas('kelasAjarans', function ($query) use ($activeYearId) {
                $query->where('academic_year_id', $activeYearId);
            })->orderBy('name')->get();

            $allIds = $rawClasses->pluck('id')->map(fn($id) => (string) $id)->toArray();
            $formatted = $rawClasses->map(function ($c) {
                return [
                    'id' => (string) $c->id,
                    'name' => $c->name,
                    'access_label' => 'Semua Kelas',
                    'is_wali' => true,
                    'is_bk' => false,
                ];
            });

            return [
                'classes' => $formatted,
                'wali_class_ids' => $allIds,
                'pantau_class_ids' => [],
                'all_class_ids' => $allIds,
                'is_wali_kelas' => true,
                'is_bk' => false,
                'is_bypass' => true,
                'role_summary' => 'Super Admin',
            ];
        }

        if (!$actor) {
            return [
                'classes' => collect(),
                'wali_class_ids' => [],
                'pantau_class_ids' => [],
                'all_class_ids' => [],
                'is_wali_kelas' => false,
                'is_bk' => false,
                'is_bypass' => false,
                'role_summary' => 'Guru',
            ];
        }

        // 1. Kelas Wali Kelas Utama
        $waliClassIds = $actor->kelasAjarans()
            ->when($activeYearId, fn($q) => $q->where('academic_year_id', $activeYearId))
            ->pluck('class_id')
            ->filter()
            ->map(fn($id) => (string) $id)
            ->toArray();
        if (empty($waliClassIds) && $activeYearId) {
            $waliClassIds = $actor->kelasAjarans()->pluck('class_id')->filter()->map(fn($id) => (string) $id)->toArray();
        }

        // 2. Kelas Pantau (Binaan BK)
        $pantauClassIds = $actor->kelasPantau()
            ->when($activeYearId, fn($q) => $q->where('academic_year_id', $activeYearId))
            ->pluck('class_id')
            ->filter()
            ->map(fn($id) => (string) $id)
            ->toArray();
        if (empty($pantauClassIds) && $activeYearId) {
            $pantauClassIds = $actor->kelasPantau()->pluck('class_id')->filter()->map(fn($id) => (string) $id)->toArray();
        }

        $allClassIds = array_values(array_unique(array_merge($waliClassIds, $pantauClassIds)));
        $allClasses = !empty($allClassIds)
            ? Kelas::whereIn('id', $allClassIds)->orderBy('name')->get()
            : collect();

        $formatted = $allClasses->map(function ($c) use ($waliClassIds, $pantauClassIds) {
            $cid = (string) $c->id;
            $isWali = in_array($cid, $waliClassIds, true);
            $isBk = in_array($cid, $pantauClassIds, true);

            $label = $isWali ? 'Wali Kelas' : ($isBk ? 'Binaan BK' : 'Kelas Binaan');
            return [
                'id' => $cid,
                'name' => $c->name,
                'access_label' => $label,
                'is_wali' => $isWali,
                'is_bk' => $isBk,
            ];
        });

        $roleSummary = 'Guru';
        if (!empty($waliClassIds) && !empty($pantauClassIds)) {
            $roleSummary = 'Wali Kelas & Guru BK';
        } elseif (!empty($waliClassIds)) {
            $roleSummary = 'Wali Kelas';
        } elseif ($isGuruBk || !empty($pantauClassIds)) {
            $roleSummary = 'Guru BK';
        }

        return [
            'classes' => $formatted,
            'wali_class_ids' => $waliClassIds,
            'pantau_class_ids' => $pantauClassIds,
            'all_class_ids' => $allClassIds,
            'is_wali_kelas' => !empty($waliClassIds),
            'is_bk' => $isGuruBk || !empty($pantauClassIds),
            'is_bypass' => false,
            'role_summary' => $roleSummary,
        ];
    }

    /**
     * Cek apakah user berhak memberikan keputusan (Approve / Reject / Reset) pada LeaveRequest tertentu.
     */
    protected function canApproveOrReject(?User $user, LeaveRequest $record, array $accessInfo): bool
    {
        if (!$user) return false;

        // Admin mode bypass
        if (!empty($accessInfo['is_bypass'])) {
            return true;
        }

        if ($user->hasRole(['super_admin', 'admin_presensi'])) {
            return true;
        }

        $studentClassId = $record->student?->enrollmentAktif?->class_id;
        if (!$studentClassId) {
            // Fallback cari enrollment pada academic_year terkait
            $studentClassId = EnrollmentSiswa::where('student_id', $record->student_id)
                ->where('academic_year_id', $record->academic_year_id)
                ->where('status', 'aktif')
                ->value('class_id');
        }

        if (!$studentClassId) return false;

        // Hanya Wali Kelas dari rombel tersebut yang berhak memutuskan
        return in_array((string) $studentClassId, $accessInfo['wali_class_ids'], true);
    }

    /**
     * Ekstraksi seluruh berkas lampiran dari LeaveRequest (file_path + file_paths + attachments accessor)
     * tanpa bergantung mutlak pada disk exists check agar tidak hilang di UI saat export database tanpa folder foto.
     */
    protected function extractAttachments(LeaveRequest $record): array
    {
        $rawPaths = [];

        if (!empty($record->file_path)) {
            $rawPaths[] = $record->file_path;
        }

        $extraPaths = $record->file_paths;
        if (is_string($extraPaths)) {
            $decoded = json_decode($extraPaths, true);
            if (is_array($decoded)) {
                $extraPaths = $decoded;
            }
        }
        if (is_array($extraPaths)) {
            foreach ($extraPaths as $p) {
                if (!empty($p) && is_string($p)) {
                    $rawPaths[] = $p;
                }
            }
        }

        try {
            $modelAttachments = $record->attachments;
            if (is_array($modelAttachments)) {
                foreach ($modelAttachments as $att) {
                    if (is_array($att) && !empty($att['path'])) {
                        $rawPaths[] = $att['path'];
                    }
                }
            }
        } catch (\Throwable $e) {}

        $rawPaths = array_values(array_unique(array_filter($rawPaths)));
        $attachmentsData = [];
        $index = 0;

        foreach ($rawPaths as $path) {
            $cleanPath = ltrim(str_replace(['public/', '/storage/', 'storage/'], '', $path), '/');
            $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));

            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'])
                || str_contains($path, 'image') || str_contains($cleanPath, '.jpg') || str_contains($cleanPath, '.png');
            $isPdf = $ext === 'pdf';

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $url = $path;
            } else {
                $url = url('/api/v1/guru/ijin-kehadiran/file-stream?path=' . urlencode($cleanPath));
            }

            $attachmentsData[] = [
                'index' => $index,
                'name' => basename($cleanPath) ?: ('Lampiran ' . ($index + 1)),
                'url' => $url,
                'path' => $cleanPath,
                'mediatype' => $isImage ? 'image' : ($isPdf ? 'document' : 'file'),
                'is_image' => $isImage,
                'is_pdf' => $isPdf,
            ];
            $index++;
        }

        return $attachmentsData;
    }

    /**
     * GET /api/v1/guru/ijin-kehadiran
     * Daftar Permohonan Izin Siswa dengan filter status, kelas, bulan, dan pencarian.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terautentikasi atau token tidak valid.',
            ], 401);
        }

        $activeYear = $this->getActiveAcademicYear();
        $accessInfo = $this->getAccessibleClasses($user, $activeYear?->id);

        if (empty($accessInfo['all_class_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Menu permohonan ijin hanya untuk Wali Kelas dan Guru BK yang memiliki kelas binaan aktif.',
            ], 403);
        }

        $filterStatus = $request->input('status', 'all'); // 'all', 'pending', 'approved', 'rejected'
        $filterClass = $request->input('class_id');
        $filterMonth = $request->input('month', date('Y-m'));
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 15);

        // 1. Tentukan class_id yang difilter
        $targetClassIds = $filterClass && in_array($filterClass, $accessInfo['all_class_ids'])
            ? [$filterClass]
            : $accessInfo['all_class_ids'];

        // 2. Dapatkan studentIds sesuai kelas target pada tahun ajaran aktif
        $allStudentIds = EnrollmentSiswa::whereIn('class_id', $accessInfo['all_class_ids'])
            ->where('status', 'aktif')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('student_id')
            ->toArray();

        $targetStudentIds = EnrollmentSiswa::whereIn('class_id', $targetClassIds)
            ->where('status', 'aktif')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('student_id')
            ->toArray();

        // 3. Ringkasan Statistik Keseluruhan (Metrik Tab)
        $pendingCount = LeaveRequest::whereIn('student_id', $allStudentIds)
            ->where('status', 'pending')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();

        $approvedCount = LeaveRequest::whereIn('student_id', $allStudentIds)
            ->where('status', 'approved')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();

        $rejectedCount = LeaveRequest::whereIn('student_id', $allStudentIds)
            ->where('status', 'rejected')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();

        $totalCount = $pendingCount + $approvedCount + $rejectedCount;

        // 4. Query Data Permohonan
        $query = LeaveRequest::whereIn('student_id', $targetStudentIds)
            ->with([
                'student.enrollmentAktif.kelas',
                'student.user',
                'approvedBy',
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($filterStatus && $filterStatus !== 'all', function ($q) use ($filterStatus) {
                $q->where('status', $filterStatus);
            })
            ->when($filterMonth && str_contains($filterMonth, '-'), function ($q) use ($filterMonth) {
                $parts = explode('-', $filterMonth);
                if (count($parts) === 2) {
                    $q->whereYear('start_date', $parts[0])
                        ->whereMonth('start_date', $parts[1]);
                }
            })
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc');

        $paginated = $query->paginate($perPage);

        // 5. Transformasi Data
        $items = collect($paginated->items())->map(function (LeaveRequest $item) use ($user, $accessInfo) {
            $student = $item->student;
            $enrollment = $student?->enrollmentAktif;
            $kelasName = $enrollment?->kelas?->name ?? '-';
            $classId = $enrollment?->class_id;

            // Thumbnail lampiran pertama (jika berupa gambar)
            $attachmentsData = $this->extractAttachments($item);
            $thumbnailUrl = null;

            foreach ($attachmentsData as $att) {
                if ($att['is_image']) {
                    $thumbnailUrl = $att['url'];
                    break;
                }
            }

            $canApprove = $this->canApproveOrReject($user, $item, $accessInfo);

            $statusLabel = match ($item->status) {
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                default => 'Menunggu',
            };

            $typeLabel = match ($item->type) {
                'sakit' => 'Sakit',
                default => 'Izin',
            };

            return [
                'id' => (string) $item->id,
                'student_id' => (string) $item->student_id,
                'student_name' => $student?->name ?? 'Siswa',
                'student_nisn' => $student?->nisn ?? '-',
                'student_gender' => $student?->gender ?? 'L',
                'student_avatar_url' => $student?->avatar_url ?? null,
                'class_id' => $classId,
                'class_name' => $kelasName,
                'type' => $item->type,
                'type_label' => $typeLabel,
                'start_date' => $item->start_date ? Carbon::parse($item->start_date)->format('Y-m-d') : null,
                'start_date_formatted' => $item->start_date ? Carbon::parse($item->start_date)->translatedFormat('d M Y') : '-',
                'end_date' => $item->end_date ? Carbon::parse($item->end_date)->format('Y-m-d') : null,
                'end_date_formatted' => $item->end_date ? Carbon::parse($item->end_date)->translatedFormat('d M Y') : '-',
                'duration_days' => (int) $item->duration_days,
                'duration_label' => $item->duration_days . ' Hari',
                'reason' => (string) $item->reason,
                'status' => $item->status,
                'status_label' => $statusLabel,
                'approved_by_name' => $item->approvedBy?->name ?? null,
                'approved_at' => $item->approved_at ? Carbon::parse($item->approved_at)->format('Y-m-d H:i') : null,
                'approved_at_formatted' => $item->approved_at ? Carbon::parse($item->approved_at)->translatedFormat('d M Y H:i') : null,
                'created_at_formatted' => $item->created_at ? Carbon::parse($item->created_at)->translatedFormat('d M Y H:i') : null,
                'attachments_count' => count($attachmentsData),
                'thumbnail_url' => $thumbnailUrl,
                'attachments' => $attachmentsData,
                'can_approve' => $canApprove,
            ];
        });

        // 6. Daftar Bulan Tersedia (6 bulan terakhir)
        $availableMonths = [];
        for ($i = 0; $i < 6; $i++) {
            $d = now()->subMonths($i);
            $availableMonths[] = [
                'key' => $d->format('Y-m'),
                'label' => $d->translatedFormat('F Y'),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Data permohonan ijin berhasil dimuat.',
            'data' => [
                'requests' => $items,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
                'today_stats' => [
                    'pending' => $pendingCount,
                    'approved' => $approvedCount,
                    'rejected' => $rejectedCount,
                    'total' => $totalCount,
                ],
                'accessible_classes' => $accessInfo['classes'],
                'available_months' => $availableMonths,
                'user_info' => [
                    'name' => $user->name,
                    'is_wali_kelas' => $accessInfo['is_wali_kelas'],
                    'is_bk' => $accessInfo['is_bk'],
                    'role_summary' => $accessInfo['role_summary'],
                ],
                'academic_year' => [
                    'id' => $activeYear?->id,
                    'name' => $activeYear?->name ?? 'Tahun Ajaran Aktif',
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/ijin-kehadiran/{id}
     * Detail Lengkap satu permohonan izin beserta lampiran, ortu, log riwayat & otorisasi.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terautentikasi.',
            ], 401);
        }

        $activeYear = $this->getActiveAcademicYear();
        $accessInfo = $this->getAccessibleClasses($user, $activeYear?->id);

        $record = LeaveRequest::with([
            'student.enrollmentAktif.kelas',
            'student.user',
            'logs.user',
            'approvedBy',
        ])->find($id);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Data permohonan ijin tidak ditemukan.',
            ], 404);
        }

        $student = $record->student;
        $enrollment = $student?->enrollmentAktif;
        $classId = $enrollment?->class_id;

        // Pastikan guru memiliki akses ke kelas siswa ini
        if (!$accessInfo['is_bypass'] && !in_array($classId, $accessInfo['all_class_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki akses ke data kelas siswa ini.',
            ], 403);
        }

        $canApprove = $this->canApproveOrReject($user, $record, $accessInfo);

        // Kumpulkan data lampiran (tetap membaca path database meskipun file fisik di storage belum diexport)
        $attachmentsData = $this->extractAttachments($record);

        // Format nomor HP Ortu untuk WhatsApp
        $rawParentPhone = $student?->no_hp_orang_tua ?: $student?->no_hp;
        $cleanParentPhone = null;
        if ($rawParentPhone) {
            $cleaned = preg_replace('/[^0-9]/', '', $rawParentPhone);
            if (str_starts_with($cleaned, '08')) {
                $cleanParentPhone = '628' . substr($cleaned, 2);
            } elseif (str_starts_with($cleaned, '62')) {
                $cleanParentPhone = $cleaned;
            } else {
                $cleanParentPhone = $cleaned;
            }
        }

        // Format log riwayat
        $logs = $record->logs->map(function ($log) {
            $actionLabel = match ($log->action) {
                'created' => 'Diajukan',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'updated' => 'Diperbarui',
                default => ucfirst($log->action),
            };

            return [
                'id' => (string) $log->id,
                'action' => $log->action,
                'action_label' => $actionLabel,
                'reason' => $log->reason,
                'user_name' => $log->user?->name ?? 'Sistem',
                'created_at' => $log->created_at ? Carbon::parse($log->created_at)->format('Y-m-d H:i') : null,
                'created_at_formatted' => $log->created_at ? Carbon::parse($log->created_at)->translatedFormat('l, d F Y H:i') : '-',
            ];
        });

        // Banner pembatasan wewenang jika Guru BK
        $restrictionNotice = null;
        if (!$canApprove) {
            $waliName = 'Wali Kelas';
            if ($enrollment) {
                $kelasAjaran = \App\Models\KelasAjaran::where('class_id', $enrollment->class_id)
                    ->where('academic_year_id', $enrollment->academic_year_id)
                    ->with('guru')
                    ->first();
                if ($kelasAjaran?->guru?->name) {
                    $waliName = $kelasAjaran->guru->name;
                }
            }
            $restrictionNotice = "Anda login sebagai Guru BK dengan hak pantau. Wewenang persetujuan atau penolakan berada pada Wali Kelas ({$waliName}).";
        }

        $statusLabel = match ($record->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Menunggu Persetujuan',
        };

        $typeLabel = match ($record->type) {
            'sakit' => 'Sakit',
            default => 'Izin',
        };

        return response()->json([
            'success' => true,
            'message' => 'Detail permohonan ijin berhasil dimuat.',
            'data' => [
                'id' => (string) $record->id,
                'student' => [
                    'id' => (string) $student?->id,
                    'name' => $student?->name ?? 'Siswa',
                    'nisn' => $student?->nisn ?? '-',
                    'nis' => $student?->nis ?? '-',
                    'gender' => $student?->gender ?? 'L',
                    'avatar_url' => $student?->avatar_url ?? null,
                    'class_name' => $enrollment?->kelas?->name ?? '-',
                    'class_id' => $classId,
                    'parent_name' => $student?->nama_ayah ?: ($student?->nama_ibu ?: ($student?->nama_wali ?: 'Orang Tua / Wali')),
                    'parent_phone' => $rawParentPhone,
                    'parent_phone_clean' => $cleanParentPhone,
                ],
                'leave' => [
                    'type' => $record->type,
                    'type_label' => $typeLabel,
                    'start_date' => $record->start_date ? Carbon::parse($record->start_date)->format('Y-m-d') : null,
                    'start_date_formatted' => $record->start_date ? Carbon::parse($record->start_date)->translatedFormat('l, d F Y') : '-',
                    'end_date' => $record->end_date ? Carbon::parse($record->end_date)->format('Y-m-d') : null,
                    'end_date_formatted' => $record->end_date ? Carbon::parse($record->end_date)->translatedFormat('l, d F Y') : '-',
                    'duration_days' => (int) $record->duration_days,
                    'duration_label' => $record->duration_days . ' Hari Kalender',
                    'reason' => (string) $record->reason,
                    'status' => $record->status,
                    'status_label' => $statusLabel,
                    'approved_by_name' => $record->approvedBy?->name ?? null,
                    'approved_at' => $record->approved_at ? Carbon::parse($record->approved_at)->format('Y-m-d H:i') : null,
                    'approved_at_formatted' => $record->approved_at ? Carbon::parse($record->approved_at)->translatedFormat('l, d F Y H:i') : null,
                    'created_at_formatted' => $record->created_at ? Carbon::parse($record->created_at)->translatedFormat('l, d F Y H:i') : '-',
                ],
                'attachments' => $attachmentsData,
                'logs' => $logs,
                'authorization' => [
                    'can_approve_or_reject' => $canApprove,
                    'restriction_notice' => $restrictionNotice,
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/guru/ijin-kehadiran/{id}/approve
     * Menyetujui permohonan izin siswa & memicu otomatisasi presensi via LeaveRequestService.
     */
    public function approve(Request $request, string $id, LeaveRequestService $service): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terautentikasi.',
            ], 401);
        }

        $activeYear = $this->getActiveAcademicYear();
        $accessInfo = $this->getAccessibleClasses($user, $activeYear?->id);

        $record = LeaveRequest::with(['student.enrollmentAktif', 'approvedBy'])->find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Data permohonan ijin tidak ditemukan.',
            ], 404);
        }

        if (!$this->canApproveOrReject($user, $record, $accessInfo)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Wali Kelas dari siswa bersangkutan yang berhak menyetujui permohonan ijin.',
            ], 403);
        }

        if ($record->status === 'approved') {
            return response()->json([
                'success' => true,
                'message' => 'Permohonan ijin ini sudah berstatus disetujui.',
            ]);
        }

        DB::beginTransaction();
        try {
            $record->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_by_type' => get_class($user),
                'approved_at' => now(),
            ]);

            $record->recordLog('approved', "Disetujui oleh {$user->name} via Mobile App");
            $generatedCount = $service->syncAttendances($record);

            DB::commit();

            // Kirim notifikasi WA ke Siswa / Orang Tua secara asinkron
            $this->sendNotificationToStudent($record, 'DISETUJUI', null, $user);

            return response()->json([
                'success' => true,
                'message' => "Ijin berhasil disetujui. {$generatedCount} catatan presensi berhasil dibuat/disinkronkan.",
                'data' => [
                    'id' => (string) $record->id,
                    'status' => 'approved',
                    'generated_presensi_count' => $generatedCount,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyetujui izin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/guru/ijin-kehadiran/{id}/reject
     * Menolak permohonan izin siswa, mencabut presensi jika sebelumnya approved, & kirim notifikasi penolakan.
     */
    public function reject(Request $request, string $id, LeaveRequestService $service): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terautentikasi.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:3',
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 3 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $activeYear = $this->getActiveAcademicYear();
        $accessInfo = $this->getAccessibleClasses($user, $activeYear?->id);

        $record = LeaveRequest::with(['student.enrollmentAktif', 'approvedBy'])->find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Data permohonan ijin tidak ditemukan.',
            ], 404);
        }

        if (!$this->canApproveOrReject($user, $record, $accessInfo)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Wali Kelas dari siswa bersangkutan yang berhak menolak permohonan ijin.',
            ], 403);
        }

        $oldStatus = $record->status;
        $reason = trim((string) $request->input('reason'));

        DB::beginTransaction();
        try {
            // Jika sebelumnya disetujui, hapus presensi yang sudah di-generate
            if ($oldStatus === 'approved') {
                $service->removeAttendances($record);
            }

            $record->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_by_type' => get_class($user),
                'approved_at' => now(),
            ]);

            $record->recordLog('rejected', "Ditolak: {$reason}");

            DB::commit();

            // Kirim notifikasi WA penolakan ke Siswa / Orang Tua
            $this->sendNotificationToStudent($record, 'DITOLAK', $reason, $user);

            return response()->json([
                'success' => true,
                'message' => 'Permohonan ijin berhasil ditolak.',
                'data' => [
                    'id' => (string) $record->id,
                    'status' => 'rejected',
                    'reason' => $reason,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menolak izin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/guru/ijin-kehadiran/{id}/reset
     * Mengembalikan status permohonan ke 'pending' & mencabut presensi terkait.
     */
    public function reset(Request $request, string $id, LeaveRequestService $service): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terautentikasi.',
            ], 401);
        }

        $activeYear = $this->getActiveAcademicYear();
        $accessInfo = $this->getAccessibleClasses($user, $activeYear?->id);

        $record = LeaveRequest::with(['student.enrollmentAktif'])->find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Data permohonan ijin tidak ditemukan.',
            ], 404);
        }

        if (!$this->canApproveOrReject($user, $record, $accessInfo)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Wali Kelas dari siswa bersangkutan yang berhak mengubah status permohonan ijin.',
            ], 403);
        }

        $oldStatus = $record->status;
        $reason = trim((string) $request->input('reason', 'Dikembalikan ke status pending oleh guru via Mobile App'));

        DB::beginTransaction();
        try {
            if ($oldStatus === 'approved') {
                $service->removeAttendances($record);
            }

            $record->update([
                'status' => 'pending',
                'approved_by' => null,
                'approved_by_type' => null,
                'approved_at' => null,
            ]);

            $record->recordLog('updated', "Dikembalikan ke Pending: {$reason}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status permohonan berhasil dikembalikan ke Pending.',
                'data' => [
                    'id' => (string) $record->id,
                    'status' => 'pending',
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengembalikan status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/guru/ijin-kehadiran/notification-check
     * Polling ringan untuk mengecek permohonan pending baru (In-App Notification di Android).
     */
    public function notificationCheck(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $activeYear = $this->getActiveAcademicYear();
        $accessInfo = $this->getAccessibleClasses($user, $activeYear?->id);

        if (empty($accessInfo['all_class_ids'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'pending_count' => 0,
                    'latest_pending' => null,
                ],
            ]);
        }

        $studentIds = EnrollmentSiswa::whereIn('class_id', $accessInfo['all_class_ids'])
            ->where('status', 'aktif')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('student_id')
            ->toArray();

        $pendingQuery = LeaveRequest::whereIn('student_id', $studentIds)
            ->where('status', 'pending')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->with(['student.enrollmentAktif.kelas'])
            ->orderBy('created_at', 'desc');

        $pendingCount = (clone $pendingQuery)->count();
        $latest = $pendingQuery->first();

        $latestData = null;
        if ($latest) {
            $student = $latest->student;
            $kelasName = $student?->enrollmentAktif?->kelas?->name ?? '-';
            $typeLabel = $latest->type === 'sakit' ? 'Sakit' : 'Izin';

            $latestData = [
                'id' => (string) $latest->id,
                'title' => "Permohonan {$typeLabel} Baru",
                'body' => "{$student?->name} (Kelas {$kelasName}) mengajukan {$typeLabel} ({$latest->duration_days} hari)",
                'student_name' => $student?->name,
                'class_name' => $kelasName,
                'type' => $latest->type,
                'duration_days' => $latest->duration_days,
                'created_at' => $latest->created_at ? $latest->created_at->toIso8601String() : null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'pending_count' => $pendingCount,
                'latest_pending' => $latestData,
            ],
        ]);
    }

    /**
     * Helper pengiriman notifikasi WhatsApp ke Siswa / Orang Tua saat permohonan diputuskan.
     */
    protected function sendNotificationToStudent(LeaveRequest $record, string $statusLabel, ?string $alasanTolak, ?User $user): void
    {
        try {
            $setting = PresensiNotificationSetting::where('status_presensi', 'leave_approval')->first();
            if (!$setting || !$setting->is_active || empty($setting->recipients) || empty($setting->template_pesan)) {
                return;
            }

            $student = $record->student;
            if (!$student) return;

            $alasanText = $alasanTolak ? "Alasan penolakan: " . $alasanTolak : "";

            $replacements = [
                '{nama_siswa}' => $student->name,
                '{jenis_ijin}' => ucfirst($record->type),
                '{tanggal_mulai}' => Carbon::parse($record->start_date)->translatedFormat('d F Y'),
                '{tanggal_selesai}' => Carbon::parse($record->end_date)->translatedFormat('d F Y'),
                '{status_persetujuan}' => $statusLabel,
                '{nama_guru}' => $user?->name ?? 'Wali Kelas',
                '{alasan_penolakan}' => $alasanText,
            ];

            $pesan = strtr($setting->template_pesan, $replacements);

            $resolver = app(RecipientResolverService::class);
            $resolvedRecipients = $resolver->resolveRecipients($setting->recipients, $student);

            foreach ($resolvedRecipients as $recipient) {
                if (!empty($recipient['number'])) {
                    \App\Jobs\SendWhatsAppNotificationJob::dispatch(
                        $recipient['number'],
                        $pesan,
                        'leave_approval',
                        $record->id,
                        $recipient['type'] ?? 'unknown'
                    );
                }
            }
        } catch (\Throwable $e) {
            // Notifikasi WA gagal tidak boleh membatalkan aksi persetujuan di UI
            report($e);
        }
    }

    /**
     * GET /api/v1/guru/ijin-kehadiran/file-stream
     * Melayani file lampiran dengan header Access-Control-Allow-Origin: *
     * agar gambar dapat dimuat di Flutter Web / Mobile tanpa terblokir CORS policy browser.
     */
    public function streamFile(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $path = $request->input('path');
        if (empty($path)) {
            abort(404, 'Path berkas tidak diberikan.');
        }

        $cleanPath = ltrim(str_replace(['public/', '/storage/', 'storage/'], '', $path), '/');

        // Cegah path traversal
        if (str_contains($cleanPath, '..')) {
            abort(403, 'Akses path tidak valid.');
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($cleanPath)) {
            if (!str_contains($cleanPath, '/') && $disk->exists('leave-requests/' . $cleanPath)) {
                $cleanPath = 'leave-requests/' . $cleanPath;
            } else {
                // Jika file fisik tidak ada di disk (misal dump db tanpa folder storage), kembalikan SVG placeholder
                $fileNameEscaped = htmlspecialchars(basename($cleanPath));
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="240" viewBox="0 0 400 240"><rect width="100%" height="100%" fill="#F1F5F9" rx="12"/><path d="M170 80h60v20h-60zm-20 30h100v12H150zm10 20h80v12h-80z" fill="#94A3B8"/><text x="50%" y="68%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="13" font-weight="bold" fill="#334155">' . $fileNameEscaped . '</text><text x="50%" y="82%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="11" fill="#64748B">(Berkas lampiran tercatat di database)</text></svg>';
                return response($svg, 200, [
                    'Content-Type' => 'image/svg+xml',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                    'Access-Control-Allow-Headers' => '*',
                    'Cache-Control' => 'no-cache',
                ]);
            }
        }

        $fullPath = $disk->path($cleanPath);
        $mime = $disk->mimeType($cleanPath) ?: 'application/octet-stream';
        $filename = basename($cleanPath);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

