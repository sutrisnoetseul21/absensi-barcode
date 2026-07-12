<div class="min-h-screen flex flex-col lg:flex-row bg-slate-900 lg:bg-white font-jakarta relative overflow-hidden">
    <!-- Global Background for Mobile -->
    <div class="absolute inset-0 z-0 lg:hidden">
        @if(isset($pengaturanSekolah) && $pengaturanSekolah->login_background_path)
            <img src="{{ asset('storage/' . $pengaturanSekolah->login_background_path) }}" class="w-full h-full object-cover object-center opacity-60">
        @else
            <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover object-center opacity-60">
        @endif
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/50 via-slate-900/70 to-slate-900/95 mix-blend-multiply"></div>
        <!-- Decorative Blobs for Mobile -->
        <div class="absolute top-1/4 -left-20 w-72 h-72 bg-indigo-500 rounded-full mix-blend-screen filter blur-[80px] opacity-30 animate-blob"></div>
        <div class="absolute bottom-1/4 -right-20 w-72 h-72 bg-violet-500 rounded-full mix-blend-screen filter blur-[80px] opacity-30 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Left Column: Visual (Desktop) -->
    <div class="relative hidden lg:flex lg:w-1/2 bg-slate-900 overflow-hidden items-center justify-center p-12 z-10">
        <div class="absolute inset-0 z-0">
            @if(isset($pengaturanSekolah) && $pengaturanSekolah->login_background_path)
                <img src="{{ asset('storage/' . $pengaturanSekolah->login_background_path) }}" class="w-full h-full object-cover object-center opacity-40">
            @else
                <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover object-center opacity-40">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-indigo-950 via-slate-900/60 to-slate-900/40 mix-blend-multiply"></div>
            <!-- Decorative Blobs -->
            <div class="absolute top-1/4 -left-20 w-72 h-72 bg-indigo-500 rounded-full mix-blend-screen filter blur-[80px] opacity-40 animate-blob"></div>
            <div class="absolute bottom-1/4 -right-20 w-72 h-72 bg-violet-500 rounded-full mix-blend-screen filter blur-[80px] opacity-40 animate-blob animation-delay-2000"></div>
        </div>
        
        <div class="relative z-10 w-full max-w-lg text-left">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-white/10 rounded-xl backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-lg">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-wide">Sistem Presensi</h2>
                    <p class="text-indigo-200 text-sm">Berbasis Barcode</p>
                </div>
            </div>
            <h1 class="text-4xl lg:text-5xl font-extrabold text-white mb-6 leading-tight">
                Selamat Datang di <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-300 drop-shadow-sm">Portal Wali Kelas</span>
            </h1>
            <p class="text-lg text-slate-300 leading-relaxed mb-8 max-w-md">
                Akses manajemen data kelas Anda, pantau riwayat kehadiran harian siswa, dan kelola laporan secara efisien.
            </p>
            <div class="flex items-center gap-4 text-sm text-slate-400 font-medium">
                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Server Online</div>
                <span>&bull;</span>
                <div>Aman & Terenkripsi</div>
            </div>
        </div>
    </div>

    <!-- Right Column: Form -->
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24 bg-transparent lg:bg-white relative z-10">
        <!-- Back to Home -->
        <a href="/" class="absolute top-6 right-6 lg:top-8 lg:right-8 flex items-center gap-2 text-sm font-semibold text-slate-300 lg:text-slate-500 hover:text-indigo-400 lg:hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>

        <!-- Form Wrapper with Glassmorphism on Mobile -->
        <div class="mx-auto w-full max-w-sm bg-white/10 lg:bg-transparent backdrop-blur-xl lg:backdrop-blur-none border border-white/20 lg:border-none rounded-3xl lg:rounded-none p-6 sm:p-8 lg:p-0 shadow-2xl lg:shadow-none">
            
            <!-- Mobile Header (Visible only on small screens) -->
            <div class="lg:hidden text-center mb-8">
                 <div class="mx-auto w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-4 border border-white/30 shadow-inner">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7"></path></svg>
                </div>
                <h2 class="text-2xl font-extrabold text-white tracking-tight drop-shadow-md">Portal Wali Kelas</h2>
                <p class="text-sm text-indigo-100 mt-1 drop-shadow-sm">Sistem Presensi Berbasis Barcode</p>
            </div>

            <div>
                <h2 class="mt-6 text-3xl font-extrabold text-slate-900 tracking-tight hidden lg:block">Masuk ke Akun</h2>
                <p class="mt-2 text-sm text-slate-500 hidden lg:block">Silakan masukkan username/NIP dan password Anda.</p>
            </div>

            <div class="mt-8 lg:mt-10">
                <form wire:submit.prevent="login" class="space-y-5">
                    <div>
                        <label for="username" class="block text-sm font-semibold text-white lg:text-slate-700 mb-1.5 drop-shadow-sm lg:drop-shadow-none">Username / NIP</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <input wire:model="username" id="username" type="text" required class="block w-full pl-11 pr-3 py-3 border border-slate-200 lg:border-slate-200 rounded-xl leading-5 bg-white/90 lg:bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 lg:focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition-all duration-200 sm:text-sm shadow-sm" placeholder="Masukkan Username/NIP Anda">
                        </div>
                        @error('username') <p class="mt-2 text-xs text-rose-300 lg:text-rose-500 font-medium drop-shadow-sm lg:drop-shadow-none">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-white lg:text-slate-700 mb-1.5 drop-shadow-sm lg:drop-shadow-none">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <input wire:model="password" id="password" type="password" required class="block w-full pl-11 pr-3 py-3 border border-slate-200 lg:border-slate-200 rounded-xl leading-5 bg-white/90 lg:bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 lg:focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition-all duration-200 sm:text-sm shadow-sm" placeholder="••••••••">
                        </div>
                        @error('password') <p class="mt-2 text-xs text-rose-300 lg:text-rose-500 font-medium drop-shadow-sm lg:drop-shadow-none">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input wire:model="remember" id="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded cursor-pointer transition-colors">
                            <label for="remember" class="ml-2 block text-sm text-slate-200 lg:text-slate-600 cursor-pointer select-none font-medium drop-shadow-sm lg:drop-shadow-none">
                                Ingat sesi ini
                            </label>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-900/50 lg:shadow-indigo-500/30 text-sm font-bold text-white bg-gradient-to-r from-indigo-500 to-violet-400 lg:from-indigo-600 lg:to-violet-500 hover:from-indigo-400 hover:to-violet-300 lg:hover:from-indigo-500 lg:hover:to-violet-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5">
                            Masuk ke Dashboard
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
                
                <div class="mt-10 text-center text-xs text-slate-300 lg:text-slate-500 font-medium drop-shadow-sm lg:drop-shadow-none">
                    &copy; {{ date('Y') }} Hak Cipta Dilindungi.<br>Sistem Presensi Berbasis Barcode
                </div>
            </div>
        </div>
    </div>
</div>
