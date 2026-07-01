<?php

namespace App\Livewire;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ForceChangePassword extends Component
{
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';
    public $guard = '';

    public function mount()
    {
        if (request()->is('wali-kelas/*')) {
            $this->guard = 'wali_kelas';
        } elseif (request()->is('siswa/*')) {
            $this->guard = 'siswa';
        }
    }

    public function changePassword()
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::guard($this->guard)->user();

        if (!$user) {
            return redirect('/');
        }

        if (!Hash::check($this->current_password, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $user->forceFill([
            'password' => $this->new_password,
            'must_change_password' => false,
        ])->save();

        if ($this->guard === 'wali_kelas') {
            return redirect()->route('wali-kelas.dashboard');
        }

        if ($this->guard === 'siswa') {
            return redirect()->route('siswa.dashboard');
        }

        return redirect('/');
    }

    public function render()
    {
        return view('livewire.force-change-password');
    }
}
