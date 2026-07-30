<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use App\Models\PengaturanSekolah;

class PetugasPerpusLogin extends Component
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

        $key = 'login-petugas-perpus:' . request()->ip() . ':' . $this->username;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $email = $this->username;
        if (!str_contains($email, '@')) {
            $email = trim($email) . '@' . config('school.email_domain', 'sekolah.com');
        }

        if (Auth::guard('web')->attempt(['email' => $email, 'password' => $this->password], $this->remember) ||
            Auth::guard('web')->attempt(['name' => $this->username, 'password' => $this->password], $this->remember)) {

            RateLimiter::clear($key);
            session()->regenerate();

            return redirect()->intended('/portal-perpustakaan');
        }

        RateLimiter::hit($key);

        throw ValidationException::withMessages([
            'username' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ]);
    }

    public function render()
    {
        $settings = Cache::remember('public_pengaturan_sekolah', 3600, function () {
            return PengaturanSekolah::current();
        });

        return view('livewire.petugas-perpus-login', [
            'pengaturanSekolah' => $settings
        ])->title('Login Portal Perpustakaan');
    }
}
