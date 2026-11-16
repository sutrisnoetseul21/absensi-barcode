<div class="p-4 lg:p-6 space-y-6 max-w-7xl mx-auto pb-16 font-jakarta">
    <!-- Hero Welcome Card (Gaya Portal Guru) -->
    <div class="relative bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 overflow-hidden border border-brand-primary/30">
        <div class="absolute -top-24 -right-24 w-80 h-80 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-brand-secondary/20 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                        Portal Presensi
                    </span>
                    <span class="bg-white/15 text-white/95 text-xs font-semibold px-3 py-1 rounded-full border border-white/20">
                        TA {{ $activeYearName }}
                    </span>
                    @if($isHoliday)
                        <span class="bg-amber-400/25 text-amber-100 text-xs font-bold px-3 py-1 rounded-full border border-amber-300/40">
                            Hari Libur: {{ $holidayDesc }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold mt-3 tracking-tight leading-snug">
                    Selamat Datang, {{ $userName }}!
                </h1>
                <p class="text-white/85 text-xs sm:text-sm mt-1.5 max-w-2xl leading-relaxed font-medium">
                    Ringkasan cepat aktivitas presensi hari ini (<strong class="text-white">{{ $todayFormatted }}</strong>). Pilih modul presensi di bawah atau buka mesin scanner kiosk.
                </p>
            </div>

            <!-- Pintasan Cepat Kiosk -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ $kioskKehadiranUrl }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl text-xs font-bold transition-all shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Kiosk Presensi Datang</span>
                </a>

                @if($enableSholatDhuhur)
                    <a href="{{ route('kiosk.scan-sholat-dhuhur') }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-500 hover:bg-teal-600 text-white rounded-2xl text-xs font-bold transition-all shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Kiosk Sholat Dhuhur</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Grid Kartu Modul & Pintasan -->
    <div>
        <div class="mb-4">
            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Modul Layanan Presensi</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Rekap singkat harian hari ini dan akses cepat ke masing-masing jenis presensi.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- KARTU 1: PRESENSI KEHADIRAN (FOKUS ABSEN DATANG) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-brand-primary flex items-center justify-center border border-indigo-100 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Presensi Kehadiran</h3>
                                <p class="text-xs text-slate-500">Fokus absen datang hari ini &bull; Masuk {{ $checkinTime }} WIB</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-brand-primary border border-indigo-100 shrink-0">
                            {{ $kehadiranStats['persentase'] }}% Hadir
                        </span>
                    </div>

                    <!-- Rekapan Kecil Absen Datang -->
                    <div class="grid grid-cols-3 gap-2.5 mt-5">
                        <div class="rounded-2xl bg-emerald-50/80 border border-emerald-200/70 p-3">
                            <div class="text-[11px] font-bold text-emerald-700">Sudah Presensi</div>
                            <div class="text-xl font-black text-emerald-900 mt-0.5 tabular-nums">
                                {{ $kehadiranStats['sudah_presensi'] }}
                                <span class="text-xs font-semibold text-emerald-600">/ {{ $kehadiranStats['total'] }}</span>
                            </div>
                            <div class="text-[10px] text-emerald-700/80 mt-0.5">Tepat: {{ $kehadiranStats['hadir'] }}</div>
                        </div>

                        <div class="rounded-2xl bg-amber-50/80 border border-amber-200/70 p-3">
                            <div class="text-[11px] font-bold text-amber-700">Terlambat</div>
                            <div class="text-xl font-black text-amber-900 mt-0.5 tabular-nums">
                                {{ $kehadiranStats['telat'] }}
                            </div>
                            <div class="text-[10px] text-amber-700/80 mt-0.5">Masuk telat</div>
                        </div>

                        <div class="rounded-2xl bg-rose-50/80 border border-rose-200/70 p-3">
                            <div class="text-[11px] font-bold text-rose-700">Belum Hadir</div>
                            <div class="text-xl font-black text-rose-900 mt-0.5 tabular-nums">
                                {{ $kehadiranStats['belum_hadir'] }}
                            </div>
                            <div class="text-[10px] text-rose-700/80 mt-0.5">S/I/A: {{ $kehadiranStats['izin_sakit_alpa'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
                    <a href="{{ route('portal-presensi.kehadiran.dashboard') }}"
                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-extrabold text-white bg-brand-primary hover:opacity-95 shadow-sm shadow-brand-primary/20 transition-all">
                        <span>Buka Dashboard Kehadiran</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('portal-presensi.input-manual') }}"
                       class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Input Manual
                    </a>
                    <a href="{{ route('portal-presensi.cetak-laporan') }}"
                       class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Rekap dan Cetak Laporan
                    </a>
                </div>
            </div>

            <!-- KARTU 2: PRESENSI SHOLAT DHUHUR -->
            @if($enableSholatDhuhur)
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-100 shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Presensi Sholat Dhuhur</h3>
                                    <p class="text-xs text-slate-500">Ibadah berjamaah harian siswa</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-teal-50 text-teal-700 border border-teal-100 shrink-0">
                                {{ $dhuhurStats['persentase'] }}% Hadir
                            </span>
                        </div>

                        <!-- Rekapan Kecil Sholat Dhuhur -->
                        <div class="grid grid-cols-3 gap-2.5 mt-5">
                            <div class="rounded-2xl bg-emerald-50/80 border border-emerald-200/70 p-3">
                                <div class="text-[11px] font-bold text-emerald-700">Hadir Sholat</div>
                                <div class="text-xl font-black text-emerald-900 mt-0.5 tabular-nums">
                                    {{ $dhuhurStats['hadir'] }}
                                    <span class="text-xs font-semibold text-emerald-600">/ {{ $dhuhurStats['wajib'] }}</span>
                                </div>
                                <div class="text-[10px] text-emerald-700/80 mt-0.5">Berjamaah</div>
                            </div>

                            <div class="rounded-2xl bg-sky-50/80 border border-sky-200/70 p-3">
                                <div class="text-[11px] font-bold text-sky-700">Ijin / Haid</div>
                                <div class="text-xl font-black text-sky-900 mt-0.5 tabular-nums">
                                    {{ $dhuhurStats['izin_halangan'] }}
                                </div>
                                <div class="text-[10px] text-sky-700/80 mt-0.5">Alpa: {{ $dhuhurStats['tidak_hadir'] }}</div>
                            </div>

                            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-3">
                                <div class="text-[11px] font-bold text-slate-600">Belum Absen</div>
                                <div class="text-xl font-black text-slate-900 mt-0.5 tabular-nums">
                                    {{ $dhuhurStats['belum'] }}
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">Wajib sholat</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        <a href="{{ route('portal-presensi.sholat-dhuhur.dashboard') }}"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-extrabold text-white bg-teal-600 hover:bg-teal-700 shadow-sm shadow-teal-600/20 transition-all">
                            <span>Buka Dashboard Dhuhur</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('portal-presensi.sholat-dhuhur.input-manual') }}"
                           class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                            Input Manual
                        </a>
                        <a href="{{ route('portal-presensi.sholat-dhuhur.laporan') }}"
                           class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                            Rekap dan Cetak Laporan
                        </a>
                    </div>
                </div>
            @endif

            <!-- KARTU 3: CETAK KARTU SISWA -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center border border-violet-100 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Cetak Kartu Presensi</h3>
                                <p class="text-xs text-slate-500">Kartu barcode / QR siswa untuk scan mesin kiosk</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-violet-50 text-violet-700 border border-violet-100 shrink-0">
                            {{ $totalKelas }} Kelas
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-4 leading-relaxed">
                        Unduh atau cetak kartu siswa per kelas maupun individu yang kompatibel dengan kiosk presensi datang maupun sholat dhuhur.
                    </p>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a href="{{ route('portal-presensi.cetak-kartu') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-extrabold text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <span>Buka Cetak Kartu</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- KARTU 4: PENGATURAN PRESENSI & HARI LIBUR -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Pengaturan &amp; Hari Libur</h3>
                                <p class="text-xs text-slate-500">Jam masuk, absen pulang, auto alpa &amp; kalender libur</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                            {{ $workDaysLabel }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-4">
                        <span class="px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
                            Jam Masuk: {{ $checkinTime }} WIB
                        </span>
                        <span class="px-2.5 py-1 rounded-xl border text-xs font-bold {{ $enableCheckout ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-500' }}">
                            Absen Pulang: {{ $enableCheckout ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a href="{{ route('portal-presensi.pengaturan') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-extrabold text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <span>Buka Pengaturan</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

