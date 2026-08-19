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
use App\Services\TurnstileService;

#[Layout('components.layouts.app')]
class UnifiedLogin extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;

    // Property untuk mode Turnstile
    public string $turnstile_token = '';

    // Property untuk mode Math Captcha
    public int $num1 = 0;
    public int $num2 = 0;
    public $captcha_answer = '';

    public function mount(): void
    {
        if ($this->shouldUseMathCaptcha()) {
            $this->generateCaptcha();
        }
    }

    public function shouldUseMathCaptcha(): bool
    {
        return blank(config('services.turnstile.site_key'));
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

        // 2. Validasi input & Verifikasi Keamanan (Dual-Mode)
        if ($this->shouldUseMathCaptcha()) {
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
        } else {
            $this->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if (blank($this->turnstile_token)) {
                $this->dispatch('reset-turnstile');
                throw ValidationException::withMessages([
                    'turnstile_token' => 'Verifikasi keamanan Turnstile wajib diselesaikan sebelum masuk.',
                ]);
            }

            $turnstileService = app(TurnstileService::class);
            if (!$turnstileService->verify($this->turnstile_token, $ip)) {
                $this->turnstile_token = '';
                $this->dispatch('reset-turnstile');
                throw ValidationException::withMessages([
                    'turnstile_token' => 'Verifikasi keamanan gagal atau kedaluwarsa. Silakan verifikasi kembali widget.',
                ]);
            }
        }

        // 3. Primary Rate Limiter (Individu: IP + Username, max 5 attempts)
        $key = 'login-unified:' . $ip . ':' . $this->username;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            if ($this->shouldUseMathCaptcha()) {
                $this->generateCaptcha();
            } else {
                $this->turnstile_token = '';
                $this->dispatch('reset-turnstile');
            }
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // 4. Proses Autentikasi Fleksibel (Email, Username Prefix, NISN, atau Nama)
        $input = trim($this->username);
        $schoolDomain = config('school.email_domain');

        // Cari user berdasarkan email langsung, email sekolah, nama, atau username prefix
        $targetUser = \App\Models\User::where('email', $input)
            ->orWhere('email', str_contains($input, '@') ? $input : $input . '@' . $schoolDomain)
            ->orWhere('name', $input)
            ->orWhere('email', 'like', $input . '@%')
            ->first();

        $loginSuccess = false;
        if ($targetUser && \Illuminate\Support\Facades\Hash::check($this->password, $targetUser->password)) {
            Auth::guard('web')->login($targetUser, $this->remember);
            $loginSuccess = true;
        }

        if ($loginSuccess) {
            $user = Auth::guard('web')->user();

            // Bypass pengecekan profil khusus untuk super admin
            if (!$user->isSuperAdmin()) {
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

            RateLimiter::clear($key);
            session()->regenerate();

            // Ambil daftar portal yang berhak diakses oleh user ini
            $accessiblePortals = $user->getAccessiblePortals();

            // Jika hanya memiliki 1 akses portal (misal: Siswa murni), langsung arahkan ke portal tersebut
            if (count($accessiblePortals) === 1) {
                return redirect()->intended($accessiblePortals[0]['url']);
            }

            // Jika memiliki lebih dari 1 akses portal (misal: Super Admin, Guru + Petugas/Admin),
            // arahkan ke halaman hub pemilihan portal
            if (count($accessiblePortals) > 1) {
                return redirect()->intended('/pilih-portal');
            }

            return redirect()->intended('/');
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

            if ($this->shouldUseMathCaptcha()) {
                $this->generateCaptcha();
            } else {
                $this->turnstile_token = '';
                $this->dispatch('reset-turnstile');
            }

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
        
        if ($this->shouldUseMathCaptcha()) {
            $this->generateCaptcha();
        } else {
            $this->turnstile_token = '';
            $this->dispatch('reset-turnstile');
        }

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
