<div class="p-4 lg:p-6 space-y-6 max-w-7xl mx-auto pb-16 font-jakarta">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Rekap &amp; Cetak Laporan Ekstrakurikuler</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Lihat matriks kehadiran seluruh anggota ekstrakurikuler lintas pertemuan serta ekspor ke Excel atau PDF.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($selectedEkskul)
                <button type="button" wire:click="downloadExcel" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 shadow-xs cursor-pointer">
                    <span>Export Excel (.xlsx)</span>
                </button>
                <button type="button" wire:click="downloadPdf" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-brand-primary hover:opacity-95 shadow-xs cursor-pointer">
                    <span>Cetak Laporan (PDF)</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Tahun Ajaran</label>
            <select wire:model.live="selectedAcademicYearId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-brand-primary">
                @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}">TA {{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Pilih Ekstrakurikuler</label>
            <select wire:model.live="selectedEkskulId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-brand-primary">
                @forelse($ekskuls as $eks)
                    <option value="{{ $eks->id }}">{{ $eks->nama_ekskul }} (Pembina: {{ $eks->pembinas->pluck('name')->implode(', ') ?: '-' }})</option>
                @empty
                    <option value="">-- Tidak ada ekstrakurikuler --</option>
                @endforelse
            </select>
        </div>
    </div>

    @if($selectedEkskul)
        <!-- Ringkasan -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-[11px] font-bold text-slate-500 uppercase">Anggota</div>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $reportSummary['total_anggota'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-[11px] font-bold text-slate-500 uppercase">Pertemuan</div>
                <div class="text-2xl font-black text-brand-primary mt-0.5">{{ $reportSummary['total_pertemuan'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-[11px] font-bold text-emerald-600 uppercase">Total Hadir</div>
                <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $reportSummary['total_hadir'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-[11px] font-bold text-sky-600 uppercase">Izin</div>
                <div class="text-2xl font-black text-sky-600 mt-0.5">{{ $reportSummary['total_izin'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-[11px] font-bold text-indigo-600 uppercase">Sakit</div>
                <div class="text-2xl font-black text-indigo-600 mt-0.5">{{ $reportSummary['total_sakit'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-[11px] font-bold text-rose-600 uppercase">Alpa</div>
                <div class="text-2xl font-black text-rose-600 mt-0.5">{{ $reportSummary['total_alpa'] ?? 0 }}</div>
            </div>
        </div>

        <!-- Matriks Kehadiran -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-black text-slate-800">Matriks Kehadiran: {{ $selectedEkskul->nama_ekskul }} ({{ count($reportMeetings) }} Pertemuan)</h3>
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
                                <th class="px-2.5 py-3 text-center whitespace-nowrap" title="{{ $rm['tanggal_label'] }} — {{ $rm['judul_kegiatan'] }}">
                                    Ke-{{ $rm['pertemuan_ke'] }}
                                </th>
                            @endforeach
                            <th class="px-2.5 py-3 text-center text-emerald-700">H</th>
                            <th class="px-2.5 py-3 text-center text-sky-700">I</th>
                            <th class="px-2.5 py-3 text-center text-indigo-700">S</th>
                            <th class="px-2.5 py-3 text-center text-rose-700">A</th>
                            <th class="px-4 py-3 text-center">% Hadir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reportMembers as $i => $m)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-center text-xs font-bold text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-800">{{ $m['name'] }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $m['nisn'] }}</div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $m['kelas'] }}</span>
                                </td>
                                @foreach($reportMeetings as $rm)
                                    @php $code = $m['per_meeting'][$rm['id']] ?? '-'; @endphp
                                    <td class="px-2.5 py-3 text-center text-xs font-extrabold
                                        {{ $code === 'H' ? 'text-emerald-600' : '' }}
                                        {{ $code === 'I' ? 'text-sky-600' : '' }}
                                        {{ $code === 'S' ? 'text-indigo-600' : '' }}
                                        {{ $code === 'A' ? 'text-rose-600' : '' }}
                                        {{ $code === '-' ? 'text-slate-300' : '' }}
                                    ">{{ $code }}</td>
                                @endforeach
                                <td class="px-2.5 py-3 text-center text-xs font-black text-emerald-700">{{ $m['hadir'] }}</td>
                                <td class="px-2.5 py-3 text-center text-xs font-bold text-sky-700">{{ $m['izin'] }}</td>
                                <td class="px-2.5 py-3 text-center text-xs font-bold text-indigo-700">{{ $m['sakit'] }}</td>
                                <td class="px-2.5 py-3 text-center text-xs font-bold text-rose-700">{{ $m['alpa'] }}</td>
                                <td class="px-4 py-3 text-center text-xs font-black text-slate-800">{{ $m['pct'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 8 + count($reportMeetings) }}" class="px-6 py-10 text-center text-xs font-semibold text-slate-400">
                                    Belum ada anggota aktif terdaftar di ekstrakurikuler ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

