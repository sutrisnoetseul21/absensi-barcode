<div class="mt-4 mb-12">
    <!-- View Switcher & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <!-- Tab Mode Selector -->
        <div class="inline-flex p-1 bg-slate-200/80 rounded-2xl border border-slate-300/60 shadow-inner self-start sm:self-auto">
            <button type="button" 
                @click="rekapMode = 'harian'"
                :class="rekapMode === 'harian' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3.5 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Presensi Hari Ini</span>
                <span :class="rekapMode === 'harian' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300/60 text-slate-700'" class="px-2 py-0.5 rounded-full text-[10px] font-black">
                    {{ $todayStats['total'] ?? count($students) }}
                </span>
            </button>
            <button type="button" 
                @click="rekapMode = 'bulanan'"
                :class="rekapMode === 'bulanan' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3.5 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Tabel Matriks Bulanan</span>
            </button>
        </div>

        <!-- Live Search Input -->
        <div class="relative w-full sm:w-72">
            <input type="text" x-model="searchQuery" placeholder="Cari nama siswa / NISN..." 
                class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 font-medium shadow-sm transition-all">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <button x-show="searchQuery.length > 0" @click="searchQuery = ''" type="button" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 1: DAFTAR PRESENSI SHOLAT HARI INI (OPTIMAL UNTUK HP & MONITORING) -->
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

                <!-- Belum Absen Sholat -->
                <button type="button" @click="dailyFilter = 'belum'"
                    :class="dailyFilter === 'belum' ? 'bg-indigo-600 text-white font-bold shadow-sm ring-2 ring-indigo-300' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ ($todayStats['belum'] ?? 0) > 0 ? 'bg-amber-400 animate-pulse' : 'bg-slate-400' }}"></span>
                    Belum Absen Sholat
                    <span :class="dailyFilter === 'belum' ? 'bg-white/20 text-white' : 'bg-indigo-200 text-indigo-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['belum'] ?? 0 }}
                    </span>
                </button>

                <!-- Hadir Berjamaah -->
                <button type="button" @click="dailyFilter = 'hadir'"
                    :class="dailyFilter === 'hadir' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-2 ring-emerald-300' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Hadir Berjamaah
                    <span :class="dailyFilter === 'hadir' ? 'bg-white/20 text-white' : 'bg-emerald-200 text-emerald-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['hadir'] ?? 0 }}
                    </span>
                </button>

                <!-- Ijin / Halangan -->
                <button type="button" @click="dailyFilter = 'ijin'"
                    :class="dailyFilter === 'ijin' ? 'bg-blue-600 text-white font-bold shadow-sm ring-2 ring-blue-300' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Ijin / Halangan
                    <span :class="dailyFilter === 'ijin' ? 'bg-white/20 text-white' : 'bg-blue-200 text-blue-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['ijin'] ?? 0 }}
                    </span>
                </button>

                <!-- Tidak Hadir / Alpa -->
                <button type="button" @click="dailyFilter = 'tidak_hadir'"
                    :class="dailyFilter === 'tidak_hadir' ? 'bg-rose-600 text-white font-bold shadow-sm ring-2 ring-rose-300' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Tidak Hadir
                    <span :class="dailyFilter === 'tidak_hadir' ? 'bg-white/20 text-white' : 'bg-rose-200 text-rose-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['tidak_hadir'] ?? 0 }}
                    </span>
                </button>

                <!-- Non-Muslim -->
                <button type="button" @click="dailyFilter = 'non_muslim'"
                    :class="dailyFilter === 'non_muslim' ? 'bg-indigo-600 text-white font-bold shadow-sm ring-2 ring-indigo-300' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 font-semibold'"
                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Non-Muslim
                    <span :class="dailyFilter === 'non_muslim' ? 'bg-white/20 text-white' : 'bg-indigo-200 text-indigo-800'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                        {{ $todayStats['non_muslim_count'] ?? 0 }}
                    </span>
                </button>
            </div>

            <!-- Tombol Cepat Input Presensi Sholat -->
            <button type="button" wire:click="openInputModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 transition-all cursor-pointer ml-auto">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Input Sholat</span>
            </button>
        </div>

        <!-- Banner Spesial jika Filter 'Belum Absen Sholat' dipilih dan nilainya 0 -->
        <div x-show="dailyFilter === 'belum' && {{ $todayStats['belum'] ?? 0 }} === 0" class="p-8 text-center bg-gradient-to-br from-emerald-50 via-white to-teal-50 rounded-3xl border border-emerald-200/80 shadow-md">
            <div class="w-16 h-16 mx-auto mb-3 bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <h4 class="text-lg font-black text-slate-800">Alhamdulillah! Seluruh Siswa Sudah Terdata 🎉</h4>
            <p class="text-sm text-slate-600 mt-1 max-w-md mx-auto font-medium">
                Tidak ada siswa yang belum presensi sholat dhuhur hari ini. Seluruh {{ $todayStats['total'] ?? count($students) }} siswa telah tercatat statusnya.
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
            @forelse($students as $student)
                @php
                    $att = $todayAttendances[$student->id] ?? null;
                    $isNm = !empty($student->religion) && strtolower(trim($student->religion)) !== 'islam';
                    $status = $isNm ? 'non_muslim' : ($att['status'] ?? 'belum');
                    $keterangan = $att['keterangan'] ?? null;
                    $time = $att['time'] ?? null;

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
                    $waText = urlencode("Assalamu'alaikum Bapak/Ibu wali dari {$student->name}.\n\nKami menginfokan bahwa ananda belum tercatat dalam presensi Sholat Dhuhur berjamaah SMP Negeri 3 Kedungreja hari ini ({$dateLabel}).\n\nMohon konfirmasi atau perhatiannya. Terima kasih.");
                @endphp

                <div x-show="(dailyFilter === 'all' || 
                             (dailyFilter === 'belum' && '{{ $status }}' === 'belum') ||
                             (dailyFilter === 'hadir' && '{{ $status }}' === 'hadir') ||
                             (dailyFilter === 'ijin' && '{{ $status }}' === 'ijin') ||
                             (dailyFilter === 'tidak_hadir' && '{{ $status }}' === 'tidak_hadir') ||
                             (dailyFilter === 'non_muslim' && '{{ $status }}' === 'non_muslim')) && 
                             ('{{ strtolower(addslashes($student->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ $student->nisn }}'.includes(searchQuery))"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:shadow-md transition-all">
                    
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Inisial Siswa -->
                            <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center font-black text-sm {{ $status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($status === 'ijin' ? 'bg-blue-100 text-blue-700' : ($status === 'tidak_hadir' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600')) }}">
                                {{ strtoupper(substr($student->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="font-bold text-slate-800 text-sm hover:text-emerald-700 block truncate">
                                    {{ $student->name }}
                                </a>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-medium">
                                    <span>NISN: {{ $student->nisn ?? '-' }}</span>
                                    <span>&bull;</span>
                                    <span class="{{ $student->gender === 'P' ? 'text-pink-600 font-semibold' : 'text-blue-600 font-semibold' }}">
                                        {{ $student->gender === 'P' ? 'Pr' : 'Lk' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge Sholat -->
                        <div>
                            @if($status === 'non_muslim')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    Non-Muslim
                                </span>
                            @elseif($status === 'hadir')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Hadir {{ $time ? '('.$time.')' : '' }}
                                </span>
                            @elseif($status === 'ijin')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    {{ str_contains((string)$keterangan, 'Haid') || str_contains((string)$keterangan, 'Halangan') ? 'Halangan' : 'Ijin' }}
                                </span>
                            @elseif($status === 'tidak_hadir')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Tidak Hadir
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    Belum Sholat
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Keterangan jika ada (misal: Halangan / Haid, Sakit di presensi pagi) -->
                    @if(!empty($keterangan))
                        <div class="mt-2.5 px-3 py-1.5 bg-slate-50 rounded-xl text-xs text-slate-600 border border-slate-100 flex items-start gap-1.5">
                            <span class="font-bold text-slate-700">Keterangan:</span>
                            <span class="truncate">{{ $keterangan }}</span>
                        </div>
                    @endif

                    <!-- Quick Actions Footer -->
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="text-xs font-bold text-slate-500 hover:text-emerald-700 flex items-center gap-1">
                            <span>Detail Siswa</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <div class="flex items-center gap-2">
                            @if($status === 'belum' || $status === 'tidak_hadir')
                                @if(!empty($cleanPhone))
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        <span>WA Ortu</span>
                                    </a>
                                @endif
                                <button type="button" wire:click="openInputModal" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-300 rounded-xl text-xs font-bold transition-all">
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
                            <th class="py-4 px-4 text-center">Status Sholat</th>
                            <th class="py-4 px-4 text-center">Waktu</th>
                            <th class="py-4 px-4">Keterangan</th>
                            <th class="py-4 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $index => $student)
                            @php
                                $att = $todayAttendances[$student->id] ?? null;
                                $isNm = !empty($student->religion) && strtolower(trim($student->religion)) !== 'islam';
                                $status = $isNm ? 'non_muslim' : ($att['status'] ?? 'belum');
                                $keterangan = $att['keterangan'] ?? null;
                                $time = $att['time'] ?? null;

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
                                $waText = urlencode("Assalamu'alaikum Bapak/Ibu wali dari {$student->name}.\n\nKami menginfokan bahwa ananda belum tercatat dalam presensi Sholat Dhuhur berjamaah SMP Negeri 3 Kedungreja hari ini ({$dateLabel}).\n\nMohon konfirmasi atau perhatiannya. Terima kasih.");
                            @endphp

                            <tr x-show="(dailyFilter === 'all' || 
                                         (dailyFilter === 'belum' && '{{ $status }}' === 'belum') ||
                                         (dailyFilter === 'hadir' && '{{ $status }}' === 'hadir') ||
                                         (dailyFilter === 'ijin' && '{{ $status }}' === 'ijin') ||
                                         (dailyFilter === 'tidak_hadir' && '{{ $status }}' === 'tidak_hadir') ||
                                         (dailyFilter === 'non_muslim' && '{{ $status }}' === 'non_muslim')) && 
                                         ('{{ strtolower(addslashes($student->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ $student->nisn }}'.includes(searchQuery))"
                                class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400 text-xs">{{ $loop->iteration }}</td>
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs {{ $status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($status === 'ijin' ? 'bg-blue-100 text-blue-700' : ($status === 'tidak_hadir' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600')) }}">
                                            {{ strtoupper(substr($student->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="font-bold text-slate-800 text-sm hover:text-emerald-700 block">
                                                {{ $student->name }}
                                            </a>
                                            <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                                                <span>NISN: {{ $student->nisn ?? '-' }}</span>
                                                <span>&bull;</span>
                                                <span class="{{ $student->gender === 'P' ? 'text-pink-600 font-semibold' : 'text-blue-600 font-semibold' }}">
                                                    {{ $student->gender === 'P' ? 'Pr' : 'Lk' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($status === 'non_muslim')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                            Non-Muslim
                                        </span>
                                    @elseif($status === 'hadir')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Hadir Berjamaah
                                        </span>
                                    @elseif($status === 'ijin')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            {{ str_contains((string)$keterangan, 'Haid') || str_contains((string)$keterangan, 'Halangan') ? 'Halangan / Haid' : 'Ijin' }}
                                        </span>
                                    @elseif($status === 'tidak_hadir')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            Tidak Hadir
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 animate-pulse">
                                            Belum Absen
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center text-xs font-bold text-slate-600">
                                    {{ $time ? $time . ' WIB' : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-500 max-w-xs truncate">
                                    {{ $keterangan ?: '-' }}
                                </td>
                                <td class="py-3.5 px-6 text-center">
                                    @if($status === 'non_muslim')
                                        <span class="text-xs text-indigo-500 font-semibold italic">Non-Muslim</span>
                                    @else
                                        <div class="flex items-center justify-center gap-2">
                                            @if(($status === 'belum' || $status === 'tidak_hadir') && !empty($cleanPhone))
                                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-xs font-bold transition-all shadow-2xs" title="Chat WhatsApp Wali">
                                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                                    <span>WA Ortu</span>
                                                </a>
                                            @endif
                                            <button type="button" wire:click="openInputModal" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all" title="Input Presensi">
                                                Input
                                            </button>
                                            <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="p-1 text-slate-400 hover:text-emerald-600" title="Lihat Profil">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500">
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
    <div x-show="rekapMode === 'bulanan'" style="display: none;" class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/40 overflow-hidden relative z-20">
        <div class="p-5 border-b border-slate-100/80 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 class="text-base font-bold text-slate-800">Matriks Presensi Sholat Dhuhur Bulanan</h3>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                    Jumat & Hari Libur Terdata Otomatis (L)
                </span>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> H: Hadir
                </span>
                <span class="inline-flex items-center gap-1.5 text-blue-700 bg-blue-50 px-2 py-1 rounded-md">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> I: Ijin / Halangan
                </span>
                <span class="inline-flex items-center gap-1.5 text-rose-700 bg-rose-50 px-2 py-1 rounded-md">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> A: Alpa
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600 bg-slate-200 px-2 py-1 rounded-md">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span> L: Libur / Jumat
                </span>
                <span class="inline-flex items-center gap-1.5 text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-1 rounded-md">
                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span> NM: Non-Muslim
                </span>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 backdrop-blur-sm border-b border-slate-200/60 font-black text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-4 sticky left-0 bg-slate-50/95 backdrop-blur-md z-10 shadow-[1px_0_0_0_#e2e8f0] min-w-[200px]">
                            Nama Siswa
                        </th>
                        @for($i = 1; $i <= $daysInMonth; $i++)
                            <th class="py-4 px-1.5 text-center min-w-[32px]">{{ $i }}</th>
                        @endfor
                        <th class="py-4 px-3 text-center text-emerald-700 bg-emerald-50/90 backdrop-blur-sm">H</th>
                        <th class="py-4 px-3 text-center text-blue-700 bg-blue-50/90 backdrop-blur-sm">I</th>
                        <th class="py-4 px-3 text-center text-rose-700 bg-rose-50/90 backdrop-blur-sm">A</th>
                        <th class="py-4 px-3 text-center text-slate-700 bg-slate-100/90 backdrop-blur-sm">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/60">
                    @forelse($students as $student)
                        @php
                            $stStats = $monthlyStats[$student->id] ?? null;
                            $hadirCount = $stStats['hadir'] ?? 0;
                            $ijinCount = $stStats['ijin'] ?? 0;
                            $alpaCount = $stStats['tidak_hadir'] ?? 0;
                            $effDays = $classMonthlyStats['effective_days'] ?? 0;
                            $pct = $effDays > 0 ? round(($hadirCount / $effDays) * 100, 1) : 0;
                        @endphp
                        <tr class="bg-white hover:bg-slate-50/80 transition-colors"
                            x-show="'{{ strtolower(addslashes($student->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ $student->nisn }}'.includes(searchQuery)">
                            <td class="py-3 px-4 sticky left-0 z-10 shadow-[1px_0_0_0_#f1f5f9] bg-white group-hover:bg-slate-50">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center font-bold text-[11px] text-slate-600 shrink-0">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ !empty($student->id) ? route('portal-guru.student-detail', ['id' => $student->id]) : '#' }}" class="font-bold text-slate-800 hover:text-emerald-700 truncate block">
                                            {{ $student->name }}
                                        </a>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                            <span>{{ $student->nisn ?? '-' }}</span>
                                            <span>&bull;</span>
                                            <span class="{{ $student->gender === 'P' ? 'text-pink-600 font-semibold' : 'text-blue-600 font-semibold' }}">
                                                {{ $student->gender === 'P' ? 'Pr' : 'Lk' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @for($i = 1; $i <= $daysInMonth; $i++)
                                @php
                                    $code = $stStats['daily'][$i] ?? '-';
                                    $isNm = $stStats['is_non_muslim'] ?? false;
                                    $colorClass = match($code) {
                                        'H'  => 'text-emerald-700 font-black bg-emerald-50 rounded border border-emerald-200/50',
                                        'I'  => 'text-blue-700 font-black bg-blue-50 rounded border border-blue-200/50',
                                        'A'  => 'text-rose-700 font-black bg-rose-50 rounded border border-rose-200/50',
                                        'L'  => 'text-slate-400 font-semibold bg-slate-100/90 rounded border border-slate-200/50 cursor-not-allowed',
                                        'NM' => 'text-indigo-500 font-semibold bg-indigo-50 rounded border border-indigo-200/50 cursor-not-allowed text-[9px]',
                                        default => 'text-slate-300',
                                    };
                                @endphp
                                <td class="py-2 px-1 text-center" title="{{ $code === 'L' ? 'Hari Libur / Jumat' : ($code === 'NM' ? 'Non-Muslim' : ($code === 'I' ? 'Ijin / Halangan' : ($code === 'H' ? 'Hadir' : ($code === 'A' ? 'Alpa' : '')))) }}">
                                    <div class="w-6 h-6 mx-auto flex items-center justify-center text-[11px] {{ $colorClass }}">
                                        {{ $code }}
                                    </div>
                                </td>
                            @endfor
                            <td class="py-3 px-3 text-center font-black bg-emerald-50/40 {{ ($stStats['is_non_muslim'] ?? false) ? 'text-indigo-400' : 'text-emerald-700' }}">
                                {{ ($stStats['is_non_muslim'] ?? false) ? '-' : $hadirCount }}
                            </td>
                            <td class="py-3 px-3 text-center font-black bg-blue-50/40 {{ ($stStats['is_non_muslim'] ?? false) ? 'text-indigo-400' : 'text-blue-700' }}">
                                {{ ($stStats['is_non_muslim'] ?? false) ? '-' : $ijinCount }}
                            </td>
                            <td class="py-3 px-3 text-center font-black bg-rose-50/40 {{ ($stStats['is_non_muslim'] ?? false) ? 'text-indigo-400' : 'text-rose-700' }}">
                                {{ ($stStats['is_non_muslim'] ?? false) ? '-' : $alpaCount }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold bg-slate-50/50 {{ ($stStats['is_non_muslim'] ?? false) ? 'text-indigo-400 italic text-[10px]' : 'text-slate-700' }}">
                                {{ ($stStats['is_non_muslim'] ?? false) ? 'N/A' : $pct . '%' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 5 }}" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-300">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                                <p class="font-bold text-slate-700">Belum ada siswa di kelas ini</p>
                                <p class="text-xs text-slate-400 mt-0.5">Pastikan rombel siswa aktif di tahun ajaran yang dipilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
