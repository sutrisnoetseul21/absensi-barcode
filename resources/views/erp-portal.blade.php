<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Terpadu (ERP) Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="antialiased text-slate-100">

@php
    $pengaturanSekolah = \App\Models\PengaturanSekolah::current();
    $authUser = Auth::user();
    $accessiblePortals = $authUser ? $authUser->getAccessiblePortals() : [];
@endphp

<x-public-dashboard.navbar :pengaturanSekolah="$pengaturanSekolah" :alwaysDark="true" />

<div class="min-h-screen flex flex-col items-center justify-center bg-slate-900 relative overflow-hidden p-4 sm:p-8 pt-24">
    <!-- Global Background -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        @if($pengaturanSekolah && $pengaturanSekolah->login_background_path)
            <img src="{{ asset('storage/' . $pengaturanSekolah->login_background_path) }}" class="w-full h-full object-cover object-center opacity-50">
        @else
            <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover object-center opacity-50">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/60 to-slate-900/40 mix-blend-multiply"></div>
        <!-- Decorative Blobs -->
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-blue-500 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-teal-500 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-5xl mx-auto">
        
        @auth
            <!-- Logged-in View: Hub Pemilihan Portal Sesuai Hak Akses -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/20 text-xs font-semibold text-indigo-200 backdrop-blur-md mb-4 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Login Aktif: {{ $authUser->role_badge }}
                </div>
                
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-3">
                    Selamat Datang, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-teal-200 to-indigo-200 drop-shadow-sm">{{ $authUser->display_name }}</span>
                </h1>
                <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto leading-relaxed">
                    Silakan pilih portal kerja atau layanan sistem yang ingin Anda akses di bawah ini.
                </p>
            </div>

            <!-- Portal Grid for Authenticated User -->
            <div class="grid grid-cols-1 md:grid-cols-2 {{ count($accessiblePortals) >= 3 ? 'lg:grid-cols-3' : '' }} gap-6 sm:gap-8 max-w-5xl mx-auto">
                @forelse($accessiblePortals as $portal)
                    <a href="{{ $portal['url'] }}" 
                       class="group relative flex flex-col justify-between p-6 sm:p-7 rounded-3xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 hover:border-blue-400/50 hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 transform hover:-translate-y-1.5 overflow-hidden">
                        
                        <div class="absolute inset-0 bg-gradient-to-br {{ $portal['gradient'] ?? 'from-blue-500 to-teal-500' }} opacity-0 group-hover:opacity-15 transition-opacity duration-300"></div>

                        <div>
                            <div class="flex items-center justify-between mb-5">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-md bg-slate-800 text-slate-200 group-hover:bg-blue-500 group-hover:text-white">
                                    @if(($portal['icon'] ?? '') === 'shield')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    @elseif(($portal['icon'] ?? '') === 'user-group')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    @elseif(($portal['icon'] ?? '') === 'clock')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif(($portal['icon'] ?? '') === 'book')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @elseif(($portal['icon'] ?? '') === 'academic-cap')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                    @else
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    @endif
                                </div>
                                
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border backdrop-blur-xs {{ $portal['badge_color'] ?? 'bg-white/10 text-white border-white/20' }}">
                                    {{ $portal['badge'] }}
                                </span>
                            </div>

                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-blue-200 transition-colors">
                                {{ $portal['name'] }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed mb-6">
                                {{ $portal['desc'] }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs sm:text-sm font-bold text-blue-300 group-hover:text-white transition-colors">
                            <span>Buka Modul</span>
                            <div class="w-8 h-8 rounded-xl bg-white/5 group-hover:bg-white/20 flex items-center justify-center transition-all duration-200 group-hover:translate-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full p-8 text-center bg-white/5 rounded-3xl border border-white/10 text-slate-400">
                        <p class="font-medium text-sm">Tidak ada portal aktif yang terhubung dengan akun Anda.</p>
                    </div>
                @endforelse
            </div>
        @else
            <!-- Guest View: Landing Page Sistem Terpadu -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 text-white shadow-xl mb-6">
                    @if($pengaturanSekolah && $pengaturanSekolah->school_logo_path)
                        <img src="{{ asset('storage/' . $pengaturanSekolah->school_logo_path) }}" alt="Logo" class="w-12 h-12 object-contain">
                    @else
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    @endif
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
                    Sistem Informasi Terpadu <br class="hidden md:block"> <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-teal-300 drop-shadow-sm">{{ $pengaturanSekolah->school_name ?? 'Sekolah' }}</span>
                </h1>
                <p class="text-lg text-slate-300 max-w-2xl mx-auto">
                    Portal utama menuju seluruh layanan digital pendidikan. Silakan pilih sistem yang ingin Anda akses di bawah ini.
                </p>
            </div>

            <!-- Public Portal Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
                
                {{-- Modul Presensi --}}
                <a href="{{ url('/presensi') }}" class="group block p-8 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-3xl border border-white/20 hover:border-blue-400/50 hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-400/0 to-blue-500/0 group-hover:from-blue-400/10 group-hover:to-blue-500/10 transition-colors duration-300"></div>
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-20 h-20 bg-slate-800 text-slate-300 group-hover:bg-blue-500 group-hover:text-white rounded-2xl flex items-center justify-center mb-5 transition-colors duration-300 shadow-lg">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Sistem Presensi</h3>
                        <p class="text-slate-300">Dashboard Kehadiran & Rekapitulasi Harian Siswa.</p>
                    </div>
                </a>

                {{-- Modul Perpustakaan --}}
                <a href="{{ url('/perpustakaan') }}" class="group block p-8 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-3xl border border-white/20 hover:border-teal-400/50 hover:shadow-2xl hover:shadow-teal-500/20 transition-all duration-300 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-400/0 to-teal-500/0 group-hover:from-teal-400/10 group-hover:to-teal-500/10 transition-colors duration-300"></div>
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-20 h-20 bg-slate-800 text-slate-300 group-hover:bg-teal-500 group-hover:text-white rounded-2xl flex items-center justify-center mb-5 transition-colors duration-300 shadow-lg">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Sistem Perpustakaan</h3>
                        <p class="text-slate-300">Katalog Buku, Sirkulasi & E-Library.</p>
                    </div>
                </a>
            </div>

            <div class="text-center mt-16 text-sm text-slate-400">
                <a href="{{ url('/login') }}" class="hover:text-white transition-colors underline underline-offset-4">Masuk ke Portal</a>
            </div>
        @endauth

    </div>
</div>

@livewireScripts
</body>
</html>
