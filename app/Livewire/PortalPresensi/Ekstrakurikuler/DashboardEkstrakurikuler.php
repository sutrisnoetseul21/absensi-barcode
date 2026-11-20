<?php

namespace App\Livewire\PortalPresensi\Ekstrakurikuler;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerPertemuan;
use App\Models\EkstrakurikulerPresensi;
use App\Models\EkstrakurikulerSiswa;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Models\TahunAjaran;
use App\Services\UnattendedEkstrakurikulerNotificationService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class DashboardEkstrakurikuler extends Component
{
    public $academicYears = [];
    public ?string $selectedAcademicYearId = null;
    public string $selectedDate = '';
    public string $search = '';

    // Pop-up modal daftar siswa belum hadir / alpa pada sesi pertemuan + kirim WA Ortu
    public bool $showWaPertemuanModal = false;
    public ?array $waPertemuanInfo = null;
    public array $waPertemuanStudents = [];

    public function mount(): void
    {
        abort_if(
            !(PengaturanSekolah::current()?->enable_ekstrakurikuler ?? true),
            403,
            'Fitur Ekstrakurikuler sedang dinonaktifkan.'
        );

        $this->selectedDate = Carbon::today('Asia/Jakarta')->toDateString();
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first()
            ?? (PengaturanSekolah::current()?->academic_year_id_active
                ? TahunAjaran::find(PengaturanSekolah::current()->academic_year_id_active)
                : null)
            ?? $this->academicYears->first();

        $this->selectedAcademicYearId = $activeYear?->id;
    }

    public function openWaPertemuanModal(string $pertemuanId): void
    {
        $pertemuan = EkstrakurikulerPertemuan::with('ekstrakurikuler.pembinas')->find($pertemuanId);
        if (!$pertemuan || !$pertemuan->ekstrakurikuler) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sesi pertemuan tidak ditemukan.']);
            return;
        }

        $ekskul = $pertemuan->ekstrakurikuler;
        $members = EkstrakurikulerSiswa::with([
                'siswa' => fn ($q) => $q->with([
                    'enrollments' => fn ($eq) => $eq->where('academic_year_id', $ekskul->academic_year_id)
                        ->where('status', 'aktif')
                        ->with('kelas'),
                ]),
            ])
            ->where('ekstrakurikuler_id', $ekskul->id)
            ->where('status_aktif', true)
            ->get()
            ->sortBy(fn ($m) => strtolower($m->siswa?->name ?? ''));

        $presensisByStudent = EkstrakurikulerPresensi::where('pertemuan_id', $pertemuan->id)
            ->get()
            ->keyBy('student_id');

        $waService = app(UnattendedEkstrakurikulerNotificationService::class);
        $studentsList = [];

        foreach ($members as $m) {
            $student = $m->siswa;
            if (!$student) {
                continue;
            }

            $pres = $presensisByStudent->get($student->id);
            $status = $pres?->status ?? 'belum';

            if (!in_array($status, ['belum', 'alpa'], true)) {
                continue;
            }

            $statusLabel = $status === 'alpa' ? 'Alpa (Ekstrakurikuler)' : 'Belum Absen Ekskul';
            $kelasModel = $student->enrollments->first()?->kelas
                ?? $student->resolveKelasModel($ekskul->academic_year_id);
            $kelasName = $kelasModel?->name ?? '-';
            $waliKelas = $kelasModel?->waliKelas($ekskul->academic_year_id);

            $waPayload = $waService->buildPersonalWaPayload(
                student: $student,
                ekskul: $ekskul,
                pertemuan: $pertemuan,
                kelasName: $kelasName,
                waliKelas: $waliKelas,
                statusKehadiran: $statusLabel
            );

            $studentsList[] = [
                'id'           => $student->id,
                'name'         => $student->name,
                'nisn'         => $student->nisn ?? '-',
                'kelas'        => $kelasName,
                'status_key'   => $status,
                'status_label' => $statusLabel,
                'phone'        => $waPayload['raw_phone'],
                'clean_phone'  => $waPayload['clean_phone'],
                'wa_text_raw'  => $waPayload['wa_text_raw'],
            ];
        }

        $this->waPertemuanInfo = [
            'pertemuan_id'   => $pertemuan->id,
            'ekskul_name'    => $ekskul->nama_ekskul,
            'pertemuan_ke'   => $pertemuan->pertemuan_ke,
            'judul_kegiatan' => $pertemuan->judul_kegiatan ?: 'Belum diisi pembina',
            'tanggal_label'  => $pertemuan->tanggal
                ? $pertemuan->tanggal->locale('id')->translatedFormat('l, d F Y')
                : '-',
        ];
        $this->waPertemuanStudents = $studentsList;
        $this->showWaPertemuanModal = true;
    }

    public function closeWaPertemuanModal(): void
    {
        $this->showWaPertemuanModal = false;
    }

    public function sendWaDirect(string $studentId, string $targetPhone, string $messageText, string $type = 'ekstrakurikuler'): void
    {
        $res = app(UnattendedEkstrakurikulerNotificationService::class)
            ->sendDirectMessage($studentId, $targetPhone, $messageText);

        $this->dispatch('notify', [
            'type'    => $res['type'],
            'message' => $res['message'],
        ]);

        if ($res['success']) {
            $this->dispatch('wa-modal-close');
        }
    }

    public function kirimWaBelumHadir(string $pertemuanId): void
    {
        $pertemuan = EkstrakurikulerPertemuan::with('ekstrakurikuler')->find($pertemuanId);
        if (!$pertemuan) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sesi pertemuan tidak ditemukan.']);
            return;
        }

        $result = app(UnattendedEkstrakurikulerNotificationService::class)
            ->dispatchForPertemuan($pertemuan, 'manual');

        $this->dispatch('notify', [
            'type'    => !$result['success'] ? 'warning' : ($result['dispatched'] > 0 ? 'success' : 'info'),
            'message' => $result['message'],
        ]);

        if (!empty($result['success'])) {
            $this->showWaPertemuanModal = false;
        }
    }

    public function render()
    {
        $dateFormatted = Carbon::parse($this->selectedDate ?: Carbon::today('Asia/Jakarta')->toDateString())
            ->locale('id')
            ->translatedFormat('l, d F Y');

        $waSetting = PresensiNotificationSetting::where('status_presensi', 'ekstrakurikuler')->first();
        $waEkskulActive = (bool) ($waSetting?->is_active ?? false);
        $canSendManualToday = $waSetting ? $waSetting->canSendManualToday() : true;
        $todayStr = Carbon::now('Asia/Jakarta')->toDateString();
        $manualSentCount = ($waSetting && $waSetting->manual_send_date && $waSetting->manual_send_date->toDateString() === $todayStr)
            ? (int) $waSetting->manual_send_count
            : 0;

        $ekskulsQuery = Ekstrakurikuler::with(['pembinas'])
            ->withCount(['anggotaAktif', 'pertemuans'])
            ->where('status_aktif', true)
            ->orderBy('nama_ekskul');

        if ($this->selectedAcademicYearId) {
            $ekskulsQuery->where('academic_year_id', $this->selectedAcademicYearId);
        }

        if (trim($this->search) !== '') {
            $q = '%' . trim($this->search) . '%';
            $ekskulsQuery->where('nama_ekskul', 'like', $q);
        }

        $ekskuls = $ekskulsQuery->get();

        $ekskulIds = $ekskuls->pluck('id');
        $datePertemuans = EkstrakurikulerPertemuan::whereIn('ekstrakurikuler_id', $ekskulIds)
            ->whereDate('tanggal', $this->selectedDate)
            ->get()
            ->keyBy('ekstrakurikuler_id');

        $pertemuanIds = $datePertemuans->pluck('id');

        // Hitung status presensi hanya untuk anggota aktif
        $activeMemberIdsByEkskul = EkstrakurikulerSiswa::whereIn('ekstrakurikuler_id', $ekskulIds)
            ->where('status_aktif', true)
            ->get(['ekstrakurikuler_id', 'student_id'])
            ->groupBy('ekstrakurikuler_id')
            ->map(fn ($rows) => $rows->pluck('student_id')->all());

        $presensisOnDate = $pertemuanIds->isNotEmpty()
            ? EkstrakurikulerPresensi::whereIn('pertemuan_id', $pertemuanIds)->get()->groupBy('pertemuan_id')
            : collect();

        $cards = [];
        $totalAnggotaSemua = 0;
        $totalSesiTanggal = 0;
        $totalHadirTanggal = 0;
        $totalBelumTanggal = 0;

        foreach ($ekskuls as $eks) {
            $activeIds = $activeMemberIdsByEkskul->get($eks->id, []);
            $memberCount = count($activeIds);
            $totalAnggotaSemua += $memberCount;

            $pertemuan = $datePertemuans->get($eks->id);
            $hadir = $izin = $sakit = $alpa = $belum = 0;

            if ($pertemuan) {
                $totalSesiTanggal++;
                $pRows = $presensisOnDate->get($pertemuan->id, collect())
                    ->whereIn('student_id', $activeIds);

                $hadir = $pRows->where('status', 'hadir')->count();
                $izin  = $pRows->where('status', 'izin')->count();
                $sakit = $pRows->where('status', 'sakit')->count();
                $alpa  = $pRows->where('status', 'alpa')->count();
                $belum = max(0, $memberCount - ($hadir + $izin + $sakit + $alpa));

                $totalHadirTanggal += $hadir;
                $totalBelumTanggal += ($belum + $alpa);
            }

            $cards[] = [
                'id'            => $eks->id,
                'nama_ekskul'   => $eks->nama_ekskul,
                'slug'          => $eks->slug,
                'pembinas'      => $eks->pembinas->pluck('name')->implode(', ') ?: '-',
                'anggota_count' => $memberCount,
                'total_sesi'    => (int) $eks->pertemuans_count,
                'pertemuan'     => $pertemuan,
                'hadir'         => $hadir,
                'izin'          => $izin,
                'sakit'         => $sakit,
                'alpa'          => $alpa,
                'belum'         => $belum,
            ];
        }

        // Riwayat scan terbaru pada tanggal terpilih
        $recentLogs = $pertemuanIds->isNotEmpty()
            ? EkstrakurikulerPresensi::with(['siswa', 'ekstrakurikuler', 'pertemuan'])
                ->whereIn('pertemuan_id', $pertemuanIds)
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get()
            : collect();

        return view('livewire.portal-presensi.ekstrakurikuler.dashboard-ekstrakurikuler', [
            'dateFormatted'      => $dateFormatted,
            'cards'              => $cards,
            'totalEkskul'        => $ekskuls->count(),
            'totalAnggotaSemua'  => $totalAnggotaSemua,
            'totalSesiTanggal'   => $totalSesiTanggal,
            'totalHadirTanggal'  => $totalHadirTanggal,
            'totalBelumTanggal'  => $totalBelumTanggal,
            'recentLogs'         => $recentLogs,
            'waEkskulActive'     => $waEkskulActive,
            'canSendManualToday' => $canSendManualToday,
            'manualSentCount'    => $manualSentCount,
        ])->title('Dashboard Presensi Ekstrakurikuler');
    }
}

