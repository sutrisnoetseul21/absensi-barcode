<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use App\Models\KonsultasiGuruWali;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CetakLaporanGuruWali extends Component
{
    public $teacher;
    public $kelompok;
    public $activeTab = 'individual'; // 'individual' or 'kelompok'

    // Form Filters - Tab 1: Tabel Individual
    public $studentSearch = '';
    public $selectedPeriode = 'tahunan'; // 'tahunan', 'semester_1', 'semester_2'
    public $selectedAcademicYearId = null;
    public $previewStudentId = null;

    // Form Filters - Tab 2: Kelompok
    public $selectedKelompokPeriode = 'tahunan';
    public $selectedKelompokAcademicYearId = null;
    public $catatanRefleksi = '';

    public function mount()
    {
        $user = Auth::user();
        $this->teacher = $user?->teacher;

        if (! $this->teacher) {
            abort(403, 'Anda tidak terdaftar sebagai guru.');
        }

        $this->kelompok = KelompokGuruWali::where('teacher_id', $this->teacher->id)->first();

        if (! $this->kelompok || ! $this->kelompok->status_aktif) {
            abort(403, 'Anda belum memiliki tugas penugasan Guru Wali aktif.');
        }

        // Default tahun ajaran aktif
        $activeTahun = TahunAjaran::where('status', 'aktif')->first();
        if ($activeTahun) {
            $this->selectedAcademicYearId = $activeTahun->id;
            $this->selectedKelompokAcademicYearId = $activeTahun->id;
        }
    }

    public function setTab($tab)
    {
        if (in_array($tab, ['individual', 'kelompok'])) {
            $this->activeTab = $tab;
        }
    }

    public function showDetail($studentId)
    {
        $this->previewStudentId = $studentId;
    }

    public function closeDetail()
    {
        $this->previewStudentId = null;
    }

    protected function getDateRange($periode, $tahunAjaran)
    {
        $startYear = $tahunAjaran?->start_year ?? Carbon::now()->year;
        $endYear = $tahunAjaran?->end_year ?? ($startYear + 1);

        if ($periode === 'semester_1') {
            return [
                'startDate'    => Carbon::createFromDate($startYear, 7, 1)->startOfDay(),
                'endDate'      => Carbon::createFromDate($startYear, 12, 31)->endOfDay(),
                'labelPeriode' => 'Semester 1 (Ganjil)',
            ];
        }

        if ($periode === 'semester_2') {
            return [
                'startDate'    => Carbon::createFromDate($endYear, 1, 1)->startOfDay(),
                'endDate'      => Carbon::createFromDate($endYear, 6, 30)->endOfDay(),
                'labelPeriode' => 'Semester 2 (Genap)',
            ];
        }

        return [
            'startDate'    => Carbon::createFromDate($startYear, 7, 1)->startOfDay(),
            'endDate'      => Carbon::createFromDate($endYear, 6, 30)->endOfDay(),
            'labelPeriode' => '1 Tahun Penuh (Tahunan)',
        ];
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();

        // Anggota Siswa Aktif Kelompok Ini
        $anggotaAktif = $this->kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get();

        // -------------------------------------------------------------
        // TAB 1: DATA TABEL INDIVIDUAL SELURUH SISWA
        // -------------------------------------------------------------
        $selectedTahun = $academicYears->firstWhere('id', $this->selectedAcademicYearId) 
            ?? $academicYears->firstWhere('status', 'aktif');

        $indDateRange = $this->getDateRange($this->selectedPeriode, $selectedTahun);

        // Ambil semua jurnal periode ini untuk kelompok ini sekaligus (efisien)
        $allJurnalsPeriode = JurnalGuruWali::where('teacher_id', $this->teacher->id)
            ->where('kelompok_id', $this->kelompok->id)
            ->whereBetween('tanggal_waktu', [$indDateRange['startDate'], $indDateRange['endDate']])
            ->orderBy('tanggal_waktu', 'desc')
            ->get()
            ->groupBy('student_id');

        // Ambil semua konsultasi mandiri periode ini
        $allKonsultasiPeriode = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
            ->where('kelompok_id', $this->kelompok->id)
            ->whereBetween('created_at', [$indDateRange['startDate'], $indDateRange['endDate']])
            ->get()
            ->groupBy('student_id');

        $tabelSiswa = $anggotaAktif->map(function ($anggota) use ($allJurnalsPeriode, $allKonsultasiPeriode, $selectedTahun) {
            $siswa = $anggota->siswa;
            $jurnals = $allJurnalsPeriode->get($siswa->id, collect());
            $konsultasis = $allKonsultasiPeriode->get($siswa->id, collect());
            $lastSesi = $jurnals->first();

            return (object) [
                'id'               => $siswa->id,
                'nama'             => $siswa->name,
                'avatar_url'       => $siswa->avatar_url,
                'nis'              => $siswa->nis ?? '—',
                'nisn'             => $siswa->nisn ?? '—',
                'kelas'            => $siswa->enrollmentAktif?->kelas?->name ?? 'Tanpa Kelas',
                'total_sesi'       => $jurnals->count(),
                'pilar_akademik'   => $jurnals->where('kategori_pendampingan', 'Akademik')->count(),
                'pilar_karakter'   => $jurnals->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
                'pilar_minat'      => $jurnals->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
                'pilar_sosial'     => $jurnals->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
                'total_konsultasi' => $konsultasis->count(),
                'last_sesi_date'   => $lastSesi ? Carbon::parse($lastSesi->tanggal_waktu)->translatedFormat('d M Y') : '—',
                'last_status'      => $lastSesi?->status_sesi ?? 'Belum ada sesi',
                'print_url'        => route('portal-guru.guru-wali.cetak.individual.print', [
                    'student_id'       => $siswa->id,
                    'periode'          => $this->selectedPeriode,
                    'academic_year_id' => $selectedTahun?->id,
                    'autoprint'        => '1',
                ]),
                'pdf_url'          => route('portal-guru.guru-wali.cetak.individual.pdf', [
                    'student_id'       => $siswa->id,
                    'periode'          => $this->selectedPeriode,
                    'academic_year_id' => $selectedTahun?->id,
                ]),
            ];
        });

        // Filter pencarian
        if (!empty(trim($this->studentSearch))) {
            $searchTerm = strtolower(trim($this->studentSearch));
            $tabelSiswa = $tabelSiswa->filter(function ($item) use ($searchTerm) {
                return str_contains(strtolower($item->nama), $searchTerm)
                    || str_contains(strtolower($item->nisn), $searchTerm)
                    || str_contains(strtolower($item->nis), $searchTerm)
                    || str_contains(strtolower($item->kelas), $searchTerm);
            });
        }

        // Modal Preview Siswa (jika tombol Detail diklik)
        $previewSiswa = null;
        $previewJurnals = collect();
        $previewKonsultasis = collect();
        if ($this->previewStudentId) {
            $previewSiswa = Siswa::with(['enrollmentAktif.kelas'])->find($this->previewStudentId);
            if ($previewSiswa) {
                $previewJurnals = JurnalGuruWali::where('student_id', $previewSiswa->id)
                    ->where('teacher_id', $this->teacher->id)
                    ->whereBetween('tanggal_waktu', [$indDateRange['startDate'], $indDateRange['endDate']])
                    ->orderBy('tanggal_waktu', 'desc')
                    ->get();

                $previewKonsultasis = KonsultasiGuruWali::where('student_id', $previewSiswa->id)
                    ->where('teacher_id', $this->teacher->id)
                    ->whereBetween('created_at', [$indDateRange['startDate'], $indDateRange['endDate']])
                    ->get();
            }
        }

        // -------------------------------------------------------------
        // TAB 2: DATA LAPORAN KINERJA KELOMPOK
        // -------------------------------------------------------------
        $selectedKelompokTahun = $academicYears->firstWhere('id', $this->selectedKelompokAcademicYearId)
            ?? $academicYears->firstWhere('status', 'aktif');

        $kelDateRange = $this->getDateRange($this->selectedKelompokPeriode, $selectedKelompokTahun);

        $jurnalsKelompok = JurnalGuruWali::where('kelompok_id', $this->kelompok->id)
            ->where('teacher_id', $this->teacher->id)
            ->whereBetween('tanggal_waktu', [$kelDateRange['startDate'], $kelDateRange['endDate']])
            ->get();

        $totalSesiKelompok = $jurnalsKelompok->count();
        $totalSiswaKelompok = $anggotaAktif->count();
        $siswaIdsDenganSesi = $jurnalsKelompok->pluck('student_id')->unique()->count();
        $persentaseKepatuhanKelompok = $totalSiswaKelompok > 0 
            ? round(($siswaIdsDenganSesi / $totalSiswaKelompok) * 100) 
            : 0;

        $kelompokMetrics = [
            'total_siswa'          => $totalSiswaKelompok,
            'siswa_didampingi'     => $siswaIdsDenganSesi,
            'persentase_kepatuhan' => $persentaseKepatuhanKelompok,
            'total_sesi'           => $totalSesiKelompok,
            'sesi_individu'        => $jurnalsKelompok->where('jenis_pendampingan', 'Individu')->count(),
            'sesi_kelompok'        => $jurnalsKelompok->whereIn('jenis_pendampingan', ['Kelompok Kecil', 'Klasikal'])->count(),
            'pilar_akademik'       => $jurnalsKelompok->where('kategori_pendampingan', 'Akademik')->count(),
            'pilar_karakter'       => $jurnalsKelompok->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
            'pilar_minat'          => $jurnalsKelompok->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
            'pilar_sosial'         => $jurnalsKelompok->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
            'rujukan_bk'           => $jurnalsKelompok->where('rujukan_kolaborasi', 'Guru BK')->count(),
            'rujukan_wali_kelas'   => $jurnalsKelompok->where('rujukan_kolaborasi', 'Wali Kelas')->count(),
            'rujukan_orang_tua'    => $jurnalsKelompok->where('rujukan_kolaborasi', 'Orang Tua / Wali')->count(),
            'label_periode'        => $kelDateRange['labelPeriode'],
        ];

        return view('livewire.portal-guru.guru-wali.cetak-laporan-guru-wali', [
            'teacher'                       => $this->teacher,
            'kelompok'                      => $this->kelompok,
            'activeTab'                     => $this->activeTab,
            'studentSearch'                 => $this->studentSearch,
            'selectedPeriode'               => $this->selectedPeriode,
            'selectedAcademicYearId'        => $this->selectedAcademicYearId,
            'selectedKelompokPeriode'       => $this->selectedKelompokPeriode,
            'selectedKelompokAcademicYearId'=> $this->selectedKelompokAcademicYearId,
            'catatanRefleksi'               => $this->catatanRefleksi,
            'previewStudentId'              => $this->previewStudentId,
            'previewSiswa'                  => $previewSiswa,
            'previewJurnals'                => $previewJurnals,
            'previewKonsultasis'            => $previewKonsultasis,
            'academicYears'                 => $academicYears,
            'anggotaAktif'                  => $anggotaAktif,
            'tabelSiswa'                    => $tabelSiswa,
            'selectedTahun'                 => $selectedTahun,
            'indLabelPeriode'               => $indDateRange['labelPeriode'],
            'kelompokMetrics'               => $kelompokMetrics,
            'selectedKelompokTahun'         => $selectedKelompokTahun,
        ]);
    }
}
