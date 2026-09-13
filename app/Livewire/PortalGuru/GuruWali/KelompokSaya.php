<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class KelompokSaya extends Component
{
    public $teacher;
    public $kelompok;
    public $search = '';
    public $activeTab = 'aktif'; // 'aktif' | 'arsip'

    public function mount()
    {
        $user = Auth::user();
        $this->teacher = $user->teacher;

        if (! $this->teacher) {
            abort(403, 'Anda tidak terdaftar sebagai guru.');
        }

        $this->kelompok = KelompokGuruWali::where('teacher_id', $this->teacher->id)->first();

        if (! $this->kelompok || ! $this->kelompok->status_aktif) {
            abort(403, 'Anda belum memiliki tugas penugasan Guru Wali aktif.');
        }
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // ── 1. Siswa Aktif ───────────────────────────────────────────
        $queryAktif = $this->kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->whereHas('siswa', function ($q) {
                if (trim($this->search)) {
                    $q->where(function ($sub) {
                        $sub->where('name', 'like', '%' . trim($this->search) . '%')
                            ->orWhere('nisn', 'like', '%' . trim($this->search) . '%');
                    });
                }
            });

        $siswaAktifList = $queryAktif->get();

        // Map status pendampingan bulan ini untuk setiap siswa aktif
        $siswaIds = $siswaAktifList->pluck('student_id')->toArray();
        $jurnalsBulanIni = JurnalGuruWali::where('teacher_id', $this->teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->whereBetween('tanggal_waktu', [$startOfMonth, $endOfMonth])
            ->latest('tanggal_waktu')
            ->get()
            ->groupBy('student_id');

        $siswaAktifData = $siswaAktifList->map(function ($item) use ($jurnalsBulanIni) {
            $jurnals = $jurnalsBulanIni->get($item->student_id, collect());
            $lastJurnal = $jurnals->first();

            return (object) [
                'anggota_id'        => $item->id,
                'student_id'        => $item->student_id,
                'siswa'             => $item->siswa,
                'tahun_masuk'       => $item->tahun_masuk,
                'has_bimbingan'     => $jurnals->isNotEmpty(),
                'bimbingan_count'   => $jurnals->count(),
                'last_bimbingan_at' => $lastJurnal?->tanggal_waktu,
                'last_kategori'     => $lastJurnal?->kategori_pendampingan,
                'last_status'       => $lastJurnal?->status_sesi,
            ];
        });

        // ── 2. Siswa Arsip (Riwayat) ──────────────────────────────────
        $queryArsip = $this->kelompok->anggotaArsip()
            ->with(['siswa.enrollmentAktif.kelas', 'siswa.kelompokGuruWali.kelompok'])
            ->whereHas('siswa', function ($q) {
                if (trim($this->search)) {
                    $q->where(function ($sub) {
                        $sub->where('name', 'like', '%' . trim($this->search) . '%')
                            ->orWhere('nisn', 'like', '%' . trim($this->search) . '%');
                    });
                }
            });

        $siswaArsipList = $queryArsip->get();

        // ── 3. Statistik ──────────────────────────────────────────────
        $totalAktif = $this->kelompok->anggotaAktif()->count();
        $totalArsip = $this->kelompok->anggotaArsip()->count();
        $totalDidampingiBulanIni = count($jurnalsBulanIni);
        $persenDidampingi = $totalAktif > 0 ? round(($totalDidampingiBulanIni / $totalAktif) * 100) : 0;

        return view('livewire.portal-guru.guru-wali.kelompok-saya', [
            'siswaAktifData'   => $siswaAktifData,
            'siswaArsipList'   => $siswaArsipList,
            'totalAktif'       => $totalAktif,
            'totalArsip'       => $totalArsip,
            'didampingiCount'  => $totalDidampingiBulanIni,
            'persenDidampingi' => $persenDidampingi,
            'namaBulanIni'     => Carbon::now()->translatedFormat('F Y'),
        ]);
    }
}
