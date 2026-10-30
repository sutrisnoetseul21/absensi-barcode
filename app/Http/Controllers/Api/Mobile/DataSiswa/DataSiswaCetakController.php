<?php

namespace App\Http\Controllers\Api\Mobile\DataSiswa;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;

class DataSiswaCetakController extends DataSiswaBaseController
{
    /**
     * Mengambil pratinjau data kartu digital & opsi download PDF kartu login/barcode siswa.
     */
    public function cetakKartu(Request $request, string $id)
    {
        $student = Siswa::with(['presensiProfile', 'enrollmentAktif.kelas'])->find($id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $settings = PengaturanSekolah::current();
        $className = $student->enrollmentAktif?->kelas?->name ?? '-';

        // Jika diminta format JSON untuk pratinjau kartu digital di Flutter
        if ($request->query('format') === 'json') {
            $barcodeData = $student->presensiProfile?->barcode_code ?? $student->nisn ?? 'NO-BARCODE';
            $photoUrl = null;
            if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
                $photoUrl = url(Storage::url($student->photo_path));
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => (string) $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn ?? '-',
                    'nis' => $student->nis ?? '-',
                    'class_name' => $className,
                    'barcode_code' => $barcodeData,
                    'barcode_active' => (bool) ($student->presensiProfile?->barcode_active ?? false),
                    'photo_url' => $photoUrl,
                    'school_name' => $settings?->nama_sekolah ?? 'SMP Negeri 3 Kedungreja',
                    'school_address' => $settings?->alamat_sekolah ?? '-',
                ],
            ]);
        }

        // Generate PDF
        $generator = new BarcodeGeneratorPNG();
        $barcodeData = $student->presensiProfile?->barcode_code ?? $student->nisn ?? 'NO-BARCODE';
        $barcodeImage = base64_encode($generator->getBarcode($barcodeData, $generator::TYPE_CODE_128, 2, 50));

        $logoBase64 = null;
        if ($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path))) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('storage/' . $settings->school_logo_path)));
        }

        $photoBase64 = null;
        if ($student->photo_path && file_exists(public_path('storage/' . $student->photo_path))) {
            $photoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('storage/' . $student->photo_path)));
        }

        $mode = $settings?->barcode_scan_mode ?? 'nisn';
        $isNisMode = $mode === 'nis';
        $identifierLabel = $isNisMode ? 'NIS' : 'NISN';
        $identifierValue = $isNisMode ? $student->nis : $student->nisn;

        $pdf = Pdf::loadView('pdf.kartu-siswa-download', [
            'student' => $student,
            'settings' => $settings,
            'className' => $className,
            'barcodeImage' => $barcodeImage,
            'logoBase64' => $logoBase64,
            'photoBase64' => $photoBase64,
            'identifierLabel' => $identifierLabel,
            'identifierValue' => $identifierValue,
        ])->setPaper([0, 0, 153.07, 243.78], 'portrait');

        return $pdf->stream('Kartu_Siswa_' . ($student->nisn ?? $student->id) . '.pdf');
    }

    /**
     * Mengunduh berkas PDF biodata resmi 1 siswa.
     */
    public function cetakBiodata(Request $request, string $id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $settings = PengaturanSekolah::first();
        $kepsek = Guru::whereHas('jabatans', function ($q) {
            $q->where('nama_jabatan', 'like', '%Kepala Sekolah%')
                ->where(function ($q2) {
                    $q2->whereNull('teacher_jabatan.tanggal_selesai')
                        ->orWhere('teacher_jabatan.tanggal_selesai', '>=', now()->toDateString());
                });
        })->first();

        $pdf = Pdf::loadView('pdf.biodata-siswa', [
            'siswa' => $siswa,
            'namaKepsek' => $kepsek ? $kepsek->name : ($settings?->principal_name ?? config('school.kepala_sekolah_nama')),
            'nipKepsek' => $kepsek ? $kepsek->nip : ($settings?->principal_nip ?? config('school.kepala_sekolah_nip')),
            'namaKota' => $settings?->tempat_rapor ?? ($settings?->kota ?? config('school.kota')),
            'tanggalRapor' => $settings?->tanggal_rapor ? $settings->tanggal_rapor->format('Y-m-d') : now()->format('Y-m-d'),
        ]);

        return $pdf->stream('Biodata-' . Str::slug($siswa->name) . '.pdf');
    }

    /**
     * Mengunduh kartu barcode login massal 1 kelas / seluruh siswa terpilih.
     */
    public function cetakKartuMassal(Request $request)
    {
        $classId = $request->query('class_id');
        $academicYear = $this->getActiveAcademicYear();
        $academicYearId = $request->query('academic_year_id') ?: $academicYear?->id;

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'Parameter class_id diperlukan.'], 422);
        }

        $students = Siswa::where('status', 'aktif')
            ->whereHas('enrollments', function ($q) use ($classId, $academicYearId) {
                $q->where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('status', 'aktif');
            })
            ->with(['presensiProfile', 'enrollments.kelas'])
            ->orderBy('name')
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada siswa aktif di kelas ini.'], 404);
        }

        $settings = PengaturanSekolah::current();
        $kelas = Kelas::find($classId);

        $pdf = Pdf::loadView('pdf.kartu-login-siswa-massal', [
            'students' => $students,
            'settings' => $settings,
            'kelas' => $kelas,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Kartu_Massal_Kelas_' . Str::slug($kelas?->name ?? 'Kelas') . '.pdf');
    }

    /**
     * Mengunduh rekap berkas PDF biodata seluruh siswa dalam 1 kelas.
     */
    public function cetakBiodataMassal(Request $request)
    {
        $classId = $request->query('class_id');
        $academicYear = $this->getActiveAcademicYear();
        $academicYearId = $request->query('academic_year_id') ?: $academicYear?->id;

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'Parameter class_id diperlukan.'], 422);
        }

        $siswas = Siswa::where('status', 'aktif')
            ->whereHas('enrollments', function ($q) use ($classId, $academicYearId) {
                $q->where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('status', 'aktif');
            })
            ->orderBy('name')
            ->get();

        if ($siswas->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada siswa aktif di kelas ini.'], 404);
        }

        $settings = PengaturanSekolah::first();
        $kepsek = Guru::whereHas('jabatans', function ($q) {
            $q->where('nama_jabatan', 'like', '%Kepala Sekolah%')
                ->where(function ($q2) {
                    $q2->whereNull('teacher_jabatan.tanggal_selesai')
                        ->orWhere('teacher_jabatan.tanggal_selesai', '>=', now()->toDateString());
                });
        })->first();

        $pdf = Pdf::loadView('pdf.biodata-siswa-batch', [
            'siswas' => $siswas,
            'namaKepsek' => $kepsek ? $kepsek->name : ($settings?->principal_name ?? config('school.kepala_sekolah_nama')),
            'nipKepsek' => $kepsek ? $kepsek->nip : ($settings?->principal_nip ?? config('school.kepala_sekolah_nip')),
            'namaKota' => $settings?->tempat_rapor ?? ($settings?->kota ?? config('school.kota')),
            'tanggalRapor' => $settings?->tanggal_rapor ? $settings->tanggal_rapor->format('Y-m-d') : now()->format('Y-m-d'),
        ]);

        $kelas = Kelas::find($classId);
        return $pdf->stream('Biodata-Kelas-' . Str::slug($kelas?->name ?? 'Kelas') . '.pdf');
    }
}
