<div class="space-y-4">
    <!-- Ringkasan Statistik Presensi -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
            <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Siswa</span>
            <span class="text-lg font-bold text-slate-900 dark:text-white">{{ $record->total_siswa }}</span>
        </div>
        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
            <span class="text-xs text-emerald-600 dark:text-emerald-400 block font-medium">Hadir di Kelas</span>
            <span class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ $record->total_hadir }}</span>
        </div>
        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800">
            <span class="text-xs text-rose-600 dark:text-rose-400 block font-medium">Tidak Mengikuti</span>
            <span class="text-lg font-bold text-rose-700 dark:text-rose-300">{{ $record->total_tidak_mengikuti }}</span>
        </div>
        @php
            $diabsenkanCount = $record->presensis->where('diabsenkan_telat_di_gerbang', true)->count();
        @endphp
        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
            <span class="text-xs text-amber-600 dark:text-amber-400 block font-medium">Diabsenkan Telat</span>
            <span class="text-lg font-bold text-amber-700 dark:text-amber-300">{{ $diabsenkanCount }}</span>
        </div>
    </div>

    <!-- Tabel Rincian Siswa -->
    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
        <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-3 py-2.5 w-10 text-center">No</th>
                    <th class="px-3 py-2.5 w-28">NIS / NISN</th>
                    <th class="px-3 py-2.5">Nama Siswa</th>
                    <th class="px-3 py-2.5 text-center w-36">Status di Jurnal</th>
                    <th class="px-3 py-2.5 text-center w-36">Status Gerbang</th>
                    <th class="px-3 py-2.5">Catatan Guru</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                @forelse($record->presensis()->with('siswa')->get() as $index => $item)
                    @php
                        $siswa = $item->siswa;
                        $nis = $siswa?->nis ?? $siswa?->nisn ?? '-';
                        $statusJurnal = $item->status_jurnal;
                        $statusGerbang = $item->status_gerbang_snapshot;
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-3 py-2.5 text-center font-medium text-slate-500">{{ $index + 1 }}</td>
                        <td class="px-3 py-2.5 font-mono text-slate-600 dark:text-slate-400">{{ $nis }}</td>
                        <td class="px-3 py-2.5 font-semibold text-slate-900 dark:text-white">
                            {{ $siswa?->name ?? 'Siswa tidak ditemukan' }}
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if($statusJurnal === 'hadir')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    Hadir
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                    Tidak Mengikuti
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if($statusGerbang === 'hadir')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    Hadir Gerbang
                                </span>
                            @elseif($statusGerbang === 'telat')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                    Telat
                                    @if($item->diabsenkan_telat_di_gerbang)
                                        <span class="ml-1 text-[9px] font-bold underline">(Oleh Guru)</span>
                                    @endif
                                </span>
                            @elseif($statusGerbang === 'sakit')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-yellow-50 text-yellow-700 dark:bg-yellow-950/50 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">
                                    Sakit
                                </span>
                            @elseif($statusGerbang === 'izin')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                    Izin
                                </span>
                            @elseif($statusGerbang === 'alpa')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                    Alpa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    Belum Absen Gerbang
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 italic">
                            {{ $item->keterangan ?: '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-slate-400">
                            Belum ada rekaman presensi siswa untuk jurnal ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
