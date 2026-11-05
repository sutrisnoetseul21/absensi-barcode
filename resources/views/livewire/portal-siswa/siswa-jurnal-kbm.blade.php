<div class="p-4 lg:p-8 space-y-8 max-w-7xl mx-auto pb-20 font-jakarta">

    <!-- 1. Header Hero Card -->
    <div class="relative bg-gradient-to-r from-indigo-800 via-indigo-700 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-900/20 overflow-hidden border border-indigo-500/20">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500/20 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-indigo-200 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    📖 Jurnal Mengajar & Presensi KBM
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                    Jurnal KBM Siswa
                </h1>
                <p class="text-indigo-100/90 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                    Pantau kehadiran Anda pada setiap mata pelajaran berdasarkan pertemuan yang telah terlaksana dan diinput oleh guru pengampu.
                </p>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap gap-3 items-center self-start md:self-center">
                <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-3 min-w-[120px]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-200 block">Kelas</span>
                    <span class="text-lg font-extrabold text-white mt-0.5 block truncate">{{ $kelasName }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-3 min-w-[120px]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-200 block">Tahun Ajaran</span>
                    <span class="text-lg font-extrabold text-white mt-0.5 block truncate">{{ $tahunAjaranName }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Kontrol Filter Periode & Ringkasan Kehadiran -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Periode Pertemuan
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Pilih periode semester atau filter per bulan tertentu:</p>
        </div>

        <div class="relative w-full sm:w-72">
            <select wire:model.live="selectedMonthYear" class="w-full pl-4 pr-10 py-2.5 text-sm font-bold text-slate-800 bg-white border border-slate-200 rounded-2xl shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none cursor-pointer">
                @foreach($availableMonths as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>

    <!-- 3. Kartu Ringkasan KBM Terdata (Indigo Card) -->
    <div class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 rounded-3xl p-6 sm:p-7 text-white shadow-xl shadow-indigo-600/20 border border-indigo-400/20">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 pb-6 border-b border-white/15">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center shadow-inner">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Rekapitulasi Kehadiran KBM</h3>
                    <p class="text-xs text-indigo-100/80 mt-0.5">Dihitung otomatis dari jurnal pembelajaran yang telah dibuat guru</p>
                </div>
            </div>

            <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 self-start sm:self-auto">
                <svg class="w-5 h-5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-indigo-200 block leading-tight">Persentase</span>
                    <span class="text-xl font-extrabold text-white">{{ number_format($summary['persentase_kehadiran'], 1) }}%</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-6 text-center">
            <div class="bg-black/15 backdrop-blur-md rounded-2xl p-3 border border-white/10">
                <span class="text-xl sm:text-2xl font-black text-white block">{{ $summary['total_pertemuan_terdata'] }}</span>
                <span class="text-[11px] font-semibold text-indigo-200 block mt-0.5">Pertemuan Terdata</span>
            </div>
            <div class="bg-black/15 backdrop-blur-md rounded-2xl p-3 border border-white/10">
                <span class="text-xl sm:text-2xl font-black text-emerald-300 block">{{ $summary['total_hadir'] }}</span>
                <span class="text-[11px] font-semibold text-indigo-200 block mt-0.5">Hadir</span>
            </div>
            <div class="bg-black/15 backdrop-blur-md rounded-2xl p-3 border border-white/10">
                <span class="text-xl sm:text-2xl font-black text-blue-300 block">{{ $summary['total_izin'] }}</span>
                <span class="text-[11px] font-semibold text-indigo-200 block mt-0.5">Izin</span>
            </div>
            <div class="bg-black/15 backdrop-blur-md rounded-2xl p-3 border border-white/10">
                <span class="text-xl sm:text-2xl font-black text-amber-300 block">{{ $summary['total_sakit'] }}</span>
                <span class="text-[11px] font-semibold text-indigo-200 block mt-0.5">Sakit</span>
            </div>
            <div class="bg-black/15 backdrop-blur-md rounded-2xl p-3 border border-white/10 col-span-2 sm:col-span-1">
                <span class="text-xl sm:text-2xl font-black text-rose-300 block">{{ $summary['total_alpa'] }}</span>
                <span class="text-[11px] font-semibold text-indigo-200 block mt-0.5">Alpa</span>
            </div>
        </div>
    </div>

    <!-- 4. Daftar 11-12 Mata Pelajaran Siswa -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Daftar Mata Pelajaran
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Klik mata pelajaran untuk melihat kronologi materi dan absensi:</p>
            </div>
            <span class="px-3 py-1 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold rounded-xl">
                {{ count($subjects) }} Mapel
            </span>
        </div>

        @if(empty($subjects))
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak Ada Mata Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Rombongan belajar Anda belum memiliki jadwal mata pelajaran aktif yang terdaftar.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($subjects as $item)
                    @php
                        $stats = $item['stats'];
                        $hasData = $item['has_data'];
                        $persen = $stats['persentase_kehadiran'];
                        $hadirColor = $persen >= 85 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : ($persen >= 70 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200');
                    @endphp

                    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all flex flex-col justify-between group">
                        <div>
                            <!-- Header Mapel Card -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-xs shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>

                                <div class="flex flex-col items-end gap-1">
                                    @if($hasData)
                                        <span class="text-xs font-black {{ $hadirColor }} px-2.5 py-1 rounded-xl border">
                                            {{ number_format($persen, 0) }}% Hadir
                                        </span>
                                        <span class="text-[10px] font-semibold text-slate-400">
                                            {{ $stats['hadir'] }}/{{ $stats['total_pertemuan_terdata'] }} Pertemuan
                                        </span>
                                    @else
                                        <span class="text-[11px] font-semibold text-slate-400 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-xl">
                                            Belum Ada Jurnal
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1">
                                {{ $item['nama_mapel'] }}
                            </h3>

                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span class="truncate">{{ $item['guru_pengampu']['name'] }}</span>
                            </p>

                            @if($hasData)
                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2 text-xs">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold text-[10px]">
                                        {{ $stats['total_pertemuan_terdata'] }} Pertemuan Terdata
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Action Button -->
                        <button type="button" wire:click="showDetail({{ $item['mata_pelajaran_id'] }})" class="mt-4 w-full py-2.5 px-4 rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 font-bold text-xs flex items-center justify-between transition-colors border border-slate-200/60 hover:border-indigo-200 cursor-pointer">
                            <span>Lihat Riwayat KBM</span>
                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 5. Modal Rincian Pertemuan Mapel Terpilih -->
    @if($selectedSubjectDetail)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-xs animate-fade-in"
             x-data
             @keydown.escape.window="$wire.closeDetail()">
            <div class="bg-white w-full max-w-3xl max-h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col border border-slate-200">

                <!-- Modal Header -->
                <div class="p-6 bg-gradient-to-r from-indigo-800 to-indigo-900 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-white/15 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-white">{{ $selectedSubjectDetail['nama_mapel'] }}</h3>
                            <p class="text-xs text-indigo-200 mt-0.5">Guru Pengampu: {{ $selectedSubjectDetail['guru_pengampu']['name'] }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetail" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Modal Mini Stats -->
                <div class="p-6 bg-slate-50 border-b border-slate-200/80 shrink-0">
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                        <div class="bg-white rounded-2xl p-2.5 border border-slate-200 shadow-xs">
                            <span class="text-lg font-black text-slate-800 block">{{ $selectedSubjectDetail['stats']['total_pertemuan_terdata'] }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Terdata</span>
                        </div>
                        <div class="bg-white rounded-2xl p-2.5 border border-emerald-200 shadow-xs">
                            <span class="text-lg font-black text-emerald-600 block">{{ $selectedSubjectDetail['stats']['hadir'] }}</span>
                            <span class="text-[10px] font-bold text-emerald-600 uppercase">Hadir</span>
                        </div>
                        <div class="bg-white rounded-2xl p-2.5 border border-blue-200 shadow-xs">
                            <span class="text-lg font-black text-blue-600 block">{{ $selectedSubjectDetail['stats']['izin'] }}</span>
                            <span class="text-[10px] font-bold text-blue-600 uppercase">Izin</span>
                        </div>
                        <div class="bg-white rounded-2xl p-2.5 border border-amber-200 shadow-xs">
                            <span class="text-lg font-black text-amber-600 block">{{ $selectedSubjectDetail['stats']['sakit'] }}</span>
                            <span class="text-[10px] font-bold text-amber-600 uppercase">Sakit</span>
                        </div>
                        <div class="bg-white rounded-2xl p-2.5 border border-rose-200 shadow-xs col-span-2 sm:col-span-1">
                            <span class="text-lg font-black text-rose-600 block">{{ $selectedSubjectDetail['stats']['alpa'] }}</span>
                            <span class="text-[10px] font-bold text-rose-600 uppercase">Alpa</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Meetings List (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-4 flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-bold text-slate-800">Riwayat Kronologis Pertemuan</h4>
                        <span class="text-xs text-slate-500 font-semibold">{{ count($selectedSubjectDetail['pertemuan_list']) }} Pertemuan</span>
                    </div>

                    @if(empty($selectedSubjectDetail['pertemuan_list']))
                        <div class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            <p class="text-sm font-semibold">Belum ada jurnal pertemuan yang diinput guru untuk mata pelajaran ini.</p>
                        </div>
                    @else
                        @foreach($selectedSubjectDetail['pertemuan_list'] as $pertemuan)
                            @php
                                $st = $pertemuan['status'];
                                $badgeStyle = match($st) {
                                    'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'izin' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'alpa' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-purple-50 text-purple-700 border-purple-200',
                                };
                            @endphp

                            <div class="bg-white rounded-2xl p-4.5 border border-slate-200/90 shadow-xs hover:border-indigo-200 transition-all space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-extrabold text-xs">
                                            Pertemuan {{ $pertemuan['pertemuan_ke'] }}
                                        </span>
                                        <span class="text-xs font-semibold text-slate-500">
                                            {{ $pertemuan['jam_ke_label'] }}
                                        </span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold border {{ $badgeStyle }}">
                                        {{ $pertemuan['status_label'] }}
                                    </span>
                                </div>

                                <div class="text-xs font-semibold text-slate-500 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span>{{ $pertemuan['hari'] }}, {{ $pertemuan['tanggal_formatted'] }}</span>
                                </div>

                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                                    <span class="font-bold text-slate-700 block mb-0.5">Materi Pembelajaran:</span>
                                    <span class="text-slate-900 font-medium">{{ $pertemuan['materi'] }}</span>
                                    @if(!empty($pertemuan['uraian_materi']))
                                        <p class="text-slate-500 mt-1 text-[11px] leading-relaxed">{{ $pertemuan['uraian_materi'] }}</p>
                                    @endif
                                </div>

                                @if(!empty($pertemuan['keterangan']))
                                    <p class="text-[11px] text-slate-500 italic pl-1">
                                        Catatan: {{ $pertemuan['keterangan'] }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0">
                    <button type="button" wire:click="closeDetail" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
