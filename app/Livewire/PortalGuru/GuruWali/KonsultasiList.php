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
    public $pesanBaru = '';
    public $pesanInputs = [];

    // Rujuk BK variables
    public $showRujukModal = false;
    public $alasan_rujukan = '';
    public $consultationToRujuk;

    public $activeYear;

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

        $this->activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
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
        $this->selectedConsultation = KonsultasiGuruWali::with(['siswa.enrollmentAktif.kelas', 'jurnal', 'pesan'])
            ->where('teacher_id', $this->teacher->id)
            ->findOrFail($id);

        // Tandai pesan dari siswa sebagai sudah dibaca
        if ($this->selectedConsultation) {
            \App\Models\PesanKonsultasiGuruWali::where('konsultasi_id', $this->selectedConsultation->id)
                ->where('sender_type', 'siswa')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $this->showDetailModal = true;
    }

    public function closeDetail()
    {
        $this->showDetailModal = false;
        $this->selectedConsultation = null;
        $this->pesanBaru = '';
    }

    public function kirimPesanObrolan()
    {
        $this->validate(['pesanBaru' => 'required|string|min:2|max:2000'], [
            'pesanBaru.required' => 'Pesan tidak boleh kosong.',
            'pesanBaru.min' => 'Pesan minimal 2 karakter.',
        ]);

        if (!$this->selectedConsultation) return;

        if (in_array($this->selectedConsultation->status_pengajuan?->value, [\App\Enums\StatusPengajuan::MenungguKonfirmasi->value, \App\Enums\StatusPengajuan::Dijadwalkan->value])) {
            \App\Models\PesanKonsultasiGuruWali::create([
                'konsultasi_id' => $this->selectedConsultation->id,
                'sender_type'   => 'guru',
                'pesan'         => $this->pesanBaru,
                'is_read'       => false,
            ]);

            // Update status ke Dijadwalkan (direspon) jika masih menunggu konfirmasi
            if ($this->selectedConsultation->status_pengajuan?->value === \App\Enums\StatusPengajuan::MenungguKonfirmasi->value) {
                $this->selectedConsultation->update(['status_pengajuan' => \App\Enums\StatusPengajuan::Dijadwalkan]);
            }
            
            // Simpan snippet untuk backward compatibility
            $this->selectedConsultation->update(['tanggapan_guru' => $this->pesanBaru]);

            $this->pesanBaru = '';
            $this->selectedConsultation->load('pesan');
        }
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
                'status_pengajuan' => \App\Enums\StatusPengajuan::Dijadwalkan,
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
        $this->markAsCompleted = $this->consultationToReply->status_pengajuan?->value === \App\Enums\StatusPengajuan::Selesai->value;
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
                $updateData['status_pengajuan'] = \App\Enums\StatusPengajuan::Selesai;
            } elseif ($this->consultationToReply->status_pengajuan?->value === \App\Enums\StatusPengajuan::MenungguKonfirmasi->value) {
                $updateData['status_pengajuan'] = \App\Enums\StatusPengajuan::Dijadwalkan;
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
                'status_pengajuan' => \App\Enums\StatusPengajuan::Ditolak,
                'alasan_penolakan' => $this->alasan_penolakan,
            ]);

            session()->flash('success', 'Permintaan konsultasi telah ditolak.');
            $this->showRejectModal = false;
            $this->consultationToReject = null;
        }
    }

    // ── TANDAI SELESAI & BADGE ───────────────────────────────────────────────
    public $showBadgeModal = false;
    public $consultationToComplete = null;
    public $selectedBadge = '';
    public $catatan_apresiasi = '';

    public function openBadgeModal($id)
    {
        $this->consultationToComplete = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);
        $this->selectedBadge = '';
        $this->catatan_apresiasi = '';
        $this->showBadgeModal = true;
    }

    public function markCompletedWithBadge()
    {
        if ($this->consultationToComplete) {
            $this->consultationToComplete->update(['status_pengajuan' => \App\Enums\StatusPengajuan::Selesai]);

            if (!empty($this->selectedBadge)) {
                \App\Models\BadgeKarakterSiswa::create([
                    'student_id' => $this->consultationToComplete->student_id,
                    'teacher_id' => $this->teacher->id,
                    'academic_year_id' => $this->activeYear?->id,
                    'class_id' => $this->consultationToComplete->siswa?->resolveKelasModel($this->activeYear?->id)?->id,
                    'nama_badge' => $this->selectedBadge,
                    'catatan_apresiasi' => $this->catatan_apresiasi,
                    'source_type' => get_class($this->consultationToComplete),
                    'source_id' => $this->consultationToComplete->id,
                ]);
                session()->flash('success', 'Konsultasi selesai dan badge karakter diberikan kepada siswa.');
            } else {
                session()->flash('success', 'Konsultasi ditandai selesai tanpa pemberian badge.');
            }

            $this->showBadgeModal = false;
            $this->consultationToComplete = null;
        }
    }

    // ── RUJUK KE BK ──────────────────────────────────────────────────
    public function openRujukModal($id)
    {
        $this->consultationToRujuk = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);
        $this->alasan_rujukan = '';
        $this->showRujukModal = true;
    }

    public function saveRujuk()
    {
        $this->validate([
            'alasan_rujukan' => 'required|string|min:10',
        ], [
            'alasan_rujukan.required' => 'Mohon jelaskan alasan mengapa kasus ini perlu dirujuk ke Guru BK.',
            'alasan_rujukan.min' => 'Alasan rujukan minimal 10 karakter.'
        ]);

        if ($this->consultationToRujuk) {
            // Ubah status konsultasi saat ini agar diketahui bahwa ini sudah dialihkan
            $this->consultationToRujuk->update([
                'status_pengajuan' => \App\Enums\StatusPengajuan::DikonversiKeJurnal, // atau kita biarkan selesai dengan flag khusus
            ]);

            // Buat record Konseling BK
            \App\Models\KonselingBk::create([
                'academic_year_id' => $this->activeYear?->id,
                'class_id' => $this->consultationToRujuk->siswa->enrollmentAktif->class_id ?? null,
                'teacher_id' => null, // Belum diambil oleh guru BK spesifik
                'student_id' => $this->consultationToRujuk->student_id,
                'konsultasi_guru_wali_id' => $this->consultationToRujuk->id,
                'is_rujukan' => true,
                'alasan_rujukan' => $this->alasan_rujukan,
                'status_kasus' => \App\Enums\StatusKasus::DalamProses,
                'is_rahasia' => true,
            ]);

            session()->flash('success', 'Kasus berhasil dirujuk ke Guru BK.');
            $this->showRujukModal = false;
            $this->consultationToRujuk = null;
        }
    }

    public function kirimPesanInline($id)
    {
        $input = trim($this->pesanInputs[$id] ?? $this->pesanBaru);
        if (empty($input) || strlen($input) < 2) {
            $this->addError("pesanInputs.{$id}", 'Pesan balasan minimal 2 karakter.');
            return;
        }

        $konsul = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);

        if (in_array($konsul->status_pengajuan?->value, ['Menunggu Konfirmasi', 'Dijadwalkan'])) {
            \App\Models\PesanKonsultasiGuruWali::create([
                'konsultasi_id' => $konsul->id,
                'sender_type'   => 'guru',
                'pesan'         => $input,
                'is_read'       => false,
            ]);

            // Jika status masih Menunggu Konfirmasi, ubah jadi Dijadwalkan (sudah dibalas)
            if ($konsul->status_pengajuan?->value === 'Menunggu Konfirmasi') {
                $konsul->update(['status_pengajuan' => \App\Enums\StatusPengajuan::Dijadwalkan]);
            }

            $konsul->update(['tanggapan_guru' => $input]);

            $this->pesanInputs[$id] = '';
            $this->pesanBaru = '';
        }
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $query = KonsultasiGuruWali::with(['siswa.enrollmentAktif.kelas', 'jurnal', 'pesan', 'guru'])
            ->where('teacher_id', $this->teacher->id);

        // Filter tab
        if ($this->activeTab === 'menunggu') {
            $query->where('status_pengajuan', \App\Enums\StatusPengajuan::MenungguKonfirmasi);
        } elseif ($this->activeTab === 'dijadwalkan') {
            $query->where('status_pengajuan', \App\Enums\StatusPengajuan::Dijadwalkan);
        } elseif ($this->activeTab === 'selesai') {
            $query->whereIn('status_pengajuan', [\App\Enums\StatusPengajuan::Selesai, \App\Enums\StatusPengajuan::DikonversiKeJurnal]);
        } elseif ($this->activeTab === 'ditolak') {
            $query->where('status_pengajuan', \App\Enums\StatusPengajuan::Ditolak);
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
            ->where('status_pengajuan', \App\Enums\StatusPengajuan::MenungguKonfirmasi)
            ->count();
        $countDijadwalkan = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
            ->where('status_pengajuan', \App\Enums\StatusPengajuan::Dijadwalkan)
            ->count();
        $countSelesai = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
            ->whereIn('status_pengajuan', [\App\Enums\StatusPengajuan::Selesai, \App\Enums\StatusPengajuan::DikonversiKeJurnal])
            ->count();
        $countDitolak = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
            ->where('status_pengajuan', \App\Enums\StatusPengajuan::Ditolak)
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
