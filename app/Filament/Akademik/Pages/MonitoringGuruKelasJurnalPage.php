<?php

namespace App\Filament\Akademik\Pages;

use App\Filament\Traits\HasSimplePageRoleAccess;
use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\KelasAjaran;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class MonitoringGuruKelasJurnalPage extends Page
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationLabel = 'Monitoring Guru & Kelas';

    protected static ?string $title = 'Monitoring Jurnal Guru & Rekap Kelas';

    protected static ?string $slug = 'jurnal-monitoring-guru-kelas';

    protected static string|\UnitEnum|null $navigationGroup = 'Jurnal Mengajar';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.akademik.pages.monitoring-guru-kelas-jurnal';

    public string $activeTab = 'guru'; // 'guru' | 'kelas'
    public ?string $selectedAcademicYearId = null;
    public string $selectedPeriod = ''; // 'semester_ganjil', 'semester_genap', or 'MM-YYYY'
    public ?string $selectedClassId = '';
    public string $statusFilter = 'all'; // 'all', 'filled', 'unfilled'
    public ?string $selectedTeacherId = '';
    public ?string $selectedMapelId = '';
    public bool $isProcessed = false;

    public function mount(): void
    {
        $activeYear = PengaturanSekolah::current()?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->first()?->id
            ?? TahunAjaran::orderBy('start_year', 'desc')->value('id');

        $this->selectedAcademicYearId = $activeYear;
        $this->selectedPeriod = (int) date('n') >= 7 ? 'semester_ganjil' : 'semester_genap';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['guru', 'kelas'], true) ? $tab : 'guru';
        $this->isProcessed = false;
    }

    public function prosesData(): void
    {
        if (empty($this->selectedClassId)) {
            \Filament\Notifications\Notification::make()
                ->title('Pilih Kelas Terlebih Dahulu')
                ->body('Silakan pilih Kelas sebelum menampilkan data monitoring.')
                ->warning()
                ->send();

            return;
        }

        $this->isProcessed = true;
    }

    public function updatedSelectedPeriod(): void
    {
        $this->isProcessed = false;
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedTeacherId = '';
        $this->selectedMapelId = '';
        $this->isProcessed = false;
    }

    public function updatedSelectedTeacherId(): void
    {
        $this->selectedMapelId = '';
    }

    public function getPeriodOptionsProperty(): array
    {
        $options = [
            'semester_ganjil' => 'Semester 1 / Ganjil (Juli – Desember)',
            'semester_genap'  => 'Semester 2 / Genap (Januari – Juni)',
        ];

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if ($ay) {
            $sy = $ay->start_year ?: (int) date('Y');
            $ey = $ay->end_year ?: ($sy + 1);
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            for ($m = 7; $m <= 12; $m++) {
                $options[sprintf('%02d-%d', $m, $sy)] = 'Per Bulan: ' . $monthNames[$m] . ' ' . $sy;
            }
            for ($m = 1; $m <= 6; $m++) {
                $options[sprintf('%02d-%d', $m, $ey)] = 'Per Bulan: ' . $monthNames[$m] . ' ' . $ey;
            }
        }

        return $options;
    }

    protected function resolveDateRange(): array
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sy = $ay?->start_year ?: (int) date('Y');
        $ey = $ay?->end_year ?: ($sy + 1);

        if ($this->selectedPeriod === 'semester_ganjil') {
            return [
                Carbon::create($sy, 7, 1)->toDateString(),
                Carbon::create($sy, 12, 31)->toDateString(),
            ];
        }

        if ($this->selectedPeriod === 'semester_genap') {
            return [
                Carbon::create($ey, 1, 1)->toDateString(),
                Carbon::create($ey, 6, 30)->toDateString(),
            ];
        }

        if (preg_match('/^(\d{2})-(\d{4})$/', $this->selectedPeriod, $m)) {
            $start = Carbon::create((int) $m[2], (int) $m[1], 1)->startOfMonth();
            $end = Carbon::create((int) $m[2], (int) $m[1], 1)->endOfMonth();

            return [$start->toDateString(), $end->toDateString()];
        }

        return [
            Carbon::create($sy, 7, 1)->toDateString(),
            Carbon::create($ey, 6, 30)->toDateString(),
        ];
    }

    protected function getViewData(): array
    {
        $activeYearObj = TahunAjaran::find($this->selectedAcademicYearId);
        $classes = Kelas::orderBy('grade_level')->orderBy('name')->get(['id', 'name', 'grade_level']);

        // Daftar guru & mapel untuk kelas yang dipilih (cascading)
        $availableTeachers = collect();
        $availableMapels = collect();

        if (!empty($this->selectedClassId)) {
            $pengajaransInClass = Pengajaran::query()
                ->with(['guru:id,name', 'mataPelajaran:id,nama_mapel'])
                ->whereHas('kelasAjaran', fn ($q) => $q
                    ->where('class_id', $this->selectedClassId)
                    ->when($this->selectedAcademicYearId, fn ($sq) => $sq->where('academic_year_id', $this->selectedAcademicYearId))
                )
                ->get();

            $availableTeachers = $pengajaransInClass->pluck('guru')->filter()->unique('id')->sortBy('name')->values();
            if ($availableTeachers->isEmpty()) {
                $availableTeachers = Guru::orderBy('name')->get(['id', 'name']);
            }

            $availableMapels = $pengajaransInClass
                ->when($this->selectedTeacherId, fn ($c) => $c->where('teacher_id', $this->selectedTeacherId))
                ->pluck('mataPelajaran')
                ->filter()
                ->unique('id')
                ->sortBy('nama_mapel')
                ->values();

            if ($availableMapels->isEmpty()) {
                $availableMapels = MataPelajaran::orderBy('nama_mapel')->get(['id', 'nama_mapel']);
            }
        }

        if (!$this->isProcessed || empty($this->selectedClassId)) {
            return [
                'activeYearName'    => $activeYearObj?->name ?? 'Aktif',
                'classes'           => $classes,
                'availableTeachers' => $availableTeachers,
                'availableMapels'   => $availableMapels,
                'periodOptions'     => $this->periodOptions,
                'kpiTotalPenugasan' => 0,
                'kpiSudahIsi'       => 0,
                'kpiBelumIsi'       => 0,
                'kpiTotalSesi'      => 0,
                'kpiTotalJp'        => 0,
                'filteredGuruRows'  => collect(),
                'selectedClassObj'  => null,
                'waliKelasName'     => '-',
                'kelasSubjectRows'  => collect(),
                'dateFrom'          => null,
                'dateTo'            => null,
            ];
        }

        [$dateFrom, $dateTo] = $this->resolveDateRange();

        // Agregasi SQL GROUP BY langsung di MySQL untuk kelas & periode terpilih (Super cepat walau ribuan jurnal)
        $aggregatedStats = DB::table('jurnal_pembelajarans')
            ->selectRaw('
                teacher_id,
                class_id,
                mata_pelajaran_id,
                COUNT(*) as total_sesi,
                COALESCE(SUM(durasi_jp), 0) as total_jp,
                COALESCE(SUM(total_hadir), 0) as sum_hadir,
                COALESCE(SUM(total_siswa), 0) as sum_siswa,
                COALESCE(SUM(total_tidak_mengikuti), 0) as sum_tidak_mengikuti,
                MAX(tanggal) as last_date
            ')
            ->when($this->selectedAcademicYearId, fn ($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->where('class_id', $this->selectedClassId)
            ->when($dateFrom, fn ($q) => $q->whereDate('tanggal', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('tanggal', '<=', $dateTo))
            ->groupBy('teacher_id', 'class_id', 'mata_pelajaran_id')
            ->get();

        // Ambil materi terakhir per (teacher_id, class_id, mata_pelajaran_id) untuk kelas ini
        $latestMateriMap = [];
        if ($aggregatedStats->isNotEmpty()) {
            $latestJurnals = JurnalPembelajaran::query()
                ->select(['teacher_id', 'class_id', 'mata_pelajaran_id', 'tanggal', 'materi'])
                ->when($this->selectedAcademicYearId, fn ($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
                ->where('class_id', $this->selectedClassId)
                ->when($dateFrom, fn ($q) => $q->whereDate('tanggal', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('tanggal', '<=', $dateTo))
                ->orderBy('tanggal', 'desc')
                ->orderBy('jam_mulai', 'desc')
                ->get();

            foreach ($latestJurnals as $lj) {
                $k = $lj->teacher_id . '|' . $lj->class_id . '|' . $lj->mata_pelajaran_id;
                if (!isset($latestMateriMap[$k])) {
                    $latestMateriMap[$k] = $lj->materi;
                }
            }
        }

        $teacherMap = Guru::pluck('name', 'id');
        $mapelMap = MataPelajaran::pluck('nama_mapel', 'id');
        $selectedClassObj = $classes->firstWhere('id', $this->selectedClassId);
        $className = $selectedClassObj?->name ?? '-';

        $jurnalGroupMap = [];
        foreach ($aggregatedStats as $row) {
            $key = $row->teacher_id . '|' . $row->class_id . '|' . $row->mata_pelajaran_id;
            $jurnalGroupMap[$key] = [
                'teacher_id'          => $row->teacher_id,
                'teacher_name'        => $teacherMap[$row->teacher_id] ?? '-',
                'class_id'            => $row->class_id,
                'class_name'          => $className,
                'mata_pelajaran_id'   => $row->mata_pelajaran_id,
                'mapel_name'          => $mapelMap[$row->mata_pelajaran_id] ?? '-',
                'total_sesi'          => (int) $row->total_sesi,
                'total_jp'            => (int) $row->total_jp,
                'sum_hadir'           => (int) $row->sum_hadir,
                'sum_siswa'           => (int) $row->sum_siswa,
                'sum_tidak_mengikuti' => (int) $row->sum_tidak_mengikuti,
                'last_date'           => $row->last_date ? Carbon::parse($row->last_date)->format('d M Y') : null,
                'last_materi'         => $latestMateriMap[$key] ?? null,
            ];
        }

        // Ambil penugasan resmi dari tabel Pengajaran untuk Kelas & TA ini
        $pengajarans = Pengajaran::query()
            ->with(['guru:id,name', 'mataPelajaran:id,nama_mapel', 'kelasAjaran:id,class_id'])
            ->whereHas('kelasAjaran', fn ($q) => $q
                ->where('class_id', $this->selectedClassId)
                ->when($this->selectedAcademicYearId, fn ($sq) => $sq->where('academic_year_id', $this->selectedAcademicYearId))
            )
            ->get();

        $monitoringRows = [];
        $seenKeys = [];

        foreach ($pengajarans as $p) {
            $key = $p->teacher_id . '|' . $this->selectedClassId . '|' . $p->mata_pelajaran_id;
            $seenKeys[$key] = true;
            $stat = $jurnalGroupMap[$key] ?? null;

            $sumSiswa = $stat['sum_siswa'] ?? 0;
            $sumHadir = $stat['sum_hadir'] ?? 0;

            $monitoringRows[] = [
                'teacher_id'          => $p->teacher_id,
                'teacher_name'        => $p->guru?->name ?? '-',
                'class_id'            => $this->selectedClassId,
                'class_name'          => $className,
                'mata_pelajaran_id'   => $p->mata_pelajaran_id,
                'mapel_name'          => $p->mataPelajaran?->nama_mapel ?? '-',
                'total_sesi'          => $stat['total_sesi'] ?? 0,
                'total_jp'            => $stat['total_jp'] ?? 0,
                'avg_kehadiran'       => $sumSiswa > 0 ? round(($sumHadir / $sumSiswa) * 100, 1) : 0.0,
                'sum_tidak_mengikuti' => $stat['sum_tidak_mengikuti'] ?? 0,
                'last_date'           => $stat['last_date'] ?? null,
                'last_materi'         => $stat['last_materi'] ?? null,
            ];
        }

        foreach ($jurnalGroupMap as $key => $stat) {
            if (isset($seenKeys[$key])) {
                continue;
            }
            $sumSiswa = $stat['sum_siswa'];
            $sumHadir = $stat['sum_hadir'];
            $monitoringRows[] = [
                'teacher_id'          => $stat['teacher_id'],
                'teacher_name'        => $stat['teacher_name'],
                'class_id'            => $stat['class_id'],
                'class_name'          => $stat['class_name'],
                'mata_pelajaran_id'   => $stat['mata_pelajaran_id'],
                'mapel_name'          => $stat['mapel_name'],
                'total_sesi'          => $stat['total_sesi'],
                'total_jp'            => $stat['total_jp'],
                'avg_kehadiran'       => $sumSiswa > 0 ? round(($sumHadir / $sumSiswa) * 100, 1) : 0.0,
                'sum_tidak_mengikuti' => $stat['sum_tidak_mengikuti'],
                'last_date'           => $stat['last_date'],
                'last_materi'         => $stat['last_materi'],
            ];
        }

        $kpiTotalPenugasan = count($monitoringRows);
        $kpiSudahIsi = collect($monitoringRows)->where('total_sesi', '>', 0)->count();
        $kpiBelumIsi = $kpiTotalPenugasan - $kpiSudahIsi;
        $kpiTotalSesi = collect($monitoringRows)->sum('total_sesi');
        $kpiTotalJp = collect($monitoringRows)->sum('total_jp');

        $filteredGuruRows = collect($monitoringRows)
            ->when($this->selectedTeacherId, fn ($c) => $c->where('teacher_id', $this->selectedTeacherId))
            ->when($this->selectedMapelId, fn ($c) => $c->where('mata_pelajaran_id', (int) $this->selectedMapelId))
            ->when($this->statusFilter === 'filled', fn ($c) => $c->where('total_sesi', '>', 0))
            ->when($this->statusFilter === 'unfilled', fn ($c) => $c->where('total_sesi', '=', 0))
            ->sortBy([
                ['total_sesi', 'desc'],
                ['mapel_name', 'asc'],
            ])
            ->values();

        $waliKelasName = 'Belum Ditentukan';
        if ($this->selectedClassId && $this->selectedAcademicYearId) {
            $ka = KelasAjaran::with('guru:id,name')
                ->where('class_id', $this->selectedClassId)
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->first();
            $waliKelasName = $ka?->guru?->name ?? 'Belum Ditentukan';
        }

        $kelasSubjectRows = collect($monitoringRows)
            ->sortBy('mapel_name')
            ->values();

        return [
            'activeYearName'    => $activeYearObj?->name ?? 'Aktif',
            'classes'           => $classes,
            'availableTeachers' => $availableTeachers,
            'availableMapels'   => $availableMapels,
            'periodOptions'     => $this->periodOptions,
            'kpiTotalPenugasan' => $kpiTotalPenugasan,
            'kpiSudahIsi'       => $kpiSudahIsi,
            'kpiBelumIsi'       => $kpiBelumIsi,
            'kpiTotalSesi'      => $kpiTotalSesi,
            'kpiTotalJp'        => $kpiTotalJp,
            'filteredGuruRows'  => $filteredGuruRows,
            'selectedClassObj'  => $selectedClassObj,
            'waliKelasName'     => $waliKelasName,
            'kelasSubjectRows'  => $kelasSubjectRows,
            'dateFrom'          => $dateFrom,
            'dateTo'            => $dateTo,
        ];
    }
}
