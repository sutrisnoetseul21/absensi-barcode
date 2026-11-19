<?php

namespace App\Livewire;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerPertemuan;
use App\Models\EkstrakurikulerPresensi;
use App\Models\PengaturanSekolah;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AttendanceKioskEkstrakurikuler extends Component
{
    public Ekstrakurikuler $ekskul;
    public string $judulKegiatan = '';
    public ?int $activePertemuanKe = null;

    public function mount(string $slug): void
    {
        abort_if(
            !(PengaturanSekolah::current()?->enable_ekstrakurikuler ?? true),
            403,
            'Fitur Ekstrakurikuler sedang dinonaktifkan.'
        );

        $this->ekskul = Ekstrakurikuler::with(['pembinas', 'tahunAjaran'])
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless(
            $this->ekskul->canUserOpenKiosk(Auth::user()),
            403,
            'Anda tidak memiliki akses ke scanner barcode ekstrakurikuler ini.'
        );

        abort_if(
            !$this->ekskul->status_aktif,
            403,
            "Ekstrakurikuler {$this->ekskul->nama_ekskul} sedang dinonaktifkan."
        );

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $todayPertemuan = EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $this->ekskul->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($todayPertemuan) {
            $this->activePertemuanKe = $todayPertemuan->pertemuan_ke;
            $this->judulKegiatan = $todayPertemuan->judul_kegiatan ?? '';
        } else {
            $nextKe = ((int) EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $this->ekskul->id)->max('pertemuan_ke')) + 1;
            $this->activePertemuanKe = $nextKe;
        }
    }

    public function saveJudulKegiatan(): void
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $pertemuan = EkstrakurikulerPertemuan::findOrCreateForDate(
            $this->ekskul,
            $today,
            Auth::user(),
            trim($this->judulKegiatan)
        );
        $pertemuan->update(['judul_kegiatan' => trim($this->judulKegiatan) ?: null]);
        $this->activePertemuanKe = $pertemuan->pertemuan_ke;

        session()->flash('kiosk_saved', "Materi Pertemuan ke-{$pertemuan->pertemuan_ke} disimpan.");
    }

    public function render()
    {
        $settings = PengaturanSekolah::current();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $totalMembers = $this->ekskul->anggotaAktif()->count();
        $todayPertemuan = EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $this->ekskul->id)
            ->whereDate('tanggal', $today)
            ->first();

        $todayScannedCount = 0;
        $recentScans = collect();

        if ($todayPertemuan) {
            $this->activePertemuanKe = $todayPertemuan->pertemuan_ke;
            $todayScannedCount = EkstrakurikulerPresensi::where('pertemuan_id', $todayPertemuan->id)
                ->where('status', 'hadir')
                ->count();

            $yearId = $this->ekskul->academic_year_id;
            $recentScans = EkstrakurikulerPresensi::with([
                'siswa.enrollments' => fn ($q) => $q->where('academic_year_id', $yearId)
                    ->where('status', 'aktif')
                    ->with('kelas'),
            ])
                ->where('pertemuan_id', $todayPertemuan->id)
                ->where('status', 'hadir')
                ->orderBy('updated_at', 'desc')
                ->take(15)
                ->get();
        }

        return view('livewire.attendance-kiosk-ekstrakurikuler', [
            'settings'          => $settings,
            'now'               => $now,
            'totalMembers'      => $totalMembers,
            'todayPertemuan'    => $todayPertemuan,
            'todayScannedCount' => $todayScannedCount,
            'recentScans'       => $recentScans,
        ])->title('Kiosk Presensi ' . $this->ekskul->nama_ekskul . ' - ' . ($settings->school_name ?? 'Sekolah'));
    }
}

