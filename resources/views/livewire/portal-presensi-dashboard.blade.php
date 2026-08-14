<div>
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Presensi</h1>
        <p class="text-sm sm:text-base text-slate-500 mt-1">Ringkasan kehadiran siswa hari ini ({{ \Carbon\Carbon::today('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}).</p>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-6 mb-8">
        <!-- Hadir -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="p-2.5 bg-emerald-100 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-sm font-semibold text-slate-500 mb-1">Hadir</p>
                <h3 class="text-3xl font-extrabold text-slate-900">{{ $hadir }}</h3>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="p-2.5 bg-amber-100 text-amber-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-sm font-semibold text-slate-500 mb-1">Terlambat</p>
                <h3 class="text-3xl font-extrabold text-slate-900">{{ $telat }}</h3>
            </div>
        </div>

        <!-- Izin -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="p-2.5 bg-blue-100 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-sm font-semibold text-slate-500 mb-1">Izin</p>
                <h3 class="text-3xl font-extrabold text-slate-900">{{ $izin }}</h3>
            </div>
        </div>

        <!-- Sakit -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-purple-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="p-2.5 bg-purple-100 text-purple-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-sm font-semibold text-slate-500 mb-1">Sakit</p>
                <h3 class="text-3xl font-extrabold text-slate-900">{{ $sakit }}</h3>
            </div>
        </div>

        <!-- Alpa -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-rose-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="p-2.5 bg-rose-100 text-rose-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-sm font-semibold text-slate-500 mb-1">Alpa</p>
                <h3 class="text-3xl font-extrabold text-slate-900">{{ $alpa }}</h3>
            </div>
        </div>
    </div>
</div>
