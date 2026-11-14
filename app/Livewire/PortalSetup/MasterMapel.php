<?php

namespace App\Livewire\PortalSetup;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MasterMapel extends Component
{
    public string $school_level = 'SMP';

    // Form tambah mapel tambahan / jurusan
    public string $nama_mapel = '';
    public string $kode_mapel = '';
    public string $kategori = 'Umum';

    // Edit inline
    public ?int $editingId = null;
    public string $edit_nama_mapel = '';
    public string $edit_kode_mapel = '';
    public string $edit_kategori = 'Umum';

    public function mount(): void
    {
        $school = PengaturanSekolah::current();
        $schoolReady = $school && filled($school->school_name) && filled($school->school_address);
        $yearReady = TahunAjaran::where('status', 'aktif')->exists();
        $classReady = Kelas::exists();

        if (!$schoolReady || !$yearReady || !$classReady) {
            session()->flash('setup_error', 'Lengkapi Identitas Sekolah, Tahun Ajaran Aktif, dan Master Kelas terlebih dahulu sebelum mengatur Master Mata Pelajaran.');
            $this->redirect(route('portal-setup.dasar'), navigate: false);
            return;
        }

        $this->school_level = $school->school_level ?: 'SMP';
        $this->kategori = $this->school_level === 'SMK' ? 'Kejuruan' : 'Umum';
    }

    public function syncStandarMapel(): void
    {
        $standar = MataPelajaran::standardByJenjang($this->school_level);
        $added = 0;

        foreach ($standar as $item) {
            $record = MataPelajaran::firstOrCreate(
                ['nama_mapel' => $item['nama_mapel']],
                [
                    'kode_mapel'  => $item['kode_mapel'],
                    'kategori'    => $item['kategori'],
                    'kkm_default' => 75.00,
                ]
            );

            if ($record->wasRecentlyCreated) {
                $added++;
            }
        }

        if ($added > 0) {
            session()->flash('setup_success', "{$added} mata pelajaran standar jenjang {$this->school_level} berhasil disinkronkan.");
        } else {
            session()->flash('setup_success', "Semua mata pelajaran standar jenjang {$this->school_level} sudah tersedia.");
        }
    }

    public function createMapel(): void
    {
        $this->validate([
            'nama_mapel' => 'required|string|max:255',
            'kode_mapel' => 'nullable|string|max:30',
            'kategori'   => 'required|in:Umum,Kejuruan,Pilihan,Muatan Lokal',
        ], [
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
        ]);

        $cleanName = trim($this->nama_mapel);
        $cleanCode = trim($this->kode_mapel) !== '' ? strtoupper(trim($this->kode_mapel)) : null;

        if (MataPelajaran::where('nama_mapel', $cleanName)->exists()) {
            $this->addError('nama_mapel', "Mata pelajaran \"{$cleanName}\" sudah terdaftar.");
            return;
        }

        MataPelajaran::create([
            'nama_mapel'  => $cleanName,
            'kode_mapel'  => $cleanCode,
            'kategori'    => $this->kategori,
            'kkm_default' => 75.00,
        ]);

        $this->nama_mapel = '';
        $this->kode_mapel = '';
        session()->flash('setup_success', "Mata pelajaran \"{$cleanName}\" berhasil ditambahkan.");
    }

    public function startEdit(int $id): void
    {
        $mapel = MataPelajaran::findOrFail($id);
        $this->editingId = $mapel->id;
        $this->edit_nama_mapel = $mapel->nama_mapel;
        $this->edit_kode_mapel = $mapel->kode_mapel ?? '';
        $this->edit_kategori = $mapel->kategori ?: 'Umum';
        $this->resetValidation(['edit_nama_mapel', 'edit_kode_mapel', 'edit_kategori']);
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->edit_nama_mapel = '';
        $this->edit_kode_mapel = '';
        $this->edit_kategori = 'Umum';
    }

    public function saveEdit(): void
    {
        if (!$this->editingId) {
            return;
        }

        $this->validate([
            'edit_nama_mapel' => 'required|string|max:255',
            'edit_kode_mapel' => 'nullable|string|max:30',
            'edit_kategori'   => 'required|in:Umum,Kejuruan,Pilihan,Muatan Lokal',
        ], [
            'edit_nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
        ]);

        $mapel = MataPelajaran::findOrFail($this->editingId);
        $cleanName = trim($this->edit_nama_mapel);
        $cleanCode = trim($this->edit_kode_mapel) !== '' ? strtoupper(trim($this->edit_kode_mapel)) : null;

        if (MataPelajaran::where('nama_mapel', $cleanName)->where('id', '!=', $mapel->id)->exists()) {
            $this->addError('edit_nama_mapel', "Mata pelajaran \"{$cleanName}\" sudah dipakai.");
            return;
        }

        $mapel->update([
            'nama_mapel' => $cleanName,
            'kode_mapel' => $cleanCode,
            'kategori'   => $this->edit_kategori,
        ]);

        $this->cancelEdit();
        session()->flash('setup_success', "Mata pelajaran berhasil diperbarui menjadi \"{$cleanName}\".");
    }

    public function deleteMapel(int $id): void
    {
        $mapel = MataPelajaran::withCount(['pengajarans', 'ujianAkademiks'])->findOrFail($id);

        if ($mapel->pengajarans_count > 0 || $mapel->ujian_akademiks_count > 0) {
            session()->flash('setup_error', "Mata pelajaran \"{$mapel->nama_mapel}\" sudah digunakan pada data Pengajaran/Ujian dan tidak boleh dihapus.");
            return;
        }

        $name = $mapel->nama_mapel;
        $mapel->delete();
        session()->flash('setup_success', "Mata pelajaran \"{$name}\" berhasil dihapus.");
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $mapels = MataPelajaran::withCount(['pengajarans', 'ujianAkademiks'])
            ->orderByRaw("CASE kategori WHEN 'Umum' THEN 1 WHEN 'Pilihan' THEN 2 WHEN 'Kejuruan' THEN 3 ELSE 4 END")
            ->orderBy('nama_mapel')
            ->get();

        $standarList = MataPelajaran::standardByJenjang($this->school_level);
        $existingNames = $mapels->pluck('nama_mapel')->all();
        $missingCount = collect($standarList)
            ->reject(fn ($item) => in_array($item['nama_mapel'], $existingNames, true))
            ->count();

        return view('livewire.portal-setup.master-mapel', [
            'mapels'        => $mapels,
            'standarList'   => $standarList,
            'existingNames' => $existingNames,
            'missingCount'  => $missingCount,
        ])->title('Master Mata Pelajaran — Portal Setup');
    }
}

