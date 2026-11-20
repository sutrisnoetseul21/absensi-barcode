<div class="p-4 lg:p-6 space-y-6 max-w-7xl mx-auto pb-16 font-jakarta" x-data="{
    showWaModal: false,
    waStudentId: '',
    waStudentName: '',
    waStudentClass: '',
    waStudentNisn: '',
    waRecipientPhone: '',
    waOriginalMessage: '',
    waMessage: '',
    waStatusType: 'ekstrakurikuler',
    waStatusLabel: '',
    isSending: false,
    openWaModal(data) {
        this.waStudentId = data.id;
        this.waStudentName = data.name;
        this.waStudentClass = data.className;
        this.waStudentNisn = data.nisn || '-';
        this.waRecipientPhone = data.cleanPhone || data.phone || '';
        this.waOriginalMessage = data.rawText || '';
        this.waMessage = data.rawText || '';
        this.waStatusType = data.type || 'ekstrakurikuler';
        this.waStatusLabel = data.statusLabel || 'Belum Absen Ekskul';
        this.isSending = false;
        this.showWaModal = true;
    },
    resetTemplate() {
        this.waMessage = this.waOriginalMessage;
    },
    submitSend() {
        if (!this.waRecipientPhone.trim()) { alert('Silakan masukkan nomor WhatsApp tujuan!'); return; }
        if (!this.waMessage.trim()) { alert('Isi pesan tidak boleh kosong!'); return; }
        this.isSending = true;
        this.$wire.sendWaDirect(this.waStudentId, this.waRecipientPhone, this.waMessage, this.waStatusType)
            .catch(err => alert('Terjadi kesalahan: ' + (err?.message || err)))
            .finally(() => { this.isSending = false; });
    },
    openWhatsAppWeb() {
        if (!this.waRecipientPhone.trim()) { alert('Silakan masukkan nomor WhatsApp tujuan!'); return; }
        if (!this.waMessage.trim()) { alert('Isi pesan tidak boleh kosong!'); return; }
        let cleanPhone = this.waRecipientPhone.replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
        else if (cleanPhone.startsWith('8')) cleanPhone = '62' + cleanPhone;
        window.open('https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(this.waMessage), '_blank');
    }
}" @wa-modal-close.window="showWaModal = false">
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
                                    wire:click="openWaPertemuanModal('{{ $p->id }}')"
                                    wire:loading.attr="disabled"
                                    title="Lihat daftar anggota belum hadir &amp; kirim WA Orang Tua (WA Web / Gateway)"
                                    class="ml-auto inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors cursor-pointer">
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

    <!-- Pop-Up Modal Daftar Belum Hadir & Kirim WA Pertemuan Ekskul -->
    @if($showWaPertemuanModal && $waPertemuanInfo)
        <div class="fixed inset-0 z-40 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeWaPertemuanModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-200">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 px-6 py-4 flex items-center justify-between text-white">
                        <div>
                            <h3 class="text-base font-black leading-tight">
                                Daftar Belum Hadir &amp; Kirim WA — {{ $waPertemuanInfo['ekskul_name'] }}
                            </h3>
                            <p class="text-xs text-emerald-100 font-medium mt-0.5">
                                Pertemuan ke-{{ $waPertemuanInfo['pertemuan_ke'] }} &bull; {{ $waPertemuanInfo['tanggal_label'] }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeWaPertemuanModal"
                                class="text-white/80 hover:text-white p-1.5 rounded-xl hover:bg-white/10 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body: Daftar Siswa Belum Hadir / Alpa -->
                    <div class="p-5 sm:p-6 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3">
                            <div>
                                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] block">Materi Pertemuan</span>
                                <span class="text-slate-800 font-bold">{{ $waPertemuanInfo['judul_kegiatan'] }}</span>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                {{ count($waPertemuanStudents) }} Siswa Belum Hadir / Alpa
                            </span>
                        </div>

                        @if(count($waPertemuanStudents) === 0)
                            <div class="p-6 text-center text-xs font-bold text-slate-500 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                Semua anggota aktif sudah tercatat hadir / izin / sakit pada pertemuan ini.
                            </div>
                        @else
                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 border border-slate-200 rounded-2xl">
                                @foreach($waPertemuanStudents as $st)
                                    <div class="p-3.5 sm:px-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition-colors">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-extrabold text-sm text-slate-900">{{ $st['name'] }}</span>
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $st['status_key'] === 'alpa' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $st['status_key'] === 'alpa' ? 'Alpa' : 'Belum Absen' }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-0.5">
                                                Kelas <strong class="text-slate-700">{{ $st['kelas'] }}</strong> &bull;
                                                NISN: {{ $st['nisn'] }} &bull;
                                                WA Ortu: <span class="font-mono font-semibold text-slate-700">{{ $st['clean_phone'] ?: ($st['phone'] ?: 'Belum ada nomor') }}</span>
                                            </div>
                                        </div>

                                        @php
                                            $waRowData = [
                                                'id'          => $st['id'],
                                                'name'        => $st['name'],
                                                'className'   => $st['kelas'],
                                                'nisn'        => $st['nisn'],
                                                'phone'       => $st['phone'],
                                                'cleanPhone'  => $st['clean_phone'],
                                                'rawText'     => $st['wa_text_raw'],
                                                'type'        => 'ekstrakurikuler',
                                                'statusLabel' => $st['status_label'],
                                            ];
                                        @endphp
                                        <button type="button"
                                                x-on:click="openWaModal({{ \Illuminate\Support\Js::from($waRowData) }})"
                                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors cursor-pointer shrink-0">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            <span>WA Ortu</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(!$waEkskulActive)
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 font-semibold">
                                Notifikasi WhatsApp Ekstrakurikuler di <strong>Portal Sistem</strong> sedang nonaktif. Gunakan tombol <strong>WA Ortu &rarr; Buka di WA (Web / HP)</strong> untuk mengirim manual tanpa gateway, atau aktifkan toggle di Portal Sistem untuk Broadcast Semua.
                            </div>
                        @elseif(!$canSendManualToday)
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-semibold">
                                Batas kirim broadcast massal harian ({{ $manualSentCount }}/5) telah tercapai. Anda masih dapat mengirim manual per siswa via <strong>WA Ortu</strong>.
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2.5">
                        <button type="button" wire:click="closeWaPertemuanModal"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200/70 transition-colors cursor-pointer">
                            Tutup
                        </button>

                        <button type="button"
                                wire:click="kirimWaBelumHadir('{{ $waPertemuanInfo['pertemuan_id'] }}')"
                                wire:loading.attr="disabled"
                                @disabled(!$waEkskulActive || !$canSendManualToday || count($waPertemuanStudents) === 0)
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-45 disabled:cursor-not-allowed shadow-sm transition-colors cursor-pointer">
                            <span>Kirim Broadcast Semua via Gateway ({{ count($waPertemuanStudents) }})</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-portal.wa-ortu-modal
        title="Kirim Pesan WhatsApp Ekstrakurikuler"
        subtitle="Kirim via WhatsApp Web/HP atau langsung lewat gateway aplikasi"
        statusHeading="Status Kehadiran"
        templateNote="Pesan di atas diambil otomatis dari template notifikasi WA Ekstrakurikuler sekolah."
        :canSendGateway="$waEkskulActive"
    />
</div>

