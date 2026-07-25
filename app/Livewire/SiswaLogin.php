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

        $email = $this->nisn;
        if (!str_contains($email, '@')) {
            $email = trim($email) . '@' . config('school.email_domain');
        }

        if (Auth::guard('web')->attempt(['email' => $email, 'password' => $this->password], $this->remember)) {
            $user = Auth::guard('web')->user();

            if (!$user->hasRole('siswa') || $user->student === null) {
                Auth::guard('web')->logout();
                RateLimiter::hit($key);
                throw ValidationException::withMessages([
                    'nisn' => 'Akun ini bukan akun siswa.',
                ]);
            }

            $student = $user->student;
            
            // Cek apakah siswa punya enrollment aktif di tahun ajaran aktif
            $hasActiveEnrollment = $student->enrollmentAktif()->exists();

            if (!$hasActiveEnrollment) {
                Auth::guard('web')->logout();
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
