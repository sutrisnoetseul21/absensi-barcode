<?php

namespace App\Filament\Akademik\Pages;

use App\Filament\Traits\HasSimplePageRoleAccess;
use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AnalisaPresensiKbmSiswaPage extends Page
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Analisa Presensi KBM Siswa';

    protected static ?string $title = 'Analisa Presensi KBM & Deteksi Siswa Tidak Mengikuti Jam';

    protected static ?string $slug = 'jurnal-analisa-presensi-siswa';

    protected static string|\UnitEnum|null $navigationGroup = 'Jurnal Mengajar';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.akademik.pages.analisa-presensi-kbm-siswa';

    public ?string $selectedAcademicYearId = null;
    public string $selectedPeriod = 'semester_ganjil';
    public ?string $selectedClassId = '';
    public ?string $selectedMapelId = '';
    public string $focusFilter = 'bolos_only'; // 'bolos_only' | 'absent_only' | 'all'
    public ?string $selectedStudentId = '';
    public string $search = '';
    public bool $isProcessed = false;

    public function mount(): void
    {
        $this->selectedAcademicYearId = PengaturanSekolah::current()?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->first()?->id
            ?? TahunAjaran::orderBy('start_year', 'desc')->value('id');

        $this->selectedPeriod = (int) date('n') >= 7 ? 'semester_ganjil' : 'semester_genap';
    }

    public function prosesAnalisa(): void
    {
        if (empty($this->selectedClassId)) {
            Notification::make()
                ->title('Pilih Kelas Terlebih Dahulu')
                ->body('Silakan pilih 1 Kelas terlebih dahulu sebelum memproses analisa KBM.')
                ->warning()
                ->send();

            return;
        }

        $this->isProcessed = true;
    }

    public function selectStudent(string $studentId = '', string $studentLabel = '', string $classId = ''): void
    {
        $this->selectedStudentId = $studentId;
        $this->search = $studentLabel;
        if ($classId !== '' && empty($this->selectedClassId)) {
            $this->selectedClassId = $classId;
        }
        $this->isProcessed = false;
    }

    public function clearStudentSelection(): void
    {
        $this->selectedStudentId = '';
        $this->search = '';
        $this->isProcessed = false;
    }

    public function updatedSelectedPeriod(): void
    {
        $this->isProcessed = false;
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedMapelId = '';
        $this->selectedStudentId = '';
        $this->search = '';
        $this->isProcessed = false;
    }

    public function updatedSelectedMapelId(): void
    {
        $this->isProcessed = false;
    }

    public function updatedSearch(): void
    {
        // Reset selectedStudentId when user manually edits search text
        $this->selectedStudentId = '';
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

    protected function getStudentDropdownOptions(): array
    {
        $term = trim($this->search);

        $query = EnrollmentSiswa::query()
            ->join('students', 'students.id', '=', 'student_enrollments.student_id')
            ->join('classes', 'classes.id', '=', 'student_enrollments.class_id')
            ->where('student_enrollments.status', 'aktif')
            ->where('students.status', 'aktif')
            ->when($this->selectedAcademicYearId, fn ($q) => $q->where('student_enrollments.academic_year_id', $this->selectedAcademicYearId))
            ->when(!empty($this->selectedClassId), fn ($q) => $q->where('student_enrollments.class_id', $this->selectedClassId))
            ->when($term !== '' && empty($this->selectedStudentId), function ($q) use ($term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('students.name', 'like', "%{$term}%")
                        ->orWhere('students.nisn', 'like', "%{$term}%")
                        ->orWhere('students.nis', 'like', "%{$term}%");
                });
            })
            ->select([
                'students.id as id',
                'students.name as name',
                'students.nisn as nisn',
                'students.nis as nis',
                'classes.id as class_id',
                'classes.name as class_name',
            ])
            ->orderBy('students.name')
            ->limit(!empty($this->selectedClassId) ? 60 : 30)
            ->get();

        return $query->map(fn ($row) => [
            'id'         => (string) $row->id,
            'name'       => (string) $row->name,
            'nisn'       => (string) ($row->nisn ?: ($row->nis ?: '-')),
            'class_id'   => (string) $row->class_id,
            'class_name' => (string) $row->class_name,
        ])->toArray();
    }

    protected function getViewData(): array
    {
        $activeYearObj = TahunAjaran::find($this->selectedAcademicYearId);
        $classes = Kelas::orderBy('grade_level')->orderBy('name')->get(['id', 'name', 'grade_level']);
        $studentOptions = $this->getStudentDropdownOptions();

        // Daftar mapel untuk kelas yang dipilih (opsional bisa Semua Mapel)
        $availableMapels = collect();
        if (!empty($this->selectedClassId)) {
            $mapelIds = Pengajaran::query()
                ->whereHas('kelasAjaran', fn ($q) => $q
                    ->where('class_id', $this->selectedClassId)
                    ->when($this->selectedAcademicYearId, fn ($sq) => $sq->where('academic_year_id', $this->selectedAcademicYearId))
                )
                ->pluck('mata_pelajaran_id')
                ->filter()
                ->unique()
                ->values();

            if ($mapelIds->isNotEmpty()) {
                $availableMapels = MataPelajaran::whereIn('id', $mapelIds)->orderBy('nama_mapel')->get(['id', 'nama_mapel']);
            } else {
                $availableMapels = MataPelajaran::orderBy('nama_mapel')->get(['id', 'nama_mapel']);
            }
        }

        $kpi = [
            'total_sesi'        => 0,
            'total_jp'          => 0,
            'tidak_mengikuti'   => 0,
            'siswa_bolos_count' => 0,
            'sakit_izin'        => 0,
            'alpa'              => 0,
        ];

        if (!$this->isProcessed || empty($this->selectedClassId)) {
            return [
                'activeYearName'  => $activeYearObj?->name ?? 'Aktif',
                'classes'         => $classes,
                'availableMapels' => $availableMapels,
                'studentOptions'  => $studentOptions,
                'periodOptions'   => $this->periodOptions,
                'kpi'             => $kpi,
                'studentRows'     => collect(),
                'selectedKelas'   => null,
            ];
        }

        [$dateFrom, $dateTo] = $this->resolveDateRange();
        $selectedKelas = $classes->firstWhere('id', $this->selectedClassId);
        $className = $selectedKelas?->name ?? '-';

        // 1. Hitung total sesi & JP di kelas ini via SQL agregat
        $sessionStat = DB::table('jurnal_pembelajarans')
            ->selectRaw('COUNT(*) as total_sesi, COALESCE(SUM(durasi_jp), 0) as total_jp')
            ->when($this->selectedAcademicYearId, fn ($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->where('class_id', $this->selectedClassId)
            ->when($this->selectedMapelId, fn ($q) => $q->where('mata_pelajaran_id', $this->selectedMapelId))
            ->when($dateFrom, fn ($q) => $q->whereDate('tanggal', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('tanggal', '<=', $dateTo))
            ->first();

        $kpi['total_sesi'] = (int) ($sessionStat->total_sesi ?? 0);
        $kpi['total_jp'] = (int) ($sessionStat->total_jp ?? 0);

        // 2. Agregasi SQL GROUP BY per student_id di kelas ini (sangat ringan di database)
        $studentStats = DB::table('jurnal_pembelajaran_presensis as jpp')
            ->join('jurnal_pembelajarans as jp', 'jp.id', '=', 'jpp.jurnal_pembelajaran_id')
            ->selectRaw("
                jpp.student_id,
                COUNT(*) as total_pertemuan,
                SUM(CASE WHEN LOWER(COALESCE(jpp.status_jurnal, '')) != 'tidak_mengikuti' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN LOWER(COALESCE(jpp.status_jurnal, '')) = 'tidak_mengikuti' AND LOWER(COALESCE(jpp.status_gerbang_snapshot, '')) = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(COALESCE(jpp.status_jurnal, '')) = 'tidak_mengikuti' AND LOWER(COALESCE(jpp.status_gerbang_snapshot, '')) = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(COALESCE(jpp.status_jurnal, '')) = 'tidak_mengikuti' AND LOWER(COALESCE(jpp.status_gerbang_snapshot, '')) = 'alpa' THEN 1 ELSE 0 END) as alpa,
                SUM(CASE WHEN LOWER(COALESCE(jpp.status_jurnal, '')) = 'tidak_mengikuti' AND LOWER(COALESCE(jpp.status_gerbang_snapshot, '')) NOT IN ('sakit', 'izin', 'alpa') THEN 1 ELSE 0 END) as tidak_mengikuti
            ")
            ->when($this->selectedAcademicYearId, fn ($q) => $q->where('jp.academic_year_id', $this->selectedAcademicYearId))
            ->where('jp.class_id', $this->selectedClassId)
            ->when($this->selectedMapelId, fn ($q) => $q->where('jp.mata_pelajaran_id', $this->selectedMapelId))
            ->when(!empty($this->selectedStudentId), fn ($q) => $q->where('jpp.student_id', $this->selectedStudentId))
            ->when($dateFrom, fn ($q) => $q->whereDate('jp.tanggal', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('jp.tanggal', '<=', $dateTo))
            ->groupBy('jpp.student_id')
            ->get();

        foreach ($studentStats as $st) {
            $kpi['tidak_mengikuti'] += (int) $st->tidak_mengikuti;
            $kpi['sakit_izin'] += (int) $st->sakit + (int) $st->izin;
            $kpi['alpa'] += (int) $st->alpa;
            if ((int) $st->tidak_mengikuti > 0) {
                $kpi['siswa_bolos_count']++;
            }
        }

        // Ambil profil siswa untuk student_id di kelas ini
        $studentIds = $studentStats->pluck('student_id')->filter()->unique()->values();
        $siswaMap = Siswa::whereIn('id', $studentIds)->get(['id', 'name', 'nis', 'nisn'])->keyBy('id');

        // 3. Ambil rincian insiden HANYA untuk baris yang status_jurnal = 'tidak_mengikuti' di kelas ini
        $incidentRecords = DB::table('jurnal_pembelajaran_presensis as jpp')
            ->join('jurnal_pembelajarans as jp', 'jp.id', '=', 'jpp.jurnal_pembelajaran_id')
            ->leftJoin('mata_pelajarans as mp', 'mp.id', '=', 'jp.mata_pelajaran_id')
            ->leftJoin('teachers as t', 't.id', '=', 'jp.teacher_id')
            ->select([
                'jpp.student_id',
                'jpp.status_gerbang_snapshot',
                'jpp.keterangan',
                'jp.tanggal',
                'jp.jam_mulai',
                'jp.jam_ke_label',
                'mp.nama_mapel',
                't.name as guru_name',
            ])
            ->when($this->selectedAcademicYearId, fn ($q) => $q->where('jp.academic_year_id', $this->selectedAcademicYearId))
            ->where('jp.class_id', $this->selectedClassId)
            ->when($this->selectedMapelId, fn ($q) => $q->where('jp.mata_pelajaran_id', $this->selectedMapelId))
            ->when(!empty($this->selectedStudentId), fn ($q) => $q->where('jpp.student_id', $this->selectedStudentId))
            ->when($dateFrom, fn ($q) => $q->whereDate('jp.tanggal', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('jp.tanggal', '<=', $dateTo))
            ->where('jpp.status_jurnal', 'tidak_mengikuti')
            ->orderBy('jp.tanggal', 'desc')
            ->orderBy('jp.jam_mulai', 'desc')
            ->get();

        $incidentsByStudent = [];
        foreach ($incidentRecords as $inc) {
            $sid = (string) $inc->student_id;
            if (count($incidentsByStudent[$sid] ?? []) >= 8) {
                continue;
            }
            $gerbang = strtolower((string) ($inc->status_gerbang_snapshot ?? ''));
            [$norm, $label] = match ($gerbang) {
                'sakit' => ['sakit', 'Sakit'],
                'izin'  => ['izin', 'Izin'],
                'alpa'  => ['alpa', 'Alpa'],
                default => ['tidak_mengikuti', 'Bolos / Tidak Ikut KBM'],
            };

            $incidentsByStudent[$sid][] = [
                'tanggal'    => $inc->tanggal ? Carbon::parse($inc->tanggal)->format('d M Y') : '-',
                'jam'        => $inc->jam_ke_label ?: "Jam ke {$inc->jam_mulai}",
                'mapel'      => $inc->nama_mapel ?? '-',
                'guru'       => $inc->guru_name ?? '-',
                'norm'       => $norm,
                'label'      => $label,
                'gerbang'    => strtoupper($gerbang ?: 'HADIR'),
                'keterangan' => $inc->keterangan,
            ];
        }

        $hasSpecificStudent = !empty($this->selectedStudentId);

        $studentRows = $studentStats->map(function ($st) use ($siswaMap, $className, $incidentsByStudent) {
            $sid = (string) $st->student_id;
            $siswa = $siswaMap[$sid] ?? null;
            $totalPertemuan = (int) $st->total_pertemuan;
            $hadir = (int) $st->hadir;
            $sakit = (int) $st->sakit;
            $izin = (int) $st->izin;
            $alpa = (int) $st->alpa;
            $bolos = (int) $st->tidak_mengikuti;
            $totalAbsen = $sakit + $izin + $alpa + $bolos;

            return [
                'student_id'      => $sid,
                'student_name'    => $siswa?->name ?? 'Siswa',
                'nisn'            => $siswa?->nisn ?: ($siswa?->nis ?: '-'),
                'class_name'      => $className,
                'total_pertemuan' => $totalPertemuan,
                'hadir'           => $hadir,
                'sakit'           => $sakit,
                'izin'            => $izin,
                'alpa'            => $alpa,
                'tidak_mengikuti' => $bolos,
                'total_absen'     => $totalAbsen,
                'persentase_hadir'=> $totalPertemuan > 0 ? round(($hadir / $totalPertemuan) * 100, 1) : 100.0,
                'incidents'       => $incidentsByStudent[$sid] ?? [],
            ];
        })
        ->when(!$hasSpecificStudent && $this->focusFilter === 'bolos_only', fn ($c) => $c->where('tidak_mengikuti', '>', 0))
        ->when(!$hasSpecificStudent && $this->focusFilter === 'absent_only', fn ($c) => $c->where('total_absen', '>', 0))
        ->when(!$hasSpecificStudent && trim($this->search) !== '', function ($c) {
            $q = mb_strtolower(trim($this->search));

            return $c->filter(fn ($r) => str_contains(mb_strtolower($r['student_name']), $q)
                || str_contains(mb_strtolower($r['nisn']), $q));
        })
        ->sortBy([
            ['tidak_mengikuti', 'desc'],
            ['total_absen', 'desc'],
            ['student_name', 'asc'],
        ])
        ->values();

        return [
            'activeYearName'  => $activeYearObj?->name ?? 'Aktif',
            'classes'         => $classes,
            'availableMapels' => $availableMapels,
            'studentOptions'  => $studentOptions,
            'periodOptions'   => $this->periodOptions,
            'kpi'             => $kpi,
            'studentRows'     => $studentRows,
            'selectedKelas'   => $selectedKelas,
        ];
    }
}
