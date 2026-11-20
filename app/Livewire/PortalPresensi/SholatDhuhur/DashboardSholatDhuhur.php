<?php

namespace App\Livewire\PortalPresensi\SholatDhuhur;

use App\Exports\RekapSholatDhuhurHarianExport;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Models\TahunAjaran;
use App\Services\PresensiSholatDhuhurService;
use App\Services\UnattendedSholatDhuhurNotificationService;
use App\Services\WhatsAppGatewayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.portal')]
class DashboardSholatDhuhur extends Component
{
    use WithPagination;

    public $selectedDate;
    public $selectedAcademicYearId;
    public $selectedClassFilter = 'all';
    public $searchQuery = '';
    public $activeTab = 'per_kelas'; // 'per_kelas' | 'belum_sholat' | 'semua_siswa'
    public $filterStatus = 'all';
    public int $perPage = 25;

    protected ?array $cachedSummary = null;

    public function mount(): void
    {
        abort_if(!(PengaturanSekolah::current()->enable_sholat_dhuhur ?? true), 403, 'Fitur Sholat Dhuhur sedang dinonaktifkan.');

        $this->selectedDate = Carbon::today('Asia/Jakarta')->toDateString();

        $activeYear = TahunAjaran::where('status', 'aktif')->first()
            ?? TahunAjaran::orderBy('start_year', 'desc')->first();

        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }
    }

    public function updatedSelectedDate(): void
    {
        $this->cachedSummary = null;
        $this->resetPage();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->cachedSummary = null;
        $this->resetPage();
    }

    public function updatedSelectedClassFilter(): void { $this->resetPage(); }
    public function updatedSearchQuery(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function sinkronPresensiPagiSemuaKelas(): void
    {
        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $result = app(PresensiSholatDhuhurService::class)->syncAllClassesFromMorning($date, auth()->user()?->teacher?->id);
        $this->cachedSummary = null;

        $this->dispatch('notify', [
            'type'    => !empty($result['success']) ? 'success' : 'error',
            'message' => $result['message'] ?? 'Proses sinkronisasi selesai.',
        ]);
    }

    protected function getDefaultKoordinator(): ?Guru
    {
        $mapelPaiIds = MataPelajaran::where('nama_mapel', 'like', '%Agama Islam%')
            ->orWhere('kode_mapel', 'PAIBP')
            ->pluck('id');

        $paiTeacherId = Pengajaran::whereIn('mata_pelajaran_id', $mapelPaiIds)
            ->value('teacher_id');

        return $paiTeacherId ? Guru::find($paiTeacherId) : Guru::orderBy('name')->first();
    }

    protected function getSummary(): array
    {
        if ($this->cachedSummary !== null) {
            return $this->cachedSummary;
        }

        $date = $this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString();
        $academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $classes = Kelas::orderBy('name', 'asc')->get();

        if (!$this->selectedAcademicYearId) {
            return $this->cachedSummary = [
                'academicYears'      => $academicYears,
                'classes'            => $classes,
                'academic_year'      => null,
                'date'               => $date,
                'date_formatted'     => Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y'),
                'is_holiday'         => false,
                'holiday_desc'       => null,
                'stats'              => [],
                'classesSummary'     => [],
                'unattendedStudents' => [],
                'allStudents'        => [],
                'gradeGroups'        => [],
            ];
        }

        $summary = app(PresensiSholatDhuhurService::class)
            ->getDailySchoolSummaryData($this->selectedAcademicYearId, $date);

        $summary['academicYears'] = $academicYears;
        $summary['classes'] = $classes;

        return $this->cachedSummary = $summary;
    }

    protected function filterStudents(array $students, bool $withStatusFilter = false): array
    {
        $classFilter = $this->selectedClassFilter;
        $search = mb_strtolower(trim((string) $this->searchQuery));
        $statusFilter = $this->filterStatus;

        $filtered = [];
        foreach ($students as $st) {
            if ($classFilter !== 'all' && $st['class_id'] !== $classFilter) {
                continue;
            }
            if ($withStatusFilter && $statusFilter !== 'all' && $st['status'] !== $statusFilter) {
                continue;
            }
            if ($search !== '') {
                $haystack = mb_strtolower($st['name'] . ' ' . ($st['nisn'] ?? '') . ' ' . $st['class_name']);
                if (!str_contains($haystack, $search)) {
                    continue;
                }
            }
            $filtered[] = $st;
        }

        return $filtered;
    }

    protected function paginateStudents(array $items): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = max(10, min(100, (int) $this->perPage));
        $total = count($items);
        $slice = array_slice($items, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

    public function exportExcel()
    {
        $summary = $this->getSummary();
        $date = $summary['date'];
        $safeDate = str_replace('-', '_', $date);

        return Excel::download(
            new RekapSholatDhuhurHarianExport(
                $date,
                $this->selectedAcademicYearId,
                $summary['stats'],
                $summary['classesSummary'],
                $summary['unattendedStudents'],
                $summary['allStudents'],
                $this->getDefaultKoordinator()
            ),
            "Rekap_Sholat_Dhuhur_Harian_{$safeDate}.xlsx"
        );
    }

    public function downloadRekapKelasPdf()
    {
        $summary = $this->getSummary();
        $date = $summary['date'];
        $safeDate = str_replace('-', '_', $date);

        $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-rekap-per-kelas', [
            'stats'                 => $summary['stats'],
            'classesSummary'        => $summary['classesSummary'],
            'sekolah'               => PengaturanSekolah::current(),
            'koordinator'           => $this->getDefaultKoordinator(),
            'tahunAjaran'           => $summary['academic_year'],
            'selectedDateFormatted' => $summary['date_formatted'],
            'date'                  => $date,
            'isHoliday'             => $summary['is_holiday'],
            'holidayDesc'           => $summary['holiday_desc'],
            'unattendedStudents'    => $summary['unattendedStudents'],
            'allStudents'           => $summary['allStudents'],
            'generatedAt'           => now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB',
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Rekap_Sholat_Dhuhur_Per_Kelas_{$safeDate}.pdf"
        );
    }

    public function downloadRekapHarianPdf()
    {
        $summary = $this->getSummary();
        $date = $summary['date'];
        $safeDate = str_replace('-', '_', $date);

        $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-rekap-harian', [
            'stats'                 => $summary['stats'],
            'classesSummary'        => $summary['classesSummary'],
            'gradeGroups'           => $summary['gradeGroups'],
            'sekolah'               => PengaturanSekolah::current(),
            'koordinator'           => $this->getDefaultKoordinator(),
            'tahunAjaran'           => $summary['academic_year'],
            'selectedDateFormatted' => $summary['date_formatted'],
            'date'                  => $date,
            'isHoliday'             => $summary['is_holiday'],
            'holidayDesc'           => $summary['holiday_desc'],
            'unattendedStudents'    => $summary['unattendedStudents'],
            'allStudents'           => $summary['allStudents'],
            'generatedAt'           => now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB',
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Rekap_Presensi_Sholat_Dhuhur_Harian_{$safeDate}.pdf"
        );
    }

    public function sendWaDirect(string $studentId, string $targetPhone, string $messageText, string $type = 'sholat_dhuhur'): void
    {
        $notifSetting = PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->first();
        if (!$notifSetting || !$notifSetting->is_active) {
            $this->dispatch('notify', [
                'type'    => 'warning',
                'message' => 'Fitur notifikasi WA Gateway Sholat Dhuhur sedang dinonaktifkan di Portal Sistem. Gunakan "Buka di WA (Web / HP)".',
            ]);
            return;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (empty($cleanPhone)) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Nomor WhatsApp orang tua tidak valid atau masih kosong.',
            ]);
            return;
        }

        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }

        $sent = app(WhatsAppGatewayService::class)->sendMessage(
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
                'message' => 'Gagal mengirim via sistem gateway WhatsApp. Anda dapat menggunakan tombol "Buka di WA (Web / HP)" sebagai alternatif.',
            ]);
        }
    }

    public function kirimWaBelumSholatMassal(): void
    {
        $result = app(UnattendedSholatDhuhurNotificationService::class)->dispatch(source: 'manual');
        $this->dispatch('notify', [
            'type'    => empty($result['success']) ? 'warning' : (($result['dispatched'] ?? 0) > 0 ? 'success' : 'info'),
            'message' => $result['message'] ?? 'Proses pengiriman selesai.',
        ]);
    }

    public function render()
    {
        $summary = $this->getSummary();

        $paginatedStudents = match ($this->activeTab) {
            'belum_sholat' => $this->paginateStudents($this->filterStudents($summary['unattendedStudents'])),
            'semua_siswa'  => $this->paginateStudents($this->filterStudents($summary['allStudents'], withStatusFilter: true)),
            default        => null,
        };

        $dateLabel = $summary['date_formatted'];
        $senderName = auth()->user()?->teacher?->name ?? auth()->user()?->name ?? '-';
        if ($paginatedStudents) {
            $paginatedStudents->getCollection()->transform(function ($st) use ($dateLabel, $senderName) {
                $st['wa_text_raw'] = PresensiNotificationSetting::renderTemplate('sholat_dhuhur', [
                    'nama_siswa'       => $st['name'],
                    'kelas'            => $st['class_name'] ?? '-',
                    'tanggal'          => $dateLabel,
                    'status_kehadiran' => ($st['status'] === 'tidak_hadir' ? 'Tidak Hadir Sholat' : 'Belum Presensi Sholat'),
                    'nama_wali_kelas'  => $senderName,
                ]);
                return $st;
            });
        }

        $isNotifSholatActive = (bool) (PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->value('is_active') ?? false);

        return view('livewire.portal-presensi.sholat-dhuhur.dashboard-sholat-dhuhur', [
            'academicYears'       => $summary['academicYears'],
            'classes'             => $summary['classes'],
            'dateFormatted'       => $summary['date_formatted'],
            'isHoliday'           => $summary['is_holiday'],
            'holidayDesc'         => $summary['holiday_desc'],
            'stats'               => $summary['stats'],
            'classesSummary'      => $summary['classesSummary'],
            'unattendedCount'     => count($summary['unattendedStudents']),
            'paginatedStudents'   => $paginatedStudents,
            'isNotifSholatActive' => $isNotifSholatActive,
        ])->title('Dashboard Presensi Sholat Dhuhur - Portal Presensi');
    }
}

