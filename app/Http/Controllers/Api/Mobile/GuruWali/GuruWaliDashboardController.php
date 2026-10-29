<?php

namespace App\Http\Controllers\Api\Mobile\GuruWali;

use App\Enums\StatusPengajuan;
use App\Models\JurnalGuruWali;
use App\Models\KonsultasiGuruWali;
use App\Models\PesanKonsultasiGuruWali;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruWaliDashboardController extends GuruWaliBaseController
{
    /**
     * Dashboard utama Guru Wali (Info Kelompok, Metrik Statistik, dan Grid Kotak Siswa Binaan).
     * GET /api/v1/guru/guru-wali/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $kelompok = $this->resolveKelompok($teacher);
        if (!$kelompok) {
            return response()->json([
                'success' => true,
                'has_kelompok' => false,
                'message' => 'Anda belum memiliki tugas penugasan Guru Wali yang aktif (Permendikdasmen No. 11/2025).',
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'nip' => $teacher->nip ?? '-',
                ],
                'data' => null,
            ]);
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $namaBulanIni = Carbon::now()->translatedFormat('F Y');
        $activeYear = $this->getActiveAcademicYear();

        $search = trim((string) $request->query('search', ''));

        // Query Siswa Aktif
        $queryAktif = $kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->whereHas('siswa', function ($q) use ($search) {
                if ($search !== '') {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%");
                    });
                }
            });

        $siswaAktifList = $queryAktif->get();
        $siswaIds = $siswaAktifList->pluck('student_id')->filter()->values()->toArray();

        // Riwayat Jurnal Bulan Berjalan
        $jurnalsBulanIni = JurnalGuruWali::where('teacher_id', $teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->whereBetween('tanggal_waktu', [$startOfMonth, $endOfMonth])
            ->latest('tanggal_waktu')
            ->get()
            ->groupBy('student_id');

        // Pesan Cerita / Konsultasi Aktif Siswa
        $konsultasiAktifMap = KonsultasiGuruWali::where('teacher_id', $teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->whereIn('status_pengajuan', [
                StatusPengajuan::MenungguKonfirmasi->value,
                StatusPengajuan::Dijadwalkan->value,
            ])
            ->latest('created_at')
            ->get()
            ->groupBy('student_id');

        // Unread messages per siswa
        $unreadMessagesCount = PesanKonsultasiGuruWali::whereHas('konsultasi', function ($q) use ($teacher, $siswaIds) {
            $q->where('teacher_id', $teacher->id)->whereIn('student_id', $siswaIds);
        })
            ->where('sender_type', 'siswa')
            ->where('is_read', false)
            ->select('konsultasi_id', DB::raw('count(*) as total'))
            ->groupBy('konsultasi_id')
            ->pluck('total', 'konsultasi_id');

        // Format Siswa Aktif untuk Grid Kotak-Kotak
        $siswaAktifFormatted = $siswaAktifList->map(function ($item) use ($jurnalsBulanIni, $konsultasiAktifMap, $unreadMessagesCount) {
            $siswa = $item->siswa;
            $jurnals = $jurnalsBulanIni->get($item->student_id, collect());
            $lastJurnal = $jurnals->first();

            $activeKonsuls = $konsultasiAktifMap->get($item->student_id, collect());
            $activeKonsul = $activeKonsuls->first();

            $unreadCount = 0;
            if ($activeKonsul) {
                $unreadCount = $unreadMessagesCount->get($activeKonsul->id, 0);
            }

            return [
                'anggota_id' => $item->id,
                'student_id' => $item->student_id,
                'name' => $siswa?->name ?? 'Tanpa Nama',
                'nisn' => $siswa?->nisn ?? '-',
                'gender' => $siswa?->gender ?? 'L',
                'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? 'Belum ada kelas',
                'avatar_url' => $siswa?->avatar_url ?? null,
                'tahun_masuk' => $item->tahun_masuk,
                'has_bimbingan' => $jurnals->isNotEmpty(),
                'bimbingan_count' => $jurnals->count(),
                'last_bimbingan_at' => $lastJurnal?->tanggal_waktu ? Carbon::parse($lastJurnal->tanggal_waktu)->format('Y-m-d H:i') : null,
                'last_bimbingan_date_formatted' => $lastJurnal?->tanggal_waktu ? Carbon::parse($lastJurnal->tanggal_waktu)->translatedFormat('d M Y') : null,
                'last_kategori' => $lastJurnal?->kategori_pendampingan,
                'last_status' => $lastJurnal?->status_sesi,
                'has_active_konsultasi' => $activeKonsul !== null,
                'active_konsultasi_id' => $activeKonsul?->id,
                'active_konsultasi_status' => $activeKonsul?->status_pengajuan?->value ?? $activeKonsul?->status_pengajuan,
                'unread_konsultasi_count' => $unreadCount,
            ];
        });

        // Siswa Arsip (Riwayat)
        $siswaArsipList = $kelompok->anggotaArsip()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get()
            ->map(function ($item) {
                $siswa = $item->siswa;
                return [
                    'anggota_id' => $item->id,
                    'student_id' => $item->student_id,
                    'name' => $siswa?->name ?? 'Tanpa Nama',
                    'nisn' => $siswa?->nisn ?? '-',
                    'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? 'Belum ada kelas',
                    'tahun_masuk' => $item->tahun_masuk,
                ];
            });

        // Metrik Statistik
        $totalAktif = $kelompok->anggotaAktif()->count();
        $totalArsip = $kelompok->anggotaArsip()->count();
        $totalDidampingiBulanIni = count($jurnalsBulanIni);
        $persenDidampingi = $totalAktif > 0 ? (int) round(($totalDidampingiBulanIni / $totalAktif) * 100) : 0;

        $pendingKonsultasiCount = KonsultasiGuruWali::where('teacher_id', $teacher->id)
            ->where('status_pengajuan', StatusPengajuan::MenungguKonfirmasi->value)
            ->count();

        return response()->json([
            'success' => true,
            'has_kelompok' => true,
            'data' => [
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'nip' => $teacher->nip ?? '-',
                    'beban_jp' => '2 JP/Minggu',
                ],
                'kelompok' => [
                    'id' => $kelompok->id,
                    'nama_kelompok' => $kelompok->nama_kelompok,
                    'status_aktif' => (bool) $kelompok->status_aktif,
                ],
                'tahun_ajaran' => [
                    'id' => $activeYear?->id,
                    'name' => $activeYear?->name ?? Carbon::now()->year . '/' . (Carbon::now()->year + 1),
                ],
                'bulan_ini' => $namaBulanIni,
                'statistik' => [
                    'total_siswa_aktif' => $totalAktif,
                    'total_didampingi_bulan_ini' => $totalDidampingiBulanIni,
                    'persen_kepatuhan' => $persenDidampingi,
                    'total_siswa_arsip' => $totalArsip,
                    'pending_konsultasi_count' => $pendingKonsultasiCount,
                ],
                'siswa_aktif' => $siswaAktifFormatted,
                'siswa_arsip' => $siswaArsipList,
            ],
        ]);
    }
}
