<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PemantauanBulanan extends Component
{
    public $teacher;
    public $kelompok;
    public $selectedMonth; // 'Y-m'

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

        $this->selectedMonth = Carbon::now()->format('Y-m');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        try {
            $startDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->endOfMonth();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $this->selectedMonth = Carbon::now()->format('Y-m');
        }

        // Anggota Siswa Aktif
        $anggotaAktif = $this->kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get();

        $siswaIds = $anggotaAktif->pluck('student_id')->toArray();

        // Ambil semua jurnal bulan terpilih untuk kelompok ini
        $jurnalsBulanIni = JurnalGuruWali::where('teacher_id', $this->teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->whereBetween('tanggal_waktu', [$startDate, $endDate])
            ->latest('tanggal_waktu')
            ->get()
            ->groupBy('student_id');

        // Ambil bimbingan terakhir kapanpun (untuk siswa yang belum didampingi bulan ini)
        $allLastJurnals = JurnalGuruWali::where('teacher_id', $this->teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->latest('tanggal_waktu')
            ->get()
            ->groupBy('student_id');

        $siswaSudah = [];
        $siswaBelum = [];

        foreach ($anggotaAktif as $anggota) {
            $jurnals = $jurnalsBulanIni->get($anggota->student_id, collect());
            $allJurnal = $allLastJurnals->get($anggota->student_id, collect());
            $lastEver = $allJurnal->first();

            $item = (object) [
                'anggota_id'        => $anggota->id,
                'student_id'        => $anggota->student_id,
                'siswa'             => $anggota->siswa,
                'jurnals_bulan_ini' => $jurnals,
                'sesi_count'        => $jurnals->count(),
                'last_sesi'         => $jurnals->first(),
                'last_ever_date'    => $lastEver?->tanggal_waktu,
            ];

            if ($jurnals->isNotEmpty()) {
                $siswaSudah[] = $item;
            } else {
                $siswaBelum[] = $item;
            }
        }

        $totalSiswa = count($anggotaAktif);
        $totalSudah = count($siswaSudah);
        $totalBelum = count($siswaBelum);
        $totalSesi = $jurnalsBulanIni->flatten()->count();
        $persentase = $totalSiswa > 0 ? round(($totalSudah / $totalSiswa) * 100) : 0;

        // Distribusi Kategori Bimbingan Bulan Terpilih
        $kategoriCounts = [
            'Akademik'                => 0,
            'Karakter & Kedisiplinan' => 0,
            'Minat & Bakat / Ekskul'  => 0,
            'Sosial & Psikologis'     => 0,
        ];

        foreach ($jurnalsBulanIni->flatten() as $j) {
            if (isset($kategoriCounts[$j->kategori_pendampingan])) {
                $kategoriCounts[$j->kategori_pendampingan]++;
            }
        }

        return view('livewire.portal-guru.guru-wali.pemantauan-bulanan', [
            'siswaSudah'     => collect($siswaSudah),
            'siswaBelum'     => collect($siswaBelum),
            'totalSiswa'     => $totalSiswa,
            'totalSudah'     => $totalSudah,
            'totalBelum'     => $totalBelum,
            'totalSesi'      => $totalSesi,
            'persentase'     => $persentase,
            'kategoriCounts' => $kategoriCounts,
            'labelBulan'     => $startDate->translatedFormat('F Y'),
        ]);
    }
}
