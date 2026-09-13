<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use App\Models\KonsultasiGuruWali;
use App\Models\PengaturanSekolah;
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

    // Form Filters - Individual
    public $selectedStudentId = null;
    public $selectedPeriode = 'tahunan'; // 'tahunan', 'semester_1', 'semester_2'
    public $selectedAcademicYearId = null;

    // Form Filters - Kelompok
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

        // Default siswa pertama di kelompok
        $firstMember = $this->kelompok->anggotaAktif()->first();
        if ($firstMember) {
            $this->selectedStudentId = $firstMember->student_id;
        }
    }

    public function setTab($tab)
    {
        if (in_array($tab, ['individual', 'kelompok'])) {
            $this->activeTab = $tab;
        }
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

        // Daftar anggota siswa aktif
        $anggotaAktif = $this->kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get();

        // -------------------------------------------------------------
        // Data Pratinjau Individual
        // -------------------------------------------------------------
        $selectedSiswa = null;
        $individualMetrics = null;
        $recentJurnals = collect();
        $selectedTahun = $academicYears->firstWhere('id', $this->selectedAcademicYearId) 
            ?? $academicYears->firstWhere('status', 'aktif');

        if ($this->selectedStudentId) {
            $selectedSiswa = Siswa::with(['enrollmentAktif.kelas', 'enrollmentAktif.tahunAjaran'])
                ->find($this->selectedStudentId);

            if ($selectedSiswa) {
                $indDateRange = $this->getDateRange($this->selectedPeriode, $selectedTahun);

                $jurnalsInd = JurnalGuruWali::where('student_id', $selectedSiswa->id)
                    ->where('teacher_id', $this->teacher->id)
                    ->whereBetween('tanggal_waktu', [$indDateRange['startDate'], $indDateRange['endDate']])
                    ->orderBy('tanggal_waktu', 'desc')
                    ->get();

                $konsultasisInd = KonsultasiGuruWali::where('student_id', $selectedSiswa->id)
                    ->where('teacher_id', $this->teacher->id)
                    ->whereBetween('created_at', [$indDateRange['startDate'], $indDateRange['endDate']])
                    ->get();

                $individualMetrics = [
                    'total_sesi'        => $jurnalsInd->count(),
                    'total_konsultasi'  => $konsultasisInd->count(),
                    'pilar_akademik'    => $jurnalsInd->where('kategori_pendampingan', 'Akademik')->count(),
                    'pilar_karakter'    => $jurnalsInd->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
                    'pilar_minat'       => $jurnalsInd->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
                    'pilar_sosial'      => $jurnalsInd->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
                    'label_periode'     => $indDateRange['labelPeriode'],
                ];

                $recentJurnals = $jurnalsInd->take(5);
            }
        }

        // -------------------------------------------------------------
        // Data Pratinjau Kelompok
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
            'selectedStudentId'             => $this->selectedStudentId,
            'selectedPeriode'               => $this->selectedPeriode,
            'selectedAcademicYearId'        => $this->selectedAcademicYearId,
            'selectedKelompokPeriode'       => $this->selectedKelompokPeriode,
            'selectedKelompokAcademicYearId'=> $this->selectedKelompokAcademicYearId,
            'catatanRefleksi'               => $this->catatanRefleksi,
            'academicYears'                 => $academicYears,
            'anggotaAktif'                  => $anggotaAktif,
            'selectedSiswa'                 => $selectedSiswa,
            'selectedTahun'                 => $selectedTahun,
            'individualMetrics'             => $individualMetrics,
            'recentJurnals'                 => $recentJurnals,
            'kelompokMetrics'               => $kelompokMetrics,
            'selectedKelompokTahun'         => $selectedKelompokTahun,
        ]);
    }
}
