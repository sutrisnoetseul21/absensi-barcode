<?php

namespace App\Livewire\PortalGuru\JurnalMengajar;

use App\Exports\JurnalMengajarExport;
use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.portal')]
class JurnalMengajarList extends Component
{
    use WithPagination;

    public bool $isSupervisor = false;
    public ?string $currentTeacherId = null;
    public ?string $currentTeacherName = '';

    // Tab Navigasi: 'riwayat' | 'analisa'
    public string $activeTab = 'riwayat';
    public bool $onlyAbsentStudents = true;
    public string $studentSearch = '';

    // Filters
    public $academicYears = [];
    public ?string $selectedAcademicYearId = null;

    public $teachers = [];
    public ?string $selectedTeacherId = null;

    public $mapels = [];
    public ?int $selectedMapelId = null;

    public $classes = [];
    public ?string $selectedClassId = null;

    public string $search = '';
    public ?string $selectedMonthYear = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    // Modal Cetak Laporan (Model Akademik)
    public bool $showCetakModal = false;
    public string $cetakJenis = 'bulanan'; // bulanan, semester, tahunan, kustom
    public string $cetakBulanYear = '';
    public string $cetakSemester = 'ganjil'; // ganjil, genap
    public ?string $cetakClassId = null;
    public ?int $cetakMapelId = null;
    public ?string $cetakTeacherId = null;
    public ?string $cetakDateFrom = null;
    public ?string $cetakDateTo = null;
    public array $availableMonths = [];
    public $cetakAvailableMapels = [];
    public $cetakAvailableClasses = [];

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Akses ditolak: Silakan login terlebih dahulu.');
        }

        $this->isSupervisor = $user->isSuperAdmin() || $user->hasPermissionTo('portal_guru:supervisi_jurnal');

        if (!$this->isSupervisor && !$user->teacher) {
            abort(403, 'Akses ditolak: Anda tidak memiliki profil guru.');
        }

        $this->currentTeacherId = $user->teacher?->id;
        $this->currentTeacherName = $user->teacher?->name ?? $user->name;

        // Tahun Ajaran
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $activeYear = PengaturanSekolah::current()?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->first()?->id
            ?? $this->academicYears->first()?->id;
        $this->selectedAcademicYearId = $activeYear;

        if ($this->isSupervisor) {
            $this->teachers = Guru::orderBy('name', 'asc')->get();
            $this->selectedTeacherId = $this->currentTeacherId ?? $this->teachers->first()?->id;
        } else {
            $this->selectedTeacherId = $this->currentTeacherId;
        }

        $this->generateAvailableMonths();
        $this->loadMapelAndClasses();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['riwayat', 'analisa'], true) ? $tab : 'riwayat';
    }

    public function selectAnalisaClass(?string $classId): void
    {
        $this->selectedClassId = ($classId !== null && $classId !== '') ? $classId : null;
        $this->resetPage();
    }

    public function generateAvailableMonths(): void
    {
        $this->availableMonths = [];
        if (!$this->selectedAcademicYearId) {
            return;
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if ($ay) {
            $sy = $ay->start_year ?: (int) date('Y');
            $ey = $ay->end_year ?: ($sy + 1);

            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];

            // Juli - Desember (Start Year)
            for ($m = 7; $m <= 12; $m++) {
                $key = sprintf('%02d-%d', $m, $sy);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $sy;
            }
            // Januari - Juni (End Year)
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $ey;
            }
        }

        $currentMonthYear = date('m-Y');
        if (array_key_exists($currentMonthYear, $this->availableMonths)) {
            $this->cetakBulanYear = $currentMonthYear;
        } else {
            $this->cetakBulanYear = array_key_first($this->availableMonths) ?? $currentMonthYear;
        }

        if (!$this->selectedMonthYear) {
            $this->selectedMonthYear = array_key_exists($currentMonthYear, $this->availableMonths)
                ? $currentMonthYear
                : (array_key_first($this->availableMonths) ?? $currentMonthYear);
        }
    }

    public function openCetakModal(): void
    {
        $this->generateAvailableMonths();
        $this->cetakJenis = 'bulanan';
        $this->cetakSemester = (int) date('m') >= 7 ? 'ganjil' : 'genap';

        if ($this->isSupervisor) {
            $this->cetakTeacherId = $this->selectedTeacherId ?? $this->currentTeacherId ?? $this->teachers->first()?->id;
        } else {
            $this->cetakTeacherId = $this->currentTeacherId;
        }

        $this->cetakMapelId = $this->selectedMapelId;
        $this->loadCetakMapelAndClasses();

        if ($this->selectedClassId && collect($this->cetakAvailableClasses)->contains('id', $this->selectedClassId)) {
            $this->cetakClassId = $this->selectedClassId;
        } else {
            $this->cetakClassId = null;
        }

        $this->cetakDateFrom = $this->dateFrom;
        $this->cetakDateTo = $this->dateTo;
        $this->showCetakModal = true;
    }

    public function loadCetakMapelAndClasses(): void
    {
        if (!$this->cetakTeacherId || !$this->selectedAcademicYearId) {
            $this->cetakAvailableMapels = [];
            $this->cetakAvailableClasses = [];
            return;
        }

        $pengajarans = Pengajaran::where('teacher_id', $this->cetakTeacherId)
            ->whereHas('kelasAjaran', fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->with(['mataPelajaran', 'kelasAjaran.kelas'])
            ->get();

        $this->cetakAvailableMapels = $pengajarans->pluck('mataPelajaran')->unique('id')->filter()->values();

        if ($this->cetakAvailableMapels->count() === 1) {
            $this->cetakMapelId = $this->cetakAvailableMapels->first()->id;
        }

        $this->loadCetakClasses();
    }

    public function loadCetakClasses(): void
    {
        if (!$this->cetakTeacherId || !$this->selectedAcademicYearId || !$this->cetakMapelId) {
            $this->cetakAvailableClasses = [];
            $this->cetakClassId = null;
            return;
        }

        $pengajarans = Pengajaran::where('teacher_id', $this->cetakTeacherId)
            ->where('mata_pelajaran_id', $this->cetakMapelId)
            ->whereHas('kelasAjaran', fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->with('kelasAjaran.kelas')
            ->get();

        $this->cetakAvailableClasses = $pengajarans->pluck('kelasAjaran.kelas')->unique('id')->filter()->values();
    }

    public function updatedCetakTeacherId(): void
    {
        $this->cetakMapelId = null;
        $this->cetakClassId = null;
        $this->loadCetakMapelAndClasses();
    }

    public function updatedCetakMapelId(): void
    {
        $this->cetakClassId = null;
        $this->loadCetakClasses();
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->reset(['selectedMapelId', 'selectedClassId']);
        $this->generateAvailableMonths();
        $this->loadMapelAndClasses();
        $this->resetPage();
    }

    public function updatedSelectedTeacherId()
    {
        $this->reset(['selectedMapelId', 'selectedClassId']);
        $this->loadMapelAndClasses();
        $this->resetPage();
    }

    public function updatedSelectedMapelId()
    {
        $this->reset(['selectedClassId']);
        $this->loadClassesForSelectedMapel();
        $this->resetPage();
    }

    public function updatedSelectedClassId()
    {
        $this->resetPage();
    }

    public function updatedSelectedMonthYear()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function loadMapelAndClasses()
    {
        if (!$this->selectedTeacherId || !$this->selectedAcademicYearId) {
            $this->mapels = [];
            $this->classes = [];
            return;
        }

        $pengajarans = Pengajaran::where('teacher_id', $this->selectedTeacherId)
            ->whereHas('kelasAjaran', fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->with(['mataPelajaran', 'kelasAjaran.kelas'])
            ->get();

        $this->mapels = $pengajarans->pluck('mataPelajaran')->unique('id')->filter()->values();

        if ($this->mapels->count() === 1) {
            $this->selectedMapelId = $this->mapels->first()->id;
            $this->loadClassesForSelectedMapel();
        } else {
            $this->loadClassesForSelectedMapel();
        }
    }

    public function loadClassesForSelectedMapel()
    {
        if (!$this->selectedTeacherId || !$this->selectedAcademicYearId) {
            $this->classes = [];
            $this->selectedClassId = null;
            return;
        }

        $query = Pengajaran::where('teacher_id', $this->selectedTeacherId)
            ->when($this->selectedMapelId, fn($q) => $q->where('mata_pelajaran_id', $this->selectedMapelId))
            ->whereHas('kelasAjaran', fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId));

        $pengajarans = $query->with('kelasAjaran.kelas')->get();
        $this->classes = $pengajarans->pluck('kelasAjaran.kelas')->unique('id')->filter()->sortBy('name')->values();
    }

    public function delete(string $id)
    {
        $jurnal = JurnalPembelajaran::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $jurnal->teacher_id !== $this->currentTeacherId) {
            session()->flash('error', 'Anda tidak memiliki hak untuk menghapus jurnal guru lain.');
            return;
        }

        $jurnal->delete();
        session()->flash('success', 'Jurnal mengajar berhasil dihapus.');
    }

    /**
     * Hitung rentang tanggal & label cetak modal.
     */
    public function getCetakDateRange(): array
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sy = $ay?->start_year ?: (int) date('Y');
        $ey = $ay?->end_year ?: ($sy + 1);

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $m = (int) ($parts[0] ?? date('m'));
            $y = (int) ($parts[1] ?? date('Y'));
            $start = Carbon::create($y, $m, 1)->startOfMonth();
            $end = Carbon::create($y, $m, 1)->endOfMonth();
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $label = 'Bulan ' . ($monthNames[$m] ?? $m) . ' ' . $y;
        } elseif ($this->cetakJenis === 'semester') {
            if ($this->cetakSemester === 'ganjil') {
                $start = Carbon::create($sy, 7, 1)->startOfMonth();
                $end = Carbon::create($sy, 12, 31)->endOfMonth();
                $label = 'Semester Ganjil TA ' . ($ay?->name ?? ($sy . '/' . $ey));
            } else {
                $start = Carbon::create($ey, 1, 1)->startOfMonth();
                $end = Carbon::create($ey, 6, 30)->endOfMonth();
                $label = 'Semester Genap TA ' . ($ay?->name ?? ($sy . '/' . $ey));
            }
        } elseif ($this->cetakJenis === 'tahunan') {
            $start = Carbon::create($sy, 7, 1)->startOfMonth();
            $end = Carbon::create($ey, 6, 30)->endOfMonth();
            $label = 'Tahun Ajaran ' . ($ay?->name ?? ($sy . '/' . $ey));
        } else {
            $start = $this->cetakDateFrom ? Carbon::parse($this->cetakDateFrom) : null;
            $end = $this->cetakDateTo ? Carbon::parse($this->cetakDateTo) : null;
            $label = ($start ? $start->format('d/m/Y') : 'Awal') . ' s.d. ' . ($end ? $end->format('d/m/Y') : 'Sekarang');
        }

        return [
            'start' => $start?->toDateString(),
            'end' => $end?->toDateString(),
            'label' => $label,
        ];
    }

    public function downloadCetakPdf()
    {
        $range = $this->getCetakDateRange();
        $teacherId = $this->cetakTeacherId ?: $this->selectedTeacherId;
        $classId = $this->cetakClassId;
        $mapelId = $this->cetakMapelId;
        $academicYearId = $this->selectedAcademicYearId;

        $guru = $teacherId ? Guru::find($teacherId) : null;
        $tahunAjaran = $academicYearId ? TahunAjaran::find($academicYearId) : null;
        $mapel = $mapelId ? MataPelajaran::find($mapelId) : null;
        $kelas = $classId ? Kelas::find($classId) : null;
        $sekolah = PengaturanSekolah::current();

        $jurnals = JurnalPembelajaran::query()
            ->with(['guru', 'kelas', 'mataPelajaran'])
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($teacherId, fn($q) => $q->where('teacher_id', $teacherId))
            ->when($mapelId, fn($q) => $q->where('mata_pelajaran_id', $mapelId))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($range['start'], fn($q) => $q->whereDate('tanggal', '>=', $range['start']))
            ->when($range['end'], fn($q) => $q->whereDate('tanggal', '<=', $range['end']))
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdf.jurnal-mengajar-rekap', [
            'jurnals' => $jurnals,
            'guru' => $guru,
            'tahunAjaran' => $tahunAjaran,
            'mapel' => $mapel,
            'kelas' => $kelas,
            'sekolah' => $sekolah,
            'dateFrom' => $range['start'],
            'dateTo' => $range['end'],
            'periodeLabel' => $range['label'],
        ])->setPaper('a4', 'landscape');

        $parts = ['Jurnal_Mengajar'];
        if ($guru) {
            $parts[] = str_replace([' ', '/', '\\'], '_', $guru->name);
        }
        if ($kelas) {
            $parts[] = str_replace([' ', '/', '\\'], '_', $kelas->name);
        }
        $parts[] = str_replace([' ', '/', '\\'], '_', $range['label']);
        $fileName = implode('_', $parts) . '.pdf';

        $this->showCetakModal = false;

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $fileName
        );
    }

    public function downloadCetakExcel()
    {
        $range = $this->getCetakDateRange();
        $teacherId = $this->cetakTeacherId ?: $this->selectedTeacherId;
        $classId = $this->cetakClassId;
        $mapelId = $this->cetakMapelId;
        $academicYearId = $this->selectedAcademicYearId;

        $guru = $teacherId ? Guru::find($teacherId) : null;
        $tahunAjaran = $academicYearId ? TahunAjaran::find($academicYearId) : null;
        $mapel = $mapelId ? MataPelajaran::find($mapelId) : null;
        $kelas = $classId ? Kelas::find($classId) : null;
        $sekolah = PengaturanSekolah::current();

        $jurnals = JurnalPembelajaran::query()
            ->with(['guru', 'kelas', 'mataPelajaran'])
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($teacherId, fn($q) => $q->where('teacher_id', $teacherId))
            ->when($mapelId, fn($q) => $q->where('mata_pelajaran_id', $mapelId))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($range['start'], fn($q) => $q->whereDate('tanggal', '>=', $range['start']))
            ->when($range['end'], fn($q) => $q->whereDate('tanggal', '<=', $range['end']))
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $parts = ['Jurnal_Mengajar'];
        if ($guru) {
            $parts[] = str_replace([' ', '/', '\\'], '_', $guru->name);
        }
        if ($kelas) {
            $parts[] = str_replace([' ', '/', '\\'], '_', $kelas->name);
        }
        $parts[] = str_replace([' ', '/', '\\'], '_', $range['label']);
        $fileName = implode('_', $parts) . '.xlsx';

        $this->showCetakModal = false;

        return Excel::download(
            new JurnalMengajarExport(
                $jurnals,
                $guru,
                $tahunAjaran,
                $mapel,
                $kelas,
                $sekolah,
                $range['label']
            ),
            $fileName
        );
    }

    /**
     * Bangun data Analisa Kelas & Kehadiran Siswa untuk Tab 2 di Web Portal Guru.
     */
    protected function buildAnalyticsData(): array
    {
        $analyticsJurnals = JurnalPembelajaran::query()
            ->with(['kelas:id,name,grade', 'mataPelajaran:id,nama_mapel', 'presensis.siswa:id,name,nis,nisn'])
            ->when($this->selectedAcademicYearId, fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->when($this->selectedTeacherId, fn($q) => $q->where('teacher_id', $this->selectedTeacherId))
            ->when($this->selectedMapelId, fn($q) => $q->where('mata_pelajaran_id', $this->selectedMapelId))
            ->when($this->selectedMonthYear, function ($q) {
                $parts = explode('-', $this->selectedMonthYear);
                if (count($parts) === 2) {
                    $q->whereMonth('tanggal', (int) $parts[0])
                        ->whereYear('tanggal', (int) $parts[1]);
                }
            })
            ->when(!$this->selectedMonthYear && $this->dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $this->dateFrom))
            ->when(!$this->selectedMonthYear && $this->dateTo, fn($q) => $q->whereDate('tanggal', '<=', $this->dateTo))
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $overallTotals = [
            'total_sesi' => 0,
            'total_jp' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'izin_sakit' => 0,
            'alpa' => 0,
            'tidak_mengikuti' => 0,
            'total_absen' => 0,
        ];

        $classesMap = [];
        foreach ($this->classes as $tc) {
            $cid = (string) ($tc->id ?? '');
            if ($cid !== '' && !isset($classesMap[$cid])) {
                $classesMap[$cid] = [
                    'class_id' => $cid,
                    'class_name' => $tc->name ?? '-',
                    'total_sesi' => 0,
                    'total_jp' => 0,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'izin_sakit' => 0,
                    'alpa' => 0,
                    'tidak_mengikuti' => 0,
                    'total_absen' => 0,
                    'siswa_pernah_absen_count' => 0,
                ];
            }
        }

        $studentsMap = [];

        foreach ($analyticsJurnals as $jurnal) {
            $cid = (string) $jurnal->class_id;
            $cname = $jurnal->kelas?->name ?? '-';
            $jp = (int) $jurnal->durasi_jp;
            $tanggalFormatted = $jurnal->tanggal ? hari_tanggal_indonesia($jurnal->tanggal) : '-';
            $jamKeLabel = $jurnal->jam_ke_label ?: "Jam ke {$jurnal->jam_mulai} ({$jp} JP)";
            $mapelName = $jurnal->mataPelajaran?->nama_mapel ?? '-';

            $overallTotals['total_sesi']++;
            $overallTotals['total_jp'] += $jp;

            if (!isset($classesMap[$cid])) {
                $classesMap[$cid] = [
                    'class_id' => $cid,
                    'class_name' => $cname,
                    'total_sesi' => 0,
                    'total_jp' => 0,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'izin_sakit' => 0,
                    'alpa' => 0,
                    'tidak_mengikuti' => 0,
                    'total_absen' => 0,
                    'siswa_pernah_absen_count' => 0,
                ];
            }

            $classesMap[$cid]['total_sesi']++;
            $classesMap[$cid]['total_jp'] += $jp;

            foreach ($jurnal->presensis as $p) {
                $siswa = $p->siswa;
                $sid = (string) $p->student_id;
                if ($sid === '') {
                    continue;
                }

                $statusJurnal = strtolower((string) $p->status_jurnal);
                $statusGerbang = strtolower((string) ($p->status_gerbang_snapshot ?? ''));

                if ($statusJurnal === 'tidak_mengikuti') {
                    if ($statusGerbang === 'sakit') {
                        $normStatus = 'sakit';
                        $statusLabel = 'Sakit';
                    } elseif ($statusGerbang === 'izin') {
                        $normStatus = 'izin';
                        $statusLabel = 'Izin';
                    } elseif ($statusGerbang === 'alpa') {
                        $normStatus = 'alpa';
                        $statusLabel = 'Alpa';
                    } else {
                        $normStatus = 'tidak_mengikuti';
                        $statusLabel = 'Tidak Mengikuti';
                    }
                } else {
                    $normStatus = 'hadir';
                    $statusLabel = 'Hadir';
                }

                $overallTotals[$normStatus]++;
                $classesMap[$cid][$normStatus]++;

                $studentKey = $cid . '_' . $sid;
                if (!isset($studentsMap[$studentKey])) {
                    $studentsMap[$studentKey] = [
                        'student_id' => $sid,
                        'student_name' => $siswa?->name ?? 'Siswa',
                        'nis' => $siswa?->nis ?? $siswa?->nisn ?? '-',
                        'class_id' => $cid,
                        'class_name' => $cname,
                        'total_pertemuan' => 0,
                        'hadir' => 0,
                        'izin' => 0,
                        'sakit' => 0,
                        'izin_sakit' => 0,
                        'alpa' => 0,
                        'tidak_mengikuti' => 0,
                        'total_absen' => 0,
                        'absence_logs' => [],
                    ];
                }

                $studentsMap[$studentKey]['total_pertemuan']++;
                $studentsMap[$studentKey][$normStatus]++;

                if ($normStatus !== 'hadir') {
                    $studentsMap[$studentKey]['absence_logs'][] = [
                        'jurnal_id' => (string) $jurnal->id,
                        'tanggal_formatted' => $tanggalFormatted,
                        'jam_ke_label' => $jamKeLabel,
                        'mata_pelajaran_name' => $mapelName,
                        'materi' => $jurnal->materi ?? '-',
                        'status' => $normStatus,
                        'status_label' => $statusLabel,
                        'keterangan' => $p->keterangan ?? '',
                    ];
                }
            }
        }

        $overallTotals['izin_sakit'] = $overallTotals['izin'] + $overallTotals['sakit'];
        $overallTotals['total_absen'] = $overallTotals['izin_sakit'] + $overallTotals['alpa'] + $overallTotals['tidak_mengikuti'];

        $studentsList = array_values($studentsMap);
        foreach ($studentsList as &$st) {
            $st['izin_sakit'] = $st['izin'] + $st['sakit'];
            $st['total_absen'] = $st['izin_sakit'] + $st['alpa'] + $st['tidak_mengikuti'];
            if ($st['total_absen'] > 0 && isset($classesMap[$st['class_id']])) {
                $classesMap[$st['class_id']]['siswa_pernah_absen_count']++;
            }
        }
        unset($st);

        foreach ($classesMap as &$cm) {
            $cm['izin_sakit'] = $cm['izin'] + $cm['sakit'];
            $cm['total_absen'] = $cm['izin_sakit'] + $cm['alpa'] + $cm['tidak_mengikuti'];
        }
        unset($cm);

        usort($studentsList, function (array $a, array $b) {
            if ($a['tidak_mengikuti'] !== $b['tidak_mengikuti']) {
                return $b['tidak_mengikuti'] <=> $a['tidak_mengikuti'];
            }
            if ($a['alpa'] !== $b['alpa']) {
                return $b['alpa'] <=> $a['alpa'];
            }
            if ($a['total_absen'] !== $b['total_absen']) {
                return $b['total_absen'] <=> $a['total_absen'];
            }
            return strcasecmp($a['student_name'], $b['student_name']);
        });

        $classesList = array_values($classesMap);
        usort($classesList, fn(array $a, array $b) => strnatcasecmp($a['class_name'], $b['class_name']));

        return [
            'totals' => $overallTotals,
            'classes_summary' => $classesList,
            'students_summary' => $studentsList,
        ];
    }

    public function render()
    {
        $query = JurnalPembelajaran::query()
            ->with(['guru', 'kelas', 'mataPelajaran', 'tahunAjaran'])
            ->when($this->selectedAcademicYearId, fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->when($this->selectedTeacherId, fn($q) => $q->where('teacher_id', $this->selectedTeacherId))
            ->when($this->selectedMapelId, fn($q) => $q->where('mata_pelajaran_id', $this->selectedMapelId))
            ->when($this->selectedClassId, fn($q) => $q->where('class_id', $this->selectedClassId))
            ->when($this->selectedMonthYear, function ($q) {
                $parts = explode('-', $this->selectedMonthYear);
                if (count($parts) === 2) {
                    $q->whereMonth('tanggal', (int) $parts[0])
                        ->whereYear('tanggal', (int) $parts[1]);
                }
            })
            ->when(!$this->selectedMonthYear && $this->dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $this->dateFrom))
            ->when(!$this->selectedMonthYear && $this->dateTo, fn($q) => $q->whereDate('tanggal', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('materi', 'like', '%' . $this->search . '%')
                        ->orWhere('uraian_materi', 'like', '%' . $this->search . '%')
                        ->orWhere('refleksi', 'like', '%' . $this->search . '%')
                        ->orWhere('jam_ke_label', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc');

        $statsQuery = clone $query;
        $totalJurnal = $statsQuery->count();
        $totalJp = (clone $statsQuery)->sum('durasi_jp');
        $totalSiswaTercatat = (clone $statsQuery)->sum('total_siswa');
        $totalSiswaHadir = (clone $statsQuery)->sum('total_hadir');
        $avgKehadiran = $totalSiswaTercatat > 0 ? round(($totalSiswaHadir / $totalSiswaTercatat) * 100, 1) : 0;

        $jurnals = $query->paginate(10);
        $analytics = $this->buildAnalyticsData();

        return view('livewire.portal-guru.jurnal-mengajar.jurnal-mengajar-list', [
            'jurnals' => $jurnals,
            'totalJurnal' => $totalJurnal,
            'totalJp' => $totalJp,
            'avgKehadiran' => $avgKehadiran,
            'analytics' => $analytics,
        ]);
    }
}
