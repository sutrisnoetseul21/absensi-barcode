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
