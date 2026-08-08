<div class="p-4 lg:p-8 space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header / Welcome -->
    <div class="bg-gradient-to-r from-brand-primary to-indigo-600 rounded-3xl p-8 text-white shadow-lg shadow-brand-primary/20 relative overflow-hidden">
        <div class="relative z-10">
            <h1 class="text-3xl font-extrabold mb-2">Selamat Datang, {{ $teacher?->name ?? 'Guru' }}!</h1>
            <p class="text-indigo-100 max-w-2xl text-lg">Ini adalah halaman utama Portal Guru. Pantau presensi kelas yang Anda ampu serta status perpustakaan dari sini.</p>
        </div>
        
        <!-- Decorative shapes -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-10 blur-2xl"></div>
        <div class="absolute bottom-0 right-32 -mb-16 w-48 h-48 rounded-full bg-indigo-400 opacity-20 blur-xl"></div>
    </div>

    <!-- Announcements Section (if any) -->
    @if($activeAnnouncements->count() > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
            <div class="flex items-start gap-4">
                <div class="bg-amber-100 p-3 rounded-2xl text-amber-600 flex-shrink-0 mt-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-amber-900 mb-4">Pengumuman Terbaru</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($activeAnnouncements as $announcement)
                            <div class="bg-white/60 p-4 rounded-xl border border-amber-100/50">
                                <h4 class="font-bold text-slate-800">{{ $announcement->judul }}</h4>
                                <p class="text-sm text-slate-600 mt-1">{{ $announcement->isi }}</p>
                                <span class="text-xs text-amber-700/70 font-medium mt-2 block">{{ $announcement->created_at->format('d M Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kehadiran Widget -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col items-center text-center hover:shadow-md transition-shadow group">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-emerald-100 transition-all">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <h3 class="text-slate-500 font-medium mb-1">Total Kelas yang Diampu</h3>
            <div class="text-4xl font-extrabold text-emerald-500 tracking-tight">
                {{ $kelasAmpuCount }} <span class="text-xl text-slate-500 font-medium">Kelas</span>
            </div>
            <p class="text-sm text-slate-400 mt-2">Kelas yang terhubung dengan akun Anda tahun ajaran ini.</p>
            
            <a href="{{ route('portal-guru.akademik') }}" class="mt-6 w-full py-3 px-4 bg-slate-50 hover:bg-brand-primary hover:text-white text-brand-primary font-bold rounded-xl transition-colors flex justify-center items-center gap-2">
                Buka Presensi & Akademik
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </a>
        </div>

        <!-- Perpustakaan Widget -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col items-center text-center hover:shadow-md transition-shadow group">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-amber-100 transition-all">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            </div>
            <h3 class="text-slate-500 font-medium mb-1">Pinjaman Buku Pribadi</h3>
            <div class="text-4xl font-extrabold text-slate-800 tracking-tight">
                {{ $activeBooksCount }} <span class="text-xl text-slate-500 font-medium">Buku</span>
            </div>
            <p class="text-sm text-slate-400 mt-2">Buku yang sedang Anda pinjam saat ini dari perpustakaan.</p>
            
            <a href="{{ route('portal-guru.perpustakaan') }}" class="mt-6 w-full py-3 px-4 bg-slate-50 hover:bg-brand-primary hover:text-white text-brand-primary font-bold rounded-xl transition-colors flex justify-center items-center gap-2">
                Buka Perpustakaan
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </a>
        </div>
    </div>
</div>
