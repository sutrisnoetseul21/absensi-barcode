<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\KelompokGuruWali;
use App\Models\KonsultasiGuruWali;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class KonsultasiList extends Component
{
    use WithPagination;

    public $teacher;
    public $kelompok;

    // Filter & Tab
    public $activeTab = 'menunggu'; // 'menunggu', 'dijadwalkan', 'selesai', 'ditolak', 'semua'
    public $search = '';
    public $kategori = '';

    // Modal Konfirmasi / Jadwalkan
    public $showScheduleModal = false;
    public $consultationToSchedule = null;
    public $jadwal_pasti = '';
    public $tanggapan_jadwal = '';

    // Modal Tanggapan Pesan (Mode Pesan Portal / Selesai)
    public $showReplyModal = false;
    public $consultationToReply = null;
    public $tanggapan_pesan = '';
    public $markAsCompleted = false;

    // Modal Tolak
    public $showRejectModal = false;
    public $consultationToReject = null;
    public $alasan_penolakan = '';

    // Modal Detail
    public $showDetailModal = false;
    public $selectedConsultation = null;

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

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingKategori()
    {
        $this->resetPage();
    }

    // ── AKSI DETAIL ──────────────────────────────────────────────────
    public function openDetail($id)
    {
        $this->selectedConsultation = KonsultasiGuruWali::with(['siswa.enrollmentAktif.kelas', 'jurnal'])
            ->where('teacher_id', $this->teacher->id)
            ->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function closeDetail()
    {
        $this->showDetailModal = false;
        $this->selectedConsultation = null;
    }

    // ── AKSI JADWALKAN ───────────────────────────────────────────────
    public function openScheduleModal($id)
    {
        $this->consultationToSchedule = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);
        $this->jadwal_pasti = $this->consultationToSchedule->usulan_tanggal_waktu 
            ? Carbon::parse($this->consultationToSchedule->usulan_tanggal_waktu)->format('Y-m-d\TH:i') 
            : Carbon::now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d\TH:i');
        $this->tanggapan_jadwal = $this->consultationToSchedule->tanggapan_guru ?? 'Jadwal konsultasi disetujui. Silakan temui di ruang Guru Wali.';
        $this->showScheduleModal = true;
    }

    public function saveSchedule()
    {
        $this->validate([
            'jadwal_pasti' => 'required|date',
        ], [
            'jadwal_pasti.required' => 'Tentukan jadwal pasti pertemuan.',
        ]);

        if ($this->consultationToSchedule) {
            $this->consultationToSchedule->update([
                'status_pengajuan' => 'Dijadwalkan',
                'jadwal_pasti'     => $this->jadwal_pasti,
                'tanggapan_guru'   => $this->tanggapan_jadwal ?: null,
            ]);

            session()->flash('success', 'Jadwal konsultasi berhasil dikonfirmasi dan ditetapkan.');
            $this->showScheduleModal = false;
            $this->consultationToSchedule = null;
        }
    }

    // ── AKSI BALAS PESAN / TANGGAPAN ────────────────────────────────
    public function openReplyModal($id)
    {
        $this->consultationToReply = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);
        $this->tanggapan_pesan = $this->consultationToReply->tanggapan_guru ?? '';
        $this->markAsCompleted = $this->consultationToReply->status_pengajuan === 'Selesai';
        $this->showReplyModal = true;
    }

    public function saveReply()
    {
        $this->validate([
            'tanggapan_pesan' => 'required|string|min:3',
        ], [
            'tanggapan_pesan.required' => 'Tanggapan guru wajib diisi.',
        ]);

        if ($this->consultationToReply) {
            $updateData = [
                'tanggapan_guru' => $this->tanggapan_pesan,
            ];

            if ($this->markAsCompleted) {
                $updateData['status_pengajuan'] = 'Selesai';
            } elseif ($this->consultationToReply->status_pengajuan === 'Menunggu Konfirmasi') {
                $updateData['status_pengajuan'] = 'Dijadwalkan';
            }

            $this->consultationToReply->update($updateData);

            session()->flash('success', 'Tanggapan guru berhasil dikirim.');
            $this->showReplyModal = false;
            $this->consultationToReply = null;
        }
    }

    // ── AKSI TOLAK ───────────────────────────────────────────────────
    public function openRejectModal($id)
    {
        $this->consultationToReject = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);
        $this->alasan_penolakan = '';
        $this->showRejectModal = true;
    }

    public function saveReject()
    {
        $this->validate([
            'alasan_penolakan' => 'required|string|min:3',
        ], [
            'alasan_penolakan.required' => 'Sebutkan alasan penolakan atau penjadwalan ulang.',
        ]);

        if ($this->consultationToReject) {
            $this->consultationToReject->update([
                'status_pengajuan' => 'Ditolak',
                'alasan_penolakan' => $this->alasan_penolakan,
            ]);

            session()->flash('success', 'Permintaan konsultasi telah ditolak.');
            $this->showRejectModal = false;
            $this->consultationToReject = null;
        }
    }

    // ── TANDAI SELESAI ───────────────────────────────────────────────
    public function markCompleted($id)
    {
        $c = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);
        $c->update(['status_pengajuan' => 'Selesai']);
        session()->flash('success', 'Konsultasi ditandai selesai.');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $query = KonsultasiGuruWali::with(['siswa.enrollmentAktif.kelas'])
            ->where('teacher_id', $this->teacher->id);

        // Filter tab
        if ($this->activeTab === 'menunggu') {
            $query->where('status_pengajuan', 'Menunggu Konfirmasi');
        } elseif ($this->activeTab === 'dijadwalkan') {
            $query->where('status_pengajuan', 'Dijadwalkan');
        } elseif ($this->activeTab === 'selesai') {
            $query->whereIn('status_pengajuan', ['Selesai', 'Dikonversi ke Jurnal']);
        } elseif ($this->activeTab === 'ditolak') {
            $query->where('status_pengajuan', 'Ditolak');
        }

        // Search
        if (trim($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('topik_konsultasi', 'like', $term)
                    ->orWhere('detail_permasalahan', 'like', $term)
                    ->orWhereHas('siswa', function ($sq) use ($term) {
                        $sq->where('name', 'like', $term)
                            ->orWhere('nisn', 'like', $term);
                    });
            });
        }

        // Kategori
        if ($this->kategori) {
            $query->where('kategori_pendampingan', $this->kategori);
        }

        $konsultasis = $query->latest('created_at')->paginate(10);

        // Hitung count per status
        $countMenunggu = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
            ->where('status_pengajuan', 'Menunggu Konfirmasi')
            ->count();
        $countDijadwalkan = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
            ->where('status_pengajuan', 'Dijadwalkan')
            ->count();
        $countSelesai = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
            ->whereIn('status_pengajuan', ['Selesai', 'Dikonversi ke Jurnal'])
            ->count();
        $countDitolak = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
            ->where('status_pengajuan', 'Ditolak')
            ->count();

        return view('livewire.portal-guru.guru-wali.konsultasi-list', [
            'konsultasis'      => $konsultasis,
            'countMenunggu'    => $countMenunggu,
            'countDijadwalkan' => $countDijadwalkan,
            'countSelesai'     => $countSelesai,
            'countDitolak'     => $countDitolak,
        ]);
    }
}
