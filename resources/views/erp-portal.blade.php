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
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-blue-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-teal-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-4xl mx-auto">
        <!-- Header -->
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

        <!-- Portal Grid -->
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
            <a href="{{ url('/login') }}" class="hover:text-white transition-colors underline underline-offset-4">Masuk ke Portal Admin</a>
        </div>
    </div>
</div>

</body>
</html>
