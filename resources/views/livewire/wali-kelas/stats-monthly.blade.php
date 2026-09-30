@if(!empty($classMonthlyStats) || !empty($todayStats))
<div class="mb-6 mt-6">
    <!-- Header Section with Title & Mode Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight" x-text="rekapMode === 'harian' ? 'Rekapitulasi Harian' : 'Rekapitulasi Bulanan'">
                    Rekapitulasi Harian
                </h2>
                <template x-if="rekapMode === 'harian'">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Hari Ini
                    </span>
                </template>
                <template x-if="rekapMode === 'bulanan'">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        1 Bulan
                    </span>
                </template>
            </div>

            <!-- Subtitle Harian -->
            <p x-show="rekapMode === 'harian'" class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                Presensi: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($todayDate ?? now())->locale('id')->translatedFormat('l, d F Y') }}</strong>
                &bull; Total: <strong class="text-slate-700">{{ $todayStats['total'] ?? 0 }} Siswa</strong>
                @if(($todayStats['total'] ?? 0) > 0)
                    &bull; Kehadiran: <strong class="text-emerald-600">{{ $todayStats['persentase_hadir'] ?? 0 }}%</strong>
                @endif
            </p>

            <!-- Subtitle Bulanan -->
            <p x-show="rekapMode === 'bulanan'" style="display: none;" class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                Potensi Maksimal: <strong class="text-slate-700">{{ $classMonthlyStats['max_possible'] ?? 0 }}</strong> 
                (Total {{ $classMonthlyStats['total_students'] ?? 0 }} Siswa &times; {{ $classMonthlyStats['effective_days'] ?? 0 }} Hari Efektif {{ !empty($selectedMonthYear) && !empty($availableMonths[$selectedMonthYear]) ? '• ' . $availableMonths[$selectedMonthYear] : '' }})
            </p>
        </div>

        <!-- Mode Toggle Pill -->
        <div class="inline-flex p-1 bg-slate-200/80 rounded-2xl border border-slate-300/60 shadow-inner self-start sm:self-auto">
            <button type="button" 
                @click="rekapMode = 'harian'"
                :class="rekapMode === 'harian' ? 'bg-white text-brand-primary shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Rekap Harian
            </button>
            <button type="button" 
                @click="rekapMode = 'bulanan'"
                :class="rekapMode === 'bulanan' ? 'bg-white text-brand-primary shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Rekap Bulanan
            </button>
        </div>
    </div>

    <!-- ==================== MODE HARIAN (2 Kolom di HP, 4 Kolom di Desktop) ==================== -->
    <div x-show="rekapMode === 'harian'" class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-5 relative z-20">
        <!-- Hadir Tepat Waktu -->
        <div @click="dailyFilter = (dailyFilter === 'hadir' ? 'all' : 'hadir')" 
            :class="dailyFilter === 'hadir' ? 'ring-4 ring-emerald-300 shadow-2xl scale-[1.02]' : 'hover:shadow-xl hover:-translate-y-0.5'"
            class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group cursor-pointer active:scale-95">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                @if(($todayStats['total'] ?? 0) > 0)
                    <span class="text-[10px] sm:text-xs font-bold text-emerald-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">
                        {{ round((($todayStats['hadir'] ?? 0) / $todayStats['total']) * 100, 0) }}%
                    </span>
                @endif
            </div>
            <div class="relative z-10">
                <p class="text-emerald-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Hadir Tepat Waktu</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $todayStats['hadir'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-emerald-100">/ {{ $todayStats['total'] ?? 0 }}</span>
                </h3>
                <p class="text-emerald-200/90 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">Klik untuk menyaring</p>
            </div>
        </div>

        <!-- Terlambat -->
        <div @click="dailyFilter = (dailyFilter === 'telat' ? 'all' : 'telat')"
            :class="dailyFilter === 'telat' ? 'ring-4 ring-amber-300 shadow-2xl scale-[1.02]' : 'hover:shadow-xl hover:-translate-y-0.5'"
            class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group cursor-pointer active:scale-95">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[10px] sm:text-xs font-bold text-amber-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">
                    {{ ($todayStats['telat'] ?? 0) > 0 ? 'Telat' : 'Nihil' }}
                </span>
            </div>
            <div class="relative z-10">
                <p class="text-amber-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Terlambat</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $todayStats['telat'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-amber-100">/ {{ $todayStats['total'] ?? 0 }}</span>
                </h3>
                <p class="text-amber-200/90 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">Klik untuk menyaring</p>
            </div>
        </div>

        <!-- Sakit / Izin -->
        <div @click="dailyFilter = (dailyFilter === 'izin' ? 'all' : 'izin')"
            :class="dailyFilter === 'izin' ? 'ring-4 ring-rose-300 shadow-2xl scale-[1.02]' : 'hover:shadow-xl hover:-translate-y-0.5'"
            class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group cursor-pointer active:scale-95">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <span class="text-[10px] sm:text-xs font-bold text-rose-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">
                    {{ $todayStats['sakit'] ?? 0 }}S &bull; {{ $todayStats['izin'] ?? 0 }}I
                </span>
            </div>
            <div class="relative z-10">
                <p class="text-rose-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Sakit / Izin</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ ($todayStats['sakit'] ?? 0) + ($todayStats['izin'] ?? 0) }} <span class="text-xs sm:text-base font-medium text-rose-100">/ {{ $todayStats['total'] ?? 0 }}</span>
                </h3>
                <p class="text-rose-200/90 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">Klik untuk menyaring</p>
            </div>
        </div>

        <!-- Belum Absen Hari Ini -->
        @php
            $belumCount = $todayStats['belum'] ?? 0;
            $isSemuaAbsen = ($belumCount === 0);
        @endphp
        <div @click="dailyFilter = (dailyFilter === 'belum' ? 'all' : 'belum')"
            :class="dailyFilter === 'belum' ? 'ring-4 ring-indigo-300 shadow-2xl scale-[1.02]' : 'hover:shadow-xl hover:-translate-y-0.5'"
            class="bg-gradient-to-br {{ $isSemuaAbsen ? 'from-indigo-600 to-violet-700' : 'from-slate-700 to-slate-900' }} rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group cursor-pointer active:scale-95">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    @if($isSemuaAbsen)
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    @else
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    @endif
                </div>
                @if($isSemuaAbsen)
                    <span class="text-[10px] sm:text-xs font-bold text-emerald-200 bg-emerald-500/30 border border-emerald-400/40 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Tuntas
                    </span>
                @else
                    <span class="text-[10px] sm:text-xs font-bold text-amber-200 bg-amber-500/30 border border-amber-400/40 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">
                        Perlu Tindakan
                    </span>
                @endif
            </div>
            <div class="relative z-10">
                <p class="{{ $isSemuaAbsen ? 'text-indigo-100' : 'text-slate-300' }} text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Belum Absen</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $belumCount }} <span class="text-xs sm:text-base font-medium {{ $isSemuaAbsen ? 'text-indigo-200' : 'text-slate-300' }}">/ {{ $todayStats['total'] ?? 0 }}</span>
                </h3>
                @if($isSemuaAbsen)
                    <p class="text-indigo-200/90 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">✓ Lengkap seluruh siswa</p>
                @elseif(($todayStats['alpa'] ?? 0) > 0)
                    <p class="text-rose-200 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">&bull; {{ $todayStats['alpa'] }} Alpa hari ini</p>
                @else
                    <p class="text-slate-300 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">Klik untuk lihat siswa</p>
                @endif
            </div>
        </div>
    </div>

    <!-- ==================== MODE BULANAN (2 Kolom di HP, 4 Kolom di Desktop) ==================== -->
    <div x-show="rekapMode === 'bulanan'" style="display: none;" class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-5 relative z-20">
        <!-- Hadir Bulanan -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                @if(($classMonthlyStats['max_possible'] ?? 0) > 0)
                    <span class="text-[10px] sm:text-xs font-bold text-emerald-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">{{ round(($classMonthlyStats['hadir'] / $classMonthlyStats['max_possible']) * 100, 1) }}%</span>
                @endif
            </div>
            <div class="relative z-10">
                <p class="text-emerald-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Total Hadir</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $classMonthlyStats['hadir'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-emerald-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
                </h3>
            </div>
        </div>
        
        <!-- Telat Bulanan -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-amber-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Total Telat</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $classMonthlyStats['telat'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-amber-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
                </h3>
            </div>
        </div>

        <!-- Absen (S/I) Bulanan -->
        <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-rose-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Total Sakit/Izin</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ ($classMonthlyStats['sakit'] ?? 0) + ($classMonthlyStats['izin'] ?? 0) }} <span class="text-xs sm:text-base font-medium text-rose-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
                </h3>
            </div>
        </div>

        <!-- Alpa Bulanan -->
        <div class="bg-gradient-to-br from-slate-600 to-slate-700 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-slate-300 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Total Alpa</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $classMonthlyStats['alpa'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-slate-300">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
                </h3>
            </div>
        </div>
    </div>
</div>
@endif
