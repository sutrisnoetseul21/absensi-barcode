<div class="p-4 lg:p-6 space-y-6 max-w-7xl mx-auto pb-16" x-data="{ quickModal: null }" @keydown.escape.window="quickModal = null">
    <!-- Header / Welcome Hero Card -->
    <div class="relative bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-7 text-white shadow-xl shadow-brand-primary/20 overflow-hidden border border-brand-primary/30">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">Portal Guru</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold mt-2.5 tracking-tight leading-snug">
                    Selamat Datang, {{ $teacher?->name ?? 'Bapak/Ibu Guru' }}!
                </h1>
                <p class="text-white/85 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                    Kelola jurnal mengajar, absensi kelas, layanan BK, dan pendampingan siswa dari satu tempat.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}"
                   target="_blank"
                   class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Presensi Digital
                </a>
                @if(\App\Models\PengaturanSekolah::current()->enable_sholat_dhuhur ?? true)
                <a href="{{ route('kiosk.scan-sholat-dhuhur') }}"
                   target="_blank"
                   class="px-4 py-2.5 bg-teal-500 hover:bg-teal-600 text-white rounded-2xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    Sholat Dhuhur
                </a>
                @endif
                @if($isGuruWaliAktif)
                <button type="button" @click="quickModal = 'siwali'"
                   class="px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-2xl text-xs font-bold transition-all border border-white/30 backdrop-blur-sm flex items-center gap-2 relative cursor-pointer">
                    <img src="{{ asset('images/logo-si-wali.png') }}" class="w-4 h-4 object-contain" alt="Logo SI-WALI">
                    <span>SI-WALI</span>
                    @if($pendingKonsultasiCount > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow-xs animate-pulse">
                            {{ $pendingKonsultasiCount }}
                        </span>
                    @endif
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Alert Banner: Permintaan Cerita / Konsultasi SI-WALI Baru -->
    @if($isGuruWaliAktif && $pendingKonsultasiCount > 0)
    <div class="relative bg-gradient-to-r from-rose-50 via-white to-amber-50/50 rounded-2xl p-4 sm:p-5 border-2 border-rose-300 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-500/20">
                <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-200 uppercase tracking-wider">
                        Ruang Aman Cerita SI-WALI
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-600 text-white shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                        {{ $pendingKonsultasiCount }} Permintaan Menunggu
                    </span>
                </div>
                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5">
                    Ada Peserta Didik Mengajukan Cerita / Konsultasi Baru!
                </h3>
                @if($latestPendingKonsultasi)
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Siswa <strong>{{ $latestPendingKonsultasi->siswa?->name ?? 'Peserta Didik' }}</strong> mengajukan topik:
                    <span class="text-rose-700 font-semibold italic">"{{ $latestPendingKonsultasi->topik_konsultasi }}"</span>.
                </p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
            <a href="{{ route('portal-guru.guru-wali.konsultasi') }}"
               class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-md shadow-rose-600/25 flex items-center gap-1.5">
                <span>Buka Ruang Aman</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </a>
        </div>
    </div>
    @endif

    <!-- Pintasan Menu Utama Guru (4 Kotak Satu Baris) -->
    <div class="space-y-3">
        <div>
            <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                Pintasan Menu Utama Guru
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Klik menu untuk membuka halaman atau pop-up pilihan cepat.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">

            <!-- 1. Jurnal Mengajar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-indigo-300 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100">
                            @if($jurnalHariIniCount > 0)
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ $jurnalHariIniCount }} Hari Ini
                            @else
                                {{ $totalJurnalCount }} Jurnal
                            @endif
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">Jurnal Mengajar</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Materi &amp; absensi jam KBM</li>
                        <li>&bull; Rekap &amp; unduh PDF/Excel</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center gap-1.5">
                    <a href="{{ route('portal-guru.jurnal-mengajar.create') }}"
                       class="flex-1 py-1.5 px-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold transition-all text-center">
                        + Isi Jurnal
                    </a>
                    <a href="{{ route('portal-guru.jurnal-mengajar') }}"
                       class="py-1.5 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all">
                        Riwayat
                    </a>
                </div>
            </div>

            <!-- 2. Presensi & Akademik -->
            @if($isWaliKelasAktif)
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-emerald-300 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">
                            {{ $kelasAmpuCount }} Kelas
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-emerald-600 transition-colors">Presensi &amp; Akademik</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Absensi harian &amp; matriks</li>
                        <li>&bull; Cetak laporan kelas</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center gap-1.5">
                    <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}"
                       target="_blank"
                       class="flex-1 py-1.5 px-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-all text-center">
                        Scan Kiosk
                    </a>
                    <a href="{{ route('portal-guru.akademik') }}"
                       class="py-1.5 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all">
                        Rekap
                    </a>
                </div>
            </div>
            @endif

            <!-- 3. Presensi Sholat Dhuhur -->
            @if(\App\Models\PengaturanSekolah::current()->enable_sholat_dhuhur ?? true)
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-teal-300 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-[11px] font-extrabold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full border border-teal-100">
                            Ibadah
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-teal-600 transition-colors">Sholat Dhuhur</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Scan kehadiran berjamaah</li>
                        <li>&bull; Rekap matriks kelas</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center gap-1.5">
                    <a href="{{ route('kiosk.scan-sholat-dhuhur') }}"
                       target="_blank"
                       class="flex-1 py-1.5 px-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-bold transition-all text-center">
                        Scan Dhuhur
                    </a>
                    @if($isWaliKelasAktif)
                    <a href="{{ route('portal-guru.sholat-dhuhur') }}"
                       class="py-1.5 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all">
                        Matriks
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 4. Ekstrakurikuler Binaan (POP-UP) -->
            @if($canAccessEkskulCard)
            <button type="button" @click="quickModal = 'ekskul'"
               class="text-left w-full bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-indigo-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group cursor-pointer">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                        </div>
                        <span class="text-[11px] font-extrabold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100">
                            {{ count($ekskulItems) }} Ekskul
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">Ekstrakurikuler</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Kiosk scanner per ekskul</li>
                        <li>&bull; Jurnal latihan &amp; anggota</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600 w-full">
                    <span>Pilih Ekskul / Scanner</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </button>
            @endif

            <!-- 5. SI-WALI (Guru Wali) (POP-UP) -->
            @if($isGuruWaliAktif)
            <button type="button" @click="quickModal = 'siwali'"
               class="text-left w-full bg-white rounded-2xl p-4 border {{ $pendingKonsultasiCount > 0 ? 'border-rose-300 ring-2 ring-rose-500/20 bg-rose-50/10' : 'border-slate-200/90' }} shadow-xs hover:shadow-md hover:border-brand-primary/40 hover:-translate-y-0.5 transition-all flex flex-col justify-between group cursor-pointer">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl {{ $pendingKonsultasiCount > 0 ? 'bg-rose-500/10 border-rose-300' : 'bg-brand-primary/10 border-brand-primary/20' }} p-1.5 flex items-center justify-center border shrink-0">
                            <img src="{{ asset('images/logo-si-wali.png') }}" alt="Logo SI-WALI" class="w-full h-full object-contain">
                        </div>
                        @if($pendingKonsultasiCount > 0)
                            <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-rose-700 bg-rose-100 px-2.5 py-1 rounded-full border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                {{ $pendingKonsultasiCount }} Cerita
                            </span>
                        @else
                            <span class="text-[11px] font-extrabold text-brand-primary bg-brand-primary/10 px-2.5 py-1 rounded-full border border-brand-primary/20">
                                Permen 11/2025
                            </span>
                        @endif
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand-primary transition-colors">SI-WALI (Guru Wali)</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Kelompok &amp; jurnal bimbingan</li>
                        <li>&bull; Ruang aman &amp; cetak laporan</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold {{ $pendingKonsultasiCount > 0 ? 'text-rose-600' : 'text-brand-primary' }} w-full">
                    <span>Pilih Menu SI-WALI</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </button>
            @endif

            <!-- 6. SPIKAP (Aduan Siswa) (POP-UP) -->
            @if($hasSpikapAccess)
            @php $spikapSetting = \App\Models\SpikapNotifSetting::instance(); @endphp
            <button type="button" @click="quickModal = 'spikap'"
               class="text-left w-full bg-white rounded-2xl p-4 border {{ $spikapDaruratCount > 0 ? 'border-rose-300 ring-2 ring-rose-500/20 bg-rose-50/10' : 'border-slate-200/90' }} shadow-xs hover:shadow-md hover:border-rose-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group cursor-pointer">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl {{ $spikapDaruratCount > 0 ? 'bg-rose-500 text-white animate-pulse' : 'bg-rose-50 text-rose-600 border border-rose-100' }} flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        @if($spikapDaruratCount > 0)
                            <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-rose-700 bg-rose-100 px-2.5 py-1 rounded-full border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                {{ $spikapDaruratCount }} Darurat
                            </span>
                        @elseif($spikapAktifCount > 0)
                            <span class="text-[11px] font-extrabold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                                {{ $spikapAktifCount }} Aktif
                            </span>
                        @else
                            <span class="text-[11px] font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full border border-slate-200">
                                {{ $spikapCount }} Aduan
                            </span>
                        @endif
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-rose-600 transition-colors truncate">{{ $spikapSetting->getNamaAplikasi() }} (Aduan)</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Kotak masuk aduan siswa</li>
                        <li>&bull; Analitik &amp; statistik kasus</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600 w-full">
                    <span>Pilih Menu {{ $spikapSetting->getNamaAplikasi() }}</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </button>
            @endif

            <!-- 7. Bimbingan & Konseling (BK) (POP-UP) -->
            @if($canAccessBk)
            <button type="button" @click="quickModal = 'bk'"
               class="text-left w-full bg-white rounded-2xl p-4 border {{ $pendingBkRujukanCount > 0 ? 'border-indigo-300 ring-2 ring-indigo-500/20 bg-indigo-50/10' : 'border-slate-200/90' }} shadow-xs hover:shadow-md hover:border-indigo-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group cursor-pointer">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl {{ $pendingBkRujukanCount > 0 ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-600 border border-indigo-100' }} flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        @if($pendingBkRujukanCount > 0)
                            <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-indigo-700 bg-indigo-100 px-2.5 py-1 rounded-full border border-indigo-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-ping"></span>
                                {{ $pendingBkRujukanCount }} Rujukan
                            </span>
                        @else
                            <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100">
                                Layanan BK
                            </span>
                        @endif
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">Bimbingan Konseling</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Rujukan kasus &amp; siswa binaan</li>
                        <li>&bull; Catat &amp; daftar konseling</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600 w-full">
                    <span>Pilih Menu BK</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </button>
            @endif

            <!-- 8. Persetujuan Ijin -->
            @if($canAccessIjin)
            <a href="{{ route('portal-guru.ijin') }}"
               class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-rose-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        </div>
                        @if($pendingLeaveRequestsCount > 0)
                            <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                {{ $pendingLeaveRequestsCount }} Pending
                            </span>
                        @else
                            <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full border border-slate-200">
                                Bersih
                            </span>
                        @endif
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-rose-600 transition-colors">Persetujuan Ijin</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Diterima: {{ $approvedLeaveRequestsCount }} &bull; Ditolak: {{ $rejectedLeaveRequestsCount }}</li>
                        <li>&bull; Proses surat ijin/sakit siswa</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600">
                    <span>Buka Persetujuan</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endif

            <!-- 9. Data Siswa -->
            @if($isWaliKelasAktif)
            <a href="{{ route('portal-guru.data-siswa') }}"
               class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-violet-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 border border-violet-100 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                        </div>
                        <span class="text-[11px] font-bold text-violet-700 bg-violet-50 px-2.5 py-1 rounded-full border border-violet-100">
                            {{ $totalStudentsCount }} Siswa
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-violet-600 transition-colors">Data Siswa</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Kelola data siswa binaan</li>
                        <li>&bull; Update Excel &amp; cetak kartu</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-violet-600">
                    <span>Buka Data Siswa</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endif

            <!-- 10. Perpustakaan -->
            <a href="{{ route('portal-guru.perpustakaan') }}"
               class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-amber-300 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <span class="text-[11px] font-extrabold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100">
                            {{ $activeBooksCount }} Dipinjam
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-amber-600 transition-colors">Perpustakaan</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Katalog buku perpustakaan</li>
                        <li>&bull; Riwayat pinjaman pribadi</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-600">
                    <span>Buka Perpustakaan</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- 11. Profil Saya -->
            <a href="{{ route('portal-guru.profil') }}"
               class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md hover:border-brand-primary/40 hover:-translate-y-0.5 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary border border-brand-primary/20 flex items-center justify-center group-hover:bg-brand-primary group-hover:text-white transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-[11px] font-bold text-brand-primary bg-brand-primary/10 px-2.5 py-1 rounded-full border border-brand-primary/20">
                            Akun
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand-primary transition-colors">Profil Saya</h3>
                    <ul class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                        <li>&bull; Biodata, NIP &amp; foto profil</li>
                        <li>&bull; Ganti email &amp; kata sandi</li>
                    </ul>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-brand-primary">
                    <span>Lihat Profil</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

        </div>
    </div>

    <!-- Multi-Portal Access Cards (Jika memiliki akses ke Portal lain) -->
    @if($hasPresensiAccess || $hasPerpusAccess || $hasWebAccess || $isSuperAdmin)
        <div class="space-y-3">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    Akses Portal Terkait
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Portal tambahan sesuai hak akses akun Anda.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                @if($hasPresensiAccess)
                    <div class="bg-gradient-to-br from-white to-amber-50/40 rounded-2xl p-4 border border-amber-200/80 shadow-xs hover:shadow-md transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-200">Presensi</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Portal Presensi</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Rekap sekolah &amp; input manual.</p>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-amber-100 flex items-center gap-1.5">
                            <a href="{{ route('portal-presensi.dashboard') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold transition-colors">Dashboard</a>
                            <a href="{{ \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? route('kiosk.scan-nis') : route('kiosk.scan') }}" target="_blank" class="py-1.5 px-2.5 rounded-xl bg-white hover:bg-amber-50 border border-amber-200 text-amber-700 text-[11px] font-bold transition-colors">Kiosk</a>
                        </div>
                    </div>
                @endif

                @if($hasPerpusAccess)
                    <div class="bg-gradient-to-br from-white to-cyan-50/40 rounded-2xl p-4 border border-cyan-200/80 shadow-xs hover:shadow-md transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-cyan-600 text-white flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-cyan-100 text-cyan-800 border border-cyan-200">Perpus</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Portal Perpustakaan</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Katalog buku, sirkulasi &amp; kunjungan.</p>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-cyan-100 flex items-center gap-1.5">
                            <a href="{{ route('portal-perpustakaan.dashboard') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-[11px] font-bold transition-colors">Dashboard</a>
                            <a href="{{ route('portal-perpustakaan.sirkulasi-kiosk') }}" target="_blank" class="py-1.5 px-2.5 rounded-xl bg-white hover:bg-cyan-50 border border-cyan-200 text-cyan-700 text-[11px] font-bold transition-colors">Kiosk</a>
                        </div>
                    </div>
                @endif

                @if($hasWebAccess)
                    <div class="bg-gradient-to-br from-white to-violet-50/40 rounded-2xl p-4 border border-violet-200/80 shadow-xs hover:shadow-md transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-violet-600 text-white flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-violet-100 text-violet-800 border border-violet-200">Web</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Portal Publikasi Web</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Artikel, prestasi, galeri &amp; alumni.</p>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-violet-100 flex items-center gap-1.5">
                            <a href="{{ route('portal-web.dashboard') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-[11px] font-bold transition-colors">Portal Web</a>
                            <a href="{{ url('/') }}" target="_blank" class="py-1.5 px-2.5 rounded-xl bg-white hover:bg-violet-50 border border-violet-200 text-violet-700 text-[11px] font-bold transition-colors">Web</a>
                        </div>
                    </div>
                @endif

                @if($isSuperAdmin)
                    <div class="bg-gradient-to-br from-white to-purple-50/40 rounded-2xl p-4 border border-purple-200/80 shadow-xs hover:shadow-md transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-purple-100 text-purple-800 border border-purple-200">Admin</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Panel Admin Utama</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Master data &amp; pengaturan sistem.</p>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-purple-100">
                            <a href="{{ url('/admin') }}" class="w-full block text-center py-1.5 px-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-[11px] font-bold transition-colors">Buka Admin Panel</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Announcements Section -->
    @if($activeAnnouncements->count() > 0)
        <div class="bg-amber-50/80 border border-amber-200/80 rounded-3xl p-5 sm:p-6 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-500"></div>
            <div class="flex items-start gap-4">
                <div class="bg-amber-100 p-2.5 rounded-2xl text-amber-700 flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-amber-950 mb-2.5">Pengumuman Sekolah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($activeAnnouncements as $announcement)
                            <div class="bg-white/80 backdrop-blur-xs p-3.5 rounded-2xl border border-amber-100 shadow-xs">
                                <h4 class="font-bold text-slate-800 text-sm">{{ $announcement->judul }}</h4>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $announcement->isi }}</p>
                                <span class="text-[10px] text-amber-700/80 font-semibold mt-2 block">{{ $announcement->created_at->translatedFormat('d F Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- POP-UP PILIHAN CEPAT --}}
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

            {{-- 1. Pop-up Ekstrakurikuler --}}
            @if($canAccessEkskulCard)
            <template x-if="quickModal === 'ekskul'">
                <div>
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">Ekstrakurikuler Binaan</h3>
                                <p class="text-xs text-white/80">Pilih buka jurnal atau scanner barcode</p>
                            </div>
                        </div>
                        <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-5 space-y-2.5 max-h-[70vh] overflow-y-auto">
                        @forelse($ekskulItems as $eks)
                            <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-2.5">
                                <div class="font-extrabold text-sm text-slate-900">{{ $eks['nama_ekskul'] }}</div>
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('portal-guru.ekstrakurikuler', ['slug' => $eks['slug']]) }}"
                                       class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors">
                                        Jurnal &amp; Anggota
                                    </a>
                                    <a href="{{ route('kiosk.scan-ekstrakurikuler', ['slug' => $eks['slug']]) }}" target="_blank"
                                       class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">
                                        Scanner Kiosk &nearr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <a href="{{ route('portal-guru.ekstrakurikuler') }}"
                               class="flex items-center justify-between p-3.5 rounded-2xl border border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100/80 transition-colors">
                                <div>
                                    <div class="font-extrabold text-sm text-slate-900">Kelola Ekstrakurikuler</div>
                                    <div class="text-xs text-slate-500">Buka halaman manajemen ekstrakurikuler</div>
                                </div>
                                <span class="text-indigo-600 font-bold">&rarr;</span>
                            </a>
                        @endforelse
                    </div>
                </div>
            </template>
            @endif

            {{-- 2. Pop-up SI-WALI (Guru Wali) --}}
            @if($isGuruWaliAktif)
            <template x-if="quickModal === 'siwali'">
                <div>
                    <div class="px-6 py-5 bg-gradient-to-r from-brand-primary to-brand-secondary text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white p-1.5 flex items-center justify-center">
                                <img src="{{ asset('images/logo-si-wali.png') }}" alt="SI-WALI" class="w-full h-full object-contain">
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">SI-WALI (Guru Wali)</h3>
                                <p class="text-xs text-white/80">Pilih layanan pendampingan siswa</p>
                            </div>
                        </div>
                        <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-5 space-y-2 max-h-[75vh] overflow-y-auto">
                        <a href="{{ route('portal-guru.guru-wali.konsultasi') }}"
                           class="flex items-center gap-3.5 p-3 rounded-2xl border {{ $pendingKonsultasiCount > 0 ? 'border-rose-200 bg-rose-50/70 hover:bg-rose-100/80' : 'border-slate-200 bg-white hover:bg-slate-50' }} transition-colors group">
                            <div class="w-9 h-9 rounded-xl {{ $pendingKonsultasiCount > 0 ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                    <span>Ruang Aman Cerita</span>
                                    @if($pendingKonsultasiCount > 0)
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white">{{ $pendingKonsultasiCount }} Baru</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500">Tanggapi pengajuan konsultasi siswa</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('portal-guru.guru-wali.jurnal.create') }}"
                               class="flex items-center justify-center gap-1.5 p-2.5 rounded-xl bg-brand-primary text-white text-xs font-bold hover:opacity-95 transition-opacity">
                                + Jurnal Individu
                            </a>
                            <a href="{{ route('portal-guru.guru-wali.jurnal.kelompok') }}"
                               class="flex items-center justify-center gap-1.5 p-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-colors">
                                + Jurnal Kelompok
                            </a>
                        </div>

                        <a href="{{ route('portal-guru.guru-wali.kelompok') }}"
                           class="flex items-center justify-between p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                            <div>
                                <div class="font-bold text-sm text-slate-900">Kelompok Binaan</div>
                                <div class="text-xs text-slate-500">Daftar murid dampingan Anda</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-guru.guru-wali.jurnal') }}"
                           class="flex items-center justify-between p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                            <div>
                                <div class="font-bold text-sm text-slate-900">Riwayat Jurnal Pendampingan</div>
                                <div class="text-xs text-slate-500">Lihat &amp; edit catatan bimbingan</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('portal-guru.guru-wali.pemantauan') }}"
                               class="p-3 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors text-center">
                                <div class="font-bold text-xs text-slate-900">Pemantauan Bulanan</div>
                            </a>
                            <a href="{{ route('portal-guru.guru-wali.cetak') }}"
                               class="p-3 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors text-center">
                                <div class="font-bold text-xs text-slate-900">Cetak Laporan</div>
                            </a>
                        </div>

                        @if($canAccessAnalitik)
                            <a href="{{ route('portal-guru.guru-wali.analitik') }}"
                               class="flex items-center justify-between p-3 rounded-2xl border border-purple-200 bg-purple-50/70 hover:bg-purple-100/80 transition-colors group">
                                <div>
                                    <div class="font-extrabold text-sm text-purple-900">Command Center Analitik</div>
                                    <div class="text-xs text-purple-700">Pantau ringkasan bimbingan seluruh sekolah</div>
                                </div>
                                <span class="text-purple-700 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>
                        @endif
                    </div>
                </div>
            </template>
            @endif

            {{-- 3. Pop-up SPIKAP --}}
            @if($hasSpikapAccess)
            <template x-if="quickModal === 'spikap'">
                <div>
                    <div class="px-6 py-5 bg-gradient-to-r from-rose-600 to-red-600 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">{{ $spikapSetting->getNamaAplikasi() }}</h3>
                                <p class="text-xs text-white/80">{{ $spikapSetting->getSubJudul() }}</p>
                            </div>
                        </div>
                        <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-5 space-y-2.5">
                        <a href="{{ route('portal-guru.spikap') }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-rose-200 bg-rose-50/70 hover:bg-rose-100/80 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Kotak Masuk Aduan</div>
                                <div class="text-xs text-slate-500">Lihat &amp; tangani laporan masuk ({{ $spikapAktifCount }} aktif)</div>
                            </div>
                            <span class="text-rose-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-guru.spikap.analitik') }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Analitik &amp; Statistik</div>
                                <div class="text-xs text-slate-500">Grafik tren, kategori &amp; rekap penanganan</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>
            </template>
            @endif

            {{-- 4. Pop-up Bimbingan & Konseling (BK) --}}
            @if($canAccessBk)
            <template x-if="quickModal === 'bk'">
                <div>
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">Bimbingan &amp; Konseling (BK)</h3>
                                <p class="text-xs text-white/80">Pilih menu layanan Guru BK</p>
                            </div>
                        </div>
                        <button type="button" @click="quickModal = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-5 space-y-2.5">
                        <a href="{{ route('portal-guru.bk.rujukan') }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border {{ $pendingBkRujukanCount > 0 ? 'border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100/80' : 'border-slate-200 bg-white hover:bg-slate-50' }} transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                    <span>Rujukan Kasus Guru Wali</span>
                                    @if($pendingBkRujukanCount > 0)
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-600 text-white">{{ $pendingBkRujukanCount }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500">Tindak lanjuti rujukan siswa dari guru wali</div>
                            </div>
                            <span class="text-indigo-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-guru.bk.konseling.create') }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/80 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Catat Konseling Baru</div>
                                <div class="text-xs text-slate-500">Buat catatan sesi konseling siswa baru</div>
                            </div>
                            <span class="text-emerald-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-guru.bk.konseling') }}"
                           class="flex items-center justify-between p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                            <div>
                                <div class="font-bold text-sm text-slate-900">Catatan Konseling</div>
                                <div class="text-xs text-slate-500">Lihat semua riwayat sesi konseling</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-guru.bk.siswa-binaan') }}"
                           class="flex items-center justify-between p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                            <div>
                                <div class="font-bold text-sm text-slate-900">Siswa Binaan (Kelas)</div>
                                <div class="text-xs text-slate-500">Pantau daftar siswa di kelas binaan BK</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>
            </template>
            @endif
        </div>
    </div>
</div>
