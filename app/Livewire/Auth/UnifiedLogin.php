<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class UnifiedLogin extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;
    public int $num1 = 0;
    public int $num2 = 0;
    public $captcha_answer = '';

    public function mount(): void
    {
        $this->generateCaptcha();
    }

    public function generateCaptcha(): void
    {
        $this->num1 = rand(1, 9);
        $this->num2 = rand(1, 9);
        $this->captcha_answer = '';
        session(['unified_login_captcha' => $this->num1 + $this->num2]);
    }

    public function login()
    {
        $ip = request()->ip();

        // 1. PRE-CHECK: Cek Secondary Rate Limiter (Spray Protection per-IP)
        $this->checkSprayLimiter($ip);

        // 2. Validasi input & Captcha
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'captcha_answer' => 'required|numeric',
        ], [
            'captcha_answer.required' => 'Pertanyaan keamanan wajib diisi.',
            'captcha_answer.numeric' => 'Jawaban harus berupa angka.',
        ]);

        $expected = session('unified_login_captcha');
        if ($expected === null || (int) $this->captcha_answer !== (int) $expected) {
            $this->generateCaptcha();
            throw ValidationException::withMessages([
                'captcha_answer' => 'Jawaban hitungan keamanan salah. Silakan coba lagi.',
            ]);
        }

        // 3. Primary Rate Limiter (Individu: IP + Username, max 5 attempts)
        $key = 'login-unified:' . $ip . ':' . $this->username;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->generateCaptcha();
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $email = $this->username;
        if (!str_contains($email, '@')) {
            $email = trim($email) . '@' . config('school.email_domain');
        }

        // 4. Proses Autentikasi
        if (Auth::guard('web')->attempt(['email' => $email, 'password' => $this->password], $this->remember) || 
            Auth::guard('web')->attempt(['name' => $this->username, 'password' => $this->password], $this->remember)) {
            
            $user = Auth::guard('web')->user();

            // Bypass pengecekan profil untuk super admin
            if (!$user->hasRole('super_admin')) {
                // Jika role siswa, cek status enrollment aktif
                if ($user->hasRole('siswa')) {
                    if ($user->student === null) {
                        Auth::guard('web')->logout();
                        $this->recordFailedAttempt($ip, $key);
                        throw ValidationException::withMessages([
                            'username' => 'Profil siswa tidak ditemukan.',
                        ]);
                    }
                    
                    $hasActiveEnrollment = $user->student->enrollmentAktif()->exists();
                    if (!$hasActiveEnrollment) {
                        Auth::guard('web')->logout();
                        $this->recordFailedAttempt($ip, $key);
                        throw ValidationException::withMessages([
                            'username' => 'Akun Anda tidak berstatus aktif pada tahun ajaran ini (Lulus/Pindah) atau belum didaftarkan di kelas manapun.',
                        ]);
                    }
                }

                // Jika role wali kelas, cek status teacher
                if ($user->hasRole('wali_kelas')) {
                    if ($user->teacher === null) {
                        Auth::guard('web')->logout();
                        $this->recordFailedAttempt($ip, $key);
                        throw ValidationException::withMessages([
                            'username' => 'Profil guru tidak ditemukan.',
                        ]);
                    }
                }
            }

            RateLimiter::clear($key);
            session()->regenerate();

            // Auto routing berdasarkan prioritas role
            if ($user->hasRole('super_admin') || $user->isSuperAdmin() || $user->roles->contains(fn($r) => str_starts_with($r->name, 'admin_'))) {
                return redirect()->intended('/admin');
            } elseif ($user->hasRole('wali_kelas') || $user->hasRole('guru')) {
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

        // Catat kegagalan login kredensial salah
        $this->recordFailedAttempt($ip, $key);

        throw ValidationException::withMessages([
            'username' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ]);
    }

    /**
     * Pre-check: Menolak login jika IP telah mencapai threshold 30 username unik gagal dalam 10 menit.
     * Dilengkapi log throttling agar tidak membanjiri log file.
     */
    public function checkSprayLimiter(string $ip): void
    {
        $sprayKey = 'login_failed_usernames:' . $ip;
        $failedUsernames = Cache::get($sprayKey, []);

        if (is_array($failedUsernames) && count($failedUsernames) >= 30) {
            $logThrottleKey = 'login_spray_logged:' . $ip;

            // Log Throttling: Hanya catat log 1 kali per window 10 menit per IP
            if (!Cache::has($logThrottleKey)) {
                Log::warning("Potential password spraying detected from IP: {$ip}. Threshold (30 unique usernames) exceeded.", [
                    'ip' => $ip,
                    'unique_failed_count' => count($failedUsernames),
                    'attempted_username' => $this->username,
                ]);
                Cache::put($logThrottleKey, true, now()->addMinutes(10));
            }

            $this->generateCaptcha();
            throw ValidationException::withMessages([
                'username' => 'Aktivitas login mencurigakan terdeteksi dari jaringan Anda (terlalu banyak akun gagal). Akses login dari IP ini ditangguhkan sementara.',
            ]);
        }
    }

    /**
     * Mencatat username gagal ke cache set dengan Atomic Lock (Fail-Open).
     */
    public function recordFailedAttempt(string $ip, string $key): void
    {
        RateLimiter::hit($key);
        $this->generateCaptcha();

        $uniqueFailedCount = 0;

        try {
            // Atomic Lock 5 detik, block max 3 detik
            $uniqueFailedCount = (int) Cache::lock('login_spray_lock:' . $ip, 5)->block(3, function () use ($ip) {
                $sprayKey = 'login_failed_usernames:' . $ip;
                $usernames = Cache::get($sprayKey, []);

                if (!in_array($this->username, $usernames, true)) {
                    $usernames[] = $this->username;
                    Cache::put($sprayKey, $usernames, now()->addMinutes(10));
                }

                return count($usernames);
            });
        } catch (LockTimeoutException $e) {
            // Fail-open: Catat log, jangan lempar error 500
            Log::warning("Spray lock timeout on IP: {$ip}. Request proceeded in fail-open mode.", [
                'ip' => $ip,
                'username' => $this->username,
            ]);
        }

        // Di luar closure lock: Jika mencapai threshold >= 30 pada kegagalan ini, catat log audit
        if ($uniqueFailedCount >= 30) {
            $logThrottleKey = 'login_spray_logged:' . $ip;
            if (!Cache::has($logThrottleKey)) {
                Log::warning("Potential password spraying detected from IP: {$ip}. Threshold (30 unique usernames) reached.", [
                    'ip' => $ip,
                    'unique_failed_count' => $uniqueFailedCount,
                    'last_attempted_username' => $this->username,
                ]);
                Cache::put($logThrottleKey, true, now()->addMinutes(10));
            }
        }
    }

    public function render()
    {
        $pengaturanSekolah = \App\Models\PengaturanSekolah::current();
        return view('livewire.auth.unified-login', compact('pengaturanSekolah'));
    }
}
