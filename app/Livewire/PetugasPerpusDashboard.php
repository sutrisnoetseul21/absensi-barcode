<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Peminjaman;
use App\Models\KunjunganPerpustakaan;
use App\Models\PengaturanSekolah;
use Carbon\Carbon;

#[Layout('components.layouts.portal')]
class PetugasPerpusDashboard extends Component
{
    public function render()
    {
        $settings = PengaturanSekolah::current();
        $today = Carbon::today('Asia/Jakarta');

        $totalJudulBuku = Buku::count();
        $totalEksemplar = EksemplarBuku::count();
        $eksemplarTersedia = EksemplarBuku::where('status', 'tersedia')->count();
        $eksemplarDipinjam = EksemplarBuku::where('status', 'dipinjam')->count();

        $peminjamanAktifCount = Peminjaman::where('status', 'dipinjam')->count();
        $peminjamanTerlambatCount = Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->count();

        $kunjunganHariIniCount = KunjunganPerpustakaan::where('tanggal', $today->toDateString())->count();

        // Overdue list (max 5)
        $overdueLoans = Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->with(['peminjam', 'eksemplarBuku.buku'])
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->take(5)
            ->get();

        // Recent Visits today (max 5)
        $recentVisits = KunjunganPerpustakaan::where('tanggal', $today->toDateString())
            ->with(['pengunjung'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.petugas-perpus-dashboard', [
            'settings' => $settings,
            'totalJudulBuku' => $totalJudulBuku,
            'totalEksemplar' => $totalEksemplar,
            'eksemplarTersedia' => $eksemplarTersedia,
            'eksemplarDipinjam' => $eksemplarDipinjam,
            'peminjamanAktifCount' => $peminjamanAktifCount,
            'peminjamanTerlambatCount' => $peminjamanTerlambatCount,
            'kunjunganHariIniCount' => $kunjunganHariIniCount,
            'overdueLoans' => $overdueLoans,
            'recentVisits' => $recentVisits,
        ])->title('Dashboard Portal Perpustakaan');
    }
}
