<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use App\Services\TurnstileService;

class CustomLogin extends BaseLogin
{
    protected static string $layout = 'filament-panels::components.layout.base';
    protected string $view = 'filament.pages.auth.custom-login';

    // Property untuk mode Turnstile
    public string $turnstile_token = '';

    // Property untuk mode Fallback Math Captcha
    public int $captchaNum1 = 0;
    public int $captchaNum2 = 0;
    public string $captcha_answer = '';

    public function mount(): void
    {
        parent::mount();
        // Hanya generate math captcha jika Turnstile TIDAK dikonfigurasi
        if ($this->shouldUseMathCaptcha()) {
            $this->generateMathCaptcha();
        }
    }

    public function shouldUseMathCaptcha(): bool
    {
        return blank(config('services.turnstile.site_key'));
    }

    public function generateMathCaptcha(): void
    {
        $this->captchaNum1 = rand(1, 9);
        $this->captchaNum2 = rand(1, 9);
        $this->captcha_answer = '';
        session(['admin_login_captcha' => $this->captchaNum1 + $this->captchaNum2]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            if ($this->shouldUseMathCaptcha()) {
                // JALUR 1: Fallback Math Captcha (Server-Side Session)
                if (blank($this->captcha_answer)) {
                    throw ValidationException::withMessages([
                        'captcha_answer' => 'Pertanyaan keamanan wajib diisi.',
                    ]);
                }

                $expected = session('admin_login_captcha');
                if ($expected === null || (int) $this->captcha_answer !== (int) $expected) {
                    $this->generateMathCaptcha();
                    throw ValidationException::withMessages([
                        'captcha_answer' => 'Jawaban hitungan keamanan salah. Silakan coba lagi.',
                    ]);
                }
            } else {
                // JALUR 2: Cloudflare Turnstile (Fail-Closed jika config terisi)
                if (blank($this->turnstile_token)) {
                    $this->dispatch('reset-turnstile');
                    throw ValidationException::withMessages([
                        'turnstile_token' => 'Verifikasi keamanan Turnstile wajib diselesaikan sebelum masuk.',
                    ]);
                }

                $turnstileService = app(TurnstileService::class);
                if (!$turnstileService->verify($this->turnstile_token, request()->ip())) {
                    $this->turnstile_token = '';
                    $this->dispatch('reset-turnstile');
                    throw ValidationException::withMessages([
                        'turnstile_token' => 'Verifikasi keamanan gagal atau kedaluwarsa. Silakan verifikasi kembali widget.',
                    ]);
                }
            }

            // 3. Autentikasi bawaan Filament
            $response = parent::authenticate();

            if ($this->shouldUseMathCaptcha()) {
                $this->generateMathCaptcha();
            }

            return $response;

        } catch (ValidationException $e) {
            if ($this->shouldUseMathCaptcha()) {
                $this->generateMathCaptcha();
            } else {
                $this->turnstile_token = '';
                $this->dispatch('reset-turnstile');
            }
            throw $e;
        }
    }

    public function getHeading(): string | Htmlable
    {
        return '';
    }
}
