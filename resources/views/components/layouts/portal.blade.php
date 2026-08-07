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
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
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
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50 flex h-screen overflow-hidden [touch-action:pan-y]" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('portal_sidebar_collapsed') === 'true' }" x-init="$watch('sidebarCollapsed', val => localStorage.setItem('portal_sidebar_collapsed', val))">
        
        @php
            $user = Auth::guard('web')->user();
            $isPerpusRoute = request()->is('portal-perpustakaan*');

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
        <aside class="fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200/80 flex flex-col transform transition-all duration-300 lg:translate-x-0 lg:static lg:inset-0 shadow-xl lg:shadow-none"
               :class="{
                   'translate-x-0 pointer-events-auto': sidebarOpen, 
                   '-translate-x-full pointer-events-none lg:pointer-events-auto': !sidebarOpen,
                   'w-72 lg:w-72': !sidebarCollapsed,
                   'w-72 lg:w-20': sidebarCollapsed
               }">
            
            <!-- Sidebar Header (Brand Gradient) -->
            <div class="flex items-center justify-between h-20 bg-gradient-to-r from-brand-primary to-brand-secondary text-white shadow-sm transition-all duration-300"
                 :class="sidebarCollapsed ? 'px-3 justify-center' : 'px-6'">
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
                    <div class="flex flex-col" x-show="!sidebarCollapsed" x-transition.opacity>
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
            <div class="flex-1 overflow-y-auto py-6 space-y-1.5 bg-white transition-all duration-300" :class="sidebarCollapsed ? 'px-2' : 'px-4'">
                <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 truncate" x-show="!sidebarCollapsed" x-transition.opacity>Modul ERP</p>
                
                @if($isPerpusRoute)
                    @php
                        $isDashboard = request()->routeIs('portal-perpustakaan.dashboard');
                        $isBuku = request()->routeIs('portal-perpustakaan.buku');
                        $isInventaris = request()->routeIs('portal-perpustakaan.inventaris');
                        $isSirkulasi = request()->routeIs('portal-perpustakaan.sirkulasi');
                        $isPeminjaman = request()->routeIs('portal-perpustakaan.peminjaman');
                        $isKunjungan = request()->routeIs('portal-perpustakaan.kunjungan');
                    @endphp

                    <!-- Menu Dashboard -->
                    <a href="{{ route('portal-perpustakaan.dashboard') }}" 
                       :title="sidebarCollapsed ? 'Dashboard Perpustakaan' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboard ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDashboard ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Dashboard</span>
                    </a>

                    <!-- Menu Katalog & Input Buku -->
                    <a href="{{ route('portal-perpustakaan.buku') }}" 
                       :title="sidebarCollapsed ? 'Katalog & Input Buku' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isBuku ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isBuku ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Katalog & Input Buku</span>
                    </a>

                    <!-- Menu Inventaris Buku -->
                    <a href="{{ route('portal-perpustakaan.inventaris') }}" 
                       :title="sidebarCollapsed ? 'Inventaris Buku' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isInventaris ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isInventaris ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Inventaris Buku</span>
                    </a>

                    <!-- Menu Sirkulasi Peminjaman -->
                    <a href="{{ route('portal-perpustakaan.sirkulasi') }}" 
                       :title="sidebarCollapsed ? 'Sirkulasi & Peminjaman' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isSirkulasi ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isSirkulasi ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Sirkulasi</span>
                    </a>

                    <!-- Menu Peminjaman -->
                    <a href="{{ route('portal-perpustakaan.peminjaman') }}"
                       :title="sidebarCollapsed ? 'Data Peminjaman' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPeminjaman ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPeminjaman ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Peminjaman</span>
                    </a>

                    <!-- Menu Presensi Kunjungan -->
                    <a href="{{ route('portal-perpustakaan.kunjungan') }}" 
                       :title="sidebarCollapsed ? 'Riwayat Presensi Kunjungan' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isKunjungan ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isKunjungan ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Riwayat Presensi</span>
                    </a>
                @elseif($user && $user->hasRole('siswa'))
                    @php
                        $isProfilActive = request()->routeIs('portal-siswa.profil');
                        $isDashboardActive = request()->routeIs('portal-siswa.dashboard');
                        $isAkademikActive = request()->routeIs('portal-siswa.akademik');
                        $isPerpustakaanActive = request()->routeIs('portal-siswa.perpustakaan');
                    @endphp
                    
                    <a href="{{ route('portal-siswa.dashboard') }}" :title="sidebarCollapsed ? 'Dashboard Utama' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Dashboard Utama</span>
                    </a>

                    <a href="{{ route('portal-siswa.akademik') }}" :title="sidebarCollapsed ? 'Presensi & Akademik' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isAkademikActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isAkademikActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Presensi & Akademik</span>
                    </a>

                    <a href="{{ route('portal-siswa.perpustakaan') }}" :title="sidebarCollapsed ? 'Perpustakaan' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPerpustakaanActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPerpustakaanActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Perpustakaan</span>
                    </a>

                    <a href="{{ route('portal-siswa.profil') }}" :title="sidebarCollapsed ? 'Profil Saya' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isProfilActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isProfilActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Profil Saya</span>
                    </a>
                @else
                    @php
                        $isDashboardActive = request()->routeIs('portal-guru.dashboard');
                        $isAkademikActive = request()->routeIs('portal-guru.akademik') || request()->routeIs('portal-guru.student-detail');
                        $isPerpustakaanActive = request()->routeIs('portal-guru.perpustakaan');
                    @endphp
                    
                    <a href="{{ route('portal-guru.dashboard') }}" :title="sidebarCollapsed ? 'Dashboard' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Dashboard Utama</span>
                    </a>

                    <a href="{{ route('portal-guru.akademik') }}" :title="sidebarCollapsed ? 'Presensi & Akademik' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isAkademikActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isAkademikActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Presensi & Akademik</span>
                    </a>

                    <a href="{{ route('portal-guru.perpustakaan') }}" :title="sidebarCollapsed ? 'Perpustakaan' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPerpustakaanActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPerpustakaanActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!sidebarCollapsed" x-transition.opacity>Perpustakaan</span>
                    </a>
                @endif
            </div>

            <!-- Sidebar Footer (User Info & Logout) -->
            <div class="p-4 border-t border-slate-200/80 bg-slate-50 transition-all duration-300">
                <div class="flex items-center gap-3 mb-4" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-2'">
                    @if($user && $user->hasRole('siswa') && $user->student?->photo_path)
                        <img src="{{ asset('storage/' . $user->student->photo_path) }}" alt="{{ $userName }}" class="w-10 h-10 rounded-xl object-cover shadow-md shadow-brand-primary/20 border border-slate-200 min-w-10">
                    @else
                        <div class="w-10 h-10 min-w-10 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white font-bold shadow-md shadow-brand-primary/20">
                            {{ substr($userName, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex flex-col overflow-hidden" x-show="!sidebarCollapsed" x-transition.opacity>
                        <span class="text-sm font-bold text-slate-900 truncate">{{ $userName }}</span>
                        <span class="text-xs text-slate-500 truncate">{{ $userRole }}</span>
                    </div>
                </div>
                <form action="{{ $logoutRoute }}" method="POST">
                    @csrf
                    <button type="submit" :title="sidebarCollapsed ? 'Keluar Portal' : ''" class="w-full flex items-center justify-center gap-2 py-2.5 bg-white hover:bg-rose-50 hover:text-rose-600 border border-slate-200 hover:border-rose-200 rounded-xl text-xs font-bold text-slate-700 transition-all shadow-sm" :class="sidebarCollapsed ? 'px-0' : 'px-4'">
                        <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Keluar Portal</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-y-auto min-h-0 [touch-action:pan-y] [-webkit-overflow-scrolling:touch] overscroll-y-auto">
            
            <!-- Topbar Header with Single Garis Tiga Toggle Icon -->
            <div class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-white border-b border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3">
                    <!-- Single Icon Garis Tiga Toggle Button -->
                    <button @click="if (window.innerWidth < 1024) { sidebarOpen = !sidebarOpen } else { sidebarCollapsed = !sidebarCollapsed }" 
                            class="text-slate-600 hover:text-brand-primary focus:outline-none p-2 rounded-xl hover:bg-slate-100 transition-colors" 
                            :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Kecilkan Sidebar'">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                </div>

                <!-- User Profile & Logout Dropdown -->
                <div class="relative" x-data="{ userMenuOpen: false }" @click.away="userMenuOpen = false">
                    <button @click="userMenuOpen = !userMenuOpen" 
                            class="flex items-center gap-3 py-1.5 px-3 rounded-2xl border border-slate-200/80 hover:border-brand-primary/40 hover:bg-slate-50 transition-all focus:outline-none shadow-xs group">
                        @if($user && $user->hasRole('siswa') && $user->student?->photo_path)
                            <img src="{{ asset('storage/' . $user->student->photo_path) }}" alt="{{ $userName }}" class="w-8 h-8 rounded-xl object-cover border border-slate-200 shadow-xs">
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

                        <!-- Menu Navigation Items -->
                        <div class="py-1 px-1.5 space-y-0.5">
                            @if($user && $user->hasRole('siswa'))
                                <a href="{{ route('portal-siswa.profil') }}" @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-brand-primary/10 hover:text-brand-primary transition-colors">
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    Profil Saya
                                </a>
                                <a href="{{ route('portal-siswa.dashboard') }}" @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-brand-primary/10 hover:text-brand-primary transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                    Dashboard
                                </a>
                            @elseif($user && $user->hasRole('wali_kelas') && !$isPerpusRoute)
                                <a href="{{ route('portal-guru.dashboard') }}" @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-brand-primary/10 hover:text-brand-primary transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                    Dashboard Utama
                                </a>
                                <a href="{{ route('portal-guru.akademik') }}" @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-brand-primary/10 hover:text-brand-primary transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Presensi & Akademik
                                </a>
                                <a href="{{ route('portal-guru.perpustakaan') }}" @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-brand-primary/10 hover:text-brand-primary transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                    Perpustakaan
                                </a>
                            @elseif($isPerpusRoute || ($user && ($user->hasRole('petugas_perpustakaan') || $user->hasRole('admin_perpustakaan'))))
                                <a href="{{ route('portal-perpustakaan.dashboard') }}" @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-brand-primary/10 hover:text-brand-primary transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                    Dashboard Perpustakaan
                                </a>
                            @endif
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
            <main class="flex-1 relative">
                {{ $slot }}
            </main>
        </div>

    </body>
</html>
