<div class="min-h-screen flex flex-col lg:flex-row bg-slate-900 lg:bg-white dark:lg:bg-slate-900 font-jakarta w-full relative overflow-hidden">
    <!-- Global Background for Mobile -->
    <div class="absolute inset-0 z-0 lg:hidden">
        @php
            $pengaturanSekolah = \App\Models\PengaturanSekolah::current();
            $panelId = filament()->getCurrentPanel()->getId();
            
            $loginTitle = 'Portal Admin';
            $loginSubtitle = 'Pusat kendali sistem presensi. Kelola data master, konfigurasi aplikasi, dan pantau seluruh aktivitas sekolah.';
            $formTitle = 'Login Admin';
            
            if ($panelId === 'admin-akademik') {
                $loginTitle = 'Portal Master & Akademik';
                $loginSubtitle = 'Kelola data induk sekolah, kesiswaan, pembagian kelas, dan mutasi.';
                $formTitle = 'Login Master & Akademik';
            } elseif ($panelId === 'admin-presensi') {
                $loginTitle = 'Portal Presensi';
                $loginSubtitle = 'Pusat rekapitulasi kehadiran, izin, sakit, dan cetak laporan harian.';
                $formTitle = 'Login Presensi';
            } elseif ($panelId === 'admin-perpustakaan') {
                $loginTitle = 'Portal Perpustakaan';
                $loginSubtitle = 'Manajemen koleksi buku, anggota, dan sirkulasi peminjaman.';
                $formTitle = 'Login Perpustakaan';
            } else {
                $loginTitle = 'Portal Super Admin';
                $loginSubtitle = 'Pusat kendali sistem presensi. Kelola konfigurasi sistem dan hak akses.';
                $formTitle = 'Login Super Admin';
            }
        @endphp
        @if($pengaturanSekolah && $pengaturanSekolah->login_background_path)
            <img src="{{ asset('storage/' . $pengaturanSekolah->login_background_path) }}" class="w-full h-full object-cover object-center opacity-60">
        @else
            <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover object-center opacity-60">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/50 mix-blend-multiply"></div>
        <!-- Decorative Blobs -->
        <div class="absolute top-1/4 -left-20 w-72 h-72 bg-amber-500 rounded-full mix-blend-screen filter blur-[80px] opacity-30 animate-blob"></div>
        <div class="absolute bottom-1/4 -right-20 w-72 h-72 bg-orange-500 rounded-full mix-blend-screen filter blur-[80px] opacity-30 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Left Column: Visual -->
    <div class="relative hidden lg:flex lg:w-1/2 bg-slate-900 overflow-hidden items-center justify-center p-12 min-h-screen z-10">
        <div class="absolute inset-0 z-0">
            @if($pengaturanSekolah && $pengaturanSekolah->login_background_path)
                <img src="{{ asset('storage/' . $pengaturanSekolah->login_background_path) }}" class="w-full h-full object-cover object-center opacity-40">
            @else
                <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover object-center opacity-40">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-indigo-950 via-slate-900/60 to-slate-900/40 mix-blend-multiply"></div>
            <!-- Decorative Blobs -->
            <div class="absolute top-1/4 -left-20 w-72 h-72 bg-amber-500 rounded-full mix-blend-screen filter blur-[80px] opacity-40 animate-blob"></div>
            <div class="absolute bottom-1/4 -right-20 w-72 h-72 bg-orange-500 rounded-full mix-blend-screen filter blur-[80px] opacity-40 animate-blob animation-delay-2000"></div>
        </div>
        
        <div class="relative z-10 w-full max-w-lg text-left">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-white/10 rounded-xl backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-lg">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-wide">Sistem Presensi</h2>
                    <p class="text-amber-200 text-sm">Administrator Panel</p>
                </div>
            </div>
            <h1 class="text-4xl lg:text-5xl font-extrabold text-white mb-6 leading-tight">
                Selamat Datang di <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-300 drop-shadow-sm">{{ $loginTitle }}</span>
            </h1>
            <p class="text-lg text-slate-300 leading-relaxed mb-8 max-w-md">
                {{ $loginSubtitle }}
            </p>
        </div>
    </div>

    <!-- Right Column: Form -->
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24 bg-transparent lg:bg-white dark:lg:bg-slate-900 relative min-h-screen transition-colors duration-300 z-10">
        <a href="/" class="absolute top-6 right-6 lg:top-8 lg:right-8 flex items-center gap-2 text-sm font-semibold text-slate-300 lg:text-slate-500 hover:text-amber-400 dark:text-slate-400 dark:hover:text-amber-400 transition-colors drop-shadow-sm lg:drop-shadow-none z-20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>

        <!-- Form Wrapper with Card on Mobile -->
        <div class="mx-auto w-full max-w-sm bg-white/95 dark:bg-slate-900/95 lg:bg-transparent backdrop-blur-xl lg:backdrop-blur-none border border-white/20 lg:border-none rounded-3xl lg:rounded-none p-6 sm:p-8 lg:p-0 shadow-2xl lg:shadow-none">
            
            <!-- Mobile Header -->
            <div class="lg:hidden text-center mb-8">
                 <div class="mx-auto w-14 h-14 bg-amber-50 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center mb-4 border border-amber-100 dark:border-amber-500/30">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7"></path></svg>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $loginTitle }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sistem Presensi Berbasis Barcode</p>
            </div>

            <div>
                <h2 class="mt-6 text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight hidden lg:block">{{ $formTitle }}</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 hidden lg:block">Silakan masuk ke akun administrator Anda.</p>
            </div>

            <div class="mt-8 lg:mt-10">
                <form wire:submit="authenticate">
                    {{ $this->form }}

                    @if(empty(config('services.turnstile.site_key')))
                        <!-- MODE FALLBACK OTOMATIS: Math Captcha (Saat TURNSTILE_SITE_KEY kosong) -->
                        <div class="mt-4 mb-2 bg-amber-50/60 dark:bg-slate-800/80 p-3.5 rounded-2xl border border-amber-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="admin_captcha_answer" class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Hitungan Keamanan Anti-Bot
                                </label>
                                <button type="button" wire:click="generateMathCaptcha" class="text-[11px] font-medium text-amber-600 dark:text-amber-400 hover:text-amber-800 transition-colors inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Acak Ulang
                                </button>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="px-3.5 py-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 font-mono font-extrabold text-base text-amber-700 dark:text-amber-400 select-none shadow-xs whitespace-nowrap">
                                    {{ $captchaNum1 }} + {{ $captchaNum2 }} =
                                </div>
                                <input wire:model="captcha_answer" id="admin_captcha_answer" type="number" required class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl leading-5 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 text-sm shadow-xs" placeholder="Hasil ?">
                            </div>
                            @error('captcha_answer')
                                <p class="mt-1.5 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <!-- MODE TURNSTILE: Cloudflare Turnstile (Saat TURNSTILE_SITE_KEY terisi) -->
                        <div wire:ignore class="mt-4 mb-2 flex flex-col items-center">
                            <div id="turnstile-admin-container" class="flex justify-center"></div>
                            
                            <!-- Fallback UI jika script diblokir / gagal load -->
                            <div id="turnstile-error-message" class="hidden mt-2 p-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-xs text-rose-600 dark:text-rose-300 text-center w-full">
                                Gagal memuat verifikasi keamanan Cloudflare. Periksa koneksi internet Anda atau muat ulang halaman.
                            </div>
                        </div>

                        @error('turnstile_token')
                            <p class="mt-1 text-xs text-rose-500 font-semibold text-center">{{ $message }}</p>
                        @enderror

                        <script>
                            let turnstileAdminWidgetId = null;

                            function renderAdminTurnstile() {
                                const container = document.getElementById('turnstile-admin-container');
                                const sitekey = "{{ config('services.turnstile.site_key') }}";

                                if (!container || !sitekey || typeof turnstile === 'undefined') {
                                    return;
                                }

                                if (turnstileAdminWidgetId === null) {
                                    turnstileAdminWidgetId = turnstile.render(container, {
                                        sitekey: sitekey,
                                        theme: 'auto',
                                        callback: function(token) {
                                            document.getElementById('turnstile-error-message')?.classList.add('hidden');
                                            @this.set('turnstile_token', token);
                                        },
                                        'error-callback': function() {
                                            document.getElementById('turnstile-error-message')?.classList.remove('hidden');
                                            @this.set('turnstile_token', '');
                                        },
                                        'expired-callback': function() {
                                            @this.set('turnstile_token', '');
                                        }
                                    });
                                }
                            }

                            // 1. Explicit Onload Callback dari Cloudflare API
                            window.onloadTurnstileCallback = function() {
                                renderAdminTurnstile();
                            };

                            // 2. Fallback Timeout 8 Detik
                            setTimeout(function() {
                                if (turnstileAdminWidgetId === null && "{{ config('services.turnstile.site_key') }}") {
                                    document.getElementById('turnstile-error-message')?.classList.remove('hidden');
                                }
                            }, 8000);

                            // 3. Livewire Reset Event Listener
                            window.addEventListener('reset-turnstile', function() {
                                if (turnstileAdminWidgetId !== null && typeof turnstile !== 'undefined') {
                                    turnstile.reset(turnstileAdminWidgetId);
                                }
                                @this.set('turnstile_token', '');
                            });
                        </script>

                        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback&render=explicit" async defer></script>
                    @endif

                    <div class="mt-6">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-medium text-white bg-amber-600 hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200">
                            Masuk ke Dashboard
                        </button>
                    </div>
                </form>

                <div class="mt-8 border-t border-slate-200 dark:border-slate-700 pt-6 text-center">
                    <a href="{{ url('/login') }}" class="inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-500 hover:text-amber-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali ke Pilihan Portal
                    </a>
                </div>
            </div>
            
            <div class="mt-10 text-center text-xs text-slate-500 dark:text-slate-400 font-medium">
                &copy; {{ date('Y') }} Hak Cipta Dilindungi.<br>Sistem Presensi Berbasis Barcode
            </div>
        </div>
    </div>
</div>
