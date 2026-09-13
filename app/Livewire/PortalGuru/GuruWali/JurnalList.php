<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class JurnalList extends Component
{
    use WithPagination;

    public $teacher;
    public $kelompok;

    // Filters
    public $search = '';
    public $studentId = '';
    public $kategori = '';
    public $statusSesi = '';
    public $bulan = ''; // 'Y-m'

    // Modal detail
    public $selectedJurnal = null;
    public $showDetailModal = false;

    // Modal delete
    public $jurnalToDelete = null;
    public $showDeleteModal = false;

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

        // Default bulan ini
        $this->bulan = Carbon::now()->format('Y-m');

        if (request()->has('student_id')) {
            $this->studentId = request('student_id');
            $this->bulan = ''; // Tampilkan riwayat penuh siswa
        }

        if (request()->has('search')) {
            $this->search = request('search');
            $this->bulan = '';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStudentId()
    {
        $this->resetPage();
    }

    public function updatingKategori()
    {
        $this->resetPage();
    }

    public function updatingStatusSesi()
    {
        $this->resetPage();
    }

    public function updatingBulan()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'studentId', 'kategori', 'statusSesi', 'bulan']);
        $this->resetPage();
    }

    public function openDetail($id)
    {
        $this->selectedJurnal = JurnalGuruWali::with(['siswa.enrollmentAktif.kelas', 'teacher'])
            ->where('teacher_id', $this->teacher->id)
            ->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function closeDetail()
    {
        $this->showDetailModal = false;
        $this->selectedJurnal = null;
    }

    public function confirmDelete($id)
    {
        $this->jurnalToDelete = JurnalGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->jurnalToDelete = null;
    }

    public function deleteJurnal()
    {
        if ($this->jurnalToDelete) {
            $this->jurnalToDelete->delete();
            $this->cancelDelete();
            session()->flash('success', 'Catatan jurnal pendampingan berhasil dihapus.');
        }
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $query = JurnalGuruWali::with(['siswa.enrollmentAktif.kelas'])
            ->where('teacher_id', $this->teacher->id);

        if (trim($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('uraian_pembahasan', 'like', $term)
                    ->orWhere('rencana_tindak_lanjut', 'like', $term)
                    ->orWhereHas('siswa', function ($sq) use ($term) {
                        $sq->where('name', 'like', $term)
                            ->orWhere('nisn', 'like', $term);
                    });
            });
        }

        if ($this->studentId) {
            $query->where('student_id', $this->studentId);
        }

        if ($this->kategori) {
            $query->where('kategori_pendampingan', $this->kategori);
        }

        if ($this->statusSesi) {
            $query->where('status_sesi', $this->statusSesi);
        }

        if ($this->bulan) {
            try {
                $start = Carbon::createFromFormat('Y-m', $this->bulan)->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $this->bulan)->endOfMonth();
                $query->whereBetween('tanggal_waktu', [$start, $end]);
            } catch (\Exception $e) {
                // ignore format error
            }
        }

        $jurnals = $query->latest('tanggal_waktu')->paginate(10);

        // Statistik ringkas
        $totalAll = JurnalGuruWali::where('teacher_id', $this->teacher->id)->count();
        $totalBulanIni = JurnalGuruWali::where('teacher_id', $this->teacher->id)
            ->whereBetween('tanggal_waktu', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();
        $totalPemantauan = JurnalGuruWali::where('teacher_id', $this->teacher->id)
            ->whereIn('status_sesi', ['Dalam Pemantauan', 'Bimbingan Lanjutan'])
            ->count();

        // Daftar anggota untuk filter dropdown
        $anggotaList = $this->kelompok->anggotaAktif()->with('siswa')->get();

        return view('livewire.portal-guru.guru-wali.jurnal-list', [
            'jurnals'         => $jurnals,
            'totalAll'        => $totalAll,
            'totalBulanIni'   => $totalBulanIni,
            'totalPemantauan' => $totalPemantauan,
            'anggotaList'     => $anggotaList,
        ]);
    }
}
