<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Portal Layanan - {{ config('app.name', 'Sekolah') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="antialiased text-slate-100">

@php
    $pengaturanSekolah = \App\Models\PengaturanSekolah::current();
    $authUser = Auth::user();
    $accessiblePortals = $accessiblePortals ?? ($authUser ? $authUser->getAccessiblePortals() : []);
@endphp

<div class="min-h-screen flex flex-col items-center justify-center bg-slate-900 relative overflow-hidden px-4 py-12 sm:px-6">
    <!-- Global Background -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        @if($pengaturanSekolah && $pengaturanSekolah->login_background_path)
            <img src="{{ asset('storage/' . $pengaturanSekolah->login_background_path) }}" class="w-full h-full object-cover object-center opacity-70">
        @else
            <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover object-center opacity-70">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-900/40 to-slate-900/20"></div>
        <!-- Decorative Blobs -->
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-blue-500 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-teal-500 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="relative z-10 w-full max-w-5xl mx-auto">

        <!-- Mini Header (no navbar, clean & focused) -->
        <div class="flex items-center justify-between mb-10 sm:mb-12">
            <!-- Logo + Nama Sekolah -->
            <div class="flex items-center gap-3">
                @if($pengaturanSekolah && $pengaturanSekolah->school_logo_path)
                    <img src="{{ asset('storage/' . $pengaturanSekolah->school_logo_path) }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg">
                @else
                    <div class="w-9 h-9 bg-white/10 border border-white/20 rounded-lg flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                @endif
                <span class="text-sm font-bold text-white/80 hidden sm:block">{{ $pengaturanSekolah->school_name ?? config('app.name') }}</span>
            </div>
        </div>

        <!-- User Greeting -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/20 text-xs font-semibold text-indigo-200 backdrop-blur-md mb-4 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Login Aktif: {{ $authUser->role_badge }}
            </div>
            
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-3">
                Selamat Datang, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-teal-200 to-indigo-200 drop-shadow-sm">{{ $authUser->display_name }}</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto leading-relaxed">
                Silakan pilih portal kerja atau layanan sistem yang ingin Anda tuju.
            </p>
        </div>

        <!-- Portals Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 max-w-5xl mx-auto">

            {{-- Kartu Beranda (selalu paling awal) --}}
            <a href="{{ url('/') }}"
               class="group relative flex flex-col p-6 sm:p-7 rounded-3xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 hover:border-slate-300/50 hover:shadow-2xl hover:shadow-slate-400/10 transition-all duration-300 transform hover:-translate-y-1.5 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-400 to-slate-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="flex items-center justify-between mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-md bg-slate-800 text-slate-200 group-hover:bg-slate-500 group-hover:text-white">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border bg-white/10 text-slate-200 border-white/20">Navigasi</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-2 group-hover:text-slate-200 transition-colors">Beranda</h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">Kembali ke halaman utama portal layanan sekolah.</p>
            </a>

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
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            {{ $portal['desc'] }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="col-span-full p-8 text-center bg-white/5 rounded-3xl border border-white/10 text-slate-400">
                    <p class="font-medium text-sm">Tidak ada portal aktif yang terhubung dengan akun Anda.</p>
                </div>
            @endforelse

            {{-- Kartu Keluar --}}
            <form action="{{ route('logout') }}" method="POST" class="contents">
                @csrf
                <button type="submit" class="group relative text-left flex flex-col w-full p-6 sm:p-7 rounded-3xl bg-white/10 hover:bg-rose-500/20 backdrop-blur-md border border-white/20 hover:border-rose-400/50 hover:shadow-2xl hover:shadow-rose-500/20 transition-all duration-300 transform hover:-translate-y-1.5 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-500 to-red-600 opacity-0 group-hover:opacity-15 transition-opacity duration-300"></div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-md bg-slate-800 text-slate-200 group-hover:bg-rose-500 group-hover:text-white">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border bg-rose-500/20 text-rose-300 border-rose-400/30">Akun</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-rose-300 transition-colors">Keluar</h3>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">Akhiri sesi dan keluar dari akun Anda.</p>
                </button>
            </form>
        </div>


    </div>
</div>

</body>
</html>
