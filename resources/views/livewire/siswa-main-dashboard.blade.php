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

    <div class="space-y-6" x-data="{ quickModal: null }" @keydown.escape.window="quickModal = null">
        <!-- Header / Welcome Hero Card -->
        <div class="relative bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-7 text-white shadow-xl shadow-brand-primary/20 overflow-hidden border border-brand-primary/30">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand-secondary/20 rounded-full filter blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2.5">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-semibold text-white">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Portal Siswa &amp; Akademik
                        </span>
                        <span class="px-3 py-1 rounded-full bg-white/15 border border-white/20 text-xs font-bold text-white">
                            Kelas {{ $kelasName }}
                        </span>
                        <span class="px-3 py-1 rounded-full bg-white/15 border border-white/20 text-xs font-bold text-white">
                            NISN: {{ $student?->nisn ?? '-' }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        Selamat Datang, {{ $student?->name ?? 'Siswa' }}!
                    </h1>
                    <p class="text-white/85 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                        Pantau kehadiran harian, ajukan ijin, cek pinjaman perpustakaan, dan akses bimbingan Guru Wali dari satu tempat.
                    </p>
                </div>

                <!-- Quick Hero Actions -->
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('portal-siswa.ijin.form') }}"
                       class="px-4 py-2.5 bg-white text-brand-primary rounded-2xl text-xs font-extrabold hover:bg-white/90 transition-colors shadow-md flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Ajukan Ijin
                    </a>
                    <a href="{{ route('portal-siswa.cetak-kartu') }}" target="_blank"
                       class="px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white border border-white/25 rounded-2xl text-xs font-bold transition-colors backdrop-blur-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                        Cetak Kartu
                    </a>
                </div>
            </div>
        </div>

        <!-- Pintasan Menu Utama Siswa (4 Kotak Satu Baris) -->
        <div class="space-y-3">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                    Pintasan Menu Utama Siswa
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Klik kartu untuk membuka layanan atau pilihan cepat.</p>
            </div>

            @php
                $schoolSettings = \App\Models\PengaturanSekolah::current();
                $enableSholatDhuhur = $schoolSettings->enable_sholat_dhuhur ?? true;
                $enablePresensiKbm = $schoolSettings->enable_presensi_kbm ?? true;
                $spikapSetting = \App\Models\SpikapNotifSetting::instance();
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">

                <!-- 1. Presensi & Akademik -->
                <a href="{{ route('portal-siswa.akademik') }}"
                   class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-emerald-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <span class="text-[11px] font-extrabold {{ $attendancePercentage >= 90 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : ($attendancePercentage >= 75 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200') }} px-2.5 py-1 rounded-full border">
                                {{ $attendancePercentage }}% Hadir
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-emerald-600 transition-colors">Presensi Sekolah</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Riwayat kehadiran harian</li>
                            <li>&bull; Kalender akademik sekolah</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-600">
                        <span>Buka Presensi</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>

                <!-- 2. Pengajuan Ijin (POP-UP) -->
                <button type="button" @click="quickModal = 'ijin'"
                   class="text-left w-full bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-amber-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group cursor-pointer">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            </div>
                            @if($pendingIjinCount > 0)
                                <span class="text-[11px] font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                                    {{ $pendingIjinCount }} Menunggu
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">
                                    {{ $totalIjinCount }} Total
                                </span>
                            @endif
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-amber-600 transition-colors">Pengajuan Ijin</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Buat permohonan ijin / sakit</li>
                            <li>&bull; Pantau status persetujuan</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-600 w-full">
                        <span>Pilih Ajukan / Riwayat</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </button>

                <!-- 3. Sholat Dhuhur -->
                @if($enableSholatDhuhur)
                <a href="{{ route('portal-siswa.sholat-dhuhur') }}"
                   class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-teal-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            </div>
                            <span class="text-[11px] font-bold text-teal-700 bg-teal-50 border border-teal-100 px-2.5 py-1 rounded-full">
                                Ibadah
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-teal-600 transition-colors">Sholat Dhuhur</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Rekap presensi berjamaah</li>
                            <li>&bull; Catatan ibadah harian</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-teal-600">
                        <span>Buka Sholat Dhuhur</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>
                @endif

                <!-- 4. Jurnal KBM -->
                @if($enablePresensiKbm)
                <a href="{{ route('portal-siswa.jurnal-kbm') }}"
                   class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-indigo-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                            <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-full">
                                KBM Mapel
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">Jurnal KBM</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Kehadiran per mata pelajaran</li>
                            <li>&bull; Materi pembelajaran guru</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600">
                        <span>Buka Jurnal KBM</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>
                @endif

                <!-- 5. Ekstrakurikuler (POP-UP) -->
                @if($enableEkskul)
                <button type="button" @click="quickModal = 'ekskul'"
                   class="text-left w-full bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-indigo-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group cursor-pointer">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            </div>
                            <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-full">
                                {{ count($ekskulItems) }} Ekskul
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">Ekstrakurikuler</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Materi latihan &amp; pembina</li>
                            <li>&bull; Rekap kehadiran kegiatan</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600 w-full">
                        <span>Pilih Ekstrakurikuler</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </button>
                @endif

                <!-- 6. SI-WALI (Guru Wali) -->
                <a href="{{ route('portal-siswa.guru-wali') }}"
                   class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-brand-primary/40 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-primary/10 p-1.5 flex items-center justify-center border border-brand-primary/20 shrink-0">
                                <img src="{{ asset('images/logo-si-wali.png') }}" alt="Logo SI-WALI" class="w-full h-full object-contain">
                            </div>
                            @if($dijadwalkanKonsultasiCount > 0)
                                <span class="text-[11px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full">
                                    {{ $dijadwalkanKonsultasiCount }} Dijadwalkan
                                </span>
                            @elseif($pendingKonsultasiCount > 0)
                                <span class="text-[11px] font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                                    {{ $pendingKonsultasiCount }} Menunggu
                                </span>
                            @elseif($hasGuruWali)
                                <span class="text-[11px] font-bold text-brand-primary bg-brand-primary/10 border border-brand-primary/20 px-2.5 py-1 rounded-full">
                                    Aktif
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-slate-400 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-full">
                                    Belum Ada
                                </span>
                            @endif
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand-primary transition-colors">SI-WALI</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li class="truncate">&bull; {{ $guruWaliName ? 'Wali: ' . $guruWaliName : 'Pendampingan Guru Wali' }}</li>
                            <li>&bull; Ruang aman &amp; jadwal cerita</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-brand-primary">
                        <span>Buka Konsultasi</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>

                <!-- 7. SPIKAP (Aduan Siswa) (POP-UP) -->
                <button type="button" @click="quickModal = 'spikap'"
                   class="text-left w-full bg-white rounded-2xl p-4 border {{ $spikapAktifCount > 0 ? 'border-rose-300 ring-2 ring-rose-500/15' : 'border-slate-200/90' }} shadow-xs hover:shadow-md hover:border-rose-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group cursor-pointer">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            @if($spikapAktifCount > 0)
                                <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                    {{ $spikapAktifCount }} Diproses
                                </span>
                            @else
                                <span class="text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-100 px-2.5 py-1 rounded-full">
                                    {{ $spikapLaporanCount }} Laporan
                                </span>
                            @endif
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-rose-600 transition-colors truncate">{{ $spikapSetting->getNamaAplikasi() }}</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Buat laporan anti-perundungan</li>
                            <li>&bull; Pantau tindak lanjut kasus</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600 w-full">
                        <span>Pilih Lapor / Riwayat</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </button>

                <!-- 8. Perpustakaan -->
                <a href="{{ route('portal-siswa.perpustakaan') }}"
                   class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-cyan-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 border border-cyan-100 flex items-center justify-center group-hover:bg-cyan-600 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            </div>
                            <span class="text-[11px] font-extrabold text-cyan-700 bg-cyan-50 border border-cyan-100 px-2.5 py-1 rounded-full">
                                {{ $activeBooksCount }} Dipinjam
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-cyan-600 transition-colors">Perpustakaan</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Buku yang sedang dipinjam</li>
                            <li>&bull; Riwayat peminjaman siswa</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-cyan-600">
                        <span>Buku Saya</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>

                <!-- 9. Cetak Kartu Siswa -->
                <a href="{{ route('portal-siswa.cetak-kartu') }}" target="_blank"
                   class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-violet-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 border border-violet-100 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                            </div>
                            <span class="text-[11px] font-bold text-violet-700 bg-violet-50 border border-violet-100 px-2.5 py-1 rounded-full">
                                PDF Kartu
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-violet-600 transition-colors">Cetak Kartu Siswa</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Kartu pelajar ber-barcode</li>
                            <li>&bull; Unduh / cetak format PDF</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-violet-600">
                        <span>Cetak Mandiri</span>
                        <span class="group-hover:translate-x-1 transition-transform">&nearr;</span>
                    </div>
                </a>

                <!-- 10. Profil Saya -->
                <a href="{{ route('portal-siswa.profil') }}"
                   class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-brand-primary/40 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary border border-brand-primary/20 flex items-center justify-center group-hover:bg-brand-primary group-hover:text-white transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <span class="text-[11px] font-bold text-brand-primary bg-brand-primary/10 border border-brand-primary/20 px-2.5 py-1 rounded-full">
                                Biodata
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand-primary transition-colors">Profil Saya</h3>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                            <li>&bull; Biodata siswa &amp; orang tua</li>
                            <li>&bull; Ubah password akun</li>
                        </ul>
                    </div>
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-brand-primary">
                        <span>Lihat Profil</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>

            </div>
        </div>

        <!-- Pengumuman Sekolah Section -->
        @if($activeAnnouncements && $activeAnnouncements->count() > 0)
            <div class="space-y-3">
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Pengumuman Sekolah Terbaru
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    @foreach($activeAnnouncements as $announcement)
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fas fa-bullhorn text-[10px]"></i> Pengumuman
                                    </span>
                                    <span class="text-xs text-slate-400 font-medium">
                                        {{ $announcement->created_at->format('d M Y') }}
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $announcement->judul }}</h3>
                                <div class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                                    {!! strip_tags($announcement->isi) !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- POP-UP PILIHAN CEPAT SISWA --}}
        <div x-show="quickModal !== null" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="quickModal = null">

            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2">

                {{-- 1. Pop-up Pengajuan Ijin --}}
                <template x-if="quickModal === 'ijin'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-amber-500 to-orange-500 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">Pengajuan Ijin</h3>
                                    <p class="text-xs text-white/85">Pilih buat ijin baru atau lihat status</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5">
                            <a href="{{ route('portal-siswa.ijin.form') }}"
                               class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100/80 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-extrabold text-sm text-slate-900">Ajukan Ijin / Sakit Baru</div>
                                    <div class="text-xs text-slate-500">Langsung buka formulir pengajuan ke wali kelas</div>
                                </div>
                                <span class="text-amber-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>

                            <a href="{{ route('portal-siswa.ijin') }}"
                               class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-extrabold text-sm text-slate-900">Riwayat &amp; Status Pengajuan</div>
                                    <div class="text-xs text-slate-500">Menunggu: {{ $pendingIjinCount }} &bull; Diterima: {{ $approvedIjinCount }} &bull; Ditolak: {{ $rejectedIjinCount }}</div>
                                </div>
                                <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </template>

                {{-- 2. Pop-up SPIKAP --}}
                <template x-if="quickModal === 'spikap'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-rose-600 to-red-600 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">{{ $spikapSetting->getNamaAplikasi() }}</h3>
                                    <p class="text-xs text-white/85">{{ $spikapSetting->getSubJudul() }}</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5">
                            <a href="{{ route('portal-siswa.spikap.form') }}"
                               class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-rose-200 bg-rose-50/70 hover:bg-rose-100/80 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-extrabold text-sm text-slate-900">Buat Laporan Baru</div>
                                    <div class="text-xs text-slate-500">Laporkan kejadian atau keluhan secara aman</div>
                                </div>
                                <span class="text-rose-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>

                            <a href="{{ route('portal-siswa.spikap') }}"
                               class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-extrabold text-sm text-slate-900">Riwayat &amp; Status Laporan</div>
                                    <div class="text-xs text-slate-500">Total {{ $spikapLaporanCount }} laporan &bull; {{ $spikapAktifCount }} diproses</div>
                                </div>
                                <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </template>

                {{-- 3. Pop-up Ekstrakurikuler --}}
                @if($enableEkskul)
                <template x-if="quickModal === 'ekskul'">
                    <div>
                        <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base leading-tight">Ekstrakurikuler Saya</h3>
                                    <p class="text-xs text-white/85">Pilih ekstrakurikuler untuk melihat materi &amp; kehadiran</p>
                                </div>
                            </div>
                            <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="p-5 space-y-2.5 max-h-[70vh] overflow-y-auto">
                            @forelse($ekskulItems as $eks)
                                <a href="{{ route('portal-siswa.ekstrakurikuler', ['slug' => $eks['slug']]) }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100/80 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-slate-900">{{ $eks['nama_ekskul'] }}</div>
                                        <div class="text-xs text-slate-500">Lihat materi pertemuan &amp; rekap hadir</div>
                                    </div>
                                    <span class="text-indigo-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @empty
                                <a href="{{ route('portal-siswa.ekstrakurikuler') }}"
                                   class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors group">
                                    <div>
                                        <div class="font-extrabold text-sm text-slate-900">Lihat Halaman Ekstrakurikuler</div>
                                        <div class="text-xs text-slate-500">Belum ada ekstrakurikuler aktif yang diikuti</div>
                                    </div>
                                    <span class="text-slate-400 font-bold">&rarr;</span>
                                </a>
                            @endforelse
                        </div>
                    </div>
                </template>
                @endif
            </div>
        </div>
    </div>

    @endif

</div>
