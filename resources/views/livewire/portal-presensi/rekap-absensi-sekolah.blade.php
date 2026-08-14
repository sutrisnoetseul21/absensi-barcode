<div class="space-y-6">
    {{-- Header & Filter Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between flex-wrap">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Rekap Tahunan Per Kelas</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Pilih Tahun Ajaran untuk memuat rekap presensi seluruh kelas secara tahunan.
                </p>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap items-end gap-3">
                <div class="flex flex-col gap-1.5 min-w-[200px]">
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tahun Ajaran</label>
                    <select wire:model.change="selectedAcademicYearId"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors cursor-pointer">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }} {{ $year->status === 'aktif' ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                @if($selectedAcademicYearId)
                <div class="flex gap-2">
                    <button wire:click="exportExcel" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-sm font-bold transition-colors min-w-[120px]">
                        <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Excel
                        </span>
                        <span wire:loading wire:target="exportExcel" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4 text-emerald-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Proses...
                        </span>
                    </button>
                    <button wire:click="exportPdf" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl text-sm font-bold transition-colors min-w-[120px]">
                        <span wire:loading.remove wire:target="exportPdf" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            PDF
                        </span>
                        <span wire:loading wire:target="exportPdf" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4 text-rose-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Proses...
                        </span>
                    </button>
                </div>
                @endif
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-5 mt-5 pt-5 border-t border-slate-100 text-sm">
            <div class="flex items-center gap-2 font-black text-emerald-600">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Hadir (H)
            </div>
            <div class="flex items-center gap-2 font-black text-indigo-600">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Sakit (S)
            </div>
            <div class="flex items-center gap-2 font-black text-sky-600">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-sky-500"></span> Izin (I)
            </div>
            <div class="flex items-center gap-2 font-black text-rose-600">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500"></span> Alpa (A)
            </div>
            <div class="text-slate-400 font-medium text-[11px] uppercase tracking-wider self-center ml-auto">
                * Hadir = Hadir tepat waktu + Terlambat
            </div>
        </div>
    </div>

    {{-- ===== EMPTY STATE ===== --}}
    @if(!$selectedAcademicYearId)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex items-center gap-4">
        <div class="flex-shrink-0 w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center border border-amber-100">
            <svg class="w-7 h-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <div>
            <h4 class="font-bold text-lg text-slate-900 tracking-tight">Tahun Ajaran Belum Ditentukan</h4>
            <p class="text-sm text-slate-500 mt-1">Silakan buat dan aktifkan tahun ajaran baru terlebih dahulu.</p>
        </div>
    </div>
    @else

    {{-- ===== MATRIX TABLE ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
        <div class="overflow-x-auto custom-scrollbar" wire:loading.class="opacity-50">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-slate-50">
                    {{-- Row 1: Nama bulan --}}
                    <tr class="border-b-2 border-slate-300">
                        <th rowspan="2" class="px-3 py-3 text-center font-bold text-slate-500 uppercase tracking-wider w-10 border-r border-slate-200">No</th>
                        <th rowspan="2" class="px-4 py-3 text-left font-bold text-slate-500 uppercase tracking-wider border-r border-slate-200 min-w-[150px] sticky left-0 z-10 bg-slate-50 shadow-[1px_0_0_0_#e2e8f0]">Kelas</th>
                        <th rowspan="2" class="px-3 py-3 text-center font-bold text-slate-500 uppercase tracking-wider border-r-2 border-slate-300">Jml Siswa</th>
                        @foreach($monthsList as $m)
                            <th colspan="4" class="px-2 py-2 text-center text-slate-700 tracking-wider border-r-2 border-slate-300 bg-slate-100/50">
                                <div class="font-extrabold text-xs uppercase">{{ $m['label'] }}</div>
                                <div class="text-[10px] text-slate-500 font-medium mt-0.5">Hari Efektif: {{ $m['effective_days'] }} Hari</div>
                            </th>
                        @endforeach
                    </tr>
                    {{-- Row 2: Sub-kolom H S I A --}}
                    <tr class="border-b border-slate-200">
                        @foreach($monthsList as $m)
                            <th class="px-2 py-2.5 text-center text-[11px] font-black text-emerald-600 w-8 bg-slate-50/80">H</th>
                            <th class="px-2 py-2.5 text-center text-[11px] font-black text-indigo-600 w-8 bg-slate-50/80">S</th>
                            <th class="px-2 py-2.5 text-center text-[11px] font-black text-sky-600 w-8 bg-slate-50/80">I</th>
                            <th class="px-2 py-2.5 text-center text-[11px] font-black text-rose-600 w-8 border-r-2 border-slate-300 bg-slate-50/80">A</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classesData as $index => $row)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-3 py-3 text-center text-slate-400 font-medium border-r border-slate-200">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap border-r border-slate-200 sticky left-0 z-10 bg-white group-hover:bg-slate-50/80 shadow-[1px_0_0_0_#e2e8f0]">
                                {{ $row['name'] }}
                            </td>
                            <td class="px-3 py-3 text-center font-bold text-slate-600 border-r-2 border-slate-300">
                                {{ $row['student_count'] }}
                            </td>
                            @foreach($monthsList as $m)
                                @php
                                    $key   = "{$m['year']}-{$m['month']}";
                                    $stats = $row['months'][$key] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                                @endphp
                                <td class="px-2 py-3 text-center font-black text-emerald-600">{{ $stats['hadir'] ?: '-' }}</td>
                                <td class="px-2 py-3 text-center font-black text-indigo-600">{{ $stats['sakit'] ?: '-' }}</td>
                                <td class="px-2 py-3 text-center font-black text-sky-600">{{ $stats['izin'] ?: '-' }}</td>
                                <td class="px-2 py-3 text-center font-black text-rose-600 border-r-2 border-slate-300">{{ $stats['alpa'] ?: '-' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + (count($monthsList) * 4) }}" class="py-12 text-center text-slate-500">
                                Belum ada data kelas yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Loading overlay --}}
        <div wire:loading wire:target="selectedAcademicYearId, loadReportData" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-20 flex items-center justify-center">
            <div class="flex items-center gap-2 px-4 py-2 bg-white shadow-md rounded-xl border border-slate-100">
                <svg class="animate-spin h-5 w-5 text-brand-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-sm font-bold text-slate-700">Memuat Data...</span>
            </div>
        </div>
    </div>
    @endif
</div>
