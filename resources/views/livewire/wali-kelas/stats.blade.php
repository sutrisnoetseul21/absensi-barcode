<!-- Stats Today -->
@if(!empty($todayStats))
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 relative z-20">
    <!-- Hadir -->
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg shadow-emerald-500/20 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <span class="text-sm font-bold text-emerald-50 bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">{{ $todayStats['persentase_hadir'] }}%</span>
        </div>
        <div class="relative z-10">
            <p class="text-emerald-100 text-sm font-medium mb-1">Hadir Hari Ini</p>
            <h3 class="text-3xl font-black text-white flex items-baseline gap-2">
                {{ $todayStats['hadir'] }} <span class="text-lg font-medium text-emerald-100">/ {{ $todayStats['total'] }}</span>
            </h3>
        </div>
    </div>
    
    <!-- Telat -->
    <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl shadow-lg shadow-amber-500/20 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>
        <div class="relative z-10">
            <p class="text-amber-100 text-sm font-medium mb-1">Terlambat</p>
            <h3 class="text-3xl font-black text-white flex items-baseline gap-2">
                {{ $todayStats['telat'] }} <span class="text-lg font-medium text-amber-100">Siswa</span>
            </h3>
        </div>
    </div>

    <!-- Absen (S/I/A) -->
    <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-lg shadow-rose-500/20 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5" /></svg>
            </div>
        </div>
        <div class="relative z-10">
            <p class="text-rose-100 text-sm font-medium mb-1">Tidak Hadir (S/I/A)</p>
            <h3 class="text-3xl font-black text-white flex items-baseline gap-2">
                {{ $todayStats['sakit'] + $todayStats['izin'] + $todayStats['alpa'] }} <span class="text-lg font-medium text-rose-100">Siswa</span>
            </h3>
        </div>
    </div>

    <!-- Belum Absen -->
    <div class="bg-gradient-to-br from-slate-600 to-slate-700 rounded-2xl shadow-lg shadow-slate-500/20 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>
        <div class="relative z-10">
            <p class="text-slate-300 text-sm font-medium mb-1">Belum Presensi</p>
            <h3 class="text-3xl font-black text-white flex items-baseline gap-2">
                {{ $todayStats['belum'] }} <span class="text-lg font-medium text-slate-300">Siswa</span>
            </h3>
        </div>
    </div>
</div>
@endif
