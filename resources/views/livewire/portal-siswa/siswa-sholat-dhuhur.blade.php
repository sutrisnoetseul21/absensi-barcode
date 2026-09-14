<div class="min-h-full bg-slate-50 font-jakarta pb-12">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-700 via-teal-800 to-emerald-900 pt-8 pb-16 text-white shadow-lg relative overflow-hidden">
        <!-- Decorative blobs -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute left-1/3 -top-10 w-48 h-48 bg-teal-400/10 rounded-full blur-xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/15 backdrop-blur-md text-emerald-100 border border-white/20">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Pembiasaan Karakter & Ibadah</span>
                        </span>
                        @if($enrollment?->kelas)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/30 text-emerald-100 border border-emerald-300/30">
                                Kelas {{ $enrollment->kelas->name }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                        Presensi Sholat Dhuhur
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-100/80 mt-1 max-w-xl leading-relaxed">
                        Rekapitulasi kehadiran ibadah sholat dhuhur berjamaah Anda. Hari Jumat merupakan hari sholat Jumat (libur dhuhur di sekolah).
                    </p>
                </div>

                <!-- Month Selector -->
                <div class="bg-white/10 backdrop-blur-md p-3 rounded-2xl border border-white/20 flex items-center gap-3">
                    <label class="text-xs font-bold text-emerald-100 uppercase tracking-wider whitespace-nowrap">Bulan:</label>
                    <select wire:model.live="selectedMonthYear" class="px-3 py-1.5 text-xs font-bold text-slate-800 bg-white rounded-xl focus:ring-2 focus:ring-emerald-400 focus:outline-none shadow-sm cursor-pointer">
                        @foreach($availableMonths as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10 space-y-8">
        
        <!-- 4 Summary Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Hadir -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-5 flex items-center gap-4 hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hadir</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $monthlyStats['hadir'] }} <span class="text-xs text-slate-400 font-normal">Hari</span></h3>
                </div>
            </div>

            <!-- Ijin / Halangan -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-5 flex items-center gap-4 hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ijin / Halangan</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $monthlyStats['ijin'] }} <span class="text-xs text-slate-400 font-normal">Hari</span></h3>
                </div>
            </div>

            <!-- Tidak Hadir -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-5 flex items-center gap-4 hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tidak Hadir</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $monthlyStats['tidak_hadir'] }} <span class="text-xs text-slate-400 font-normal">Hari</span></h3>
                </div>
            </div>

            <!-- Kepatuhan Berjamaah -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-5 flex items-center gap-4 hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 font-black text-sm">
                    %
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kepatuhan</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-0.5">{{ $attendancePercentage }}%</h3>
                </div>
            </div>
        </div>

        <!-- Monthly Calendar View -->
        <div class="bg-white rounded-3xl shadow-xl border border-white/40 overflow-hidden p-6 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-black text-slate-800 tracking-tight">Kalender Presensi Sholat Dhuhur</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Status kehadiran harian Anda dalam satu bulan penuh.</p>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Hadir
                    </span>
                    <span class="inline-flex items-center gap-1 text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Ijin / Halangan
                    </span>
                    <span class="inline-flex items-center gap-1 text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Tidak Hadir
                    </span>
                    <span class="inline-flex items-center gap-1 text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> Libur / Jumat
                    </span>
                </div>
            </div>

            <!-- Calendar Grid Header -->
            <div class="grid grid-cols-7 gap-2 mb-2 text-center text-xs font-black uppercase tracking-wider text-slate-400">
                <span class="text-rose-500">Min</span>
                <span>Sen</span>
                <span>Sel</span>
                <span>Rab</span>
                <span>Kam</span>
                <span class="text-teal-600 font-extrabold">Jum</span>
                <span>Sab</span>
            </div>

            <!-- Calendar Days Grid -->
            <div class="grid grid-cols-7 gap-2">
                {{-- Empty cells before day 1 --}}
                @for($offset = 0; $offset < $startOfMonthOffset; $offset++)
                    <div class="h-20 sm:h-24 rounded-2xl bg-slate-50/50 border border-transparent"></div>
                @endfor

                {{-- Day cells --}}
                @foreach($attendanceData as $d => $info)
                    @php
                        $st = $info['status'];
                        $isToday = $info['date'] === $todayDate;
                        $cardBg = match($st) {
                            'hadir'       => 'bg-emerald-50/70 border-emerald-200 text-emerald-900',
                            'ijin'        => 'bg-blue-50/70 border-blue-200 text-blue-900',
                            'tidak_hadir' => 'bg-rose-50/70 border-rose-200 text-rose-900',
                            'libur'       => 'bg-slate-100/70 border-slate-200 text-slate-500',
                            default       => 'bg-white border-slate-200 text-slate-400',
                        };
                        $badgeBg = match($st) {
                            'hadir'       => 'bg-emerald-600 text-white',
                            'ijin'        => 'bg-blue-600 text-white',
                            'tidak_hadir' => 'bg-rose-600 text-white',
                            'libur'       => 'bg-slate-300 text-slate-700',
                            default       => 'bg-slate-100 text-slate-400',
                        };
                        $statusLabel = match($st) {
                            'hadir'       => 'Hadir',
                            'ijin'        => 'Ijin',
                            'tidak_hadir' => 'Tidak Hadir',
                            'libur'       => 'Libur',
                            default       => '-',
                        };
                    @endphp
                    <div class="h-20 sm:h-24 p-2 sm:p-2.5 rounded-2xl border flex flex-col justify-between transition-all hover:shadow-md {{ $cardBg }} {{ $isToday ? 'ring-2 ring-emerald-500 shadow-sm' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-black {{ $isToday ? 'text-emerald-700 underline' : '' }}">
                                {{ $d }}
                            </span>
                            @if($isToday)
                                <span class="text-[9px] font-extrabold bg-emerald-600 text-white px-1.5 py-0.5 rounded-md">Hari Ini</span>
                            @endif
                        </div>

                        <div class="mt-auto">
                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] sm:text-xs font-bold w-full text-center truncate {{ $badgeBg }}">
                                {{ $statusLabel }}
                            </span>
                            @if(!empty($info['keterangan']))
                                <p class="text-[9px] truncate text-slate-500 mt-0.5" title="{{ $info['keterangan'] }}">
                                    {{ $info['keterangan'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
