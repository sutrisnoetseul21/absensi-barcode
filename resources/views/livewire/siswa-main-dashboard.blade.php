<div class="p-4 lg:p-8 space-y-8 max-w-7xl mx-auto pb-16">

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- KONDISI 1: SISWA BERSTATUS LULUS (ALUMNI / TRACER STUDY)         --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    @if($student && $student->isLulus())
        
        <!-- Header Banner Khusus Alumni -->
        <div class="relative bg-gradient-to-br from-emerald-700 via-teal-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-teal-900/20 overflow-hidden border border-emerald-500/20">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-500/20 rounded-full filter blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-teal-500/20 rounded-full filter blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-emerald-200 mb-3">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        🎓 Portal Alumni & Tracer Study
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Halo, {{ $student->name }}!
                    </h1>
                    <p class="text-emerald-100/90 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                        Selamat atas kelulusan Anda! Mohon luangkan waktu sejenak untuk memperbarui data riwayat pendidikan atau karir terkini Anda untuk mendukung kemajuan sekolah kita.
                    </p>
                </div>

                <div class="flex flex-wrap sm:flex-nowrap gap-3 items-center self-start md:self-center">
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-3 min-w-[120px]">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-200 block">Status Siswa</span>
                        <span class="text-lg font-extrabold text-white mt-0.5 block">LULUS (ALUMNI)</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-3 min-w-[120px]">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-200 block">NISN</span>
                        <span class="text-lg font-extrabold text-white mt-0.5 block">{{ $student->nisn }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(session()->has('success_tracer'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                    <i class="fas fa-check text-sm"></i>
                </div>
                <p class="text-sm font-semibold">{{ session('success_tracer') }}</p>
            </div>
        @endif

        <!-- Formulir Tracer Study Alumni -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-graduation-cap text-emerald-600"></i> Form Pembaruan Tracer Study
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Isi informasi terkini mengenai kelanjutan studi atau aktivitas Anda.</p>
                </div>
            </div>

            <form wire:submit="simpanTracerAlumni" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Nama Lengkap (Readonly) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Nama Lengkap</label>
                        <input type="text" value="{{ $student->name }}" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-semibold text-sm cursor-not-allowed">
                    </div>

                    <!-- NISN (Readonly) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">NISN</label>
                        <input type="text" value="{{ $student->nisn }}" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-semibold text-sm cursor-not-allowed">
                    </div>

                    <!-- Tahun Kelulusan -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tahun Lulus <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="tahun_lulus_override" required min="2000" max="2099" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-semibold">
                        @error('tahun_lulus_override') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- No. HP / WhatsApp Terkini -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">No. HP / WhatsApp Aktif</label>
                        <input type="text" wire:model="no_hp_alumni" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        @error('no_hp_alumni') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status Melanjutkan Pendidikan -->
                    <div class="md:col-span-2 pt-2">
                        <label class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer hover:bg-slate-100 transition-colors">
                            <input type="checkbox" wire:model.live="status_melanjutkan" class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <div>
                                <span class="text-sm font-bold text-slate-800 block">Saya melanjutkan pendidikan (Sekolah / Perguruan Tinggi)</span>
                                <span class="text-xs text-slate-500 block">Centang jika Anda melanjutkan studi ke jenjang berikutnya.</span>
                            </div>
                        </label>
                    </div>

                    @if($status_melanjutkan)
                        <!-- Pilihan Jenjang Lanjutan -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jenjang Pendidikan Lanjutan <span class="text-red-500">*</span></label>
                            <select wire:model="jenjang_lanjutan_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-semibold bg-white">
                                <option value="">-- Pilih Jenjang --</option>
                                @foreach($jenjangs as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jenjang }}</option>
                                @endforeach
                            </select>
                            @error('jenjang_lanjutan_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nama Sekolah / Kampus Lanjutan -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Sekolah / Instansi / Kampus <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nama_sekolah_lanjutan" required placeholder="Contoh: SMA Negeri 1 Cilacap / Universitas Jenderal Soedirman" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            @error('nama_sekolah_lanjutan') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition-all">
                        <i class="fas fa-save"></i> Simpan Pembaruan Tracer Study
                    </button>
                </div>
            </form>
        </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- KONDISI 2: SISWA BERSTATUS MUTASI KELUAR                         --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    @elseif($student && $student->isMutasi())

        <!-- Header Banner Khusus Siswa Mutasi -->
        <div class="relative bg-gradient-to-br from-amber-600 via-orange-700 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-amber-900/20 overflow-hidden border border-amber-500/20">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-amber-500/20 rounded-full filter blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-orange-500/20 rounded-full filter blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-amber-200 mb-3">
                        <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                        🚚 Status Siswa: Mutasi Keluar / Pindah
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Halo, {{ $student->name }}!
                    </h1>
                    <p class="text-amber-100/90 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                        Akun Anda tercatat sebagai siswa yang telah mutasi (pindah sekolah). Mohon lengkapi informasi sekolah tujuan mutasi Anda untuk melengkapi arsip administrasi sekolah.
                    </p>
                </div>

                <div class="flex flex-wrap sm:flex-nowrap gap-3 items-center self-start md:self-center">
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-3 min-w-[120px]">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-200 block">Status</span>
                        <span class="text-lg font-extrabold text-white mt-0.5 block">MUTASI KELUAR</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-3 min-w-[120px]">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-200 block">NISN</span>
                        <span class="text-lg font-extrabold text-white mt-0.5 block">{{ $student->nisn }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(session()->has('success_mutasi'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                    <i class="fas fa-check text-sm"></i>
                </div>
                <p class="text-sm font-semibold">{{ session('success_mutasi') }}</p>
            </div>
        @endif

        <!-- Formulir Data Mutasi Siswa -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-school text-amber-600"></i> Form Konfirmasi Sekolah Tujuan Mutasi
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Lengkapi data sekolah baru tempat Anda melanjutkan pendidikan.</p>
            </div>

            <form wire:submit="simpanDataMutasi" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Nama Sekolah Tujuan -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Sekolah / Instansi Tujuan Mutasi <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="tujuan_mutasi" required placeholder="Contoh: SMP Negeri 1 Cilacap / SMP Telkom Purwokerto" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm">
                        @error('tujuan_mutasi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tanggal Mutasi -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Mutasi</label>
                        <input type="date" wire:model="tanggal_mutasi" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm">
                        @error('tanggal_mutasi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- No. HP Aktif -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">No. HP / WhatsApp Aktif</label>
                        <input type="text" wire:model="no_hp_mutasi" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm">
                        @error('no_hp_mutasi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Alasan Mutasi / Kepindahan -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alasan Kepindahan / Mutasi</label>
                        <textarea wire:model="alasan_mutasi" rows="3" placeholder="Contoh: Mengikuti domisili orang tua yang pindah tugas kerja" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm"></textarea>
                        @error('alasan_mutasi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md shadow-amber-600/20 transition-all">
                        <i class="fas fa-save"></i> Simpan Data Mutasi
                    </button>
                </div>
            </form>
        </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- KONDISI 3: SISWA AKTIF REGULER (DASHBOARD LENGKAP)               --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    @else

        <!-- Header / Welcome Hero Card -->
        <div class="relative bg-gradient-to-br from-indigo-700 via-indigo-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-900/20 overflow-hidden border border-indigo-500/20">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full filter blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-violet-500/20 rounded-full filter blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-indigo-200 mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Portal Siswa & Akademik
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Selamat Datang, {{ $student?->name ?? 'Siswa' }}!
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
                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Presensi & Akademik</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">Cek riwayat kehadiran harian dan kalender akademik sekolah.</p>
                    </div>
                    <a href="{{ route('portal-siswa.akademik') }}" class="mt-4 inline-flex items-center justify-between w-full text-xs font-bold text-emerald-600 hover:text-emerald-700 pt-3 border-t border-slate-100">
                        <span>Buka Presensi</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <!-- Shortcut 2: Pengajuan Ijin -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-amber-200 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-105 group-hover:bg-amber-600 group-hover:text-white transition-all shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            @if($pendingIjinCount > 0)
                                <span class="text-xs font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-xl">
                                    {{ $pendingIjinCount }} Menunggu
                                </span>
                            @else
                                <span class="text-xs font-semibold text-slate-500 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-xl">
                                    {{ $totalIjinCount }} Total
                                </span>
                            @endif
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-amber-600 transition-colors">Pengajuan Ijin</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">Ajukan surat ijin sakit atau keperluan pribadi ke wali kelas.</p>
                    </div>
                    <a href="{{ route('portal-siswa.ijin') }}" class="mt-4 inline-flex items-center justify-between w-full text-xs font-bold text-amber-600 hover:text-amber-700 pt-3 border-t border-slate-100">
                        <span>Ajukan Ijin</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <!-- Shortcut 3: Perpustakaan Digital -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-cyan-200 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center group-hover:scale-105 group-hover:bg-cyan-600 group-hover:text-white transition-all shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <span class="text-xs font-extrabold text-cyan-700 bg-cyan-50 border border-cyan-200 px-2.5 py-1 rounded-xl">
                                {{ $activeBooksCount }} Dipinjam
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-cyan-600 transition-colors">Perpustakaan</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">Lihat buku yang sedang dipinjam dan riwayat peminjaman.</p>
                    </div>
                    <a href="{{ route('portal-siswa.perpustakaan') }}" class="mt-4 inline-flex items-center justify-between w-full text-xs font-bold text-cyan-600 hover:text-cyan-700 pt-3 border-t border-slate-100">
                        <span>Buku Saya</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <!-- Shortcut 4: Cetak Kartu Siswa -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-violet-200 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center group-hover:scale-105 group-hover:bg-violet-600 group-hover:text-white transition-all shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-violet-700 bg-violet-50 border border-violet-200 px-2.5 py-1 rounded-xl">
                                PDF Kartu
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-violet-600 transition-colors">Cetak Kartu Siswa</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">Unduh atau cetak kartu tanda pelajar ber-barcode digital.</p>
                    </div>
                    <a href="{{ route('portal-siswa.cetak-kartu') }}" target="_blank" class="mt-4 inline-flex items-center justify-between w-full text-xs font-bold text-violet-600 hover:text-violet-700 pt-3 border-t border-slate-100">
                        <span>Cetak Mandiri</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
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
                            <span class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-xl">
                                Biodata
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Profil Saya</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">Periksa biodata siswa, data orang tua, dan ganti password.</p>
                    </div>
                    <a href="{{ route('portal-siswa.profil') }}" class="mt-4 inline-flex items-center justify-between w-full text-xs font-bold text-indigo-600 hover:text-indigo-700 pt-3 border-t border-slate-100">
                        <span>Lihat Profil</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

            </div>
        </div>

        <!-- Pengumuman Sekolah Section -->
        @if($activeAnnouncements && $activeAnnouncements->count() > 0)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        Pengumuman Sekolah Terbaru
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($activeAnnouncements as $announcement)
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fas fa-bullhorn text-[10px]"></i> Pengumuman
                                    </span>
                                    <span class="text-xs text-slate-400 font-medium">
                                        {{ $announcement->created_at->format('d M Y') }}
                                    </span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 mb-1.5">{{ $announcement->judul }}</h3>
                                <div class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                                    {!! strip_tags($announcement->isi) !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @endif

</div>
