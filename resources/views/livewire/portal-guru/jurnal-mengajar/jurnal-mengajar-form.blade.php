<div class="space-y-6 max-w-7xl mx-auto pb-20">
    <!-- HERO HEADER CARD (Sesuai Tema Sistem) -->
    <div class="relative bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary border border-brand-primary/30 text-white rounded-3xl p-6 sm:p-8 shadow-xl overflow-hidden">
        <!-- Ambient Background Glows -->
        <div class="absolute -top-12 -right-12 w-80 h-80 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-80 h-80 bg-brand-secondary/20 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <!-- Top Breadcrumb & Badge -->
                <div class="flex flex-wrap items-center gap-2.5 mb-3">
                    <a href="{{ route('portal-guru.jurnal-mengajar') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold backdrop-blur-md border border-white/25 transition-all shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        <span>Daftar Jurnal</span>
                    </a>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-white/15 text-white border border-white/25 text-xs font-bold tracking-wide uppercase backdrop-blur-md">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Presensi & Jurnal Digital KBM
                    </span>
                    @if($isEdit)
                        <span class="px-2.5 py-1 rounded-xl bg-amber-400/25 text-amber-100 border border-amber-300/40 text-xs font-bold backdrop-blur-md">
                            Mode Edit
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight text-white">
                    {{ $isEdit ? 'Perbarui Jurnal Mengajar' : 'Tulis Jurnal Mengajar' }}
                </h1>
                <p class="text-white/80 text-sm sm:text-base mt-1.5 max-w-2xl leading-relaxed font-medium">
                    Dokumentasi agenda pembelajaran, kalkulasi JP otomatis, dan sinkronisasi langsung dengan kehadiran gerbang sekolah.
                </p>
            </div>

            <!-- Quick Status Badge -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 shrink-0">
                <div class="px-4 py-3 rounded-2xl bg-white/15 backdrop-blur-md border border-white/25 text-xs">
                    <span class="text-white/80 block font-medium">Tahun Ajaran Aktif</span>
                    <span class="text-white font-extrabold text-sm flex items-center gap-1.5 mt-0.5">
                        <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ \App\Models\TahunAjaran::find($academic_year_id)?->name ?? 'Tahun Aktif' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('error'))
        <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-200/90 flex items-start gap-3.5 shadow-sm">
            <div class="p-2 rounded-xl bg-rose-100 text-rose-700 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-rose-900">Peringatan Validasi</h3>
                <p class="text-xs sm:text-sm text-rose-700 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- LANGKAH 1: Parameter Pembelajaran & Jadwal KBM -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-6 sm:p-8 transition-all hover:shadow-md">
            <!-- Header Langkah -->
            <div class="flex items-center gap-3.5 border-b border-slate-100 pb-4 mb-6">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary text-white flex items-center justify-center font-black text-sm shadow-md shadow-brand-primary/25 shrink-0">
                    01
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                        Parameter Pembelajaran & Jadwal Waktu
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500">
                        Tentukan rombel kelas binaan, mata pelajaran, serta durasi jam pelajaran.
                    </p>
                </div>
            </div>

            <!-- Grid Input 4 Kolom -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Guru Pengampu (Read-Only) -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        <span>Guru Pengampu</span>
                    </label>
                    <div class="relative">
                        <input type="text" value="{{ $teacher_name }}" readonly 
                                class="w-full px-3.5 py-2.5 pl-9 rounded-2xl border border-slate-200 bg-slate-100/70 text-slate-700 text-sm font-bold cursor-not-allowed shadow-inner">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                </div>

                <!-- Tanggal Pelaksanaan KBM (Format Indonesia) -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Tanggal KBM <span class="text-rose-500">*</span></span>
                        </span>
                        <span class="text-[10px] font-bold text-brand-primary bg-brand-primary/10 px-2 py-0.5 rounded-md border border-brand-primary/20">
                            Format Indonesia
                        </span>
                    </label>
                    <div class="relative group">
                        <!-- Visual Display Input (Format: Kamis, 29 Oktober 2026) -->
                        <div class="w-full px-3.5 py-2.5 pl-9 pr-9 rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm font-bold shadow-xs flex items-center justify-between group-hover:border-slate-400 group-focus-within:ring-4 group-focus-within:ring-brand-primary/15 group-focus-within:border-brand-primary transition-all">
                            <span class="truncate">
                                {{ hari_tanggal_indonesia($tanggal) }}
                            </span>
                        </div>
                        <svg class="w-4 h-4 text-brand-primary absolute left-3 top-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-3 pointer-events-none group-hover:text-brand-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <!-- Native Date Input (Transparent Overlaid for Seamless Date Picker) -->
                        <input type="date" wire:model.live="tanggal" 
                               title="Klik untuk memilih tanggal KBM"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    </div>
                    @error('tanggal') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <!-- Mata Pelajaran (Dipilih Dulu) -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span>Mata Pelajaran <span class="text-rose-500">*</span></span>
                    </label>
                    @if($singleMapel && count($availableMapels) === 1)
                        <div class="px-3.5 py-2.5 rounded-2xl border border-emerald-300 bg-gradient-to-r from-emerald-50 to-teal-50 text-emerald-950 text-sm font-bold flex items-center justify-between shadow-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ $availableMapels[0]->nama_mapel }}</span>
                            </div>
                            <span class="text-[10px] font-extrabold text-emerald-800 bg-emerald-200/80 px-2 py-0.5 rounded-lg border border-emerald-300/60 uppercase tracking-wider">
                                Otomatis
                            </span>
                        </div>
                    @else
                        <select wire:model.live="mata_pelajaran_id" 
                                class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm font-semibold outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition-all shadow-xs hover:border-slate-400 cursor-pointer">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($availableMapels as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('mata_pelajaran_id') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <!-- Kelas Rombel (Baru aktif setelah Mapel dipilih) -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        <span>Kelas Rombel <span class="text-rose-500">*</span></span>
                    </label>
                    <div class="relative">
                        @if(!$mata_pelajaran_id)
                            <select disabled 
                                    class="w-full px-3.5 py-2.5 pr-8 rounded-2xl border border-slate-200 bg-slate-100 text-slate-400 text-sm font-semibold cursor-not-allowed shadow-inner opacity-75">
                                <option value="">-- Pilih Mapel Terlebih Dahulu --</option>
                            </select>
                        @else
                            <select wire:model.live="class_id" 
                                    class="w-full px-3.5 py-2.5 pr-8 rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm font-semibold outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition-all shadow-xs hover:border-slate-400 cursor-pointer animate-fade-in">
                                <option value="">-- Pilih Kelas Rombel --</option>
                                @foreach($availableClasses as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @error('class_id') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- INTERACTIVE TIME SLOT & SMART JP CALCULATOR STRIP -->
            <div class="mt-6 pt-5 border-t border-slate-100 bg-slate-50/80 rounded-3xl p-3.5 sm:p-5 border border-slate-200/90 shadow-inner">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-5 items-stretch">
                    
                    <!-- KOTAK 1: Jam Mulai (Ke-) 1 s.d. 8 -->
                    <div class="lg:col-span-4 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm flex flex-col justify-between transition-all hover:border-brand-primary/40 hover:shadow-md">
                        <div>
                            <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-slate-100">
                                <label class="text-xs font-extrabold text-slate-800 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-lg bg-brand-primary/10 text-brand-primary flex items-center justify-center text-[11px] font-black">1</span>
                                    <span>Jam Mulai (Ke-)</span>
                                </label>
                                <span class="text-[11px] font-bold text-brand-primary bg-brand-primary/10 px-2.5 py-0.5 rounded-lg border border-brand-primary/20 shadow-2xs">
                                    Jam Ke-{{ $jam_mulai }}
                                </span>
                            </div>

                            <!-- Pilihan Langsung Angka 1-9 (Tanpa Input Manual) -->
                            <div class="grid grid-cols-9 gap-1 sm:gap-1.5">
                                @foreach(range(1, 9) as $jamOpt)
                                    <button type="button" 
                                            wire:key="jam-mulai-btn-{{ $jamOpt }}"
                                            wire:click="setJamMulai({{ $jamOpt }})" 
                                            aria-label="Jam Mulai Ke-{{ $jamOpt }}"
                                            aria-pressed="{{ $jam_mulai === $jamOpt ? 'true' : 'false' }}"
                                            title="Jam Mulai Ke-{{ $jamOpt }}"
                                            class="h-10 sm:h-11 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center justify-center cursor-pointer select-none active:scale-95 {{ $jam_mulai === $jamOpt ? 'bg-gradient-to-br from-brand-primary to-brand-secondary text-white shadow-md shadow-brand-primary/30 ring-2 ring-brand-primary/25 scale-[1.03]' : 'bg-slate-50 text-slate-700 hover:text-brand-primary hover:bg-brand-primary/10 border border-slate-200/90 shadow-2xs' }}">
                                        {{ $jamOpt }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-slate-100 px-0.5">
                            <span class="text-[10px] text-slate-400 font-medium">Jam Awal KBM</span>
                            <span class="text-[10px] text-slate-600 font-bold bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">Maks. Jam ke-9</span>
                        </div>
                        @error('jam_mulai') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <!-- KOTAK 2: Durasi Pembelajaran 1 s.d. 5 JP -->
                    <div class="lg:col-span-4 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm flex flex-col justify-between transition-all hover:border-brand-secondary/40 hover:shadow-md">
                        <div>
                            <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-slate-100">
                                <label class="text-xs font-extrabold text-slate-800 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-lg bg-brand-secondary/10 text-brand-secondary flex items-center justify-center text-[11px] font-black">2</span>
                                    <span>Durasi Pembelajaran</span>
                                </label>
                                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200 shadow-2xs">
                                    40 Menit / JP
                                </span>
                            </div>

                            <!-- Pilihan Langsung 1-5 JP (Tanpa Input Manual) -->
                            <div class="grid grid-cols-5 gap-1.5 sm:gap-2">
                                @foreach(range(1, 5) as $jpOpt)
                                    <button type="button" 
                                            wire:key="durasi-jp-btn-{{ $jpOpt }}"
                                            wire:click="setDurasi({{ $jpOpt }})" 
                                            aria-label="{{ $jpOpt }} Jam Pelajaran"
                                            aria-pressed="{{ $durasi_jp === $jpOpt ? 'true' : 'false' }}"
                                            title="{{ $jpOpt }} JP ({{ $jpOpt * 40 }} Menit)"
                                            class="h-10 sm:h-11 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center justify-center cursor-pointer select-none active:scale-95 {{ $durasi_jp === $jpOpt ? 'bg-gradient-to-r from-brand-primary to-brand-secondary text-white shadow-md shadow-brand-primary/30 ring-2 ring-brand-secondary/25 scale-[1.03]' : 'bg-slate-50 text-slate-700 hover:text-brand-primary hover:bg-brand-primary/10 border border-slate-200/90 shadow-2xs' }}">
                                        {{ $jpOpt }} JP
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-slate-100 px-0.5">
                            <span class="text-[10px] text-slate-400 font-medium">Pilihan: 1 s.d. 5 JP</span>
                            <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/70">{{ $durasi_jp * 40 }} Menit KBM</span>
                        </div>
                        @error('durasi_jp') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <!-- KOTAK 3: Hasil Kalkulasi Waktu KBM -->
                    <div class="lg:col-span-4 bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary text-white rounded-2xl p-4 sm:p-5 shadow-lg shadow-brand-primary/25 border border-white/20 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-white/15">
                                <label class="text-xs font-extrabold text-white flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-lg bg-white/20 text-white flex items-center justify-center text-[11px] font-black">✓</span>
                                    <span>Hasil Kalkulasi Jadwal</span>
                                </label>
                                <span class="text-[11px] font-bold text-white bg-white/20 backdrop-blur-md px-2.5 py-0.5 rounded-lg border border-white/25 shadow-2xs">
                                    Otomatis
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 py-1">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/30">
                                        <svg class="w-5 h-5 text-white animate-spin" style="animation-duration: 15s;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="truncate">
                                        <span class="text-[10px] sm:text-[11px] font-semibold text-white/80 uppercase tracking-wider block">Rentang KBM Terjadwal</span>
                                        <h3 class="text-sm sm:text-base font-black tracking-tight leading-tight text-white mt-0.5 truncate">
                                            {{ $jam_ke_label }}
                                        </h3>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-block px-3 py-1 rounded-xl bg-white/25 backdrop-blur-md text-xs sm:text-sm font-black text-white border border-white/30 shadow-xs whitespace-nowrap">
                                        {{ $durasi_jp * 40 }} Menit
                                    </span>
                                    <span class="text-[10px] text-white/85 font-semibold block mt-0.5">
                                        {{ $durasi_jp }} JP × 40 mnt
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-white/15 px-0.5">
                            <span class="text-[10px] text-white/75 font-medium">Standar Durasi</span>
                            <span class="text-[10px] text-white font-extrabold bg-white/20 px-2 py-0.5 rounded-md">1 JP = 40 Menit</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- LANGKAH 2: Dokumentasi Materi & Refleksi Pembelajaran -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-6 sm:p-8 transition-all hover:shadow-md">
            <!-- Header Langkah -->
            <div class="flex items-center gap-3.5 border-b border-slate-100 pb-4 mb-6">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-brand-secondary to-brand-primary text-white flex items-center justify-center font-black text-sm shadow-md shadow-brand-secondary/25 shrink-0">
                    02
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                        Dokumentasi Materi Pokok & Refleksi KBM
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500">
                        Uraikan capaian pembelajaran, ringkasan aktivitas kelas, serta catatan evaluasi.
                    </p>
                </div>
            </div>

            <div class="space-y-5">
                <!-- Materi Pokok -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            <span>Materi Pokok / Bab / Topik Pembelajaran <span class="text-rose-500">*</span></span>
                        </span>
                        <span class="text-[11px] font-medium text-slate-400">Wajib diisi</span>
                    </label>
                    <input type="text" wire:model="materi" placeholder="Contoh: Bab 3 - Persamaan Linier Dua Variabel (SPLDV)" 
                           class="w-full px-4 py-3 rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm font-semibold outline-none focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all shadow-xs hover:border-slate-400">
                    @error('materi') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <!-- Uraian Aktivitas KBM -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                            <span>Uraian Aktivitas KBM & Penugasan <span class="text-rose-500">*</span></span>
                        </span>
                        <span class="text-[11px] font-medium text-slate-400">Minimal 5 karakter</span>
                    </label>
                    <textarea rows="3" wire:model="uraian_materi" 
                              placeholder="Deskripsikan secara ringkas kegiatan pembelajaran: apersepsi, penyampaian materi, diskusi kelompok, praktikum, latihan soal, atau evaluasi harian..." 
                              class="w-full px-4 py-3 rounded-2xl border border-slate-300 bg-white text-slate-900 text-sm font-normal outline-none focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all shadow-xs hover:border-slate-400 leading-relaxed"></textarea>
                    @error('uraian_materi') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <!-- Refleksi & Catatan Guru (Inspiratif) -->
                <div class="bg-gradient-to-br from-amber-50/70 via-orange-50/30 to-amber-50/50 rounded-2xl p-4 sm:p-5 border border-amber-200/80">
                    <label class="block text-xs font-extrabold text-amber-950 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                        <span>Refleksi / Catatan Tindak Lanjut Guru (Opsional)</span>
                    </label>
                    <textarea rows="2" wire:model="refleksi" 
                              placeholder="Catatan evaluasi ketercapaian kelas, nama siswa yang memerlukan bimbingan tambahan/remedial, catatan kedisiplinan, atau rencana tugas berikutnya..." 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300/80 bg-white text-slate-900 text-sm font-normal outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-xs leading-relaxed"></textarea>
                    @error('refleksi') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- LANGKAH 3: Sinkronisasi Presensi Siswa di Jam KBM -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-6 sm:p-8 transition-all hover:shadow-md">
            <!-- Header Langkah -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-emerald-500/25 shrink-0">
                        03
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                                Presensi Siswa Rombel di Jam KBM
                            </h2>
                            <span class="text-xs font-black text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                {{ count($studentList) }} Siswa
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500">
                            Terintegrasi otomatis dengan presensi gerbang. Siswa sakit/izin/alpa dikunci demi validitas.
                        </p>
                    </div>
                </div>

                <!-- Quick Batch Action Buttons -->
                @if(!empty($studentList))
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <button type="button" wire:click="markAllHadir" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm shadow-emerald-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            <span>Tandai Semua Hadir KBM</span>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Live Status Legend Chips -->
            <div class="flex flex-wrap items-center gap-2 mb-5 p-3 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-xs font-semibold text-slate-600">
                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mr-1">Petunjuk Status:</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-100/80 text-emerald-800 border border-emerald-200/60 font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Hadir Tepat Waktu
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-100/80 text-amber-800 border border-amber-200/60 font-bold">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Telat Gerbang
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-200 text-slate-700 border border-slate-300 font-bold">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span> Belum Scan Gerbang
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-rose-100/80 text-rose-800 border border-rose-200/60 font-bold">
                    <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    Sakit/Izin/Alpa (Terkunci)
                </span>
            </div>

            @if(empty($studentList))
                <!-- Empty State -->
                <div class="p-10 text-center bg-gradient-to-br from-slate-50 via-brand-primary/5 to-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shadow-inner">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-800">Daftar Siswa Belum Dimuat</h3>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1">
                        Silakan pilih <strong>Kelas Rombel</strong> pada Langkah 01 di atas untuk memuat seluruh peserta didik aktif dan presensi gerbangnya.
                    </p>
                </div>
            @else
                <!-- Presensi Student Table -->
                <div class="overflow-x-auto rounded-2xl border border-slate-200/90 shadow-xs">
                    <table class="w-full text-left text-sm">
                        <thead class="text-[11px] uppercase bg-slate-50/90 text-slate-500 font-extrabold tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3.5 w-12 text-center">No</th>
                                <th class="px-4 py-3.5">Peserta Didik</th>
                                <th class="px-4 py-3.5 text-center">Status Gerbang Pagi</th>
                                <th class="px-4 py-3.5 text-center">Kehadiran Jam KBM</th>
                                <th class="px-4 py-3.5">Catatan Khusus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($studentList as $studentId => $item)
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $item['is_locked'] ? 'bg-rose-50/30' : '' }}">
                                    <!-- No -->
                                    <td class="px-4 py-3.5 text-center text-xs font-bold text-slate-400">
                                        {{ $loop->iteration }}
                                    </td>

                                    <!-- Nama & NISN Siswa -->
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-primary/10 to-brand-secondary/20 text-brand-primary font-black text-xs flex items-center justify-center shrink-0 border border-brand-primary/20">
                                                {{ strtoupper(substr($item['name'], 0, 2)) }}
                                            </div>
                                            <div>
                                                <h4 class="font-extrabold text-slate-900 text-sm leading-tight">
                                                    {{ $item['name'] }}
                                                </h4>
                                                <span class="text-[11px] font-semibold text-slate-400 mt-0.5 block">
                                                    NIS/NISN: {{ $item['nis'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status Gerbang Pagi -->
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        @if($item['status_gerbang'] === 'hadir')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                ✓ Tepat Waktu
                                            </span>
                                        @elseif($item['status_gerbang'] === 'telat')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200 shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                ⏰ Telat di Gerbang
                                            </span>
                                        @elseif($item['status_gerbang'] === 'izin')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-blue-50 text-blue-800 border border-blue-200 shadow-xs">
                                                ✉️ Izin Resmi
                                            </span>
                                        @elseif($item['status_gerbang'] === 'sakit')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-purple-50 text-purple-800 border border-purple-200 shadow-xs">
                                                🩺 Sakit
                                            </span>
                                        @elseif($item['status_gerbang'] === 'alpa')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200 shadow-xs">
                                                ✕ Alpa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 shadow-xs">
                                                ⚪ Belum Scan
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Kehadiran Jam KBM -->
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        @if($item['is_locked'])
                                            <!-- Siswa Sakit/Izin/Alpa: Terkunci Aman -->
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-500 text-xs font-extrabold border border-slate-200 cursor-not-allowed shadow-xs" 
                                                 title="Siswa tercatat {{ strtoupper($item['status_gerbang']) }} di gerbang. Dilarang diabsenkan hadir di kelas tanpa lapor ke BK/Piket.">
                                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                <span>Tidak Mengikuti (Terkunci)</span>
                                            </div>
                                        @elseif($item['status_gerbang'] === 'belum_absen')
                                            <!-- Siswa Belum Absen: Hadirkan Telat Otomatis -->
                                            <button type="button" wire:click="toggleAbsenkanTelat('{{ $studentId }}')" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black transition-all {{ $item['diabsenkan_telat'] ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25 scale-[1.02]' : 'bg-slate-100 text-slate-700 hover:bg-amber-100 hover:text-amber-900 border border-slate-300' }}">
                                                @if($item['diabsenkan_telat'])
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                    <span>✓ Diabsenkan (Wajib Telat)</span>
                                                @else
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                    <span>+ Hadirkan (Wajib Telat)</span>
                                                @endif
                                            </button>
                                        @else
                                            <!-- Siswa Normal: Toggle Hadir / Tidak Hadir -->
                                            <div class="inline-flex p-1 rounded-2xl bg-slate-100 border border-slate-200/80 shadow-inner">
                                                <button type="button" wire:click="setStatusJurnal('{{ $studentId }}', 'hadir')"
                                                        class="px-3 py-1.5 text-xs font-black rounded-xl transition-all {{ $item['status_jurnal'] === 'hadir' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25 scale-[1.02]' : 'text-slate-600 hover:text-slate-900' }}">
                                                    ✓ Hadir
                                                </button>
                                                <button type="button" wire:click="setStatusJurnal('{{ $studentId }}', 'tidak_mengikuti')"
                                                        class="px-3 py-1.5 text-xs font-black rounded-xl transition-all {{ $item['status_jurnal'] === 'tidak_mengikuti' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/25 scale-[1.02]' : 'text-slate-600 hover:text-slate-900' }}">
                                                    ✕ Tidak Hadir
                                                </button>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Keterangan Khusus -->
                                    <td class="px-4 py-3.5">
                                        @if($item['is_locked'])
                                            <span class="text-xs text-rose-700 italic font-semibold">
                                                Tercatat {{ ucfirst($item['status_gerbang']) }} di gerbang pagi.
                                            </span>
                                        @elseif($item['status_jurnal'] === 'tidak_mengikuti')
                                            <input type="text" wire:model="studentList.{{ $studentId }}.keterangan" 
                                                   placeholder="Alasan tidak mengikuti KBM (misal: UKS, Bolos)..." 
                                                   class="w-full px-3 py-1.5 text-xs rounded-xl border border-rose-300 bg-rose-50/50 text-slate-800 outline-none focus:ring-2 focus:ring-rose-400 font-medium">
                                        @elseif($item['diabsenkan_telat'])
                                            <span class="text-xs text-amber-800 font-extrabold flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                                Otomatis dicatat TELAT di presensi sekolah
                                            </span>
                                        @else
                                            <input type="text" wire:model="studentList.{{ $studentId }}.keterangan" 
                                                   placeholder="Catatan opsional..." 
                                                   class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50/70 text-slate-800 outline-none focus:ring-2 focus:ring-brand-primary/20 font-medium hover:border-slate-300 transition-all">
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- STICKY FLOATING BOTTOM ACTION BAR -->
        <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-md rounded-3xl p-4 sm:p-5 border border-slate-200/90 shadow-xl shadow-slate-900/10 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Pastikan data kelas, materi KBM, dan absensi telah terisi dengan benar.</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('portal-guru.jurnal-mengajar') }}" 
                   class="px-5 py-2.5 rounded-2xl border border-slate-300 bg-white text-slate-700 text-sm font-extrabold hover:bg-slate-50 transition-all shadow-xs">
                    Batal
                </a>

                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-7 py-3 rounded-2xl bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary text-white text-sm font-black shadow-lg shadow-brand-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 cursor-pointer">
                    <span wire:loading.remove class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        <span>{{ $isEdit ? 'Simpan Perubahan Jurnal' : 'Simpan Jurnal Mengajar' }}</span>
                    </span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menyimpan Jurnal...</span>
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
