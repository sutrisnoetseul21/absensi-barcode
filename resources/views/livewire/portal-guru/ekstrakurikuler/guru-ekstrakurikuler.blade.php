<div class="space-y-6 pb-10">
    @if(count($ekskuls) === 0 || !$selectedEkskul)
        <!-- Header & Empty State -->
        <div class="bg-gradient-to-br from-brand-primary via-indigo-700 to-violet-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/15 text-white mb-2">
                        Ekstrakurikuler
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Ekstrakurikuler Binaan</h1>
                </div>
                <div class="w-full sm:w-52">
                    <select wire:model.live="selectedAcademicYearId" class="w-full rounded-xl border border-white/25 bg-white/15 text-white text-sm font-bold px-3.5 py-2 outline-none">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" class="text-slate-900">TA {{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-extrabold text-slate-800">Belum Ada Penugasan Pembina</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Anda belum ditugaskan sebagai Guru Pembina pada ekstrakurikuler mana pun di Tahun Ajaran yang dipilih.</p>
        </div>
    @else
        <!-- Hero Banner Header ala Jurnal Mengajar & Analisa KBM -->
        <div class="bg-gradient-to-br from-brand-primary via-sky-700 to-blue-800 rounded-3xl p-6 sm:p-7 text-white shadow-xl space-y-5"
             x-data="{ copied: false, copyUrl(url) { navigator.clipboard.writeText(url); this.copied = true; setTimeout(() => this.copied = false, 2500); } }">
            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-white/15 border border-white/20">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            TA {{ $selectedEkskul->tahunAjaran?->name }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/15 border border-white/20">
                            Pembina: {{ $selectedEkskul->pembinas->pluck('name')->implode(', ') ?: '-' }}
                        </span>
                        @if(count($ekskuls) > 1)
                            <select wire:change="selectEkskul($event.target.value)" class="rounded-full border border-white/30 bg-white/15 text-white text-xs font-bold px-3 py-1 outline-none cursor-pointer">
                                @foreach($ekskuls as $eksOpt)
                                    <option value="{{ $eksOpt->id }}" class="text-slate-900" {{ $eksOpt->id === $selectedEkskul->id ? 'selected' : '' }}>
                                        Pindah: {{ $eksOpt->nama_ekskul }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                        Ekstrakurikuler {{ $selectedEkskul->nama_ekskul }}
                    </h1>

                    @if($selectedEkskul->keterangan || $selectedEkskul->slug)
                        <p class="text-xs text-sky-100/90 font-medium">
                            @if($selectedEkskul->keterangan)
                                {{ $selectedEkskul->keterangan }}
                            @endif
                            @if($selectedEkskul->keterangan && $selectedEkskul->slug)
                                &bull;
                            @endif
                            @if($selectedEkskul->slug)
                                <span class="font-mono text-sky-200">{{ route('kiosk.scan-ekstrakurikuler', ['slug' => $selectedEkskul->slug]) }}</span>
                            @endif
                        </p>
                    @endif
                </div>

                <!-- Action Buttons Top Right -->
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if($selectedEkskul->slug && $selectedEkskul->status_aktif)
                        <a href="{{ route('kiosk.scan-ekstrakurikuler', ['slug' => $selectedEkskul->slug]) }}"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-extrabold shadow-md transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            <span>Buka Scanner</span>
                        </a>

                        <button type="button"
                                @click="copyUrl('{{ route('kiosk.scan-ekstrakurikuler', ['slug' => $selectedEkskul->slug]) }}')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white border border-white/25 text-xs font-bold transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            <span x-show="!copied">Salin Link</span>
                            <span x-show="copied">Tersalin!</span>
                        </button>
                    @endif

                    <button type="button" wire:click="openCreatePertemuanModal"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white text-brand-primary hover:bg-sky-50 text-xs font-extrabold shadow-md transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        <span>+ Pertemuan Baru</span>
                    </button>

                    <button type="button" wire:click="downloadExcel" class="inline-flex items-center gap-1 px-3 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white border border-white/25 text-xs font-bold transition-all cursor-pointer">
                        <span>Excel</span>
                    </button>
                    <button type="button" wire:click="downloadPdf" class="inline-flex items-center gap-1 px-3 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white border border-white/25 text-xs font-bold transition-all cursor-pointer">
                        <span>PDF</span>
                    </button>
                </div>
            </div>

            <!-- 3 Tab Pill Bar di dalam Hero Banner -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 bg-white/10 p-1.5 rounded-2xl border border-white/15 backdrop-blur-sm">
                <button type="button" wire:click="$set('activeTab', 'presensi')"
                    class="py-2.5 px-4 rounded-xl text-xs font-extrabold transition-all cursor-pointer flex items-center justify-center gap-2 {{ $activeTab === 'presensi' ? 'bg-white text-brand-primary shadow-md' : 'text-white/90 hover:bg-white/10' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Presensi Pertemuan ({{ count($presensiRows) }})</span>
                </button>
                <button type="button" wire:click="$set('activeTab', 'jurnal')"
                    class="py-2.5 px-4 rounded-xl text-xs font-extrabold transition-all cursor-pointer flex items-center justify-center gap-2 {{ $activeTab === 'jurnal' ? 'bg-white text-brand-primary shadow-md' : 'text-white/90 hover:bg-white/10' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    <span>Jurnal Kegiatan ({{ count($pertemuans) }})</span>
                </button>
                <button type="button" wire:click="$set('activeTab', 'anggota')"
                    class="py-2.5 px-4 rounded-xl text-xs font-extrabold transition-all cursor-pointer flex items-center justify-center gap-2 {{ $activeTab === 'anggota' ? 'bg-white text-brand-primary shadow-md' : 'text-white/90 hover:bg-white/10' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span>Anggota &amp; Rekap ({{ count($memberStats) }})</span>
                </button>
            </div>
        </div>

        <!-- TAB 1: PRESENSI PERTEMUAN -->
        @if($activeTab === 'presensi')
            @if(count($pertemuans) === 0)
                <div class="bg-white rounded-3xl border border-slate-200 p-10 text-center shadow-sm space-y-4">
                    <h3 class="text-base font-extrabold text-slate-800">Belum Ada Pertemuan Tercatat</h3>
                    <p class="text-xs text-slate-500 max-w-lg mx-auto leading-relaxed">
                        Pertemuan otomatis dibuat saat ada siswa yang scan barcode di Kiosk Scanner, atau klik <strong>+ Pertemuan Baru</strong> untuk membuat sesi dan mencatat kehadiran secara manual.
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        @if($selectedEkskul->slug && $selectedEkskul->status_aktif)
                            <a href="{{ route('kiosk.scan-ekstrakurikuler', ['slug' => $selectedEkskul->slug]) }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-xs">
                                Buka Scanner Barcode
                            </a>
                        @endif
                        <button type="button" wire:click="openCreatePertemuanModal" class="px-4 py-2 rounded-xl bg-brand-primary text-white text-xs font-bold shadow-xs cursor-pointer">
                            + Pertemuan Baru
                        </button>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Control Bar Pilih Pertemuan + Aksi Massal -->
                    <div class="p-5 bg-slate-50/80 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                            <label class="text-xs font-extrabold uppercase tracking-wider text-slate-500 shrink-0">Pilih Pertemuan:</label>
                            <select wire:model.live="selectedPertemuanId" class="w-full sm:max-w-md rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-bold text-slate-800 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 outline-none">
                                @foreach($pertemuans as $pOpt)
                                    <option value="{{ $pOpt->id }}">
                                        Pertemuan ke-{{ $pOpt->pertemuan_ke }} — {{ $pOpt->tanggal?->locale('id')->translatedFormat('l, d M Y') }} ({{ $pOpt->judul_kegiatan ?: 'Tanpa judul' }})
                                    </option>
                                @endforeach
                            </select>
                            @if($selectedPertemuanCard)
                                <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-bold">
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">H: {{ $selectedPertemuanCard['hadir_count'] }}</span>
                                    <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200">I: {{ $selectedPertemuanCard['izin_count'] }}</span>
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">S: {{ $selectedPertemuanCard['sakit_count'] }}</span>
                                    <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200">A: {{ $selectedPertemuanCard['alpa_count'] }}</span>
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">Belum: {{ $selectedPertemuanCard['belum_count'] }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
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

                    <!-- Tabel Kehadiran Anggota -->
                    @if(count($presensiRows) === 0)
                        <div class="p-10 text-center text-xs font-semibold text-slate-400">
                            Belum ada anggota aktif terdaftar di {{ $selectedEkskul->nama_ekskul }} (kelola di Portal Akademik).
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
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($presensiRows as $idx => $row)
                                        <tr wire:key="prow-{{ $row['student_id'] }}" class="hover:bg-slate-50">
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
                                                <input type="text" wire:model="presensiRows.{{ $idx }}.keterangan" placeholder="Keterangan izin/sakit/catatan..." class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs focus:border-brand-primary outline-none">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
                            <span class="text-xs text-slate-500">Siswa yang sudah scan tetap mempertahankan jam scan kiosk-nya.</span>
                            <button type="button" wire:click="saveMeetingPresensi" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white text-xs font-extrabold shadow-sm transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Simpan Presensi Pertemuan</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endif

        <!-- TAB 2: JURNAL KEGIATAN -->
        @elseif($activeTab === 'jurnal')
            @if(count($pertemuans) === 0)
                <div class="bg-white rounded-3xl border border-slate-200 p-10 text-center shadow-sm space-y-3">
                    <h3 class="text-base font-extrabold text-slate-800">Belum Ada Jurnal Kegiatan</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">Klik tombol <strong>+ Pertemuan Baru</strong> untuk mencatat kegiatan latihan beserta tanggal dan materinya.</p>
                    <button type="button" wire:click="openCreatePertemuanModal" class="px-4 py-2 rounded-xl bg-brand-primary text-white text-xs font-bold cursor-pointer">
                        + Pertemuan Baru
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- Daftar Pertemuan (Kiri / Penuh bila jurnal belum dibuka) -->
                    <div class="{{ $activeJurnalCard ? 'lg:col-span-5' : 'lg:col-span-12' }} bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Daftar Pertemuan</h3>
                                <p class="text-[11px] text-slate-400">Klik pertemuan untuk membuka &amp; mengedit jurnal materi serta catatan</p>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-[540px] overflow-y-auto">
                            @foreach($pertemuanCards as $p)
                                @php $isP = $activeJurnalPertemuanId === $p['id']; @endphp
                                <div class="p-4 transition-colors {{ $isP ? 'bg-indigo-50/70 border-l-4 border-brand-primary' : 'hover:bg-slate-50' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <button type="button" wire:click="openJurnalPertemuan('{{ $p['id'] }}')" class="text-left flex-1 cursor-pointer">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-md text-[11px] font-black {{ $isP ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-700' }}">
                                                    Pertemuan ke-{{ $p['pertemuan_ke'] }}
                                                </span>
                                                <span class="text-[11px] font-bold {{ $p['status_sesi'] === 'selesai' ? 'text-emerald-600' : 'text-amber-600' }}">
                                                    {{ $p['status_sesi'] === 'selesai' ? 'Selesai' : 'Aktif' }}
                                                </span>
                                            </div>
                                            <p class="text-xs font-extrabold text-slate-800 mt-1.5">
                                                {{ $p['tanggal_label'] }}
                                            </p>
                                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">
                                                {{ $p['judul_kegiatan'] ?: 'Belum ada judul kegiatan' }}
                                            </p>
                                            <div class="flex flex-wrap gap-2 mt-2 text-[10px] font-bold">
                                                <span class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">H: {{ $p['hadir_count'] }}</span>
                                                <span class="text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded">I: {{ $p['izin_count'] }}</span>
                                                <span class="text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded">S: {{ $p['sakit_count'] }}</span>
                                                <span class="text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded">A: {{ $p['alpa_count'] }}</span>
                                                <span class="text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">Belum Absen: {{ $p['belum_count'] }}</span>
                                            </div>
                                        </button>
                                        <button type="button"
                                                wire:click="deletePertemuan('{{ $p['id'] }}')"
                                                wire:confirm="Hapus Pertemuan ke-{{ $p['pertemuan_ke'] }} beserta seluruh data kehadirannya?"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Hapus Pertemuan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Form Edit Jurnal Pertemuan (Kanan, hanya muncul jika pertemuan diklik) -->
                    @if($activeJurnalCard)
                        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
                                <div>
                                    <h3 class="text-base font-black text-slate-900">Jurnal Pertemuan ke-{{ $activeJurnalCard['pertemuan_ke'] }}</h3>
                                    <p class="text-xs text-slate-500">{{ $activeJurnalCard['tanggal_label'] }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model="statusSesi" class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700">
                                        <option value="aktif">Sesi Aktif</option>
                                        <option value="selesai">Sesi Selesai</option>
                                    </select>
                                    <button type="button" wire:click="closeJurnalPertemuan"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-bold transition-colors cursor-pointer"
                                            title="Tutup panel jurnal">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        <span>Tutup</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Judul / Materi Kegiatan</label>
                                <input type="text" wire:model="judulKegiatan" placeholder="Misal: PBB Dasar, Latihan Tanding, Materi Tali Temali..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-medium focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pembina (Opsional)</label>
                                <textarea wire:model="catatanPembina" rows="4" placeholder="Evaluasi latihan atau catatan khusus pertemuan ini..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-medium focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 outline-none"></textarea>
                            </div>
                            <div class="pt-2 flex items-center justify-between">
                                <button type="button" wire:click="$set('activeTab', 'presensi')" class="text-xs font-bold text-brand-primary hover:underline cursor-pointer">
                                    Isi Presensi Pertemuan Ini &rarr;
                                </button>
                                <button type="button" wire:click="savePertemuanJurnal" class="px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white text-xs font-extrabold shadow-sm transition-colors cursor-pointer">
                                    Simpan Jurnal Pertemuan
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        <!-- TAB 3: ANGGOTA & REKAP -->
        @else
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-black text-slate-800">Matriks &amp; Rekap Kehadiran ({{ count($pertemuans) }} Pertemuan)</h3>
                    <span class="text-xs text-slate-500">H = Hadir &bull; I = Izin &bull; S = Sakit &bull; A = Alpa</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-5 py-3">Nama Siswa</th>
                                <th class="px-3 py-3 text-center">Kelas</th>
                                @foreach($reportMeetings as $rm)
                                    <th class="px-2.5 py-3 text-center whitespace-nowrap" title="{{ $rm['tanggal_label'] }}">
                                        <div>P{{ $rm['pertemuan_ke'] }}</div>
                                        <div class="text-[10px] font-normal text-slate-400">{{ $rm['tanggal_short'] }}</div>
                                    </th>
                                @endforeach
                                <th class="px-3 py-3 text-center text-emerald-700 border-l border-slate-200">H</th>
                                <th class="px-3 py-3 text-center text-sky-700">I</th>
                                <th class="px-3 py-3 text-center text-indigo-700">S</th>
                                <th class="px-3 py-3 text-center text-rose-700">A</th>
                                <th class="px-4 py-3 text-center">% Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($reportMembers as $ms)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-center text-xs font-bold text-slate-400">{{ $ms['no'] }}</td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $ms['name'] }}</div>
                                        <div class="text-[10px] font-mono text-slate-400">{{ $ms['nisn'] }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-center text-xs font-bold text-slate-600">{{ $ms['kelas'] }}</td>
                                    @foreach($reportMeetings as $rm)
                                        @php
                                            $c = $ms['daily'][$rm['id']] ?? '-';
                                            $cCls = match($c) {
                                                'H' => 'text-emerald-700 bg-emerald-50',
                                                'I' => 'text-sky-700 bg-sky-50',
                                                'S' => 'text-indigo-700 bg-indigo-50',
                                                'A' => 'text-rose-700 bg-rose-50',
                                                default => 'text-slate-300',
                                            };
                                        @endphp
                                        <td class="px-2.5 py-3 text-center text-xs font-black {{ $cCls }}">{{ $c }}</td>
                                    @endforeach
                                    <td class="px-3 py-3 text-center font-black text-emerald-600 border-l border-slate-200">{{ $ms['hadir'] }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-sky-600">{{ $ms['izin'] }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-indigo-600">{{ $ms['sakit'] }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-rose-600">{{ $ms['alpa'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black {{ $ms['pct'] >= 75 ? 'bg-emerald-100 text-emerald-800' : ($ms['pct'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $ms['pct'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 8 + count($reportMeetings) }}" class="py-10 text-center text-xs text-slate-400 font-semibold">Belum ada anggota aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    <!-- Modal Tambah Pertemuan Baru -->
    @if($showCreatePertemuanModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
                <div class="bg-gradient-to-r from-brand-primary to-brand-secondary px-6 py-4 flex items-center justify-between text-white">
                    <h3 class="text-base font-extrabold">Buat Pertemuan Ekstrakurikuler</h3>
                    <button type="button" wire:click="$set('showCreatePertemuanModal', false)" class="p-1 rounded-full hover:bg-white/20 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Latihan / Pertemuan</label>
                        <input type="date" wire:model="newTanggal" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm font-semibold">
                        @error('newTanggal') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul / Materi Kegiatan</label>
                        <input type="text" wire:model="newJudul" placeholder="Contoh: Latihan PBB, Pembinaan Lomba..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pembina (Opsional)</label>
                        <textarea wire:model="newCatatan" rows="2" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showCreatePertemuanModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 cursor-pointer">Batal</button>
                    <button type="button" wire:click="createPertemuan" class="px-5 py-2 rounded-xl bg-brand-primary text-white text-xs font-bold cursor-pointer">Simpan Pertemuan</button>
                </div>
            </div>
        </div>
    @endif
</div>
