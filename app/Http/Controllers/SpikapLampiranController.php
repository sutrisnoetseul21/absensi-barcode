<?php

namespace App\Http\Controllers;

use App\Models\SpikapLampiran;
use App\Models\SpikapLaporan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SpikapLampiranController extends Controller
{
    /**
     * Verifikasi otorisasi akses file lampiran.
     */
    protected function authorizeAccess(SpikapLampiran $lampiran): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        // Siswa pemilik laporan
        if ($user->student && $user->student->id === $lampiran->laporan->student_id) {
            return;
        }

        // Guru / Admin yang memiliki hak akses ke laporan tersebut
        $hasAccess = SpikapLaporan::forUser($user)->where('id', $lampiran->laporan_id)->exists();
        if ($hasAccess) {
            return;
        }

        abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses lampiran ini.');
    }

    /**
     * Tampilkan file langsung di browser (inline preview untuk foto & streaming video).
     */
    public function show(SpikapLampiran $lampiran): Response
    {
        $this->authorizeAccess($lampiran);

        if (!Storage::disk('local')->exists($lampiran->path_file)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        return Storage::disk('local')->response($lampiran->path_file, $lampiran->nama_asli);
    }

    /**
     * Paksa unduh (force download) file lampiran.
     */
    public function download(SpikapLampiran $lampiran): Response
    {
        $this->authorizeAccess($lampiran);

        if (!Storage::disk('local')->exists($lampiran->path_file)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        return Storage::disk('local')->download($lampiran->path_file, $lampiran->nama_asli);
    }
}
