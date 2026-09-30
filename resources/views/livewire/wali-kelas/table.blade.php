<div class="mt-4 mb-12">
    <!-- View Switcher & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <!-- Tab Mode Selector -->
        <div class="inline-flex p-1 bg-slate-200/80 rounded-2xl border border-slate-300/60 shadow-inner self-start sm:self-auto">
            <button type="button" 
                @click="rekapMode = 'harian'"
                :class="rekapMode === 'harian' ? 'bg-white text-brand-primary shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3.5 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Presensi Hari Ini</span>
                <span :class="rekapMode === 'harian' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-300/60 text-slate-700'" class="px-2 py-0.5 rounded-full text-[10px] font-black">
                    {{ $todayStats['total'] ?? count($students) }}
                </span>
            </button>
            <button type="button" 
                @click="rekapMode = 'bulanan'"
                :class="rekapMode === 'bulanan' ? 'bg-white text-brand-primary shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3.5 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Tabel Matriks Bulanan</span>
            </button>
        </div>

        <!-- Live Search Input -->
        <div class="relative w-full sm:w-72">
            <input type="text" x-model="searchQuery" placeholder="Cari nama siswa / NISN..." 
                class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/40 focus:border-brand-primary font-medium shadow-sm transition-all">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <button x-show="searchQuery.length > 0" @click="searchQuery = ''" type="button" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 1: DAFTAR PRESENSI HARI INI (OPTIMAL UNTUK HP & MONITORING HARIAN) -->
    <!-- ========================================================================= -->
    <div x-show="rekapMode === 'harian'" class="space-y-4">
        <!-- Filter Chips Bar -->
        <div class="bg-white/90 backdrop-blur-md rounded-2xl p-3 border border-slate-200/80 shadow-sm flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar pb-1 sm:pb-0 w-full sm:w-auto">
                <!-- Semua -->
                <button type="button" @click="dailyFilter = 'all'"
                    :class="dailyFilter === 'all' ? 'bg-slate-800 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    Semua
                    <span :class="dailyFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['total'] ?? count($students) }}
                    </span>
                </button>

                <!-- Belum Absen -->
                <button type="button" @click="dailyFilter = 'belum'"
                    :class="dailyFilter === 'belum' ? 'bg-rose-600 text-white font-bold shadow-sm ring-2 ring-rose-300' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ ($todayStats['belum'] ?? 0) > 0 ? 'bg-rose-500 animate-pulse' : 'bg-slate-400' }}"></span>
                    Belum Absen
                    <span :class="dailyFilter === 'belum' ? 'bg-white/20 text-white' : 'bg-rose-200 text-rose-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['belum'] ?? 0 }}
                    </span>
                </button>

                <!-- Hadir Tepat Waktu -->
                <button type="button" @click="dailyFilter = 'hadir'"
                    :class="dailyFilter === 'hadir' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-2 ring-emerald-300' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Hadir
                    <span :class="dailyFilter === 'hadir' ? 'bg-white/20 text-white' : 'bg-emerald-200 text-emerald-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['hadir'] ?? 0 }}
                    </span>
                </button>

                <!-- Terlambat -->
                <button type="button" @click="dailyFilter = 'telat'"
                    :class="dailyFilter === 'telat' ? 'bg-amber-600 text-white font-bold shadow-sm ring-2 ring-amber-300' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Telat
                    <span :class="dailyFilter === 'telat' ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['telat'] ?? 0 }}
                    </span>
                </button>

                <!-- Sakit / Izin -->
                <button type="button" @click="dailyFilter = 'izin'"
                    :class="dailyFilter === 'izin' ? 'bg-purple-600 text-white font-bold shadow-sm ring-2 ring-purple-300' : 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    Sakit / Izin
                    <span :class="dailyFilter === 'izin' ? 'bg-white/20 text-white' : 'bg-purple-200 text-purple-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ ($todayStats['sakit'] ?? 0) + ($todayStats['izin'] ?? 0) }}
                    </span>
                </button>

                @if(($todayStats['alpa'] ?? 0) > 0)
                <!-- Alpa -->
                <button type="button" @click="dailyFilter = 'alpa'"
                    :class="dailyFilter === 'alpa' ? 'bg-slate-700 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    Alpa
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 text-slate-800">
                        {{ $todayStats['alpa'] }}
                    </span>
                </button>
                @endif
            </div>

            <!-- Tombol Cepat Input Manual -->
            <button type="button" wire:click="openInputModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-primary bg-brand-primary/10 hover:bg-brand-primary/20 border border-brand-primary/20 transition-all cursor-pointer ml-auto">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Input Presensi</span>
            </button>
        </div>

        <!-- Banner Spesial jika Filter 'Belum Absen' dipilih dan nilainya 0 -->
        <div x-show="dailyFilter === 'belum' && {{ $todayStats['belum'] ?? 0 }} === 0" class="p-8 text-center bg-gradient-to-br from-emerald-50 via-white to-teal-50 rounded-3xl border border-emerald-200/80 shadow-md">
            <div class="w-16 h-16 mx-auto mb-3 bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <h4 class="text-lg font-black text-slate-800">Luar Biasa! Semua Siswa Sudah Presensi 🎉</h4>
            <p class="text-sm text-slate-600 mt-1 max-w-md mx-auto font-medium">
                Tidak ada siswa yang belum hadir hari ini. Seluruh {{ $todayStats['total'] ?? count($students) }} siswa telah terdata dalam sistem presensi.
            </p>
            <div class="mt-4">
                <button type="button" @click="dailyFilter = 'all'" class="px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-md hover:bg-emerald-700 transition-all cursor-pointer">
                    Tampilkan Semua Siswa
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- A. TAMPILAN KHUSUS MOBILE (KARTU SISWA RINGKAS) -->
        <!-- ============================================== -->
        <div class="block md:hidden space-y-3">
            @forelse($students as $index => $student)
                @php
                    $att = $todayAttendances[$student->id] ?? null;
                    $status = $att['status'] ?? 'belum';
                    $statusPulang = $att['status_pulang'] ?? null;
                    $scanDatang = $att['scan_time'] ?? null;
                    $scanPulang = $att['scan_out_time'] ?? null;
                    $lateMinutes = $att['late_minutes'] ?? 0;
                    $note = $att['note'] ?? null;

                    // Phone number processing for WA Ortu
                    $rawPhone = $student->no_hp_orang_tua ?: $student->no_hp;
                    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
                    if (!empty($cleanPhone)) {
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        } elseif (str_starts_with($cleanPhone, '8')) {
                            $cleanPhone = '62' . $cleanPhone;
                        }
                    }
                    $dateLabel = \Carbon\Carbon::parse($todayDate ?? now())->locale('id')->translatedFormat('l, d F Y');
                    $waText = urlencode("Assalamu'alaikum / Selamat pagi Bapak/Ibu wali dari {$student->name}.\n\nKami menginfokan bahwa ananda belum tercatat hadir dalam presensi SMP Negeri 3 Kedungreja hari ini ({$dateLabel}).\n\nMohon konfirmasi keterangan kehadiran ananda. Terima kasih.");
                @endphp

                <div x-show="(dailyFilter === 'all' || 
                             (dailyFilter === 'belum' && '{{ $status }}' === 'belum') ||
                             (dailyFilter === 'hadir' && '{{ $status }}' === 'hadir') ||
                             (dailyFilter === 'telat' && '{{ $status }}' === 'telat') ||
                             (dailyFilter === 'izin' && ('{{ $status }}' === 'izin' || '{{ $status }}' === 'sakit')) ||
                             (dailyFilter === 'alpa' && '{{ $status }}' === 'alpa')) && 
                             ('{{ strtolower(addslashes($student->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ $student->nisn }}'.includes(searchQuery))"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:shadow-md transition-all">
                    
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Inisial Siswa -->
                            <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center font-black text-sm {{ $status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($status === 'telat' ? 'bg-amber-100 text-amber-700' : ($status === 'izin' || $status === 'sakit' ? 'bg-purple-100 text-purple-700' : 'bg-rose-100 text-rose-700')) }}">
                                {{ strtoupper(substr($student->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="font-bold text-slate-800 text-sm hover:text-brand-primary block truncate">
                                    {{ $student->name }}
                                </a>
                                <p class="text-xs text-slate-400 font-medium">NISN: {{ $student->nisn }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($status === 'hadir')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Hadir {{ $scanDatang ? '('.$scanDatang.')' : '' }}
                                </span>
                            @elseif($status === 'telat')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Telat {{ $lateMinutes ? "({$lateMinutes}m)" : '' }}
                                </span>
                            @elseif($status === 'izin')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    Izin
                                </span>
                            @elseif($status === 'sakit')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                    Sakit
                                </span>
                            @elseif($status === 'alpa')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-700 text-white border border-slate-800">
                                    Alpa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Belum Absen
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Keterangan / Note jika ada -->
                    @if(!empty($note))
                        <div class="mt-2.5 px-3 py-1.5 bg-slate-50 rounded-xl text-xs text-slate-600 border border-slate-100 flex items-start gap-1.5">
                            <span class="font-bold text-slate-700">Catatan:</span>
                            <span class="truncate">{{ $note }}</span>
                        </div>
                    @endif

                    <!-- Detail Jam Scan Pulang jika ada -->
                    @if(!empty($scanPulang))
                        <div class="mt-2 text-xs text-slate-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Scan Pulang: <strong class="text-slate-700">{{ $scanPulang }} WIB</strong></span>
                        </div>
                    @endif

                    <!-- Quick Actions Footer -->
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="text-xs font-bold text-slate-500 hover:text-brand-primary flex items-center gap-1">
                            <span>Detail Siswa</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <div class="flex items-center gap-2">
                            @if($status === 'belum')
                                @if(!empty($cleanPhone))
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        <span>WA Ortu</span>
                                    </a>
                                @endif
                                <button type="button" wire:click="openInputModal" class="inline-flex items-center gap-1 px-3 py-1.5 bg-brand-primary/10 text-brand-primary hover:bg-brand-primary/20 border border-brand-primary/20 rounded-xl text-xs font-bold transition-all">
                                    <span>Input Presensi</span>
                                </button>
                            @else
                                <button type="button" wire:click="openInputModal" class="inline-flex items-center gap-1 px-2.5 py-1 text-slate-500 hover:text-slate-800 text-xs font-semibold">
                                    <span>Ubah</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-500">
                    <p class="font-semibold text-sm">Tidak ada siswa terdaftar di kelas ini.</p>
                </div>
            @endforelse
        </div>

        <!-- ============================================== -->
        <!-- B. TAMPILAN TABLE HARIAN DESKTOP (MD KE ATAS) -->
        <!-- ============================================== -->
        <div class="hidden md:block bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                            <th class="py-4 px-4 w-12 text-center">No</th>
                            <th class="py-4 px-6">Nama Siswa</th>
                            <th class="py-4 px-4 text-center">Status Datang</th>
                            <th class="py-4 px-4 text-center">Jam Datang</th>
                            <th class="py-4 px-4 text-center">Status Pulang</th>
                            <th class="py-4 px-4 text-center">Jam Pulang</th>
                            <th class="py-4 px-4">Catatan</th>
                            <th class="py-4 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $index => $student)
                            @php
                                $att = $todayAttendances[$student->id] ?? null;
                                $status = $att['status'] ?? 'belum';
                                $statusPulang = $att['status_pulang'] ?? null;
                                $scanDatang = $att['scan_time'] ?? null;
                                $scanPulang = $att['scan_out_time'] ?? null;
                                $lateMinutes = $att['late_minutes'] ?? 0;
                                $note = $att['note'] ?? null;

                                $rawPhone = $student->no_hp_orang_tua ?: $student->no_hp;
                                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
                                if (!empty($cleanPhone)) {
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                    } elseif (str_starts_with($cleanPhone, '8')) {
                                        $cleanPhone = '62' . $cleanPhone;
                                    }
                                }
                                $dateLabel = \Carbon\Carbon::parse($todayDate ?? now())->locale('id')->translatedFormat('l, d F Y');
                                $waText = urlencode("Assalamu'alaikum / Selamat pagi Bapak/Ibu wali dari {$student->name}.\n\nKami menginfokan bahwa ananda belum tercatat hadir dalam presensi SMP Negeri 3 Kedungreja hari ini ({$dateLabel}).\n\nMohon konfirmasi keterangan kehadiran ananda. Terima kasih.");
                            @endphp

                            <tr x-show="(dailyFilter === 'all' || 
                                         (dailyFilter === 'belum' && '{{ $status }}' === 'belum') ||
                                         (dailyFilter === 'hadir' && '{{ $status }}' === 'hadir') ||
                                         (dailyFilter === 'telat' && '{{ $status }}' === 'telat') ||
                                         (dailyFilter === 'izin' && ('{{ $status }}' === 'izin' || '{{ $status }}' === 'sakit')) ||
                                         (dailyFilter === 'alpa' && '{{ $status }}' === 'alpa')) && 
                                         ('{{ strtolower(addslashes($student->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ $student->nisn }}'.includes(searchQuery))"
                                class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400 text-xs">{{ $loop->iteration }}</td>
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs {{ $status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($status === 'telat' ? 'bg-amber-100 text-amber-700' : ($status === 'izin' || $status === 'sakit' ? 'bg-purple-100 text-purple-700' : 'bg-rose-100 text-rose-700')) }}">
                                            {{ strtoupper(substr($student->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="font-bold text-slate-800 text-sm hover:text-brand-primary block">
                                                {{ $student->name }}
                                            </a>
                                            <span class="text-xs text-slate-400 font-medium">NISN: {{ $student->nisn }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($status === 'hadir')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Hadir Tepat Waktu</span>
                                    @elseif($status === 'telat')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Terlambat ({{ $lateMinutes }}m)</span>
                                    @elseif($status === 'izin')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Izin</span>
                                    @elseif($status === 'sakit')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">Sakit</span>
                                    @elseif($status === 'alpa')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-700 text-white">Alpa</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">Belum Absen</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center text-xs font-bold text-slate-600">
                                    {{ $scanDatang ? $scanDatang . ' WIB' : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($statusPulang === 'pulang')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Pulang</span>
                                    @elseif($statusPulang)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 capitalize">{{ $statusPulang }}</span>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center text-xs font-bold text-slate-600">
                                    {{ $scanPulang ? $scanPulang . ' WIB' : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-500 max-w-xs truncate">
                                    {{ $note ?: '-' }}
                                </td>
                                <td class="py-3.5 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($status === 'belum' && !empty($cleanPhone))
                                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-xs font-bold transition-all shadow-2xs" title="Chat WhatsApp Wali">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                                <span>WA Ortu</span>
                                            </a>
                                        @endif
                                        <button type="button" wire:click="openInputModal" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all" title="Input Presensi">
                                            Input
                                        </button>
                                        <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="p-1 text-slate-400 hover:text-brand-primary" title="Lihat Profil">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500">
                                    <p class="font-semibold text-sm">Belum ada siswa terdaftar di kelas ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 2: TABEL MATRIKS BULANAN (1-31 HARI LENGKAP) -->
    <!-- ========================================================================= -->
    <div x-show="rekapMode === 'bulanan'" style="display: none;" class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-xl border border-white/40 overflow-hidden relative z-20">
        <div class="p-4 bg-slate-50/80 border-b border-slate-200/60 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-black text-slate-800">Tabel Presensi Matriks Bulanan</h4>
                <p class="text-xs text-slate-500">Menampilkan rekapan harian dari tanggal 1 s.d {{ $daysInMonth }}</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> H: Hadir</span>
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-amber-500"></span> T: Telat</span>
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-blue-500"></span> I: Izin</span>
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-purple-500"></span> S: Sakit</span>
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-rose-500"></span> A: Alpa</span>
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-slate-300"></span> L: Libur</span>
            </div>
        </div>
        <div class="overflow-x-auto custom-scrollbar [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 backdrop-blur-sm border-b border-slate-200/60 text-xs font-black text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-5 sticky left-0 bg-slate-50/90 backdrop-blur-md z-10 shadow-[1px_0_0_0_#e2e8f0]">Nama Siswa</th>
                        @for($i = 1; $i <= $daysInMonth; $i++)
                            <th class="py-4 px-2 text-center min-w-[32px]">{{ $i }}</th>
                        @endfor
                        <th class="py-4 px-3 text-center text-emerald-600 bg-emerald-50/80 backdrop-blur-sm">H</th>
                        <th class="py-4 px-3 text-center text-amber-600 bg-amber-50/80 backdrop-blur-sm">T</th>
                        <th class="py-4 px-3 text-center text-blue-600 bg-blue-50/80 backdrop-blur-sm">I</th>
                        <th class="py-4 px-3 text-center text-indigo-600 bg-indigo-50/80 backdrop-blur-sm">S</th>
                        <th class="py-4 px-3 text-center text-rose-600 bg-rose-50/80 backdrop-blur-sm">A</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/60">
                    @forelse($students as $student)
                        @php
                            $isAlpaWarning = in_array($student->id, $alerts['alpa'] ?? []);
                            $isTelatWarning = in_array($student->id, $alerts['telat'] ?? []);
                            $rowBg = ($isAlpaWarning || $isTelatWarning) ? 'bg-red-50/50 hover:bg-red-50' : 'bg-white hover:bg-slate-50';
                            $stickyBg = ($isAlpaWarning || $isTelatWarning) ? 'bg-red-50' : 'bg-white';
                        @endphp
                        <tr class="{{ $rowBg }} transition-colors"
                            x-show="'{{ strtolower(addslashes($student->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ $student->nisn }}'.includes(searchQuery)">
                            <td class="py-3 px-5 sticky left-0 z-10 shadow-[1px_0_0_0_#f1f5f9] {{ $stickyBg }}">
                                <div class="flex flex-col">
                                    <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="font-bold text-brand-primary hover:text-brand-secondary hover:underline transition-colors whitespace-nowrap cursor-pointer text-xs sm:text-sm" title="Lihat Detail Siswa">
                                        {{ $student->name }}
                                    </a>
                                    <div class="flex gap-1 mt-0.5">
                                        @if($isAlpaWarning)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">⚠️ ≥3 Alpa</span>
                                        @endif
                                        @if($isTelatWarning)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">⚠️ ≥100mnt Telat</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @for($i = 1; $i <= $daysInMonth; $i++)
                                @php
                                    $code = $monthlyStats[$student->id]['daily'][$i] ?? '-';
                                    $colorClass = match($code) {
                                        'H' => 'text-emerald-600 font-black bg-emerald-50 rounded',
                                        'T' => 'text-amber-600 font-black bg-amber-50 rounded',
                                        'I' => 'text-blue-600 font-black bg-blue-50 rounded',
                                        'S' => 'text-indigo-600 font-black bg-indigo-50 rounded',
                                        'A' => 'text-red-600 font-black bg-red-50 rounded',
                                        'L' => 'text-slate-500 font-black bg-slate-200 rounded cursor-not-allowed',
                                        default => 'text-slate-300',
                                    };
                                @endphp
                                <td class="py-2.5 px-1 text-center" title="{{ $code === 'L' ? 'Libur' : '' }}">
                                    <div class="w-6 h-6 sm:w-7 sm:h-7 mx-auto flex items-center justify-center text-[11px] sm:text-xs {{ $colorClass }}">
                                        {{ $code === 'L' ? 'L' : $code }}
                                    </div>
                                </td>
                            @endfor
                            <td class="py-2.5 px-3 text-center font-black text-emerald-600 bg-emerald-50/50 text-xs sm:text-sm">{{ $monthlyStats[$student->id]['hadir'] ?? 0 }}</td>
                            <td class="py-2.5 px-3 text-center font-black text-amber-600 bg-amber-50/50 text-xs sm:text-sm">{{ $monthlyStats[$student->id]['telat'] ?? 0 }}</td>
                            <td class="py-2.5 px-3 text-center font-black text-blue-600 bg-blue-50/50 text-xs sm:text-sm">{{ $monthlyStats[$student->id]['izin'] ?? 0 }}</td>
                            <td class="py-2.5 px-3 text-center font-black text-indigo-600 bg-indigo-50/50 text-xs sm:text-sm">{{ $monthlyStats[$student->id]['sakit'] ?? 0 }}</td>
                            <td class="py-2.5 px-3 text-center font-black text-red-600 bg-red-50/50 text-xs sm:text-sm">{{ $monthlyStats[$student->id]['alpa'] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 6 }}" class="py-12 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <p class="mt-3 font-semibold text-sm">Belum ada siswa terdaftar di kelas ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
