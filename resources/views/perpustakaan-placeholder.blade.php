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

<div class="min-h-screen flex flex-col items-center justify-center bg-slate-900 relative overflow-hidden p-4 sm:p-8">
    <!-- Global Background -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/50 mix-blend-multiply"></div>
        <!-- Decorative Blobs -->
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-teal-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-2xl mx-auto text-center">
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
