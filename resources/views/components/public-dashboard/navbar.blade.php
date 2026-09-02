@props(['pengaturanSekolah' => null, 'alwaysDark' => false])

@php
    $webSetting = \App\Models\WebSetting::instance();
    $linkPengaduan = $webSetting->link_pengaduan ?: url('/pengaduan');
@endphp

<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500" id="main-navbar"
    x-data="{ 
        scrolled: false, 
        mobileMenuOpen: false, 
        mobileProfilOpen: false,
        mobileLayananOpen: false
    }"
    @scroll.window="scrolled = {{ $alwaysDark ? 'false' : 'window.scrollY > 30' }}"
    :class="scrolled ? 'bg-white/90 backdrop-blur-xl shadow-lg shadow-slate-200/50 border-b border-slate-200/80 py-2' : (mobileMenuOpen ? 'bg-slate-950/90 backdrop-blur-2xl border-b border-white/10 py-3' : 'bg-slate-950/40 backdrop-blur-md border-b border-white/10 py-3.5')">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 sm:h-18">
            
            {{-- Header Kiri: Logo & Nama Sekolah --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                @if($pengaturanSekolah && $pengaturanSekolah->school_logo_path)
                    <div class="relative">
                        <div class="absolute inset-0 bg-brand-primary/30 rounded-2xl blur-md group-hover:blur-lg transition-all duration-300"></div>
                        <img src="{{ asset('storage/' . $pengaturanSekolah->school_logo_path) }}" alt="Logo"
                            class="relative h-10 sm:h-12 w-auto object-contain drop-shadow-md">
                    </div>
                @else
                    <div class="w-10 h-10 rounded-2xl bg-brand-primary text-white flex items-center justify-center font-bold text-lg shadow-md">
                        <i class="fas fa-school"></i>
                    </div>
                @endif
                <div>
                    <h1 class="text-base sm:text-lg font-extrabold tracking-tight leading-tight transition-colors duration-300"
                        :class="scrolled ? 'text-slate-900' : 'text-white drop-shadow-md'">
                        {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'Digital School' }}
                    </h1>
                    <p class="text-[11px] font-semibold transition-colors duration-300" :class="scrolled ? 'text-brand-primary' : 'text-amber-300'">
                        {{ request()->is('/') ? 'Sistem Informasi Terpadu' : (request()->is('perpustakaan*') ? 'Perpustakaan Digital' : 'Sistem Presensi Digital') }}
                    </p>
                </div>
            </a>

            {{-- Header Kanan: Menu Navigasi Desktop --}}
            <nav class="hidden xl:flex items-center space-x-1 shrink-0">
                
                {{-- 1. Beranda --}}
                <a href="{{ url('/') }}"
                    class="relative group px-3 py-2 rounded-xl font-bold text-xs uppercase tracking-wider whitespace-nowrap transition-all duration-200"
                    :class="scrolled ? 'text-slate-700 hover:text-brand-primary hover:bg-slate-100' : 'text-white/90 hover:text-white hover:bg-white/10'">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-home text-xs"></i> Beranda
                    </span>
                </a>

                {{-- 2. Profil (Dropdown Sub-Menu) --}}
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button type="button" @click="open = !open" 
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-xs uppercase tracking-wider whitespace-nowrap transition-all duration-200"
                        :class="scrolled ? 'text-slate-700 hover:text-brand-primary hover:bg-slate-100' : 'text-white/90 hover:text-white hover:bg-white/10'">
                        <span>Profil</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-70 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         @click.away="open = false"
                         class="absolute left-0 mt-1 min-w-[230px] bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 overflow-hidden text-slate-800"
                         style="display: none;">
                        <a href="{{ url('/') }}#tentang" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">
                            <i class="fas fa-history text-slate-400 w-4"></i> Sejarah Singkat
                        </a>
                        <a href="{{ url('/') }}#tentang" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">
                            <i class="fas fa-bullseye text-slate-400 w-4"></i> Visi & Misi Sekolah
                        </a>
                        <a href="{{ url('/') }}#sambutan" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">
                            <i class="fas fa-user-tie text-slate-400 w-4"></i> Sambutan Kepala Sekolah
                        </a>
                        <a href="{{ url('/') }}#tentang" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">
                            <i class="fas fa-sitemap text-slate-400 w-4"></i> Struktur Organisasi
                        </a>
                        <a href="{{ url('/') }}#prestasi" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors border-t border-slate-50">
                            <i class="fas fa-trophy text-amber-500 w-4"></i> Prestasi Sekolah
                        </a>
                    </div>
                </div>

                {{-- 3. Pendidik (Guru & Karyawan) --}}
                <a href="{{ route('guru.all') }}"
                    class="relative group px-3 py-2 rounded-xl font-bold text-xs uppercase tracking-wider whitespace-nowrap transition-all duration-200"
                    :class="scrolled ? 'text-slate-700 hover:text-brand-primary hover:bg-slate-100' : 'text-white/90 hover:text-white hover:bg-white/10'">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-chalkboard-teacher text-xs"></i> Pendidik
                    </span>
                </a>

                {{-- 4. Layanan Publik (Dropdown Sub-Menu) --}}
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button type="button" @click="open = !open" 
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-xs uppercase tracking-wider whitespace-nowrap transition-all duration-200"
                        :class="scrolled ? 'text-slate-700 hover:text-brand-primary hover:bg-slate-100' : 'text-white/90 hover:text-white hover:bg-white/10'">
                        <span>Layanan Publik</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-70 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         @click.away="open = false"
                         class="absolute left-0 mt-1 min-w-[240px] bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 overflow-hidden text-slate-800"
                         style="display: none;">
                        <a href="{{ $linkPengaduan }}" target="{{ str_starts_with($linkPengaduan, 'http') ? '_blank' : '_self' }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-primary transition-colors">
                            <i class="fas fa-bullhorn text-amber-500 w-4"></i> Layanan Aspirasi & Pengaduan
                        </a>
                    </div>
                </div>

                {{-- 5. Alumni --}}
                <a href="{{ url('/alumni') }}"
                    class="relative group px-3 py-2 rounded-xl font-bold text-xs uppercase tracking-wider whitespace-nowrap transition-all duration-200"
                    :class="scrolled ? 'text-slate-700 hover:text-brand-primary hover:bg-slate-100' : 'text-white/90 hover:text-white hover:bg-white/10'">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-user-graduate text-xs"></i> Alumni
                    </span>
                </a>

                {{-- 6. Presensi --}}
                <a href="{{ url('/presensi') }}"
                    class="relative group px-3 py-2 rounded-xl font-bold text-xs uppercase tracking-wider whitespace-nowrap transition-all duration-200"
                    :class="scrolled ? 'text-slate-700 hover:text-brand-primary hover:bg-slate-100' : 'text-white/90 hover:text-white hover:bg-white/10'">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-clock text-xs"></i> Presensi
                    </span>
                </a>

                {{-- 7. Perpustakaan --}}
                <a href="{{ url('/perpustakaan') }}"
                    class="relative group px-3 py-2 rounded-xl font-bold text-xs uppercase tracking-wider whitespace-nowrap transition-all duration-200"
                    :class="scrolled ? 'text-slate-700 hover:text-brand-primary hover:bg-slate-100' : 'text-white/90 hover:text-white hover:bg-white/10'">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-book-reader text-xs"></i> Perpustakaan
                    </span>
                </a>

                {{-- Separator --}}
                <div class="h-5 w-px mx-1.5" :class="scrolled ? 'bg-slate-200' : 'bg-white/20'"></div>

                {{-- 8. Login & Status Autentikasi --}}
                @guest
                    <a href="{{ url('/pilih-portal') }}"
                        class="px-4 py-2 rounded-xl font-bold text-xs uppercase tracking-wider whitespace-nowrap shadow-sm transition-all duration-200 flex items-center gap-1.5"
                        :class="scrolled ? 'bg-brand-primary hover:opacity-90 text-white shadow-brand-primary/20' : 'bg-white text-slate-900 hover:bg-amber-300'">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                @endguest

                @auth
                    @php
                        $authUser = Auth::user();
                        $authName = $authUser->display_name;
                        $authRole = $authUser->role_badge;
                        $accessiblePortals = $authUser->getAccessiblePortals();
                    @endphp

                    {{-- User Profile Dropdown --}}
                    <div class="relative shrink-0" x-data="{ userMenuOpen: false }" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" 
                                class="flex items-center gap-2 py-1.5 px-3 rounded-xl transition-all duration-200 border whitespace-nowrap shrink-0"
                                :class="scrolled ? 'bg-slate-100 border-slate-200 text-slate-800 hover:bg-slate-200' : 'bg-white/10 border-white/20 text-white hover:bg-white/20'">
                            <div class="w-7 h-7 rounded-lg bg-brand-primary text-white font-bold text-xs flex items-center justify-center shadow-sm shrink-0">
                                {{ strtoupper(substr($authName, 0, 1)) }}
                            </div>
                            <div class="flex flex-col text-left whitespace-nowrap">
                                <span class="text-xs font-bold leading-tight truncate max-w-[110px] whitespace-nowrap">{{ $authName }}</span>
                                <span class="text-[10px] opacity-75 font-medium leading-none whitespace-nowrap">{{ $authRole }}</span>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] opacity-70 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }"></i>
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
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand-primary border border-emerald-100">
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
                                <div class="max-h-60 overflow-y-auto space-y-1 pr-0.5 custom-scrollbar">
                                    @forelse($accessiblePortals as $portal)
                                        <a href="{{ $portal['url'] }}" @click="userMenuOpen = false" class="flex items-center gap-2.5 p-2 rounded-xl border border-transparent hover:border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition-all">
                                            <div class="w-6 h-6 rounded-lg {{ $portal['icon_bg'] ?? 'bg-emerald-100 text-brand-primary' }} flex items-center justify-center shrink-0 text-xs">
                                                <i class="fas fa-external-link-alt text-[10px]"></i>
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
                                        <i class="fas fa-sign-out-alt text-rose-500"></i>
                                        Keluar (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Presensi Digital (Hanya untuk Guru / Petugas / Admin) --}}
                    @if(!$authUser->hasRole('siswa'))
                        @if($authUser->hasRole(['petugas_presensi', 'admin_portal_presensi', 'super_admin', 'wali_kelas', 'guru']))
                            <a href="{{ $pengaturanSekolah?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}"
                                class="flex items-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-90 text-white px-3.5 py-2 rounded-xl font-bold text-xs whitespace-nowrap shrink-0 shadow-md shadow-brand-primary/20 transition-all transform hover:scale-105">
                                <i class="fas fa-qrcode text-sm"></i>
                                Presensi Digital
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse ml-0.5 shrink-0"></span>
                            </a>
                        @endif
                    @endif
                @endauth

            </nav>

            {{-- Mobile Hamburger Button --}}
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="xl:hidden p-2 rounded-xl transition-all duration-200 cursor-pointer"
                :class="scrolled ? 'text-slate-800 hover:bg-slate-100' : 'text-white hover:bg-white/10'"
                aria-label="Menu Navigasi">
                <i x-show="!mobileMenuOpen" class="fas fa-bars text-xl"></i>
                <i x-show="mobileMenuOpen" class="fas fa-times text-xl" style="display: none;"></i>
            </button>
        </div>
    </div>

    {{-- Mobile Menu Drawer --}}
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="xl:hidden absolute top-full left-0 w-full backdrop-blur-2xl border-b shadow-2xl transition-colors duration-300 overflow-y-auto max-h-[85vh]"
         :class="scrolled ? 'bg-white/95 border-slate-200 text-slate-800' : 'bg-slate-950/95 border-white/10 text-white'"
         style="display: none;">
        
        <div class="px-5 pt-3 pb-8 space-y-1.5">
            
            {{-- 1. Beranda --}}
            <a href="{{ url('/') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
               :class="scrolled ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-white/10 text-white'">
                <i class="fas fa-home w-5 text-brand-primary"></i> Beranda
            </a>

            {{-- 2. Profil (Accordion) --}}
            <div>
                <button type="button" @click="mobileProfilOpen = !mobileProfilOpen" class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
                    :class="scrolled ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-white/10 text-white'">
                    <span class="flex items-center gap-2"><i class="fas fa-school w-5 text-brand-primary"></i> Profil</span>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="mobileProfilOpen ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="mobileProfilOpen" class="pl-8 pr-2 py-1 space-y-1" style="display: none;">
                    <a href="{{ url('/') }}#tentang" class="block py-2 text-xs font-semibold opacity-80 hover:opacity-100">Sejarah Singkat</a>
                    <a href="{{ url('/') }}#tentang" class="block py-2 text-xs font-semibold opacity-80 hover:opacity-100">Visi & Misi Sekolah</a>
                    <a href="{{ url('/') }}#sambutan" class="block py-2 text-xs font-semibold opacity-80 hover:opacity-100">Sambutan Kepala Sekolah</a>
                    <a href="{{ url('/') }}#tentang" class="block py-2 text-xs font-semibold opacity-80 hover:opacity-100">Struktur Organisasi</a>
                    <a href="{{ url('/') }}#prestasi" class="block py-2 text-xs font-semibold text-amber-400">Prestasi Sekolah</a>
                </div>
            </div>

            {{-- 3. Pendidik --}}
            <a href="{{ route('guru.all') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
               :class="scrolled ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-white/10 text-white'">
                <i class="fas fa-chalkboard-teacher w-5 text-brand-primary"></i> Pendidik (PTK)
            </a>

            {{-- 4. Layanan Publik (Accordion) --}}
            <div>
                <button type="button" @click="mobileLayananOpen = !mobileLayananOpen" class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
                    :class="scrolled ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-white/10 text-white'">
                    <span class="flex items-center gap-2"><i class="fas fa-concierge-bell w-5 text-brand-primary"></i> Layanan Publik</span>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="mobileLayananOpen ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="mobileLayananOpen" class="pl-8 pr-2 py-1 space-y-1" style="display: none;">
                    <a href="{{ $linkPengaduan }}" target="{{ str_starts_with($linkPengaduan, 'http') ? '_blank' : '_self' }}" class="block py-2 text-xs font-semibold opacity-80 hover:opacity-100">
                        Layanan Aspirasi & Pengaduan
                    </a>
                </div>
            </div>

            {{-- 5. Alumni --}}
            <a href="{{ url('/alumni') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
               :class="scrolled ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-white/10 text-white'">
                <i class="fas fa-user-graduate w-5 text-brand-primary"></i> Alumni
            </a>

            {{-- 6. Presensi --}}
            <a href="{{ url('/presensi') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
               :class="scrolled ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-white/10 text-white'">
                <i class="fas fa-clock w-5 text-brand-primary"></i> Presensi
            </a>

            {{-- 7. Perpustakaan --}}
            <a href="{{ url('/perpustakaan') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
               :class="scrolled ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-white/10 text-white'">
                <i class="fas fa-book-reader w-5 text-brand-primary"></i> Perpustakaan
            </a>

            {{-- Auth Box Mobile --}}
            <div class="pt-4 mt-4 border-t" :class="scrolled ? 'border-slate-200' : 'border-white/10'">
                @guest
                    <a href="{{ url('/pilih-portal') }}" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-bold text-sm bg-brand-primary text-white shadow-md">
                        <i class="fas fa-sign-in-alt"></i> Masuk / Login
                    </a>
                @endguest

                @auth
                    <div class="p-3.5 rounded-2xl mb-3" :class="scrolled ? 'bg-slate-100' : 'bg-white/10'">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Login Sebagai:</p>
                        <p class="text-sm font-bold truncate">{{ $authName }}</p>
                        <span class="inline-block mt-0.5 text-xs font-semibold text-brand-primary">{{ $authRole }}</span>
                    </div>

                    @if(!$authUser->hasRole('siswa'))
                        @if($authUser->hasRole(['petugas_presensi', 'admin_portal_presensi', 'super_admin', 'wali_kelas', 'guru']))
                            <a href="{{ $pengaturanSekolah?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" 
                               class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-bold text-sm bg-gradient-to-r from-brand-primary to-brand-secondary text-white shadow-md mb-2">
                                <i class="fas fa-qrcode"></i> Presensi Digital
                            </a>
                        @endif
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 rounded-xl font-bold text-xs flex items-center justify-between text-rose-500 hover:bg-rose-50/20 transition-colors">
                            <span>Keluar (Logout)</span>
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                @endauth
            </div>

        </div>
    </div>
</header>
