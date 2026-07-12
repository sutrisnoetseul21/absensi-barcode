@props(['pengaturanSekolah', 'isTodayHoliday', 'realStats'])

<!-- ====================== HERO SECTION MODERN ====================== -->
<div class="relative overflow-hidden min-h-[70vh] flex items-center">
    <!-- Background Photo (Full, Prominent) -->
    <div class="absolute inset-0">
        @if($pengaturanSekolah && $pengaturanSekolah->hero_image_path)
            <img src="{{ asset('storage/' . $pengaturanSekolah->hero_image_path) }}"
                alt="School Background" class="w-full h-full object-cover object-top scale-105">
        @else
            <img src="{{ asset('hero-bg-school.png') }}"
                alt="School Background" class="w-full h-full object-cover object-top scale-105">
        @endif
        <!-- Dark gradient overlay agar teks tetap terbaca -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/70 to-black/40"></div>
        <!-- Warna accent indigo di atas overlay -->
        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-950/60 via-transparent to-violet-950/30"></div>
    </div>
    <!-- Subtle animated color blobs (di atas foto sebagai aksen) -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -left-20 w-96 h-96 rounded-full bg-indigo-600/20 blur-3xl animate-[moveblob1_15s_ease-in-out_infinite]"></div>
        <div class="absolute -bottom-20 right-0 w-80 h-80 rounded-full bg-violet-600/15 blur-3xl animate-[moveblob2_18s_ease-in-out_infinite]"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-20">
        <div class="max-w-3xl">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 backdrop-blur-sm text-indigo-300 text-sm font-semibold px-4 py-2 rounded-full mb-8 shadow-lg">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Sistem berjalan aktif & real-time
            </div>

            <!-- Main Title -->
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.1] tracking-tight mb-6">
                Presensi 
                <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-cyan-400 bg-clip-text text-transparent">
                    Digital
                </span>
                <span class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-200 mt-2 block">
                    {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'SMPN 1 Majenang' }}
                </span>
            </h1>

            <p class="text-lg text-slate-400 mb-10 max-w-xl leading-relaxed">
                Pantau kehadiran siswa secara real-time. Sistem terintegrasi dengan barcode untuk pencatatan yang cepat, akurat, dan transparan.
            </p>

            <!-- Live Stats Pills -->
            <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                <div class="flex items-center gap-3 bg-white/5 border border-white/10 backdrop-blur-sm px-4 sm:px-5 py-3 rounded-2xl w-full sm:w-auto justify-between sm:justify-start">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold leading-none mb-0.5">Waktu Sekarang</p>
                            <span x-text="formattedTime" class="font-mono text-base sm:text-lg font-bold text-white tabular-nums tracking-wider leading-none"></span>
                        </div>
                    </div>
                    <div class="w-px h-8 bg-white/10 mx-1 sm:mx-2 hidden sm:block"></div>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-violet-400 flex-shrink-0 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <div class="text-right sm:text-left">
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold leading-none mb-0.5">Tanggal</p>
                            <span x-text="formattedDate" class="text-sm font-semibold text-white leading-none"></span>
                        </div>
                    </div>
                </div>
                
                @if(!$isTodayHoliday)
                <div class="flex items-center gap-2.5 bg-emerald-950/50 border border-emerald-700/30 backdrop-blur-sm px-5 py-3 rounded-2xl w-full sm:w-auto">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse flex-shrink-0"></span>
                    <div>
                        <p class="text-[10px] text-emerald-700 uppercase tracking-widest font-semibold leading-none mb-0.5">Hadir Hari Ini</p>
                        <span class="text-lg font-bold text-emerald-400 leading-none">{{ $realStats['hadir_telat'] }} <span class="text-sm font-medium text-emerald-600">siswa</span></span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Decorative Side Orb (Desktop Only) -->
    <div class="hidden lg:block absolute right-0 top-0 bottom-0 w-1/3 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-l from-indigo-900/20 to-transparent"></div>
        <div class="absolute top-1/2 right-20 -translate-y-1/2 w-72 h-72 rounded-full bg-gradient-to-br from-indigo-600/20 to-violet-600/20 blur-2xl"></div>
        <div class="absolute top-1/2 right-32 -translate-y-1/2 w-48 h-48 rounded-full bg-gradient-to-br from-indigo-500/10 to-cyan-500/10 blur-xl animate-[spin_20s_linear_infinite]"></div>
    </div>
</div>
