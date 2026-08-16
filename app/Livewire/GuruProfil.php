<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\TahunAjaran;

#[Layout('components.layouts.portal')]
class GuruProfil extends Component
{
    use WithFileUploads;

    public $teacher;
    public $user;

    // Readonly Academic Info
    public $nip;
    public $nama_lengkap;
    public $kelasWaliList = [];
    public $kelasPantauList = [];
    public $mapelList = [];
    public $jabatanList = [];

    // Editable Contact Info
    public $no_hp;
    public $email;

    // Photo
    public $photo;
    public $photo_path;

    // Password
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function mount()
    {
        $this->user = Auth::user();
        if (!$this->user || !$this->user->teacher) {
            return redirect()->route('portal-guru.login');
        }

        $this->teacher = $this->user->teacher;
        $this->nama_lengkap = $this->teacher->name;
        $this->nip = $this->teacher->nip ?: '-';
        $this->no_hp = $this->teacher->no_hp ?: $this->user->no_hp;
        $this->email = $this->user->email;
        $this->photo_path = $this->teacher->photo_path;

        // Fetch academic assignment for active academic year
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if ($activeYear) {
            // Wali Kelas
            $this->kelasWaliList = $this->teacher->kelasAjarans()
                ->where('academic_year_id', $activeYear->id)
                ->with('kelas')
                ->get()
                ->map(fn($ka) => $ka->kelas->name ?? '-')
                ->toArray();

            // Kelas Pantau BK
            $this->kelasPantauList = $this->teacher->kelasPantau()
                ->where('academic_year_id', $activeYear->id)
                ->with('kelas')
                ->get()
                ->map(fn($kp) => $kp->kelas->name ?? '-')
                ->toArray();

            // Mapel yang diampu
            $this->mapelList = $this->teacher->pengajarans()
                ->whereHas('kelasAjaran', function($q) use ($activeYear) {
                    $q->where('academic_year_id', $activeYear->id);
                })
                ->with(['mataPelajaran', 'kelasAjaran.kelas'])
                ->get()
                ->map(fn($p) => ($p->mataPelajaran->nama_mapel ?? 'Mapel') . ' (' . ($p->kelasAjaran->kelas->name ?? '') . ')')
                ->toArray();
        }

        $this->jabatanList = $this->teacher->semua_jabatan;
    }

    public function updateContact()
    {
        $this->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'no_hp' => 'nullable|string|max:20',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
        ]);

        $this->user->update([
            'email' => $this->email,
            'no_hp' => $this->no_hp,
        ]);

        $this->teacher->update([
            'no_hp' => $this->no_hp,
        ]);

        session()->flash('success_contact', 'Informasi kontak berhasil diperbarui!');
    }

    public function updatePhoto()
    {
        $this->validate([
            'photo' => 'required|image|max:2048', // max 2MB
        ], [
            'photo.required' => 'Pilih foto terlebih dahulu.',
            'photo.image' => 'Berkas harus berupa gambar (jpg, jpeg, png, webp).',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        // Delete old photo if exists
        if ($this->teacher->photo_path && Storage::disk('public')->exists($this->teacher->photo_path)) {
            Storage::disk('public')->delete($this->teacher->photo_path);
        }

        // Store new photo
        $filename = 'guru_' . $this->teacher->id . '_' . time() . '.' . $this->photo->getClientOriginalExtension();
        $path = $this->photo->storeAs('guru-photos', $filename, 'public');

        $this->teacher->update([
            'photo_path' => $path,
        ]);

        $this->photo_path = $path;
        $this->reset('photo');

        session()->flash('success_photo', 'Foto profil berhasil diperbarui!');
    }

    public function removePhoto()
    {
        if ($this->teacher->photo_path && Storage::disk('public')->exists($this->teacher->photo_path)) {
            Storage::disk('public')->delete($this->teacher->photo_path);
        }

        $this->teacher->update([
            'photo_path' => null,
        ]);

        $this->photo_path = null;
        $this->reset('photo');

        session()->flash('success_photo', 'Foto profil berhasil dihapus!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($this->current_password, $this->user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini yang Anda masukkan salah.',
            ]);
        }

        $this->user->forceFill([
            'password' => Hash::make($this->new_password),
            'must_change_password' => false,
        ])->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('success_password', 'Kata sandi berhasil diperbarui!');
    }

    public function render()
    {
        return view('livewire.guru-profil');
    }
}
