<?php

namespace App\Http\Controllers\Api\Mobile\GuruWali;

use App\Models\JurnalGuruWali;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuruWaliLaporanController extends GuruWaliBaseController
{
    /**
     * Mengambil matriks pemantauan bulanan per siswa dan distribusi 4 pilar.
     * GET /api/v1/guru/guru-wali/pemantauan
     */
    public function pemantauan(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $kelompok = $this->resolveKelompok($teacher);
        if (!$kelompok) {
            return response()->json(['success' => false, 'message' => 'Kelompok aktif tidak ditemukan.'], 403);
        }

        $activeYear = $this->getActiveAcademicYear();
        $startYear = $activeYear?->start_year ?? Carbon::now()->year;
        $endYear = $activeYear?->end_year ?? ($startYear + 1);

        // Anggota Siswa Aktif
        $anggotaAktif = $kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get();

        $siswaIds = $anggotaAktif->pluck('student_id')->toArray();

        // Rentang Tahun Ajaran Penuh: 1 Juli startYear s.d. 30 Juni endYear
        $startAcademic = Carbon::createFromDate($startYear, 7, 1)->startOfDay();
        $endAcademic = Carbon::createFromDate($endYear, 6, 30)->endOfDay();

        $jurnals = JurnalGuruWali::where('teacher_id', $teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->whereBetween('tanggal_waktu', [$startAcademic, $endAcademic])
            ->get();

        // 12 Bulan dalam 1 Tahun Ajaran (Juli - Desember startYear, Jan - Juni endYear)
        $bulanKeys = [
            ['key' => "{$startYear}-07", 'label' => 'Jul', 'bulan' => 7, 'tahun' => $startYear],
            ['key' => "{$startYear}-08", 'label' => 'Agu', 'bulan' => 8, 'tahun' => $startYear],
            ['key' => "{$startYear}-09", 'label' => 'Sep', 'bulan' => 9, 'tahun' => $startYear],
            ['key' => "{$startYear}-10", 'label' => 'Okt', 'bulan' => 10, 'tahun' => $startYear],
            ['key' => "{$startYear}-11", 'label' => 'Nov', 'bulan' => 11, 'tahun' => $startYear],
            ['key' => "{$startYear}-12", 'label' => 'Des', 'bulan' => 12, 'tahun' => $startYear],
            ['key' => "{$endYear}-01", 'label' => 'Jan', 'bulan' => 1, 'tahun' => $endYear],
            ['key' => "{$endYear}-02", 'label' => 'Feb', 'bulan' => 2, 'tahun' => $endYear],
            ['key' => "{$endYear}-03", 'label' => 'Mar', 'bulan' => 3, 'tahun' => $endYear],
            ['key' => "{$endYear}-04", 'label' => 'Apr', 'bulan' => 4, 'tahun' => $endYear],
            ['key' => "{$endYear}-05", 'label' => 'Mei', 'bulan' => 5, 'tahun' => $endYear],
            ['key' => "{$endYear}-06", 'label' => 'Jun', 'bulan' => 6, 'tahun' => $endYear],
        ];

        $matriksSiswa = $anggotaAktif->map(function ($item) use ($jurnals, $bulanKeys) {
            $siswa = $item->siswa;
            $jurnalSiswa = $jurnals->where('student_id', $item->student_id);

            $monthlyStatus = [];
            $totalBulanDidampingi = 0;

            foreach ($bulanKeys as $bk) {
                $count = $jurnalSiswa->filter(function ($j) use ($bk) {
                    $dt = Carbon::parse($j->tanggal_waktu);
                    return $dt->year == $bk['tahun'] && $dt->month == $bk['bulan'];
                })->count();

                $isDidampingi = $count > 0;
                if ($isDidampingi) {
                    $totalBulanDidampingi++;
                }

                $monthlyStatus[] = [
                    'key' => $bk['key'],
                    'label' => $bk['label'],
                    'is_didampingi' => $isDidampingi,
                    'count' => $count,
                ];
            }

            return [
                'student_id' => $item->student_id,
                'name' => $siswa?->name ?? '-',
                'nisn' => $siswa?->nisn ?? '-',
                'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? '-',
                'avatar_url' => $siswa?->avatar_url,
                'total_sesi' => $jurnalSiswa->count(),
                'total_bulan_didampingi' => $totalBulanDidampingi,
                'kepatuhan_persen' => (int) round(($totalBulanDidampingi / 12) * 100),
                'monthly_status' => $monthlyStatus,
            ];
        });

        // Distribusi 4 Pilar
        $distribusiPilar = [
            'Akademik' => $jurnals->where('kategori_pendampingan', 'Akademik')->count(),
            'Karakter & Kedisiplinan' => $jurnals->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
            'Minat & Bakat / Ekskul' => $jurnals->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
            'Sosial & Psikologis' => $jurnals->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'bulan_headers' => $bulanKeys,
                'matriks_siswa' => $matriksSiswa,
                'distribusi_pilar' => $distribusiPilar,
                'total_seluruh_sesi' => $jurnals->count(),
            ],
        ]);
    }

    /**
     * Download / Stream File PDF Cetak Laporan Resmi Guru Wali.
     * GET /api/v1/guru/guru-wali/cetak/pdf
     */
    public function cetakPdf(Request $request)
    {
        $user = $this->resolveUser($request);
        $teacher = $this->resolveTeacher($request);
        if (!$teacher || !$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!auth()->check()) {
            auth()->setUser($user);
        }

        $type = $request->query('type', 'rekap'); // 'rekap' | 'individual'

        try {
            $cetakController = app(\App\Http\Controllers\GuruWaliCetakController::class);

            if ($type === 'individual') {
                return $cetakController->pdfIndividual($request);
            }

            return $cetakController->pdfKelompok($request);
        } catch (\Throwable $e) {
            Log::error('GuruWaliLaporanController@cetakPdf: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghasilkan PDF: ' . $e->getMessage(),
            ], 500);
        }
    }
}

