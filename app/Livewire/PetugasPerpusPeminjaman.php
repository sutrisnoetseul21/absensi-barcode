<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Peminjaman;
use Carbon\Carbon;

#[Layout('components.layouts.portal')]
class PetugasPerpusPeminjaman extends Component
{
    use WithPagination;

    public string $activeTab = 'dipinjam'; // 'dipinjam' | 'terlambat' | 'dikembalikan'
    public string $search = '';
    public int $perPage = 15;

    // Modal Unduh
    public bool $showUnduhModal = false;
    public array $filterStatusUnduh = [];
    public array $filterTipeAnggotaUnduh = [];
    public string $formatUnduh = 'pdf';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->search = '';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->search = '';
    }

    public function openUnduhModal(): void
    {
        $this->filterStatusUnduh      = [];
        $this->filterTipeAnggotaUnduh = [];
        $this->formatUnduh            = 'pdf';
        $this->showUnduhModal         = true;
    }

    public function downloadPeminjaman(): void
    {
        $routeName = $this->formatUnduh === 'excel'
            ? 'perpustakaan.peminjaman-buku.excel'
            : 'perpustakaan.peminjaman-buku.pdf';

        $params = [];
        if (!empty($this->filterStatusUnduh)) {
            $params['status'] = $this->filterStatusUnduh;
        }
        if (!empty($this->filterTipeAnggotaUnduh)) {
            $params['tipe'] = $this->filterTipeAnggotaUnduh;
        }

        $this->showUnduhModal = false;
        $this->redirect(route($routeName, $params));
    }

    public function kembalikanBuku(string $peminjamanId): void
    {
        $peminjaman = Peminjaman::find($peminjamanId);
        if (!$peminjaman || $peminjaman->status !== 'dipinjam') return;

        $eksemplar = $peminjaman->eksemplarBuku;
        $peminjaman->update([
            'status'          => 'dikembalikan',
            'tanggal_kembali' => now()->toDateString(),
        ]);

        if ($eksemplar) {
            $eksemplar->update(['status' => 'tersedia']);
        }

        session()->flash('flash_success', 'Buku "' . ($eksemplar?->buku?->judul ?? '') . '" berhasil dikembalikan!');
        $this->resetPage();
    }

    // Modal Tambah Peminjaman Manual
    public bool $showTambahModal = false;
    public string $form_peminjam_type = 'siswa'; // 'siswa' | 'guru'
    public string $form_peminjam_id = '';
    public string $form_eksemplar_id = '';
    public string $form_tanggal_pinjam = '';
    public string $form_tanggal_jatuh_tempo = '';
    public string $searchMemberModal = '';
    public string $searchEksemplarModal = '';

    public function updatedFormPeminjamType(): void
    {
        $this->form_peminjam_id = '';
        $this->searchMemberModal = '';
    }

    public function selectMember(string $id, string $type, string $name): void
    {
        $this->form_peminjam_id = $id;
        $this->form_peminjam_type = $type;
        $this->searchMemberModal = $name;
    }

    public function selectEksemplar(string $id, string $display): void
    {
        $this->form_eksemplar_id = $id;
        $this->searchEksemplarModal = $display;
    }

    public function scanMember(): void
    {
        $search = trim($this->searchMemberModal);
        if (!$search) return;

        // Try exact match on student barcode
        $studentProfile = \App\Models\StudentPresensiProfile::where('barcode_code', $search)->first();
        if ($studentProfile && $studentProfile->student) {
            $this->selectMember($studentProfile->student->id, 'siswa', $studentProfile->student->name);
            return;
        }

        // Try exact match on teacher barcode
        $teacherProfile = \App\Models\TeacherPresensiProfile::where('barcode_code', $search)->first();
        if ($teacherProfile && $teacherProfile->teacher) {
            $this->selectMember($teacherProfile->teacher->id, 'guru', $teacherProfile->teacher->name);
            return;
        }
    }

    public function scanEksemplar(): void
    {
        $search = trim($this->searchEksemplarModal);
        if (!$search) return;

        $eksemplar = \App\Models\EksemplarBuku::with('buku.kategoriBuku')
            ->where('kode_eksemplar', $search)
            ->first();

        if ($eksemplar) {
            $judul = $eksemplar->buku?->judul ?? 'Buku';
            
            if ($eksemplar->status !== 'tersedia') {
                $statusFormatted = strtoupper($eksemplar->status);
                $this->addError('form_eksemplar_id', "{$judul} sedang berstatus {$statusFormatted}, tidak bisa dipilih.");
                return;
            }

            if (!($eksemplar->buku?->kategoriBuku?->is_bisa_dipinjam ?? true)) {
                $this->addError('form_eksemplar_id', "{$judul} adalah koleksi referensi yang tidak dapat dipinjam.");
                return;
            }

            $this->selectEksemplar($eksemplar->id, $judul . ' - [Kode: ' . $eksemplar->kode_eksemplar . ']');
        } else {
            $this->addError('form_eksemplar_id', "Buku dengan barcode '{$search}' tidak ditemukan.");
        }
    }

    public function openTambahModal(): void
    {
        $lamaPinjam = \App\Models\PengaturanSekolah::current()?->lama_pinjam_buku_hari ?? 7;

        $this->form_peminjam_type = '';
        $this->form_peminjam_id = '';
        $this->form_eksemplar_id = '';
        $this->form_tanggal_pinjam = now()->toDateString();
        $this->form_tanggal_jatuh_tempo = now()->addDays($lamaPinjam)->toDateString();
        $this->searchMemberModal = '';
        $this->searchEksemplarModal = '';
        $this->showTambahModal = true;
    }

    public function updatedFormTanggalPinjam($value): void
    {
        if ($value) {
            $lamaPinjam = \App\Models\PengaturanSekolah::current()?->lama_pinjam_buku_hari ?? 7;
            $this->form_tanggal_jatuh_tempo = Carbon::parse($value)->addDays($lamaPinjam)->toDateString();
        }
    }

    public function simpanPeminjaman(): void
    {
        $this->validate([
            'form_peminjam_type' => 'required|in:siswa,guru',
            'form_peminjam_id' => 'required',
            'form_eksemplar_id' => 'required|exists:eksemplar_bukus,id',
            'form_tanggal_pinjam' => 'required|date',
            'form_tanggal_jatuh_tempo' => 'required|date|after_or_equal:form_tanggal_pinjam',
        ], [
            'form_peminjam_id.required' => 'Peminjam harus dipilih.',
            'form_eksemplar_id.required' => 'Buku/Eksemplar harus dipilih.',
            'form_tanggal_pinjam.required' => 'Tanggal pinjam harus diisi.',
            'form_tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo harus diisi.',
            'form_tanggal_jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal pinjam.',
        ]);

        $eksemplar = \App\Models\EksemplarBuku::with('buku.kategoriBuku')->find($this->form_eksemplar_id);

        if (!$eksemplar || $eksemplar->status !== 'tersedia') {
            $this->addError('form_eksemplar_id', 'Eksemplar buku ini sudah tidak tersedia.');
            return;
        }

        $isBisaDipinjam = $eksemplar->buku?->kategoriBuku?->is_bisa_dipinjam ?? true;
        if (!$isBisaDipinjam) {
            $this->addError('form_eksemplar_id', 'Koleksi ini tidak dapat dipinjam (hanya dibaca di tempat).');
            return;
        }

        $user = auth()->user();

        Peminjaman::create([
            'eksemplar_id' => $eksemplar->id,
            'peminjam_type' => $this->form_peminjam_type,
            'peminjam_id' => $this->form_peminjam_id,
            'tanggal_pinjam' => $this->form_tanggal_pinjam,
            'tanggal_jatuh_tempo' => $this->form_tanggal_jatuh_tempo,
            'status' => 'dipinjam',
            'petugas_id' => $user?->id,
        ]);

        $eksemplar->update(['status' => 'dipinjam']);

        $this->showTambahModal = false;
        session()->flash('flash_success', 'Peminjaman buku "' . ($eksemplar->buku?->judul ?? '') . '" berhasil ditambahkan!');
        $this->resetPage();
    }

    public function render()
    {
        $today = Carbon::today('Asia/Jakarta');

        $baseQuery = Peminjaman::with(['peminjam', 'eksemplarBuku.buku'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->whereHas('eksemplarBuku', function ($sub) {
                        $sub->where('kode_eksemplar', 'like', "%{$this->search}%")
                            ->orWhereHas('buku', fn ($b) => $b->where('judul', 'like', "%{$this->search}%"));
                    })->orWhereHasMorph('peminjam', ['App\Models\Siswa', 'App\Models\Guru'], function ($pm) {
                        $pm->where('name', 'like', "%{$this->search}%");
                    });
                });
            });

        // Count badges (independent of search)
        $countDipinjam    = Peminjaman::where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '>=', $today)->count();
        $countTerlambat   = Peminjaman::where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', $today)->count();
        $countDikembalikan = Peminjaman::where('status', 'dikembalikan')->count();

        $query = clone $baseQuery;
        match ($this->activeTab) {
            'terlambat'    => $query->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', $today),
            'dikembalikan' => $query->where('status', 'dikembalikan'),
            default        => $query->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '>=', $today),
        };

        $peminjamans = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        $availableMembers = collect();
        $availableEksemplars = collect();

        if ($this->showTambahModal) {
            if (strlen(trim($this->searchMemberModal)) >= 2) {
                $siswa = \App\Models\Siswa::when($this->searchMemberModal, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('name', 'like', "%{$this->searchMemberModal}%")
                            ->orWhere('nisn', 'like', "%{$this->searchMemberModal}%")
                            ->orWhere('nis', 'like', "%{$this->searchMemberModal}%");
                    });
                })->select('id', 'name', 'nisn', 'nis')
                  ->limit(20)
                  ->get()
                  ->map(function($item) {
                    $item->model_type = 'siswa';
                    return $item;
                });

                $guru = \App\Models\Guru::when($this->searchMemberModal, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('name', 'like', "%{$this->searchMemberModal}%")
                            ->orWhere('nip', 'like', "%{$this->searchMemberModal}%");
                    });
                })->select('id', 'name', 'nip')
                  ->limit(20)
                  ->get()
                  ->map(function($item) {
                    $item->model_type = 'guru';
                    return $item;
                });

                $availableMembers = $siswa->concat($guru)->sortBy('name')->take(20);
            }

            if (strlen(trim($this->searchEksemplarModal)) >= 2) {
                $availableEksemplars = \App\Models\EksemplarBuku::with('buku.kategoriBuku')
                    ->where('status', 'tersedia')
                    ->where(function ($q) {
                        $q->whereHas('buku.kategoriBuku', fn ($q2) => $q2->where('is_bisa_dipinjam', true))
                          ->orWhereDoesntHave('buku.kategoriBuku');
                    })
                    ->when($this->searchEksemplarModal, function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('kode_eksemplar', 'like', "%{$this->searchEksemplarModal}%")
                                ->orWhereHas('buku', fn ($b) => $b->where('judul', 'like', "%{$this->searchEksemplarModal}%"));
                        });
                    })
                    ->limit(20)
                    ->get();
            }
        }

        return view('livewire.petugas-perpus-peminjaman', [
            'peminjamans'         => $peminjamans,
            'today'               => $today,
            'countDipinjam'       => $countDipinjam,
            'countTerlambat'      => $countTerlambat,
            'countDikembalikan'   => $countDikembalikan,
            'availableMembers'    => $availableMembers,
            'availableEksemplars' => $availableEksemplars,
        ])->title('Data Peminjaman - Portal Perpustakaan');
    }
}
