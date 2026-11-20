<x-filament-panels::page>
    <div class="space-y-6" x-data="{ quickModal: null }" @keydown.escape.window="quickModal = null">
        {{-- Hero Banner (Mengikuti Pengaturan Tema Sistem) --}}
        <div class="relative rounded-3xl p-6 sm:p-7 text-white shadow-lg overflow-hidden"
            style="background: linear-gradient(135deg, {{ $themePrimary }}, {{ $themeSecondary }});">
            <div
                class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full filter blur-3xl pointer-events-none">
            </div>
            <div
                class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/10 rounded-full filter blur-2xl pointer-events-none">
            </div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-[11px] font-bold uppercase tracking-wider">
                            Portal Sistem &amp; Akses
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold {{ $waActive ? 'bg-emerald-400/25 text-emerald-50 border border-emerald-300/40' : 'bg-rose-400/25 text-rose-50 border border-rose-300/40' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ $waActive ? 'bg-emerald-300' : 'bg-rose-300' }}"></span>
                            WA Gateway: {{ $waActive ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold {{ $schedulerActive ? 'bg-emerald-400/25 text-emerald-50 border border-emerald-300/40' : 'bg-amber-400/25 text-amber-50 border border-amber-300/40' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ $schedulerActive ? 'bg-emerald-300' : 'bg-amber-300' }}"></span>
                            Scheduler: {{ $schedulerAgeLabel }}
                        </span>
                        @if ($maintenanceCount > 0)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/80 text-white text-[11px] font-bold border border-rose-300/50">
                                {{ $maintenanceCount }} Portal Maintenance
                            </span>
                        @endif
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-3">Dashboard Portal Sistem</h2>
                    <p class="text-sm text-white/85 mt-1">
                        Halo, <strong class="text-white">{{ $userName }}</strong> &bull; {{ $todayFormatted }}.
                        Pilih kotak menu di bawah untuk mengelola akses, saklar layanan, modul, dan gateway WhatsApp.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    @if ($canUserAccess)
                        <button type="button" @click="quickModal = 'users'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-primary-700 rounded-2xl text-xs sm:text-sm font-extrabold hover:bg-primary-50 transition-colors shadow-md cursor-pointer">
                            <x-filament::icon icon="heroicon-o-user-plus" class="w-4 h-4" />
                            Kelola User &amp; Akses
                        </button>
                    @endif
                    @if ($canMaintenance)
                        <a href="{{ $urlMaintenance }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white border border-white/25 rounded-2xl text-xs sm:text-sm font-bold transition-colors backdrop-blur-sm">
                            <x-filament::icon icon="heroicon-o-wrench-screwdriver" class="w-4 h-4" />
                            Saklar Maintenance
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Ringkasan Statistik Akun & Sistem --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-o-users" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">
                        {{ $totalPenggunaNonSiswa }}</div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Akun Staf / Admin</div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-o-shield-check" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">
                        {{ $totalSuperAdmin }}</div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Super Admin</div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-o-key" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">
                        {{ $totalAdminModul }}</div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Admin Modul / Portal</div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-o-lock-closed" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">
                        {{ $totalRoleSistem }}</div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Role Spatie Aktif</div>
                </div>
            </div>
        </div>

        {{-- Kelompok 1: Manajemen Akses, Maintenance & Kontrol Sistem --}}
        <div>
            <div class="mb-3 px-0.5">
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                    Manajemen Akses, Saklar &amp; Kontrol Sistem
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Menu kontrol utama untuk akun pengguna, penguncian
                    portal, pengumuman global, broadcast Android, dan tema warna.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                @if ($canUserAccess)
                    <button type="button" @click="quickModal = 'users'"
                        class="text-left w-full bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-xs hover:shadow-md hover:border-primary-400 dark:hover:border-primary-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3.5">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-users" class="w-5 h-5" />
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 border border-primary-100 dark:border-primary-900">
                                    {{ $totalPenggunaNonSiswa }} Akun
                                </span>
                            </div>
                            <h4
                                class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                Manajemen User &amp; Akses</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Tambah akun
                                admin/staf, atur hak akses portal, atau reset password pengguna.</p>
                        </div>
                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-primary-600 dark:text-primary-400 w-full">
                            <span>Pilih Tambah / Kelola</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </button>
                @endif

                @if ($canMaintenance)
                    <a href="{{ $urlMaintenance }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border {{ $maintenanceCount > 0 ? 'border-rose-300 dark:border-rose-800' : 'border-gray-200 dark:border-gray-800' }} p-5 shadow-xs hover:shadow-md hover:border-amber-400 dark:hover:border-amber-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3.5">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-wrench-screwdriver" class="w-5 h-5" />
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $maintenanceCount > 0 ? 'bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800' : 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900' }}">
                                    {{ $maintenanceCount > 0 ? $maintenanceCount . ' Terkunci' : 'Semua Normal' }}
                                </span>
                            </div>
                            <h4
                                class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                Mode Maintenance &amp; Saklar</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                Saklar tutup portal (Siswa/Guru/Perpus), form /pengaduan, /alumni &amp; banner global.
                            </p>
                        </div>
                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400">
                            <span>{{ $globalAnnouncementActive ? 'Banner Global: Aktif' : 'Atur Saklar Portal' }}</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                @if ($canPesanMassal)
                    <a href="{{ $urlPesanMassal }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-xs hover:shadow-md hover:border-blue-400 dark:hover:border-blue-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3.5">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-megaphone" class="w-5 h-5" />
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-900">
                                    FCM Push
                                </span>
                            </div>
                            <h4
                                class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Pesan Massal Android</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Kirim broadcast
                                notifikasi push ke aplikasi Android siswa, guru, atau per kelas.</p>
                        </div>
                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-blue-600 dark:text-blue-400">
                            <span>Kirim Broadcast</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                @if ($canTheme)
                    <a href="{{ $urlTheme }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-xs hover:shadow-md hover:border-fuchsia-400 dark:hover:border-fuchsia-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3.5">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-fuchsia-50 dark:bg-fuchsia-950/50 text-fuchsia-600 dark:text-fuchsia-400 border border-fuchsia-100 dark:border-fuchsia-900 flex items-center justify-center group-hover:bg-fuchsia-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-swatch" class="w-5 h-5" />
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-fuchsia-50 dark:bg-fuchsia-950/60 text-fuchsia-700 dark:text-fuchsia-300 border border-fuchsia-100 dark:border-fuchsia-900">
                                    Brand UI
                                </span>
                            </div>
                            <h4
                                class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-fuchsia-600 dark:group-hover:text-fuchsia-400 transition-colors">
                                Pengaturan Tema</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Atur preset warna
                                utama dan palet brand di seluruh portal &amp; website publik.</p>
                        </div>
                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-fuchsia-600 dark:text-fuchsia-400">
                            <span>Sesuaikan Warna</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif
            </div>
        </div>

        {{-- Kelompok 2: Gateway & Pengaturan Modul --}}
        <div>
            <div class="mb-3 px-0.5">
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Gateway &amp; Pengaturan Modul
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Konfigurasi server gateway WhatsApp dan identitas
                    modul publik/khusus (SPIKAP, Pelayanan Publik, Tracer Alumni).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                @if ($canKoneksiWa)
                    <a href="{{ $urlKoneksiWa }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border {{ $waActive ? 'border-gray-200 dark:border-gray-800' : 'border-rose-200 dark:border-rose-900' }} p-5 shadow-xs hover:shadow-md hover:border-emerald-400 dark:hover:border-emerald-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3.5">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-signal" class="w-5 h-5" />
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $waActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900' : 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800' }}">
                                    {{ $waActive ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <h4
                                class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                Pengaturan Koneksi WA</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Instance: <strong
                                    class="text-gray-700 dark:text-gray-200">{{ $waInstance }}</strong> &bull; Jam
                                kirim, jeda &amp; test koneksi.</p>
                        </div>
                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400">
                            <span>Konfigurasi Gateway</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                @if ($canSpikapSetting)
                    <a href="{{ $urlSpikapSetting }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-xs hover:shadow-md hover:border-rose-400 dark:hover:border-rose-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3.5">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-shield-exclamation" class="w-5 h-5" />
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-100 dark:border-rose-900">
                                    /{{ $spikapSlug }}
                                </span>
                            </div>
                            <h4
                                class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">
                                Pengaturan Modul {{ $spikapName }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Penamaan aplikasi,
                                slug URL khusus &amp; pejabat penangan laporan darurat.</p>
                        </div>
                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-rose-600 dark:text-rose-400">
                            <span>Atur Modul {{ $spikapName }}</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                @if ($canPelayananSetting)
                    <a href="{{ $urlPelayananSetting }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-xs hover:shadow-md hover:border-cyan-400 dark:hover:border-cyan-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3.5">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-cyan-50 dark:bg-cyan-950/50 text-cyan-600 dark:text-cyan-400 border border-cyan-100 dark:border-cyan-900 flex items-center justify-center group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-5 h-5" />
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $pengaduanActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900' : 'bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                                    /pengaduan {{ $pengaduanActive ? 'Buka' : 'Tutup' }}
                                </span>
                            </div>
                            <h4
                                class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">
                                Pengaturan Layanan Publik</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Banner pengaduan,
                                Maklumat Pelayanan, link SP4N-LAPOR!, SIPPN &amp; IKM.</p>
                        </div>
                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-cyan-600 dark:text-cyan-400">
                            <span>Atur Layanan Publik</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif

                @if ($canAlumniSetting)
                    <a href="{{ $urlAlumniSetting }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-xs hover:shadow-md hover:border-indigo-400 dark:hover:border-indigo-600 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3.5">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-5 h-5" />
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $alumniActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900' : 'bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                                    /alumni {{ $alumniActive ? 'Buka' : 'Tutup' }}
                                </span>
                            </div>
                            <h4
                                class="font-extrabold text-gray-900 dark:text-white text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                Pengaturan Tracer Alumni</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Judul banner,
                                deskripsi pengantar &amp; tombol formulir tracer alumni.</p>
                        </div>
                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            <span>Atur Tracer Alumni</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endif
            </div>
        </div>

        {{-- Kelompok 3: Pusat Template & Penerima Notifikasi WhatsApp --}}
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 px-0.5">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                        Pusat Notifikasi WhatsApp Otomatis
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kelola template pesan &amp; penerima otomatis
                        untuk 5 modul notifikasi WhatsApp ({{ $waNotifAktifCount }} aturan aktif).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                @if ($canWaPresensi)
                    <a href="{{ $urlWaPresensi }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-teal-400 dark:hover:border-teal-600 transition-all group flex items-center gap-3.5">
                        <div
                            class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                            <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-teal-600 dark:group-hover:text-teal-400 truncate">
                                Notifikasi WA Presensi</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Datang, pulang, dhuhur,
                                ekskul, ijin &amp; rekap</p>
                        </div>
                        <span class="text-gray-400 group-hover:text-teal-600 text-sm font-bold">&rarr;</span>
                    </a>
                @endif

                @if ($canWaJurnal)
                    <a href="{{ $urlWaJurnal }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-sky-400 dark:hover:border-sky-600 transition-all group flex items-center gap-3.5">
                        <div
                            class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                            <x-filament::icon icon="heroicon-o-book-open" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-sky-600 dark:group-hover:text-sky-400 truncate">
                                Notifikasi WA Jurnal Mengajar</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Absensi KBM ke wali kelas,
                                ortu &amp; siswa</p>
                        </div>
                        <span class="text-gray-400 group-hover:text-sky-600 text-sm font-bold">&rarr;</span>
                    </a>
                @endif

                @if ($canWaSpikap)
                    <a href="{{ $urlWaSpikap }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-rose-400 dark:hover:border-rose-600 transition-all group flex items-center gap-3.5">
                        <div
                            class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                            <x-filament::icon icon="heroicon-o-shield-exclamation" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-rose-600 dark:group-hover:text-rose-400 truncate">
                                Notifikasi WA {{ $spikapName }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Laporan darurat, biasa,
                                apresiasi &amp; status</p>
                        </div>
                        <span class="text-gray-400 group-hover:text-rose-600 text-sm font-bold">&rarr;</span>
                    </a>
                @endif

                @if ($canWaGuruWali)
                    <a href="{{ $urlWaGuruWali }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-indigo-400 dark:hover:border-indigo-600 transition-all group flex items-center gap-3.5">
                        <div
                            class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <x-filament::icon icon="heroicon-o-user-group" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate">
                                Notifikasi WA Guru Wali</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Pengajuan &amp; jadwal
                                bimbingan tatap muka</p>
                        </div>
                        <span class="text-gray-400 group-hover:text-indigo-600 text-sm font-bold">&rarr;</span>
                    </a>
                @endif

                @if ($canWaPelayanan)
                    <a href="{{ $urlWaPelayanan }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-emerald-400 dark:hover:border-emerald-600 transition-all group flex items-center gap-3.5">
                        <div
                            class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <x-filament::icon icon="heroicon-o-inbox-stack" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-emerald-600 dark:group-hover:text-emerald-400 truncate">
                                Notifikasi WA Pelayanan Publik</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Notif petugas, tanda terima
                                &amp; status aduan</p>
                        </div>
                        <span class="text-gray-400 group-hover:text-emerald-600 text-sm font-bold">&rarr;</span>
                    </a>
                @endif

                @if ($canPortalSetup)
                    <a href="{{ url('/portal-setup') }}"
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-xs hover:shadow-md hover:border-slate-400 dark:hover:border-slate-600 transition-all group flex items-center gap-3.5">
                        <div
                            class="w-11 h-11 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 flex items-center justify-center shrink-0 group-hover:bg-gray-800 group-hover:text-white transition-colors">
                            <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-gray-900 dark:text-white text-sm truncate">Portal Setup &amp;
                                Instalasi</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Identitas sekolah, tahun
                                ajaran, kelas &amp; mapel</p>
                        </div>
                        <span class="text-gray-400 text-sm font-bold">&rarr;</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Pop-up Pilihan Cepat: Manajemen User & Akses --}}
        @if ($canUserAccess)
            <div x-show="quickModal === 'users'" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click.self="quickModal = null">

                <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 w-full max-w-md overflow-hidden"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2">
                    <div class="px-6 py-5 text-white flex items-center justify-between"
                        style="background: linear-gradient(135deg, {{ $themePrimary }}, {{ $themeSecondary }});">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <x-filament::icon icon="heroicon-o-users" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">Manajemen User &amp; Akses</h3>
                                <p class="text-xs text-white/80">Pilih aksi yang ingin dilakukan</p>
                            </div>
                        </div>
                        <button type="button" @click="quickModal = null"
                            class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                            <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="p-5 space-y-2.5">
                        @if ($canCreateUser)
                            <a href="{{ $urlUsersCreate }}"
                                class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-primary-200 dark:border-primary-800 bg-primary-50/70 dark:bg-primary-950/30 hover:bg-primary-100/80 dark:hover:bg-primary-950/60 transition-colors group">
                                <div
                                    class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shrink-0">
                                    <x-filament::icon icon="heroicon-o-user-plus" class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-extrabold text-sm text-gray-900 dark:text-white">Tambah User /
                                        Admin Baru</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Buat akun staf, admin portal,
                                        atau petugas baru</div>
                                </div>
                                <span
                                    class="text-primary-600 dark:text-primary-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>
                        @endif

                        <a href="{{ $urlUsersIndex }}"
                            class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                            <div
                                class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 flex items-center justify-center shrink-0">
                                <x-filament::icon icon="heroicon-o-key" class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-gray-900 dark:text-white">Lihat &amp; Atur Hak
                                    Akses Portal</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Kelola
                                    {{ $totalPenggunaNonSiswa }} akun, ubah role, atau reset password</div>
                            </div>
                            <span
                                class="text-gray-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
