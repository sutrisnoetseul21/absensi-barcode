<div class="space-y-6 max-w-7xl mx-auto pb-20">
    <!-- HERO HEADER CARD -->
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-indigo-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-indigo-950/20 overflow-hidden border border-indigo-500/20">
        <!-- Ambient Background Glows -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-violet-500/15 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <!-- Top Badge -->
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 text-xs font-bold tracking-wide uppercase">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Portal Guru • KBM & Agenda Mengajar
                    </span>

                    @if($isSupervisor)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-amber-500/30 text-amber-200 border border-amber-400/40 text-xs font-bold shadow-xs">
                            <svg class="w-3.5 h-3.5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            Mode Supervisi Kepala Sekolah / Kurikulum
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight">
                    Jurnal Mengajar
                </h1>
                <p class="text-indigo-200/90 text-sm sm:text-base mt-1.5 max-w-2xl leading-relaxed">
                    @if($isSupervisor)
                        Pantau agenda pembelajaran, kehadiran kelas, dan catatan refleksi seluruh guru di sekolah.
                    @else
                        Dokumentasikan agenda pembelajaran harian, durasi JP, materi pokok, dan sinkronisasi kehadiran siswa.
                    @endif
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 self-start md:self-auto shrink-0">
                <a href="{{ route('portal-guru.jurnal-mengajar.create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-xs sm:text-sm font-black shadow-lg shadow-indigo-600/30 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Tulis Jurnal Baru</span>
                </a>
                
                <a href="{{ route('portal-guru.jurnal-mengajar.cetak', [
                        'academic_year_id' => $selectedAcademicYearId,
                        'teacher_id'       => $selectedTeacherId,
                        'mata_pelajaran_id'=> $selectedMapelId,
                        'class_id'         => $selectedClassId,
                        'date_from'        => $dateFrom,
                        'date_to'          => $dateTo,
                    ]) }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md text-xs sm:text-sm font-bold shadow-xs hover:scale-[1.02] active:scale-[0.98] transition-all">
                    <svg class="w-4 h-4 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    <span>Cetak Rekap PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('success'))
        <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 border border-emerald-200/90 flex items-start gap-3.5 shadow-sm">
            <div class="p-2 rounded-xl bg-emerald-100 text-emerald-700 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-emerald-900">Operasi Berhasil</h3>
                <p class="text-xs sm:text-sm text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-200/90 flex items-start gap-3.5 shadow-sm">
            <div class="p-2 rounded-xl bg-rose-100 text-rose-700 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-rose-900">Terjadi Kesalahan</h3>
                <p class="text-xs sm:text-sm text-rose-700 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- SUMMARY METRICS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Card 1: Total Sesi KBM -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition-all flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500/10 to-indigo-600/20 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-200/50 shadow-xs">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            </div>
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Total Sesi KBM</span>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight mt-0.5">
                    {{ number_format($totalJurnal) }}
                    <span class="text-xs font-bold text-slate-500 tracking-normal">Pertemuan</span>
                </h3>
            </div>
        </div>

        <!-- Card 2: Total JP -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition-all flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-teal-600/20 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200/50 shadow-xs">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Total Jam Pelajaran</span>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight mt-0.5">
                    {{ number_format($totalJp) }}
                    <span class="text-xs font-bold text-slate-500 tracking-normal">JP Terlaksana</span>
                </h3>
            </div>
        </div>

        <!-- Card 3: Rata-Rata Kehadiran -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition-all flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500/10 to-fuchsia-600/20 text-purple-600 flex items-center justify-center shrink-0 border border-purple-200/50 shadow-xs">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="flex-1">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Rata-Rata Kehadiran</span>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight mt-0.5">
                    {{ $avgKehadiran }}%
                </h3>
                <!-- Mini Progress Bar -->
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, (float)$avgKehadiran) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER BAR CARD -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-6 transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
            <h2 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                <span>Filter & Pencarian Jurnal</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Filter Tahun Ajaran -->
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1.5">Tahun Ajaran</label>
                <select wire:model.live="selectedAcademicYearId" class="w-full rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm px-3.5 py-2.5 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all font-semibold shadow-xs">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }} {{ $year->status === 'aktif' ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Guru (Khusus Mode Supervisi) -->
            @if($isSupervisor)
                <div>
                    <label class="block text-xs font-extrabold text-amber-900 mb-1.5 flex items-center justify-between">
                        <span>Pilih Guru Pengampu</span>
                        <span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.2 rounded font-black">Supervisi</span>
                    </label>
                    <select wire:model.live="selectedTeacherId" class="w-full rounded-2xl border border-amber-300 bg-amber-50/50 text-slate-900 text-sm px-3.5 py-2.5 outline-none focus:ring-4 focus:ring-amber-500/15 font-semibold shadow-xs">
                        <option value="">-- Semua Guru Pengampu --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filter Mata Pelajaran -->
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1.5">Mata Pelajaran</label>
                <select wire:model.live="selectedMapelId" class="w-full rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm px-3.5 py-2.5 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all font-semibold shadow-xs">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kelas Rombel -->
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1.5">Kelas Rombel</label>
                <select wire:model.live="selectedClassId" class="w-full rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm px-3.5 py-2.5 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all font-semibold shadow-xs">
                    <option value="">-- Semua Kelas Rombel --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Filter Baris 2: Search & Rentang Tanggal -->
        <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Cari Topik Materi -->
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1.5">Cari Materi / Refleksi</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Ketik kata kunci topik materi..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all shadow-xs font-medium">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>

            <!-- Tanggal Dari -->
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1.5">Tanggal Mulai</label>
                <input type="date" wire:model.live="dateFrom" 
                       class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all shadow-xs font-semibold">
            </div>

            <!-- Tanggal Sampai -->
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1.5">Tanggal Selesai</label>
                <input type="date" wire:model.live="dateTo" 
                       class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all shadow-xs font-semibold">
            </div>
        </div>
    </div>

    <!-- JURNAL LIST CARDS -->
    <div class="space-y-4">
        @forelse($jurnals as $jurnal)
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-lg transition-all p-6 group">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                    <!-- Left: Metadata & Info Materi -->
                    <div class="space-y-3 flex-1">
                        <!-- Badges Header -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Tanggal -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold bg-slate-100 text-slate-800 shadow-xs">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ $jurnal->tanggal->translatedFormat('l, d F Y') }}
                            </span>

                            <!-- Jam Ke Label -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-gradient-to-r from-indigo-50 to-blue-50 text-indigo-800 border border-indigo-200/80 shadow-xs">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $jurnal->jam_ke_label }}
                            </span>

                            <!-- Kelas Rombel -->
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-50 text-emerald-800 border border-emerald-200/80 shadow-xs">
                                🎓 Kelas {{ $jurnal->kelas?->name }}
                            </span>

                            <!-- Mata Pelajaran -->
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-extrabold bg-purple-50 text-purple-800 border border-purple-200/80 shadow-xs">
                                📖 {{ $jurnal->mataPelajaran?->nama_mapel }}
                            </span>

                            <!-- Guru Pengampu (Supervisi) -->
                            @if($isSupervisor && $jurnal->guru)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200/80 shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    {{ $jurnal->guru->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Materi Pokok & Uraian -->
                        <div class="pt-1">
                            <h3 class="text-base sm:text-lg font-black text-slate-900 group-hover:text-indigo-600 transition-colors">
                                {{ $jurnal->materi }}
                            </h3>
                            <p class="text-sm text-slate-600 mt-1 leading-relaxed whitespace-pre-line bg-slate-50/60 p-3 rounded-2xl border border-slate-100">
                                {{ $jurnal->uraian_materi }}
                            </p>
                        </div>

                        <!-- Refleksi Guru jika ada -->
                        @if($jurnal->refleksi)
                            <div class="p-3.5 rounded-2xl bg-gradient-to-r from-amber-50/80 to-orange-50/50 border border-amber-200/80 text-xs text-amber-950 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                                <div>
                                    <span class="font-black text-amber-900">Refleksi / Catatan Tindak Lanjut:</span>
                                    <span class="ml-1 text-slate-700">{{ $jurnal->refleksi }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Kehadiran Siswa & Action Buttons -->
                    <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between gap-4 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100 shrink-0">
                        <!-- Indikator Kehadiran -->
                        <div class="text-left lg:text-right">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block">Tingkat Kehadiran</span>
                            <div class="flex items-center gap-1.5 lg:justify-end mt-0.5">
                                <span class="text-base font-black text-emerald-700">{{ $jurnal->total_hadir }}/{{ $jurnal->total_siswa }}</span>
                                <span class="text-xs font-extrabold text-slate-500">({{ $jurnal->persentase_kehadiran }}%)</span>
                            </div>

                            @if($jurnal->total_tidak_mengikuti > 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 mt-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    {{ $jurnal->total_tidak_mengikuti }} Tidak Hadir
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 mt-1">
                                    ✓ Semua Hadir
                                </span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2">
                            @if(!$isSupervisor || auth()->user()->isSuperAdmin() || $jurnal->teacher_id === $currentTeacherId)
                                <a href="{{ route('portal-guru.jurnal-mengajar.edit', $jurnal->id) }}" 
                                   class="p-2.5 rounded-2xl text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 transition-all shadow-xs" title="Edit Jurnal">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>

                                <button wire:click="delete('{{ $jurnal->id }}')" 
                                        wire:confirm="Yakin ingin menghapus catatan jurnal mengajar tanggal {{ $jurnal->tanggal->format('d/m/Y') }} ini?"
                                        class="p-2.5 rounded-2xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-300 transition-all shadow-xs cursor-pointer" title="Hapus Jurnal">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-slate-200/90 p-12 text-center shadow-sm">
                <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-indigo-50 text-indigo-500 flex items-center justify-center shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                </div>
                <h3 class="text-base font-extrabold text-slate-800">Belum Ada Catatan Jurnal Mengajar</h3>
                <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto mt-1">
                    Belum ada riwayat pembelajaran yang tercatat pada filter tahun ajaran, guru, atau kelas yang dipilih.
                </p>
                <div class="mt-6">
                    <a href="{{ route('portal-guru.jurnal-mengajar.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-xs sm:text-sm font-black shadow-lg shadow-indigo-600/25 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        <span>Tulis Jurnal Baru Sekarang</span>
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $jurnals->links() }}
    </div>
</div>
