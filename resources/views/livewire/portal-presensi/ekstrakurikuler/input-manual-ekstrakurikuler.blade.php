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
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Input Manual Presensi Ekstrakurikuler</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Catat atau koreksi kehadiran anggota ekstrakurikuler. Siswa yang sudah scan tetap mempertahankan waktu scan kiosk-nya.</p>
        </div>
        <a href="{{ route('portal-presensi.ekstrakurikuler.dashboard') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs">
            &larr; Kembali ke Dashboard Ekskul
        </a>
    </div>

    <!-- Filter Panel -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Tahun Ajaran</label>
                <select wire:model.live="selectedAcademicYearId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}">TA {{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Pilih Ekstrakurikuler</label>
                <select wire:model.live="selectedEkskulId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20">
                    @forelse($ekskuls as $eks)
                        <option value="{{ $eks->id }}">{{ $eks->nama_ekskul }}</option>
                    @empty
                        <option value="">-- Tidak ada ekskul aktif --</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Tanggal Pertemuan</label>
                <x-portal-presensi.date-indo
                    model="inputDate"
                    :value="$inputDate"
                    :live="true"
                    placeholder="Pilih tanggal"
                    buttonClass="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-sm bg-slate-50 rounded-xl border border-slate-200 font-bold text-slate-800 hover:bg-slate-100 outline-none cursor-pointer"
                />
            </div>
        </div>

        @if($selectedEkskul)
            <div class="pt-3 border-t border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">
                        Judul / Materi Kegiatan
                        @if($existingPertemuanKe)
                            <span class="ml-1.5 px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-brand-primary/10 text-brand-primary">Pertemuan ke-{{ $existingPertemuanKe }}</span>
                        @else
                            <span class="ml-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pertemuan baru otomatis dibuat saat disimpan</span>
                        @endif
                    </label>
                    <input type="text" wire:model="judulKegiatan" placeholder="Misal: PBB Dasar, Latihan Tanding..."
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-medium focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Status Sesi</label>
                    <select wire:model="statusSesi" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-brand-primary">
                        <option value="aktif">Sesi Aktif</option>
                        <option value="selesai">Sesi Selesai</option>
                    </select>
                </div>
            </div>
        @endif
    </div>

    <!-- Tabel Anggota -->
    @if($selectedEkskul)
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 bg-slate-50/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-800">Daftar Anggota Aktif: {{ $selectedEkskul->nama_ekskul }} ({{ count($presensiRows) }})</h2>
                    <p class="text-[11px] text-slate-500">Pilih status kehadiran lalu tekan Simpan Presensi di bagian bawah.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="markUnfilledAs('hadir')" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold cursor-pointer">
                        Belum Diisi = Hadir
                    </button>
                    <button type="button" wire:click="markUnfilledAs('izin')" class="px-3 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-xs font-bold cursor-pointer">
                        Belum Diisi = Izin
                    </button>
                    <button type="button" wire:click="markUnfilledAs('alpa')" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold cursor-pointer">
                        Belum Diisi = Alpa
                    </button>
                </div>
            </div>

            @if(count($presensiRows) === 0)
                <div class="p-10 text-center text-xs font-semibold text-slate-400">
                    Belum ada anggota aktif terdaftar pada {{ $selectedEkskul->nama_ekskul }} (kelola di Portal Akademik).
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-5 py-3 w-12 text-center">No</th>
                                <th class="px-5 py-3">Siswa</th>
                                <th class="px-3 py-3 text-center">Kelas</th>
                                <th class="px-3 py-3 text-center">Sumber / Jam</th>
                                <th class="px-3 py-3 text-center">Status Kehadiran</th>
                                <th class="px-5 py-3">Keterangan</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($presensiRows as $idx => $row)
                                <tr wire:key="pp-prow-{{ $row['student_id'] }}" class="hover:bg-slate-50">
                                    <td class="px-5 py-3 text-center text-xs font-bold text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="px-5 py-3">
                                        <div class="font-bold text-slate-800">{{ $row['name'] }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $row['nisn'] }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $row['kelas'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center text-xs">
                                        @if($row['is_manual_input'] === false && $row['scan_time'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Scan {{ $row['scan_time'] }} WIB
                                            </span>
                                        @elseif($row['scan_time'])
                                            <span class="text-xs font-mono text-slate-500">{{ $row['scan_time'] }} WIB</span>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <select wire:model="presensiRows.{{ $idx }}.status" class="rounded-lg border border-slate-300 text-xs font-bold px-3 py-1.5 outline-none focus:ring-2 focus:ring-brand-primary/20
                                            {{ $row['status'] === 'hadir' ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : '' }}
                                            {{ $row['status'] === 'izin' ? 'bg-sky-50 border-sky-300 text-sky-800' : '' }}
                                            {{ $row['status'] === 'sakit' ? 'bg-indigo-50 border-indigo-300 text-indigo-800' : '' }}
                                            {{ $row['status'] === 'alpa' ? 'bg-rose-50 border-rose-300 text-rose-800' : '' }}
                                        ">
                                            <option value="">-- Belum Diisi --</option>
                                            <option value="hadir">Hadir</option>
                                            <option value="izin">Izin</option>
                                            <option value="sakit">Sakit</option>
                                            <option value="alpa">Alpa</option>
                                        </select>
                                    </td>
                                    <td class="px-5 py-3">
                                        <input type="text" wire:model="presensiRows.{{ $idx }}.keterangan" placeholder="Keterangan izin/sakit/catatan..."
                                            class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs focus:border-brand-primary outline-none">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if(empty($row['status']) || $row['status'] === 'alpa')
                                            <button type="button"
                                                @click="openWaModal({
                                                    id: '{{ $row['student_id'] }}',
                                                    name: '{{ addslashes($row['name']) }}',
                                                    className: '{{ addslashes($row['kelas']) }}',
                                                    nisn: '{{ $row['nisn'] }}',
                                                    cleanPhone: '{{ $row['clean_phone'] ?? '' }}',
                                                    phone: '{{ $row['raw_phone'] ?? '' }}',
                                                    type: 'ekstrakurikuler',
                                                    statusLabel: '{{ ($row['status'] ?? '') === 'alpa' ? 'Alpa Ekskul' : 'Belum Absen Ekskul' }}',
                                                    rawText: {{ json_encode($row['wa_text_raw'] ?? '') }}
                                                })"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-xs font-bold transition-all shadow-2xs cursor-pointer whitespace-nowrap"
                                                title="Kirim Pesan WhatsApp ke Orang Tua">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                                <span>WA Ortu</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
                    <span class="text-xs text-slate-500">Siswa yang sudah scan tetap mempertahankan jam scan kiosk-nya.</span>
                    <button type="button" wire:click="savePresensi" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-primary hover:opacity-95 text-white text-xs font-extrabold shadow-sm transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>Simpan Presensi Ekstrakurikuler</span>
                    </button>
                </div>
            @endif
        </div>
    @endif

    <x-portal.wa-ortu-modal
        title="Kirim Pesan WhatsApp Ekstrakurikuler"
        subtitle="Kirim via WhatsApp Web/HP atau langsung lewat gateway aplikasi"
        statusHeading="Status Ekskul"
        :canSendGateway="$isNotifEkskulActive ?? false"
        :templateNote="'Pesan di atas diambil otomatis dari template notifikasi ekstrakurikuler ' . (\App\Models\PengaturanSekolah::current()?->school_name ?? 'Sekolah') . '.'"
    />
</div>

