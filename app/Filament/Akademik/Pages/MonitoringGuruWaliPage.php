<?php

namespace App\Filament\Akademik\Pages;

use App\Enums\StatusPengajuan;
use App\Filament\Akademik\Concerns\InteractsWithGuruWaliPeriod;
use App\Filament\Traits\HasSimplePageRoleAccess;
use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use App\Models\KonsultasiGuruWali;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class MonitoringGuruWaliPage extends Page
{
    use HasSimplePageRoleAccess;
    use InteractsWithGuruWaliPeriod;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationLabel = 'Monitoring Guru Wali';

    protected static ?string $title = 'Monitoring Guru Wali & Kelompok Binaan';

    protected static ?string $slug = 'guru-wali/monitoring';

    protected static string|\UnitEnum|null $navigationGroup = 'Guru Wali';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.akademik.pages.monitoring-guru-wali';

    public string $activeTab = 'guru_wali'; // 'guru_wali' | 'kelompok'
    public ?string $selectedAcademicYearId = null;
    public string $selectedPeriod = '';
    public string $complianceFilter = ''; // '' | 'all' | 'complete' | 'incomplete' | 'empty'
    public ?string $selectedKelompokId = '';
    public bool $isProcessed = false;

    public ?array $unservedModalData = null;

    public function mount(): void
    {
        $activeYear = self::getActiveGuruWaliAcademicYear();
        $this->selectedAcademicYearId = $activeYear?->id;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['guru_wali', 'kelompok'], true) ? $tab : 'guru_wali';
        $this->unservedModalData = null;
        $this->isProcessed = false;
    }

    public function inspectKelompok(string $kelompokId): void
    {
        $this->selectedKelompokId = $kelompokId;
        $this->activeTab = 'kelompok';
        $this->unservedModalData = null;
        $this->isProcessed = ! empty($this->selectedPeriod);
    }

    public function prosesData(): void
    {
        if (empty($this->selectedPeriod)) {
            Notification::make()
                ->title('Pilih Periode Waktu Terlebih Dahulu')
                ->body('Silakan pilih Bulan, Semester, atau Tahun sebelum menampilkan data.')
                ->warning()
                ->send();

            return;
        }

        if ($this->activeTab === 'guru_wali' && empty($this->complianceFilter)) {
            Notification::make()
                ->title('Pilih Status Kepatuhan Terlebih Dahulu')
                ->body('Silakan pilih Status Kepatuhan sebelum menampilkan data monitoring.')
                ->warning()
                ->send();

            return;
        }

        if ($this->activeTab === 'kelompok' && empty($this->selectedKelompokId)) {
            Notification::make()
                ->title('Pilih Kelompok Terlebih Dahulu')
                ->body('Silakan pilih Kelompok Guru Wali sebelum menampilkan rincian anggota.')
                ->warning()
                ->send();

            return;
        }

        $this->isProcessed = true;
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->selectedPeriod = '';
        $this->complianceFilter = '';
        $this->selectedKelompokId = '';
        $this->unservedModalData = null;
        $this->isProcessed = false;
    }

    public function updatedSelectedPeriod(): void
    {
        $this->complianceFilter = '';
        $this->selectedKelompokId = '';
        $this->unservedModalData = null;
        $this->isProcessed = false;
    }

    public function updatedComplianceFilter(): void
    {
        $this->unservedModalData = null;
        $this->isProcessed = false;
    }

    public function updatedSelectedKelompokId(): void
    {
        $this->isProcessed = false;
    }

    public function showUnservedModal(string $kelompokId): void
    {
        $kelompok = KelompokGuruWali::with(['guru:id,name', 'anggotaAktif.siswa.enrollmentAktif.kelas'])
            ->find($kelompokId);

        if (! $kelompok) {
            return;
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        [$startDate, $endDate, $periodLabel] = self::resolveGuruWaliDateRange($this->selectedPeriod, $ay);

        $servedStudentIds = JurnalGuruWali::where('kelompok_id', $kelompok->id)
            ->whereBetween('tanggal_waktu', [$startDate, $endDate])
            ->distinct()
            ->pluck('student_id')
            ->flip()
            ->toArray();

        $lastJurnalByStudent = JurnalGuruWali::where('kelompok_id', $kelompok->id)
            ->selectRaw('student_id, MAX(tanggal_waktu) as last_date')
            ->groupBy('student_id')
            ->pluck('last_date', 'student_id');

        $belumSiswa = [];
        foreach ($kelompok->anggotaAktif as $anggota) {
            if (! $anggota->siswa || isset($servedStudentIds[$anggota->student_id])) {
                continue;
            }
            $lastDate = $lastJurnalByStudent[$anggota->student_id] ?? null;
            $belumSiswa[] = [
                'name'      => $anggota->siswa->name,
                'nisn'      => $anggota->siswa->nisn ?: '-',
                'kelas'     => $anggota->siswa->getRombelName($this->selectedAcademicYearId) ?? '-',
                'last_ever' => $lastDate ? Carbon::parse($lastDate)->translatedFormat('d M Y') : 'Belum pernah sama sekali',
            ];
        }

        $this->unservedModalData = [
            'kelompok_id'   => $kelompok->id,
            'nama_kelompok' => $kelompok->nama_kelompok,
            'guru_name'     => $kelompok->guru?->name ?? '-',
            'period_label'  => $periodLabel,
            'students'      => $belumSiswa,
        ];
    }

    public function closeUnservedModal(): void
    {
        $this->unservedModalData = null;
    }

    protected function getViewData(): array
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId) ?? self::getActiveGuruWaliAcademicYear();
        $academicYearOptions = self::getAllGuruWaliAcademicYears();
        $periodOptions = self::buildGuruWaliPeriodOptions($ay, true, true);
        [$startDate, $endDate, $periodLabel] = ! empty($this->selectedPeriod)
            ? self::resolveGuruWaliDateRange($this->selectedPeriod, $ay)
            : [null, null, '-'];

        $allKelompok = KelompokGuruWali::with('guru:id,name,nip')
            ->withCount('anggotaAktif')
            ->orderBy('nama_kelompok')
            ->get();

        $guruRows = collect();
        $kelompokMembers = collect();
        $selectedKelompokObj = null;

        if ($this->activeTab === 'guru_wali' && $this->isProcessed && ! empty($this->selectedPeriod) && ! empty($this->complianceFilter)) {
            $jurnalStats = JurnalGuruWali::query()
                ->selectRaw('
                    kelompok_id,
                    COUNT(*) as total_sesi,
                    COUNT(DISTINCT student_id) as siswa_dibimbing,
                    SUM(CASE WHEN jenis_pendampingan = "Individu" THEN 1 ELSE 0 END) as sesi_individu,
                    SUM(CASE WHEN jenis_pendampingan IN ("Kelompok Kecil", "Klasikal") THEN 1 ELSE 0 END) as sesi_kelompok,
                    SUM(CASE WHEN rujukan_kolaborasi = "Guru BK" THEN 1 ELSE 0 END) as rujukan_bk,
                    MAX(tanggal_waktu) as last_date
                ')
                ->whereBetween('tanggal_waktu', [$startDate, $endDate])
                ->groupBy('kelompok_id')
                ->get()
                ->keyBy('kelompok_id');

            $selesaiStatuses = [
                StatusPengajuan::Selesai->value,
                StatusPengajuan::DikonversiKeJurnal->value,
            ];

            $konsultasiStats = KonsultasiGuruWali::query()
                ->selectRaw('
                    kelompok_id,
                    COUNT(*) as total_konsultasi,
                    SUM(CASE WHEN status_pengajuan IN (?, ?) THEN 1 ELSE 0 END) as konsultasi_selesai
                ', $selesaiStatuses)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('kelompok_id')
                ->get()
                ->keyBy('kelompok_id');

            $guruRows = $allKelompok
                ->filter(fn ($k) => $k->status_aktif || isset($jurnalStats[$k->id]))
                ->map(function ($k) use ($jurnalStats, $konsultasiStats) {
                    $js = $jurnalStats->get($k->id);
                    $ks = $konsultasiStats->get($k->id);

                    $totalSiswa = (int) $k->anggota_aktif_count;
                    $dibimbing = min($totalSiswa, (int) ($js->siswa_dibimbing ?? 0));
                    $belum = max(0, $totalSiswa - $dibimbing);
                    $coverage = $totalSiswa > 0 ? (int) round(($dibimbing / $totalSiswa) * 100) : 0;

                    return [
                        'kelompok_id'        => $k->id,
                        'nama_kelompok'      => $k->nama_kelompok,
                        'guru_name'          => $k->guru?->name ?? '-',
                        'guru_nip'           => $k->guru?->nip ?? '-',
                        'status_aktif'       => (bool) $k->status_aktif,
                        'total_siswa'        => $totalSiswa,
                        'siswa_dibimbing'    => $dibimbing,
                        'siswa_belum'        => $belum,
                        'coverage'           => $coverage,
                        'total_sesi'         => (int) ($js->total_sesi ?? 0),
                        'sesi_individu'      => (int) ($js->sesi_individu ?? 0),
                        'sesi_kelompok'      => (int) ($js->sesi_kelompok ?? 0),
                        'rujukan_bk'         => (int) ($js->rujukan_bk ?? 0),
                        'total_konsultasi'   => (int) ($ks->total_konsultasi ?? 0),
                        'konsultasi_selesai' => (int) ($ks->konsultasi_selesai ?? 0),
                        'last_date'          => $js?->last_date ? Carbon::parse($js->last_date)->translatedFormat('d M Y') : null,
                    ];
                })
                ->filter(function ($row) {
                    return match ($this->complianceFilter) {
                        'complete'   => $row['total_siswa'] > 0 && $row['coverage'] >= 100,
                        'incomplete' => $row['total_sesi'] > 0 && $row['coverage'] < 100,
                        'empty'      => $row['total_sesi'] === 0,
                        default      => true,
                    };
                })
                ->values();
        } elseif ($this->activeTab === 'kelompok' && $this->isProcessed && ! empty($this->selectedPeriod) && ! empty($this->selectedKelompokId)) {
            $selectedKelompokObj = KelompokGuruWali::with([
                'guru:id,name,nip',
                'anggotaAktif.siswa.enrollmentAktif.kelas',
            ])->find($this->selectedKelompokId);

            if ($selectedKelompokObj) {
                $jurnalsPeriode = JurnalGuruWali::where('kelompok_id', $selectedKelompokObj->id)
                    ->whereBetween('tanggal_waktu', [$startDate, $endDate])
                    ->orderBy('tanggal_waktu', 'desc')
                    ->get()
                    ->groupBy('student_id');

                $lastEverMap = DB::table('jurnal_guru_wali')
                    ->where('kelompok_id', $selectedKelompokObj->id)
                    ->whereNull('deleted_at')
                    ->selectRaw('student_id, MAX(tanggal_waktu) as last_ever')
                    ->groupBy('student_id')
                    ->pluck('last_ever', 'student_id');

                $konsultasiByStudent = KonsultasiGuruWali::where('kelompok_id', $selectedKelompokObj->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy('student_id');

                $kelompokMembers = $selectedKelompokObj->anggotaAktif
                    ->filter(fn ($a) => $a->siswa !== null)
                    ->sortBy(fn ($a) => $a->siswa->name)
                    ->map(function ($anggota) use ($jurnalsPeriode, $lastEverMap, $konsultasiByStudent, $ay) {
                        $siswa = $anggota->siswa;
                        $jurnals = $jurnalsPeriode->get($siswa->id, collect());
                        $konsultasis = $konsultasiByStudent->get($siswa->id, collect());
                        $lastSesi = $jurnals->first();
                        $lastEver = $lastEverMap[$siswa->id] ?? null;

                        $pilarDominan = $jurnals->isNotEmpty()
                            ? $jurnals->groupBy('kategori_pendampingan')->sortByDesc->count()->keys()->first()
                            : '—';

                        return [
                            'student_id'       => $siswa->id,
                            'name'             => $siswa->name,
                            'nisn'             => $siswa->nisn ?: '-',
                            'kelas'            => $siswa->getRombelName($ay?->id) ?? '-',
                            'total_sesi'       => $jurnals->count(),
                            'pilar_dominan'    => $pilarDominan,
                            'last_status'      => $lastSesi?->status_sesi ?? 'Belum ada sesi',
                            'last_date'        => $lastSesi ? Carbon::parse($lastSesi->tanggal_waktu)->translatedFormat('d M Y') : '—',
                            'last_ever_date'   => $lastEver ? Carbon::parse($lastEver)->translatedFormat('d M Y') : 'Belum pernah',
                            'total_konsultasi' => $konsultasis->count(),
                            'is_served'        => $jurnals->isNotEmpty(),
                        ];
                    })
                    ->values();
            }
        }

        return [
            'activeYearId'        => $ay?->id,
            'activeYearName'      => $ay?->name ?? 'Aktif',
            'academicYearOptions' => $academicYearOptions,
            'periodOptions'       => $periodOptions,
            'periodLabel'         => $periodLabel,
            'allKelompok'         => $allKelompok,
            'guruRows'            => $guruRows,
            'selectedKelompokObj' => $selectedKelompokObj,
            'kelompokMembers'     => $kelompokMembers,
        ];
    }
}

