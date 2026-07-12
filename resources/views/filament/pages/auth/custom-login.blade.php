<div class="min-h-screen flex flex-col lg:flex-row bg-slate-900 lg:bg-white dark:lg:bg-slate-900 font-jakarta w-full relative overflow-hidden">
    <!-- Global Background for Mobile -->
    <div class="absolute inset-0 z-0 lg:hidden">
        @php
            $pengaturanSekolah = \App\Models\PengaturanSekolah::current();
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
                Selamat Datang di <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-300 drop-shadow-sm">Portal Admin</span>
            </h1>
            <p class="text-lg text-slate-300 leading-relaxed mb-8 max-w-md">
                Pusat kendali sistem presensi. Kelola data master, konfigurasi aplikasi, dan pantau seluruh aktivitas sekolah.
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
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Portal Admin</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sistem Presensi Berbasis Barcode</p>
            </div>

            <div>
                <h2 class="mt-6 text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight hidden lg:block">Login Admin</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 hidden lg:block">Silakan masuk ke akun administrator Anda.</p>
            </div>

            <div class="mt-8 lg:mt-10">
                <form wire:submit="authenticate">
                    {{ $this->form }}

                    <div class="mt-6">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-medium text-white bg-amber-600 hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200">
                            Masuk ke Dashboard Admin
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-10 text-center text-xs text-slate-500 dark:text-slate-400 font-medium">
                &copy; {{ date('Y') }} Hak Cipta Dilindungi.<br>Sistem Presensi Berbasis Barcode
            </div>
        </div>
    </div>
</div>
