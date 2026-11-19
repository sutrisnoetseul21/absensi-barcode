<div class="min-h-full pb-16 font-jakarta max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-brand-primary"></span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pengaturan Presensi</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                Kelola aturan jam kehadiran, otomatisasi alpa, hari kerja sekolah, dan kalender hari libur secara terpusat.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200/80 text-xs font-bold text-brand-primary self-start sm:self-auto">
            <span>🔗 Sinkron otomatis dengan aturan Kiosk &amp; Scheduler</span>
        </div>
    </div>

    <!-- Tab Switcher -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-1.5 inline-flex flex-wrap gap-1 shadow-xs">
        <button type="button" wire:click="setTab('presensi')"
            class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all cursor-pointer {{ $activeTab === 'presensi' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Pengaturan Presensi</span>
        </button>

        <button type="button" wire:click="setTab('hari_libur')"
            class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all cursor-pointer {{ $activeTab === 'hari_libur' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Hari Libur &amp; Hari Kerja</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: PENGATURAN PRESENSI (TANPA KONEKSI API WA) -->
    <!-- ========================================================================= -->
    @if($activeTab === 'presensi')
        <form wire:submit.prevent="savePresensiSettings" class="space-y-6">
            <!-- Section 1: Waktu Kehadiran & Fitur -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Waktu Kehadiran &amp; Fitur Presensi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Atur jam masuk, toleransi keterlambatan, batas scan kiosk, serta fitur presensi pulang, sholat, dan KBM.</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Jam Masuk -->
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jam Masuk (Check-in) <span class="text-rose-500">*</span>
                            </label>
                            <x-portal-presensi.time-24-wib model="checkin_time" :value="$checkin_time" />
                            <p class="text-[11px] text-slate-400 mt-1">Jam normal siswa masuk sekolah (format 24 jam WIB).</p>
                            @error('checkin_time') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Batas Toleransi Terlambat -->
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                Batas Toleransi Terlambat (Menit) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" min="0" max="240" wire:model="late_threshold_minutes"
                                class="w-full px-3.5 py-2.5 text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all">
                            <p class="text-[11px] text-slate-400 mt-1">Jumlah menit toleransi setelah jam masuk sebelum dianggap terlambat.</p>
                            @error('late_threshold_minutes') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Batas Jam Absen Datang -->
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                Batas Jam Absen Datang (Kiosk) <span class="text-rose-500">*</span>
                            </label>
                            <x-portal-presensi.time-24-wib model="batas_scan_datang_time" :value="$batas_scan_datang_time" />
                            <p class="text-[11px] text-slate-400 mt-1">Maksimal jam siswa bisa absen datang lewat mesin scanner (24 jam WIB). Lewat dari ini ditolak otomatis.</p>
                            @error('batas_scan_datang_time') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Mode Kios Scanner Barcode -->
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                Mode Kios Scanner Barcode <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="barcode_scan_mode"
                                class="w-full px-3.5 py-2.5 text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all">
                                <option value="nisn">Gunakan NISN (Default)</option>
                                <option value="nis">Gunakan NIS</option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Menentukan jenis barcode yang akan dipindai oleh mesin presensi.</p>
                            @error('barcode_scan_mode') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-5 space-y-4">
                        <!-- Toggle Presensi Pulang -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/70 space-y-4">
                            <label class="flex items-start justify-between gap-4 cursor-pointer">
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">Aktifkan Presensi Pulang (Check-out)</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Jika dinonaktifkan, Kiosk Scanner dan menu presensi hanya mencatat kedatangan, dan kolom pulang disembunyikan.</span>
                                </div>
                                <input type="checkbox" wire:model.live="enable_checkout"
                                    class="mt-1 h-5 w-5 rounded border-slate-300 text-brand-primary focus:ring-brand-primary cursor-pointer shrink-0">
                            </label>

                            @if($enable_checkout)
                                <div class="pt-3 border-t border-slate-200/70 max-w-sm">
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Jam Mulai Absen Pulang (Kiosk) <span class="text-rose-500">*</span>
                                    </label>
                                    <x-portal-presensi.time-24-wib model="start_scan_out_time" :value="$start_scan_out_time" bgClass="bg-white" />
                                    <p class="text-[11px] text-slate-400 mt-1">Mesin scanner akan menganggap tap kartu setelah jam ini sebagai absen pulang (24 jam WIB).</p>
                                    @error('start_scan_out_time') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        </div>

                        <!-- Toggle Sholat Dhuhur & Jumat -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/70 space-y-4">
                            <label class="flex items-start justify-between gap-4 cursor-pointer">
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">Aktifkan Presensi Sholat Dhuhur</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Jika dinonaktifkan, menu dan halaman rekap Sholat Dhuhur akan disembunyikan dari portal siswa dan guru.</span>
                                </div>
                                <input type="checkbox" wire:model.live="enable_sholat_dhuhur"
                                    class="mt-1 h-5 w-5 rounded border-slate-300 text-brand-primary focus:ring-brand-primary cursor-pointer shrink-0">
                            </label>

                            @if($enable_sholat_dhuhur)
                                <label class="flex items-start justify-between gap-4 cursor-pointer pt-3 border-t border-slate-200/70">
                                    <div>
                                        <span class="block text-sm font-bold text-slate-800">Aktifkan Presensi Sholat pada Hari Jumat</span>
                                        <span class="block text-xs text-slate-500 mt-0.5">Jika diaktifkan, presensi sholat tetap berjalan di hari Jumat. Jika nonaktif (default), hari Jumat otomatis ditandai libur sholat.</span>
                                    </div>
                                    <input type="checkbox" wire:model="enable_sholat_jumat"
                                        class="mt-1 h-5 w-5 rounded border-slate-300 text-brand-primary focus:ring-brand-primary cursor-pointer shrink-0">
                                </label>
                            @endif
                        </div>

                        <!-- Toggle Presensi KBM -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/70">
                            <label class="flex items-start justify-between gap-4 cursor-pointer">
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">Aktifkan Rekapitulasi Presensi KBM di Portal Siswa</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Jika diaktifkan, siswa dapat melihat rekapitulasi kehadiran per mata pelajaran dari jurnal mengajar guru di aplikasi mobile.</span>
                                </div>
                                <input type="checkbox" wire:model="enable_presensi_kbm"
                                    class="mt-1 h-5 w-5 rounded border-slate-300 text-brand-primary focus:ring-brand-primary cursor-pointer shrink-0">
                            </label>
                        </div>

                        <!-- Toggle Fitur Ekstrakurikuler -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/70">
                            <label class="flex items-start justify-between gap-4 cursor-pointer">
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">Aktifkan Presensi Ekstrakurikuler</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Jika dinonaktifkan, menu dan halaman Ekstrakurikuler beserta Kiosk Scanner-nya akan disembunyikan dari Portal Presensi, Portal Guru, dan Portal Siswa.</span>
                                </div>
                                <input type="checkbox" wire:model="enable_ekstrakurikuler"
                                    class="mt-1 h-5 w-5 rounded border-slate-300 text-brand-primary focus:ring-brand-primary cursor-pointer shrink-0">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Otomatisasi Alpa -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Otomatisasi Alpa</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tandai Alpa secara otomatis untuk siswa yang belum absen pada jam tertentu.</p>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/70">
                            <label class="flex items-start justify-between gap-4 cursor-pointer">
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">Aktifkan Otomatisasi Alpa</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Sistem akan otomatis menandai alpa siswa yang belum absen hari ini (kecuali hari libur).</span>
                                </div>
                                <input type="checkbox" wire:model.live="auto_alpa_active"
                                    class="mt-1 h-5 w-5 rounded border-slate-300 text-brand-primary focus:ring-brand-primary cursor-pointer shrink-0">
                            </label>
                        </div>

                        @if($auto_alpa_active)
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Jam Proses Otomatis <span class="text-rose-500">*</span>
                                </label>
                                <x-portal-presensi.time-24-wib model="auto_alpa_time" :value="$auto_alpa_time" />
                                <p class="text-[11px] text-slate-400 mt-1">Sistem akan mulai memproses siswa yang belum hadir pada jam ini (24 jam WIB).</p>
                                @error('auto_alpa_time') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-200/80 text-xs text-blue-800 font-medium">
                        ℹ️ Pengaturan <strong>Koneksi API WhatsApp (Evolution API)</strong> &amp; template notifikasi sudah dipusatkan di <strong>Portal Sistem</strong>.
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-black text-white bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-95 shadow-lg shadow-brand-primary/25 transition-all cursor-pointer disabled:opacity-50">
                    <svg wire:loading.remove wire:target="savePresensiSettings" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="savePresensiSettings" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Simpan Pengaturan Presensi</span>
                </button>
            </div>
        </form>
    @endif

    <!-- ========================================================================= -->
    <!-- TAB 2: PENGATURAN HARI LIBUR & HARI KERJA -->
    <!-- ========================================================================= -->
    @if($activeTab === 'hari_libur')
        <div class="space-y-6">
            <!-- 2A. Pengaturan Hari Kerja Sekolah -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 rounded-t-3xl">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Pengaturan Hari Kerja Sekolah</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tentukan hari efektif belajar mingguan (5 hari atau 6 hari) beserta tanggal mulai berlakunya.</p>
                </div>

                <form wire:submit.prevent="saveWorkDaysSettings" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 cursor-pointer p-4 rounded-2xl border transition-all {{ $work_days_type === '5_hari' ? 'border-brand-primary bg-indigo-50/60 ring-1 ring-brand-primary/20' : 'border-slate-200 hover:bg-slate-50' }}">
                            <input type="radio" wire:model.live="work_days_type" value="5_hari" class="mt-1 text-brand-primary focus:ring-brand-primary">
                            <div>
                                <div class="text-sm font-bold text-slate-900">5 Hari Sekolah (Senin - Jumat)</div>
                                <div class="text-xs text-slate-500 mt-0.5">Sabtu &amp; Minggu otomatis dihitung sebagai hari libur.</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer p-4 rounded-2xl border transition-all {{ $work_days_type === '6_hari' ? 'border-brand-primary bg-indigo-50/60 ring-1 ring-brand-primary/20' : 'border-slate-200 hover:bg-slate-50' }}">
                            <input type="radio" wire:model.live="work_days_type" value="6_hari" class="mt-1 text-brand-primary focus:ring-brand-primary">
                            <div>
                                <div class="text-sm font-bold text-slate-900">6 Hari Sekolah (Senin - Sabtu)</div>
                                <div class="text-xs text-slate-500 mt-0.5">Hanya Minggu yang dihitung sebagai hari libur rutin.</div>
                            </div>
                        </label>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                        <div class="max-w-sm w-full">
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal Mulai Berlaku <span class="text-rose-500">*</span>
                            </label>
                            <x-portal-presensi.date-indo model="effective_date" :value="$effective_date" placeholder="Pilih tanggal mulai berlaku" />
                            <p class="text-[11px] text-slate-400 mt-1">Periode sebelum tanggal ini tetap memakai tipe hari kerja sebelumnya.</p>
                            @error('effective_date') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black text-white bg-brand-primary hover:opacity-95 shadow-md shadow-brand-primary/20 transition-all cursor-pointer">
                            <span>Simpan Pengaturan Hari Kerja</span>
                        </button>
                    </div>

                    @if(count($work_days_history) > 0)
                        <div class="pt-4 border-t border-slate-100">
                            <h3 class="text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-2">Riwayat Pengaturan Hari Kerja</h3>
                            <div class="space-y-1.5 max-w-lg">
                                @foreach($work_days_history as $hist)
                                    <div class="text-xs text-slate-600 flex items-center justify-between gap-3 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
                                        <span>
                                            📅 {{ \Carbon\Carbon::parse($hist['start_date'])->locale('id')->translatedFormat('d M Y') }}
                                            s/d
                                            {{ !empty($hist['end_date']) ? \Carbon\Carbon::parse($hist['end_date'])->locale('id')->translatedFormat('d M Y') : 'Sekarang' }}
                                        </span>
                                        <span class="font-bold text-brand-primary">
                                            {{ ($hist['work_days_type'] ?? '5_hari') === '5_hari' ? '5 Hari Sekolah' : '6 Hari Sekolah' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </form>
            </div>

            <!-- 2B. Tabel Data Hari Libur -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Data Hari Libur</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar hari libur nasional, cuti bersama, dan libur khusus per kelas.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="confirmImportNasional"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-200 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Impor Libur Nasional 2026</span>
                        </button>

                        <button type="button" wire:click="openCreateHolidayModal"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black text-white bg-brand-primary hover:opacity-95 shadow-md shadow-brand-primary/20 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah Hari Libur</span>
                        </button>
                    </div>
                </div>

                <!-- Filter Search & Type -->
                <div class="px-5 sm:px-6 py-3.5 bg-slate-50/70 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="relative w-full sm:w-72">
                        <input type="text" wire:model.live.debounce.300ms="holidaySearch" placeholder="Cari keterangan libur..."
                            class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 font-medium focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select wire:model.live="holidayTypeFilter"
                        class="text-xs sm:text-sm font-bold bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/30">
                        <option value="all">Semua Tipe Libur</option>
                        <option value="nasional">Libur Nasional</option>
                        <option value="cuti_bersama">Cuti Bersama</option>
                        <option value="khusus">Khusus Kelas</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-5">Keterangan</th>
                                <th class="py-3.5 px-4 text-center">Mulai</th>
                                <th class="py-3.5 px-4 text-center">Selesai</th>
                                <th class="py-3.5 px-4 text-center">Tipe</th>
                                <th class="py-3.5 px-4 text-center">Kelas</th>
                                <th class="py-3.5 px-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($holidays as $holiday)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-5 font-bold text-slate-800">{{ $holiday->description }}</td>
                                    <td class="py-3.5 px-4 text-center text-slate-600 font-medium whitespace-nowrap">
                                        {{ $holiday->start_date ? $holiday->start_date->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center text-slate-600 font-medium whitespace-nowrap">
                                        {{ $holiday->end_date ? $holiday->end_date->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($holiday->type === 'nasional')
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Nasional</span>
                                        @elseif($holiday->type === 'cuti_bersama')
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Cuti Bersama</span>
                                        @elseif($holiday->type === 'khusus')
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Khusus Kelas</span>
                                        @else
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $holiday->type }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center text-slate-600 font-medium">
                                        {{ $holiday->kelas?->name ?? 'Semua Kelas' }}
                                    </td>
                                    <td class="py-3.5 px-5 text-center">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            <button type="button" wire:click="openEditHolidayModal('{{ $holiday->id }}')"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer">
                                                Edit
                                            </button>
                                            <button type="button" wire:click="confirmDeleteHoliday('{{ $holiday->id }}')"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors cursor-pointer">
                                                Hapus
                                            </button>
                                            <button type="button" wire:click="confirmForceLibur('{{ $holiday->id }}')"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition-colors cursor-pointer"
                                                title="Hapus data presensi yang terlanjur tercatat pada rentang tanggal ini">
                                                Force Libur
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400 font-bold">
                                        Belum ada data hari libur yang sesuai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($holidays->hasPages())
                    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/70">
                        {{ $holidays->links() }}
                    </div>
                @endif
            </div>

            <!-- 2C. Kalender Hari Libur Interaktif -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Kalender Hari Libur</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Klik tanggal pada kalender untuk menambah hari libur baru.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs font-bold text-slate-600">
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Nasional</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Cuti Bersama</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Khusus Kelas</span>
                    </div>
                </div>

                <div class="p-5 sm:p-6" wire:ignore
                     x-data="{
                         calendar: null,
                         initEvents: {{ \Illuminate\Support\Js::from($calendarEvents) }},
                         init() {
                             const mountCal = () => {
                                 if (typeof FullCalendar === 'undefined') return;
                                 const el = this.$refs.calendarEl;
                                 if (!el || this.calendar) return;
                                 this.calendar = new FullCalendar.Calendar(el, {
                                     initialView: 'dayGridMonth',
                                     locale: 'id',
                                     events: this.initEvents,
                                     height: 'auto',
                                     headerToolbar: {
                                         left: 'prev,next today',
                                         center: 'title',
                                         right: 'dayGridMonth,dayGridWeek'
                                     },
                                     dateClick: (info) => {
                                         $wire.openCreateHolidayModal(info.dateStr);
                                     }
                                 });
                                 this.calendar.render();
                             };
                             if (typeof FullCalendar !== 'undefined') {
                                 mountCal();
                             } else {
                                 const script = document.createElement('script');
                                 script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js';
                                 script.onload = mountCal;
                                 document.head.appendChild(script);
                             }
                         },
                         refreshEvents(events) {
                             if (!this.calendar) return;
                             this.calendar.removeAllEvents();
                             this.calendar.addEventSource(events);
                         }
                     }"
                     @holiday-calendar-updated.window="refreshEvents($event.detail.events)">
                    <div x-ref="calendarEl" style="min-height: 420px;"></div>
                </div>
            </div>

            <!-- 2D. Log Aktivitas & Riwayat -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Log Aktivitas &amp; Riwayat Perubahan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">15 aktivitas terbaru terkait pengaturan presensi, hari kerja, dan hari libur.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-5">Tanggal &amp; Waktu</th>
                                <th class="py-3 px-4">Aktor</th>
                                <th class="py-3 px-4">Aktivitas</th>
                                <th class="py-3 px-5">Detail Perubahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($holidayLogs as $log)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-3 px-5 whitespace-nowrap font-medium text-slate-800">
                                        {{ $log->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i:s') }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-700">{{ $log->causer?->name ?? 'System' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold {{ str_contains($log->description, 'Menghapus') || str_contains($log->description, 'Force') ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $log->description }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-5 text-xs font-mono text-slate-600">
                                        @if(in_array($log->description, ['Menghapus hari libur', 'Menghapus hari libur (Bulk)', 'Menjalankan Force Libur']))
                                            <div>Keterangan: {{ $log->properties['description'] ?? '-' }}</div>
                                            <div>Mulai: {{ $log->properties['start_date'] ?? '-' }}</div>
                                            @if(!empty($log->properties['end_date']))
                                                <div>Selesai: {{ $log->properties['end_date'] }}</div>
                                            @endif
                                            @if(isset($log->properties['deleted_presensi']))
                                                <div>Presensi Dihapus: {{ $log->properties['deleted_presensi'] }}</div>
                                            @endif
                                        @elseif($log->description === 'Mengubah pengaturan hari kerja sekolah')
                                            <div>Tipe Baru: {{ ($log->properties['new_work_days_type'] ?? '') === '5_hari' ? '5 Hari Sekolah' : '6 Hari Sekolah' }}</div>
                                            <div>Mulai Berlaku: {{ $log->properties['effective_date'] ?? '-' }}</div>
                                        @elseif($log->description === 'Mengubah pengaturan presensi')
                                            <div>Jam Masuk: {{ $log->properties['checkin_time'] ?? '-' }} | Toleransi: {{ $log->properties['late_threshold_minutes'] ?? '-' }} mnt | Auto Alpa: {{ !empty($log->properties['auto_alpa_active']) ? 'Aktif (' . ($log->properties['auto_alpa_time'] ?? '-') . ')' : 'Nonaktif' }}</div>
                                        @else
                                            {{ json_encode($log->properties) }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400 font-bold">
                                        Belum ada log aktivitas yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL TAMBAH / EDIT HARI LIBUR -->
    <!-- ========================================================================= -->
    @if($showHolidayModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeHolidayModal"></div>

                <div class="relative z-10 w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100">
                    <div class="px-6 py-4 bg-gradient-to-r from-brand-primary to-brand-secondary text-white flex items-center justify-between rounded-t-3xl">
                        <h3 class="text-base sm:text-lg font-black">
                            {{ $editingHolidayId ? 'Edit Hari Libur' : 'Tambah Hari Libur Baru' }}
                        </h3>
                        <button type="button" wire:click="closeHolidayModal" class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveHoliday" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tanggal Mulai <span class="text-rose-500">*</span>
                                </label>
                                <x-portal-presensi.date-indo model="holiday_start_date" :value="$holiday_start_date" placeholder="Pilih tanggal mulai" />
                                @error('holiday_start_date') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tanggal Selesai
                                </label>
                                <x-portal-presensi.date-indo model="holiday_end_date" :value="$holiday_end_date" :nullable="true" placeholder="Opsional (1 hari)" />
                                <p class="text-[11px] text-slate-400 mt-1">Biarkan kosong jika libur hanya 1 hari.</p>
                                @error('holiday_end_date') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                Keterangan / Nama Libur <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="holiday_description" placeholder="Contoh: Libur Awal Ramadhan / Ujian Kelas 9"
                                class="w-full px-3.5 py-2.5 text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30">
                            @error('holiday_description') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tipe Libur <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model.live="holiday_type"
                                class="w-full px-3.5 py-2.5 text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30">
                                <option value="nasional">Libur Nasional</option>
                                <option value="cuti_bersama">Cuti Bersama</option>
                                <option value="khusus">Khusus Kelas</option>
                            </select>
                            @error('holiday_type') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($holiday_type === 'khusus')
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Pilih Kelas (Khusus Kelas) <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="holiday_class_id"
                                    class="w-full px-3.5 py-2.5 text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $cls)
                                        <option value="{{ $cls->id }}">Kelas {{ $cls->name }}</option>
                                    @endforeach
                                </select>
                                @error('holiday_class_id') <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                            <button type="button" wire:click="closeHolidayModal"
                                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs sm:text-sm font-black text-white bg-brand-primary hover:opacity-95 shadow-md shadow-brand-primary/20 cursor-pointer">
                                {{ $editingHolidayId ? 'Simpan Perubahan' : 'Tambahkan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL KONFIRMASI AKSI -->
    <!-- ========================================================================= -->
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeConfirmModal"></div>

                <div class="relative z-10 w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 {{ $confirmActionType === 'import_nasional' ? 'bg-sky-100 text-sky-700' : 'bg-rose-100 text-rose-700' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900">{{ $confirmTitle }}</h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed">{{ $confirmMessage }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeConfirmModal"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                            Batal
                        </button>
                        <button type="button" wire:click="executeConfirmedAction"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-black text-white cursor-pointer {{ $confirmActionType === 'import_nasional' ? 'bg-sky-600 hover:bg-sky-700' : ($confirmActionType === 'force_libur' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-rose-600 hover:bg-rose-700') }}">
                            Ya, Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

