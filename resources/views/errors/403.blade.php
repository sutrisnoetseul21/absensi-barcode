<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden text-center p-8 border border-gray-100 dark:border-slate-700 relative">
        
        <!-- Decorative blobs -->
        <div class="absolute -top-10 -left-10 w-32 h-32 bg-red-500 rounded-full mix-blend-multiply filter blur-2xl opacity-20 dark:opacity-40"></div>
        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-orange-500 rounded-full mix-blend-multiply filter blur-2xl opacity-20 dark:opacity-40"></div>

        <div class="relative z-10">
            <div class="w-24 h-24 bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-red-100 dark:border-red-500/20">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-3 tracking-tight">Akses Ditolak</h1>
            
            <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed font-medium">
                Maaf, Anda tidak diizinkan atau tidak punya wewenang untuk mengakses halaman tersebut.
            </p>
            
            <div class="flex flex-col gap-3">
                <button onclick="window.history.back()" class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 dark:bg-amber-600 dark:hover:bg-amber-500 text-white rounded-xl font-semibold shadow-lg shadow-slate-900/20 dark:shadow-amber-900/20 transition-all active:scale-[0.98]">
                    Kembali ke Sebelumnya
                </button>
                <a href="{{ url('/') }}" class="w-full py-3 px-4 bg-white hover:bg-slate-50 dark:bg-slate-700/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-semibold border border-slate-200 dark:border-slate-600 transition-all active:scale-[0.98]">
                    Ke Halaman Utama
                </a>
            </div>
        </div>
    </div>
</body>
</html>
