<?php

namespace App\Http\Controllers\Api\Mobile\DataSiswa;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataSiswaListController extends DataSiswaBaseController
{
    /**
     * Opsi Tahun Ajaran & Daftar Kelas Binaan yang diampu Guru.
     */
    public function options(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $teacher = $this->resolveTeacher($request);

        if (!$user && !$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $activeYear = $this->getActiveAcademicYear();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id);

        $academicYears = TahunAjaran::orderBy('start_year', 'desc')->get()->map(fn($y) => [
            'id' => (string) $y->id,
            'name' => $y->name,
            'is_active' => $y->status === 'aktif',
        ]);

        $access = $this->getAccessibleClasses($user, $selectedYearId);
        $classes = $access['classes'];

        $selectedClassId = $request->query('class_id');
        if (!$selectedClassId && $classes->isNotEmpty()) {
            $selectedClassId = $classes->first()['id'];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'teacher' => [
                    'id' => $teacher?->id,
                    'name' => $teacher?->name ?? $user->name,
                    'nip' => $teacher?->nip ?? '-',
                ],
                'academic_years' => $academicYears,
                'selected_academic_year_id' => $selectedYearId,
                'classes' => $classes,
                'selected_class_id' => $selectedClassId,
                'is_bk' => $access['is_bk'],
                'role_summary' => $access['role_summary'],
            ],
        ]);
    }

    /**
     * Mengambil daftar siswa dalam kelas tertentu beserta statistik rombel & filter.
     */
    public function students(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $teacher = $this->resolveTeacher($request);

        if (!$user && !$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $request->query('academic_year_id') ?: $activeYear?->id;
        $classId = $request->query('class_id');

        if (!$classId) {
            $access = $this->getAccessibleClasses($user, $academicYearId);
            $classes = $access['classes'];
            if ($classes->isNotEmpty()) {
                $classId = $classes->first()['id'];
            }
        }

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'students' => [],
                    'stats' => [
                        'total' => 0,
                        'laki' => 0,
                        'perempuan' => 0,
                        'berbarcode' => 0,
                        'lengkap_nohp' => 0,
                        'persen_berbarcode' => 0,
                    ],
                    'selected_class' => null,
                ],
            ]);
        }

        $kelas = Kelas::find($classId);

        $query = Siswa::query()
            ->where('status', 'aktif')
            ->whereHas('enrollments', function ($q) use ($classId, $academicYearId) {
                $q->where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('status', 'aktif');
            })
            ->with(['presensiProfile', 'user']);

        // Search term
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter Barcode
        $filterBarcode = $request->query('filter_barcode', 'all');
        if ($filterBarcode === 'has_barcode') {
            $query->whereHas('presensiProfile', function ($p) {
                $p->whereNotNull('barcode_code')->where('barcode_code', '!=', '');
            });
        } elseif ($filterBarcode === 'no_barcode') {
            $query->where(function ($q) {
                $q->whereDoesntHave('presensiProfile')
                    ->orWhereHas('presensiProfile', function ($p) {
                        $p->whereNull('barcode_code')->orWhere('barcode_code', '');
                    });
            });
        }

        // Filter No HP Ortu
        $filterNoHp = $request->query('filter_nohp', 'all');
        if ($filterNoHp === 'has_nohp') {
            $query->whereNotNull('no_hp_orang_tua')->where('no_hp_orang_tua', '!=', '');
        } elseif ($filterNoHp === 'no_nohp') {
            $query->where(function ($q) {
                $q->whereNull('no_hp_orang_tua')->orWhere('no_hp_orang_tua', '');
            });
        }

        $rawStudents = $query->orderBy('name')->get();

        // Hitung statistik kelas keseluruhan (tanpa filter pencarian agar angka rombel tetap konsisten)
        $allClassStudents = Siswa::where('status', 'aktif')
            ->whereHas('enrollments', function ($q) use ($classId, $academicYearId) {
                $q->where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('status', 'aktif');
            })
            ->with(['presensiProfile'])
            ->get();

        $totalAll = $allClassStudents->count();
        $countLaki = $allClassStudents->where('gender', 'L')->count();
        $countPerempuan = $allClassStudents->where('gender', 'P')->count();
        $countBerbarcode = $allClassStudents->filter(fn($s) => !empty($s->presensiProfile?->barcode_code))->count();
        $countLengkapNoHp = $allClassStudents->filter(fn($s) => !empty($s->no_hp_orang_tua))->count();
        $persenBerbarcode = $totalAll > 0 ? (int) round(($countBerbarcode / $totalAll) * 100) : 0;

        $formattedStudents = $rawStudents->map(function (Siswa $s) use ($kelas) {
            $barcode = $s->presensiProfile?->barcode_code;
            $isBarcodeActive = (bool) ($s->presensiProfile?->barcode_active ?? false);

            $photoUrl = null;
            if ($s->photo_path && Storage::disk('public')->exists($s->photo_path)) {
                $photoUrl = url(Storage::url($s->photo_path));
            }

            return [
                'id' => (string) $s->id,
                'name' => $s->name,
                'nisn' => $s->nisn ?? '-',
                'nis' => $s->nis ?? '-',
                'gender' => $s->gender ?? 'L',
                'class_name' => $kelas?->name ?? '-',
                'photo_url' => $photoUrl,
                'birth_place' => $s->birth_place,
                'birth_date' => $s->birth_date ? $s->birth_date->format('Y-m-d') : null,
                'address' => $s->address,
                'religion' => $s->religion,
                'no_hp' => $s->no_hp,
                'no_hp_orang_tua' => $s->no_hp_orang_tua,
                'nama_ayah' => $s->nama_ayah,
                'nama_ibu' => $s->nama_ibu,
                'nama_wali' => $s->nama_wali,
                'barcode_code' => $barcode,
                'barcode_active' => $isBarcodeActive,
                'has_user_account' => !empty($s->user_id),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'selected_class' => [
                    'id' => $kelas?->id,
                    'name' => $kelas?->name ?? 'Kelas',
                    'grade_level' => $kelas?->grade_level,
                ],
                'stats' => [
                    'total' => $totalAll,
                    'laki' => $countLaki,
                    'perempuan' => $countPerempuan,
                    'berbarcode' => $countBerbarcode,
                    'lengkap_nohp' => $countLengkapNoHp,
                    'persen_berbarcode' => $persenBerbarcode,
                ],
                'students' => $formattedStudents,
            ],
        ]);
    }
}
