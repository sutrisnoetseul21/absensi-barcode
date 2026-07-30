<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.portal')]
class SiswaProfil extends Component
{
    use WithFileUploads;

    public $student;
    public $enrollment;

    // Editable fields
    public $address;
    public $photo;
    public $photo_path;

    // Password fields
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function mount()
    {
        $user = Auth::user();
        if (!$user || !$user->student) {
            return redirect()->route('portal-siswa.login');
        }

        $this->student = $user->student;
        $this->enrollment = $this->student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first();
        
        $this->address = $this->student->address;
        $this->photo_path = $this->student->photo_path;
    }

    public function updateAddress()
    {
        $this->validate([
            'address' => 'required|string|max:500',
        ], [
            'address.required' => 'Alamat tempat tinggal tidak boleh kosong.',
            'address.max' => 'Alamat maksimal 500 karakter.',
        ]);

        $this->student->update([
            'address' => $this->address,
        ]);

        session()->flash('success_address', 'Alamat tempat tinggal berhasil diperbarui!');
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
        if ($this->student->photo_path && Storage::disk('public')->exists($this->student->photo_path)) {
            Storage::disk('public')->delete($this->student->photo_path);
        }

        // Store new photo
        $filename = 'siswa_' . $this->student->id . '_' . time() . '.' . $this->photo->getClientOriginalExtension();
        $path = $this->photo->storeAs('siswa-photos', $filename, 'public');

        $this->student->update([
            'photo_path' => $path,
        ]);

        $this->photo_path = $path;
        $this->reset('photo');

        session()->flash('success_photo', 'Foto profil berhasil diperbarui!');
    }

    public function removePhoto()
    {
        if ($this->student->photo_path && Storage::disk('public')->exists($this->student->photo_path)) {
            Storage::disk('public')->delete($this->student->photo_path);
        }

        $this->student->update([
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

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini yang Anda masukkan salah.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($this->new_password),
            'must_change_password' => false,
        ])->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('success_password', 'Kata sandi berhasil diperbarui!');
    }

    public function render()
    {
        return view('livewire.siswa-profil');
    }
}
