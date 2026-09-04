<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Portal Admin - Sistem Presensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="antialiased">

@php
    $pengaturanSekolah = \App\Models\PengaturanSekolah::current();
@endphp

<div class="min-h-screen flex flex-col items-center justify-center bg-slate-900 relative overflow-hidden p-4 sm:p-8">
    <!-- Global Background -->
    <div class="absolute inset-0 z-0">
        @if($pengaturanSekolah && $pengaturanSekolah->login_background_path)
            <img src="{{ asset('storage/' . $pengaturanSekolah->login_background_path) }}" class="w-full h-full object-cover object-center opacity-40">
        @else
            <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover object-center opacity-40">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/50 mix-blend-multiply"></div>
        <!-- Decorative Blobs -->
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-indigo-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-5xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 text-white shadow-xl mb-6">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7"></path></svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
                Pilih <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-300 drop-shadow-sm">Portal Admin</span>
            </h1>
            <p class="text-lg text-slate-300 max-w-2xl mx-auto">
                Sistem Presensi Berbasis Barcode memiliki beberapa area manajemen. Silakan pilih portal yang sesuai dengan hak akses Anda.
            </p>
        </div>

        <!-- Portal Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Panel Super Admin --}}
            <a href="{{ url('/admin/login') }}" class="group block p-8 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-3xl border border-white/20 hover:border-amber-400/50 hover:shadow-2xl hover:shadow-amber-500/20 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-400/0 to-amber-500/0 group-hover:from-amber-400/10 group-hover:to-amber-500/10 transition-colors duration-300"></div>
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-slate-800 text-slate-300 group-hover:bg-amber-500 group-hover:text-white rounded-2xl flex items-center justify-center mb-5 transition-colors duration-300 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Super Admin</h3>
                    <p class="text-sm text-slate-300">Pengaturan Sistem & Manajemen Akses</p>
                </div>
            </a>

            {{-- Panel Data Master & Akademik --}}
            <a href="{{ url('/admin-akademik/login') }}" class="group block p-8 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-3xl border border-white/20 hover:border-emerald-400/50 hover:shadow-2xl hover:shadow-emerald-500/20 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/0 to-emerald-500/0 group-hover:from-emerald-400/10 group-hover:to-emerald-500/10 transition-colors duration-300"></div>
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-slate-800 text-slate-300 group-hover:bg-emerald-500 group-hover:text-white rounded-2xl flex items-center justify-center mb-5 transition-colors duration-300 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Data Master & Akademik</h3>
                    <p class="text-sm text-slate-300">Data Induk, Pembagian Kelas & Mutasi</p>
                </div>
            </a>

            {{-- Panel Presensi --}}
            <a href="{{ url('/admin-presensi/login') }}" class="group block p-8 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-3xl border border-white/20 hover:border-orange-400/50 hover:shadow-2xl hover:shadow-orange-500/20 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-400/0 to-orange-500/0 group-hover:from-orange-400/10 group-hover:to-orange-500/10 transition-colors duration-300"></div>
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-slate-800 text-slate-300 group-hover:bg-orange-500 group-hover:text-white rounded-2xl flex items-center justify-center mb-5 transition-colors duration-300 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Presensi</h3>
                    <p class="text-sm text-slate-300">Input Manual & Rekap Kehadiran</p>
                </div>
            </a>

            {{-- Panel Perpustakaan --}}
            <a href="{{ url('/admin-perpustakaan/login') }}" class="group block p-8 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-3xl border border-white/20 hover:border-blue-400/50 hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-400/0 to-blue-500/0 group-hover:from-blue-400/10 group-hover:to-blue-500/10 transition-colors duration-300"></div>
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-slate-800 text-slate-300 group-hover:bg-blue-500 group-hover:text-white rounded-2xl flex items-center justify-center mb-5 transition-colors duration-300 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Perpustakaan</h3>
                    <p class="text-sm text-slate-300">Manajemen Koleksi & Sirkulasi Buku</p>
                </div>
            </a>
            
        </div>

        <div class="text-center mt-12">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Halaman Utama
            </a>
        </div>
    </div>
</div>

</body>
</html>
