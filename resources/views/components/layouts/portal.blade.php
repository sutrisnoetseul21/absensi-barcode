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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
            .tox-tinymce-aux, .tox-silver-sink { z-index: 99999 !important; }
            trix-toolbar [data-trix-button-group="file-tools"] { display: none; }
            trix-editor { min-height: 150px; background: white; }
            trix-editor ul, .trix-content ul { list-style-type: disc !important; padding-left: 1.5rem !important; margin-top: 0.5rem; margin-bottom: 0.5rem; }
            trix-editor ol, .trix-content ol { list-style-type: decimal !important; padding-left: 1.5rem !important; margin-top: 0.5rem; margin-bottom: 0.5rem; }
            trix-editor a, .trix-content a { color: #2563eb; text-decoration: underline; }
            trix-editor strong, .trix-content strong { font-weight: bold; }
        </style>

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
            $isWebRoute = request()->is('portal-web*');
            $isGuruRoute = request()->is('portal-guru*');
            $isSiswaRoute = request()->is('portal-siswa*');

            $isSuper = $user && ($user->isSuperAdmin() || $user->hasRole('super_admin'));

            // Tentukan Judul Portal yang sedang aktif
            if ($isWebRoute) {
                $currentPortalTitle = 'Portal Web';
            } elseif ($isPerpusRoute) {
                $currentPortalTitle = 'Portal Perpustakaan';
            } elseif ($isPresensiRoute) {
                $currentPortalTitle = 'Portal Presensi';
            } elseif ($isGuruRoute) {
                $currentPortalTitle = 'Portal Guru';
            } elseif ($isSiswaRoute || ($user && $user->hasRole('siswa'))) {
                $currentPortalTitle = 'Portal Siswa';
            } else {
                $currentPortalTitle = 'Portal ERP';
            }

            // Tentukan Hak Akses Nyata Pengguna
            if ($user && $user->hasRole('siswa')) {
                $userRole = 'Siswa';
                $userName = $user->student?->nama ?? $user->name;
                $logoutRoute = route('portal-siswa.logout');
                $activeDashboard = route('portal-siswa.dashboard');
            } elseif ($isSuper) {
                $userRole = 'Super Admin';
                $userName = $user?->name ?? 'Admin';
                $logoutRoute = $isWebRoute ? route('portal-web.logout') : ($isPerpusRoute ? route('portal-perpustakaan.logout') : ($isPresensiRoute ? route('portal-presensi.logout') : ($isGuruRoute ? route('portal-guru.logout') : '/')));
                $activeDashboard = $isWebRoute ? route('portal-web.dashboard') : ($isPerpusRoute ? route('portal-perpustakaan.dashboard') : ($isPresensiRoute ? route('portal-presensi.dashboard') : ($isGuruRoute ? route('portal-guru.dashboard') : '/admin')));
            } elseif ($user && $user->hasRole('admin_portal_web')) {
                $userRole = 'Admin Web';
                $userName = $user?->name ?? 'Admin';
                $logoutRoute = route('portal-web.logout');
                $activeDashboard = route('portal-web.dashboard');
            } elseif ($user && ($user->hasRole(['wali_kelas', 'guru']) || $user->teacher)) {
                $userRole = 'Guru';
                $userName = $user->teacher?->nama ?? $user->name;
                $logoutRoute = route('portal-guru.logout');
                $activeDashboard = route('portal-guru.dashboard');
            } elseif ($user && ($user->hasRole('petugas_perpustakaan') || $user->hasRole('admin_perpustakaan'))) {
                $userRole = 'Petugas Perpustakaan';
                $userName = $user?->name ?? 'Petugas';
                $logoutRoute = route('portal-perpustakaan.logout');
                $activeDashboard = route('portal-perpustakaan.dashboard');
            } elseif ($user && ($user->hasRole('admin_portal_presensi') || $user->hasRole('petugas_presensi'))) {
                $userRole = 'Petugas Presensi';
                $userName = $user?->name ?? 'Admin';
                $logoutRoute = route('portal-presensi.logout') ?? '/';
                $activeDashboard = route('portal-presensi.dashboard');
            } else {
                $userRole = 'Staff';
                $userName = $user?->name ?? 'Tamu';
                $logoutRoute = '/';
                $activeDashboard = '/';
            }

            // Akses Multi Portal
            $canAccessGuru = $isSuper || ($user && ($user->hasRole(['wali_kelas', 'guru']) || $user->teacher));
            $canAccessPresensi = $isSuper || ($user && ($user->hasRole(['admin_portal_presensi', 'petugas_presensi']) || $user->roles->contains(fn($r) => str_starts_with($r->name, 'admin_presensi'))));
            $canAccessPerpus = $isSuper || ($user && ($user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan']) || $user->roles->contains(fn($r) => str_contains($r->name, 'admin_perpustakaan'))));
            $canAccessWeb = $isSuper || ($user && $user->hasRole('admin_portal_web'));
            $hasMultiplePortals = $isSuper || collect([$canAccessGuru, $canAccessPresensi, $canAccessPerpus, $canAccessWeb])->filter()->count() > 1;
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
                        <span class="text-[10px] text-white/80 font-bold tracking-widest uppercase opacity-90 truncate">{{ $currentPortalTitle }}</span>
                    </div>
                </div>

                <!-- Mobile close button -->
                <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Sidebar Navigation -->
            <div class="flex-1 overflow-y-auto py-6 space-y-1.5 bg-white transition-all duration-300" :class="isCollapsed ? 'px-2' : 'px-4'">
                
                @if($isWebRoute)
                    @php
                        $isDashboard  = request()->routeIs('portal-web.dashboard');
                        $isArtikel    = request()->routeIs('portal-web.artikel');
                        $isPrestasi   = request()->routeIs('portal-web.prestasi');
                        $isGaleri     = request()->routeIs('portal-web.galeri');
                        $isAlumni     = request()->routeIs('portal-web.alumni*');
                        $isAkademik   = request()->routeIs('portal-web.akademik*') || request()->is('admin/akademik*');
                        $isMicrosite  = request()->routeIs('portal-web.microsite*');
                        $isAksesCepat = request()->routeIs('portal-web.akses-cepat');
                        $isPelayanan  = request()->routeIs('portal-web.pelayanan.*');
                        $isSarpras    = request()->routeIs('portal-web.sarpras');
                        $isPilar      = request()->routeIs('portal-web.pilar-keunggulan');
                        $isStatistik  = request()->routeIs('portal-web.statistik');
                        $isPengaturan = request()->routeIs('portal-web.pengaturan');
                        $activeClass  = 'bg-violet-50 text-violet-700 font-bold border-r-2 border-violet-600';
                        $inactiveClass = 'text-slate-600 hover:bg-violet-50 hover:text-violet-700 font-semibold';
                    @endphp

                    {{-- SIDEBAR PORTAL WEB --}}
                    <a href="{{ route('portal-web.dashboard') }}" :title="isCollapsed ? 'Dashboard Web' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isDashboard ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Dashboard Web</span>
                    </a>

                    <a href="{{ route('portal-web.artikel') }}" :title="isCollapsed ? 'Artikel & Pengumuman' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isArtikel ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Artikel & Pengumuman</span>
                    </a>

                    <a href="{{ route('portal-web.akademik') }}" :title="isCollapsed ? 'Halaman Akademik' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isAkademik ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Halaman Akademik</span>
                    </a>

                    <a href="{{ route('portal-web.microsite') }}" :title="isCollapsed ? 'Microsite' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isMicrosite ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Microsite</span>
                    </a>

                    <a href="{{ route('portal-web.prestasi') }}" :title="isCollapsed ? 'Prestasi Sekolah' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isPrestasi ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Prestasi Sekolah</span>
                    </a>

                    <a href="{{ route('portal-web.galeri') }}" :title="isCollapsed ? 'Galeri Foto' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isGaleri ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Galeri Foto</span>
                    </a>

                    <!-- Sub Menu Data Alumni -->
                    <div x-data="{ open: {{ $isAlumni ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open" :title="isCollapsed ? 'Data Alumni' : ''"
                                class="w-full flex items-center justify-between gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isAlumni ? 'bg-slate-50 text-slate-800 font-bold' : $inactiveClass }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                <span x-show="!isCollapsed" x-transition.opacity>Data Alumni</span>
                            </div>
                            <svg x-show="!isCollapsed" :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <div x-show="open && !isCollapsed" x-collapse class="pl-11 pr-3 space-y-1 mt-1">
                            <a href="{{ route('portal-web.alumni') }}" class="block py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('portal-web.alumni') ? 'text-violet-700 font-bold' : 'text-slate-500 hover:text-violet-600' }}">Data Tracer Alumni</a>
                            <a href="{{ route('portal-web.alumni.jenjang') }}" class="block py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('portal-web.alumni.jenjang') ? 'text-violet-700 font-bold' : 'text-slate-500 hover:text-violet-600' }}">Pilihan Jenjang</a>
                            <a href="{{ route('portal-web.alumni.pengaturan') }}" class="block py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('portal-web.alumni.pengaturan') ? 'text-violet-700 font-bold' : 'text-slate-500 hover:text-violet-600' }}">Pengaturan Tracer</a>
                        </div>
                    </div>

                    <a href="{{ route('portal-web.sarpras') }}" :title="isCollapsed ? 'Sarana & Prasarana' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isSarpras ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Sarana & Prasarana</span>
                    </a>

                    <a href="{{ route('portal-web.pilar-keunggulan') }}" :title="isCollapsed ? 'Pilar Keunggulan' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isPilar ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Pilar Keunggulan</span>
                    </a>

                    <a href="{{ route('portal-web.akses-cepat') }}" :title="isCollapsed ? 'Akses Cepat' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isAksesCepat ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Akses Cepat</span>
                    </a>

                    <a href="{{ route('portal-web.statistik') }}" :title="isCollapsed ? 'Statistik & Info' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isStatistik ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Statistik & Info</span>
                    </a>

                    <!-- Sub Menu Pelayanan Publik -->
                    <div x-data="{ open: {{ $isPelayanan ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open" :title="isCollapsed ? 'Pelayanan Publik' : ''"
                                class="w-full flex items-center justify-between gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isPelayanan ? 'bg-slate-50 text-slate-800 font-bold' : $inactiveClass }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span x-show="!isCollapsed" x-transition.opacity>Pelayanan Publik</span>
                            </div>
                            <svg x-show="!isCollapsed" :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <div x-show="open && !isCollapsed" x-collapse class="pl-11 pr-3 space-y-1 mt-1">
                            <a href="{{ route('portal-web.pelayanan.halaman') }}" class="block py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('portal-web.pelayanan.halaman') ? 'text-violet-700 font-bold' : 'text-slate-500 hover:text-violet-600' }}">Halaman Layanan</a>
                            <a href="{{ route('portal-web.pelayanan.data') }}" class="block py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('portal-web.pelayanan.data') ? 'text-violet-700 font-bold' : 'text-slate-500 hover:text-violet-600' }}">Data Pengaduan</a>
                            <a href="{{ route('portal-web.pelayanan.kategori') }}" class="block py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('portal-web.pelayanan.kategori') ? 'text-violet-700 font-bold' : 'text-slate-500 hover:text-violet-600' }}">Kategori</a>
                            <a href="{{ route('portal-web.pelayanan.faq') }}" class="block py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('portal-web.pelayanan.faq') ? 'text-violet-700 font-bold' : 'text-slate-500 hover:text-violet-600' }}">FAQ Sekolah</a>
                            <a href="{{ route('portal-web.pelayanan.pengaturan') }}" class="block py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('portal-web.pelayanan.pengaturan') ? 'text-violet-700 font-bold' : 'text-slate-500 hover:text-violet-600' }}">Pengaturan Layanan</a>
                        </div>
                    </div>

                    <a href="{{ route('portal-web.pengaturan') }}" :title="isCollapsed ? 'Pengaturan Web' : ''"
                       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm transition-all {{ $isPengaturan ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-show="!isCollapsed" x-transition.opacity>Pengaturan Web</span>
                    </a>

                    @include('components.portal-sidebar-shortcuts')

                @elseif($isPerpusRoute)
                    @php
                        $isDashboard = request()->routeIs('portal-perpustakaan.dashboard');
                        $isBuku = request()->routeIs('portal-perpustakaan.buku');
                        $isRiwayatHapusBuku = request()->routeIs('portal-perpustakaan.riwayat-hapus-buku');
                        $isInventaris = request()->routeIs('portal-perpustakaan.inventaris');
                        $isSirkulasi = request()->routeIs('portal-perpustakaan.sirkulasi') || request()->routeIs('portal-perpustakaan.sirkulasi-kiosk');
                        $isPeminjaman = request()->routeIs('portal-perpustakaan.peminjaman');
                        $isPeminjamanPaket = request()->routeIs('portal-perpustakaan.peminjaman-paket*');
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
                        <div class="p-1.5 rounded-lg {{ $isDashboard ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Dashboard</span>
                    </a>

                    <!-- Menu Katalog & Input Buku -->
                    <a href="{{ route('portal-perpustakaan.buku') }}" 
                       :title="isCollapsed ? 'Katalog & Input Buku' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isBuku ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isBuku ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
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
                        <div class="p-1.5 rounded-lg {{ $isKlasifikasi ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Klasifikasi DDC</span>
                    </a>

                    <!-- Menu Inventaris Buku -->
                    <a href="{{ route('portal-perpustakaan.inventaris') }}" 
                       :title="isCollapsed ? 'Inventaris Buku' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isInventaris ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isInventaris ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Inventaris Buku</span>
                    </a>

                    <!-- Menu Sirkulasi Peminjaman -->
                    <a href="{{ route('portal-perpustakaan.sirkulasi') }}" 
                       :title="isCollapsed ? 'Sirkulasi & Peminjaman' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isSirkulasi ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isSirkulasi ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Sirkulasi</span>
                    </a>

                    <!-- Menu Peminjaman -->
                    <a href="{{ route('portal-perpustakaan.peminjaman') }}"
                       :title="isCollapsed ? 'Data Peminjaman' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPeminjaman ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPeminjaman ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Peminjaman</span>
                    </a>

                    <!-- Menu Peminjaman Buku Paket (1 Tahun) -->
                    <a href="{{ route('portal-perpustakaan.peminjaman-paket') }}"
                       :title="isCollapsed ? 'Peminjaman Buku Paket' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPeminjamanPaket ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPeminjamanPaket ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Peminjaman Buku Paket</span>
                    </a>

                    <!-- Menu Presensi Kunjungan -->
                    <a href="{{ route('portal-perpustakaan.kunjungan') }}" 
                       :title="isCollapsed ? 'Riwayat Presensi Kunjungan' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isKunjungan ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isKunjungan ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Riwayat Presensi</span>
                    </a>

                    <!-- Cetak Kartu -->
                    <a href="{{ route('portal-perpustakaan.cetak-kartu') }}" 
                       :title="isCollapsed ? 'Cetak Kartu Siswa' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isCetakKartu ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isCetakKartu ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Cetak Kartu</span>
                    </a>

                    @include('components.portal-sidebar-shortcuts')

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
                        <div class="p-1.5 rounded-lg {{ $isDashboard ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Dashboard Utama</span>
                    </a>

                    <!-- Menu Input Presensi Manual -->
                    <a href="{{ route('portal-presensi.input-manual') }}" 
                       :title="isCollapsed ? 'Input Presensi Manual' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isInputManual ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isInputManual ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Input Manual</span>
                    </a>

                    <!-- Menu Rekap Presensi Kelas -->
                    <a href="{{ route('portal-presensi.rekap-kelas') }}" 
                       :title="isCollapsed ? 'Rekap Presensi Kelas' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isRekapKelas ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isRekapKelas ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Rekap Kelas</span>
                    </a>

                    <!-- Menu Rekap Presensi Sekolah -->
                    <a href="{{ route('portal-presensi.rekap-sekolah') }}" 
                       :title="isCollapsed ? 'Rekap Presensi Sekolah' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isRekapSekolah ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isRekapSekolah ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Rekap Sekolah</span>
                    </a>

                    <!-- Menu Cetak Laporan -->
                    <a href="{{ route('portal-presensi.cetak-laporan') }}" 
                       :title="isCollapsed ? 'Cetak Laporan Presensi' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isCetakLaporan ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isCetakLaporan ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Cetak Laporan</span>
                    </a>

                    <!-- Menu Cetak Kartu -->
                    <a href="{{ route('portal-presensi.cetak-kartu') }}" 
                       :title="isCollapsed ? 'Cetak Kartu Siswa' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isCetakKartu ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isCetakKartu ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Cetak Kartu</span>
                    </a>

                    <!-- Menu Setting Notifikasi -->
                    <a href="{{ route('portal-presensi.setting-notifikasi') }}" 
                       :title="isCollapsed ? 'Setting Notifikasi WA' : ''"
                       class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isSettingNotifikasi ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group"
                       :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isSettingNotifikasi ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Setting Notifikasi WA</span>
                    </a>

                    @include('components.portal-sidebar-shortcuts')

                @elseif($user && $user->hasRole('siswa'))
                    @php
                        $student = $user->student;
                        $isDashboardActive = request()->routeIs('portal-siswa.dashboard');
                        $isAkademikActive = request()->routeIs('portal-siswa.akademik');
                        $isIjinKehadiranActive = request()->routeIs('portal-siswa.ijin') || request()->routeIs('portal-siswa.ijin.form');
                        $isGuruWaliActive = request()->routeIs('portal-siswa.guru-wali*');
                        $isPerpustakaanActive = request()->routeIs('portal-siswa.perpustakaan');
                        $isCetakKartuActive = request()->routeIs('portal-siswa.cetak-kartu');
                        $isProfilActive = request()->routeIs('portal-siswa.profil');
                        $isSpikapActive = request()->routeIs('portal-siswa.spikap') || request()->routeIs('portal-siswa.spikap.form');

                        $guruWaliNotifCount = 0;
                        if ($student) {
                            $guruWaliNotifCount = \App\Models\KonsultasiGuruWali::where('student_id', $student->id)
                                ->where('status_pengajuan', 'Dijadwalkan')
                                ->count();
                        }
                    @endphp

                    @if($student && $student->isLulus())
                        {{-- ── Menu Khusus Siswa Lulus (Alumni) ── --}}
                        <p class="px-3 text-[11px] font-bold text-emerald-500 uppercase tracking-widest mb-3 truncate" x-show="!isCollapsed" x-transition.opacity>Portal Alumni</p>

                        <!-- Menu Tracer Study -->
                        <a href="{{ route('portal-siswa.dashboard') }}" :title="isCollapsed ? 'Tracer Study Alumni' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-emerald-600 text-white font-bold shadow-lg shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-emerald-100 group-hover:text-emerald-600' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <i class="fas fa-graduation-cap text-base"></i>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Tracer Study Alumni</span>
                        </a>

                        <!-- Menu Profil Saya -->
                        <a href="{{ route('portal-siswa.profil') }}" :title="isCollapsed ? 'Profil Saya' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isProfilActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isProfilActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <i class="fas fa-user-circle text-base"></i>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Profil Saya</span>
                        </a>

                    @elseif($student && $student->isMutasi())
                        {{-- ── Menu Khusus Siswa Mutasi ── --}}
                        <p class="px-3 text-[11px] font-bold text-amber-500 uppercase tracking-widest mb-3 truncate" x-show="!isCollapsed" x-transition.opacity>Portal Siswa Mutasi</p>

                        <!-- Menu Data Mutasi -->
                        <a href="{{ route('portal-siswa.dashboard') }}" :title="isCollapsed ? 'Data Mutasi Siswa' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-amber-600 text-white font-bold shadow-lg shadow-amber-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-amber-100 group-hover:text-amber-600' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <i class="fas fa-school text-base"></i>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Data Mutasi Siswa</span>
                        </a>

                        <!-- Menu Profil Saya -->
                        <a href="{{ route('portal-siswa.profil') }}" :title="isCollapsed ? 'Profil Saya' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isProfilActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isProfilActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <i class="fas fa-user-circle text-base"></i>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Profil Saya</span>
                        </a>

                    @else
                        {{-- ── Menu Lengkap Siswa Aktif Reguler ── --}}
                        <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 truncate" x-show="!isCollapsed" x-transition.opacity>Modul Siswa</p>

                        <!-- Menu Dashboard Utama -->
                        <a href="{{ route('portal-siswa.dashboard') }}" :title="isCollapsed ? 'Dashboard Utama' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Dashboard Utama</span>
                        </a>

                        <!-- Menu Presensi & Akademik -->
                        <a href="{{ route('portal-siswa.akademik') }}" :title="isCollapsed ? 'Presensi & Akademik' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isAkademikActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isAkademikActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Presensi & Akademik</span>
                        </a>

                        <!-- Menu Pengajuan Ijin -->
                        <a href="{{ route('portal-siswa.ijin') }}" :title="isCollapsed ? 'Pengajuan Ijin' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isIjinKehadiranActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isIjinKehadiranActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Pengajuan Ijin</span>
                        </a>

                        <!-- Menu Konsultasi Guru Wali (Permen 11/2025) -->
                        <a href="{{ route('portal-siswa.guru-wali') }}" :title="isCollapsed ? 'Konsultasi Guru Wali' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isGuruWaliActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isGuruWaliActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm relative">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                @if($guruWaliNotifCount > 0)
                                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-blue-500 rounded-full ring-2 ring-white"></span>
                                @endif
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Konsultasi Guru Wali</span>
                            @if($guruWaliNotifCount > 0)
                                <span class="ml-auto px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded-full" x-show="!isCollapsed">{{ $guruWaliNotifCount }}</span>
                            @endif
                        </a>
                        
                        @php $spikapAppSetting = \App\Models\SpikapNotifSetting::instance(); @endphp
                        <!-- Menu SPIKAP — Anti-Perundungan -->
                        <a href="{{ route('portal-siswa.spikap') }}" :title="isCollapsed ? '{{ $spikapAppSetting->getNamaAplikasi() }} ({{ $spikapAppSetting->getSubJudul() }})' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isSpikapActive ? 'bg-rose-600 text-white font-bold shadow-lg shadow-rose-500/30' : 'text-slate-600 hover:bg-rose-50 hover:text-rose-700 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isSpikapActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-rose-100 group-hover:text-rose-600' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l1.664 1.664M21 21l-1.5-1.5m-5.485-1.242L12 17.25 4.5 21V8.742m.164-4.078a2.15 2.15 0 011.743-1.342 48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185V19.5M4.664 4.664L19.5 19.5" />
                                </svg>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>{{ $spikapAppSetting->getNamaAplikasi() }}</span>
                        </a>

                        <!-- Menu Perpustakaan -->
                        <a href="{{ route('portal-siswa.perpustakaan') }}" :title="isCollapsed ? 'Perpustakaan' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPerpustakaanActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isPerpustakaanActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Perpustakaan</span>
                        </a>

                        <!-- Menu Cetak Kartu Siswa Mandiri -->
                        <a href="{{ route('portal-siswa.cetak-kartu') }}" target="_blank" :title="isCollapsed ? 'Cetak Kartu Siswa' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isCetakKartuActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isCetakKartuActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Cetak Kartu Saya</span>
                        </a>

                        <!-- Menu Profil Saya -->
                        <a href="{{ route('portal-siswa.profil') }}" :title="isCollapsed ? 'Profil Saya' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isProfilActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="p-1.5 rounded-lg {{ $isProfilActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Profil Saya</span>
                        </a>
                    @endif
                @else
                    @php
                        $isDashboardActive = request()->routeIs('portal-guru.dashboard');
                        
                        // Akademik & Siswa group
                        $isAkademikActive = request()->routeIs('portal-guru.akademik') || request()->routeIs('portal-guru.student-detail');
                        $isDataSiswaActive = request()->routeIs('portal-guru.data-siswa');
                        $isIjinKehadiranActive = request()->routeIs('portal-guru.ijin') || request()->routeIs('portal-guru.ijin.detail');
                        $isAkademikGroupActive = $isAkademikActive || $isDataSiswaActive || $isIjinKehadiranActive;
                        $canAccessAkademikGroup = $user?->isWaliKelasAktif() || $user?->isWaliKelasMurni() || ($user?->isGuruBk() && $user->teacher?->kelasPantau()->exists());

                        // Guru Wali group
                        $guruWaliKelompok = $user?->teacher?->kelompokGuruWali;
                        $isGuruWaliAktif = $guruWaliKelompok && $guruWaliKelompok->status_aktif;
                        $isGwKelompok = request()->routeIs('portal-guru.guru-wali.kelompok*');
                        $isGwJurnal = request()->routeIs('portal-guru.guru-wali.jurnal*');
                        $isGwKonsultasi = request()->routeIs('portal-guru.guru-wali.konsultasi*');
                        $isGwPemantauan = request()->routeIs('portal-guru.guru-wali.pemantauan*');
                        $isGwCetak = request()->routeIs('portal-guru.guru-wali.cetak*');
                        $isGuruWaliGroupActive = $isGwKelompok || $isGwJurnal || $isGwKonsultasi || $isGwPemantauan || $isGwCetak;

                        $pendingKonsultasiCount = 0;
                        if ($isGuruWaliAktif && $user?->teacher) {
                            $pendingKonsultasiCount = \App\Models\KonsultasiGuruWali::where('teacher_id', $user->teacher->id)
                                ->where('status_pengajuan', 'Menunggu Konfirmasi')
                                ->count();
                        }

                        // SPIKAP group
                        $isSpikapGuruActive = request()->routeIs('portal-guru.spikap*');
                        $spikapAppSetting = \App\Models\SpikapNotifSetting::instance();
                        $canAccessSpikap = $user?->canAccessSpikapGuru();

                        // Perpustakaan & Profil
                        $isPerpustakaanActive = request()->routeIs('portal-guru.perpustakaan');
                        $isProfilGuruActive = request()->routeIs('portal-guru.profil');
                    @endphp
                    
                    <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 truncate" x-show="!isCollapsed" x-transition.opacity>Modul Guru</p>

                    <!-- Menu Dashboard Utama -->
                    <a href="{{ route('portal-guru.dashboard') }}" :title="isCollapsed ? 'Dashboard Utama' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isDashboardActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isDashboardActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Dashboard Utama</span>
                    </a>

                    <!-- Submenu: Akademik & Wali Kelas -->
                    @if($canAccessAkademikGroup)
                    <div x-data="{ open: {{ $isAkademikGroupActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="if(isCollapsed) { sidebarCollapsed = false; } open = !open" 
                                :title="isCollapsed ? 'Akademik & Kelas' : ''"
                                type="button"
                                class="w-full flex items-center justify-between gap-3.5 py-3 rounded-2xl transition-all group {{ $isAkademikGroupActive ? 'bg-brand-primary/10 text-brand-primary font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                                :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="p-1.5 rounded-lg {{ $isAkademikGroupActive ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                </div>
                                <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Akademik & Kelas</span>
                            </div>
                            <div class="flex items-center gap-1.5" x-show="!isCollapsed" x-transition.opacity>
                                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 {{ $isAkademikGroupActive ? 'text-brand-primary' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Submenu Dropdown Container -->
                        <div x-show="open && !isCollapsed" x-collapse class="pl-4 pr-1 py-1 space-y-1 relative before:absolute before:left-6 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                            @if($user?->isWaliKelasAktif())
                                <!-- Presensi & Akademik -->
                                <a href="{{ route('portal-guru.akademik') }}" 
                                   class="flex items-center gap-2.5 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ $isAkademikActive ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isAkademikActive ? 'bg-white' : 'bg-slate-400' }}"></span>
                                    <span class="truncate">Presensi & Jurnal</span>
                                </a>

                                <!-- Data Siswa -->
                                <a href="{{ route('portal-guru.data-siswa') }}" 
                                   class="flex items-center gap-2.5 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ $isDataSiswaActive ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isDataSiswaActive ? 'bg-white' : 'bg-slate-400' }}"></span>
                                    <span class="truncate">Data Siswa</span>
                                </a>
                            @endif

                            @if($user?->isWaliKelasMurni() || ($user?->isGuruBk() && $user->teacher?->kelasPantau()->exists()))
                                <!-- Persetujuan Izin -->
                                <a href="{{ route('portal-guru.ijin') }}" 
                                   class="flex items-center justify-between gap-2 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ $isIjinKehadiranActive ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isIjinKehadiranActive ? 'bg-white' : 'bg-slate-400' }}"></span>
                                        <span class="truncate">Persetujuan Izin</span>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Submenu: Guru Wali (Permen 11/2025) -->
                    @if($isGuruWaliAktif)
                    <div x-data="{ open: {{ $isGuruWaliGroupActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="if(isCollapsed) { sidebarCollapsed = false; } open = !open" 
                                :title="isCollapsed ? 'Guru Wali' : ''"
                                type="button"
                                class="w-full flex items-center justify-between gap-3.5 py-3 rounded-2xl transition-all group relative {{ $isGuruWaliGroupActive ? 'bg-brand-primary/10 text-brand-primary font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                                :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="p-1.5 rounded-lg {{ $isGuruWaliGroupActive ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm relative">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    @if($pendingKonsultasiCount > 0)
                                        <span x-show="isCollapsed" class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                                    @endif
                                </div>
                                <div class="flex flex-col text-left min-w-0" x-show="!isCollapsed" x-transition.opacity>
                                    <span class="text-sm truncate leading-tight">Guru Wali</span>
                                    <span class="text-[10px] text-slate-400 font-normal leading-tight">Permen 11/2025</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5" x-show="!isCollapsed" x-transition.opacity>
                                @if($pendingKonsultasiCount > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow-xs">
                                        {{ $pendingKonsultasiCount }}
                                    </span>
                                @endif
                                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 {{ $isGuruWaliGroupActive ? 'text-brand-primary' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Submenu Dropdown Container -->
                        <div x-show="open && !isCollapsed" x-collapse class="pl-4 pr-1 py-1 space-y-1 relative before:absolute before:left-6 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                            <!-- Kelompok Saya -->
                            <a href="{{ route('portal-guru.guru-wali.kelompok') }}" 
                               class="flex items-center gap-2.5 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ $isGwKelompok ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isGwKelompok ? 'bg-white' : 'bg-slate-400' }}"></span>
                                <span class="truncate">Kelompok Saya</span>
                            </a>

                            <!-- Jurnal Siswa -->
                            <a href="{{ route('portal-guru.guru-wali.jurnal') }}" 
                               class="flex items-center gap-2.5 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ $isGwJurnal ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isGwJurnal ? 'bg-white' : 'bg-slate-400' }}"></span>
                                <span class="truncate">Jurnal Siswa</span>
                            </a>

                            <!-- Permintaan Konsultasi -->
                            <a href="{{ route('portal-guru.guru-wali.konsultasi') }}" 
                               class="flex items-center justify-between gap-2 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ $isGwKonsultasi ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isGwKonsultasi ? 'bg-white' : 'bg-slate-400' }}"></span>
                                    <span class="truncate">Konsultasi Siswa</span>
                                </div>
                                @if($pendingKonsultasiCount > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $isGwKonsultasi ? 'bg-white text-brand-primary' : 'bg-rose-500 text-white' }}">
                                        {{ $pendingKonsultasiCount }}
                                    </span>
                                @endif
                            </a>

                            <!-- Pemantauan Bulanan -->
                            <a href="{{ route('portal-guru.guru-wali.pemantauan') }}" 
                               class="flex items-center gap-2.5 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ $isGwPemantauan ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isGwPemantauan ? 'bg-white' : 'bg-slate-400' }}"></span>
                                <span class="truncate">Pemantauan Bulanan</span>
                            </a>

                            <!-- Cetak Laporan -->
                            <a href="{{ route('portal-guru.guru-wali.cetak') }}" 
                               class="flex items-center gap-2.5 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ $isGwCetak ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isGwCetak ? 'bg-white' : 'bg-slate-400' }}"></span>
                                <span class="truncate">Cetak Laporan</span>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Submenu: SPIKAP (Aduan Siswa) -->
                    @if($canAccessSpikap)
                    <div x-data="{ open: {{ $isSpikapGuruActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="if(isCollapsed) { sidebarCollapsed = false; } open = !open" 
                                :title="isCollapsed ? '{{ $spikapAppSetting->getNamaAplikasi() }}' : ''"
                                type="button"
                                class="w-full flex items-center justify-between gap-3.5 py-3 rounded-2xl transition-all group {{ $isSpikapGuruActive ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-600 hover:bg-rose-50/70 hover:text-rose-700 font-medium' }}"
                                :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="p-1.5 rounded-lg {{ $isSpikapGuruActive ? 'bg-rose-600 text-white shadow-md shadow-rose-600/25' : 'bg-rose-50 text-rose-600 group-hover:bg-rose-100 group-hover:text-rose-700' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>{{ $spikapAppSetting->getNamaAplikasi() }} (Aduan)</span>
                            </div>
                            <div class="flex items-center gap-1.5" x-show="!isCollapsed" x-transition.opacity>
                                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 {{ $isSpikapGuruActive ? 'text-rose-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Submenu Dropdown Container -->
                        <div x-show="open && !isCollapsed" x-collapse class="pl-4 pr-1 py-1 space-y-1 relative before:absolute before:left-6 before:top-2 before:bottom-2 before:w-0.5 before:bg-rose-200">
                            <!-- Kotak Masuk Aduan -->
                            <a href="{{ route('portal-guru.spikap') }}" 
                               class="flex items-center gap-2.5 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ request()->routeIs('portal-guru.spikap') || request()->routeIs('portal-guru.spikap.detail') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/25' : 'text-slate-600 hover:bg-rose-50 hover:text-rose-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('portal-guru.spikap') || request()->routeIs('portal-guru.spikap.detail') ? 'bg-white' : 'bg-rose-400' }}"></span>
                                <span class="truncate">Kotak Masuk Aduan</span>
                            </a>

                            <!-- Analitik Aduan -->
                            <a href="{{ route('portal-guru.spikap.analitik') }}" 
                               class="flex items-center gap-2.5 py-2.5 px-3 pl-6 rounded-xl text-xs font-semibold transition-all relative {{ request()->routeIs('portal-guru.spikap.analitik') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/25' : 'text-slate-600 hover:bg-rose-50 hover:text-rose-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('portal-guru.spikap.analitik') ? 'bg-white' : 'bg-rose-400' }}"></span>
                                <span class="truncate">Analitik & Statistik</span>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Menu Perpustakaan Guru -->
                    <a href="{{ route('portal-guru.perpustakaan') }}" :title="isCollapsed ? 'Perpustakaan' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isPerpustakaanActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isPerpustakaanActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Perpustakaan</span>
                    </a>

                    <!-- Menu Profil Saya (Guru) -->
                    <a href="{{ route('portal-guru.profil') }}" :title="isCollapsed ? 'Profil Saya' : ''" class="flex items-center gap-3.5 py-3 rounded-2xl {{ $isProfilGuruActive ? 'bg-brand-primary text-white font-bold shadow-lg shadow-brand-primary/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }} transition-all group" :class="isCollapsed ? 'justify-center px-0' : 'px-3.5'">
                        <div class="p-1.5 rounded-lg {{ $isProfilGuruActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-primary/10 group-hover:text-brand-primary' }} group-hover:scale-105 transition-transform backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-sm truncate" x-show="!isCollapsed" x-transition.opacity>Profil Saya</span>
                    </a>

                    @include('components.portal-sidebar-shortcuts')
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

                    <div class="hidden sm:flex items-center gap-2">
                        <span class="text-sm font-extrabold text-slate-800 tracking-tight">{{ $currentPortalTitle }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Topbar Quick Action Pills (Desktop) -->
                    <div class="hidden md:flex items-center gap-1.5">
                        <a href="{{ url('/') }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Web Utama</span>
                        </a>

                        @if($hasMultiplePortals)
                            <a href="{{ url('/pilih-portal') }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>Pilih Portal</span>
                            </a>
                        @endif

                        @if($isSuper)
                            <a href="{{ url('/admin') }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200/80 rounded-xl transition-all shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Panel Admin</span>
                            </a>
                        @endif
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
                             class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200/80 py-2 z-50 overflow-hidden" 
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
                                    <span class="text-xs font-semibold text-brand-primary truncate">{{ $userRole }}</span>
                                </div>
                            </div>

                            <!-- Hub Akses Utama -->
                            <div class="px-3 pt-2 pb-1 space-y-1">
                                @if($hasMultiplePortals)
                                    <a href="{{ url('/pilih-portal') }}" class="flex items-center justify-between p-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-100 text-xs font-semibold text-slate-700 transition-colors group">
                                        <div class="flex items-center gap-2.5">
                                            <div class="p-1.5 bg-white rounded-lg shadow-xs text-brand-primary">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                            </div>
                                            <span class="group-hover:text-slate-900">Pilih Portal ERP</span>
                                        </div>
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @endif

                                @if($isSuper)
                                    <a href="{{ url('/admin') }}" class="flex items-center justify-between p-2 rounded-xl bg-purple-50/70 hover:bg-purple-100/70 border border-purple-100 text-xs font-semibold text-purple-800 transition-colors group">
                                        <div class="flex items-center gap-2.5">
                                            <div class="p-1.5 bg-white rounded-lg shadow-xs text-purple-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            </div>
                                            <span>Panel Admin</span>
                                        </div>
                                        <span class="text-[10px] bg-purple-200 text-purple-800 px-1.5 py-0.5 rounded font-bold">Master</span>
                                    </a>
                                @endif

                                <a href="{{ url('/') }}" target="_blank" class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-600 transition-colors group">
                                    <div class="flex items-center gap-2.5">
                                        <div class="p-1.5 bg-slate-100 rounded-lg text-slate-500 group-hover:text-slate-700">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </div>
                                        <span>Web Utama Sekolah</span>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                            
                            <div class="border-t border-slate-100 mx-3 my-1"></div>

                            <!-- Pintasan Modul & Portal -->
                            <div class="px-3 py-1.5">
                                <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider mb-1.5 px-1">Pintasan Portal & Modul</p>
                                <div class="max-h-56 overflow-y-auto space-y-1 pr-0.5">
                                    @if($user && $user->hasRole('siswa'))
                                        <a href="{{ route('portal-siswa.dashboard') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-blue-100 hover:bg-blue-50/50 text-xs font-semibold text-slate-700 hover:text-blue-700 transition-all">
                                            <div class="p-1.5 rounded-lg bg-blue-100 text-blue-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                            </div>
                                            <span>Dashboard Siswa</span>
                                                            <a href="{{ route('portal-siswa.profil') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl border border-transparent hover:border-brand-primary/20 hover:bg-brand-primary/5 text-xs font-semibold text-slate-700 hover:text-brand-primary transition-all">
                                            <div class="p-1.5 rounded-lg bg-brand-primary/10 text-brand-primary">
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
                                        {{-- Portal Web --}}
                                        @if($canAccessWeb)
                                            <a href="{{ route('portal-web.dashboard') }}" @click="userMenuOpen = false"
                                                class="flex items-center justify-between p-2 rounded-xl {{ $isWebRoute ? 'bg-violet-50 text-violet-700 font-bold border border-violet-200/80' : 'hover:bg-slate-50 text-slate-700 font-semibold border border-transparent' }} text-xs transition-all">
                                                <div class="flex items-center gap-2">
                                                    <div class="p-1.5 rounded-lg {{ $isWebRoute ? 'bg-violet-600 text-white' : 'bg-violet-100 text-violet-600' }}">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                                    </div>
                                                    <span>Portal Web</span>
                                                </div>
                                                @if($isWebRoute)
                                                    <span class="text-[9px] bg-violet-200 text-violet-800 px-1.5 py-0.5 rounded font-bold">Aktif</span>
                                                @endif
                                            </a>
                                        @endif

                                        {{-- Portal Presensi --}}
                                        @if($canAccessPresensi)
                                            <a href="{{ route('portal-presensi.dashboard') }}" @click="userMenuOpen = false"
                                                class="flex items-center justify-between p-2 rounded-xl {{ $isPresensiRoute ? 'bg-amber-50 text-amber-700 font-bold border border-amber-200/80' : 'hover:bg-slate-50 text-slate-700 font-semibold border border-transparent' }} text-xs transition-all">
                                                <div class="flex items-center gap-2">
                                                    <div class="p-1.5 rounded-lg {{ $isPresensiRoute ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-600' }}">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    </div>
                                                    <span>Portal Presensi</span>
                                                </div>
                                                @if($isPresensiRoute)
                                                    <span class="text-[9px] bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded font-bold">Aktif</span>
                                                @endif
                                            </a>
                                        @endif

                                        {{-- Portal Perpustakaan --}}
                                        @if($canAccessPerpus)
                                            <a href="{{ route('portal-perpustakaan.dashboard') }}" @click="userMenuOpen = false"
                                                class="flex items-center justify-between p-2 rounded-xl {{ $isPerpusRoute ? 'bg-cyan-50 text-cyan-700 font-bold border border-cyan-200/80' : 'hover:bg-slate-50 text-slate-700 font-semibold border border-transparent' }} text-xs transition-all">
                                                <div class="flex items-center gap-2">
                                                    <div class="p-1.5 rounded-lg {{ $isPerpusRoute ? 'bg-cyan-600 text-white' : 'bg-cyan-100 text-cyan-600' }}">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                                    </div>
                                                    <span>Portal Perpustakaan</span>
                                                </div>
                                                @if($isPerpusRoute)
                                                    <span class="text-[9px] bg-cyan-200 text-cyan-800 px-1.5 py-0.5 rounded font-bold">Aktif</span>
                                                @endif
                                            </a>
                                        @endif

                                        {{-- Portal Guru --}}
                                        @if($canAccessGuru)
                                            <a href="{{ route('portal-guru.dashboard') }}" @click="userMenuOpen = false"
                                                class="flex items-center justify-between p-2 rounded-xl {{ $isGuruRoute ? 'bg-brand-primary/10 text-brand-primary font-bold border border-brand-primary/30' : 'hover:bg-slate-50 text-slate-700 font-semibold border border-transparent' }} text-xs transition-all">
                                                <div class="flex items-center gap-2">
                                                    <div class="p-1.5 rounded-lg {{ $isGuruRoute ? 'bg-brand-primary text-white' : 'bg-brand-primary/10 text-brand-primary' }}">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                                    </div>
                                                    <span>Portal Guru</span>
                                                </div>
                                                @if($isGuruRoute)
                                                    <span class="text-[9px] bg-brand-primary/20 text-brand-primary px-1.5 py-0.5 rounded font-bold">Aktif</span>
                                                @endif
                                            </a>

                                            @if($user && $user->teacher)
                                                <a href="{{ route('portal-guru.profil') }}" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700 hover:text-brand-primary transition-all">
                                                    <div class="p-1.5 rounded-lg bg-brand-primary/10 text-brand-primary">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                    </div>
                                                    <span>Profil Saya (Guru)</span>
                                                </a>
                                            @endif
                                        @endif

                                        {{-- Kiosk & Layanan Digital --}}
                                        @if($canAccessPresensi || ($user && $user->teacher))
                                            <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" target="_blank" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl hover:bg-blue-50 text-xs font-semibold text-slate-700 hover:text-blue-700 transition-all">
                                                <div class="p-1.5 rounded-lg bg-blue-100 text-blue-600">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                                </div>
                                                <span>Presensi Digital (Kiosk)</span>
                                            </a>
                                        @endif

                                        @if($canAccessPerpus)
                                            <a href="{{ route('perpustakaan.kunjungan') }}" target="_blank" @click="userMenuOpen = false" class="flex items-center gap-2 p-2 rounded-xl hover:bg-purple-50 text-xs font-semibold text-slate-700 hover:text-purple-700 transition-all">
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
            </div>

            @include('components.announcement-banner')

            <!-- Page Content -->
            <main class="flex-1 relative p-4 sm:p-6 pb-12 sm:pb-16">
                {{ $slot }}
            </main>
        </div>

    </body>
</html>
