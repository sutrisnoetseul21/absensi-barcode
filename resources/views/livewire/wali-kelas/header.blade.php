<!-- Header Banner Card -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-2">
    <div class="relative bg-gradient-to-r from-brand-primary via-indigo-900 to-brand-secondary border border-brand-primary/30 rounded-3xl p-6 sm:p-8 shadow-xl overflow-hidden text-white">
        <!-- Decorative Glow Blobs -->
        <div class="absolute -top-12 -right-12 w-80 h-80 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-80 h-80 bg-brand-secondary/20 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 md:flex md:items-center md:justify-between gap-6">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white shadow-lg border border-white/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-extrabold text-white sm:text-3xl tracking-tight">
                            Dashboard Portal Guru
                        </h2>
                        <p class="mt-1 text-sm text-indigo-100/90 font-medium max-w-2xl">
                            Kelola data presensi, pantau keterlambatan harian, dan lakukan presensi manual untuk siswa di kelas binaan Anda.
                        </p>
                    </div>
                </div>
                
                @if(count($classes) > 0 && $selectedClassId && !empty($todayStats))
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <div class="bg-white/15 border border-white/25 rounded-xl px-4 py-2 flex items-center gap-3 backdrop-blur-md">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
                        </span>
                        <span class="text-sm font-semibold text-white">Sudah Absen Hari Ini: <span class="font-extrabold text-emerald-300 ml-1">{{ $todayStats['total'] - $todayStats['belum'] }}</span></span>
                    </div>
                    <div class="bg-white/15 border border-white/25 rounded-xl px-4 py-2 flex items-center gap-3 backdrop-blur-md">
                        <span class="w-3 h-3 rounded-full bg-rose-400 shadow-sm"></span>
                        <span class="text-sm font-semibold text-white">Belum Absen: <span class="font-extrabold text-rose-300 ml-1">{{ $todayStats['belum'] }}</span></span>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="mt-6 md:mt-0 flex flex-wrap xl:flex-nowrap gap-3 items-center">
                @if(count($classes) > 0 && $selectedClassId)
                    <button wire:click="openCetakModal" class="inline-flex items-center px-4 py-2.5 border border-white/25 rounded-xl shadow-lg text-sm font-bold text-white bg-white/15 hover:bg-white/25 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-300">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-indigo-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan
                    </button>
                    
                    <button wire:click="openInputModal" class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-xl shadow-xl text-sm font-bold text-slate-900 bg-white hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-300 transform hover:-translate-y-0.5">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Input Manual
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
