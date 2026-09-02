<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? ('ERP Portal ' . (\App\Models\PengaturanSekolah::current()?->school_name ?? 'Sekolah')) }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- Favicon -->
        @php
            $sekolah = \App\Models\PengaturanSekolah::current();
            $favicon = $sekolah?->school_logo_path ? asset('storage/' . $sekolah->school_logo_path) : asset('favicon.ico');
        @endphp
        <link rel="icon" type="image/png" href="{{ $favicon }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            $themeSettings = \App\Models\PengaturanSekolah::current();
        @endphp

        @if($themeSettings)
            <style>
                :root {
                    @if($themeSettings->theme_primary) --color-brand-primary: {{ $themeSettings->theme_primary }}; @endif
                    @if($themeSettings->theme_secondary) --color-brand-secondary: {{ $themeSettings->theme_secondary }}; @endif
                    @if($themeSettings->theme_accent) --color-brand-accent: {{ $themeSettings->theme_accent }}; @endif
                    @if($themeSettings->theme_warning) --color-brand-warning: {{ $themeSettings->theme_warning }}; @endif
                    @if($themeSettings->theme_danger) --color-brand-danger: {{ $themeSettings->theme_danger }}; @endif
                    @if($themeSettings->theme_info) --color-brand-info: {{ $themeSettings->theme_info }}; @endif
                }
            </style>
        @endif
        <!-- Reset sidebar default for tablet/small screen: always collapse on screens < 1280px unless user explicitly expanded -->
        <script>
            (function() {
                // Force collapse on tablet screens (< 1280px) if sidebar is currently expanded (false) or unset
                var s = localStorage.getItem('portal_sidebar_collapsed');
                var w = window.innerWidth;
                if (w < 1280 && (s === null || s === 'false')) {
                    localStorage.setItem('portal_sidebar_collapsed', 'true');
                }
            })();
        </script>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50 flex overflow-hidden" style="height: 100dvh; max-height: 100dvh;" x-data="{ sidebarOpen: false, sidebarCollapsed: (() => { const s = localStorage.getItem('portal_sidebar_collapsed'); return s === null ? window.innerWidth < 1280 : s === 'true'; })(), windowWidth: window.innerWidth, get isCollapsed() { return this.sidebarCollapsed && this.windowWidth >= 1024; } }" x-init="window.addEventListener('resize', () => { windowWidth = window.innerWidth; }); $watch('sidebarCollapsed', val => localStorage.setItem('portal_sidebar_collapsed', val));">
        
        @php
            $user = Auth::guard('web')->user();
            $isPerpusRoute = request()->is('portal-perpustakaan*');
            $isPresensiRoute = request()->is('portal-presensi*') && !request()->is('portal-presensi/scan*');

            if($user && $user->hasRole('siswa')) {
                $logoutRoute = route('portal-siswa.logout');
                $userRole = 'Siswa';
                $userName = $user->student?->nama ?? $user->name;
                $activeDashboard = route('portal-siswa.dashboard');
            } elseif($user && $user->hasRole('wali_kelas') && !$isPerpusRoute) {
                $logoutRoute = route('portal-guru.logout');
                $userRole = 'Guru';
                $userName = $user->teacher?->nama ?? $user->name;
                $activeDashboard = route('portal-guru.dashboard');
            } elseif($isPerpusRoute || ($user && ($user->hasRole('petugas_perpustakaan') || $user->hasRole('admin_perpustakaan')))) {
                $logoutRoute = route('portal-perpustakaan.logout');
                $userRole = 'Petugas Perpustakaan';
                $userName = $user?->name ?? 'Petugas';
                $activeDashboard = route('portal-perpustakaan.dashboard');
            } elseif($isPresensiRoute || ($user && $user->hasRole('admin_portal_presensi'))) {
                $logoutRoute = route('portal-presensi.logout') ?? '/';
                $userRole = 'Admin Presensi';
                $userName = $user?->name ?? 'Admin';
                $activeDashboard = route('portal-presensi.dashboard');
            } else {
                $logoutRoute = '/';
                $userRole = 'Staff';
                $userName = $user?->name ?? 'Tamu';
                $activeDashboard = '/';
            }
        @endphp

        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-900/80 backdrop-blur-sm lg:hidden transition-opacity" 
             x-transition:enter="transition-opacity ease-linear duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200/80 flex flex-col transform transition-all duration-300 lg:translate-x-0 lg:static lg:inset-0 shadow-xl lg:shadow-none h-full"
               :class="{
                   'translate-x-0 pointer-events-auto': sidebarOpen, 
                   '-translate-x-full max-lg:pointer-events-none': !sidebarOpen,
                   'w-72 lg:w-72': !isCollapsed,
                   'w-72 lg:w-20': isCollapsed
               }">
            
            <!-- Sidebar Header (Brand Gradient) -->
            <div class="flex items-center justify-between h-20 bg-gradient-to-r from-brand-primary to-brand-secondary text-white shadow-sm transition-all duration-300"
                 :class="isCollapsed ? 'px-3 justify-center' : 'px-6'">
                <div class="flex items-center gap-3 overflow-hidden">
                    @if($sekolah?->school_logo_path)
                        <div class="h-10 w-10 min-w-10 bg-white p-1 rounded-xl shadow-md flex items-center justify-center">
                            <img src="{{ asset('storage/' . $sekolah->school_logo_path) }}" alt="Logo" class="h-full w-full object-contain">
                        </div>
                    @else
                        <div class="h-10 w-10 min-w-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-white border border-white/30 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                    @endif
                    <div class="flex flex-col" x-show="!isCollapsed" x-transition.opacity>
                        <span class="font-extrabold text-white text-base leading-tight truncate w-36 tracking-tight">{{ $sekolah?->school_name ?? 'ERP Sekolah' }}</span>
                        <span class="text-[10px] text-indigo-100 font-bold tracking-widest uppercase opacity-90 truncate">Portal {{ $userRole }}</span>
                    </div>
                </div>

                <!-- Mobile close button -->
                <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Sidebar Navigation -->
            <div class="flex-1 overflow-y-auto py-6 space-y-1.5 bg-white transition-all duration-300" :class="isCollapsed ? 'px-2' : 'px-4'">
                
                @if($isPerpusRoute)
                    @php
                        $isDashboard = request()->routeIs('portal-perpustakaan.dashboard');
                        $isBuku = request()->routeIs('portal-perpustakaan.buku');
                        $isRiwayatHapusBuku = request()->routeIs('portal-perpustakaan.riwayat-hapus-buku');
                        $isInventaris = request()->routeIs('portal-perpustakaan.inventaris');
                        $isSirkulasi = request()->routeIs('portal-perpustakaan.sirkulasi') || request()->routeIs('portal-perpustakaan.sirkulasi-kiosk');
                        $isPeminjaman = request()->routeIs('portal-perpustakaan.peminjaman');
                        $isKunjungan = request()->routeIs('portal-perpustakaan.kunjungan');
                        $isCetakKartu = request()->routeIs('portal-perpustakaan.cetak-kartu');
                        $isKlasifikasi = request()->routeIs('portal-perpustakaan.klasifikasi-ddc');
                    @endphp

                    <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 truncate" x-show="!isCollapsed" x-transition.opacity>Modul Perpustakaan</p>

                    <!-- Menu Dashboard -->
                    <a href="{{ route('portal-perpustakaan.dashboard') }}" 
                       :title="isCollapsed ? 'Dashboard Perpustakaan' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboard ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDashboard ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Dashboard</span>
                    </a>

                    <!-- Menu Katalog & Input Buku -->
                    <a href="{{ route('portal-perpustakaan.buku') }}" 
                       :title="isCollapsed ? 'Katalog & Input Buku' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isBuku ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isBuku ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Katalog & Input Buku</span>
                    </a>

                    <!-- Menu Riwayat Hapus Buku -->
                    <a href="{{ route('portal-perpustakaan.riwayat-hapus-buku') }}"
                       :title="isCollapsed ? 'Riwayat Hapus Buku' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isRiwayatHapusBuku ? 'bg-rose-600 text-white font-bold shadow-lg shadow-rose-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-rose-700 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isRiwayatHapusBuku ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-rose-100 group-hover:text-rose-600' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Riwayat Hapus Buku</span>
                    </a>

                    <!-- Menu Klasifikasi DDC -->
                    <a href="{{ route('portal-perpustakaan.klasifikasi-ddc') }}"
                       :title="isCollapsed ? 'Klasifikasi DDC' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isKlasifikasi ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isKlasifikasi ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Klasifikasi DDC</span>
                    </a>

                    <!-- Menu Inventaris Buku -->
                    <a href="{{ route('portal-perpustakaan.inventaris') }}" 
                       :title="isCollapsed ? 'Inventaris Buku' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isInventaris ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isInventaris ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Inventaris Buku</span>
                    </a>

                    <!-- Menu Sirkulasi Peminjaman -->
                    <a href="{{ route('portal-perpustakaan.sirkulasi') }}" 
                       :title="isCollapsed ? 'Sirkulasi & Peminjaman' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isSirkulasi ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isSirkulasi ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Sirkulasi</span>
                    </a>

                    <!-- Menu Peminjaman -->
                    <a href="{{ route('portal-perpustakaan.peminjaman') }}"
                       :title="isCollapsed ? 'Data Peminjaman' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPeminjaman ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPeminjaman ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Peminjaman</span>
                    </a>

                    <!-- Menu Presensi Kunjungan -->
                    <a href="{{ route('portal-perpustakaan.kunjungan') }}" 
                       :title="isCollapsed ? 'Riwayat Presensi Kunjungan' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isKunjungan ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isKunjungan ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Riwayat Presensi</span>
                    </a>

                    <!-- Cetak Kartu -->
                    <a href="{{ route('portal-perpustakaan.cetak-kartu') }}" 
                       :title="isCollapsed ? 'Cetak Kartu Siswa' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isCetakKartu ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isCetakKartu ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Cetak Kartu</span>
                    </a>

                    <!-- Section Pintasan Portal Lain (Kondisional) -->
                    @if($user && ($user->hasRole(['wali_kelas', 'admin_portal_presensi', 'super_admin'])))
                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 truncate" x-show="!isCollapsed" x-transition.opacity>Pintasan Portal Terkait</p>

                            @if($user->hasRole(['wali_kelas', 'super_admin']))
                                <a href="{{ route('portal-guru.dashboard') }}" :title="isCollapsed ? 'Portal Guru' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-indigo-100 text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Portal Guru</span>
                                </a>
                            @endif

                            @if($user->hasRole(['admin_portal_presensi', 'super_admin']))
                                <a href="{{ route('portal-presensi.dashboard') }}" :title="isCollapsed ? 'Portal Presensi' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-amber-50 hover:text-amber-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-amber-100 text-amber-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Portal Presensi</span>
                                </a>
                            @endif

                            <a href="{{ route('portal-perpustakaan.sirkulasi-kiosk') }}" target="_blank" :title="isCollapsed ? 'Sirkulasi Kiosk' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 text-xs font-semibold transition-all">
                                <div class="p-1 rounded-lg bg-cyan-100 text-cyan-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg></div>
                                <span x-show="!isCollapsed" x-transition.opacity>Sirkulasi Kiosk</span>
                            </a>

                            @if($user->hasRole('super_admin'))
                                <a href="{{ url('/admin') }}" :title="isCollapsed ? 'Panel Admin' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-purple-50 hover:text-purple-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-purple-100 text-purple-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Panel Admin</span>
                                </a>
                            @endif
                        </div>
                    @endif

                @elseif($isPresensiRoute)
                    @php
                        $isDashboard = request()->routeIs('portal-presensi.dashboard');
                        $isInputManual = request()->routeIs('portal-presensi.input-manual');
                        $isRekapKelas = request()->routeIs('portal-presensi.rekap-kelas');
                        $isRekapSekolah = request()->routeIs('portal-presensi.rekap-sekolah');
                        $isCetakLaporan = request()->routeIs('portal-presensi.cetak-laporan');
                        $isCetakKartu = request()->routeIs('portal-presensi.cetak-kartu');
                        $isSettingNotifikasi = request()->routeIs('portal-presensi.setting-notifikasi');
                    @endphp

                    <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 truncate" x-show="!isCollapsed" x-transition.opacity>Modul Presensi</p>

                    <!-- Menu Dashboard -->
                    <a href="{{ route('portal-presensi.dashboard') }}" 
                       :title="isCollapsed ? 'Dashboard Presensi' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboard ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDashboard ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Dashboard Utama</span>
                    </a>

                    <!-- Menu Input Presensi Manual -->
                    <a href="{{ route('portal-presensi.input-manual') }}" 
                       :title="isCollapsed ? 'Input Presensi Manual' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isInputManual ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isInputManual ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Input Manual</span>
                    </a>

                    <!-- Menu Rekap Presensi Kelas -->
                    <a href="{{ route('portal-presensi.rekap-kelas') }}" 
                       :title="isCollapsed ? 'Rekap Presensi Kelas' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isRekapKelas ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isRekapKelas ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Rekap Kelas</span>
                    </a>

                    <!-- Menu Rekap Presensi Sekolah -->
                    <a href="{{ route('portal-presensi.rekap-sekolah') }}" 
                       :title="isCollapsed ? 'Rekap Presensi Sekolah' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isRekapSekolah ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isRekapSekolah ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Rekap Sekolah</span>
                    </a>

                    <!-- Menu Cetak Laporan -->
                    <a href="{{ route('portal-presensi.cetak-laporan') }}" 
                       :title="isCollapsed ? 'Cetak Laporan Presensi' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isCetakLaporan ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isCetakLaporan ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Cetak Laporan</span>
                    </a>

                    <!-- Menu Cetak Kartu -->
                    <a href="{{ route('portal-presensi.cetak-kartu') }}" 
                       :title="isCollapsed ? 'Cetak Kartu Siswa' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isCetakKartu ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isCetakKartu ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Cetak Kartu</span>
                    </a>

                    <!-- Menu Setting Notifikasi -->
                    <a href="{{ route('portal-presensi.setting-notifikasi') }}" 
                       :title="isCollapsed ? 'Setting Notifikasi WA' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isSettingNotifikasi ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isSettingNotifikasi ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Setting Notifikasi WA</span>
                    </a>

                    <!-- Section Pintasan Portal Lain (Kondisional) -->
                    @if($user && ($user->hasRole(['wali_kelas', 'petugas_perpustakaan', 'admin_perpustakaan', 'super_admin'])))
                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 truncate" x-show="!isCollapsed" x-transition.opacity>Pintasan Portal Terkait</p>

                            @if($user->hasRole(['wali_kelas', 'super_admin']))
                                <a href="{{ route('portal-guru.dashboard') }}" :title="isCollapsed ? 'Portal Guru' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-indigo-100 text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Portal Guru</span>
                                </a>
                            @endif

                            @if($user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan', 'super_admin']))
                                <a href="{{ route('portal-perpustakaan.dashboard') }}" :title="isCollapsed ? 'Portal Perpustakaan' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-cyan-100 text-cyan-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Portal Perpustakaan</span>
                                </a>
                            @endif

                            <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" target="_blank" :title="isCollapsed ? 'Kiosk Scan' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-blue-50 hover:text-blue-700 text-xs font-semibold transition-all">
                                <div class="p-1 rounded-lg bg-blue-100 text-blue-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg></div>
                                <span x-show="!isCollapsed" x-transition.opacity>Kiosk Scan</span>
                            </a>

                            @if($user->hasRole('super_admin'))
                                <a href="{{ url('/admin') }}" :title="isCollapsed ? 'Panel Admin' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-purple-50 hover:text-purple-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-purple-100 text-purple-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Panel Admin</span>
                                </a>
                            @endif
                        </div>
                    @endif

                @elseif($user && $user->hasRole('siswa'))
                    @php
                        $isDashboardActive = request()->routeIs('portal-siswa.dashboard');
                        $isAkademikActive = request()->routeIs('portal-siswa.akademik');
                        $isIjinKehadiranActive = request()->routeIs('portal-siswa.ijin') || request()->routeIs('portal-siswa.ijin.form');
                        $isPerpustakaanActive = request()->routeIs('portal-siswa.perpustakaan');
                        $isCetakKartuActive = request()->routeIs('portal-siswa.cetak-kartu');
                        $isProfilActive = request()->routeIs('portal-siswa.profil');
                    @endphp

                    <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 truncate" x-show="!isCollapsed" x-transition.opacity>Modul Siswa</p>

                    <!-- Menu Dashboard Utama -->
                    <a href="{{ route('portal-siswa.dashboard') }}" :title="isCollapsed ? 'Dashboard Utama' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Dashboard Utama</span>
                    </a>

                    <!-- Menu Presensi & Akademik -->
                    <a href="{{ route('portal-siswa.akademik') }}" :title="isCollapsed ? 'Presensi & Akademik' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isAkademikActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isAkademikActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Presensi & Akademik</span>
                    </a>

                    <!-- Menu Pengajuan Ijin -->
                    <a href="{{ route('portal-siswa.ijin') }}" :title="isCollapsed ? 'Pengajuan Ijin' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isIjinKehadiranActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isIjinKehadiranActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Pengajuan Ijin</span>
                    </a>

                    <!-- Menu Perpustakaan -->
                    <a href="{{ route('portal-siswa.perpustakaan') }}" :title="isCollapsed ? 'Perpustakaan' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPerpustakaanActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPerpustakaanActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Perpustakaan</span>
                    </a>

                    <!-- Menu Cetak Kartu Siswa Mandiri -->
                    <a href="{{ route('portal-siswa.cetak-kartu') }}" target="_blank" :title="isCollapsed ? 'Cetak Kartu Siswa' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isCetakKartuActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isCetakKartuActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Cetak Kartu Saya</span>
                    </a>

                    <!-- Menu Profil Saya -->
                    <a href="{{ route('portal-siswa.profil') }}" :title="isCollapsed ? 'Profil Saya' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isProfilActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isProfilActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Profil Saya</span>
                    </a>
                @else
                    @php
                        $isDashboardActive = request()->routeIs('portal-guru.dashboard');
                        $isAkademikActive = request()->routeIs('portal-guru.akademik') || request()->routeIs('portal-guru.student-detail');
                        $isPerpustakaanActive = request()->routeIs('portal-guru.perpustakaan');
                        $isDataSiswaActive = request()->routeIs('portal-guru.data-siswa');
                        $isIjinKehadiranActive = request()->routeIs('portal-guru.ijin') || request()->routeIs('portal-guru.ijin.detail');
                    @endphp
                    
                    <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 truncate" x-show="!isCollapsed" x-transition.opacity>Modul Guru</p>

                    <!-- Menu Dashboard Utama -->
                    <a href="{{ route('portal-guru.dashboard') }}" :title="isCollapsed ? 'Dashboard' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Dashboard Utama</span>
                    </a>

                    <!-- Menu Presensi & Akademik -->
                    <a href="{{ route('portal-guru.akademik') }}" :title="isCollapsed ? 'Presensi & Akademik' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isAkademikActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isAkademikActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Presensi & Akademik</span>
                    </a>

                    <!-- Menu Data Siswa -->
                    <a href="{{ route('portal-guru.data-siswa') }}" :title="isCollapsed ? 'Data Siswa' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDataSiswaActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDataSiswaActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Data Siswa</span>
                    </a>

                    <!-- Menu Persetujuan Ijin -->
                    <a href="{{ route('portal-guru.ijin') }}" :title="isCollapsed ? 'Persetujuan Ijin' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isIjinKehadiranActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isIjinKehadiranActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Persetujuan Ijin</span>
                    </a>

                    <!-- Menu Perpustakaan Guru -->
                    <a href="{{ route('portal-guru.perpustakaan') }}" :title="isCollapsed ? 'Perpustakaan' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPerpustakaanActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPerpustakaanActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Perpustakaan</span>
                    </a>

                    @php
                        $isProfilGuruActive = request()->routeIs('portal-guru.profil');
                    @endphp

                    <!-- Menu Profil Saya (Guru) -->
                    <a href="{{ route('portal-guru.profil') }}" :title="isCollapsed ? 'Profil Saya' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isProfilGuruActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isProfilGuruActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Profil Saya</span>
                    </a>

                    <!-- Section Akses Portal Terkait (Jika Punya Role Lain) -->
                    @if($user && ($user->hasRole(['admin_portal_presensi', 'petugas_presensi', 'petugas_perpustakaan', 'admin_perpustakaan', 'super_admin', 'wali_kelas']) || $user->teacher))
                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 truncate" x-show="!isCollapsed" x-transition.opacity>Akses Portal Terkait</p>

                            @if($user->hasRole(['admin_portal_presensi', 'petugas_presensi', 'super_admin']))
                                <a href="{{ route('portal-presensi.dashboard') }}" :title="isCollapsed ? 'Portal Presensi' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-amber-50 hover:text-amber-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-amber-100 text-amber-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Portal Presensi</span>
                                </a>
                            @endif

                            @if($user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan', 'super_admin']))
                                <a href="{{ route('portal-perpustakaan.dashboard') }}" :title="isCollapsed ? 'Portal Perpustakaan' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-cyan-100 text-cyan-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Portal Perpustakaan</span>
                                </a>
                            @endif

                            @if($user->hasRole(['petugas_presensi', 'admin_portal_presensi', 'super_admin', 'wali_kelas']) || $user->teacher)
                                <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" target="_blank" :title="isCollapsed ? 'Kiosk Scan' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-blue-50 hover:text-blue-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-blue-100 text-blue-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Kiosk Scan</span>
                                </a>
                            @endif

                            @if($user->hasRole('super_admin'))
                                <a href="{{ url('/admin') }}" :title="isCollapsed ? 'Panel Admin' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-purple-50 hover:text-purple-700 text-xs font-semibold transition-all">
                                    <div class="p-1 rounded-lg bg-purple-100 text-purple-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div>
                                    <span x-show="!isCollapsed" x-transition.opacity>Panel Admin</span>
                                </a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>

            <!-- Sidebar Footer (User Info & Logout) -->
            <div class="p-4 border-t border-slate-200/80 bg-slate-50 transition-all duration-300 flex-shrink-0" style="padding-bottom: max(1rem, env(safe-area-inset-bottom, 1rem));">
                <div class="flex items-center gap-3 mb-3" :class="isCollapsed ? 'justify-center px-0' : 'px-1'">
                    @if($user && $user->hasRole('siswa') && $user->student?->photo_path)
                        <img src="{{ asset('storage/' . $user->student->photo_path) }}" alt="{{ $userName }}" class="w-9 h-9 rounded-xl object-cover shadow-md shadow-brand-primary/20 border border-slate-200 min-w-9 flex-shrink-0">
                    @elseif($user && $user->teacher?->photo_path)
                        <img src="{{ asset('storage/' . $user->teacher->photo_path) }}" alt="{{ $userName }}" class="w-9 h-9 rounded-xl object-cover shadow-md shadow-brand-primary/20 border border-slate-200 min-w-9 flex-shrink-0">
                    @else
                        <div class="w-9 h-9 min-w-9 flex-shrink-0 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white font-bold shadow-md shadow-brand-primary/20 text-sm">
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex flex-col overflow-hidden min-w-0" x-show="!isCollapsed" x-transition.opacity>
                        <span class="text-sm font-bold text-slate-900 truncate leading-tight">{{ $userName }}</span>
                        <span class="text-xs text-slate-500 truncate leading-tight">{{ $userRole }}</span>
                    </div>
                </div>
                <form action="{{ $logoutRoute }}" method="POST">
                    @csrf
                    <button type="submit" :title="isCollapsed ? 'Keluar Portal' : ''" class="w-full flex items-center justify-center gap-2 py-2 bg-white hover:bg-rose-50 hover:text-rose-600 border border-slate-200 hover:border-rose-200 rounded-xl text-xs font-bold text-slate-700 transition-all shadow-sm" :class="isCollapsed ? 'px-0' : 'px-3'">
                        <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Keluar Portal</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-y-auto" style="min-height: 0; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch;">
            
            <!-- Topbar Header with Single Garis Tiga Toggle Icon -->
            <div class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-white border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3">
                    <!-- Single Icon Garis Tiga Toggle Button -->
                    <button @click="if (window.innerWidth < 1024) { sidebarOpen = !sidebarOpen } else { sidebarCollapsed = !sidebarCollapsed }" 
                            class="text-slate-600 hover:text-brand-primary focus:outline-none p-2 rounded-xl hover:bg-slate-100 transition-colors" 
                            :title="isCollapsed ? 'Perluas Sidebar' : 'Kecilkan Sidebar'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                </div>

                <!-- User Profile & Logout Dropdown -->
                <div class="relative" x-data="{ userMenuOpen: false }" @click.away="userMenuOpen = false">
                    <button @click="userMenuOpen = !userMenuOpen" 
                            class="flex items-center gap-3 py-1.5 px-3 rounded-2xl border border-slate-200/80 hover:border-brand-primary/40 hover:bg-slate-50 transition-all focus:outline-none shadow-xs group">
                        @if($user && $user->hasRole('siswa') && $user->student?->photo_path)
                            <img src="{{ asset('storage/' . $user->student->photo_path) }}" alt="{{ $userName }}" class="w-8 h-8 rounded-xl object-cover border border-slate-200 shadow-xs">
                        @elseif($user && $user->teacher?->photo_path)
                            <img src="{{ asset('storage/' . $user->teacher->photo_path) }}" alt="{{ $userName }}" class="w-8 h-8 rounded-xl object-cover border border-slate-200 shadow-xs">
                        @else
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            </div>
                        @endif
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-bold text-slate-800 leading-snug group-hover:text-brand-primary transition-colors">{{ $userName }}</span>
                            <span class="text-[10px] text-slate-500 font-medium leading-none">{{ $userRole }}</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="userMenuOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200/80 py-2 z-50 overflow-hidden" 
                         style="display: none;">
                        
                        <!-- User Info Header -->
                        <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-100 flex items-center gap-3">
                            @if($user && $user->hasRole('siswa') && $user->student?->photo_path)
                                <img src="{{ asset('storage/' . $user->student->photo_path) }}" alt="{{ $userName }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-xs">
                            @elseif($user && $user->teacher?->photo_path)
                                <img src="{{ asset('storage/' . $user->teacher->photo_path) }}" alt="{{ $userName }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-xs">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary text-white font-bold text-sm flex items-center justify-center shadow-xs">
                                    {{ strtoupper(substr($userName, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold text-slate-900 truncate">{{ $userName }}</span>
                                <span class="text-xs text-slate-500 font-medium truncate">{{ $userRole }}</span>
                            </div>
                        </div>

                        <!-- Halaman Utama Link -->
                        <div class="px-3 pt-3 pb-1">
                            <a href="{{ url('/') }}" class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-50 hover:bg-brand-primary/10 border border-slate-100 hover:border-brand-primary/30 transition-colors group">
                                <div class="p-1.5 bg-white rounded-lg shadow-xs group-hover:bg-brand-primary group-hover:text-white text-slate-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-800 group-hover:text-brand-primary transition-colors">Beranda</span>
                                    <span class="text-[10px] text-slate-500">Kembali ke halaman utama</span>
                                </div>
                            </a>
                        </div>
                        
                        <div class="border-t border-slate-100 mx-3 my-1"></div>

                        <!-- Menu Navigation Items (Static) -->
                        <div class="px-3 py-2">
                            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider mb-1.5 px-1">Pintasan Akses</p>
                            <div class="max-h-60 overflow-y-auto space-y-1 pr-0.5">
                                
                                @if($user && $user->hasRole('siswa'))
                                    <a href="{{ route('portal-siswa.dashboard') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-blue-100 hover:bg-blue-50/50 text-xs font-semibold text-slate-700 hover:text-blue-700 transition-all">
                                        <div class="p-1.5 rounded-lg bg-blue-100 text-blue-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                        </div>
                                        <span>Dashboard Siswa</span>
                                    </a>

                                    <a href="{{ route('portal-siswa.profil') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-indigo-100 hover:bg-indigo-50/50 text-xs font-semibold text-slate-700 hover:text-indigo-700 transition-all">
                                        <div class="p-1.5 rounded-lg bg-indigo-100 text-indigo-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <span>Profil Saya</span>
                                    </a>

                                    <a href="{{ route('portal-siswa.cetak-kartu') }}" target="_blank" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-violet-100 hover:bg-violet-50/50 text-xs font-semibold text-slate-700 hover:text-violet-700 transition-all">
                                        <div class="p-1.5 rounded-lg bg-violet-100 text-violet-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                                        </div>
                                        <span>Cetak Kartu Saya</span>
                                    </a>
                                @else
                                    @if($user && ($user->hasRole(['wali_kelas', 'super_admin']) || $user->teacher))
                                        <a href="{{ route('portal-guru.dashboard') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-indigo-100 hover:bg-indigo-50/50 text-xs font-semibold text-slate-700 hover:text-indigo-700 transition-all">
                                            <div class="p-1.5 rounded-lg bg-indigo-100 text-indigo-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                            </div>
                                            <span>Dashboard Guru</span>
                                        </a>

                                        <a href="{{ route('portal-guru.profil') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-indigo-100 hover:bg-indigo-50/50 text-xs font-semibold text-slate-700 hover:text-indigo-700 transition-all">
                                            <div class="p-1.5 rounded-lg bg-indigo-100 text-indigo-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            </div>
                                            <span>Profil Saya</span>
                                        </a>
                                    @endif

                                    @if($user && $user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan', 'super_admin']))
                                        <a href="{{ url('/portal-perpustakaan') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-emerald-100 hover:bg-emerald-50/50 text-xs font-semibold text-slate-700 hover:text-emerald-700 transition-all">
                                            <div class="p-1.5 rounded-lg bg-emerald-100 text-emerald-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                            </div>
                                            <span>Portal Perpustakaan</span>
                                        </a>
                                    @endif

                                    @if($user && $user->hasRole(['admin_portal_presensi', 'super_admin']))
                                        <a href="{{ url('/portal-presensi') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-amber-100 hover:bg-amber-50/50 text-xs font-semibold text-slate-700 hover:text-amber-700 transition-all">
                                            <div class="p-1.5 rounded-lg bg-amber-100 text-amber-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <span>Portal Presensi</span>
                                        </a>
                                        
                                        <div class="border-t border-slate-100 my-1 mx-2"></div>
                                    @endif

                                    @if($user && ($user->hasRole(['petugas_presensi', 'admin_portal_presensi', 'super_admin', 'wali_kelas']) || $user->teacher))
                                        <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" target="_blank" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-blue-100 hover:bg-blue-50/50 text-xs font-semibold text-slate-700 hover:text-blue-700 transition-all">
                                            <div class="p-1.5 rounded-lg bg-blue-100 text-blue-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                            </div>
                                            <span>Presensi Digital</span>
                                        </a>
                                    @endif

                                    @if($user && $user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan', 'super_admin']))
                                        <a href="{{ route('perpustakaan.kunjungan') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-purple-100 hover:bg-purple-50/50 text-xs font-semibold text-slate-700 hover:text-purple-700 transition-all">
                                            <div class="p-1.5 rounded-lg bg-purple-100 text-purple-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                            </div>
                                            <span>Kunjungan Perpustakaan</span>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Logout Section -->
                        <div class="pt-1 mt-1 border-t border-slate-100 px-1.5">
                            <form action="{{ $logoutRoute }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Keluar Portal
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @include('components.announcement-banner')

            <!-- Page Content -->
            <main class="flex-1 relative p-4 sm:p-6 pb-12 sm:pb-16">
                {{ $slot }}
            </main>
        </div>

    </body>
</html>
