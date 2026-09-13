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
            ->with(['guru', 'jurnal'])
            ->firstOrFail();

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedKonsultasi = null;
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
                    ->with('guru');

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
