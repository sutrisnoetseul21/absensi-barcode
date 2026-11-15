<?php

namespace App\Filament\Akademik\Pages;

use App\Enums\KategoriSentimen;
use App\Enums\StatusPengajuan;
use App\Enums\StudentFeedbackEmoji;
use App\Filament\Akademik\Concerns\InteractsWithGuruWaliPeriod;
use App\Filament\Traits\HasSimplePageRoleAccess;
use App\Models\BadgeKarakterSiswa;
use App\Models\EnrollmentSiswa;
use App\Models\JurnalGuruWali;
use App\Models\Kelas;
use App\Models\KelompokGuruWali;
use App\Models\KonsultasiGuruWali;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AnalisaGuruWaliSiswaPage extends Page
{
    use HasSimplePageRoleAccess;
    use InteractsWithGuruWaliPeriod;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Analisa Bimbingan Siswa';

    protected static ?string $title = 'Analisa Bimbingan Guru Wali & Konsultasi Siswa';

    protected static ?string $slug = 'guru-wali/analisa';

    protected static string|\UnitEnum|null $navigationGroup = 'Guru Wali';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.akademik.pages.analisa-guru-wali-siswa';

    public ?string $selectedAcademicYearId = null;
    public string $selectedPeriod = '';
    public string $scopeType = ''; // '' | 'kelas' | 'kelompok'
    public string $scopeTargetId = '';
    public string $focusFilter = ''; // '' | 'all' | 'unserved' | 'attention' | 'consult_active'
    public ?string $selectedStudentId = '';
    public string $search = '';
    public bool $isProcessed = false;

    public ?array $timelineModal = null;

    public function mount(): void
    {
        $this->selectedAcademicYearId = self::getActiveGuruWaliAcademicYear()?->id;
    }

    public function prosesAnalisa(): void
    {
        if (empty($this->selectedPeriod)) {
            Notification::make()
                ->title('Pilih Periode Waktu Terlebih Dahulu')
                ->body('Silakan pilih Periode Waktu sebelum memproses analisa.')
                ->warning()
                ->send();

            return;
        }

        if (empty($this->scopeType) || empty($this->scopeTargetId)) {
            Notification::make()
                ->title('Pilih Lingkup & Target Terlebih Dahulu')
                ->body('Silakan pilih Kelas atau Kelompok Guru Wali sebelum memproses analisa.')
                ->warning()
                ->send();

            return;
        }

        if (empty($this->focusFilter)) {
            Notification::make()
                ->title('Pilih Fokus Tampilan Terlebih Dahulu')
                ->body('Silakan pilih Fokus Tampilan sebelum memproses analisa.')
                ->warning()
                ->send();

            return;
        }

        $this->isProcessed = true;
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->selectedPeriod = '';
        $this->scopeType = '';
        $this->scopeTargetId = '';
        $this->focusFilter = '';
        $this->selectedStudentId = '';
        $this->search = '';
        $this->timelineModal = null;
        $this->isProcessed = false;
    }

    public function updatedSelectedPeriod(): void
    {
        $this->scopeType = '';
        $this->scopeTargetId = '';
        $this->focusFilter = '';
        $this->selectedStudentId = '';
        $this->search = '';
        $this->timelineModal = null;
        $this->isProcessed = false;
    }

    public function updatedScopeType(): void
    {
        $this->scopeTargetId = '';
        $this->focusFilter = '';
        $this->selectedStudentId = '';
        $this->search = '';
        $this->timelineModal = null;
        $this->isProcessed = false;
    }

    public function updatedScopeTargetId(): void
    {
        $this->focusFilter = '';
        $this->selectedStudentId = '';
        $this->search = '';
        $this->timelineModal = null;
        $this->isProcessed = false;
    }

    public function updatedFocusFilter(): void
    {
        $this->isProcessed = false;
    }

    public function updatedSearch(): void
    {
        $this->selectedStudentId = '';
    }

    public function selectStudent(?string $studentId, string $label = ''): void
    {
        $this->selectedStudentId = $studentId ?? '';
        $this->search = $label;
    }

    public function clearStudentSelection(): void
    {
        $this->selectedStudentId = '';
        $this->search = '';
    }

    public function openTimeline(string $studentId): void
    {
        $siswa = Siswa::with(['kelompokGuruWali.kelompok.guru', 'enrollmentAktif.kelas'])->find($studentId);
        if (! $siswa) {
            return;
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        [$startDate, $endDate, $periodLabel] = self::resolveGuruWaliDateRange($this->selectedPeriod ?: 'tahunan', $ay);

        $jurnals = JurnalGuruWali::with(['guru:id,name', 'konselingBk:id,jurnal_guru_wali_id,status_kasus,topik_masalah'])
            ->where('student_id', $studentId)
            ->whereBetween('tanggal_waktu', [$startDate, $endDate])
            ->orderBy('tanggal_waktu', 'desc')
            ->get()
            ->map(function ($j) {
                $sentimen = $j->kategori_sentimen instanceof KategoriSentimen
                    ? $j->kategori_sentimen->value
                    : ($j->kategori_sentimen ?? '-');
                $bkStatus = $j->konselingBk?->status_kasus instanceof \BackedEnum
                    ? $j->konselingBk->status_kasus->value
                    : ($j->konselingBk?->status_kasus ?? null);

                return [
                    'tanggal'  => $j->tanggal_waktu?->translatedFormat('d M Y H:i') ?? '-',
                    'guru'     => $j->guru?->name ?? '-',
                    'jenis'    => $j->jenis_pendampingan,
                    'pilar'    => $j->kategori_pendampingan,
                    'sentimen' => $sentimen,
                    'status'   => $j->status_sesi,
                    'rujukan'  => $j->rujukan_kolaborasi ?? 'Mandiri',
                    'uraian'   => $j->uraian_pembahasan,
                    'rtl'      => $j->rencana_tindak_lanjut,
                    'bk'       => $bkStatus,
                ];
            })
            ->toArray();

        $konsultasis = KonsultasiGuruWali::where('student_id', $studentId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($k) {
                $status = $k->status_pengajuan instanceof StatusPengajuan
                    ? $k->status_pengajuan->value
                    : ($k->status_pengajuan ?? '-');
                $emoji = $k->student_feedback_emoji instanceof StudentFeedbackEmoji
                    ? $k->student_feedback_emoji->value
                    : ($k->student_feedback_emoji ?? null);

                return [
                    'tanggal'  => $k->created_at?->translatedFormat('d M Y H:i') ?? '-',
                    'pilar'    => $k->kategori_pendampingan,
                    'mode'     => $k->mode_konsultasi,
                    'topik'    => $k->topik_konsultasi,
                    'detail'   => $k->detail_permasalahan,
                    'status'   => $status,
                    'tanggapan'=> $k->tanggapan_guru,
                    'rating'   => $k->student_feedback_rating,
                    'emoji'    => $emoji,
                ];
            })
            ->toArray();

        $badges = BadgeKarakterSiswa::where('student_id', $studentId)
            ->when($this->selectedAcademicYearId, fn ($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->latest()
            ->get(['nama_badge', 'catatan_apresiasi', 'created_at'])
            ->map(fn ($b) => [
                'nama'    => $b->nama_badge,
                'catatan' => $b->catatan_apresiasi,
                'tanggal' => $b->created_at?->translatedFormat('d M Y') ?? '-',
            ])
            ->toArray();

        $kelompok = $siswa->kelompokGuruWali?->kelompok;

        $this->timelineModal = [
            'student_id'   => $siswa->id,
            'kelompok_id'  => $kelompok?->id,
            'name'         => $siswa->name,
            'nisn'         => $siswa->nisn ?: '-',
            'kelas'        => $siswa->getRombelName($this->selectedAcademicYearId) ?? '-',
            'guru_wali'    => $kelompok?->guru?->name ?? 'Belum diplotting',
            'kelompok'     => $kelompok?->nama_kelompok ?? '-',
            'period_label' => $periodLabel,
            'jurnals'      => $jurnals,
            'konsultasis'  => $konsultasis,
            'badges'       => $badges,
        ];
    }

    public function closeTimeline(): void
    {
        $this->timelineModal = null;
    }

    protected function resolveScopedStudents(?TahunAjaran $ay)
    {
        if (empty($this->scopeType) || empty($this->scopeTargetId)) {
            return collect();
        }

        if ($this->scopeType === 'kelas') {
            return EnrollmentSiswa::query()
                ->with(['student.kelompokGuruWali.kelompok.guru', 'kelas'])
                ->where('class_id', $this->scopeTargetId)
                ->when($ay?->id, fn ($q) => $q->where('academic_year_id', $ay->id))
                ->where('status', 'aktif')
                ->get()
                ->filter(fn ($e) => $e->student !== null)
                ->map(fn ($e) => [
                    'student'     => $e->student,
                    'kelas_name'  => $e->kelas?->name ?? '-',
                    'kelompok_id' => $e->student->kelompokGuruWali?->kelompok_id,
                    'kelompok'    => $e->student->kelompokGuruWali?->kelompok?->nama_kelompok ?? 'Belum Masuk Kelompok',
                    'guru_wali'   => $e->student->kelompokGuruWali?->kelompok?->guru?->name ?? '-',
                ])
                ->sortBy(fn ($r) => $r['student']->name)
                ->values();
        }

        $kelompok = KelompokGuruWali::with([
            'guru:id,name',
            'anggotaAktif.siswa.enrollmentAktif.kelas',
        ])->find($this->scopeTargetId);

        if (! $kelompok) {
            return collect();
        }

        return $kelompok->anggotaAktif
            ->filter(fn ($a) => $a->siswa !== null)
            ->map(fn ($a) => [
                'student'     => $a->siswa,
                'kelas_name'  => $a->siswa->getRombelName($ay?->id) ?? '-',
                'kelompok_id' => $kelompok->id,
                'kelompok'    => $kelompok->nama_kelompok,
                'guru_wali'   => $kelompok->guru?->name ?? '-',
            ])
            ->sortBy(fn ($r) => $r['student']->name)
            ->values();
    }

    protected function getViewData(): array
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId) ?? self::getActiveGuruWaliAcademicYear();
        $academicYearOptions = self::getAllGuruWaliAcademicYears();
        $periodOptions = self::buildGuruWaliPeriodOptions($ay, true, true);
        $classes = Kelas::orderBy('grade_level')->orderBy('name')->get(['id', 'name']);
        $kelompokList = KelompokGuruWali::with('guru:id,name')->orderBy('nama_kelompok')->get(['id', 'nama_kelompok', 'teacher_id']);

        $scopedStudents = $this->resolveScopedStudents($ay);
        $studentDropdownOptions = $scopedStudents
            ->map(fn ($item) => [
                'id'    => $item['student']->id,
                'name'  => $item['student']->name,
                'nisn'  => $item['student']->nisn ?: '-',
                'label' => $item['student']->name . ($item['student']->nisn ? " ({$item['student']->nisn})" : ''),
            ])
            ->when(trim($this->search) !== '' && empty($this->selectedStudentId), function ($col) {
                $q = mb_strtolower(trim($this->search));

                return $col->filter(fn ($s) => str_contains(mb_strtolower($s['name']), $q) || str_contains(mb_strtolower($s['nisn']), $q));
            })
            ->values();

        $emptySummary = [
            'pilar'       => [
                'Akademik'                => 0,
                'Karakter & Kedisiplinan' => 0,
                'Minat & Bakat / Ekskul'  => 0,
                'Sosial & Psikologis'     => 0,
            ],
            'sentimen'    => ['Positif' => 0, 'Netral' => 0, 'Negatif' => 0],
            'konsultasi'  => ['total' => 0, 'aktif' => 0, 'selesai' => 0, 'avg_rating' => '-'],
            'emoji'       => ['Lega' => 0, 'Biasa' => 0, 'Masih Bingung' => 0],
        ];

        if (! $this->isProcessed || empty($this->selectedPeriod) || empty($this->scopeType) || empty($this->scopeTargetId) || empty($this->focusFilter)) {
            return [
                'activeYearId'           => $ay?->id,
                'activeYearName'         => $ay?->name ?? 'Aktif',
                'academicYearOptions'    => $academicYearOptions,
                'periodOptions'          => $periodOptions,
                'periodLabel'            => '-',
                'classes'                => $classes,
                'kelompokList'           => $kelompokList,
                'studentDropdownOptions' => $studentDropdownOptions,
                'summary'                => $emptySummary,
                'rows'                   => collect(),
            ];
        }

        [$startDate, $endDate, $periodLabel] = self::resolveGuruWaliDateRange($this->selectedPeriod, $ay);
        $studentIds = $scopedStudents->pluck('student.id')->filter()->values()->toArray();

        $jurnalsByStudent = empty($studentIds)
            ? collect()
            : JurnalGuruWali::with('konselingBk:id,jurnal_guru_wali_id,status_kasus')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('tanggal_waktu', [$startDate, $endDate])
                ->orderBy('tanggal_waktu', 'desc')
                ->get()
                ->groupBy('student_id');

        $konsultasiByStudent = empty($studentIds)
            ? collect()
            : KonsultasiGuruWali::whereIn('student_id', $studentIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('student_id');

        $badgesCountByStudent = empty($studentIds)
            ? collect()
            : BadgeKarakterSiswa::whereIn('student_id', $studentIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('student_id, COUNT(*) as total')
                ->groupBy('student_id')
                ->pluck('total', 'student_id');

        // Summary aggregates across scoped students
        $allJurnals = $jurnalsByStudent->flatten();
        $allKonsultasis = $konsultasiByStudent->flatten();

        foreach (array_keys($emptySummary['pilar']) as $pilar) {
            $emptySummary['pilar'][$pilar] = $allJurnals->where('kategori_pendampingan', $pilar)->count();
        }
        foreach ($allJurnals as $j) {
            $s = $j->kategori_sentimen instanceof KategoriSentimen ? $j->kategori_sentimen->value : ($j->kategori_sentimen ?? 'Netral');
            if (isset($emptySummary['sentimen'][$s])) {
                $emptySummary['sentimen'][$s]++;
            }
        }

        $selesaiStatuses = [StatusPengajuan::Selesai->value, StatusPengajuan::DikonversiKeJurnal->value];
        $aktifStatuses = [StatusPengajuan::MenungguKonfirmasi->value, StatusPengajuan::Dijadwalkan->value];
        $rated = $allKonsultasis->whereNotNull('student_feedback_rating');

        $emptySummary['konsultasi'] = [
            'total'      => $allKonsultasis->count(),
            'aktif'      => $allKonsultasis->filter(fn ($k) => in_array($k->status_pengajuan instanceof StatusPengajuan ? $k->status_pengajuan->value : $k->status_pengajuan, $aktifStatuses, true))->count(),
            'selesai'    => $allKonsultasis->filter(fn ($k) => in_array($k->status_pengajuan instanceof StatusPengajuan ? $k->status_pengajuan->value : $k->status_pengajuan, $selesaiStatuses, true))->count(),
            'avg_rating' => $rated->isNotEmpty() ? number_format($rated->avg('student_feedback_rating'), 1) . ' / 5' : '-',
        ];

        foreach ($allKonsultasis as $k) {
            $emoji = $k->student_feedback_emoji instanceof StudentFeedbackEmoji ? $k->student_feedback_emoji->value : $k->student_feedback_emoji;
            if ($emoji && isset($emptySummary['emoji'][$emoji])) {
                $emptySummary['emoji'][$emoji]++;
            }
        }

        $rows = $scopedStudents
            ->map(function ($item) use ($jurnalsByStudent, $konsultasiByStudent, $badgesCountByStudent, $aktifStatuses) {
                $s = $item['student'];
                $jurnals = $jurnalsByStudent->get($s->id, collect());
                $konsultasis = $konsultasiByStudent->get($s->id, collect());
                $lastJ = $jurnals->first();
                $lastK = $konsultasis->first();

                $sentimenLast = $lastJ?->kategori_sentimen instanceof KategoriSentimen
                    ? $lastJ->kategori_sentimen->value
                    : ($lastJ?->kategori_sentimen ?? null);

                $hasNegatif = $jurnals->contains(fn ($j) => ($j->kategori_sentimen instanceof KategoriSentimen ? $j->kategori_sentimen->value : $j->kategori_sentimen) === 'Negatif');
                $hasPemantauan = $jurnals->contains(fn ($j) => in_array($j->status_sesi, ['Dalam Pemantauan', 'Bimbingan Lanjutan'], true));
                $hasRujukan = $jurnals->contains(fn ($j) => ($j->rujukan_kolaborasi ?? 'Mandiri') !== 'Mandiri');
                $hasActiveConsult = $konsultasis->contains(fn ($k) => in_array($k->status_pengajuan instanceof StatusPengajuan ? $k->status_pengajuan->value : $k->status_pengajuan, $aktifStatuses, true));

                $needsAttention = $hasNegatif || $hasPemantauan || $hasRujukan;

                $lastKonsultasiStatus = $lastK
                    ? ($lastK->status_pengajuan instanceof StatusPengajuan ? $lastK->status_pengajuan->value : $lastK->status_pengajuan)
                    : null;

                $lastEmoji = $lastK?->student_feedback_emoji instanceof StudentFeedbackEmoji
                    ? $lastK->student_feedback_emoji->value
                    : $lastK?->student_feedback_emoji;

                return [
                    'student_id'             => $s->id,
                    'name'                   => $s->name,
                    'nisn'                   => $s->nisn ?: '-',
                    'kelas_name'             => $item['kelas_name'],
                    'kelompok_id'            => $item['kelompok_id'],
                    'kelompok'               => $item['kelompok'],
                    'guru_wali'              => $item['guru_wali'],
                    'total_jurnal'           => $jurnals->count(),
                    'last_tanggal'           => $lastJ ? Carbon::parse($lastJ->tanggal_waktu)->translatedFormat('d M Y') : '—',
                    'last_pilar'             => $lastJ?->kategori_pendampingan ?? '—',
                    'last_status'            => $lastJ?->status_sesi ?? 'Belum ada sesi',
                    'last_sentimen'          => $sentimenLast,
                    'last_rujukan'           => $lastJ?->rujukan_kolaborasi ?? 'Mandiri',
                    'has_bk'                 => $jurnals->contains(fn ($j) => $j->konselingBk !== null),
                    'total_konsultasi'       => $konsultasis->count(),
                    'has_active_consult'     => $hasActiveConsult,
                    'last_konsultasi_status' => $lastKonsultasiStatus,
                    'last_rating'            => $lastK?->student_feedback_rating,
                    'last_emoji'             => $lastEmoji,
                    'total_badge'            => (int) ($badgesCountByStudent[$s->id] ?? 0),
                    'needs_attention'        => $needsAttention,
                ];
            })
            ->filter(function ($r) {
                if (! empty($this->selectedStudentId) && $r['student_id'] !== $this->selectedStudentId) {
                    return false;
                }

                if (empty($this->selectedStudentId) && trim($this->search) !== '') {
                    $q = mb_strtolower(trim($this->search));
                    if (! str_contains(mb_strtolower($r['name']), $q) && ! str_contains(mb_strtolower($r['nisn']), $q)) {
                        return false;
                    }
                }

                return match ($this->focusFilter) {
                    'unserved'       => $r['total_jurnal'] === 0,
                    'attention'      => $r['needs_attention'],
                    'consult_active' => $r['total_konsultasi'] > 0,
                    default          => true,
                };
            })
            ->values();

        return [
            'activeYearId'           => $ay?->id,
            'activeYearName'         => $ay?->name ?? 'Aktif',
            'academicYearOptions'    => $academicYearOptions,
            'periodOptions'          => $periodOptions,
            'periodLabel'            => $periodLabel,
            'classes'                => $classes,
            'kelompokList'           => $kelompokList,
            'studentDropdownOptions' => $studentDropdownOptions,
            'summary'                => $emptySummary,
            'rows'                   => $rows,
        ];
    }
}

