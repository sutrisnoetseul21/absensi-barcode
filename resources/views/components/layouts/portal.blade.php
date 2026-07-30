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
    <body class="font-sans antialiased text-gray-900 bg-gray-50 flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
        
        @php
            $user = Auth::guard('web')->user();
            if($user && $user->hasRole('siswa')) {
                $logoutRoute = route('portal-siswa.logout');
                $userRole = 'Siswa';
                $userName = $user->student?->nama ?? $user->name;
                $activeDashboard = route('portal-siswa.dashboard');
            } elseif($user && $user->hasRole('wali_kelas')) {
                $logoutRoute = route('portal-guru.logout');
                $userRole = 'Guru';
                $userName = $user->teacher?->nama ?? $user->name;
                $activeDashboard = route('portal-guru.dashboard');
            } else {
                $logoutRoute = '/';
                $userRole = 'Guest';
                $userName = 'Tamu';
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
        <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200/80 flex flex-col transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0 shadow-xl lg:shadow-none"
               :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
            
            <!-- Sidebar Header (Brand Gradient) -->
            <div class="flex items-center justify-between h-20 px-6 bg-gradient-to-r from-brand-primary to-brand-secondary text-white shadow-sm">
                <div class="flex items-center gap-3">
                    @if($sekolah?->school_logo_path)
                        <div class="h-10 w-10 bg-white p-1 rounded-xl shadow-md flex items-center justify-center">
                            <img src="{{ asset('storage/' . $sekolah->school_logo_path) }}" alt="Logo" class="h-full w-full object-contain">
                        </div>
                    @else
                        <div class="h-10 w-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-white border border-white/30 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="font-extrabold text-white text-base leading-tight truncate w-40 tracking-tight">{{ $sekolah?->school_name ?? 'ERP Sekolah' }}</span>
                        <span class="text-[10px] text-indigo-100 font-bold tracking-widest uppercase opacity-90">Portal {{ $userRole }}</span>
                    </div>
                </div>
                <!-- Mobile close button -->
                <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Sidebar Navigation -->
            <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5 bg-white">
                <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">Modul ERP</p>
                
                @if($user && $user->hasRole('siswa'))
                    @php
                        $isProfilActive = request()->routeIs('portal-siswa.profil');
                        $isDashboardActive = request()->routeIs('portal-siswa.dashboard');
                    @endphp
                    <!-- Menu Presensi (Active if on dashboard) -->
                    <a href="{{ $activeDashboard }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group">
                        <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-sm">Presensi & Akademik</span>
                    </a>

                    <!-- Menu Profil Saya -->
                    <a href="{{ route('portal-siswa.profil') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl {{ $isProfilActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group">
                        <div class="p-1.5 rounded-lg {{ $isProfilActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-sm">Profil Saya</span>
                    </a>
                @else
                    <!-- Menu Presensi (Active) for Guru -->
                    <a href="{{ $activeDashboard }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30 transition-all group">
                        <div class="p-1.5 rounded-lg bg-white/20 text-white group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-sm">Presensi & Akademik</span>
                    </a>
                @endif

                <!-- Menu Perpustakaan (Segera) -->
                <a href="#" class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-all group">
                    <div class="p-1.5 rounded-lg bg-slate-100 text-slate-500 group-hover:bg-teal-100 group-hover:text-teal-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <span class="text-sm">Perpustakaan</span>
                    <span class="ml-auto bg-slate-100 text-slate-500 py-0.5 px-2 rounded-md text-[10px] font-bold border border-slate-200">Segera</span>
                </a>
            </div>

            <!-- Sidebar Footer (User Info & Logout) -->
            <div class="p-4 border-t border-slate-200/80 bg-slate-50">
                <div class="flex items-center gap-3 mb-4 px-2">
                    @if($user && $user->hasRole('siswa') && $user->student?->photo_path)
                        <img src="{{ asset('storage/' . $user->student->photo_path) }}" alt="{{ $userName }}" class="w-10 h-10 rounded-xl object-cover shadow-md shadow-brand-primary/20 border border-slate-200">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white font-bold shadow-md shadow-brand-primary/20">
                            {{ substr($userName, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex flex-col overflow-hidden">
                        <span class="text-sm font-bold text-slate-900 truncate">{{ $userName }}</span>
                        <span class="text-xs text-slate-500 truncate">Hak Akses: {{ $userRole }}</span>
                    </div>
                </div>
                <form action="{{ $logoutRoute }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-rose-50 hover:text-rose-600 border border-slate-200 hover:border-rose-200 rounded-xl text-xs font-bold text-slate-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Keluar Portal
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-y-auto">
            
            <!-- Mobile Topbar -->
            <div class="lg:hidden flex items-center justify-between h-16 px-4 bg-white border-b border-gray-200 shadow-sm sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-brand-primary focus:outline-none p-1 rounded-md hover:bg-gray-100 transition-colors">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <span class="font-bold text-gray-800">Menu Portal</span>
                </div>
                <div class="w-8 h-8 rounded-full bg-brand-primary/10 flex items-center justify-center text-brand-primary font-bold text-sm">
                    {{ substr($userName, 0, 1) }}
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 relative">
                {{ $slot }}
            </main>
        </div>

    </body>
</html>
