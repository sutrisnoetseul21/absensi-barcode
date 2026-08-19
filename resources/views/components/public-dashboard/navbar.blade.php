@props(['pengaturanSekolah' => null, 'alwaysDark' => false])

<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500" id="main-navbar"
    x-data="{ scrolled: false, mobileMenuOpen: false, loginMenuOpenMobile: false }"
    @scroll.window="scrolled = {{ $alwaysDark ? 'false' : 'window.scrollY > 30' }}"
    :class="scrolled ? 'bg-white/80 backdrop-blur-xl shadow-lg shadow-brand-primary-100/50 border-b border-slate-200/60' : (mobileMenuOpen ? 'bg-slate-950/70 backdrop-blur-2xl border-b border-white/10' : 'bg-slate-950/40 backdrop-blur-sm border-b border-white/10')">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Header Kiri: Logo & Nama Sekolah -->
            <div class="flex items-center gap-3 group">
                @if($pengaturanSekolah && $pengaturanSekolah->school_logo_path)
                    <div class="relative">
                        <div class="absolute inset-0 bg-brand-primary-light/30 rounded-xl blur-md group-hover:blur-lg transition-all duration-300"></div>
                        <img src="{{ asset('storage/' . $pengaturanSekolah->school_logo_path) }}" alt="Logo"
                            class="relative h-10 sm:h-12 w-auto object-contain drop-shadow-md">
                    </div>
                @endif
                <div>
                    <h1 class="text-lg sm:text-xl font-extrabold tracking-tight leading-tight transition-colors duration-300"
                        :class="scrolled ? 'text-slate-800' : 'text-white drop-shadow-md'">
                        {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'Digital' }}
                    </h1>
                    <p class="text-xs font-medium transition-colors duration-300" :class="scrolled ? 'text-brand-primary' : 'text-brand-primary-100'">
                        {{ request()->is('/') ? 'Sistem Informasi Terpadu' : (request()->is('perpustakaan') ? 'Perpustakaan Digital' : 'Sistem Presensi Digital') }}
                    </p>
                </div>
            </div>

            <!-- Header Kanan: Menu Navigasi Desktop -->
            <nav class="hidden lg:flex items-center space-x-1 xl:space-x-2 shrink-0">
                <a href="{{ url('/') }}"
                    class="relative group px-2.5 xl:px-3.5 py-2 rounded-xl font-semibold text-xs xl:text-sm whitespace-nowrap transition-all duration-200 shrink-0"
                    :class="scrolled ? 'text-slate-600 hover:text-brand-primary-dark hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                    <span class="flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Home
                    </span>
                </a>
                <a href="{{ url('/presensi') }}"
                    class="relative group px-2.5 xl:px-3.5 py-2 rounded-xl font-semibold text-xs xl:text-sm whitespace-nowrap transition-all duration-200 shrink-0"
                    :class="scrolled ? 'text-slate-600 hover:text-brand-primary-dark hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                    <span class="flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Presensi
                    </span>
                </a>
                <a href="{{ url('/perpustakaan') }}"
                    class="relative group px-2.5 xl:px-3.5 py-2 rounded-xl font-semibold text-xs xl:text-sm whitespace-nowrap transition-all duration-200 shrink-0"
                    :class="scrolled ? 'text-slate-600 hover:text-brand-primary-dark hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                    <span class="flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Perpustakaan
                    </span>
                </a>

                @guest
                    <a href="{{ url('/login') }}"
                        class="relative group px-2.5 xl:px-3.5 py-2 rounded-xl font-semibold text-xs xl:text-sm whitespace-nowrap transition-all duration-200 shrink-0"
                        :class="scrolled ? 'text-slate-600 hover:text-brand-primary-dark hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            Login
                        </span>
                    </a>
                @endguest

                @auth
                    @php
                        $authUser = Auth::user();
                        $authName = $authUser->display_name;
                        $authRole = $authUser->role_badge;
                        $accessiblePortals = $authUser->getAccessiblePortals();
                    @endphp

                    <!-- User Profile & Logout Dropdown -->
                    <div class="relative ml-1 xl:ml-2 shrink-0" x-data="{ userMenuOpen: false }" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" 
                                class="flex items-center gap-2 py-1.5 px-2.5 xl:px-3 rounded-xl transition-all duration-200 border whitespace-nowrap shrink-0"
                                :class="scrolled ? 'bg-slate-100 border-slate-200 text-slate-800 hover:bg-slate-200' : 'bg-white/10 border-white/20 text-white hover:bg-white/20'">
                            <div class="w-7 h-7 rounded-lg bg-brand-primary text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                                {{ strtoupper(substr($authName, 0, 1)) }}
                            </div>
                            <div class="flex flex-col text-left whitespace-nowrap">
                                <span class="text-xs font-bold leading-tight truncate max-w-[100px] xl:max-w-[130px] whitespace-nowrap">{{ $authName }}</span>
                                <span class="text-[10px] opacity-75 font-medium leading-none whitespace-nowrap">{{ $authRole }}</span>
                            </div>
                            <svg class="w-3.5 h-3.5 opacity-75 transition-transform duration-200 shrink-0" :class="{ 'rotate-180': userMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="userMenuOpen" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-50 overflow-hidden text-slate-800"
                             style="display: none;">
                            
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Login Sebagai:</p>
                                <p class="text-sm font-bold text-slate-900 truncate">{{ $authName }}</p>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-brand-primary-50 text-brand-primary border border-brand-primary-100">
                                        {{ $authRole }}
                                    </span>
                                    @if($authUser->email)
                                        <span class="text-[10px] text-slate-400 truncate max-w-[130px]">{{ $authUser->email }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="px-3 py-2">
                                <div class="flex items-center justify-between px-1 mb-1.5">
                                    <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Pintasan Akses</p>
                                    @if(count($accessiblePortals) > 1)
                                        <a href="{{ url('/pilih-portal') }}" class="text-[10px] font-bold text-brand-primary hover:underline">Semua Portal</a>
                                    @endif
                                </div>
                                <div class="max-h-60 overflow-y-auto space-y-1 pr-0.5">
                                    @forelse($accessiblePortals as $portal)
                                        <a href="{{ $portal['url'] }}" @click="userMenuOpen = false" class="flex items-center gap-2.5 p-2 rounded-xl border border-transparent hover:border-indigo-100 hover:bg-indigo-50/50 text-xs font-semibold text-slate-700 hover:text-indigo-700 transition-all">
                                            <div class="p-1.5 rounded-lg {{ $portal['icon_bg'] ?? 'bg-indigo-100 text-indigo-600' }} shrink-0">
                                                @if(($portal['icon'] ?? '') === 'shield')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                @elseif(($portal['icon'] ?? '') === 'user-group')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                @elseif(($portal['icon'] ?? '') === 'clock')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @elseif(($portal['icon'] ?? '') === 'book')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                @elseif(($portal['icon'] ?? '') === 'academic-cap')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                @endif
                                            </div>
                                            <div class="flex flex-col text-left truncate">
                                                <span class="truncate leading-tight">{{ $portal['name'] }}</span>
                                                <span class="text-[9px] text-slate-400 font-normal leading-none mt-0.5">{{ $portal['badge'] }}</span>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-xs text-slate-400 p-2 text-center">Tidak ada pintasan aktif.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="pt-1 mt-1 border-t border-slate-100 px-1.5">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                                        <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                        Keluar (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth

                <!-- Action Buttons: Presensi Digital & Cari Buku -->
                @auth
                    @if(!$authUser->hasRole('siswa'))
                        <div class="flex items-center gap-2 ml-2 xl:ml-3">
                            @if($authUser->hasRole(['petugas_presensi', 'admin_portal_presensi', 'super_admin', 'wali_kelas']))
                                <a href="{{ $pengaturanSekolah?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}"
                                    class="flex items-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary hover:from-brand-primary-light hover:to-brand-secondary-light text-white px-3 xl:px-4 py-2.5 rounded-xl font-bold text-xs xl:text-sm whitespace-nowrap shrink-0 shadow-lg shadow-brand-primary/30 transition-all duration-300 transform hover:scale-105 hover:shadow-brand-primary/50">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    Presensi Digital
                                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse ml-1 shrink-0"></span>
                                </a>
                            @endif

                            @if($authUser->hasRole(['petugas_perpustakaan', 'admin_perpustakaan', 'super_admin']))
                                <a href="{{ route('perpustakaan.kunjungan') }}"
                                    class="flex items-center gap-2 bg-gradient-to-r from-brand-secondary to-brand-accent hover:from-brand-secondary-light hover:to-brand-accent-light text-white px-3 xl:px-4 py-2.5 rounded-xl font-bold text-xs xl:text-sm whitespace-nowrap shrink-0 shadow-lg shadow-brand-secondary/30 transition-all duration-300 transform hover:scale-105 hover:shadow-brand-secondary/50">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Kunjungan Perpus
                                </a>
                            @endif
                        </div>
                    @endif
                @endauth
            </nav>

            <!-- Mobile Menu Button -->
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-xl transition-all duration-200 cursor-pointer"
                :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'"
                aria-label="Menu Navigasi">
                <svg x-show="!mobileMenuOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileMenuOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden absolute top-full left-0 w-full backdrop-blur-xl border-b shadow-xl transition-colors duration-300 overflow-y-auto max-h-[80vh]"
         :class="scrolled ? 'bg-white/95 border-slate-200' : 'bg-slate-950/90 border-white/10'"
         style="display: none;">
        <div class="px-4 pt-2 pb-6 space-y-2">
            <a href="{{ url('/') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
               :class="scrolled ? 'text-slate-700 hover:bg-brand-primary-50 hover:text-brand-primary' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                Home
            </a>
            <a href="{{ url('/presensi') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
               :class="scrolled ? 'text-slate-700 hover:bg-brand-primary-50 hover:text-brand-primary' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                Presensi
            </a>
            <a href="{{ url('/perpustakaan') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
               :class="scrolled ? 'text-slate-700 hover:bg-brand-primary-50 hover:text-brand-primary' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                Perpustakaan
            </a>

            @guest
                <a href="{{ url('/login') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-brand-primary-50 hover:text-brand-primary' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Login
                </a>
            @endguest

            @auth
                @php
                    $authUser = Auth::user();
                    $authName = $authUser->display_name;
                    $authRole = $authUser->role_badge;
                    $accessiblePortals = $authUser->getAccessiblePortals();
                @endphp

                <div class="pt-3 border-t space-y-2" :class="scrolled ? 'border-slate-200' : 'border-white/10'">
                    <div class="px-4 py-2.5 rounded-xl" :class="scrolled ? 'bg-brand-primary/10' : 'bg-white/10'">
                        <p class="text-[10px] uppercase font-bold" :class="scrolled ? 'text-slate-400' : 'text-slate-400'">Login Sebagai:</p>
                        <p class="text-sm font-bold" :class="scrolled ? 'text-slate-800' : 'text-white'">{{ $authName }}</p>
                        <span class="inline-block mt-0.5 text-xs font-semibold" :class="scrolled ? 'text-brand-primary' : 'text-brand-primary-light'">{{ $authRole }}</span>
                    </div>

                    <div class="flex items-center justify-between px-1 mt-2">
                        <p class="text-[10px] font-bold uppercase" :class="scrolled ? 'text-slate-400' : 'text-slate-400'">Pintasan Akses:</p>
                        @if(count($accessiblePortals) > 1)
                            <a href="{{ url('/pilih-portal') }}" class="text-[10px] font-bold text-brand-primary hover:underline">Semua Portal</a>
                        @endif
                    </div>
                    <div class="space-y-1.5">
                        @forelse($accessiblePortals as $portal)
                            <a href="{{ $portal['url'] }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all" :class="scrolled ? 'border-slate-100 hover:bg-slate-50 text-slate-700' : 'border-white/10 hover:bg-white/5 text-slate-200'">
                                <div class="w-5 h-5 flex items-center justify-center shrink-0">
                                    @if(($portal['icon'] ?? '') === 'shield')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    @elseif(($portal['icon'] ?? '') === 'user-group')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    @elseif(($portal['icon'] ?? '') === 'clock')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif(($portal['icon'] ?? '') === 'book')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @elseif(($portal['icon'] ?? '') === 'academic-cap')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    @endif
                                </div>
                                <div class="flex flex-col text-left truncate">
                                    <span class="truncate leading-tight">{{ $portal['name'] }}</span>
                                    <span class="text-[9px] opacity-75 font-normal leading-none mt-0.5">{{ $portal['badge'] }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-400 p-2 text-center">Tidak ada pintasan aktif.</p>
                        @endforelse
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 rounded-xl font-bold flex items-center justify-between transition-colors"
                                :class="scrolled ? 'text-rose-600 bg-rose-50 hover:bg-rose-100' : 'text-rose-400 bg-rose-500/10 hover:bg-rose-500/20'">
                            Keluar (Logout)
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </button>
                    </form>
                </div>
            @endauth

            @auth
                @if(!$authUser->hasRole('siswa'))
                    <div class="grid grid-cols-1 gap-2 mt-4 pt-4 border-t" :class="scrolled ? 'border-slate-200' : 'border-white/10'">
                        @if($authUser->hasRole(['petugas_presensi', 'admin_portal_presensi', 'super_admin']))
                            <a href="{{ $pengaturanSekolah?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" class="flex items-center justify-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary text-white px-4 py-3 rounded-xl font-bold shadow-md shadow-brand-primary/20 hover:shadow-brand-primary/40 transition-shadow">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                Presensi Digital
                            </a>
                        @endif
                        @if($authUser->hasRole(['petugas_perpustakaan', 'admin_perpustakaan', 'super_admin']))
                            <a href="{{ route('perpustakaan.kunjungan') }}" class="flex items-center justify-center gap-2 bg-gradient-to-r from-brand-secondary to-brand-accent text-white px-4 py-3 rounded-xl font-bold shadow-md shadow-brand-secondary/20 hover:shadow-brand-secondary/40 transition-shadow">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Kunjungan Perpus
                            </a>
                        @endif
                    </div>
                @endif
            @endauth
        </div>
    </div>
</header>
