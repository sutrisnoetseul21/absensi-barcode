<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PengaturanSekolah;
use App\Models\KunjunganPerpustakaan;
use Carbon\Carbon;

class KunjunganKiosk extends Component
{
    public function render()
    {
        $settings = PengaturanSekolah::current();

        $recentVisitsToday = KunjunganPerpustakaan::where('tanggal', now('Asia/Jakarta')->toDateString())
            ->with(['pengunjung'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentScansData = $recentVisitsToday->map(function ($v) {
            $nama = $v->pengunjung ? $v->pengunjung->name : 'Pengunjung';
            $kelas = $v->pengunjung_type === 'siswa' && $v->pengunjung && $v->pengunjung->enrollmentAktif 
                ? 'Kelas ' . $v->pengunjung->enrollmentAktif->kelas->name 
                : ($v->pengunjung_type === 'guru' ? 'Guru / Staff' : 'Anggota');
            $photo = $v->pengunjung_type === 'siswa' && $v->pengunjung && $v->pengunjung->photo_path 
                ? asset('storage/' . $v->pengunjung->photo_path) 
                : null;
            return [
                'id' => $v->id,
                'name' => $nama,
                'class_name' => $kelas,
                'photo_url' => $photo,
                'time' => Carbon::parse($v->waktu_masuk)->format('H:i:s')
            ];
        })->values()->toArray();

        return view('livewire.kunjungan-kiosk', [
            'settings' => $settings,
            'recentScansData' => $recentScansData,
        ])->title('Kiosk Presensi Kunjungan Perpustakaan');
    }
}
