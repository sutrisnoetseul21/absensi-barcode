<?php

namespace App\Livewire;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class WaliKelasLogin extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;

    public function login()
    {
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = 'login-wali-kelas:' . request()->ip() . ':' . $this->username;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $email = $this->username;
        if (!str_contains($email, '@')) {
            $email = trim($email) . '@' . config('school.email_domain');
        }

        if (Auth::guard('web')->attempt(['email' => $email, 'password' => $this->password], $this->remember)) {
            $user = Auth::guard('web')->user();

            if (!$user->hasRole('wali_kelas') || $user->teacher === null) {
                Auth::guard('web')->logout();
                RateLimiter::hit($key);
                throw ValidationException::withMessages([
                    'username' => 'Akun ini bukan akun wali kelas.',
                ]);
            }

            RateLimiter::clear($key);
            session()->regenerate();

            return redirect()->intended('/portal-guru');
        }

        RateLimiter::hit($key);

        throw ValidationException::withMessages([
            'username' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ]);
    }

    public function render()
    {
        $settings = \Illuminate\Support\Facades\Cache::remember('public_pengaturan_sekolah', 3600, function () {
            return \App\Models\PengaturanSekolah::current();
        });

        return view('livewire.wali-kelas-login', [
            'pengaturanSekolah' => $settings
        ]);
    }
}
