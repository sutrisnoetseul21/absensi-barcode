<?php

namespace App\Http\Controllers;

use App\Exports\JurnalMengajarExport;
use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class JurnalMengajarCetakController extends Controller
{
    /**
     * Resolve filter parameters and query jurnals.
     */
    protected function resolveJurnalData(Request $request): array
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

        $tahunAjaran = $academicYearId ? TahunAjaran::find($academicYearId) : null;
        $mapelId = $request->query('mata_pelajaran_id');
        $classId = $request->query('class_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $periodeType = $request->query('periode_type', 'bulanan');
        $periodeLabel = $request->query('periode_label');

        // Otomatis hitung rentang tanggal jika date_from/date_to kosong
        if (empty($dateFrom) && empty($dateTo) && $tahunAjaran) {
            $sy = $tahunAjaran->start_year ?: (int)date('Y');
            $ey = $tahunAjaran->end_year ?: ($sy + 1);

            if ($periodeType === 'bulanan') {
                $monthYear = $request->query('month_year', date('m-Y'));
                $parts = explode('-', $monthYear);
                $m = (int)($parts[0] ?? date('m'));
                $y = (int)($parts[1] ?? date('Y'));
                $start = Carbon::create($y, $m, 1)->startOfMonth();
                $end   = Carbon::create($y, $m, 1)->endOfMonth();
                $dateFrom = $start->toDateString();
                $dateTo   = $end->toDateString();
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                if (empty($periodeLabel)) {
                    $periodeLabel = 'Bulan ' . ($monthNames[$m] ?? $m) . ' ' . $y;
                }
            } elseif ($periodeType === 'semester') {
                $semester = $request->query('semester', 'ganjil');
                if ($semester === 'ganjil') {
                    $dateFrom = Carbon::create($sy, 7, 1)->toDateString();
                    $dateTo   = Carbon::create($sy, 12, 31)->toDateString();
                    if (empty($periodeLabel)) {
                        $periodeLabel = 'Semester Ganjil TA ' . $tahunAjaran->name;
                    }
                } else {
                    $dateFrom = Carbon::create($ey, 1, 1)->toDateString();
                    $dateTo   = Carbon::create($ey, 6, 30)->toDateString();
                    if (empty($periodeLabel)) {
                        $periodeLabel = 'Semester Genap TA ' . $tahunAjaran->name;
                    }
                }
            } elseif ($periodeType === 'tahunan') {
                $dateFrom = Carbon::create($sy, 7, 1)->toDateString();
                $dateTo   = Carbon::create($ey, 6, 30)->toDateString();
                if (empty($periodeLabel)) {
                    $periodeLabel = 'Tahun Ajaran ' . $tahunAjaran->name;
                }
            }
        }

        if (empty($periodeLabel)) {
            if ($dateFrom && $dateTo) {
                $periodeLabel = Carbon::parse($dateFrom)->format('d/m/Y') . ' s.d. ' . Carbon::parse($dateTo)->format('d/m/Y');
            } else {
                $periodeLabel = 'Semua Periode';
            }
        }

        $guru = $teacherId ? Guru::find($teacherId) : null;
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

        return compact(
            'jurnals',
            'guru',
            'tahunAjaran',
            'mapel',
            'kelas',
            'sekolah',
            'dateFrom',
            'dateTo',
            'periodeType',
            'periodeLabel'
        );
    }

    /**
     * Stream / Download PDF Rekap Jurnal Mengajar.
     */
    public function cetak(Request $request)
    {
        $data = $this->resolveJurnalData($request);
        $guru = $data['guru'];
        $kelas = $data['kelas'];
        $mapel = $data['mapel'];

        $pdf = Pdf::loadView('pdf.jurnal-mengajar-rekap', $data)
            ->setPaper('a4', 'landscape');

        $parts = ['Jurnal_Mengajar'];
        if ($guru) $parts[] = str_replace([' ', '/', '\\'], '_', $guru->name);
        if ($kelas) $parts[] = str_replace([' ', '/', '\\'], '_', $kelas->name);
        if ($mapel) $parts[] = str_replace([' ', '/', '\\'], '_', $mapel->nama_mapel);
        $parts[] = now()->format('Ymd');

        $filename = implode('_', $parts) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download Excel Spreadsheet Rekap Jurnal Mengajar.
     */
    public function excel(Request $request)
    {
        $data = $this->resolveJurnalData($request);
        $guru = $data['guru'];
        $kelas = $data['kelas'];
        $mapel = $data['mapel'];

        $parts = ['Jurnal_Mengajar'];
        if ($guru) $parts[] = str_replace([' ', '/', '\\'], '_', $guru->name);
        if ($kelas) $parts[] = str_replace([' ', '/', '\\'], '_', $kelas->name);
        if ($mapel) $parts[] = str_replace([' ', '/', '\\'], '_', $mapel->nama_mapel);
        $parts[] = now()->format('Ymd');

        $filename = implode('_', $parts) . '.xlsx';

        return Excel::download(
            new JurnalMengajarExport(
                $data['jurnals'],
                $data['guru'],
                $data['tahunAjaran'],
                $data['mapel'],
                $data['kelas'],
                $data['sekolah'],
                $data['periodeLabel']
            ),
            $filename
        );
    }
}
