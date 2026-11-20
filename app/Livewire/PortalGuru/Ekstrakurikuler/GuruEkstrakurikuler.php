<?php

namespace App\Livewire\PortalGuru\Ekstrakurikuler;

use App\Exports\EkstrakurikulerRekapExport;
use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerPertemuan;
use App\Models\EkstrakurikulerPresensi;
use App\Models\EkstrakurikulerSiswa;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Models\TahunAjaran;
use App\Services\EkstrakurikulerRekapService;
use App\Services\UnattendedEkstrakurikulerNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.portal')]
class GuruEkstrakurikuler extends Component
{
    public $academicYears = [];
    public ?string $selectedAcademicYearId = null;

    public $ekskuls = [];
    public ?string $selectedEkskulId = null;

    public $pertemuans = [];
    public array $pertemuanCards = [];
    public ?string $selectedPertemuanId = null;
    public ?string $activeJurnalPertemuanId = null;

    public string $activeTab = 'presensi'; // presensi | jurnal | anggota

    // Form edit jurnal pertemuan terpilih
    public string $judulKegiatan = '';
    public string $catatanPembina = '';
    public string $statusSesi = 'aktif';

    // Daftar baris presensi anggota pada pertemuan terpilih
    public array $presensiRows = [];

    // Rekap & matriks anggota ekstra
    public array $memberStats = [];
    public array $reportMeetings = [];
    public array $reportMembers = [];

    // Modal tambah pertemuan baru (tanggal bebas oleh pembina)
    public bool $showCreatePertemuanModal = false;
    public string $newTanggal = '';
    public string $newJudul = '';
    public string $newCatatan = '';

    public function mount(?string $slug = null): void
    {
        abort_if(
            !(PengaturanSekolah::current()?->enable_ekstrakurikuler ?? true),
            403,
            'Fitur Ekstrakurikuler sedang dinonaktifkan.'
        );

        $user = Auth::user();
        if (!$user || (!$user->teacher && !$user->isSuperAdmin() && !$user->hasRole('super_admin'))) {
            abort(403, 'Akses ditolak: Halaman ini khusus Guru Pembina Ekstrakurikuler atau Administrator.');
        }

        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $this->selectedAcademicYearId = PengaturanSekolah::current()?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->value('id')
            ?? $this->academicYears->first()?->id;

        if ($slug) {
            $target = Ekstrakurikuler::where('slug', $slug)->firstOrFail();
            $canViewAll = $user->isSuperAdmin()
                || $user->hasRole('super_admin')
                || ($user->teacher && $user->teacher->canAccessAllClasses());
            $isPembina = $user->teacher && $target->pembinas()->where('teachers.id', $user->teacher->id)->exists();
            abort_unless($canViewAll || $isPembina, 403, 'Anda bukan pembina ekstrakurikuler ini.');

            $this->selectedAcademicYearId = $target->academic_year_id;
            $this->selectedEkskulId = $target->id;
        }

        $this->newTanggal = Carbon::now('Asia/Jakarta')->toDateString();
        $this->loadEkskuls();
    }

    public function updatedSelectedPertemuanId(): void
    {
        $this->loadSelectedPertemuan();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->selectedEkskulId = null;
        $this->selectedPertemuanId = null;
        $this->activeJurnalPertemuanId = null;
        $this->loadEkskuls();
    }

    public function selectEkskul(string $ekskulId): void
    {
        $this->selectedEkskulId = $ekskulId;
        $this->selectedPertemuanId = null;
        $this->activeJurnalPertemuanId = null;
        $this->loadEkskulDetail();
    }

    public function selectPertemuan(string $pertemuanId): void
    {
        $this->selectedPertemuanId = $pertemuanId;
        $this->loadSelectedPertemuan();
    }

    public function openJurnalPertemuan(string $pertemuanId): void
    {
        $this->activeJurnalPertemuanId = $pertemuanId;
        $this->selectedPertemuanId = $pertemuanId;
        $this->loadSelectedPertemuan();
    }

    public function closeJurnalPertemuan(): void
    {
        $this->activeJurnalPertemuanId = null;
    }

    public function loadEkskuls(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->ekskuls = collect();
            return;
        }

        $user = Auth::user();
        $canViewAll = $user->isSuperAdmin()
            || $user->hasRole('super_admin')
            || ($user->teacher && $user->teacher->canAccessAllClasses());

        $query = Ekstrakurikuler::with(['pembinas', 'tahunAjaran'])
            ->withCount(['anggotaAktif', 'pertemuans'])
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->orderBy('nama_ekskul');

        if (!$canViewAll) {
            $teacherId = $user->teacher?->id;
            $query->whereHas('pembinas', fn ($q) => $q->where('teachers.id', $teacherId));
        }

        $this->ekskuls = $query->get();

        if ($this->ekskuls->isNotEmpty()) {
            $exists = $this->selectedEkskulId && $this->ekskuls->contains('id', $this->selectedEkskulId);
            if (!$exists) {
                $this->selectedEkskulId = $this->ekskuls->first()->id;
            }
            $this->loadEkskulDetail();
        } else {
            $this->selectedEkskulId = null;
            $this->pertemuans = collect();
            $this->pertemuanCards = [];
            $this->selectedPertemuanId = null;
            $this->activeJurnalPertemuanId = null;
            $this->presensiRows = [];
            $this->memberStats = [];
            $this->reportMeetings = [];
            $this->reportMembers = [];
        }
    }

    public function loadEkskulDetail(): void
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul) {
            $this->pertemuans = collect();
            $this->pertemuanCards = [];
            $this->activeJurnalPertemuanId = null;
            $this->presensiRows = [];
            $this->memberStats = [];
            $this->reportMeetings = [];
            $this->reportMembers = [];
            return;
        }

        $this->pertemuans = EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $ekskul->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('pertemuan_ke', 'desc')
            ->get();

        // Hitung ringkasan H/I/S/A/Belum berbasis anggota aktif & simpan sebagai plain array
        // agar nilainya tidak hilang saat Livewire melakukan re-hydrasi EloquentCollection.
        $activeStudentIds = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskul->id)
            ->where('status_aktif', true)
            ->pluck('student_id');
        $totalActiveMembers = $activeStudentIds->count();

        $presensiGrouped = collect();
        if ($this->pertemuans->isNotEmpty() && $totalActiveMembers > 0) {
            $presensiGrouped = EkstrakurikulerPresensi::whereIn('pertemuan_id', $this->pertemuans->pluck('id'))
                ->whereIn('student_id', $activeStudentIds)
                ->selectRaw('pertemuan_id, status, COUNT(DISTINCT student_id) as total')
                ->groupBy('pertemuan_id', 'status')
                ->get()
                ->groupBy('pertemuan_id');
        }

        $cards = [];
        foreach ($this->pertemuans as $p) {
            $counts = $presensiGrouped->get($p->id, collect())->pluck('total', 'status');
            $hadir = (int) ($counts['hadir'] ?? 0);
            $izin  = (int) ($counts['izin'] ?? 0);
            $sakit = (int) ($counts['sakit'] ?? 0);
            $alpa  = (int) ($counts['alpa'] ?? 0);
            $terisi = $hadir + $izin + $sakit + $alpa;

            $cards[] = [
                'id'             => $p->id,
                'pertemuan_ke'   => (int) $p->pertemuan_ke,
                'tanggal_label'  => $p->tanggal?->locale('id')->translatedFormat('l, d F Y') ?? '-',
                'judul_kegiatan' => $p->judul_kegiatan,
                'status_sesi'    => $p->status_sesi,
                'hadir_count'    => $hadir,
                'izin_count'     => $izin,
                'sakit_count'    => $sakit,
                'alpa_count'     => $alpa,
                'belum_count'    => max(0, $totalActiveMembers - $terisi),
            ];
        }
        $this->pertemuanCards = $cards;

        if ($this->activeJurnalPertemuanId && !$this->pertemuans->contains('id', $this->activeJurnalPertemuanId)) {
            $this->activeJurnalPertemuanId = null;
        }

        if ($this->pertemuans->isNotEmpty()) {
            $exists = $this->selectedPertemuanId && $this->pertemuans->contains('id', $this->selectedPertemuanId);
            if (!$exists) {
                $this->selectedPertemuanId = $this->pertemuans->first()->id;
            }
            $this->loadSelectedPertemuan();
        } else {
            $this->selectedPertemuanId = null;
            $this->judulKegiatan = '';
            $this->catatanPembina = '';
            $this->statusSesi = 'aktif';
            $this->presensiRows = [];
        }

        $this->buildMemberStats($ekskul);
        $reportData = app(EkstrakurikulerRekapService::class)->buildEkskulReportData($ekskul);
        $this->reportMeetings = $reportData['meetings'];
        $this->reportMembers = $reportData['members'];
    }

    public function loadSelectedPertemuan(): void
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul || !$this->selectedPertemuanId) {
            $this->presensiRows = [];
            return;
        }

        $pertemuan = EkstrakurikulerPertemuan::find($this->selectedPertemuanId);
        if (!$pertemuan || $pertemuan->ekstrakurikuler_id !== $ekskul->id) {
            $this->presensiRows = [];
            return;
        }

        $this->judulKegiatan = $pertemuan->judul_kegiatan ?? '';
        $this->catatanPembina = $pertemuan->catatan_pembina ?? '';
        $this->statusSesi = $pertemuan->status_sesi ?? 'aktif';

        $yearId = $ekskul->academic_year_id;
        $members = $ekskul->anggotaAktif()
            ->with([
                'siswa.enrollments' => fn ($q) => $q->where('academic_year_id', $yearId)
                    ->where('status', 'aktif')
                    ->with('kelas'),
            ])
            ->get()
            ->sortBy(fn ($a) => mb_strtolower($a->siswa?->name ?? ''))
            ->values();

        $existing = EkstrakurikulerPresensi::where('pertemuan_id', $pertemuan->id)
            ->get()
            ->keyBy('student_id');

        $ekskul->loadMissing('pembinas');
        $waNotifService = app(UnattendedEkstrakurikulerNotificationService::class);

        $rows = [];
        foreach ($members as $member) {
            $st = $member->siswa;
            if (!$st) continue;
            $att = $existing->get($st->id);
            $kelasName = $st->enrollments->first()?->kelas?->name ?? '-';
            $statusVal = $att ? $att->status : '';
            $statusLabel = $statusVal === 'alpa' ? 'Alpa Ekskul' : 'Belum Hadir Ekskul';
            $waPayload = $waNotifService->buildPersonalWaPayload($st, $ekskul, $pertemuan, $kelasName, null, $statusLabel);

            $rows[] = [
                'student_id'      => $st->id,
                'name'            => $st->name,
                'nisn'            => $st->nisn ?? '-',
                'kelas'           => $kelasName,
                'status'          => $statusVal,
                'scan_time'       => ($att && $att->scan_time) ? substr((string) $att->scan_time, 0, 5) : null,
                'is_manual_input' => $att ? (bool) $att->is_manual_input : null,
                'keterangan'      => $att?->keterangan ?? '',
                'raw_phone'       => $waPayload['raw_phone'],
                'clean_phone'     => $waPayload['clean_phone'],
                'wa_text_raw'     => $waPayload['wa_text_raw'],
            ];
        }

        $this->presensiRows = $rows;
    }

    private function buildMemberStats(Ekstrakurikuler $ekskul): void
    {
        $yearId = $ekskul->academic_year_id;
        $totalMeetings = $this->pertemuans->count();

        $members = $ekskul->anggotaAktif()
            ->with([
                'siswa.enrollments' => fn ($q) => $q->where('academic_year_id', $yearId)
                    ->where('status', 'aktif')
                    ->with('kelas'),
            ])
            ->get()
            ->sortBy(fn ($a) => mb_strtolower($a->siswa?->name ?? ''))
            ->values();

        $allPresensis = EkstrakurikulerPresensi::where('ekstrakurikuler_id', $ekskul->id)
            ->get()
            ->groupBy('student_id');

        $stats = [];
        foreach ($members as $m) {
            $st = $m->siswa;
            if (!$st) continue;
            $recs = $allPresensis->get($st->id, collect());
            $hadir = $recs->where('status', 'hadir')->count();
            $izin = $recs->where('status', 'izin')->count();
            $sakit = $recs->where('status', 'sakit')->count();
            $alpa = $recs->where('status', 'alpa')->count();

            $stats[] = [
                'name'           => $st->name,
                'nisn'           => $st->nisn ?? '-',
                'kelas'          => $st->enrollments->first()?->kelas?->name ?? '-',
                'tanggal_gabung' => $m->tanggal_gabung ? Carbon::parse($m->tanggal_gabung)->format('d M Y') : '-',
                'hadir'          => $hadir,
                'izin'           => $izin,
                'sakit'          => $sakit,
                'alpa'           => $alpa,
                'pct'            => $totalMeetings > 0 ? round(($hadir / $totalMeetings) * 100) : 0,
            ];
        }

        $this->memberStats = $stats;
    }

    public function openCreatePertemuanModal(): void
    {
        $this->newTanggal = Carbon::now('Asia/Jakarta')->toDateString();
        $this->newJudul = '';
        $this->newCatatan = '';
        $this->showCreatePertemuanModal = true;
    }

    public function createPertemuan(): void
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul) return;

        $this->validate([
            'newTanggal' => 'required|date',
            'newJudul'   => 'nullable|string|max:200',
            'newCatatan' => 'nullable|string|max:2000',
        ], [
            'newTanggal.required' => 'Tanggal pertemuan wajib diisi.',
        ]);

        $existsSameDate = EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $ekskul->id)
            ->whereDate('tanggal', $this->newTanggal)
            ->first();

        if ($existsSameDate) {
            $this->selectedPertemuanId = $existsSameDate->id;
            $this->showCreatePertemuanModal = false;
            $this->loadEkskulDetail();
            $this->dispatch('notify', [
                'type'    => 'warning',
                'message' => "Pertemuan pada tanggal {$this->newTanggal} sudah ada (Pertemuan ke-{$existsSameDate->pertemuan_ke}). Langsung dibuka untuk Anda edit.",
            ]);
            return;
        }

        $pertemuan = EkstrakurikulerPertemuan::findOrCreateForDate(
            $ekskul,
            $this->newTanggal,
            Auth::user(),
            trim($this->newJudul)
        );
        if (filled($this->newCatatan)) {
            $pertemuan->update(['catatan_pembina' => trim($this->newCatatan)]);
        }

        $this->selectedPertemuanId = $pertemuan->id;
        $this->showCreatePertemuanModal = false;
        $this->loadEkskulDetail();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => "Pertemuan ke-{$pertemuan->pertemuan_ke} berhasil dibuat.",
        ]);
    }

    public function savePertemuanJurnal(): void
    {
        if (!$this->selectedPertemuanId) return;
        $pertemuan = EkstrakurikulerPertemuan::find($this->selectedPertemuanId);
        if (!$pertemuan) return;

        $pertemuan->update([
            'judul_kegiatan'  => trim($this->judulKegiatan) ?: null,
            'catatan_pembina' => trim($this->catatanPembina) ?: null,
            'status_sesi'     => in_array($this->statusSesi, ['aktif', 'selesai'], true) ? $this->statusSesi : 'aktif',
        ]);

        $this->loadEkskulDetail();
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => "Jurnal Pertemuan ke-{$pertemuan->pertemuan_ke} berhasil disimpan.",
        ]);
    }

    public function markUnfilledAs(string $status): void
    {
        if (!in_array($status, ['hadir', 'izin', 'sakit', 'alpa'], true)) return;

        foreach ($this->presensiRows as $i => $row) {
            if (empty($row['status'])) {
                $this->presensiRows[$i]['status'] = $status;
            }
        }
    }

    public function saveMeetingPresensi(): void
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul || !$this->selectedPertemuanId) return;

        $pertemuan = EkstrakurikulerPertemuan::find($this->selectedPertemuanId);
        if (!$pertemuan || $pertemuan->ekstrakurikuler_id !== $ekskul->id) return;

        $user = Auth::user();
        $isGuru = $user?->teacher !== null;
        $actor = $isGuru ? $user->teacher : $user;
        $now = Carbon::now('Asia/Jakarta');

        DB::transaction(function () use ($pertemuan, $ekskul, $user, $isGuru, $actor, $now) {
            // Simpan jurnal pertemuan sekalian bila diisi
            $pertemuan->update([
                'judul_kegiatan'  => trim($this->judulKegiatan) ?: null,
                'catatan_pembina' => trim($this->catatanPembina) ?: null,
                'status_sesi'     => in_array($this->statusSesi, ['aktif', 'selesai'], true) ? $this->statusSesi : 'aktif',
            ]);

            foreach ($this->presensiRows as $row) {
                $status = $row['status'] ?? '';
                if (empty($status) || !in_array($status, ['hadir', 'izin', 'sakit', 'alpa'], true)) {
                    continue;
                }

                $existing = EkstrakurikulerPresensi::where('pertemuan_id', $pertemuan->id)
                    ->where('student_id', $row['student_id'])
                    ->first();

                // Jika hasil scan kiosk hadir dan guru tidak mengganti status, pertahankan scan_time & flag kiosk
                $isKioskKeep = ($existing && $existing->is_manual_input === false && $existing->status === $status && ($existing->keterangan ?? '') === ($row['keterangan'] ?? ''));
                if ($isKioskKeep) {
                    continue;
                }

                $ket = trim((string) ($row['keterangan'] ?? ''));

                EkstrakurikulerPresensi::updateOrCreate(
                    [
                        'pertemuan_id' => $pertemuan->id,
                        'student_id'   => $row['student_id'],
                    ],
                    [
                        'ekstrakurikuler_id'   => $ekskul->id,
                        'status'               => $status,
                        'scan_time'            => ($status === 'hadir')
                            ? ($existing?->scan_time ?: $now->format('H:i:s'))
                            : null,
                        'is_manual_input'      => true,
                        'manual_input_by_id'   => $actor?->id,
                        'manual_input_by_type' => $isGuru ? \App\Models\Guru::class : \App\Models\User::class,
                        'manual_input_at'      => $now,
                        'manual_input_user_id' => $user?->id,
                        'keterangan'           => $ket !== '' ? $ket : 'Input Manual Pembina',
                    ]
                );
            }
        });

        $this->loadEkskulDetail();
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => "Presensi & jurnal Pertemuan ke-{$pertemuan->pertemuan_ke} berhasil disimpan!",
        ]);
    }

    public function deletePertemuan(string $pertemuanId): void
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul) return;

        $pertemuan = EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $ekskul->id)->find($pertemuanId);
        if (!$pertemuan) return;

        $pertemuan->delete();
        if ($this->selectedPertemuanId === $pertemuanId) {
            $this->selectedPertemuanId = null;
        }
        if ($this->activeJurnalPertemuanId === $pertemuanId) {
            $this->activeJurnalPertemuanId = null;
        }
        $this->loadEkskulDetail();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Pertemuan berhasil dihapus.',
        ]);
    }

    public function getSelectedEkskul(): ?Ekstrakurikuler
    {
        if (!$this->selectedEkskulId) {
            return null;
        }

        return collect($this->ekskuls)->firstWhere('id', $this->selectedEkskulId);
    }

    public function downloadExcel()
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul) return;

        $reportData = app(EkstrakurikulerRekapService::class)->buildEkskulReportData($ekskul);
        $safeName = str_replace([' ', '/', '\\'], '_', $ekskul->nama_ekskul);

        return Excel::download(
            new EkstrakurikulerRekapExport($reportData, 'Semua Pertemuan'),
            "Laporan_Ekstrakurikuler_{$safeName}.xlsx"
        );
    }

    public function downloadPdf()
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul) return;

        $reportData = app(EkstrakurikulerRekapService::class)->buildEkskulReportData($ekskul);
        $sekolah = PengaturanSekolah::current();

        $pdf = Pdf::loadView('pdf.laporan-ekstrakurikuler', [
            'ekskul'       => $reportData['ekskul'],
            'meetings'     => $reportData['meetings'],
            'members'      => $reportData['members'],
            'summary'      => $reportData['summary'],
            'periodeLabel' => 'Semua Pertemuan',
            'sekolah'      => $sekolah,
        ])->setPaper('a4', 'landscape');

        $safeName = str_replace([' ', '/', '\\'], '_', $ekskul->nama_ekskul);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Laporan_Ekstrakurikuler_{$safeName}.pdf"
        );
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

    public function render()
    {
        $isNotifEkskulActive = (bool) (PresensiNotificationSetting::where('status_presensi', 'ekstrakurikuler')->value('is_active') ?? false);

        return view('livewire.portal-guru.ekstrakurikuler.guru-ekstrakurikuler', [
            'selectedEkskul'        => $this->getSelectedEkskul(),
            'activeJurnalCard'      => $this->activeJurnalPertemuanId
                ? collect($this->pertemuanCards)->firstWhere('id', $this->activeJurnalPertemuanId)
                : null,
            'selectedPertemuanCard' => $this->selectedPertemuanId
                ? collect($this->pertemuanCards)->firstWhere('id', $this->selectedPertemuanId)
                : null,
            'isNotifEkskulActive'   => $isNotifEkskulActive,
        ])->title('Ekstrakurikuler - Portal Guru');
    }
}

