<div class="min-h-full pb-16 font-jakarta">
    <!-- Header Section -->
    <div class="mb-6 lg:mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-brand-primary animate-pulse"></span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Presensi</h1>
                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                    Live Data
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                Rekapitulasi kehadiran harian siswa seluruh kelas untuk <strong class="text-slate-700">{{ \Carbon\Carbon::parse($selectedDate)->locale('id')->translatedFormat('l, d F Y') }}</strong>.
            </p>
        </div>

        <!-- Right Action Controls (Date, Academic Year & PDF Buttons) -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Date Picker -->
            <div class="relative">
                <input type="date" wire:model.live="selectedDate" 
                    class="pl-3 pr-2 py-2 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 font-bold text-slate-700 shadow-xs focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all cursor-pointer">
            </div>

            <!-- Academic Year Select -->
            <div class="relative">
                <select wire:model.live="selectedAcademicYearId" 
                    class="py-2 pl-3 pr-8 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 font-bold text-slate-700 shadow-xs focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all cursor-pointer">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- PDF Cetak Rekap Per Kelas Button (Brand Primary) -->
            <button type="button" wire:click="downloadRekapKelasPdf" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-95 shadow-md shadow-brand-primary/20 transition-all cursor-pointer">
                <svg wire:loading.remove wire:target="downloadRekapKelasPdf" class="w-4 h-4 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <svg wire:loading wire:target="downloadRekapKelasPdf" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Cetak Rekap Kelas (PDF)</span>
            </button>

            <!-- Export Excel Button -->
            <button type="button" wire:click="exportExcel" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 shadow-xs hover:shadow-sm transition-all cursor-pointer">
                <svg wire:loading.remove wire:target="exportExcel" class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <svg wire:loading wire:target="exportExcel" class="w-4 h-4 animate-spin text-emerald-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Export Excel</span>
            </button>

            <!-- PDF Cetak Rekap Harian Button -->
            <button type="button" wire:click="downloadRekapHarianPdf" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs hover:shadow-sm transition-all cursor-pointer">
                <svg wire:loading.remove wire:target="downloadRekapHarianPdf" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <svg wire:loading wire:target="downloadRekapHarianPdf" class="w-4 h-4 animate-spin text-slate-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span class="hidden sm:inline">Rekap Harian</span>
                <span class="sm:hidden">Rekap</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SPOTLIGHT HERO CARD (SESUAI TEMA PORTAL: BRAND PRIMARY / INDIGO) -->
    <!-- ========================================================================= -->
    <div class="mb-6 relative overflow-hidden bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary border border-brand-primary/30 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20">
        <!-- Decorative Glow Blobs (Matching guru-main-dashboard & wali-kelas/header) -->
        <div class="absolute -top-12 -right-12 w-80 h-80 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-80 h-80 bg-brand-secondary/20 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/25 mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>REKAPITULASI PRESENSI HARIAN</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-snug">
                    Total Siswa Hadir Hari Ini
                </h2>
                <p class="text-xs sm:text-sm text-white/85 mt-1 max-w-xl font-medium leading-relaxed">
                    Jumlah siswa yang hadir fisik di sekolah hari ini (hadir tepat waktu & terlambat). Data tersinkronisasi otomatis dengan scan barcode presensi.
                </p>
            </div>

            <!-- Big Metric Highlight -->
            <div class="flex items-center gap-4 bg-white/15 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/25 shrink-0 self-start md:self-auto shadow-md">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-white/80 uppercase tracking-wider block">Siswa Hadir</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl sm:text-4xl font-extrabold text-white">{{ $stats['total_hadir'] ?? 0 }}</span>
                        <span class="text-sm font-semibold text-white/80">/ {{ $stats['total_students'] ?? 0 }} siswa</span>
                    </div>
                    <span class="text-[11px] font-bold text-white/90 mt-0.5 block">
                        {{ $stats['persentase_hadir'] ?? 0 }}% Kehadiran Sekolah
                    </span>
                </div>
            </div>
        </div>

        <!-- Sub Breakdown Badges -->
        <div class="mt-6 pt-4 border-t border-white/20 flex flex-wrap items-center gap-3 text-xs font-semibold">
            <span class="inline-flex items-center gap-1.5 bg-white/15 px-3 py-1.5 rounded-xl backdrop-blur-md border border-white/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Hadir Tepat: <strong class="text-white">{{ $stats['hadir'] ?? 0 }}</strong></span>
            </span>
            <span class="inline-flex items-center gap-1.5 bg-white/15 px-3 py-1.5 rounded-xl backdrop-blur-md border border-white/20">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Terlambat: <strong class="text-white">{{ $stats['telat'] ?? 0 }}</strong></span>
            </span>
            <span class="inline-flex items-center gap-1.5 bg-white/15 px-3 py-1.5 rounded-xl backdrop-blur-md border border-white/20">
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                <span>Tidak Hadir (S/I/A): <strong class="text-white">{{ $stats['tidak_hadir'] ?? 0 }}</strong></span>
            </span>
            <span class="inline-flex items-center gap-1.5 bg-white/15 px-3 py-1.5 rounded-xl backdrop-blur-md border border-white/20">
                <span class="w-2 h-2 rounded-full {{ ($stats['belum'] ?? 0) > 0 ? 'bg-amber-300 animate-pulse' : 'bg-emerald-300' }}"></span>
                <span>Belum Presensi: <strong class="text-white">{{ $stats['belum'] ?? 0 }}</strong></span>
            </span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STATISTIK CARDS (GRID 2x2 DI HP, 5 KOLOM DI DESKTOP) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-5 mb-8">
        <!-- Hadir -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-3 -top-3 w-14 h-14 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-3">
                <div class="p-2 sm:p-2.5 bg-emerald-100 text-emerald-600 rounded-xl">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Tepat</span>
            </div>
            <div class="relative z-10">
                <p class="text-xs sm:text-sm font-semibold text-slate-500 mb-0.5">Hadir Tepat Waktu</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $stats['hadir'] ?? 0 }}</h3>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-3 -top-3 w-14 h-14 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-3">
                <div class="p-2 sm:p-2.5 bg-amber-100 text-amber-600 rounded-xl">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Telat</span>
            </div>
            <div class="relative z-10">
                <p class="text-xs sm:text-sm font-semibold text-slate-500 mb-0.5">Terlambat</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $stats['telat'] ?? 0 }}</h3>
            </div>
        </div>

        <!-- Sakit / Izin -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-3 -top-3 w-14 h-14 bg-sky-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-3">
                <div class="p-2 sm:p-2.5 bg-sky-100 text-sky-600 rounded-xl">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <span class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full">Izin / Sakit</span>
            </div>
            <div class="relative z-10">
                <p class="text-xs sm:text-sm font-semibold text-slate-500 mb-0.5">Izin & Sakit</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900">
                    {{ ($stats['izin'] ?? 0) + ($stats['sakit'] ?? 0) }}
                    <span class="text-xs font-normal text-slate-400">(S:{{ $stats['sakit'] ?? 0 }} I:{{ $stats['izin'] ?? 0 }})</span>
                </h3>
            </div>
        </div>

        <!-- Alpa -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-3 -top-3 w-14 h-14 bg-rose-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-3">
                <div class="p-2 sm:p-2.5 bg-rose-100 text-rose-600 rounded-xl">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full">Alpa</span>
            </div>
            <div class="relative z-10">
                <p class="text-xs sm:text-sm font-semibold text-slate-500 mb-0.5">Tanpa Keterangan</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $stats['alpa'] ?? 0 }}</h3>
            </div>
        </div>

        <!-- Belum Presensi (Interactive Card to Jump to Belum Tab) -->
        <div wire:click="$set('activeTab', 'belum_presensi')" 
            class="col-span-2 md:col-span-1 bg-gradient-to-br {{ ($stats['belum'] ?? 0) > 0 ? 'from-brand-primary to-brand-secondary ring-2 ring-brand-primary/30' : 'from-slate-700 to-slate-800' }} rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-lg transition-all text-white cursor-pointer group active:scale-95">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 sm:p-2.5 bg-white/20 backdrop-blur-md rounded-xl text-white">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                @if(($stats['belum'] ?? 0) === 0)
                    <span class="text-[10px] font-bold text-emerald-200 bg-emerald-500/30 px-2 py-0.5 rounded-full border border-emerald-400/30">
                        ✓ Tuntas
                    </span>
                @else
                    <span class="text-[10px] font-bold text-amber-200 bg-amber-500/30 px-2 py-0.5 rounded-full border border-amber-400/30 animate-pulse">
                        Perlu Pantau
                    </span>
                @endif
            </div>
            <div>
                <p class="text-xs sm:text-sm font-semibold text-white/90 mb-0.5">Belum Presensi</p>
                <h3 class="text-2xl sm:text-3xl font-black text-white flex items-baseline gap-1.5">
                    {{ $stats['belum'] ?? 0 }}
                    <span class="text-xs font-normal text-white/80">siswa</span>
                </h3>
                <p class="text-[10px] text-white/80 mt-1 font-medium group-hover:underline">Klik untuk lihat siswa &rarr;</p>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB NAVIGATION & FILTER BAR -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Tabs (Using brand-primary theme) -->
            <div class="inline-flex p-1 bg-slate-100 rounded-2xl border border-slate-200/60 self-start sm:self-auto">
                <button type="button" wire:click="$set('activeTab', 'per_kelas')"
                    class="px-3.5 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer font-bold {{ $activeTab === 'per_kelas' ? 'bg-white text-brand-primary shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Rekap Per Kelas</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'per_kelas' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-200 text-slate-700' }}">
                        {{ count($classesSummary) }}
                    </span>
                </button>

                <button type="button" wire:click="$set('activeTab', 'belum_presensi')"
                    class="px-3.5 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer font-bold {{ $activeTab === 'belum_presensi' ? 'bg-white text-brand-primary shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Belum Presensi</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ ($stats['belum'] ?? 0) > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">
                        {{ $stats['belum'] ?? 0 }}
                    </span>
                </button>

                <button type="button" wire:click="$set('activeTab', 'semua_siswa')"
                    class="px-3.5 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer font-bold {{ $activeTab === 'semua_siswa' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Semua Siswa</span>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full sm:w-72">
                <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari nama / NISN / kelas..."
                    class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary font-medium transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                @if($searchQuery)
                    <button wire:click="$set('searchQuery', '')" type="button" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                @endif
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: REKAPITULASI KEHADIRAN SISWA PER KELAS -->
        <!-- ========================================================================= -->
        @if($activeTab === 'per_kelas')
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                            <th class="py-4 px-4 w-12 text-center">No</th>
                            <th class="py-4 px-5">Kelas</th>
                            <th class="py-4 px-6">Wali Kelas</th>
                            <th class="py-4 px-4 text-center">Total Siswa</th>
                            <th class="py-4 px-4 text-center text-brand-primary bg-indigo-50/50">Siswa Hadir</th>
                            <th class="py-4 px-4 text-center">Terlambat</th>
                            <th class="py-4 px-4 text-center">Sakit</th>
                            <th class="py-4 px-4 text-center">Izin</th>
                            <th class="py-4 px-4 text-center">Alpa</th>
                            <th class="py-4 px-4 text-center text-rose-800 bg-rose-50/50">Belum Absen</th>
                            <th class="py-4 px-4 text-center">% Hadir</th>
                            <th class="py-4 px-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @php
                            $filteredClasses = collect($classesSummary)->filter(function($row) use ($searchQuery) {
                                if (empty($searchQuery)) return true;
                                return str_contains(strtolower($row['name']), strtolower($searchQuery)) ||
                                       str_contains(strtolower($row['wali_kelas']), strtolower($searchQuery));
                            });
                        @endphp

                        @forelse($filteredClasses as $index => $row)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-center font-bold text-slate-400 text-xs">{{ $loop->iteration }}</td>
                                <td class="py-3 px-5 font-black text-slate-900 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $row['belum'] === 0 ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' }}"></span>
                                        <span>Kelas {{ $row['name'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-slate-600 font-medium">
                                    {{ $row['wali_kelas'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-800">
                                    {{ $row['total'] }}
                                </td>
                                <td class="py-3 px-4 text-center bg-indigo-50/30">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                        {{ $row['total_hadir'] }} siswa
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-amber-700">
                                    {{ $row['telat'] ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-center text-slate-600">
                                    {{ $row['sakit'] ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-center text-slate-600">
                                    {{ $row['izin'] ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-rose-600">
                                    {{ $row['alpa'] ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-center bg-rose-50/30">
                                    @if($row['belum'] > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            {{ $row['belum'] }}
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-emerald-600">0 ✓</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <div class="w-12 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-brand-primary h-1.5 rounded-full" style="width: {{ min(100, $row['persen']) }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700 text-xs">{{ $row['persen'] }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-5 text-center">
                                    <button type="button" wire:click="filterByClass('{{ $row['id'] }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold text-brand-primary bg-brand-primary/10 hover:bg-brand-primary/20 border border-brand-primary/20 transition-all cursor-pointer">
                                        <span>Lihat Siswa</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="py-12 text-center text-slate-400">
                                    <p class="font-bold text-slate-600">Tidak ada data kelas yang sesuai.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100/80 font-black text-slate-800 text-xs sm:text-sm border-t-2 border-slate-200">
                        <tr>
                            <td colspan="3" class="py-4 px-6 text-right uppercase tracking-wider">Total Seluruh Sekolah:</td>
                            <td class="py-4 px-4 text-center">{{ $stats['total_students'] ?? 0 }}</td>
                            <td class="py-4 px-4 text-center text-brand-primary bg-indigo-100/50">
                                <span class="font-black text-sm">{{ $stats['total_hadir'] ?? 0 }} Siswa</span>
                            </td>
                            <td class="py-4 px-4 text-center text-amber-700">{{ $stats['telat'] ?? 0 }}</td>
                            <td class="py-4 px-4 text-center">{{ $stats['sakit'] ?? 0 }}</td>
                            <td class="py-4 px-4 text-center">{{ $stats['izin'] ?? 0 }}</td>
                            <td class="py-4 px-4 text-center text-rose-700">{{ $stats['alpa'] ?? 0 }}</td>
                            <td class="py-4 px-4 text-center {{ ($stats['belum'] ?? 0) > 0 ? 'text-rose-700 font-black' : 'text-emerald-700' }}">
                                {{ $stats['belum'] ?? 0 }}
                            </td>
                            <td class="py-4 px-4 text-center text-brand-primary font-black">{{ $stats['persentase_hadir'] ?? 0 }}%</td>
                            <td class="py-4 px-5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" wire:click="downloadRekapKelasPdf" class="text-xs font-bold text-brand-primary hover:underline">
                                        PDF
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" wire:click="exportExcel" class="text-xs font-bold text-emerald-600 hover:underline">
                                        Excel
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- TAB 2: DAFTAR SISWA BELUM PRESENSI HARI INI -->
        <!-- ========================================================================= -->
        @if($activeTab === 'belum_presensi')
            <div class="p-4 sm:p-5">
                <!-- Filter Dropdown Bar -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-500">Filter Kelas:</label>
                        <select wire:model.live="selectedClassFilter" class="text-xs sm:text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/30">
                            <option value="all">Semua Kelas ({{ count($unattendedStudents) }} siswa)</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedClassFilter !== 'all')
                        <button type="button" wire:click="$set('selectedClassFilter', 'all')" class="text-xs font-bold text-brand-primary hover:underline">
                            Reset ke Semua Kelas
                        </button>
                    @endif
                </div>

                @php
                    $filteredUnattended = collect($unattendedStudents)->filter(function($st) use ($selectedClassFilter, $searchQuery) {
                        if ($selectedClassFilter !== 'all' && $st['class_id'] !== $selectedClassFilter) {
                            return false;
                        }
                        if (!empty($searchQuery)) {
                            return str_contains(strtolower($st['name']), strtolower($searchQuery)) ||
                                   str_contains((string)$st['nisn'], $searchQuery) ||
                                   str_contains(strtolower($st['class_name']), strtolower($searchQuery));
                        }
                        return true;
                    });
                @endphp

                <!-- Celebration Banner if Belum is 0 -->
                @if($filteredUnattended->isEmpty() && empty($searchQuery))
                    <div class="p-10 text-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 rounded-3xl border border-indigo-200/80 my-4 shadow-xs">
                        <div class="w-16 h-16 mx-auto mb-3 bg-brand-primary text-white rounded-2xl flex items-center justify-center shadow-lg shadow-brand-primary/30">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h4 class="text-lg font-black text-slate-800">Alhamdulillah! Seluruh Siswa Sudah Terdata 🎉</h4>
                        <p class="text-sm text-slate-600 mt-1 max-w-md mx-auto font-medium">
                            Tidak ada siswa yang belum presensi {{ $selectedClassFilter !== 'all' ? 'di kelas ini' : 'di seluruh sekolah' }}. Data kehadiran seluruh siswa telah lengkap tercatat.
                        </p>
                    </div>
                @else
                    <!-- Mobile Card List -->
                    <div class="block md:hidden space-y-3">
                        @foreach($filteredUnattended as $st)
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:shadow-md transition-all">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-black text-sm shrink-0">
                                            {{ strtoupper(substr($st['name'], 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 text-sm truncate">{{ $st['name'] }}</p>
                                            <div class="flex items-center gap-1.5 text-xs text-slate-400">
                                                <span class="font-bold text-brand-primary">Kelas {{ $st['class_name'] }}</span>
                                                <span>&bull;</span>
                                                <span>NISN: {{ $st['nisn'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Belum Absen
                                    </span>
                                </div>

                                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                    <span class="text-xs text-slate-400 font-medium">
                                        {{ $st['phone'] ? 'HP: ' . $st['phone'] : 'No HP Ortu: -' }}
                                    </span>

                                    <div class="flex items-center gap-2">
                                        @if(!empty($st['wa_link']))
                                            <a href="{{ $st['wa_link'] }}" target="_blank" 
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                                <span>WA Ortu</span>
                                            </a>
                                        @endif

                                        <a href="{{ route('portal-presensi.input-manual') }}?class_id={{ $st['class_id'] }}" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
                                            <span>Input</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                                    <th class="py-4 px-4 w-12 text-center">No</th>
                                    <th class="py-4 px-5">Nama Siswa</th>
                                    <th class="py-4 px-4 text-center">Kelas</th>
                                    <th class="py-4 px-4 text-center">NISN</th>
                                    <th class="py-4 px-4 text-center">Status</th>
                                    <th class="py-4 px-4 text-center">No HP Wali</th>
                                    <th class="py-4 px-5 text-center">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($filteredUnattended as $index => $st)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-4 text-center font-bold text-slate-400 text-xs">{{ $loop->iteration }}</td>
                                        <td class="py-3 px-5">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-black text-xs shrink-0">
                                                    {{ strtoupper(substr($st['name'], 0, 2)) }}
                                                </div>
                                                <span class="font-bold text-slate-800">{{ $st['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-brand-primary">
                                            Kelas {{ $st['class_name'] }}
                                        </td>
                                        <td class="py-3 px-4 text-center text-slate-500 font-medium">
                                            {{ $st['nisn'] ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Belum Presensi
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center text-slate-600 font-medium">
                                            {{ $st['phone'] ?: '-' }}
                                        </td>
                                        <td class="py-3 px-5 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                @if(!empty($st['wa_link']))
                                                    <a href="{{ $st['wa_link'] }}" target="_blank" 
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-xs font-bold transition-all shadow-2xs" title="Chat WhatsApp Orang Tua">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                                        <span>WA Ortu</span>
                                                    </a>
                                                @endif

                                                <a href="{{ route('portal-presensi.input-manual') }}?class_id={{ $st['class_id'] }}" 
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
                                                    <span>Input</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- TAB 3: DAFTAR SELURUH SISWA HARI INI -->
        <!-- ========================================================================= -->
        @if($activeTab === 'semua_siswa')
            <div class="p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <select wire:model.live="selectedClassFilter" class="text-xs sm:text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700">
                            <option value="all">Semua Kelas</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="filterStatus" class="text-xs sm:text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700">
                            <option value="all">Semua Status Kehadiran</option>
                            <option value="hadir">Hadir Tepat Waktu</option>
                            <option value="telat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpa">Alpa</option>
                            <option value="belum">Belum Presensi</option>
                        </select>
                    </div>

                    <span class="text-xs font-semibold text-slate-500">
                        Menampilkan data presensi seluruh siswa sekolah
                    </span>
                </div>

                @php
                    $filteredAll = collect($allStudents)->filter(function($st) use ($selectedClassFilter, $filterStatus, $searchQuery) {
                        if ($selectedClassFilter !== 'all' && $st['class_id'] !== $selectedClassFilter) {
                            return false;
                        }
                        if ($filterStatus !== 'all' && $st['status'] !== $filterStatus) {
                            return false;
                        }
                        if (!empty($searchQuery)) {
                            return str_contains(strtolower($st['name']), strtolower($searchQuery)) ||
                                   str_contains((string)$st['nisn'], $searchQuery) ||
                                   str_contains(strtolower($st['class_name']), strtolower($searchQuery));
                        }
                        return true;
                    });
                @endphp

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                                <th class="py-4 px-4 w-12 text-center">No</th>
                                <th class="py-4 px-5">Nama Siswa</th>
                                <th class="py-4 px-4 text-center">Kelas</th>
                                <th class="py-4 px-4 text-center">NISN</th>
                                <th class="py-4 px-4 text-center">Status</th>
                                <th class="py-4 px-4 text-center">Jam Datang</th>
                                <th class="py-4 px-4 text-center">Status Fisik</th>
                                <th class="py-4 px-4">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($filteredAll as $index => $st)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 text-center font-bold text-slate-400 text-xs">{{ $loop->iteration }}</td>
                                    <td class="py-3 px-5 font-bold text-slate-800">{{ $st['name'] }}</td>
                                    <td class="py-3 px-4 text-center font-bold text-brand-primary">Kelas {{ $st['class_name'] }}</td>
                                    <td class="py-3 px-4 text-center text-slate-500 font-medium">{{ $st['nisn'] ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if($st['status'] === 'hadir')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Hadir</span>
                                        @elseif($st['status'] === 'telat')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Terlambat</span>
                                        @elseif($st['status'] === 'izin')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">Izin</span>
                                        @elseif($st['status'] === 'sakit')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">Sakit</span>
                                        @elseif($st['status'] === 'alpa')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">Alpa</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">Belum Absen</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-600">
                                        {{ $st['scan_time'] ? $st['scan_time'] . ' WIB' : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if(in_array($st['status'], ['hadir', 'telat']))
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary bg-indigo-50 px-2 py-0.5 rounded-md">
                                                <span>✓ Hadir di Sekolah</span>
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400 font-medium">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 text-xs truncate max-w-xs">
                                        {{ $st['note'] ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-400 font-bold">
                                        Tidak ada data siswa ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
