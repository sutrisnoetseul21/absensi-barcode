<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SiswaCetakController extends Controller
{
    public function cetakKartu(Siswa $siswa)
    {
        $settings = PengaturanSekolah::current();
        return view('pdf.kartu-osis', [
            'student' => $siswa,
            'settings' => $settings,
        ]);
    }

    public function cetakKartuLogin(Siswa $siswa)
    {
        $settings = PengaturanSekolah::current();
        return view('pdf.kartu-login-siswa', [
            'student' => $siswa,
            'settings' => $settings,
        ]);
    }

    public function cetakKartuMandiri(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->student) {
            abort(403, 'Akses ditolak. Hanya akun siswa yang dapat mencetak kartu mandiri.');
        }

        $student = $user->student;
        $settings = PengaturanSekolah::current();

        if ($request->query('download') == '1') {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $barcodeData = $student->barcode_code ?? $student->nisn ?? 'NO-BARCODE';
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

            $enrollment = $student->enrollmentAktif;
            $className = $enrollment?->kelas?->name ?? '-';

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

            return response()->streamDownload(
                fn() => print($pdf->output()),
                'Kartu_Siswa_' . ($student->nisn ?? $student->id) . '.pdf'
            );
        }

        $type = $request->query('type', 'kartu-siswa');
        $view = ($type === 'kartu-presensi') ? 'pdf.kartu-osis' : 'pdf.kartu-login-siswa';

        return view($view, [
            'student' => $student,
            'settings' => $settings,
        ]);
    }

    public function cetakKartuMassal(Request $request)
    {
        $idsString = $request->query('ids', '');
        if (empty($idsString)) {
            abort(400, 'Parameter IDs tidak boleh kosong');
        }
        
        $ids = explode(',', $idsString);
        $students = Siswa::whereIn('id', $ids)->get();

        if ($students->isEmpty()) {
            abort(404, 'Data siswa tidak ditemukan');
        }

        $settings = PengaturanSekolah::current();
        return view('pdf.kartu-osis-massal', [
            'students' => $students,
            'settings' => $settings,
        ]);
    }

    public function cetakKartuLoginMassal(Request $request)
    {
        $idsString = $request->query('ids', '');
        if (empty($idsString)) {
            abort(400, 'Parameter IDs tidak boleh kosong');
        }

        $ids = explode(',', $idsString);
        $students = Siswa::whereIn('id', $ids)->get();

        if ($students->isEmpty()) {
            abort(404, 'Data siswa tidak ditemukan');
        }

        $settings = PengaturanSekolah::current();
        return view('pdf.kartu-login-siswa-massal', [
            'students' => $students,
            'settings' => $settings,
        ]);
    }
}
