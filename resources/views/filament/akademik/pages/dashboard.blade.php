<x-filament-panels::page>
    <div class="space-y-6" x-data="{ quickModal: null }" @keydown.escape.window="quickModal = null">
        {{-- Hero Banner (Mengikuti Pengaturan Tema) --}}
        <div class="relative rounded-3xl p-6 sm:p-7 text-white shadow-lg overflow-hidden"
             style="background: linear-gradient(135deg, {{ $themePrimary }}, {{ $themeSecondary }});">
            <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/10 rounded-full filter blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-[11px] font-bold uppercase tracking-wider">
                            Portal Akademik &amp; TU
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-[11px] font-bold">
                            TA {{ $activeYearName }} &bull; Semester {{ $activeSemester }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold {{ $isHariSekolah ? 'bg-emerald-400/25 text-emerald-50 border border-emerald-300/40' : 'bg-amber-400/25 text-amber-50 border border-amber-300/40' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isHariSekolah ? 'bg-emerald-300' : 'bg-amber-300' }}"></span>
                            {{ $isHariSekolah ? 'Hari Sekolah Aktif' : 'Libur: ' . $holidayLabel }}
                        </span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-3">Dashboard Portal Akademik</h2>
                    <p class="text-sm text-white/85 mt-1">
                        Halo, <strong class="text-white">{{ $userName }}</strong> &bull; {{ $todayFormatted }}. Kelola data induk guru, siswa, rombel, supervisi KBM, Guru Wali, dan ekstrakurikuler.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    @if($canSiswaCreate)
                        <a href="{{ $urlSiswaCreate }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-primary-700 rounded-2xl text-xs sm:text-sm font-extrabold hover:bg-primary-50 transition-colors shadow-md">
                            <x-filament::icon icon="heroicon-o-user-plus" class="w-4 h-4" />
                            Tambah Siswa
                        </a>
                    @endif
                    @if($canGuruCreate)
                        <a href="{{ $urlGuruCreate }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white border border-white/25 rounded-2xl text-xs sm:text-sm font-bold transition-colors backdrop-blur-sm">
                            <x-filament::icon icon="heroicon-o-briefcase" class="w-4 h-4" />
                            Tambah Guru
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4 Kartu Statistik Utama --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-o-users" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">{{ $totalSiswaAktif }}</div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Siswa Aktif</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-o-briefcase" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">{{ $totalGuru }}</div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Guru Terdaftar</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-o-home-modern" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">{{ $totalKelas }}</div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Kelas / Rombel</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-o-book-open" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">{{ $totalJurnalHariIni }}</div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Jurnal KBM Hari Ini</div>
                </div>
            </div>
        </div>

        {{-- Blok 1: Data Master & Operasional Kesiswaan --}}
        <div>
            <div class="mb-3 px-0.5">
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                    Data Master &amp; Operasional Kesiswaan
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pengelolaan data guru, siswa, rombongan belajar, kenaikan kelas, serta arsip kelulusan/mutasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                {{-- 1. Data Guru (POP-UP) --}}
                @if($canGuru)
                    <button type="button" @click="quickModal = 'guru'"
                       class="text-left w-full bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-sky-400 dark:hover:border-sky-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900 flex items-center justify-center group-hover:bg-sky-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-briefcase" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-900">
                                    {{ $totalGuru }} Guru
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Data Guru</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Tambah &amp; edit profil guru</li>
                                <li>&bull; Import Excel &amp; jabatan</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-sky-600 dark:text-sky-400 w-full">
                            <span>Pilih Tambah / Kelola</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </button>
                @endif

                {{-- 2. Data Siswa (POP-UP) --}}
                @if($canSiswa)
                    <button type="button" @click="quickModal = 'siswa'"
                       class="text-left w-full bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-emerald-400 dark:hover:border-emerald-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-users" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900">
                                    {{ $totalSiswaAktif }} Aktif
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Data Siswa</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Tambah &amp; import siswa baru</li>
                                <li>&bull; Cetak kartu &amp; akun siswa</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400 w-full">
                            <span>Pilih Tambah / Kelola</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </button>
                @endif

                {{-- 3. Data Kelas (POP-UP) --}}
                @if($canKelas)
                    <button type="button" @click="quickModal = 'kelas'"
                       class="text-left w-full bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-amber-400 dark:hover:border-amber-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-home-modern" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-900">
                                    {{ $totalKelas }} Kelas
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Data Kelas</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Rombel, wali kelas &amp; mapel</li>
                                <li>&bull; Import template kelas</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400 w-full">
                            <span>Pilih Tambah / Kelola</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </button>
                @endif

                {{-- 4. Penempatan & Naik Kelas --}}
                @if($canEnrollment)
                    <a href="{{ $urlEnrollment }}"
                       class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-indigo-400 dark:hover:border-indigo-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900">
                                    Enrollment
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Penempatan &amp; Naik Kelas</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Plotting siswa ke rombel</li>
                                <li>&bull; Proses kenaikan kelas massal</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            <span>Buka Penempatan</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                {{-- 5. Pindah Kelas --}}
                @if($canPindahKelas)
                    <a href="{{ $urlPindahKelas }}"
                       class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-cyan-400 dark:hover:border-cyan-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-950/50 text-cyan-600 dark:text-cyan-400 border border-cyan-100 dark:border-cyan-900 flex items-center justify-center group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-arrows-right-left" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-100 dark:border-cyan-900">
                                    Antar Rombel
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">Pindah Kelas</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Mutasi siswa antar kelas</li>
                                <li>&bull; Riwayat perpindahan kelas</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-cyan-600 dark:text-cyan-400">
                            <span>Buka Pindah Kelas</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                {{-- 6. Siswa Lulus --}}
                @if($canSiswaLulus)
                    <a href="{{ $urlSiswaLulus }}"
                       class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-teal-400 dark:hover:border-teal-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-100 dark:border-teal-900">
                                    {{ $totalSiswaLulus }} Lulus
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">Siswa Lulus</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Arsip alumni &amp; tahun lulus</li>
                                <li>&bull; Sinkron data tracer alumni</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-teal-600 dark:text-teal-400">
                            <span>Lihat Siswa Lulus</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                {{-- 7. Siswa Mutasi --}}
                @if($canSiswaMutasi)
                    <a href="{{ $urlSiswaMutasi }}"
                       class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-rose-400 dark:hover:border-rose-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-100 dark:border-rose-900">
                                    {{ $totalSiswaMutasi }} Mutasi
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">Siswa Mutasi</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Catatan siswa pindah keluar</li>
                                <li>&bull; Tujuan &amp; tanggal mutasi</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-rose-600 dark:text-rose-400">
                            <span>Lihat Siswa Mutasi</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                {{-- 8. Portal Setup (jika berhak akses) --}}
                @if($canPortalSetup)
                    <a href="{{ url('/portal-setup') }}"
                       class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-slate-400 dark:hover:border-slate-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 flex items-center justify-center group-hover:bg-gray-800 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    Fondasi
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm">Portal Setup Awal</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Tahun ajaran &amp; identitas</li>
                                <li>&bull; Master jabatan &amp; mapel</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
                            <span>Buka Portal Setup</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif
            </div>
        </div>

        {{-- Blok 2: Supervisi, Monitoring & Ekstrakurikuler (Berbasis Pop-Up) --}}
        @if($canJurnalGroup || $canGwGroup || $canEkskulGroup)
        <div>
            <div class="mb-3 px-0.5">
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Supervisi Pembelajaran, Guru Wali &amp; Ekstrakurikuler
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Klik kotak modul di bawah untuk memilih menu data, monitoring, atau analisa siswa.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                {{-- 9. Jurnal Mengajar (POP-UP 3 Menu) --}}
                @if($canJurnalGroup)
                    <button type="button" @click="quickModal = 'jurnal'"
                       class="text-left w-full bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-blue-400 dark:hover:border-blue-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-book-open" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-900">
                                    {{ $totalJurnalSemua }} Jurnal
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Jurnal Mengajar (KBM)</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Data &amp; cetak jurnal KBM</li>
                                <li>&bull; Monitoring guru &amp; analisa siswa</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-blue-600 dark:text-blue-400 w-full">
                            <span>Pilih Menu KBM</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </button>
                @endif

                {{-- 10. Guru Wali / SI-WALI (POP-UP 4 Menu) --}}
                @if($canGwGroup)
                    <button type="button" @click="quickModal = 'guruwali'"
                       class="text-left w-full bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-purple-400 dark:hover:border-purple-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-user-group" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-900">
                                    {{ $totalKelompokGuruWali }} Kelompok
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Guru Wali (SI-WALI)</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Kelompok &amp; log bimbingan</li>
                                <li>&bull; Monitoring &amp; analisa siswa</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-purple-600 dark:text-purple-400 w-full">
                            <span>Pilih Menu Guru Wali</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </button>
                @endif

                {{-- 11. Ekstrakurikuler (POP-UP 3 Menu) --}}
                @if($canEkskulGroup)
                    <button type="button" @click="quickModal = 'ekskul'"
                       class="text-left w-full bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-indigo-400 dark:hover:border-indigo-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-sparkles" class="w-5 h-5" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900">
                                    {{ $totalEkskulAktif }} Ekskul
                                </span>
                            </div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Ekstrakurikuler</h4>
                            <ul class="mt-1.5 space-y-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>&bull; Data ekskul, pembina &amp; anggota</li>
                                <li>&bull; Monitoring kehadiran &amp; jurnal</li>
                            </ul>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-indigo-600 dark:text-indigo-400 w-full">
                            <span>Pilih Menu Ekskul</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </button>
                @endif
            </div>
        </div>
        @endif

        {{-- POP-UP PILIHAN CEPAT --}}
        <div x-show="quickModal !== null" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="quickModal = null">

            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 w-full max-w-md overflow-hidden"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2">

                {{-- Pop-up: Data Guru --}}
                @if($canGuru)
                <template x-if="quickModal === 'guru'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-sky-600 to-blue-600 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <x-filament::icon icon="heroicon-o-briefcase" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">Data Guru</h3>
                                    <p class="text-xs text-white/85">Pilih tambah guru baru atau kelola daftar</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5">
                            @if($canGuruCreate)
                                <a href="{{ $urlGuruCreate }}"
                                   class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-sky-200 dark:border-sky-800 bg-sky-50/70 dark:bg-sky-950/30 hover:bg-sky-100/80 transition-colors group">
                                    <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center shrink-0">
                                        <x-filament::icon icon="heroicon-o-plus" class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Tambah Guru Baru</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Input data guru &amp; akun login baru</div>
                                    </div>
                                    <span class="text-sky-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            <a href="{{ $urlGuruIndex }}"
                               class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 flex items-center justify-center shrink-0">
                                    <x-filament::icon icon="heroicon-o-list-bullet" class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-extrabold text-sm text-gray-900 dark:text-white">Lihat &amp; Kelola Data Guru</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Total {{ $totalGuru }} guru &bull; import Excel &amp; jabatan</div>
                                </div>
                                <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </template>
                @endif

                {{-- Pop-up: Data Siswa --}}
                @if($canSiswa)
                <template x-if="quickModal === 'siswa'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <x-filament::icon icon="heroicon-o-users" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">Data Siswa</h3>
                                    <p class="text-xs text-white/85">Pilih tambah siswa baru atau kelola daftar</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5">
                            @if($canSiswaCreate)
                                <a href="{{ $urlSiswaCreate }}"
                                   class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-950/30 hover:bg-emerald-100/80 transition-colors group">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                        <x-filament::icon icon="heroicon-o-user-plus" class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Tambah Siswa Baru</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Input biodata &amp; NIS/NISN siswa baru</div>
                                    </div>
                                    <span class="text-emerald-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            <a href="{{ $urlSiswaIndex }}"
                               class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 flex items-center justify-center shrink-0">
                                    <x-filament::icon icon="heroicon-o-list-bullet" class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-extrabold text-sm text-gray-900 dark:text-white">Lihat &amp; Kelola Siswa Aktif</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Total {{ $totalSiswaAktif }} siswa &bull; import &amp; cetak kartu</div>
                                </div>
                                <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </template>
                @endif

                {{-- Pop-up: Data Kelas --}}
                @if($canKelas)
                <template x-if="quickModal === 'kelas'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-amber-500 to-orange-500 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <x-filament::icon icon="heroicon-o-home-modern" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">Data Kelas</h3>
                                    <p class="text-xs text-white/85">Pilih tambah kelas atau kelola rombel</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5">
                            @if($canKelasCreate)
                                <a href="{{ $urlKelasCreate }}"
                                   class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50/70 dark:bg-amber-950/30 hover:bg-amber-100/80 transition-colors group">
                                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                                        <x-filament::icon icon="heroicon-o-plus" class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Tambah Kelas Baru</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Buat kelas &amp; atur wali kelas/mapel</div>
                                    </div>
                                    <span class="text-amber-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            <a href="{{ $urlKelasIndex }}"
                               class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 flex items-center justify-center shrink-0">
                                    <x-filament::icon icon="heroicon-o-list-bullet" class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-extrabold text-sm text-gray-900 dark:text-white">Lihat &amp; Kelola Kelas</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Total {{ $totalKelas }} kelas terdaftar</div>
                                </div>
                                <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </template>
                @endif

                {{-- Pop-up: Jurnal Mengajar (KBM) --}}
                @if($canJurnalGroup)
                <template x-if="quickModal === 'jurnal'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <x-filament::icon icon="heroicon-o-book-open" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">Jurnal Mengajar (KBM)</h3>
                                    <p class="text-xs text-white/85">Pilih menu data atau supervisi KBM</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5">
                            @if($canJurnalData)
                                <a href="{{ $urlJurnalData }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-950/30 hover:bg-blue-100/80 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Data &amp; Cetak Jurnal</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Semua jurnal masuk ({{ $totalJurnalSemua }}) &amp; export cetak</div>
                                    </div>
                                    <span class="text-blue-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            @if($canJurnalMonitoring)
                                <a href="{{ $urlJurnalMonitoring }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Monitoring Guru &amp; Kelas</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Realisasi jam mengajar guru &amp; rekap per kelas</div>
                                    </div>
                                    <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            @if($canJurnalAnalisa)
                                <a href="{{ $urlJurnalAnalisa }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Analisa Presensi KBM Siswa</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Deteksi siswa sering alpa/ijin di jam pelajaran</div>
                                    </div>
                                    <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </template>
                @endif

                {{-- Pop-up: Guru Wali (SI-WALI) --}}
                @if($canGwGroup)
                <template x-if="quickModal === 'guruwali'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <x-filament::icon icon="heroicon-o-user-group" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">Guru Wali (SI-WALI)</h3>
                                    <p class="text-xs text-white/85">Pilih menu pengelolaan &amp; supervisi</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5">
                            @if($canGwKelompok)
                                <a href="{{ $urlGwKelompok }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-purple-200 dark:border-purple-800 bg-purple-50/70 dark:bg-purple-950/30 hover:bg-purple-100/80 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Kelompok Guru Wali</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Atur pembagian guru wali &amp; siswa binaan ({{ $totalKelompokGuruWali }})</div>
                                    </div>
                                    <span class="text-purple-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            @if($canGwMonitoring)
                                <a href="{{ $urlGwMonitoring }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Monitoring Guru Wali</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Pantau keaktifan pendampingan &amp; cetak supervisi</div>
                                    </div>
                                    <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            @if($canGwAnalisa)
                                <a href="{{ $urlGwAnalisa }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Analisa Bimbingan Siswa</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Statistik kasus, rujukan BK &amp; progres murid</div>
                                    </div>
                                    <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            @if($canGwJurnal)
                                <a href="{{ $urlGwJurnal }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Log Guru Wali</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Riwayat seluruh catatan bimbingan ({{ $totalLogGuruWali }})</div>
                                    </div>
                                    <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </template>
                @endif

                {{-- Pop-up: Ekstrakurikuler --}}
                @if($canEkskulGroup)
                <template x-if="quickModal === 'ekskul'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <x-filament::icon icon="heroicon-o-sparkles" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">Ekstrakurikuler</h3>
                                    <p class="text-xs text-white/85">Pilih tambah, kelola data, atau monitoring</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5">
                            @if($canEkskulCreate)
                                <a href="{{ $urlEkskulCreate }}"
                                   class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50/70 dark:bg-indigo-950/30 hover:bg-indigo-100/80 transition-colors group">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                                        <x-filament::icon icon="heroicon-o-plus" class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Tambah Ekstrakurikuler Baru</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Buat ekskul, atur pembina &amp; jadwal</div>
                                    </div>
                                    <span class="text-indigo-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            @if($canEkskulData)
                                <a href="{{ $urlEkskulIndex }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Data &amp; Anggota Ekskul</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Kelola {{ $totalEkskulAktif }} ekstrakurikuler aktif &amp; anggota</div>
                                    </div>
                                    <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                            @if($canEkskulMonitoring)
                                <a href="{{ $urlEkskulMonitoring }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-gray-900 dark:text-white">Monitoring Ekstrakurikuler</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Pantau pertemuan, jurnal latihan &amp; kehadiran</div>
                                    </div>
                                    <span class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </template>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>

