<?php

namespace App\Livewire;

use App\Exports\RekapPresensiHarianExport;
use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\KelasAjaran;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\PresensiNotificationSetting;
use App\Models\TahunAjaran;
use App\Services\WhatsAppGatewayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.portal')]
class PortalPresensiDashboard extends Component
{
    use WithPagination;

    public $selectedDate;
    public $selectedAcademicYearId;
    public $selectedClassFilter = 'all';
    public $searchQuery = '';
    public $activeTab = 'per_kelas'; // 'per_kelas' | 'belum_presensi' | 'siswa_terlambat' | 'semua_siswa'
    public $filterStatus = 'all';
    public int $perPage = 25;
    public bool $includeCheckout = true;

    protected ?array $cachedDataset = null;

    public function mount(): void
    {
        $this->selectedDate = Carbon::today('Asia/Jakarta')->toDateString();
        $this->includeCheckout = (bool) (PengaturanSekolah::current()?->enable_checkout ?? true);

        $activeYear = TahunAjaran::where('status', 'aktif')->first()
            ?? TahunAjaran::orderBy('start_year', 'desc')->first();

        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }
    }

    public function updatedSelectedDate(): void
    {
        $this->cachedDataset = null;
        $this->resetPage();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->cachedDataset = null;
        $this->resetPage();
    }

    public function updatedSelectedClassFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;
        $this->perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 25;
        $this->resetPage();
    }

    public function loadData(): void
    {
        $this->cachedDataset = null;
    }

    /**
     * Hitung seluruh ringkasan presensi dalam satu request tanpa N+1 query
     * dan tanpa menyimpan array besar di snapshot Livewire.
     */
    protected function getDataset(): array
    {
        if ($this->cachedDataset !== null) {
            return $this->cachedDataset;
        }

        $academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $dateLabel = Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');

        if (! $this->selectedAcademicYearId) {
            return $this->cachedDataset = [
                'academicYears'      => $academicYears,
                'classes'            => collect(),
                'dateLabel'          => $dateLabel,
                'stats'              => [],
                'classesSummary'     => [],
                'unattendedStudents' => [],
                'lateStudents'       => [],
                'allStudents'        => [],
            ];
        }

        // 1. Ambil data kelas aktif pada tahun ajaran ini
        $classes = Kelas::where(function ($q) {
            $q->whereHas('kelasAjarans', function ($sub) {
                $sub->where('academic_year_id', $this->selectedAcademicYearId);
            })->orWhereHas('enrollments', function ($sub) {
                $sub->where('academic_year_id', $this->selectedAcademicYearId)->where('status', 'aktif');
            });
        })->orderBy('name', 'asc')->get(['id', 'name', 'grade_level']);

        // 2. Eager-load semua Wali Kelas dalam 1 query untuk menghindari N+1
        $waliKelasByClassId = KelasAjaran::where('academic_year_id', $this->selectedAcademicYearId)
            ->whereIn('class_id', $classes->pluck('id'))
            ->with('guru:id,name')
            ->get()
            ->mapWithKeys(fn ($ka) => [$ka->class_id => $ka->guru?->name ?? '-']);

        // 3. Ambil seluruh data enrollment siswa aktif dikelompokkan per class_id
        $enrollmentsByClass = EnrollmentSiswa::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status', 'aktif')
            ->select('id', 'student_id', 'class_id')
            ->with([
                'siswa' => fn ($q) => $q->select('id', 'name', 'nisn', 'gender', 'no_hp', 'no_hp_orang_tua'),
            ])
            ->get()
            ->groupBy('class_id');

        // 4. Ambil presensi tanggal terpilih dalam 1 query
        $attendances = Presensi::with(['inputManualOleh', 'manualInputUser'])
            ->where('date', $date)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->select(
                'id',
                'student_id',
                'status',
                'scan_time',
                'scan_out_time',
                'status_pulang',
                'note',
                'late_minutes',
                'is_manual_input',
                'manual_input_by_id',
                'manual_input_by_type',
                'manual_input_at',
                'manual_input_user_id',
                'created_at',
                'updated_at'
            )
            ->get()
            ->keyBy('student_id');

        // 5. Kalkulasi statistik per kelas dan sekolah
        $classesSummary = [];
        $unattended = [];
        $late = [];
        $all = [];

        $schoolTotal = 0;
        $schoolHadir = 0;
        $schoolTelat = 0;
        $schoolIzin = 0;
        $schoolSakit = 0;
        $schoolAlpa = 0;
        $schoolBelum = 0;

        $schoolTotal_L = 0; $schoolTotal_P = 0;
        $schoolHadir_L = 0; $schoolHadir_P = 0;
        $schoolIzin_L = 0;  $schoolIzin_P = 0;
        $schoolSakit_L = 0; $schoolSakit_P = 0;
        $schoolAlpa_L = 0;  $schoolAlpa_P = 0;
        $schoolBelum_L = 0; $schoolBelum_P = 0;

        foreach ($classes as $cls) {
            $cEnrollments = $enrollmentsByClass->get($cls->id, collect());
            $cTotal = $cEnrollments->count();
            if ($cTotal === 0) {
                continue;
            }

            $waliKelasName = $waliKelasByClassId->get($cls->id, '-');

            $cTotal_L = 0; $cTotal_P = 0;
            $cHadir_L = 0; $cHadir_P = 0;
            $cIzin_L = 0;  $cIzin_P = 0;
            $cSakit_L = 0; $cSakit_P = 0;
            $cAlpa_L = 0;  $cAlpa_P = 0;
            $cBelum_L = 0; $cBelum_P = 0;

            $cHadir = 0;
            $cTelat = 0;
            $cIzin = 0;
            $cSakit = 0;
            $cAlpa = 0;
            $cBelum = 0;

            foreach ($cEnrollments as $enr) {
                $st = $enr->siswa;
                if (! $st) {
                    continue;
                }

                $att = $attendances->get($st->id);
                $status = 'belum';
                $scanTime = null;
                $scanOut = null;
                $statusPulang = null;
                $note = null;
                $lateMinutes = 0;
                $manualAudit = null;

                if ($st->gender === 'L') {
                    $cTotal_L++;
                } else {
                    $cTotal_P++;
                }

                if ($att) {
                    $status = $att->status;
                    $scanTime = $att->scan_time ? Carbon::parse($att->scan_time)->format('H:i') : null;
                    $scanOut = $att->scan_out_time ? Carbon::parse($att->scan_out_time)->format('H:i') : null;
                    $statusPulang = $att->status_pulang;
                    $note = $att->note;
                    $lateMinutes = (int) ($att->late_minutes ?? 0);
                    $manualAudit = $att->getManualAuditInfo();

                    if ($status === 'hadir' || $status === 'telat') {
                        if ($status === 'hadir') {
                            $cHadir++;
                        }
                        if ($status === 'telat') {
                            $cTelat++;
                        }

                        if ($st->gender === 'L') {
                            $cHadir_L++;
                        } else {
                            $cHadir_P++;
                        }
                    } elseif ($status === 'izin') {
                        $cIzin++;
                        if ($st->gender === 'L') {
                            $cIzin_L++;
                        } else {
                            $cIzin_P++;
                        }
                    } elseif ($status === 'sakit') {
                        $cSakit++;
                        if ($st->gender === 'L') {
                            $cSakit_L++;
                        } else {
                            $cSakit_P++;
                        }
                    } elseif ($status === 'alpa') {
                        $cAlpa++;
                        if ($st->gender === 'L') {
                            $cAlpa_L++;
                        } else {
                            $cAlpa_P++;
                        }
                    } else {
                        $cBelum++;
                        if ($st->gender === 'L') {
                            $cBelum_L++;
                        } else {
                            $cBelum_P++;
                        }
                        $status = 'belum';
                    }
                } else {
                    $cBelum++;
                    if ($st->gender === 'L') {
                        $cBelum_L++;
                    } else {
                        $cBelum_P++;
                    }
                    $status = 'belum';
                }

                $rawPhone = $st->no_hp_orang_tua ?: $st->no_hp;
                $cleanPhone = $this->normalizeWaPhone($rawPhone);

                $studentItem = [
                    'id'           => $st->id,
                    'name'         => $st->name,
                    'nisn'         => $st->nisn,
                    'gender'       => $st->gender ?? '-',
                    'class_id'     => $cls->id,
                    'class_name'   => $cls->name,
                    'wali_kelas'   => $waliKelasName,
                    'status'       => $status,
                    'scan_time'    => $scanTime,
                    'scan_out'     => $scanOut,
                    'status_pulang'=> $statusPulang,
                    'late_minutes' => $lateMinutes,
                    'note'         => $note,
                    'manual_audit' => $manualAudit,
                    'phone'        => $rawPhone,
                    'clean_phone'  => $cleanPhone,
                ];

                $all[] = $studentItem;

                if ($status === 'belum') {
                    $unattended[] = $studentItem;
                }

                if ($status === 'telat') {
                    $late[] = $studentItem;
                }
            }

            $cTotalHadir = $cHadir + $cTelat;
            $pct = $cTotal > 0 ? round(($cTotalHadir / $cTotal) * 100, 1) : 0;
            $pct_L = $cTotal_L > 0 ? round(($cHadir_L / $cTotal_L) * 100, 1) : 0;
            $pct_P = $cTotal_P > 0 ? round(($cHadir_P / $cTotal_P) * 100, 1) : 0;

            $classesSummary[] = [
                'id'          => $cls->id,
                'name'        => $cls->name,
                'wali_kelas'  => $waliKelasName,
                'total'       => $cTotal,
                'total_l'     => $cTotal_L,
                'total_p'     => $cTotal_P,
                'hadir'       => $cHadir,
                'telat'       => $cTelat,
                'total_hadir' => $cTotalHadir,
                'hadir_l'     => $cHadir_L,
                'hadir_p'     => $cHadir_P,
                'izin'        => $cIzin,
                'izin_l'      => $cIzin_L,
                'izin_p'      => $cIzin_P,
                'sakit'       => $cSakit,
                'sakit_l'     => $cSakit_L,
                'sakit_p'     => $cSakit_P,
                'alpa'        => $cAlpa,
                'alpa_l'      => $cAlpa_L,
                'alpa_p'      => $cAlpa_P,
                'belum'       => $cBelum,
                'belum_l'     => $cBelum_L,
                'belum_p'     => $cBelum_P,
                'persen'      => $pct,
                'persen_l'    => $pct_L,
                'persen_p'    => $pct_P,
            ];

            $schoolTotal += $cTotal;
            $schoolHadir += $cHadir;
            $schoolTelat += $cTelat;
            $schoolIzin  += $cIzin;
            $schoolSakit += $cSakit;
            $schoolAlpa  += $cAlpa;
            $schoolBelum += $cBelum;

            $schoolTotal_L += $cTotal_L; $schoolTotal_P += $cTotal_P;
            $schoolHadir_L += $cHadir_L; $schoolHadir_P += $cHadir_P;
            $schoolIzin_L  += $cIzin_L;  $schoolIzin_P  += $cIzin_P;
            $schoolSakit_L += $cSakit_L; $schoolSakit_P += $cSakit_P;
            $schoolAlpa_L  += $cAlpa_L;  $schoolAlpa_P  += $cAlpa_P;
            $schoolBelum_L += $cBelum_L; $schoolBelum_P += $cBelum_P;
        }

        $schoolTotalHadir = $schoolHadir + $schoolTelat;
        $schoolPct = $schoolTotal > 0 ? round(($schoolTotalHadir / $schoolTotal) * 100, 1) : 0;

        $stats = [
            'total_students'   => $schoolTotal,
            'total_l'          => $schoolTotal_L,
            'total_p'          => $schoolTotal_P,
            'hadir'            => $schoolHadir,
            'telat'            => $schoolTelat,
            'total_hadir'      => $schoolTotalHadir,
            'hadir_l'          => $schoolHadir_L,
            'hadir_p'          => $schoolHadir_P,
            'izin'             => $schoolIzin,
            'izin_l'           => $schoolIzin_L,
            'izin_p'           => $schoolIzin_P,
            'sakit'            => $schoolSakit,
            'sakit_l'          => $schoolSakit_L,
            'sakit_p'          => $schoolSakit_P,
            'alpa'             => $schoolAlpa,
            'alpa_l'           => $schoolAlpa_L,
            'alpa_p'           => $schoolAlpa_P,
            'tidak_hadir'      => $schoolIzin + $schoolSakit + $schoolAlpa,
            'belum'            => $schoolBelum,
            'belum_l'          => $schoolBelum_L,
            'belum_p'          => $schoolBelum_P,
            'persentase_hadir' => $schoolPct,
            'persen_l'         => $schoolTotal_L > 0 ? round(($schoolHadir_L / $schoolTotal_L) * 100, 1) : 0,
            'persen_p'         => $schoolTotal_P > 0 ? round(($schoolHadir_P / $schoolTotal_P) * 100, 1) : 0,
        ];

        return $this->cachedDataset = [
            'academicYears'      => $academicYears,
            'classes'            => $classes,
            'dateLabel'          => $dateLabel,
            'stats'              => $stats,
            'classesSummary'     => $classesSummary,
            'unattendedStudents' => $unattended,
            'lateStudents'       => $late,
            'allStudents'        => $all,
        ];
    }

    protected function normalizeWaPhone(?string $rawPhone): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
        if ($cleanPhone === '') {
            return '';
        }

        if (str_starts_with($cleanPhone, '0')) {
            return '62' . substr($cleanPhone, 1);
        }

        if (str_starts_with($cleanPhone, '8')) {
            return '62' . $cleanPhone;
        }

        return $cleanPhone;
    }

    /**
     * Filter daftar siswa sesuai dropdown kelas, status (opsional), dan kata kunci pencarian.
     */
    protected function filterStudents(array $students, bool $withStatusFilter = false): Collection
    {
        $search = strtolower(trim((string) $this->searchQuery));
        $classFilter = $this->selectedClassFilter;
        $statusFilter = $this->filterStatus;

        return collect($students)->filter(function (array $st) use ($classFilter, $statusFilter, $search, $withStatusFilter) {
            if ($classFilter !== 'all' && $st['class_id'] !== $classFilter) {
                return false;
            }
            if ($withStatusFilter && $statusFilter !== 'all' && $st['status'] !== $statusFilter) {
                return false;
            }
            if ($search !== '') {
                return str_contains(strtolower($st['name']), $search)
                    || str_contains(strtolower((string) ($st['nisn'] ?? '')), $search)
                    || str_contains(strtolower($st['class_name']), $search);
            }
            return true;
        })->values();
    }

    /**
     * Paginasi collection siswa dan isi template WA hanya untuk baris yang tampil.
     */
    protected function paginateStudents(Collection $filtered, string $dateLabel, ?string $waType = null): LengthAwarePaginator
    {
        $perPage = in_array($this->perPage, [25, 50, 100], true) ? $this->perPage : 25;
        $total = $filtered->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $this->getPage()), $lastPage);

        $slice = $filtered->forPage($page, $perPage)->values();

        if ($waType !== null && $slice->isNotEmpty()) {
            $settingTemplate = PresensiNotificationSetting::where('status_presensi', $waType)->value('template_pesan');
            $template = ! empty($settingTemplate)
                ? $settingTemplate
                : PresensiNotificationSetting::getDefaultTemplate($waType);
            $namaSekolah = PengaturanSekolah::current()?->school_name ?: 'SMP Negeri 1 Sampang';

            $slice = $slice->map(function (array $st) use ($waType, $template, $namaSekolah, $dateLabel) {
                $statusKehadiran = $waType === 'telat'
                    ? 'Terlambat ' . ($st['late_minutes'] ? "({$st['late_minutes']} menit)" : '')
                    : 'Belum Presensi';

                $text = strtr($template, [
                    '{nama_siswa}'       => $st['name'] ?? '-',
                    '{kelas}'            => $st['class_name'] ?? '-',
                    '{tanggal}'          => $dateLabel,
                    '{jam}'              => $st['scan_time'] ?: '-',
                    '{status_kehadiran}' => $statusKehadiran,
                    '{nama_wali_kelas}'  => $st['wali_kelas'] ?? '-',
                    '{nama_sekolah}'     => $namaSekolah,
                ]);

                if ($waType === 'telat') {
                    $st['wa_text_telat_raw'] = $text;
                } else {
                    $st['wa_text_raw'] = $text;
                }

                return $st;
            });
        }

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

    public function filterByClass(string $classId): void
    {
        $this->selectedClassFilter = $classId;
        $this->activeTab = 'belum_presensi';
        $this->resetPage();
    }

    public function filterLateByClass(string $classId): void
    {
        $this->selectedClassFilter = $classId;
        $this->activeTab = 'siswa_terlambat';
        $this->resetPage();
    }

    public function downloadRekapKelasPdf()
    {
        $dataset = $this->getDataset();
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sekolah = PengaturanSekolah::current();
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $dateFormatted = Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');

        $pdf = Pdf::loadView('pdf.laporan-rekap-per-kelas', [
            'stats'                 => $dataset['stats'],
            'classesSummary'        => $dataset['classesSummary'],
            'sekolah'               => $sekolah,
            'tahunAjaran'           => $ay,
            'selectedDateFormatted' => $dateFormatted,
            'unattendedStudents'    => $dataset['unattendedStudents'],
            'allStudents'           => $dataset['allStudents'],
            'includeCheckout'       => $this->includeCheckout,
            'generatedAt'           => now()->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB',
        ])->setPaper('a4', 'landscape');

        $safeDate = str_replace('-', '_', $date);
        $fileName = "Rekap_Kehadiran_Per_Kelas_{$safeDate}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName
        );
    }

    public function exportExcel()
    {
        $dataset = $this->getDataset();
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $safeDate = str_replace('-', '_', $date);
        $fileName = "Rekap_Kehadiran_Harian_{$safeDate}.xlsx";

        return Excel::download(
            new RekapPresensiHarianExport(
                $date,
                $this->selectedAcademicYearId,
                $dataset['stats'],
                $dataset['classesSummary'],
                $dataset['unattendedStudents'],
                $dataset['allStudents'],
                $this->includeCheckout
            ),
            $fileName
        );
    }

    public function downloadRekapHarianPdf()
    {
        $dataset = $this->getDataset();
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sekolah = PengaturanSekolah::current();
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $dateFormatted = Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');

        $pdf = Pdf::loadView('pdf.laporan-presensi-harian-sekolah', [
            'stats'                 => $dataset['stats'],
            'classesSummary'        => $dataset['classesSummary'],
            'sekolah'               => $sekolah,
            'tahunAjaran'           => $ay,
            'selectedDateFormatted' => $dateFormatted,
            'unattendedStudents'    => $dataset['unattendedStudents'],
            'allStudents'           => $dataset['allStudents'],
            'generatedAt'           => now()->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB',
        ])->setPaper('a4', 'landscape');

        $safeDate = str_replace('-', '_', $date);
        $fileName = "Rekap_Presensi_Harian_{$safeDate}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName
        );
    }

    public function downloadMbgPdf()
    {
        $dataset = $this->getDataset();
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $dateFormatted = Carbon::parse($date)->locale('id')->translatedFormat('l, d-m-Y');

        $pdf = Pdf::loadView('pdf.laporan-mbg-harian', [
            'classesSummary' => $dataset['classesSummary'],
            'tahunAjaran'    => $ay,
            'dateFormatted'  => $dateFormatted,
        ])->setPaper('a4', 'portrait');

        $safeDate = str_replace('-', '_', $date);
        $fileName = "Laporan_MBG_Harian_{$safeDate}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName
        );
    }

    public function exportMbgExcel()
    {
        $dataset = $this->getDataset();
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();

        $safeDate = str_replace('-', '_', $date);
        $fileName = "Laporan_MBG_Harian_{$safeDate}.xlsx";

        return Excel::download(
            new \App\Exports\LaporanMbgHarianExport(
                $date,
                $ay,
                $dataset['classesSummary']
            ),
            $fileName
        );
    }

    public function sendWaDirect(string $studentId, string $targetPhone, string $messageText, string $type = 'belum'): void
    {
        $notifSetting = PresensiNotificationSetting::where('status_presensi', $type)->first();
        if ($notifSetting && !$notifSetting->is_active) {
            $this->dispatch('notify', [
                'type'    => 'warning',
                'message' => 'Fitur notifikasi WhatsApp untuk kategori ini dinonaktifkan oleh Administrator.',
            ]);
            return;
        }

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'))) {
            $this->dispatch('notify', [
                'type'    => 'warning',
                'message' => 'Hari ini adalah hari libur sekolah. Pengiriman notifikasi WA dinonaktifkan.',
            ]);
            return;
        }

        $cleanPhone = $this->normalizeWaPhone($targetPhone);
        if (empty($cleanPhone)) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Nomor WhatsApp orang tua tidak valid atau masih kosong.',
            ]);
            return;
        }

        $waService = app(WhatsAppGatewayService::class);
        $sent = $waService->sendMessage(
            toNumber: $cleanPhone,
            message: $messageText,
            relatedType: 'presensi_' . $type,
            relatedId: $studentId,
            recipientType: 'ortu',
            logId: null,
            ignoreDelay: true
        );

        if ($sent) {
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => "Pesan WhatsApp berhasil dikirim ke nomor {$cleanPhone}!",
            ]);
            $this->dispatch('wa-modal-close');
        } else {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Gagal mengirim pesan WhatsApp via sistem gateway. Silakan periksa gateway WhatsApp.',
            ]);
        }
    }

    public function render()
    {
        $dataset = $this->getDataset();
        $notifSettings = PresensiNotificationSetting::whereIn('status_presensi', ['belum', 'telat'])
            ->pluck('is_active', 'status_presensi');

        $isNotifBelumActive = (bool) ($notifSettings->get('belum', false));
        $isNotifTelatActive = (bool) ($notifSettings->get('telat', false));

        $paginatedStudents = match ($this->activeTab) {
            'belum_presensi'  => $this->paginateStudents(
                $this->filterStudents($dataset['unattendedStudents']),
                $dataset['dateLabel'],
                'belum'
            ),
            'siswa_terlambat' => $this->paginateStudents(
                $this->filterStudents($dataset['lateStudents']),
                $dataset['dateLabel'],
                'telat'
            ),
            'semua_siswa'     => $this->paginateStudents(
                $this->filterStudents($dataset['allStudents'], withStatusFilter: true),
                $dataset['dateLabel'],
                null
            ),
            default           => null,
        };

        return view('livewire.portal-presensi-dashboard', [
            'selectedDate'           => $this->selectedDate,
            'academicYears'          => $dataset['academicYears'],
            'selectedAcademicYearId' => $this->selectedAcademicYearId,
            'classes'                => $dataset['classes'],
            'selectedClassFilter'    => $this->selectedClassFilter,
            'searchQuery'            => $this->searchQuery,
            'activeTab'              => $this->activeTab,
            'filterStatus'           => $this->filterStatus,
            'perPage'                => $this->perPage,
            'stats'                  => $dataset['stats'],
            'classesSummary'         => $dataset['classesSummary'],
            'unattendedCount'        => count($dataset['unattendedStudents']),
            'lateCount'              => count($dataset['lateStudents']),
            'paginatedStudents'      => $paginatedStudents,
            'isNotifBelumActive'     => $isNotifBelumActive,
            'isNotifTelatActive'     => $isNotifTelatActive,
            'includeCheckout'        => $this->includeCheckout,
        ])->title('Dashboard Presensi Kehadiran - Portal Presensi');
    }
}
