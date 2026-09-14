@if(!empty($classMonthlyStats))
<div class="mb-6 mt-8">
    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Rekapitulasi Kehadiran Sholat Dhuhur</h2>
    <p class="text-sm text-slate-500 mt-1.5 font-medium">
        Potensi Kehadiran Maksimal: <strong class="text-slate-700">{{ $classMonthlyStats['max_possible'] ?? 0 }}</strong> 
        (Total {{ $classMonthlyStats['total_students'] ?? 0 }} Siswa &times; {{ $classMonthlyStats['effective_days'] ?? 0 }} Hari Efektif Sholat)
    </p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 relative z-20">
    <!-- Hadir Sholat -->
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg shadow-emerald-500/20 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            @if(($classMonthlyStats['max_possible'] ?? 0) > 0)
                <span class="text-sm font-bold text-emerald-50 bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">{{ round((($classMonthlyStats['hadir'] ?? 0) / $classMonthlyStats['max_possible']) * 100, 1) }}%</span>
            @endif
        </div>
        <div class="relative z-10">
            <p class="text-emerald-100 text-sm font-medium mb-1">Total Hadir Berjamaah</p>
            <h3 class="text-3xl font-black text-white flex items-baseline gap-2">
                {{ $classMonthlyStats['hadir'] ?? 0 }} <span class="text-lg font-medium text-emerald-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
            </h3>
        </div>
    </div>
    
    <!-- Ijin / Halangan -->
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/20 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            @if(($classMonthlyStats['max_possible'] ?? 0) > 0)
                <span class="text-sm font-bold text-blue-50 bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">{{ round((($classMonthlyStats['ijin'] ?? 0) / $classMonthlyStats['max_possible']) * 100, 1) }}%</span>
            @endif
        </div>
        <div class="relative z-10">
            <p class="text-blue-100 text-sm font-medium mb-1">Total Ijin / Berhalangan</p>
            <h3 class="text-3xl font-black text-white flex items-baseline gap-2">
                {{ $classMonthlyStats['ijin'] ?? 0 }} <span class="text-lg font-medium text-blue-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
            </h3>
        </div>
    </div>

    <!-- Tidak Hadir -->
    <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-lg shadow-rose-500/20 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            @if(($classMonthlyStats['max_possible'] ?? 0) > 0)
                <span class="text-sm font-bold text-rose-50 bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">{{ round((($classMonthlyStats['tidak_hadir'] ?? 0) / $classMonthlyStats['max_possible']) * 100, 1) }}%</span>
            @endif
        </div>
        <div class="relative z-10">
            <p class="text-rose-100 text-sm font-medium mb-1">Total Tidak Hadir</p>
            <h3 class="text-3xl font-black text-white flex items-baseline gap-2">
                {{ $classMonthlyStats['tidak_hadir'] ?? 0 }} <span class="text-lg font-medium text-rose-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
            </h3>
        </div>
    </div>

    <!-- Rata-rata Kehadiran -->
    <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl shadow-lg shadow-slate-700/20 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <span class="text-sm font-bold text-slate-300 bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm">{{ $classMonthlyStats['effective_days'] ?? 0 }} Hari Sholat</span>
        </div>
        <div class="relative z-10">
            <p class="text-slate-300 text-sm font-medium mb-1">% Kepatuhan Berjamaah</p>
            <h3 class="text-3xl font-black text-white flex items-baseline gap-2">
                @if(($classMonthlyStats['max_possible'] ?? 0) > 0)
                    {{ round((($classMonthlyStats['hadir'] ?? 0) / $classMonthlyStats['max_possible']) * 100, 1) }}%
                @else
                    0%
                @endif
            </h3>
        </div>
    </div>
</div>
@endif
