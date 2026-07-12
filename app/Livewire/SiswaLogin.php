<?php

namespace App\Livewire;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SiswaLogin extends Component
{
    public $nisn = '';
    public $password = '';
    public $remember = false;

    public function login()
    {
        $this->validate([
            'nisn' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = 'login-siswa:' . request()->ip() . ':' . $this->nisn;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'nisn' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (Auth::guard('siswa')->attempt(['nisn' => $this->nisn, 'password' => $this->password], $this->remember)) {
            $student = Auth::guard('siswa')->user();
            
            // Cek apakah siswa punya enrollment aktif di tahun ajaran aktif
            $hasActiveEnrollment = $student->enrollmentAktif()->exists();

            if (!$hasActiveEnrollment) {
                Auth::guard('siswa')->logout();
                RateLimiter::hit($key);
                throw ValidationException::withMessages([
                    'nisn' => 'Akun Anda tidak berstatus aktif pada tahun ajaran ini (Lulus/Pindah) atau belum didaftarkan di kelas manapun.',
                ]);
            }

            RateLimiter::clear($key);
            session()->regenerate();

            return redirect()->intended('/siswa');
        }

        RateLimiter::hit($key);

        throw ValidationException::withMessages([
            'nisn' => 'NISN atau Password yang diberikan tidak cocok dengan catatan kami.',
        ]);
    }

    public function render()
    {
        $settings = \Illuminate\Support\Facades\Cache::remember('public_pengaturan_sekolah', 3600, function () {
            return \App\Models\PengaturanSekolah::current();
        });

        return view('livewire.siswa-login', [
            'pengaturanSekolah' => $settings
        ]);
    }
}
