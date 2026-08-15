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

                <!-- Login Portal Dropdown -->
                <div class="relative group" x-data="{ loginMenuOpen: false }" @mouseenter="loginMenuOpen = true" @mouseleave="loginMenuOpen = false">
                    <button class="relative px-2.5 xl:px-3.5 py-2 rounded-xl font-semibold text-xs xl:text-sm whitespace-nowrap transition-all duration-200 shrink-0 flex items-center gap-1.5"
                            :class="scrolled ? 'text-slate-600 hover:text-brand-primary-dark hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        Login Portal
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': loginMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="loginMenuOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                         class="absolute left-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 overflow-hidden"
                         style="display: none;">
                        <a href="{{ url('/portal-siswa') }}" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">Portal Siswa</a>
                        <a href="{{ url('/portal-guru') }}" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">Portal Guru</a>
                        <a href="{{ url('/portal-perpustakaan') }}" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">Portal Perpustakaan</a>
                        <a href="{{ url('/portal-presensi') }}" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">Portal Absensi</a>
                    </div>
                </div>

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
                                <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider mb-1.5 px-1">Pintasan Akses</p>
                                <div class="max-h-60 overflow-y-auto space-y-1 pr-0.5">
                                    
                                    <a href="{{ url('/portal-guru') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-indigo-100 hover:bg-indigo-50/50 text-xs font-semibold text-slate-700 hover:text-indigo-700 transition-all">
                                        <div class="p-1.5 rounded-lg bg-indigo-100 text-indigo-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <span>Portal Guru</span>
                                    </a>

                                    <a href="{{ url('/portal-perpustakaan') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-emerald-100 hover:bg-emerald-50/50 text-xs font-semibold text-slate-700 hover:text-emerald-700 transition-all">
                                        <div class="p-1.5 rounded-lg bg-emerald-100 text-emerald-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                        </div>
                                        <span>Portal Perpustakaan</span>
                                    </a>

                                    <a href="{{ url('/portal-presensi') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-amber-100 hover:bg-amber-50/50 text-xs font-semibold text-slate-700 hover:text-amber-700 transition-all">
                                        <div class="p-1.5 rounded-lg bg-amber-100 text-amber-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <span>Portal Presensi</span>
                                    </a>
                                    
                                    <div class="border-t border-slate-100 my-1 mx-2"></div>

                                    <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-blue-100 hover:bg-blue-50/50 text-xs font-semibold text-slate-700 hover:text-blue-700 transition-all">
                                        <div class="p-1.5 rounded-lg bg-blue-100 text-blue-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                        </div>
                                        <span>Presensi Digital</span>
                                    </a>

                                    <a href="{{ route('perpustakaan.kunjungan') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-purple-100 hover:bg-purple-50/50 text-xs font-semibold text-slate-700 hover:text-purple-700 transition-all">
                                        <div class="p-1.5 rounded-lg bg-purple-100 text-purple-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        </div>
                                        <span>Kunjungan Perpustakaan</span>
                                    </a>
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
                <div class="flex items-center gap-2 ml-2 xl:ml-3">
                    <a href="{{ $pengaturanSekolah?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}"
                        class="flex items-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary hover:from-brand-primary-light hover:to-brand-secondary-light text-white px-3 xl:px-4 py-2.5 rounded-xl font-bold text-xs xl:text-sm whitespace-nowrap shrink-0 shadow-lg shadow-brand-primary/30 transition-all duration-300 transform hover:scale-105 hover:shadow-brand-primary/50">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Presensi Digital
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse ml-1 shrink-0"></span>
                    </a>

                    <a href="{{ route('perpustakaan.kunjungan') }}"
                        class="flex items-center gap-2 bg-gradient-to-r from-brand-secondary to-brand-accent hover:from-brand-secondary-light hover:to-brand-accent-light text-white px-3 xl:px-4 py-2.5 rounded-xl font-bold text-xs xl:text-sm whitespace-nowrap shrink-0 shadow-lg shadow-brand-secondary/30 transition-all duration-300 transform hover:scale-105 hover:shadow-brand-secondary/50">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Kunjungan Perpus
                    </a>
                </div>
            </nav>

            <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-xl transition-all duration-200"
                :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'">
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

            <!-- Mobile Login Portal Accordion -->
            <div class="rounded-xl overflow-hidden transition-colors" :class="scrolled ? 'bg-slate-50' : 'bg-white/5'">
                <button @click="loginMenuOpenMobile = !loginMenuOpenMobile" class="w-full flex items-center justify-between px-4 py-3 font-semibold transition-colors"
                        :class="scrolled ? 'text-slate-700 hover:text-brand-primary' : 'text-slate-200 hover:text-white'">
                    Login Portal
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': loginMenuOpenMobile }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="loginMenuOpenMobile" class="px-4 pb-3 space-y-1" style="display: none;">
                    <a href="{{ url('/portal-siswa') }}" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                       :class="scrolled ? 'text-slate-600 hover:bg-brand-primary/10 hover:text-brand-primary' : 'text-slate-300 hover:bg-white/10 hover:text-white'">
                        Portal Siswa
                    </a>
                    <a href="{{ url('/portal-guru') }}" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                       :class="scrolled ? 'text-slate-600 hover:bg-brand-primary/10 hover:text-brand-primary' : 'text-slate-300 hover:bg-white/10 hover:text-white'">
                        Portal Guru
                    </a>
                    <a href="{{ url('/portal-perpustakaan') }}" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                       :class="scrolled ? 'text-slate-600 hover:bg-brand-primary/10 hover:text-brand-primary' : 'text-slate-300 hover:bg-white/10 hover:text-white'">
                        Portal Perpustakaan
                    </a>
                    <a href="{{ url('/portal-presensi') }}" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                       :class="scrolled ? 'text-slate-600 hover:bg-brand-primary/10 hover:text-brand-primary' : 'text-slate-300 hover:bg-white/10 hover:text-white'">
                        Portal Absensi
                    </a>
                </div>
            </div>

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

                    <p class="text-[10px] font-bold uppercase px-1 mt-2" :class="scrolled ? 'text-slate-400' : 'text-slate-400'">Pintasan Akses:</p>
                    <div class="space-y-1.5">
                        <a href="{{ url('/portal-guru') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all" :class="scrolled ? 'border-slate-100 hover:bg-slate-50 text-slate-700' : 'border-white/10 hover:bg-white/5 text-slate-200'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <span>Portal Guru</span>
                        </a>
                        <a href="{{ url('/portal-perpustakaan') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all" :class="scrolled ? 'border-slate-100 hover:bg-slate-50 text-slate-700' : 'border-white/10 hover:bg-white/5 text-slate-200'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            <span>Portal Perpustakaan</span>
                        </a>
                        <a href="{{ url('/portal-presensi') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all" :class="scrolled ? 'border-slate-100 hover:bg-slate-50 text-slate-700' : 'border-white/10 hover:bg-white/5 text-slate-200'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Portal Presensi</span>
                        </a>
                        <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all" :class="scrolled ? 'border-slate-100 hover:bg-slate-50 text-slate-700' : 'border-white/10 hover:bg-white/5 text-slate-200'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            <span>Presensi Digital</span>
                        </a>
                        <a href="{{ route('perpustakaan.kunjungan') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all" :class="scrolled ? 'border-slate-100 hover:bg-slate-50 text-slate-700' : 'border-white/10 hover:bg-white/5 text-slate-200'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            <span>Kunjungan Perpustakaan</span>
                        </a>
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

            <div class="grid grid-cols-1 gap-2 mt-4 pt-4 border-t" :class="scrolled ? 'border-slate-200' : 'border-white/10'">
                <a href="{{ $pengaturanSekolah?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" class="flex items-center justify-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary text-white px-4 py-3 rounded-xl font-bold shadow-md shadow-brand-primary/20 hover:shadow-brand-primary/40 transition-shadow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Presensi Digital
                </a>
                <a href="{{ route('perpustakaan.kunjungan') }}" class="flex items-center justify-center gap-2 bg-gradient-to-r from-brand-secondary to-brand-accent text-white px-4 py-3 rounded-xl font-bold shadow-md shadow-brand-secondary/20 hover:shadow-brand-secondary/40 transition-shadow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Kunjungan Perpus
                </a>
            </div>
        </div>
    </div>
</header>
