<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Perpustakaan - Segera Hadir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="antialiased">

    <!-- ====================== HEADER / NAVBAR MODERN ====================== -->
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500" id="main-navbar"
        x-data="{ scrolled: false, mobileMenuOpen: false }"
        @scroll.window="scrolled = window.scrollY > 30"
        :class="scrolled ? 'bg-white/80 backdrop-blur-xl shadow-lg shadow-teal-100/50 border-b border-white/60' : (mobileMenuOpen ? 'bg-slate-950/70 backdrop-blur-2xl border-b border-white/10' : 'bg-transparent')">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Header Kiri: Logo & Nama Sekolah -->
                <div class="flex items-center gap-3 group">
                    @php $pengaturanSekolah = \App\Models\PengaturanSekolah::current(); @endphp
                    @if($pengaturanSekolah && $pengaturanSekolah->school_logo_path)
                        <div class="relative">
                            <div class="absolute inset-0 bg-teal-400/30 rounded-xl blur-md group-hover:blur-lg transition-all duration-300"></div>
                            <img src="{{ asset('storage/' . $pengaturanSekolah->school_logo_path) }}" alt="Logo"
                                class="relative h-10 sm:h-12 w-auto object-contain drop-shadow-md">
                        </div>
                    @endif
                    <div>
                        <h1 class="text-lg sm:text-xl font-extrabold tracking-tight leading-tight transition-colors duration-300"
                            :class="scrolled ? 'text-slate-800' : 'text-white drop-shadow-md'">
                            {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'Sistem Perpustakaan' }}
                        </h1>
                        <p class="text-xs font-medium transition-colors duration-300" :class="scrolled ? 'text-teal-600' : 'text-teal-300'">
                            Perpustakaan Digital
                        </p>
                    </div>
                </div>

                <!-- Header Kanan: Menu Navigasi Desktop -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <a href="{{ url('/') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-teal-700 hover:bg-teal-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Home
                        </span>
                    </a>
                    <a href="{{ url('/portal-siswa') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-teal-700 hover:bg-teal-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Portal Siswa
                        </span>
                    </a>
                    <a href="{{ url('/portal-guru') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-teal-700 hover:bg-teal-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Portal Guru
                        </span>
                    </a>
                    <a href="{{ route('login') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-teal-700 hover:bg-teal-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Admin
                        </span>
                    </a>
                    <a href="#"
                        onclick="alert('Fitur pencarian buku sedang dalam tahap pengembangan.')"
                        class="ml-4 flex items-center gap-2 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-teal-500/30 transition-all duration-300 transform hover:scale-105 hover:shadow-teal-500/50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cari Buku
                    </a>
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
             class="lg:hidden absolute top-full left-0 w-full backdrop-blur-xl border-b shadow-xl transition-colors duration-300"
             :class="scrolled ? 'bg-white/95 border-slate-200' : 'bg-slate-950/80 border-white/10'"
             style="display: none;">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="{{ url('/') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-teal-50 hover:text-teal-600' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Home
                </a>
                <a href="{{ url('/portal-siswa') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-teal-50 hover:text-teal-600' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Portal Siswa
                </a>
                <a href="{{ url('/portal-guru') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-teal-50 hover:text-teal-600' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Portal Guru
                </a>
                <a href="{{ route('login') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-teal-50 hover:text-teal-600' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Admin
                </a>
                <div class="pt-2">
                    <a href="#"
                        onclick="alert('Fitur pencarian buku sedang dalam tahap pengembangan.')"
                        class="flex justify-center items-center gap-2 bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-5 py-3 rounded-xl font-bold w-full shadow-lg shadow-teal-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cari Buku
                    </a>
                </div>
            </div>
        </div>
    </header>

<div class="min-h-screen flex flex-col bg-slate-900 relative overflow-x-hidden pt-28 sm:pt-32 pb-12 sm:pb-16 px-4 sm:px-6">
    <!-- Global Background -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/50 mix-blend-multiply"></div>
        <!-- Decorative Blobs -->
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-teal-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-2xl mx-auto my-auto text-center">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-white/10 rounded-3xl backdrop-blur-md border border-white/20 text-teal-400 shadow-xl mb-8">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
            Dashboard Perpustakaan <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-emerald-300">Segera Hadir</span>
        </h1>
        <p class="text-lg text-slate-300 mb-8 max-w-xl mx-auto">
            Halaman publik untuk pencarian katalog buku (OPAC) sedang dalam tahap pengembangan dan akan segera dirilis.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/') }}" class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl border border-white/20 backdrop-blur-md transition-all duration-300">
                Kembali ke Portal
            </a>
            <a href="{{ url('/admin-perpustakaan') }}" class="px-8 py-4 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-xl shadow-lg shadow-teal-500/20 transition-all duration-300">
                Masuk Panel Admin
            </a>
        </div>
    </div>
</div>

</body>
</html>
