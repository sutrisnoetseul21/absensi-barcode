<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Alumni as AlumniModel;
use App\Models\AlumniJenjang;

class Alumni extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterTahun = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $nama = '';
    public string $nisn = '';
    public string $jenis_kelamin = 'L';
    public string $tahun_lulus = '';
    public bool $melanjutkan = false;
    public ?int $jenjang_id = null;
    public string $nama_sekolah = '';
    public string $no_hp = '';
    public $foto = null;
    public ?string $existingFoto = null;

    protected function rules(): array
    {
        return [
            'nama'         => 'required|string|max:255',
            'nisn'         => 'nullable|string|max:30',
            'jenis_kelamin'=> 'required|in:L,P',
            'tahun_lulus'  => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'melanjutkan'  => 'boolean',
            'jenjang_id'   => 'nullable|exists:alumni_jenjangs,id',
            'nama_sekolah' => 'nullable|string|max:255',
            'no_hp'        => 'nullable|string|max:20',
            'foto'         => 'nullable|image|max:2048',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterTahun(): void { $this->resetPage(); }

    public function openCreate(): void { $this->resetForm(); $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $item = AlumniModel::findOrFail($id);
        $this->editingId    = $item->id;
        $this->nama         = $item->nama;
        $this->nisn         = $item->nisn ?? '';
        $this->jenis_kelamin = $item->jenis_kelamin;
        $this->tahun_lulus  = (string) $item->tahun_lulus;
        $this->melanjutkan  = (bool) $item->melanjutkan;
        $this->jenjang_id   = $item->jenjang_id;
        $this->nama_sekolah = $item->nama_sekolah ?? '';
        $this->no_hp        = $item->no_hp ?? '';
        $this->existingFoto = $item->foto;
        $this->foto         = null;
        $this->showModal    = true;
    }

    public function save(): void
    {
        $this->validate();

        $fotoPath = $this->existingFoto;
        if ($this->foto) {
            $fotoPath = $this->foto->store('alumni/foto', 'public');
        }

        $data = [
            'nama'         => $this->nama,
            'nisn'         => $this->nisn ?: null,
            'jenis_kelamin'=> $this->jenis_kelamin,
            'tahun_lulus'  => (int) $this->tahun_lulus,
            'melanjutkan'  => $this->melanjutkan,
            'jenjang_id'   => $this->melanjutkan ? $this->jenjang_id : null,
            'nama_sekolah' => $this->melanjutkan ? $this->nama_sekolah : null,
            'no_hp'        => $this->no_hp ?: null,
            'foto'         => $fotoPath,
            'source'       => 'manual',
        ];

        if ($this->editingId) {
            AlumniModel::findOrFail($this->editingId)->update($data);
        } else {
            AlumniModel::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Data alumni berhasil disimpan.');
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        if ($this->deletingId) {
            AlumniModel::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Data alumni berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->nama = ''; $this->nisn = '';
        $this->jenis_kelamin = 'L'; $this->tahun_lulus = (string) date('Y');
        $this->melanjutkan = false; $this->jenjang_id = null;
        $this->nama_sekolah = ''; $this->no_hp = '';
        $this->foto = null; $this->existingFoto = null;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $alumnis = AlumniModel::with('jenjang')
            ->when($this->search, fn($q) => $q->where('nama', 'like', '%' . $this->search . '%')
                ->orWhere('nisn', 'like', '%' . $this->search . '%'))
            ->when($this->filterTahun, fn($q) => $q->where('tahun_lulus', $this->filterTahun))
            ->orderByDesc('tahun_lulus')->orderBy('nama')
            ->paginate(20);

        $tahunList = AlumniModel::selectRaw('tahun_lulus')->distinct()->orderByDesc('tahun_lulus')->pluck('tahun_lulus');
        $jenjangs = AlumniJenjang::orderBy('nama_jenjang')->get();

        return view('livewire.portal-web.alumni', compact('alumnis', 'tahunList', 'jenjangs'))->title('Data Alumni — Portal Web');
    }
}
