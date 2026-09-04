<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Pengumuman;
use App\Models\Peminjaman;
use App\Models\Presensi;
use App\Models\LeaveRequest;
use App\Models\AlumniJenjang;
use App\Models\Alumni;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiswaMainDashboard extends Component
{
    public $student;
    public $activeAnnouncements;
    public $attendancePercentage = 0;
    public $activeBooksCount = 0;
    public $pendingIjinCount = 0;
    public $approvedIjinCount = 0;
    public $rejectedIjinCount = 0;
    public $totalIjinCount = 0;
    public $kelasName = '-';

    // Form Properties untuk Alumni (Tracer Study)
    public $status_melanjutkan = false;
    public $jenjang_lanjutan_id = null;
    public $nama_sekolah_lanjutan = '';
    public $tahun_lulus_override = null;
    public $no_hp_alumni = '';

    // Form Properties untuk Siswa Mutasi
    public $tujuan_mutasi = '';
    public $alasan_mutasi = '';
    public $tanggal_mutasi = '';
    public $no_hp_mutasi = '';

    public function mount()
    {
        $this->student = Auth::user()->student;

        // Fetch announcements
        $this->activeAnnouncements = Pengumuman::aktifSekarang()->latest()->get();

        if ($this->student) {
            $enrollment = $this->student->enrollments()->latest('created_at')->first();
            $this->kelasName = $enrollment?->kelas?->name ?? 'Belum Terdaftar';

            // Jika siswa berstatus LULUS (Alumni)
            if ($this->student->isLulus()) {
                $this->status_melanjutkan = (bool) $this->student->status_melanjutkan;
                $this->jenjang_lanjutan_id = $this->student->jenjang_lanjutan_id;
                $this->nama_sekolah_lanjutan = $this->student->nama_sekolah_lanjutan ?? '';
                $this->tahun_lulus_override = $this->student->tahun_lulus_override ?? date('Y');
                $this->no_hp_alumni = $this->student->no_hp ?? '';
            } 
            // Jika siswa berstatus MUTASI
            elseif ($this->student->isMutasi()) {
                $this->tujuan_mutasi = $this->student->tujuan_mutasi ?? '';
                $this->alasan_mutasi = $this->student->alasan_mutasi ?? '';
                $this->tanggal_mutasi = $this->student->tanggal_mutasi?->format('Y-m-d') ?? date('Y-m-d');
                $this->no_hp_mutasi = $this->student->no_hp ?? '';
            }
            // Jika siswa berstatus AKTIF
            else {
                $this->calculateAttendance($enrollment);

                $this->activeBooksCount = Peminjaman::where('peminjam_type', 'siswa')
                    ->where('peminjam_id', $this->student->id)
                    ->where('status', 'dipinjam')
                    ->count();

                $this->pendingIjinCount = LeaveRequest::where('student_id', $this->student->id)
                    ->where('status', 'pending')
                    ->count();
                    
                $this->approvedIjinCount = LeaveRequest::where('student_id', $this->student->id)
                    ->where('status', 'approved')
                    ->count();
                    
                $this->rejectedIjinCount = LeaveRequest::where('student_id', $this->student->id)
                    ->where('status', 'rejected')
                    ->count();

                $this->totalIjinCount = LeaveRequest::where('student_id', $this->student->id)
                    ->count();
            }
        }
    }

    public function simpanTracerAlumni()
    {
        if (!$this->student || !$this->student->isLulus()) return;

        $this->validate([
            'status_melanjutkan'   => 'boolean',
            'jenjang_lanjutan_id'  => 'nullable|exists:alumni_jenjangs,id',
            'nama_sekolah_lanjutan'=> 'nullable|string|max:255',
            'tahun_lulus_override' => 'required|integer|min:2000|max:2099',
            'no_hp_alumni'         => 'nullable|string|max:50',
        ]);

        // Update di tabel students
        $this->student->update([
            'status_melanjutkan'   => $this->status_melanjutkan,
            'jenjang_lanjutan_id'  => $this->status_melanjutkan ? $this->jenjang_lanjutan_id : null,
            'nama_sekolah_lanjutan'=> $this->status_melanjutkan ? $this->nama_sekolah_lanjutan : null,
            'tahun_lulus_override' => $this->tahun_lulus_override,
            'no_hp'                => $this->no_hp_alumni,
        ]);

        // Sinkronkan ke tabel alumnis
        Alumni::updateOrCreate(
            ['student_id' => $this->student->id],
            [
                'source'        => 'sistem',
                'nisn'          => $this->student->nisn,
                'nama'          => $this->student->name,
                'jenis_kelamin' => $this->student->gender === 'P' ? 'P' : 'L',
                'tahun_lulus'   => $this->tahun_lulus_override,
                'melanjutkan'   => $this->status_melanjutkan,
                'jenjang_id'    => $this->status_melanjutkan ? $this->jenjang_lanjutan_id : null,
                'nama_sekolah'  => $this->status_melanjutkan ? $this->nama_sekolah_lanjutan : null,
                'no_hp'         => $this->no_hp_alumni,
                'foto'          => $this->student->photo_path,
            ]
        );

        session()->flash('success_tracer', 'Data Tracer Study Anda berhasil diperbarui! Terima kasih atas partisipasinya.');
    }

    public function simpanDataMutasi()
    {
        if (!$this->student || !$this->student->isMutasi()) return;

        $this->validate([
            'tujuan_mutasi'  => 'required|string|max:255',
            'alasan_mutasi'  => 'nullable|string|max:1000',
            'tanggal_mutasi' => 'nullable|date',
            'no_hp_mutasi'   => 'nullable|string|max:50',
        ]);

        $this->student->update([
            'tujuan_mutasi'  => $this->tujuan_mutasi,
            'alasan_mutasi'  => $this->alasan_mutasi,
            'tanggal_mutasi' => $this->tanggal_mutasi,
            'no_hp'          => $this->no_hp_mutasi,
        ]);

        session()->flash('success_mutasi', 'Data sekolah tujuan mutasi Anda berhasil disimpan.');
    }

    private function calculateAttendance($enrollment)
    {
        if (!$this->student || !$enrollment) {
            $this->attendancePercentage = 0;
            return;
        }

        $now = Carbon::now('Asia/Jakarta');
        $startOfMonth = $now->copy()->startOfMonth();
        $endCalcDate = $now->copy();

        $presensiList = Presensi::where('student_id', $this->student->id)
            ->where('enrollment_id', $enrollment->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endCalcDate->format('Y-m-d')])
            ->get();

        $totalH = 0;
        $totalT = 0;

        foreach ($presensiList as $p) {
            if (strtolower($p->status) === 'hadir') $totalH++;
            if (strtolower($p->status) === 'telat') $totalT++;
        }

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $effectiveDays = $kalenderService->getEffectiveDays($startOfMonth, $endCalcDate, $enrollment->class_id);
        $effectiveDays = max(1, $effectiveDays);

        $presentCount = $totalH + $totalT;
        $this->attendancePercentage = min(100, round(($presentCount / $effectiveDays) * 100, 1));
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $jenjangs = AlumniJenjang::all();

        return view('livewire.siswa-main-dashboard', [
            'jenjangs' => $jenjangs,
        ]);
    }
}
