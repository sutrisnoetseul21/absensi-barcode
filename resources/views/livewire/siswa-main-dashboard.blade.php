<div class="p-4 lg:p-8 space-y-8 max-w-7xl mx-auto pb-16">
    <!-- Header / Welcome Hero Card -->
    <div class="relative bg-gradient-to-br from-indigo-700 via-indigo-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-900/20 overflow-hidden border border-indigo-500/20">
        <!-- Decorative Glow Shapes -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-violet-500/20 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-indigo-200 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Portal Siswa & Akademik
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                    Selamat Datang, {{ $student?->nama_lengkap ?? 'Siswa' }}!
                </h1>
                <p class="text-indigo-200/90 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                    Pantau kehadiran harian Anda, ajukan permohonan ijin, cek peminjaman buku perpustakaan, dan cetak kartu siswa mandiri.
                </p>
            </div>

            <!-- Quick Summary Badges -->
            <div class="flex flex-wrap sm:flex-nowrap gap-3 items-center self-start md:self-center">
                <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-3 min-w-[120px]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-300 block">Kelas Aktif</span>
                    <span class="text-xl font-extrabold text-white mt-0.5 block truncate">{{ $kelasName }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-3 min-w-[120px]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-300 block">NISN</span>
                    <span class="text-xl font-extrabold text-white mt-0.5 block">{{ $student?->nisn ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Main Shortcut Cards Grid (Fitur Utama Siswa) -->
    <div class="space-y-3">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Pintasan Menu Utama Siswa
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Akses cepat ke seluruh layanan dan modul portal siswa:</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            
            <!-- Shortcut 1: Presensi & Akademik -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-extrabold {{ $attendancePercentage >= 90 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : ($attendancePercentage >= 75 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200') }} px-2.5 py-1 rounded-xl border">
                            {{ $attendancePercentage }}% Hadir
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">
                        Presensi & Akademik
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Lihat riwayat jam masuk dan pulang, statistik bulanan, serta rekap kehadiran kelas.
                    </p>
                </div>
                <a href="{{ route('portal-siswa.akademik') }}" 
                   class="mt-4 w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-emerald-600 hover:text-white text-slate-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 hover:border-emerald-600">
                    Buka Presensi
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
            </div>

            <!-- Shortcut 2: Pengajuan Ijin -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-rose-200 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-105 group-hover:bg-rose-600 group-hover:text-white transition-all shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        @if($pendingIjinCount > 0)
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-xl border border-rose-200">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                </span>
                                {{ $pendingIjinCount }} Menunggu
                            </span>
                        @else
                            <span class="text-xs font-semibold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-200/60">
                                {{ $totalIjinCount }} Pengajuan
                            </span>
                        @endif
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-rose-600 transition-colors">
                        Pengajuan Ijin
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                        Ajukan surat ijin atau keterangan sakit ke wali kelas.
                    </p>
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" title="Disetujui">Diterima: {{ $approvedIjinCount }}</span>
                        <span class="text-[10px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200" title="Ditolak">Ditolak: {{ $rejectedIjinCount }}</span>
                        <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200" title="Menunggu Persetujuan">Menunggu: {{ $pendingIjinCount }}</span>
                    </div>
                </div>
                <a href="{{ route('portal-siswa.ijin') }}" 
                   class="mt-4 w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-rose-600 hover:text-white text-slate-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 hover:border-rose-600">
                    Buka Ijin
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
            </div>

            <!-- Shortcut 3: Perpustakaan -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-amber-200 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-105 group-hover:bg-amber-600 group-hover:text-white transition-all shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="text-xs font-extrabold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-xl border border-amber-200">
                            {{ $activeBooksCount }} Dipinjam
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-amber-600 transition-colors">
                        Perpustakaan
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Cari koleksi katalog perpustakaan dan pantau batas waktu pengembalian buku pinjaman.
                    </p>
                </div>
                <a href="{{ route('portal-siswa.perpustakaan') }}" 
                   class="mt-4 w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-amber-600 hover:text-white text-slate-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 hover:border-amber-600">
                    Buka Perpustakaan
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
            </div>

            <!-- Shortcut 4: Cetak Kartu Saya -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-violet-200 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center group-hover:scale-105 group-hover:bg-violet-600 group-hover:text-white transition-all shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold text-violet-700 bg-violet-50 px-2 py-0.5 rounded-lg border border-violet-100">
                            PDF Mandiri
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-violet-600 transition-colors">
                        Cetak Kartu Saya
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Cetak kartu identitas presensi barcode & QR code mandiri langsung dalam format PDF.
                    </p>
                </div>
                <a href="{{ route('portal-siswa.cetak-kartu') }}" 
                   target="_blank"
                   class="mt-4 w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-violet-600 hover:text-white text-slate-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 hover:border-violet-600">
                    Cetak Kartu
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
            </div>

            <!-- Shortcut 5: Profil Saya -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100">
                            Biodata
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                        Profil Saya
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Lihat kelengkapan biodata diri, nomor induk, data orang tua, dan ganti kata sandi akun.
                    </p>
                </div>
                <a href="{{ route('portal-siswa.profil') }}" 
                   class="mt-4 w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-indigo-600 hover:text-white text-slate-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 hover:border-indigo-600">
                    Lihat Profil
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
            </div>

        </div>
    </div>

    <!-- Announcements Section (if any) -->
    @if($activeAnnouncements->count() > 0)
        <div class="bg-amber-50/80 border border-amber-200/80 rounded-3xl p-6 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-500"></div>
            <div class="flex items-start gap-4">
                <div class="bg-amber-100 p-3 rounded-2xl text-amber-700 flex-shrink-0 mt-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-bold text-amber-950 mb-3">Pengumuman Sekolah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($activeAnnouncements as $announcement)
                            <div class="bg-white/80 backdrop-blur-xs p-4 rounded-2xl border border-amber-100 shadow-xs">
                                <h4 class="font-bold text-slate-800 text-sm">{{ $announcement->judul }}</h4>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $announcement->isi }}</p>
                                <span class="text-[10px] text-amber-700/80 font-semibold mt-2.5 block">{{ $announcement->created_at->translatedFormat('d F Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
