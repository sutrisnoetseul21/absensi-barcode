<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class UnifiedLogin extends Component
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

        $key = 'login-unified:' . request()->ip() . ':' . $this->username;

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

        if (Auth::guard('web')->attempt(['email' => $email, 'password' => $this->password], $this->remember) || 
            Auth::guard('web')->attempt(['name' => $this->username, 'password' => $this->password], $this->remember)) {
            
            $user = Auth::guard('web')->user();

            // Bypass pengecekan profil untuk super admin
            if (!$user->hasRole('super_admin')) {
                // Jika role siswa, cek status enrollment aktif
                if ($user->hasRole('siswa')) {
                    if ($user->student === null) {
                        Auth::guard('web')->logout();
                        RateLimiter::hit($key);
                        throw ValidationException::withMessages([
                            'username' => 'Profil siswa tidak ditemukan.',
                        ]);
                    }
                    
                    $hasActiveEnrollment = $user->student->enrollmentAktif()->exists();
                    if (!$hasActiveEnrollment) {
                        Auth::guard('web')->logout();
                        RateLimiter::hit($key);
                        throw ValidationException::withMessages([
                            'username' => 'Akun Anda tidak berstatus aktif pada tahun ajaran ini (Lulus/Pindah) atau belum didaftarkan di kelas manapun.',
                        ]);
                    }
                }

                // Jika role wali kelas, cek status teacher
                if ($user->hasRole('wali_kelas')) {
                    if ($user->teacher === null) {
                        Auth::guard('web')->logout();
                        RateLimiter::hit($key);
                        throw ValidationException::withMessages([
                            'username' => 'Profil guru tidak ditemukan.',
                        ]);
                    }
                }
            }

            RateLimiter::clear($key);
            session()->regenerate();

            // Auto routing berdasarkan prioritas role
            if ($user->hasRole('super_admin')) {
                return redirect()->intended('/admin');
            } elseif ($user->hasRole('wali_kelas')) {
                return redirect()->intended('/portal-guru');
            } elseif ($user->hasRole('admin_portal_presensi')) {
                return redirect()->intended('/portal-presensi');
            } elseif ($user->hasRole('petugas_perpustakaan')) {
                return redirect()->intended('/portal-perpustakaan');
            } elseif ($user->hasRole('siswa')) {
                return redirect()->intended('/portal-siswa');
            } else {
                return redirect()->intended('/');
            }
        }

        RateLimiter::hit($key);

        throw ValidationException::withMessages([
            'username' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ]);
    }

    public function render()
    {
        $pengaturanSekolah = \App\Models\PengaturanSekolah::current();
        return view('livewire.auth.unified-login', compact('pengaturanSekolah'));
    }
}
