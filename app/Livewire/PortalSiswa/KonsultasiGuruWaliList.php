<?php

namespace App\Livewire\PortalSiswa;

use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWaliSiswa;
use App\Models\KonsultasiGuruWali;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class KonsultasiGuruWaliList extends Component
{
    use WithPagination;

    public string $activeTab = 'semua'; // semua, menunggu, dijadwalkan, selesai, ditolak, catatan_guru
    public string $search = '';
    public string $kategoriFilter = '';

    // Modal Form Pengajuan
    public bool $showModal = false;
    public string $kategori = 'Akademik';
    public string $mode = 'Tatap Muka';
    public string $topik = '';
    public string $detail = '';
    public ?string $usulan_tanggal_waktu = null;

    // Modal Detail Konsultasi
    public bool $showDetailModal = false;
    public ?KonsultasiGuruWali $selectedKonsultasi = null;

    // Modal Detail Catatan Dampingan Publik
    public bool $showCatatanModal = false;
    public ?JurnalGuruWali $selectedCatatan = null;

    // Obrolan
    public string $pesanBaru = '';

    // Modal Feedback
    public bool $showFeedbackModal = false;
    public ?KonsultasiGuruWali $feedbackKonsultasi = null;
    public ?int $feedback_rating = null;
    public ?string $feedback_emoji = null;
    public string $feedback_note = '';

    protected $rules = [
        'kategori'             => 'required|in:Akademik,Karakter & Kedisiplinan,Minat & Bakat / Ekskul,Sosial & Psikologis',
        'mode'                 => 'required|in:Tatap Muka,Pesan Portal',
        'topik'                => 'required|string|min:5|max:255',
        'detail'               => 'required|string|min:10|max:5000',
        'usulan_tanggal_waktu' => 'nullable|date|after_or_equal:now',
    ];

    protected $messages = [
        'kategori.required'             => 'Pilih salah satu kategori pendampingan.',
        'mode.required'                 => 'Pilih mode konsultasi yang diinginkan.',
        'topik.required'                => 'Topik konsultasi wajib diisi.',
        'topik.min'                     => 'Topik konsultasi minimal 5 karakter.',
        'detail.required'               => 'Ceritakan kendala atau hal yang ingin dikonsultasikan.',
        'detail.min'                    => 'Uraian detail minimal 10 karakter.',
        'usulan_tanggal_waktu.after_or_equal' => 'Usulan waktu tidak boleh di masa lampau.',
    ];

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedKategoriFilter(): void
    {
        $this->resetPage();
    }

    public function openModal(): void
    {
        $this->resetValidation();
        $this->reset(['topik', 'detail', 'usulan_tanggal_waktu']);
        $this->kategori = 'Akademik';
        $this->mode = 'Tatap Muka';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function simpan(): void
    {
        $this->validate();

        $student = Auth::user()?->student;
        if (!$student) {
            session()->flash('error', 'Data profil siswa tidak ditemukan.');
            return;
        }

        $keanggotaan = KelompokGuruWaliSiswa::where('student_id', $student->id)
            ->where('status_aktif', true)
            ->with('kelompok.teacher')
            ->first();

        if (!$keanggotaan || !$keanggotaan->kelompok || !$keanggotaan->kelompok->teacher) {
            session()->flash('error', 'Anda belum terdaftar dalam kelompok Guru Wali yang aktif. Silakan hubungi pihak sekolah.');
            return;
        }

        KonsultasiGuruWali::create([
            'student_id'           => $student->id,
            'teacher_id'           => $keanggotaan->kelompok->teacher_id,
            'kelompok_id'          => $keanggotaan->kelompok_id,
            'kategori_pendampingan' => $this->kategori,
            'topik_konsultasi'     => $this->topik,
            'detail_permasalahan'  => $this->detail,
            'mode_konsultasi'      => $this->mode,
            'usulan_tanggal_waktu' => $this->usulan_tanggal_waktu ? Carbon::parse($this->usulan_tanggal_waktu) : null,
            'status_pengajuan'     => 'Menunggu Konfirmasi',
        ]);

        $this->closeModal();
        session()->flash('success', 'Permohonan konsultasi berhasil dikirimkan ke Guru Wali Anda!');
    }

    public function openDetail(string $id): void
    {
        $student = Auth::user()?->student;
        if (!$student) return;

        $this->selectedKonsultasi = KonsultasiGuruWali::where('id', $id)
            ->where('student_id', $student->id)
            ->with(['guru', 'jurnal', 'pesan'])
            ->firstOrFail();

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedKonsultasi = null;
        $this->pesanBaru = '';
    }

    public function kirimPesanInline($id)
    {
        $this->validate(['pesanBaru' => 'required|string|min:2|max:2000']);
        $konsul = KonsultasiGuruWali::findOrFail($id);

        if (in_array($konsul->status_pengajuan?->value, ['Menunggu Konfirmasi', 'Dijadwalkan'])) {
            \App\Models\PesanKonsultasiGuruWali::create([
                'konsultasi_id' => $konsul->id,
                'sender_type'   => 'siswa',
                'pesan'         => $this->pesanBaru,
                'is_read'       => false,
            ]);
            $this->pesanBaru = '';
        }
    }

    public function kirimPesan(): void
    {
        $this->validate(['pesanBaru' => 'required|string|min:2|max:2000'], [
            'pesanBaru.required' => 'Pesan tidak boleh kosong.',
            'pesanBaru.min' => 'Pesan minimal 2 karakter.',
        ]);

        if (!$this->selectedKonsultasi) return;

        // Boleh membalas jika belum Selesai/Ditolak/Dikonversi
        if (in_array($this->selectedKonsultasi->status_pengajuan?->value, ['Menunggu Konfirmasi', 'Dijadwalkan'])) {
            \App\Models\PesanKonsultasiGuruWali::create([
                'konsultasi_id' => $this->selectedKonsultasi->id,
                'sender_type'   => 'siswa',
                'pesan'         => $this->pesanBaru,
                'is_read'       => false,
            ]);

            $this->pesanBaru = '';
            $this->selectedKonsultasi->load('pesan');
        }
    }

    public function openCatatanDetail(string $id): void
    {
        $student = Auth::user()?->student;
        if (!$student) return;

        $this->selectedCatatan = JurnalGuruWali::where('id', $id)
            ->where('student_id', $student->id)
            ->where('is_public_note', true)
            ->with('guru')
            ->firstOrFail();

        $this->showCatatanModal = true;
    }

    public function closeCatatanModal(): void
    {
        $this->showCatatanModal = false;
        $this->selectedCatatan = null;
    }

    public function openFeedbackModal(string $id): void
    {
        $student = Auth::user()?->student;
        if (!$student) return;

        $this->feedbackKonsultasi = KonsultasiGuruWali::where('id', $id)
            ->where('student_id', $student->id)
            ->whereIn('status_pengajuan', ['Selesai', 'Dikonversi ke Jurnal'])
            ->firstOrFail();

        $this->feedback_rating = $this->feedbackKonsultasi->student_feedback_rating;
        $this->feedback_emoji = $this->feedbackKonsultasi->student_feedback_emoji?->value;
        $this->feedback_note = $this->feedbackKonsultasi->student_feedback_note ?? '';
        $this->showFeedbackModal = true;
    }

    public function closeFeedbackModal(): void
    {
        $this->showFeedbackModal = false;
        $this->feedbackKonsultasi = null;
    }

    public function submitFeedback(): void
    {
        $this->validate([
            'feedback_rating' => 'required|integer|min:1|max:5',
            'feedback_emoji'  => 'required|in:Lega,Biasa,Masih Bingung',
            'feedback_note'   => 'nullable|string|max:1000',
        ], [
            'feedback_rating.required' => 'Pilih rating bintang (1-5) untuk sesi ini.',
            'feedback_emoji.required'  => 'Pilih perasaan Anda setelah sesi (Lega, Biasa, Masih Bingung).',
        ]);

        if ($this->feedbackKonsultasi) {
            $this->feedbackKonsultasi->update([
                'student_feedback_rating' => $this->feedback_rating,
                'student_feedback_emoji'  => \App\Enums\StudentFeedbackEmoji::tryFrom($this->feedback_emoji),
                'student_feedback_note'   => $this->feedback_note,
            ]);

            $this->closeFeedbackModal();
            session()->flash('success', 'Terima kasih atas ulasan Anda! Ini membantu kami menjadi lebih baik.');
        }
    }

    public function batalkanKonsultasi(string $id): void
    {
        $student = Auth::user()?->student;
        if (!$student) return;

        $konsultasi = KonsultasiGuruWali::where('id', $id)
            ->where('student_id', $student->id)
            ->where('status_pengajuan', 'Menunggu Konfirmasi')
            ->first();

        if ($konsultasi) {
            $konsultasi->delete();
            session()->flash('success', 'Permohonan konsultasi berhasil dibatalkan.');
            if ($this->showDetailModal) {
                $this->closeDetailModal();
            }
        } else {
            session()->flash('error', 'Konsultasi tidak dapat dibatalkan.');
        }
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $student = Auth::user()?->student;

        $keanggotaan = null;
        $guruWali = null;
        $kelompok = null;

        if ($student) {
            $keanggotaan = KelompokGuruWaliSiswa::where('student_id', $student->id)
                ->where('status_aktif', true)
                ->with(['kelompok.teacher'])
                ->first();

            $kelompok = $keanggotaan?->kelompok;
            $guruWali = $kelompok?->teacher;
        }

        // Hitung badge notifikasi tab
        $counts = [
            'semua'         => 0,
            'menunggu'      => 0,
            'dijadwalkan'   => 0,
            'selesai'       => 0,
            'ditolak'       => 0,
            'catatan_guru'  => 0,
        ];

        $konsultasiList = collect();
        $catatanPublikList = collect();

        if ($student) {
            $baseQuery = KonsultasiGuruWali::where('student_id', $student->id);

            $counts['semua'] = (clone $baseQuery)->count();
            $counts['menunggu'] = (clone $baseQuery)->where('status_pengajuan', 'Menunggu Konfirmasi')->count();
            $counts['dijadwalkan'] = (clone $baseQuery)->where('status_pengajuan', 'Dijadwalkan')->count();
            $counts['selesai'] = (clone $baseQuery)->whereIn('status_pengajuan', ['Selesai', 'Dikonversi ke Jurnal'])->count();
            $counts['ditolak'] = (clone $baseQuery)->where('status_pengajuan', 'Ditolak')->count();
            $counts['catatan_guru'] = JurnalGuruWali::where('student_id', $student->id)
                ->where('is_public_note', true)
                ->count();

            if ($this->activeTab === 'catatan_guru') {
                $catatanQuery = JurnalGuruWali::where('student_id', $student->id)
                    ->where('is_public_note', true)
                    ->with(['guru', 'badge']);

                if (!empty($this->search)) {
                    $catatanQuery->where(function ($q) {
                        $q->where('uraian_pembahasan', 'like', '%' . $this->search . '%')
                          ->orWhere('rencana_tindak_lanjut', 'like', '%' . $this->search . '%');
                    });
                }

                if (!empty($this->kategoriFilter)) {
                    $catatanQuery->where('kategori_pendampingan', $this->kategoriFilter);
                }

                $catatanPublikList = $catatanQuery->orderBy('tanggal_waktu', 'desc')->paginate(8);
            } else {
                $query = KonsultasiGuruWali::where('student_id', $student->id)->with(['guru', 'jurnal']);

                if ($this->activeTab === 'menunggu') {
                    $query->where('status_pengajuan', 'Menunggu Konfirmasi');
                } elseif ($this->activeTab === 'dijadwalkan') {
                    $query->where('status_pengajuan', 'Dijadwalkan');
                } elseif ($this->activeTab === 'selesai') {
                    $query->whereIn('status_pengajuan', ['Selesai', 'Dikonversi ke Jurnal']);
                } elseif ($this->activeTab === 'ditolak') {
                    $query->where('status_pengajuan', 'Ditolak');
                }

                if (!empty($this->search)) {
                    $query->where(function ($q) {
                        $q->where('topik_konsultasi', 'like', '%' . $this->search . '%')
                          ->orWhere('detail_permasalahan', 'like', '%' . $this->search . '%')
                          ->orWhere('tanggapan_guru', 'like', '%' . $this->search . '%');
                    });
                }

                if (!empty($this->kategoriFilter)) {
                    $query->where('kategori_pendampingan', $this->kategoriFilter);
                }

                $konsultasiList = $query->orderBy('created_at', 'desc')->paginate(8);
            }
        }

        return view('livewire.portal-siswa.konsultasi-guru-wali-list', [
            'student'           => $student,
            'keanggotaan'       => $keanggotaan,
            'kelompok'          => $kelompok,
            'guruWali'          => $guruWali,
            'counts'            => $counts,
            'konsultasiList'    => $konsultasiList,
            'catatanPublikList' => $catatanPublikList,
        ]);
    }
}
