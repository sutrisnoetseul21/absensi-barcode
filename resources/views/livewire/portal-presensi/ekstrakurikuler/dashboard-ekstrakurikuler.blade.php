<div class="p-4 lg:p-6 space-y-6 max-w-7xl mx-auto pb-16 font-jakarta">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="w-3 h-3 rounded-full bg-brand-primary animate-pulse"></span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Presensi Ekstrakurikuler</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                Pusat Kiosk Scanner, pemantauan sesi latihan, dan broadcast WhatsApp untuk <strong class="text-slate-700">{{ $dateFormatted }}</strong>.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Date Picker Indo -->
            <div class="relative">
                <x-portal-presensi.date-indo
                    model="selectedDate"
                    :value="$selectedDate"
                    :live="true"
                    placeholder="Pilih tanggal"
                    buttonClass="min-w-[215px] flex items-center justify-between gap-2 px-3 py-2 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 font-bold text-slate-700 shadow-xs hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-left cursor-pointer"
                />
            </div>

            <!-- Academic Year -->
            <select wire:model.live="selectedAcademicYearId"
                class="py-2 pl-3 pr-8 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 font-bold text-slate-700 shadow-xs focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all cursor-pointer">
                @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}">TA {{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                @endforeach
            </select>

            <a href="{{ route('portal-presensi.ekstrakurikuler.input-manual') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-brand-primary hover:opacity-95 shadow-sm transition-all">
                <span>Input Manual</span>
            </a>

            <a href="{{ route('portal-presensi.ekstrakurikuler.laporan') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs transition-all">
                <span>Rekap &amp; Cetak</span>
            </a>
        </div>
    </div>

    <!-- Spotlight Card (Ikut Tema Portal Sistem: brand-primary -> brand-secondary) -->
    <div class="relative overflow-hidden bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary border border-brand-primary/30 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20">
        <div class="absolute -top-12 -right-12 w-72 h-72 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-white/20 border border-white/25">
                    Ringkasan Ekstrakurikuler
                </span>
                <h2 class="text-2xl sm:text-3xl font-black mt-3">Aktivitas Ekstrakurikuler Tanggal Dipilih</h2>
                <p class="text-white/90 text-xs sm:text-sm mt-1">
                    Ekskul Aktif: <strong>{{ $totalEkskul }}</strong> &bull;
                    Sesi Terbuka: <strong>{{ $totalSesiTanggal }}</strong> &bull;
                    Hadir Tercatat: <strong>{{ $totalHadirTanggal }}</strong> &bull;
                    Belum Hadir / Alpa (pada sesi terbuka): <strong>{{ $totalBelumTanggal }}</strong>
                </p>
            </div>
            <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl px-5 py-4 text-right shrink-0">
                <div class="text-[11px] font-bold uppercase tracking-wider text-white/80">Total Anggota Aktif</div>
                <div class="text-3xl font-black tabular-nums mt-0.5">{{ $totalAnggotaSemua }}</div>
                <div class="text-xs font-bold text-white/90 mt-0.5">
                    @if($waEkskulActive)
                        Notif WA Ekskul Aktif ({{ $manualSentCount }}/5 hari ini)
                    @else
                        Notif WA Ekskul Nonaktif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ekskul Aktif</div>
            <div class="text-2xl sm:text-3xl font-black text-brand-primary mt-1 tabular-nums">{{ $totalEkskul }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $totalAnggotaSemua }} anggota aktif</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sesi Terbuka</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 tabular-nums">{{ $totalSesiTanggal }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Pada tanggal terpilih</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hadir</div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1 tabular-nums">{{ $totalHadirTanggal }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Scan kiosk &amp; manual hadir</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Belum Absen / Alpa</div>
            <div class="text-2xl sm:text-3xl font-black text-amber-600 mt-1 tabular-nums">{{ $totalBelumTanggal }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Pada sesi yang berjalan</div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Daftar Ekstrakurikuler &amp; Kiosk Scanner</h2>
        <div class="w-full sm:w-72">
            <input type="text" wire:model.live.debounce.250ms="search" placeholder="Cari nama ekstrakurikuler..."
                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-xs focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 outline-none">
        </div>
    </div>

    <!-- Grid Kartu Ekstrakurikuler -->
    @if(count($cards) === 0)
        <div class="bg-white rounded-3xl border border-slate-200 p-10 text-center shadow-xs">
            <h3 class="text-base font-extrabold text-slate-800">Belum Ada Ekstrakurikuler Aktif</h3>
            <p class="text-xs text-slate-500 mt-1">Tambahkan ekstrakurikuler di Portal Akademik terlebih dahulu.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($cards as $c)
                @php $p = $c['pertemuan']; @endphp
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all p-5 sm:p-6 flex flex-col justify-between gap-4"
                     x-data="{ copied: false, copyUrl(url) { navigator.clipboard.writeText(url); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-black text-slate-900">{{ $c['nama_ekskul'] }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Pembina: <strong class="text-slate-700">{{ $c['pembinas'] }}</strong> &bull;
                                    {{ $c['anggota_count'] }} Anggota Aktif ({{ $c['total_sesi'] }} Pertemuan)
                                </p>
                            </div>
                            @if($p)
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold {{ $p->status_sesi === 'selesai' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-brand-primary/10 text-brand-primary border border-brand-primary/20' }} shrink-0">
                                    Pertemuan ke-{{ $p->pertemuan_ke }} ({{ $p->status_sesi === 'selesai' ? 'Selesai' : 'Aktif' }})
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 shrink-0">
                                    Belum Ada Sesi Tgl Ini
                                </span>
                            @endif
                        </div>

                        @if($p)
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                                <div class="text-xs font-bold text-slate-700">
                                    Materi: {{ $p->judul_kegiatan ?: 'Belum diisi pembina' }}
                                </div>
                                <div class="flex flex-wrap gap-1.5 text-[11px] font-bold">
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">H: {{ $c['hadir'] }}</span>
                                    <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200">I: {{ $c['izin'] }}</span>
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">S: {{ $c['sakit'] }}</span>
                                    <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200">A: {{ $c['alpa'] }}</span>
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">Belum: {{ $c['belum'] }}</span>
                                </div>
                            </div>
                        @else
                            <div class="p-3 rounded-2xl bg-slate-50/70 border border-dashed border-slate-200 text-xs text-slate-500">
                                Sesi otomatis terbuka saat anggota scan di Kiosk Scanner atau saat input manual disimpan.
                            </div>
                        @endif
                    </div>

                    <!-- Aksi per Ekskul -->
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        @if($c['slug'])
                            <a href="{{ route('kiosk.scan-ekstrakurikuler', ['slug' => $c['slug']]) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-extrabold text-white bg-brand-primary hover:opacity-95 shadow-xs transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                <span>Buka Kiosk</span>
                            </a>
                            <button type="button"
                                    @click="copyUrl('{{ route('kiosk.scan-ekstrakurikuler', ['slug' => $c['slug']]) }}')"
                                    class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">
                                <span x-show="!copied">Salin Link</span>
                                <span x-show="copied">Tersalin!</span>
                            </button>
                        @endif

                        <a href="{{ route('portal-presensi.ekstrakurikuler.input-manual', ['ekskul_id' => $c['id'], 'date' => $selectedDate]) }}"
                           class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                            Input Manual
                        </a>

                        <a href="{{ route('portal-presensi.ekstrakurikuler.laporan', ['ekskul_id' => $c['id']]) }}"
                           class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                            Laporan
                        </a>

                        @if($p && ($c['belum'] + $c['alpa']) > 0)
                            <button type="button"
                                    wire:click="kirimWaBelumHadir('{{ $p->id }}')"
                                    wire:confirm="Kirim notifikasi WA ke orang tua dari anggota {{ $c['nama_ekskul'] }} yang belum hadir/alpa pada Pertemuan ke-{{ $p->pertemuan_ke }}?"
                                    wire:loading.attr="disabled"
                                    @disabled(!$waEkskulActive || !$canSendManualToday)
                                    title="{{ !$waEkskulActive ? 'Aktifkan dulu Notifikasi WA Ekstrakurikuler di Portal Sistem' : 'Kirim WA ke anggota yang belum absen / alpa pada sesi ini' }}"
                                    class="ml-auto inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 disabled:opacity-45 disabled:cursor-not-allowed transition-colors cursor-pointer">
                                <span>Kirim WA Belum Hadir ({{ $c['belum'] + $c['alpa'] }})</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Tabel Riwayat Kehadiran Terbaru pada Tanggal Dipilih -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-black text-slate-800">Aktivitas Kehadiran Ekskul ({{ $dateFormatted }})</h3>
                <p class="text-[11px] text-slate-400">20 catatan scan &amp; input manual terbaru lintas ekstrakurikuler</p>
            </div>
        </div>
        @if($recentLogs->isEmpty())
            <div class="p-8 text-center text-xs font-semibold text-slate-400">
                Belum ada catatan presensi ekstrakurikuler pada tanggal {{ $dateFormatted }}.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Siswa</th>
                            <th class="px-4 py-3">Ekstrakurikuler</th>
                            <th class="px-4 py-3 text-center">Pertemuan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Jam</th>
                            <th class="px-5 py-3">Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentLogs as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-bold text-slate-800">{{ $log->siswa?->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-brand-primary">{{ $log->ekstrakurikuler?->nama_ekskul ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-bold text-slate-600">Ke-{{ $log->pertemuan?->pertemuan_ke ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold
                                        {{ $log->status === 'hadir' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $log->status === 'izin' ? 'bg-sky-100 text-sky-800' : '' }}
                                        {{ $log->status === 'sakit' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $log->status === 'alpa' ? 'bg-rose-100 text-rose-800' : '' }}
                                    ">{{ ucfirst($log->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center font-mono text-slate-600">{{ $log->scan_time ? substr((string) $log->scan_time, 0, 5) . ' WIB' : '-' }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $log->is_manual_input ? ($log->keterangan ?: 'Input Manual') : 'Scan Kiosk' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

