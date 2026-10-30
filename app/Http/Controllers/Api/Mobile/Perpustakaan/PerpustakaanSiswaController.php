<?php

namespace App\Http\Controllers\Api\Mobile\Perpustakaan;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerpustakaanSiswaController extends PerpustakaanBaseController
{
    /**
     * GET /api/v1/guru/perpustakaan/kelas-ampu
     * Daftar kelas yang diampu oleh guru pada tahun ajaran aktif.
     */
    public function kelasAmpu(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $guru = $this->resolveTeacher($request);
        $activeYear = $this->getActiveAcademicYear();

        $classes = $this->getAccessibleClasses($user, $guru, $activeYear);

        return $this->sendSuccess([
            'academic_year' => $activeYear ? [
                'id' => (string) $activeYear->id,
                'name' => (string) ($activeYear->name ?? $activeYear->tahun ?? 'Aktif'),
                'semester' => (string) ($activeYear->semester ?? '-'),
            ] : null,
            'classes' => $classes,
        ], 'Daftar kelas ampu berhasil dimuat');
    }

    /**
     * GET /api/v1/guru/perpustakaan/pinjaman-siswa
     * Pemantauan peminjaman buku oleh siswa pada kelas yang diampu guru.
     */
    public function pinjamanSiswa(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $guru = $this->resolveTeacher($request);
        $activeYear = $this->getActiveAcademicYear();

        $accessibleClasses = $this->getAccessibleClasses($user, $guru, $activeYear);

        if ($accessibleClasses->isEmpty()) {
            return $this->sendSuccess([
                'selected_class' => null,
                'items' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                    'has_more_pages' => false,
                ],
            ], 'Anda saat ini belum mengampu kelas atau tidak memiliki akses kelas');
        }

        $classId = (string) $request->input('class_id', '');
        if ($classId === '' || !$accessibleClasses->contains('id', $classId)) {
            $classId = (string) $accessibleClasses->first()['id'];
        }

        $selectedClassInfo = $accessibleClasses->firstWhere('id', $classId);
        if (!$selectedClassInfo) {
            $kelasModel = Kelas::find($classId);
            $selectedClassInfo = [
                'id' => $classId,
                'name' => $kelasModel?->name ?? 'Kelas',
                'grade_level' => $kelasModel?->grade_level ?? null,
                'role' => 'Ampu',
            ];
        }

        $search = trim((string) $request->input('search', ''));
        $perPage = max(1, min(50, (int) $request->input('per_page', 15)));
        $today = Carbon::today();

        // Ambil ID siswa yang terdaftar di kelas dan tahun ajaran aktif ini
        $studentIds = EnrollmentSiswa::where('class_id', $classId)
            ->whereHas('tahunAjaran', function ($q) {
                $q->where('status', 'aktif');
            })
            ->pluck('student_id');

        $query = Peminjaman::with(['eksemplarBuku.buku', 'peminjam'])
            ->where('peminjam_type', 'siswa')
            ->whereIn('peminjam_id', $studentIds)
            ->where('status', 'dipinjam');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('peminjam', function ($sq) use ($search) {
                    $sq->where('name', 'like', '%' . $search . '%')
                       ->orWhere('nisn', 'like', '%' . $search . '%')
                       ->orWhere('nis', 'like', '%' . $search . '%');
                })->orWhereHas('eksemplarBuku', function ($eq) use ($search) {
                    $eq->where('kode_eksemplar', 'like', '%' . $search . '%')
                       ->orWhereHas('buku', function ($bq) use ($search) {
                           $bq->where('judul', 'like', '%' . $search . '%')
                              ->orWhere('penulis', 'like', '%' . $search . '%');
                       });
                });
            });
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($pinjam) use ($today) {
            $isTerlambat = $pinjam->status === 'dipinjam' 
                && $pinjam->tanggal_jatuh_tempo 
                && $today->gt($pinjam->tanggal_jatuh_tempo->copy()->startOfDay());

            $hariTerlambat = $isTerlambat 
                ? $today->diffInDays($pinjam->tanggal_jatuh_tempo->copy()->startOfDay()) 
                : 0;

            $buku = $pinjam->eksemplarBuku?->buku;
            $sampulUrl = null;
            if ($buku && !empty($buku->sampul_buku)) {
                $sampulUrl = filter_var($buku->sampul_buku, FILTER_VALIDATE_URL)
                    ? $buku->sampul_buku
                    : url('storage/' . ltrim($buku->sampul_buku, '/'));
            }

            $siswa = $pinjam->peminjam;

            return [
                'id' => (string) $pinjam->id,
                'siswa' => [
                    'id' => (string) ($siswa?->id ?? $pinjam->peminjam_id),
                    'name' => (string) ($siswa?->name ?? 'Siswa Tidak Diketahui'),
                    'nisn' => $siswa?->nisn ?? null,
                    'nis' => $siswa?->nis ?? null,
                ],
                'buku' => [
                    'id' => (string) ($buku?->id ?? ''),
                    'judul' => (string) ($buku?->judul ?? 'Judul Tidak Diketahui'),
                    'penulis' => $buku?->penulis ?? '-',
                    'kode_eksemplar' => $pinjam->eksemplarBuku?->kode_eksemplar ?? '-',
                    'sampul_buku_url' => $sampulUrl,
                    'lokasi_rak' => $buku?->lokasi_rak ?? '-',
                ],
                'tanggal_pinjam' => $pinjam->tanggal_pinjam ? $pinjam->tanggal_pinjam->format('d M Y') : null,
                'tanggal_jatuh_tempo' => $pinjam->tanggal_jatuh_tempo ? $pinjam->tanggal_jatuh_tempo->format('d M Y') : null,
                'is_terlambat' => $isTerlambat,
                'hari_terlambat' => $hariTerlambat,
                'status' => (string) $pinjam->status,
                'catatan' => $pinjam->catatan,
            ];
        });

        return $this->sendSuccess([
            'selected_class' => $selectedClassInfo,
            'items' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ], 'Peminjaman siswa berhasil dimuat');
    }
}

