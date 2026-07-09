<!-- Stats Today -->
@if(!empty($todayStats))
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Hadir -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hadir Hari Ini</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-black text-slate-800">{{ $todayStats['hadir'] }}<span class="text-base font-semibold text-slate-400">/{{ $todayStats['total'] }}</span></h3>
                <span class="text-sm font-bold text-emerald-500">({{ $todayStats['persentase_hadir'] }}%)</span>
            </div>
        </div>
    </div>
    
    <!-- Telat -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Terlambat</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $todayStats['telat'] }} <span class="text-base font-semibold text-slate-400">Siswa</span></h3>
        </div>
    </div>

    <!-- Absen (S/I/A) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center text-red-600">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tidak Hadir (S/I/A)</p>
            <h3 class="text-2xl font-black text-red-500 mt-1">{{ $todayStats['sakit'] + $todayStats['izin'] + $todayStats['alpa'] }} <span class="text-base font-semibold text-slate-400">Siswa</span></h3>
        </div>
    </div>

    <!-- Belum Absen -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Belum Ada Data</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $todayStats['belum'] }} <span class="text-base font-semibold text-slate-400">Siswa</span></h3>
        </div>
    </div>
</div>
@endif
