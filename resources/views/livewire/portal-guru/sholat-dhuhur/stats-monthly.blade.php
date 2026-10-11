@if(!empty($classMonthlyStats) || !empty($todayStats))
<div class="mb-6 mt-6">
    <!-- Header Section with Title & Mode Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight" x-text="rekapMode === 'harian' ? 'Presensi Sholat Dhuhur Hari Ini' : 'Rekapitulasi Sholat Dhuhur Bulanan'">
                    Presensi Sholat Dhuhur Hari Ini
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
                Tanggal: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($todayDate ?? now())->locale('id')->translatedFormat('l, d F Y') }}</strong>
                &bull; Total: <strong class="text-slate-700">{{ $todayStats['total'] ?? 0 }} Siswa</strong>
                @if(($todayStats['total'] ?? 0) > 0)
                    &bull; Kehadiran: <strong class="text-emerald-600">{{ $todayStats['persentase_hadir'] ?? 0 }}%</strong>
                @endif
            </p>

            <!-- Subtitle Bulanan -->
            <p x-show="rekapMode === 'bulanan'" style="display: none;" class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                Potensi Maksimal: <strong class="text-slate-700">{{ $classMonthlyStats['max_possible'] ?? 0 }}</strong> 
                (Total {{ $classMonthlyStats['total_students'] ?? 0 }} Siswa &times; {{ $classMonthlyStats['effective_days'] ?? 0 }} Hari Sholat {{ !empty($selectedMonthYear) && !empty($availableMonths[$selectedMonthYear]) ? '• ' . $availableMonths[$selectedMonthYear] : '' }})
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

    <!-- Action Bar for Rekap Harian (Cetak Rekap Kelas, Export Excel, Rekap Harian) -->
    <div x-show="rekapMode === 'harian'" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 p-3 bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Tanggal:
                </span>
                <input type="date" wire:model.live="selectedDailyDate" class="py-1.5 px-3 text-xs bg-white rounded-xl border border-slate-200 font-bold text-slate-700 shadow-2xs focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition-all cursor-pointer">
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Koordinator:
                </span>
                <div class="relative">
                    <select wire:model.live="selectedKoordinatorId" class="py-1.5 pl-3 pr-8 text-xs bg-white rounded-xl border border-slate-200 font-bold text-slate-700 shadow-2xs focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition-all cursor-pointer max-w-[230px] truncate appearance-none">
                        @if(!empty($koordinators['pai']))
                            <optgroup label="Guru PAI">
                                @foreach($koordinators['pai'] as $guru)
                                    <option value="{{ $guru['id'] }}">{{ $guru['name'] }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if(!empty($koordinators['others']))
                            <optgroup label="Guru Lainnya">
                                @foreach($koordinators['others'] as $guru)
                                    <option value="{{ $guru['id'] }}">{{ $guru['name'] }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- PDF Cetak Rekap Per Kelas Button -->
            <button type="button" wire:click="downloadRekapKelasPdf" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-700 hover:opacity-95 shadow-md shadow-emerald-700/20 transition-all cursor-pointer">
                <svg wire:loading.remove wire:target="downloadRekapKelasPdf" class="w-3.5 h-3.5 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <svg wire:loading wire:target="downloadRekapKelasPdf" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Cetak Rekap Kelas (PDF)</span>
            </button>

            <!-- Export Excel Button -->
            <button type="button" wire:click="exportExcel" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                <svg wire:loading.remove wire:target="exportExcel" class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <svg wire:loading wire:target="exportExcel" class="w-3.5 h-3.5 animate-spin text-emerald-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Export Excel</span>
            </button>

            <!-- PDF Cetak Rekap Harian Button -->
            <button type="button" wire:click="downloadRekapHarianPdf" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                <svg wire:loading.remove wire:target="downloadRekapHarianPdf" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <svg wire:loading wire:target="downloadRekapHarianPdf" class="w-3.5 h-3.5 animate-spin text-slate-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Rekap Harian</span>
            </button>
        </div>
    </div>

    <!-- ==================== MODE HARIAN (2 Kolom di HP, 4 Kolom di Desktop) ==================== -->
    <div x-show="rekapMode === 'harian'" class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-5 relative z-20">
        <!-- Hadir Berjamaah -->
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
                        {{ round($todayStats['persentase_hadir'] ?? 0, 0) }}%
                    </span>
                @endif
            </div>
            <div class="relative z-10">
                <p class="text-emerald-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Hadir Berjamaah</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $todayStats['hadir'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-emerald-100">/ {{ $todayStats['total'] ?? 0 }}</span>
                </h3>
                <p class="text-emerald-200/90 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">Klik untuk menyaring</p>
            </div>
        </div>

        <!-- Ijin / Halangan -->
        <div @click="dailyFilter = (dailyFilter === 'ijin' ? 'all' : 'ijin')"
            :class="dailyFilter === 'ijin' ? 'ring-4 ring-blue-300 shadow-2xl scale-[1.02]' : 'hover:shadow-xl hover:-translate-y-0.5'"
            class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group cursor-pointer active:scale-95">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[10px] sm:text-xs font-bold text-blue-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">
                    Haid / Sakit
                </span>
            </div>
            <div class="relative z-10">
                <p class="text-blue-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Ijin / Halangan</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $todayStats['ijin'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-blue-100">/ {{ $todayStats['total'] ?? 0 }}</span>
                </h3>
                <p class="text-blue-200/90 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">Klik untuk menyaring</p>
            </div>
        </div>

        <!-- Tidak Hadir / Alpa -->
        <div @click="dailyFilter = (dailyFilter === 'tidak_hadir' ? 'all' : 'tidak_hadir')"
            :class="dailyFilter === 'tidak_hadir' ? 'ring-4 ring-rose-300 shadow-2xl scale-[1.02]' : 'hover:shadow-xl hover:-translate-y-0.5'"
            class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group cursor-pointer active:scale-95">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </div>
                <span class="text-[10px] sm:text-xs font-bold text-rose-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">
                    {{ ($todayStats['tidak_hadir'] ?? 0) > 0 ? 'Perlu Pantau' : 'Nihil' }}
                </span>
            </div>
            <div class="relative z-10">
                <p class="text-rose-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Tidak Hadir</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $todayStats['tidak_hadir'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-rose-100">/ {{ $todayStats['total'] ?? 0 }}</span>
                </h3>
                <p class="text-rose-200/90 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">Klik untuk menyaring</p>
            </div>
        </div>

        <!-- Belum Absen Sholat -->
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
                <p class="{{ $isSemuaAbsen ? 'text-indigo-100' : 'text-slate-300' }} text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Belum Absen Sholat</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $belumCount }} <span class="text-xs sm:text-base font-medium {{ $isSemuaAbsen ? 'text-indigo-200' : 'text-slate-300' }}">/ {{ $todayStats['total'] ?? 0 }}</span>
                </h3>
                @if($isSemuaAbsen)
                    <p class="text-indigo-200/90 text-[10px] sm:text-xs mt-1 font-medium hidden sm:block">✓ Lengkap seluruh siswa</p>
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
                    <span class="text-[10px] sm:text-xs font-bold text-emerald-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">{{ round((($classMonthlyStats['hadir'] ?? 0) / $classMonthlyStats['max_possible']) * 100, 1) }}%</span>
                @endif
            </div>
            <div class="relative z-10">
                <p class="text-emerald-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Total Hadir</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $classMonthlyStats['hadir'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-emerald-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
                </h3>
            </div>
        </div>
        
        <!-- Ijin / Halangan Bulanan -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                @if(($classMonthlyStats['max_possible'] ?? 0) > 0)
                    <span class="text-[10px] sm:text-xs font-bold text-blue-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">{{ round((($classMonthlyStats['ijin'] ?? 0) / $classMonthlyStats['max_possible']) * 100, 1) }}%</span>
                @endif
            </div>
            <div class="relative z-10">
                <p class="text-blue-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Total Ijin/Halangan</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $classMonthlyStats['ijin'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-blue-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
                </h3>
            </div>
        </div>

        <!-- Alpa Bulanan -->
        <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </div>
                @if(($classMonthlyStats['max_possible'] ?? 0) > 0)
                    <span class="text-[10px] sm:text-xs font-bold text-rose-50 bg-white/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">{{ round((($classMonthlyStats['tidak_hadir'] ?? 0) / $classMonthlyStats['max_possible']) * 100, 1) }}%</span>
                @endif
            </div>
            <div class="relative z-10">
                <p class="text-rose-100 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">Total Alpa</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $classMonthlyStats['tidak_hadir'] ?? 0 }} <span class="text-xs sm:text-base font-medium text-rose-100">/ {{ $classMonthlyStats['max_possible'] ?? 0 }}</span>
                </h3>
            </div>
        </div>

        <!-- Rata-rata Persentase Kepatuhan -->
        <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl shadow-md p-3.5 sm:p-5 flex flex-col justify-between transition-all duration-200 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-2 sm:mb-4 relative z-10">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="text-[10px] sm:text-xs font-bold text-slate-300 bg-white/10 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full backdrop-blur-sm">{{ $classMonthlyStats['effective_days'] ?? 0 }} Hari</span>
            </div>
            <div class="relative z-10">
                <p class="text-slate-300 text-[11px] sm:text-sm font-semibold truncate mb-0.5 sm:mb-1">% Kepatuhan</p>
                <h3 class="text-xl sm:text-3xl font-black text-white flex items-baseline gap-1 sm:gap-2">
                    {{ $classMonthlyStats['class_percentage'] ?? 0 }}%
                </h3>
            </div>
        </div>
    </div>
</div>
@endif
