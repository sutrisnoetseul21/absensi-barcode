<!-- Header & Page Banner -->
<div class="bg-gradient-to-r from-teal-700 via-teal-800 to-emerald-900 pt-8 pb-16 text-white shadow-lg relative overflow-hidden">
    <!-- Decorative background patterns -->
    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
    <div class="absolute left-1/3 -top-10 w-48 h-48 bg-teal-400/10 rounded-full blur-xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/15 backdrop-blur-md text-teal-100 border border-white/20">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Pembiasaan Ibadah & Karakter</span>
                    </span>
                    @if($selectedClassId && $classes->isNotEmpty())
                        @php $currentCls = collect($classes)->firstWhere('id', $selectedClassId); @endphp
                        @if($currentCls)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-teal-500/30 text-teal-100 border border-teal-300/30">
                                Kelas {{ $currentCls->name }}
                            </span>
                        @endif
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    Presensi Kehadiran Sholat Dhuhur
                </h1>
                <p class="text-xs sm:text-sm text-teal-100/80 mt-1 max-w-2xl leading-relaxed">
                    Pencatatan dan rekapitulasi kehadiran sholat dhuhur berjamaah peserta didik. Hari Jumat, Minggu, dan hari libur nasional ditandai sebagai hari libur (L).
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Tombol Presensi Sholat Dhuhur -->
                <a href="{{ route('kiosk.scan-sholat-dhuhur') }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs backdrop-blur-md border border-white/25 shadow-sm active:scale-95 transition-all">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span>Presensi Sholat Dhuhur</span>
                </a>

                <!-- Tombol Cetak Laporan -->
                <button type="button" 
                        wire:click="openCetakModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs backdrop-blur-md border border-white/25 shadow-sm active:scale-95 transition-all">
                    <svg class="w-4 h-4 text-teal-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Laporan</span>
                </button>

                <!-- Tombol Input Presensi Sholat -->
                <button type="button" 
                        wire:click="openInputModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-teal-400 hover:bg-teal-300 text-teal-950 font-black text-xs shadow-lg shadow-teal-900/40 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Input Presensi Sholat</span>
                </button>
            </div>
        </div>
    </div>
</div>
