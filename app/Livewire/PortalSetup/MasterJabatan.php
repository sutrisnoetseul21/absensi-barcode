<?php

namespace App\Livewire\PortalSetup;

use App\Models\Jabatan;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MasterJabatan extends Component
{
    public string $school_level = 'SMP';
    public string $nama_jabatan = '';

    // Edit inline
    public ?int $editingId = null;
    public string $edit_nama_jabatan = '';

    // Gembok / Mode Terkunci + Verifikasi Password Super Admin (Opsi B)
    public bool $isUnlocked = false;
    public bool $showUnlockModal = false;
    public string $unlockPassword = '';

    public function mount(): void
    {
        $school = PengaturanSekolah::current();
        $schoolReady = $school && filled($school->school_name) && filled($school->school_address);
        $yearReady = TahunAjaran::where('status', 'aktif')->exists();
        $classReady = Kelas::exists();

        if (!$schoolReady || !$yearReady || !$classReady) {
            session()->flash('setup_error', 'Lengkapi Identitas Sekolah, Tahun Ajaran Aktif, dan Master Kelas terlebih dahulu sebelum mengatur Master Jabatan.');
            $this->redirect(route('portal-setup.dasar'), navigate: false);
            return;
        }

        $this->school_level = $school->school_level ?: 'SMP';
    }

    // ==================== GEMBOK SUPER ADMIN (OPSI B) ====================

    public function openUnlockModal(): void
    {
        $this->resetValidation('unlockPassword');
        $this->unlockPassword = '';
        $this->showUnlockModal = true;
    }

    public function closeUnlockModal(): void
    {
        $this->showUnlockModal = false;
        $this->unlockPassword = '';
        $this->resetValidation('unlockPassword');
    }

    public function verifyUnlockPassword(): void
    {
        $this->validate([
            'unlockPassword' => 'required|string',
        ], [
            'unlockPassword.required' => 'Password Super Admin wajib diisi.',
        ]);

        $superAdmins = User::where('is_super_admin', true)
            ->orWhereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
            ->get();

        $verified = $superAdmins->contains(fn (User $admin) => Hash::check($this->unlockPassword, $admin->password));

        if (!$verified) {
            $this->addError('unlockPassword', 'Password Super Admin tidak valid.');
            return;
        }

        $this->isUnlocked = true;
        $this->closeUnlockModal();
        session()->flash('setup_success', 'Gembok jabatan dibuka. Silakan lakukan perubahan lalu kunci kembali.');
    }

    public function lockEdit(): void
    {
        $this->isUnlocked = false;
        $this->cancelEdit();
        session()->flash('setup_success', 'Gembok jabatan dikunci kembali.');
    }

    protected function isWajibReady(): bool
    {
        $wajib = Jabatan::wajibByJenjang($this->school_level);
        $existingCount = Jabatan::whereIn('nama_jabatan', $wajib)->count();

        return $existingCount === count($wajib);
    }

    protected function ensureUnlockedIfLocked(): bool
    {
        if ($this->isWajibReady() && !$this->isUnlocked) {
            $this->openUnlockModal();
            session()->flash('setup_error', 'Master Jabatan sedang digembok. Masukkan password Super Admin untuk melakukan perubahan.');
            return false;
        }

        return true;
    }

    // ==================== AKSI JABATAN ====================

    public function syncStandarJabatan(): void
    {
        if (!$this->ensureUnlockedIfLocked()) {
            return;
        }

        $standar = Jabatan::standarByJenjang($this->school_level);
        $added = 0;

        foreach ($standar as $nama) {
            $record = Jabatan::firstOrCreate(['nama_jabatan' => $nama]);
            if ($record->wasRecentlyCreated) {
                $added++;
            }
        }

        if ($added > 0) {
            session()->flash('setup_success', "{$added} jabatan standar jenjang {$this->school_level} berhasil disinkronkan.");
        } else {
            session()->flash('setup_success', "Semua jabatan standar jenjang {$this->school_level} sudah tersedia.");
        }
    }

    public function createJabatan(): void
    {
        if (!$this->ensureUnlockedIfLocked()) {
            return;
        }

        $this->validate([
            'nama_jabatan' => 'required|string|max:100',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
        ]);

        $clean = trim($this->nama_jabatan);
        if (Jabatan::where('nama_jabatan', $clean)->exists()) {
            $this->addError('nama_jabatan', "Jabatan \"{$clean}\" sudah terdaftar.");
            return;
        }

        Jabatan::create(['nama_jabatan' => $clean]);
        $this->nama_jabatan = '';
        session()->flash('setup_success', "Jabatan \"{$clean}\" berhasil ditambahkan.");
    }

    public function startEdit(int $id): void
    {
        if (!$this->ensureUnlockedIfLocked()) {
            return;
        }

        $jabatan = Jabatan::findOrFail($id);
        if (in_array($jabatan->nama_jabatan, Jabatan::SYSTEM_CORE_NAMES, true)) {
            session()->flash('setup_error', "Jabatan inti sistem \"{$jabatan->nama_jabatan}\" tidak boleh diubah namanya agar notifikasi WA & hak akses tetap normal.");
            return;
        }

        $this->editingId = $jabatan->id;
        $this->edit_nama_jabatan = $jabatan->nama_jabatan;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->edit_nama_jabatan = '';
        $this->resetValidation('edit_nama_jabatan');
    }

    public function saveEdit(): void
    {
        if (!$this->editingId || !$this->ensureUnlockedIfLocked()) {
            return;
        }

        $this->validate([
            'edit_nama_jabatan' => 'required|string|max:100',
        ], [
            'edit_nama_jabatan.required' => 'Nama jabatan wajib diisi.',
        ]);

        $clean = trim($this->edit_nama_jabatan);
        $duplicate = Jabatan::where('nama_jabatan', $clean)
            ->where('id', '!=', $this->editingId)
            ->exists();

        if ($duplicate) {
            $this->addError('edit_nama_jabatan', "Nama jabatan \"{$clean}\" sudah digunakan.");
            return;
        }

        $jabatan = Jabatan::findOrFail($this->editingId);
        if (in_array($jabatan->nama_jabatan, Jabatan::SYSTEM_CORE_NAMES, true)) {
            $this->cancelEdit();
            return;
        }

        $jabatan->update(['nama_jabatan' => $clean]);
        $this->cancelEdit();
        session()->flash('setup_success', "Jabatan berhasil diperbarui menjadi \"{$clean}\".");
    }

    public function deleteJabatan(int $id): void
    {
        if (!$this->ensureUnlockedIfLocked()) {
            return;
        }

        $jabatan = Jabatan::withCount('gurus')->findOrFail($id);

        if (in_array($jabatan->nama_jabatan, Jabatan::SYSTEM_CORE_NAMES, true)) {
            session()->flash('setup_error', "Jabatan inti sistem \"{$jabatan->nama_jabatan}\" tidak boleh dihapus.");
            return;
        }

        if ($jabatan->gurus_count > 0) {
            session()->flash('setup_error', "Jabatan \"{$jabatan->nama_jabatan}\" sedang dipakai oleh {$jabatan->gurus_count} guru.");
            return;
        }

        $name = $jabatan->nama_jabatan;
        $jabatan->delete();
        session()->flash('setup_success', "Jabatan \"{$name}\" berhasil dihapus.");
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $jabatans = Jabatan::withCount('gurus')
            ->orderBy('id')
            ->get();

        $wajibNames = Jabatan::wajibByJenjang($this->school_level);
        $standarNames = Jabatan::standarByJenjang($this->school_level);
        $existingNames = $jabatans->pluck('nama_jabatan')->toArray();

        $missingWajib = array_values(array_diff($wajibNames, $existingNames));
        $missingStandar = array_values(array_diff($standarNames, $existingNames));
        $isJabatanReady = empty($missingWajib);

        return view('livewire.portal-setup.master-jabatan', [
            'jabatans'       => $jabatans,
            'wajibNames'     => $wajibNames,
            'standarNames'   => $standarNames,
            'missingWajib'   => $missingWajib,
            'missingStandar' => $missingStandar,
            'isJabatanReady' => $isJabatanReady,
            'isLocked'       => $isJabatanReady && !$this->isUnlocked,
        ])->title('Master Jabatan — Portal Setup');
    }
}

