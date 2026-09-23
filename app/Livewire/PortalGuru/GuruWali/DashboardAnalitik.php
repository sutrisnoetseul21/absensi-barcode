<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\JurnalGuruWali;
use App\Models\KonsultasiGuruWali;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DashboardAnalitik extends Component
{
    public $kpi = [];
    public $petaPilar = [];
    public $sentimenSiswa = [];
    public $topGuru = [];

    public function mount()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $isAuthorized = $user->hasRole(['guru_bk', 'Guru BK']) || 
                        ($teacher && $teacher->hasJabatan(['Kepala Sekolah', 'Waka Kesiswaan', 'Wakil Kepala Sekolah Bidang Kesiswaan']));

        if (!$isAuthorized) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk melihat Command Center.');
        }

        $this->loadMetrik();
    }

    private function loadMetrik()
    {
        // 1. KPI Overview
        $totalJurnal = JurnalGuruWali::count();
        $totalKonsultasi = KonsultasiGuruWali::count();
        
        $konsultasiSelesai = KonsultasiGuruWali::whereIn('status_pengajuan', ['Selesai', 'Dikonversi ke Jurnal'])->count();
        $persentaseSelesai = $totalKonsultasi > 0 ? round(($konsultasiSelesai / $totalKonsultasi) * 100) : 0;
        
        $avgRating = KonsultasiGuruWali::whereNotNull('student_feedback_rating')->avg('student_feedback_rating');

        $this->kpi = [
            'total_bimbingan' => $totalJurnal + $totalKonsultasi,
            'persentase_selesai' => $persentaseSelesai,
            'avg_rating' => $avgRating ? number_format($avgRating, 1) : 0,
            'total_konsultasi_selesai' => $konsultasiSelesai,
        ];

        // 2. Peta Isu 4 Pilar
        // Kategori: Akademik & Belajar, Karakter & Kedisiplinan, Minat & Bakat / Ekskul, Sosial & Psikologis
        $pilarKonsultasi = KonsultasiGuruWali::select('kategori_pendampingan', DB::raw('count(*) as total'))
            ->groupBy('kategori_pendampingan')
            ->pluck('total', 'kategori_pendampingan')
            ->toArray();
            
        $pilarJurnal = JurnalGuruWali::select('kategori_pendampingan', DB::raw('count(*) as total'))
            ->groupBy('kategori_pendampingan')
            ->pluck('total', 'kategori_pendampingan')
            ->toArray();

        // Merge and sum
        $allPilar = [];
        $kategoriUtama = [
            'Akademik & Belajar', 
            'Karakter & Kedisiplinan', 
            'Minat & Bakat / Ekskul', 
            'Sosial & Psikologis'
        ];
        
        foreach ($kategoriUtama as $kat) {
            $allPilar[$kat] = ($pilarKonsultasi[$kat] ?? 0) + ($pilarJurnal[$kat] ?? 0);
        }
        $this->petaPilar = $allPilar;

        // 3. Sentimen & Emosi Siswa
        // Ambil emoji feedback
        $this->sentimenSiswa = KonsultasiGuruWali::whereNotNull('student_feedback_emoji')
            ->select('student_feedback_emoji', DB::raw('count(*) as total'))
            ->groupBy('student_feedback_emoji')
            ->pluck('total', 'student_feedback_emoji')
            ->toArray();

        // 4. Leaderboard Guru Wali
        // Top 5 guru berdasarkan jumlah Jurnal + Konsultasi
        $this->topGuru = DB::table('teachers')
            ->leftJoin('jurnal_guru_walis', 'teachers.id', '=', 'jurnal_guru_walis.teacher_id')
            ->select('teachers.name', 'teachers.photo_path', 'teachers.jenis_kelamin', DB::raw('count(jurnal_guru_walis.id) as total_jurnal'))
            ->groupBy('teachers.id', 'teachers.name', 'teachers.photo_path', 'teachers.jenis_kelamin')
            ->orderByDesc('total_jurnal')
            ->limit(5)
            ->get();
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-guru.guru-wali.dashboard-analitik');
    }
}
