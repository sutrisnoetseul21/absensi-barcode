<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Tahun Ajaran</label>
                    <select wire:model.live="selectedAcademicYearId" class="w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm font-semibold">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}">TA {{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Dari Tanggal (Opsional)</label>
                    <input type="date" wire:model.live="startDate" class="w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Sampai Tanggal (Opsional)</label>
                    <input type="date" wire:model.live="endDate" class="w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm font-semibold">
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="resetDateFilter" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 cursor-pointer">
                        Reset Tanggal
                    </button>
                </div>
            </div>
        </div>

        <!-- Ringkasan KPI -->
        @php $t = $overview['totals']; @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Ekstrakurikuler</span>
                <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $t['total_ekskul'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Keanggotaan Aktif</span>
                <div class="text-2xl font-black text-primary-600 mt-1">{{ $t['total_anggota'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pertemuan Terlaksana</span>
                <div class="text-2xl font-black text-success-600 mt-1">{{ $t['total_pertemuan'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Rata-rata Kehadiran</span>
                <div class="text-2xl font-black text-info-600 mt-1">{{ $t['avg_pct'] }}%</div>
            </div>
        </div>

        <!-- Tabel Monitoring Ekstrakurikuler -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Daftar Monitoring Ekstrakurikuler</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th class="px-5 py-3">Ekstrakurikuler</th>
                            <th class="px-4 py-3">Guru Pembina</th>
                            <th class="px-3 py-3 text-center">Anggota</th>
                            <th class="px-3 py-3 text-center">Pertemuan</th>
                            <th class="px-4 py-3">Pertemuan Terakhir</th>
                            <th class="px-3 py-3 text-center">H / I / S / A</th>
                            <th class="px-3 py-3 text-center">% Hadir</th>
                            <th class="px-5 py-3 text-right">Aksi &amp; Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($overview['rows'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $row['nama_ekskul'] }}</div>
                                    <span class="text-[11px] font-semibold {{ $row['status_aktif'] ? 'text-success-600' : 'text-gray-400' }}">
                                        {{ $row['status_aktif'] ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300">{{ $row['pembinas'] }}</td>
                                <td class="px-3 py-3.5 text-center font-bold">{{ $row['anggota_count'] }}</td>
                                <td class="px-3 py-3.5 text-center font-bold text-primary-600">{{ $row['pertemuan_count'] }}</td>
                                <td class="px-4 py-3.5 text-xs">
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $row['latest_tanggal'] }}</div>
                                    <div class="text-[11px] text-gray-500 line-clamp-1">{{ $row['latest_judul'] }}</div>
                                </td>
                                <td class="px-3 py-3.5 text-center text-xs font-mono">
                                    <span class="text-success-600 font-bold">{{ $row['hadir'] }}</span> /
                                    <span class="text-info-600 font-bold">{{ $row['izin'] }}</span> /
                                    <span class="text-warning-600 font-bold">{{ $row['sakit'] }}</span> /
                                    <span class="text-danger-600 font-bold">{{ $row['alpa'] }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $row['avg_pct'] >= 75 ? 'bg-success-50 text-success-700' : ($row['avg_pct'] >= 50 ? 'bg-warning-50 text-warning-700' : 'bg-danger-50 text-danger-700') }}">
                                        {{ $row['avg_pct'] }}%
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-1.5 flex-wrap justify-end">
                                        <button type="button" wire:click="inspectEkskul('{{ $row['id'] }}')" class="px-2.5 py-1 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold cursor-pointer">
                                            Detail
                                        </button>
                                        <button type="button" wire:click="downloadExcel('{{ $row['id'] }}')" class="px-2.5 py-1 rounded-lg bg-success-600 hover:bg-success-500 text-white text-xs font-bold cursor-pointer">
                                            Excel
                                        </button>
                                        <button type="button" wire:click="downloadPdf('{{ $row['id'] }}')" class="px-2.5 py-1 rounded-lg bg-danger-600 hover:bg-danger-500 text-white text-xs font-bold cursor-pointer">
                                            PDF
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-xs font-semibold text-gray-400">Belum ada data ekstrakurikuler pada tahun ajaran ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detail Ekstrakurikuler & Jurnal -->
        @if($detailData)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-primary-300 dark:border-primary-500/40 shadow-md p-6 space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 dark:border-white/10 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white">
                            Detail Pertemuan &amp; Kehadiran: {{ $detailData['ekskul']->nama_ekskul }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Pembina: {{ $detailData['ekskul']->pembinas->pluck('name')->implode(', ') ?: '-' }}
                            &bull; Anggota Aktif: {{ $detailData['summary']['total_anggota'] }}
                            &bull; Total Pertemuan: {{ $detailData['summary']['total_pertemuan'] }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-white/10 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 cursor-pointer">
                        Tutup Detail
                    </button>
                </div>

                <!-- Jurnal Pertemuan -->
                <div>
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">Riwayat Jurnal Pertemuan Pembina</h4>
                    <div class="overflow-x-auto border border-gray-200 dark:border-white/10 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-white/5 font-bold uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-2.5">Pertemuan</th>
                                    <th class="px-4 py-2.5">Tanggal</th>
                                    <th class="px-4 py-2.5">Materi / Judul</th>
                                    <th class="px-4 py-2.5">Catatan</th>
                                    <th class="px-3 py-2.5 text-center">H / I / S / A</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @forelse($detailData['meetings'] as $m)
                                    <tr>
                                        <td class="px-4 py-2.5 font-bold">Ke-{{ $m['pertemuan_ke'] }}</td>
                                        <td class="px-4 py-2.5">{{ $m['tanggal_label'] }}</td>
                                        <td class="px-4 py-2.5 font-semibold">{{ $m['judul_kegiatan'] }}</td>
                                        <td class="px-4 py-2.5 text-gray-500">{{ $m['catatan_pembina'] }}</td>
                                        <td class="px-3 py-2.5 text-center font-mono font-bold">{{ $m['hadir_count'] }} / {{ $m['izin_count'] }} / {{ $m['sakit_count'] }} / {{ $m['alpa_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada pertemuan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rekap Kehadiran Siswa -->
                <div>
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">Rekap Kehadiran Anggota</h4>
                    <div class="overflow-x-auto border border-gray-200 dark:border-white/10 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-white/5 font-bold uppercase text-gray-500">
                                <tr>
                                    <th class="px-3 py-2.5 text-center">No</th>
                                    <th class="px-4 py-2.5">Nama Siswa</th>
                                    <th class="px-3 py-2.5 text-center">Kelas</th>
                                    @foreach($detailData['meetings'] as $m)
                                        <th class="px-2 py-2.5 text-center">P{{ $m['pertemuan_ke'] }}</th>
                                    @endforeach
                                    <th class="px-3 py-2.5 text-center">H</th>
                                    <th class="px-3 py-2.5 text-center">I</th>
                                    <th class="px-3 py-2.5 text-center">S</th>
                                    <th class="px-3 py-2.5 text-center">A</th>
                                    <th class="px-3 py-2.5 text-center">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @forelse($detailData['members'] as $mem)
                                    <tr>
                                        <td class="px-3 py-2 text-center">{{ $mem['no'] }}</td>
                                        <td class="px-4 py-2 font-bold">{{ $mem['name'] }}</td>
                                        <td class="px-3 py-2 text-center">{{ $mem['kelas'] }}</td>
                                        @foreach($detailData['meetings'] as $m)
                                            <td class="px-2 py-2 text-center font-bold">{{ $mem['daily'][$m['id']] ?? '-' }}</td>
                                        @endforeach
                                        <td class="px-3 py-2 text-center font-bold text-success-600">{{ $mem['hadir'] }}</td>
                                        <td class="px-3 py-2 text-center font-bold text-info-600">{{ $mem['izin'] }}</td>
                                        <td class="px-3 py-2 text-center font-bold text-warning-600">{{ $mem['sakit'] }}</td>
                                        <td class="px-3 py-2 text-center font-bold text-danger-600">{{ $mem['alpa'] }}</td>
                                        <td class="px-3 py-2 text-center font-bold">{{ $mem['pct'] }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="py-6 text-center text-gray-400">Belum ada anggota aktif.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>

