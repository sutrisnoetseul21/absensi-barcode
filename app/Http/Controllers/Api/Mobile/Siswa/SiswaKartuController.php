<?php

namespace App\Http\Controllers\Api\Mobile\Siswa;

use App\Models\Guru;
use App\Models\PengaturanSekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;

class SiswaKartuController extends BaseSiswaApiController
{
    /**
     * Mengambil informasi lengkap identitas kartu pelajar digital siswa untuk mobile app.
     */
    public function digitalInfo(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan atau belum terautentikasi.',
            ], 404);
        }

        $student->loadMissing(['presensiProfile', 'enrollmentAktif.kelas']);
        $settings = PengaturanSekolah::current();

        $className = $student->enrollmentAktif?->kelas?->name ?? '-';
        $barcodeData = $student->presensiProfile?->barcode_code ?? $student->barcode_code ?? $student->nisn ?? 'NO-BARCODE';
        $barcodeActive = (bool) ($student->presensiProfile?->barcode_active ?? true);

        // Foto Siswa
        $photoUrl = null;
        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            $photoUrl = url(Storage::url($student->photo_path));
        }

        // Logo Sekolah
        $schoolLogoUrl = null;
        if ($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path))) {
            $schoolLogoUrl = url('storage/' . $settings->school_logo_path);
        } elseif ($settings?->district_logo_path && file_exists(public_path('storage/' . $settings->district_logo_path))) {
            $schoolLogoUrl = url('storage/' . $settings->district_logo_path);
        }

        // Jenis Kelamin
        $gender = strtoupper($student->gender ?? 'L');
        $genderLabel = $gender === 'P' ? 'Perempuan' : 'Laki-laki';

        // Tempat, Tanggal Lahir
        $birthPlace = $student->birth_place ?? '';
        $birthDateFormatted = $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '';
        $birthInfo = trim(($birthPlace ? $birthPlace . ', ' : '') . $birthDateFormatted);

        // Kepala Sekolah
        $kepsek = Guru::whereHas('jabatans', function ($q) {
            $q->where('nama_jabatan', 'like', '%Kepala Sekolah%')
                ->where(function ($q2) {
                    $q2->whereNull('teacher_jabatan.tanggal_selesai')
                        ->orWhere('teacher_jabatan.tanggal_selesai', '>=', now()->toDateString());
                });
        })->first();

        $principalName = $kepsek ? $kepsek->name : ($settings?->principal_name ?? config('school.kepala_sekolah_nama', 'Kepala Sekolah'));
        $principalNip = $kepsek ? $kepsek->nip : ($settings?->principal_nip ?? config('school.kepala_sekolah_nip', '-'));

        $scanMode = $settings?->barcode_scan_mode ?? 'nisn';

        return response()->json([
            'success' => true,
            'message' => 'Data kartu pelajar berhasil dimuat.',
            'data' => [
                'student_id' => (string) $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn ?? '-',
                'nis' => $student->nis ?? '-',
                'class_name' => $className,
                'gender' => $gender,
                'gender_label' => $genderLabel,
                'birth_place' => $birthPlace,
                'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : null,
                'birth_info' => $birthInfo,
                'photo_url' => $photoUrl,
                'barcode_code' => $barcodeData,
                'barcode_active' => $barcodeActive,
                'school_name' => $settings?->school_name ?? 'SMP Negeri 3 Kedungreja',
                'school_address' => $settings?->school_address ?? '-',
                'school_logo_url' => $schoolLogoUrl,
                'principal_name' => $principalName,
                'principal_nip' => $principalNip,
                'scan_mode' => $scanMode,
                'download_pdf_url' => url('/api/v1/siswa/kartu/download-pdf'),
                'biodata_pdf_url' => url('/api/v1/siswa/kartu/biodata-pdf'),
            ],
        ]);
    }

    /**
     * Mengalirkan (stream) berkas PDF kartu pelajar resmi siswa.
     */
    public function downloadPdf(Request $request)
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        $student->loadMissing(['presensiProfile', 'enrollmentAktif.kelas']);
        $settings = PengaturanSekolah::current();
        $className = $student->enrollmentAktif?->kelas?->name ?? '-';

        $generator = new BarcodeGeneratorPNG();
        $barcodeData = $student->presensiProfile?->barcode_code ?? $student->barcode_code ?? $student->nisn ?? 'NO-BARCODE';
        $barcodeImage = base64_encode($generator->getBarcode($barcodeData, $generator::TYPE_CODE_128, 2, 50));

        $logoBase64 = null;
        if ($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path))) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('storage/' . $settings->school_logo_path)));
        } elseif ($settings?->district_logo_path && file_exists(public_path('storage/' . $settings->district_logo_path))) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('storage/' . $settings->district_logo_path)));
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
            'student'         => $student,
            'settings'        => $settings,
            'className'       => $className,
            'barcodeImage'    => $barcodeImage,
            'logoBase64'      => $logoBase64,
            'photoBase64'     => $photoBase64,
            'identifierLabel' => $identifierLabel,
            'identifierValue' => $identifierValue,
        ])->setPaper([0, 0, 153.07, 243.78], 'portrait');

        return $pdf->stream('Kartu_Siswa_' . ($student->nisn ?? $student->id) . '.pdf');
    }

    /**
     * Mengalirkan (stream) berkas PDF biodata lengkap siswa.
     */
    public function biodataPdf(Request $request)
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        $settings = PengaturanSekolah::current();

        $kepsek = Guru::whereHas('jabatans', function ($q) {
            $q->where('nama_jabatan', 'like', '%Kepala Sekolah%')
                ->where(function ($q2) {
                    $q2->whereNull('teacher_jabatan.tanggal_selesai')
                        ->orWhere('teacher_jabatan.tanggal_selesai', '>=', now()->toDateString());
                });
        })->first();

        $principalName = $kepsek ? $kepsek->name : ($settings?->principal_name ?? config('school.kepala_sekolah_nama', 'Kepala Sekolah'));
        $principalNip = $kepsek ? $kepsek->nip : ($settings?->principal_nip ?? config('school.kepala_sekolah_nip', '-'));

        $pdf = Pdf::loadView('pdf.biodata-siswa', [
            'siswa'        => $student,
            'namaKepsek'   => $principalName,
            'nipKepsek'    => $principalNip,
            'namaKota'     => $settings?->tempat_rapor ?? ($settings?->kota ?? config('school.kota', 'Kedungreja')),
            'tanggalRapor' => $settings?->tanggal_rapor ? $settings->tanggal_rapor->format('Y-m-d') : now()->format('Y-m-d'),
        ]);

        return $pdf->stream('Biodata-' . Str::slug($student->name) . '.pdf');
    }
}

