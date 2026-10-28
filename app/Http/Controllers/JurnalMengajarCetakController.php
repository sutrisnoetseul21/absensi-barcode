<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurnalMengajarCetakController extends Controller
{
    public function cetak(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $isSupervisor = $user->isSuperAdmin() 
            || $user->hasRole('super_admin') 
            || $user->can('portal_guru:supervisi_jurnal')
            || $user->hasAnyRole(['admin_akademik_viewer', 'admin_akademik_editor', 'admin_presensi_viewer', 'admin_presensi_editor']);

        $teacherId = $request->query('teacher_id');
        if (!$isSupervisor || empty($teacherId)) {
            $teacherId = $user->teacher?->id;
        }

        $academicYearId = $request->query('academic_year_id') 
            ?? PengaturanSekolah::current()?->academic_year_id_active 
            ?? TahunAjaran::where('status', 'aktif')->first()?->id;

        $mapelId = $request->query('mata_pelajaran_id');
        $classId = $request->query('class_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $guru = $teacherId ? Guru::find($teacherId) : null;
        $tahunAjaran = $academicYearId ? TahunAjaran::find($academicYearId) : null;
        $mapel = $mapelId ? MataPelajaran::find($mapelId) : null;
        $kelas = $classId ? Kelas::find($classId) : null;
        $sekolah = PengaturanSekolah::current();

        $jurnals = JurnalPembelajaran::query()
            ->with(['guru', 'kelas', 'mataPelajaran'])
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($teacherId, fn($q) => $q->where('teacher_id', $teacherId))
            ->when($mapelId, fn($q) => $q->where('mata_pelajaran_id', $mapelId))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('tanggal', '<=', $dateTo))
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdf.jurnal-mengajar-rekap', compact(
            'jurnals',
            'guru',
            'tahunAjaran',
            'mapel',
            'kelas',
            'sekolah',
            'dateFrom',
            'dateTo'
        ))->setPaper('a4', 'landscape');

        $filename = 'Jurnal_Mengajar_' . ($guru ? str_replace(' ', '_', $guru->name) : 'Semua') . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->stream($filename);
    }
}
