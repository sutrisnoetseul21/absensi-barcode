<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public ?string $errorMessage = null;

    #[Layout('components.layouts.portal-web-guest')]
    public function render()
    {
        return view('livewire.portal-web.login')->title('Login Portal Web');
    }

    public function login(): void
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required|min:3',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (!Auth::guard('web')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->errorMessage = 'Email atau password salah.';
            return;
        }

        $user = Auth::user();

        if (!$user->hasRole(['admin_portal_web', 'super_admin'])) {
            Auth::guard('web')->logout();
            $this->errorMessage = 'Akun Anda tidak memiliki akses ke Portal Web Sekolah.';
            return;
        }

        $this->redirect(route('portal-web.dashboard'), navigate: true);
    }
}
