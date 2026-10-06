<?php

namespace App\Livewire;

use App\Exports\RekapPresensiHarianExport;
use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.portal')]
class PortalPresensiDashboard extends Component
{
    public $selectedDate;
    public $academicYears = [];
    public $selectedAcademicYearId;
    public $classes = [];
    public $selectedClassFilter = 'all';
    public $searchQuery = '';
    public $activeTab = 'per_kelas'; // 'per_kelas' | 'belum_presensi' | 'semua_siswa'
    public $filterStatus = 'all';

    public $stats = [];
    public $classesSummary = [];
    public $unattendedStudents = [];
    public $allStudents = [];

    public function mount(): void
    {
        $this->selectedDate = Carbon::today('Asia/Jakarta')->toDateString();
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadData();
    }

    public function updatedSelectedDate(): void
    {
        $this->loadData();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        if (! $this->selectedAcademicYearId) {
            $this->stats = [];
            $this->classesSummary = [];
            $this->unattendedStudents = [];
            $this->allStudents = [];
            $this->classes = [];
            return;
        }

        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $dateLabel = Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');

        // 1. Ambil data kelas aktif pada tahun ajaran ini
        $this->classes = Kelas::where(function ($q) {
            $q->whereHas('kelasAjarans', function ($sub) {
                $sub->where('academic_year_id', $this->selectedAcademicYearId);
            })->orWhereHas('enrollments', function ($sub) {
                $sub->where('academic_year_id', $this->selectedAcademicYearId)->where('status', 'aktif');
            });
        })->orderBy('name', 'asc')->get();

        // 2. Ambil seluruh data enrollment siswa aktif di sekolah
        $enrollments = EnrollmentSiswa::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status', 'aktif')
            ->with([
                'siswa' => fn ($q) => $q->select('id', 'name', 'nisn', 'gender', 'no_hp', 'no_hp_orang_tua'),
                'kelas' => fn ($q) => $q->select('id', 'name', 'grade_level'),
            ])
            ->get();

        // 3. Ambil presensi hari ini
        $attendances = Presensi::where('date', $date)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->get()
            ->keyBy('student_id');

        // 4. Kalkulasi statistik per kelas dan sekolah
        $classesSummary = [];
        $unattended = [];
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

        foreach ($this->classes as $cls) {
            $cEnrollments = $enrollments->where('class_id', $cls->id);
            $cTotal = $cEnrollments->count();
            if ($cTotal === 0) continue;

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
                if (! $st) continue;

                $att = $attendances->get($st->id);
                $status = 'belum';
                $scanTime = null;
                $scanOut = null;
                $note = null;

                if ($st->gender === 'L') {
                    $cTotal_L++;
                } else {
                    $cTotal_P++;
                }

                if ($att) {
                    $status = $att->status;
                    $scanTime = $att->scan_time ? Carbon::parse($att->scan_time)->format('H:i') : null;
                    $scanOut = $att->scan_out_time ? Carbon::parse($att->scan_out_time)->format('H:i') : null;
                    $note = $att->note;

                    if ($status === 'hadir' || $status === 'telat') {
                        if ($status === 'hadir') $cHadir++;
                        if ($status === 'telat') $cTelat++;
                        
                        if ($st->gender === 'L') {
                            $cHadir_L++;
                        } else {
                            $cHadir_P++;
                        }
                    } elseif ($status === 'izin') {
                        $cIzin++;
                        if ($st->gender === 'L') $cIzin_L++; else $cIzin_P++;
                    } elseif ($status === 'sakit') {
                        $cSakit++;
                        if ($st->gender === 'L') $cSakit_L++; else $cSakit_P++;
                    } elseif ($status === 'alpa') {
                        $cAlpa++;
                        if ($st->gender === 'L') $cAlpa_L++; else $cAlpa_P++;
                    } else {
                        $cBelum++;
                        if ($st->gender === 'L') $cBelum_L++; else $cBelum_P++;
                        $status = 'belum';
                    }
                } else {
                    $cBelum++;
                    if ($st->gender === 'L') $cBelum_L++; else $cBelum_P++;
                    $status = 'belum';
                }

                // WhatsApp Phone Processing
                $rawPhone = $st->no_hp_orang_tua ?: $st->no_hp;
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
                if (! empty($cleanPhone)) {
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    } elseif (str_starts_with($cleanPhone, '8')) {
                        $cleanPhone = '62' . $cleanPhone;
                    }
                }

                $waText = urlencode("Assalamu'alaikum / Selamat pagi Bapak/Ibu wali dari {$st->name} (Kelas {$cls->name}).\n\nKami menginfokan bahwa ananda belum tercatat hadir dalam presensi SMP Negeri 3 Kedungreja hari ini ({$dateLabel}).\n\nMohon konfirmasi keterangan kehadiran ananda. Terima kasih.");
                $waUrl = ! empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text={$waText}" : null;

                $studentItem = [
                    'id'          => $st->id,
                    'name'        => $st->name,
                    'nisn'        => $st->nisn,
                    'gender'      => $st->gender ?? '-',
                    'class_id'    => $cls->id,
                    'class_name'  => $cls->name,
                    'status'      => $status,
                    'scan_time'   => $scanTime,
                    'scan_out'    => $scanOut,
                    'note'        => $note,
                    'phone'       => $rawPhone,
                    'clean_phone' => $cleanPhone,
                    'wa_link'     => $waUrl,
                ];

                $all[] = $studentItem;

                if ($status === 'belum') {
                    $unattended[] = $studentItem;
                }
            }

            $cTotalHadir = $cHadir + $cTelat;
            $pct = $cTotal > 0 ? round(($cTotalHadir / $cTotal) * 100, 1) : 0;
            $pct_L = $cTotal_L > 0 ? round(($cHadir_L / $cTotal_L) * 100, 1) : 0;
            $pct_P = $cTotal_P > 0 ? round(($cHadir_P / $cTotal_P) * 100, 1) : 0;

            $classesSummary[] = [
                'id'          => $cls->id,
                'name'        => $cls->name,
                'wali_kelas'  => $cls->waliKelas($this->selectedAcademicYearId)?->name ?? '-',
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

        $this->stats = [
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

        $this->classesSummary = $classesSummary;
        $this->unattendedStudents = $unattended;
        $this->allStudents = $all;
    }

    public function filterByClass(string $classId): void
    {
        $this->selectedClassFilter = $classId;
        $this->activeTab = 'belum_presensi';
    }

    public function downloadRekapKelasPdf()
    {
        if (empty($this->classesSummary)) {
            $this->loadData();
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sekolah = PengaturanSekolah::current();
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $dateFormatted = Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');

        $pdf = Pdf::loadView('pdf.laporan-rekap-per-kelas', [
            'stats'                 => $this->stats,
            'classesSummary'        => $this->classesSummary,
            'sekolah'               => $sekolah,
            'tahunAjaran'           => $ay,
            'selectedDateFormatted' => $dateFormatted,
            'unattendedStudents'    => $this->unattendedStudents,
            'allStudents'           => $this->allStudents,
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
        if (empty($this->classesSummary)) {
            $this->loadData();
        }

        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $safeDate = str_replace('-', '_', $date);
        $fileName = "Rekap_Kehadiran_Harian_{$safeDate}.xlsx";

        return Excel::download(
            new RekapPresensiHarianExport(
                $date,
                $this->selectedAcademicYearId,
                $this->stats,
                $this->classesSummary,
                $this->unattendedStudents,
                $this->allStudents
            ),
            $fileName
        );
    }

    public function downloadRekapHarianPdf()
    {
        if (empty($this->classesSummary)) {
            $this->loadData();
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sekolah = PengaturanSekolah::current();
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $dateFormatted = Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');

        $pdf = Pdf::loadView('pdf.laporan-presensi-harian-sekolah', [
            'stats'                 => $this->stats,
            'classesSummary'        => $this->classesSummary,
            'sekolah'               => $sekolah,
            'tahunAjaran'           => $ay,
            'selectedDateFormatted' => $dateFormatted,
            'unattendedStudents'    => $this->unattendedStudents,
            'allStudents'           => $this->allStudents,
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
        if (empty($this->classesSummary)) {
            $this->loadData();
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $dateFormatted = Carbon::parse($date)->locale('id')->translatedFormat('l, d-m-Y');

        $pdf = Pdf::loadView('pdf.laporan-mbg-harian', [
            'classesSummary' => $this->classesSummary,
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
        if (empty($this->classesSummary)) {
            $this->loadData();
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        
        $safeDate = str_replace('-', '_', $date);
        $fileName = "Laporan_MBG_Harian_{$safeDate}.xlsx";

        return Excel::download(
            new \App\Exports\LaporanMbgHarianExport(
                $date,
                $ay,
                $this->classesSummary
            ),
            $fileName
        );
    }

    public function render()
    {
        return view('livewire.portal-presensi-dashboard', [
            'selectedDate'           => $this->selectedDate,
            'academicYears'          => $this->academicYears,
            'selectedAcademicYearId' => $this->selectedAcademicYearId,
            'classes'                => $this->classes,
            'selectedClassFilter'    => $this->selectedClassFilter,
            'searchQuery'            => $this->searchQuery,
            'activeTab'              => $this->activeTab,
            'filterStatus'           => $this->filterStatus,
            'stats'                  => $this->stats,
            'classesSummary'         => $this->classesSummary,
            'unattendedStudents'     => $this->unattendedStudents,
            'allStudents'            => $this->allStudents,
        ])->title('Dashboard Rekapitulasi Presensi Harian');
    }
}
