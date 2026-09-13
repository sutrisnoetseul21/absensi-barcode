<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use App\Models\KonsultasiGuruWali;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruWaliCetakController extends Controller
{
    /**
     * Memverifikasi penugasan guru wali dan mengambil entitas terkait
     */
    protected function getTeacherAndKelompok()
    {
        $user = Auth::user();
        if (! $user || ! $user->teacher) {
            abort(403, 'Akses ditolak. Hanya guru yang dapat mengakses modul ini.');
        }

        $teacher = $user->teacher;
        $kelompok = KelompokGuruWali::where('teacher_id', $teacher->id)->first();

        if (! $kelompok || ! $kelompok->status_aktif) {
            abort(403, 'Anda belum memiliki tugas penugasan Guru Wali yang aktif.');
        }

        return [$teacher, $kelompok];
    }

    /**
     * Helper untuk mengambil data sekolah, kepala sekolah, dan logo base64
     */
    protected function getSchoolAndPrincipalData()
    {
        $settings = PengaturanSekolah::first();

        $kepsek = Guru::whereHas('jabatans', function ($q) {
            $q->where('nama_jabatan', 'like', '%Kepala Sekolah%')
                ->where(function ($q2) {
                    $q2->whereNull('teacher_jabatan.tanggal_selesai')
                        ->orWhere('teacher_jabatan.tanggal_selesai', '>=', now()->toDateString());
                });
        })->first();

        $namaKepsek = $kepsek ? $kepsek->name : ($settings?->principal_name ?? config('school.kepala_sekolah_nama', 'Kepala Sekolah'));
        $nipKepsek = $kepsek ? $kepsek->nip : ($settings?->principal_nip ?? config('school.kepala_sekolah_nip', '—'));
        $namaKota = $settings?->tempat_rapor ?? ($settings?->kota ?? config('school.kota', 'Kedungreja'));

        $logoBase64 = null;
        if ($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path))) {
            $logoPath = public_path('storage/' . $settings->school_logo_path);
            $ext = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/' . ($ext === 'jpg' ? 'jpeg' : $ext) . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        return [
            'settings'   => $settings,
            'namaKepsek' => $namaKepsek,
            'nipKepsek'  => $nipKepsek,
            'namaKota'   => $namaKota,
            'logoBase64' => $logoBase64,
        ];
    }

    /**
     * Menghitung rentang tanggal berdasarkan periode dan tahun ajaran
     */
    protected function resolveDateRange($periode, TahunAjaran $tahunAjaran)
    {
        $startYear = $tahunAjaran->start_year ?? Carbon::now()->year;
        $endYear = $tahunAjaran->end_year ?? ($startYear + 1);

        if ($periode === 'semester_1') {
            return [
                'startDate'   => Carbon::createFromDate($startYear, 7, 1)->startOfDay(),
                'endDate'     => Carbon::createFromDate($startYear, 12, 31)->endOfDay(),
                'labelPeriode'=> 'Semester 1 (Ganjil)',
            ];
        }

        if ($periode === 'semester_2') {
            return [
                'startDate'   => Carbon::createFromDate($endYear, 1, 1)->startOfDay(),
                'endDate'     => Carbon::createFromDate($endYear, 6, 30)->endOfDay(),
                'labelPeriode'=> 'Semester 2 (Genap)',
            ];
        }

        // Default tahunan
        return [
            'startDate'   => Carbon::createFromDate($startYear, 7, 1)->startOfDay(),
            'endDate'     => Carbon::createFromDate($endYear, 6, 30)->endOfDay(),
            'labelPeriode'=> '1 Tahun Ajaran Penuh (Tahunan)',
        ];
    }

    /**
     * Menyiapkan data laporan individual siswa
     */
    protected function prepareIndividualData(Request $request)
    {
        [$teacher, $kelompok] = $this->getTeacherAndKelompok();
        $schoolData = $this->getSchoolAndPrincipalData();

        $studentId = $request->query('student_id');
        if (! $studentId) {
            abort(400, 'Parameter student_id wajib disertakan.');
        }

        // Pastikan siswa terdaftar di kelompok guru wali ini
        $anggotaSiswa = $kelompok->anggotaAktif()
            ->where('student_id', $studentId)
            ->first();

        if (! $anggotaSiswa) {
            abort(403, 'Peserta didik tidak terdaftar dalam kelompok Guru Wali Anda.');
        }

        $siswa = Siswa::with(['enrollmentAktif.kelas', 'enrollmentAktif.tahunAjaran'])->findOrFail($studentId);

        // Tahun Ajaran
        $academicYearId = $request->query('academic_year_id');
        $tahunAjaran = $academicYearId 
            ? TahunAjaran::find($academicYearId) 
            : TahunAjaran::where('status', 'aktif')->first();

        if (! $tahunAjaran) {
            $tahunAjaran = (object) [
                'id'         => null,
                'name'       => Carbon::now()->year . '/' . (Carbon::now()->year + 1),
                'start_year' => Carbon::now()->year,
                'end_year'   => Carbon::now()->year + 1,
            ];
        }

        $periode = $request->query('periode', 'tahunan');
        $dateRange = $this->resolveDateRange($periode, $tahunAjaran);

        // Kueri Jurnal untuk siswa ini
        $jurnals = JurnalGuruWali::where('student_id', $siswa->id)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('tanggal_waktu', [$dateRange['startDate'], $dateRange['endDate']])
            ->orderBy('tanggal_waktu', 'asc')
            ->get();

        // Bagi berdasarkan semester 1 (Juli-Desember) dan semester 2 (Januari-Juni)
        $semester1Jurnals = $jurnals->filter(function ($j) {
            $m = Carbon::parse($j->tanggal_waktu)->month;
            return $m >= 7 && $m <= 12;
        });

        $semester2Jurnals = $jurnals->filter(function ($j) {
            $m = Carbon::parse($j->tanggal_waktu)->month;
            return $m >= 1 && $m <= 6;
        });

        // Kueri Konsultasi Mandiri Siswa
        $konsultasis = KonsultasiGuruWali::where('student_id', $siswa->id)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('created_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->orderBy('created_at', 'asc')
            ->get();

        // Distribusi 4 Pilar
        $distribusiPilar = [
            'Akademik'                => $jurnals->where('kategori_pendampingan', 'Akademik')->count(),
            'Karakter & Kedisiplinan' => $jurnals->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
            'Minat & Bakat / Ekskul'  => $jurnals->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
            'Sosial & Psikologis'     => $jurnals->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
        ];

        return array_merge($schoolData, [
            'teacher'          => $teacher,
            'kelompok'         => $kelompok,
            'siswa'            => $siswa,
            'tahunAjaran'      => $tahunAjaran,
            'periode'          => $periode,
            'labelPeriode'     => $dateRange['labelPeriode'],
            'startDate'        => $dateRange['startDate'],
            'endDate'          => $dateRange['endDate'],
            'jurnals'          => $jurnals,
            'semester1Jurnals' => $semester1Jurnals,
            'semester2Jurnals' => $semester2Jurnals,
            'konsultasis'      => $konsultasis,
            'distribusiPilar'  => $distribusiPilar,
            'kelas'            => $siswa->enrollmentAktif?->kelas?->name ?? '—',
            'tanggalCetak'     => Carbon::now()->translatedFormat('d F Y'),
        ]);
    }

    /**
     * Menyiapkan data laporan kelompok tahunan / periodik
     */
    protected function prepareKelompokData(Request $request)
    {
        [$teacher, $kelompok] = $this->getTeacherAndKelompok();
        $schoolData = $this->getSchoolAndPrincipalData();

        $academicYearId = $request->query('academic_year_id');
        $tahunAjaran = $academicYearId 
            ? TahunAjaran::find($academicYearId) 
            : TahunAjaran::where('status', 'aktif')->first();

        if (! $tahunAjaran) {
            $tahunAjaran = (object) [
                'id'         => null,
                'name'       => Carbon::now()->year . '/' . (Carbon::now()->year + 1),
                'start_year' => Carbon::now()->year,
                'end_year'   => Carbon::now()->year + 1,
            ];
        }

        $periode = $request->query('periode', 'tahunan');
        $dateRange = $this->resolveDateRange($periode, $tahunAjaran);
        $catatanRefleksi = $request->query('catatan_refleksi', '');

        // Anggota Siswa Aktif
        $anggotaAktif = $kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get();

        $siswaIds = $anggotaAktif->pluck('student_id')->toArray();

        // Kueri semua jurnal kelompok dalam periode
        $jurnals = JurnalGuruWali::where('kelompok_id', $kelompok->id)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('tanggal_waktu', [$dateRange['startDate'], $dateRange['endDate']])
            ->orderBy('tanggal_waktu', 'asc')
            ->get();

        $totalSesi = $jurnals->count();
        $totalIndividu = $jurnals->where('jenis_pendampingan', 'Individu')->count();
        $totalKelompokKecil = $jurnals->where('jenis_pendampingan', 'Kelompok Kecil')->count();
        $totalKlasikal = $jurnals->where('jenis_pendampingan', 'Klasikal')->count();

        // Distribusi 4 Pilar
        $distribusiPilar = [
            'Akademik'                => $jurnals->where('kategori_pendampingan', 'Akademik')->count(),
            'Karakter & Kedisiplinan' => $jurnals->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
            'Minat & Bakat / Ekskul'  => $jurnals->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
            'Sosial & Psikologis'     => $jurnals->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
        ];

        // Matriks Siswa Binaan
        $matriksSiswa = $anggotaAktif->map(function ($anggota) use ($jurnals) {
            $siswa = $anggota->siswa;
            $jurnalSiswa = $jurnals->where('student_id', $siswa->id);

            // Kategori dominan
            $kategoriDominan = $jurnalSiswa->groupBy('kategori_pendampingan')
                ->sortByDesc(fn($group) => $group->count())
                ->keys()
                ->first() ?? '—';

            $sesiTerakhir = $jurnalSiswa->sortByDesc('tanggal_waktu')->first();

            return (object) [
                'id'               => $siswa->id,
                'nama'             => $siswa->name,
                'nis'              => $siswa->nis ?? '—',
                'nisn'             => $siswa->nisn ?? '—',
                'kelas'            => $siswa->enrollmentAktif?->kelas?->name ?? '—',
                'total_sesi'       => $jurnalSiswa->count(),
                'kategori_dominan' => $kategoriDominan,
                'status_terakhir'  => $sesiTerakhir?->status_sesi ?? 'Belum ada sesi',
                'tanggal_terakhir' => $sesiTerakhir ? Carbon::parse($sesiTerakhir->tanggal_waktu)->translatedFormat('d/m/Y') : '—',
                'tindak_lanjut'    => $sesiTerakhir?->rencana_tindak_lanjut ?? '—',
            ];
        });

        // Rekapitulasi Kolaborasi & Rujukan
        $rujukanGroups = $jurnals->groupBy('rujukan_kolaborasi');
        $rekapRujukan = [
            'Mandiri'          => $rujukanGroups->get('Mandiri', collect())->count(),
            'Wali Kelas'       => $rujukanGroups->get('Wali Kelas', collect())->count(),
            'Guru BK'          => $rujukanGroups->get('Guru BK', collect())->count(),
            'Orang Tua / Wali' => $rujukanGroups->get('Orang Tua / Wali', collect())->count(),
        ];

        // Kepatuhan (Siswa yang mendapat minimal 1 sesi pendampingan dalam periode)
        $siswaPernahSesi = $matriksSiswa->where('total_sesi', '>', 0)->count();
        $persentaseKepatuhan = $anggotaAktif->count() > 0 
            ? round(($siswaPernahSesi / $anggotaAktif->count()) * 100) 
            : 0;

        return array_merge($schoolData, [
            'teacher'             => $teacher,
            'kelompok'            => $kelompok,
            'tahunAjaran'         => $tahunAjaran,
            'periode'             => $periode,
            'labelPeriode'        => $dateRange['labelPeriode'],
            'startDate'           => $dateRange['startDate'],
            'endDate'             => $dateRange['endDate'],
            'jurnals'             => $jurnals,
            'totalSesi'           => $totalSesi,
            'totalIndividu'       => $totalIndividu,
            'totalKelompokKecil'  => $totalKelompokKecil,
            'totalKlasikal'       => $totalKlasikal,
            'distribusiPilar'     => $distribusiPilar,
            'matriksSiswa'        => $matriksSiswa,
            'rekapRujukan'        => $rekapRujukan,
            'totalSiswa'          => $anggotaAktif->count(),
            'siswaPernahSesi'     => $siswaPernahSesi,
            'persentaseKepatuhan' => $persentaseKepatuhan,
            'catatanRefleksi'     => $catatanRefleksi,
            'tanggalCetak'        => Carbon::now()->translatedFormat('d F Y'),
        ]);
    }

    /**
     * Tampilan Cetak Browser: Laporan Individual
     */
    public function cetakIndividual(Request $request)
    {
        $data = $this->prepareIndividualData($request);
        $data['isPdf'] = false;
        $data['autoPrint'] = $request->query('autoprint', '1') === '1';

        return view('livewire.portal-guru.guru-wali.cetak.cetak-individual', $data);
    }

    /**
     * Unduh PDF: Laporan Individual
     */
    public function pdfIndividual(Request $request)
    {
        $data = $this->prepareIndividualData($request);
        $data['isPdf'] = true;
        $data['autoPrint'] = false;

        $pdf = Pdf::loadView('livewire.portal-guru.guru-wali.cetak.cetak-individual', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'Times-Roman',
            ]);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['siswa']->name);
        $safeTahun = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['tahunAjaran']->name ?? 'TA');
        $filename = "Laporan-Individual-Guru-Wali-{$safeName}-{$safeTahun}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Tampilan Cetak Browser: Laporan Kelompok
     */
    public function cetakKelompok(Request $request)
    {
        $data = $this->prepareKelompokData($request);
        $data['isPdf'] = false;
        $data['autoPrint'] = $request->query('autoprint', '1') === '1';

        return view('livewire.portal-guru.guru-wali.cetak.cetak-kelompok', $data);
    }

    /**
     * Unduh PDF: Laporan Kelompok
     */
    public function pdfKelompok(Request $request)
    {
        $data = $this->prepareKelompokData($request);
        $data['isPdf'] = true;
        $data['autoPrint'] = false;

        $pdf = Pdf::loadView('livewire.portal-guru.guru-wali.cetak.cetak-kelompok', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'Times-Roman',
            ]);

        $safeKelompok = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['kelompok']->nama_kelompok);
        $safeTahun = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['tahunAjaran']->name ?? 'TA');
        $filename = "Laporan-Kinerja-Guru-Wali-{$safeKelompok}-{$safeTahun}.pdf";

        return $pdf->download($filename);
    }
}
