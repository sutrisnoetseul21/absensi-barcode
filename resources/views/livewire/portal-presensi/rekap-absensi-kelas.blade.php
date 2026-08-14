<div class="space-y-6">
    {{-- Header & Filter Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between flex-wrap">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Rekapitulasi Presensi Kelas</h2>
                <p class="text-sm text-slate-500 mt-1">Kelola presensi siswa per kelas dan per bulan</p>
            </div>

            <div class="flex flex-wrap gap-4 items-end">
                {{-- Filter Tahun Ajaran --}}
                <div class="flex flex-col gap-1.5 min-w-[140px]">
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tahun Ajaran</label>
                    <select wire:model.change="selectedAcademicYearId"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors cursor-pointer">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }} {{ $year->status === 'aktif' ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Kelas --}}
                @if($classes->isNotEmpty())
                <div class="flex flex-col gap-1.5 min-w-[140px]">
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Kelas</label>
                    <select wire:model.change="selectedClassId"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors cursor-pointer">
                        @foreach($classes as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Filter Bulan --}}
                <div class="flex flex-col gap-1.5 min-w-[140px]">
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Bulan</label>
                    <select wire:model.change="selectedMonth"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors cursor-pointer">
                        @foreach(['07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember','01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni'] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                @if($classes->isNotEmpty() && $selectedClassId)
                <div class="flex gap-2">
                    <button wire:click="exportExcel" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-sm font-bold transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Excel
                    </button>
                    <button wire:click="exportPdf" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl text-sm font-bold transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        PDF
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    @if($classes->isNotEmpty() && $selectedClassId && !empty($todayStats))
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Hadir Hari Ini</p>
                <p class="text-xl font-black text-slate-900 mt-1">
                    {{ ($todayStats['hadir'] ?? 0) + ($todayStats['telat'] ?? 0) }} / {{ $todayStats['total'] ?? 0 }}
                    <span class="text-sm font-bold text-slate-400">({{ $todayStats['persentase_hadir'] ?? 0 }}%)</span>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Terlambat</p>
                <p class="text-xl font-black text-slate-900 mt-1">{{ $todayStats['telat'] ?? 0 }} <span class="text-sm font-semibold text-slate-400">Siswa</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Sakit/Izin/Alpa</p>
                <p class="text-xl font-black text-rose-600 mt-1">
                    {{ ($todayStats['sakit'] ?? 0) + ($todayStats['izin'] ?? 0) + ($todayStats['alpa'] ?? 0) }} <span class="text-sm font-semibold text-rose-400">Siswa</span>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Belum Absen</p>
                <p class="text-xl font-black text-slate-900 mt-1">{{ $todayStats['belum'] ?? 0 }} <span class="text-sm font-semibold text-slate-400">Siswa</span></p>
            </div>
        </div>
    </div>
    @endif

    {{-- Kalender Table --}}
    @if($classes->isNotEmpty() && $selectedClassId)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
        <div class="overflow-x-auto custom-scrollbar" wire:loading.class="opacity-50">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-600 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="sticky left-0 z-10 bg-slate-50 px-4 py-3.5 w-48 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0]">
                            Nama Siswa
                        </th>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php $isToday = ($todayDate === date('Y') . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT)); @endphp
                            <th class="px-2 py-3.5 text-center min-w-[36px] {{ $isToday ? 'bg-brand-primary/10 text-brand-primary' : '' }}">
                                {{ $d }}
                            </th>
                        @endfor
                        <th class="px-3 py-3.5 text-center text-emerald-600 border-l border-slate-200">H</th>
                        <th class="px-3 py-3.5 text-center text-amber-600">T</th>
                        <th class="px-3 py-3.5 text-center text-sky-600">I</th>
                        <th class="px-3 py-3.5 text-center text-indigo-600">S</th>
                        <th class="px-3 py-3.5 text-center text-rose-600">A</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        @php
                            $isAlpaWarning = in_array($student->id, $alerts['alpa'] ?? []);
                            $isTelatWarning = in_array($student->id, $alerts['telat'] ?? []);
                            $stat = $monthlyStats[$student->id] ?? [];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ ($isAlpaWarning || $isTelatWarning) ? 'bg-rose-50/40' : '' }}">
                            <td class="sticky left-0 z-10 px-4 py-3 bg-white group-hover:bg-slate-50/80 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0]">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 whitespace-nowrap">{{ $student->name }}</span>
                                    @if($isAlpaWarning)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-rose-600 font-bold uppercase tracking-wider mt-0.5">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            ≥ 3 Alpa
                                        </span>
                                    @endif
                                    @if($isTelatWarning)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-amber-600 font-bold uppercase tracking-wider mt-0.5">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            ≥ 100mnt Telat
                                        </span>
                                    @endif
                                </div>
                            </td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $code = $stat['daily'][$d] ?? '-';
                                    $cellClass = match($code) {
                                        'H' => 'text-emerald-600 font-black',
                                        'T' => 'text-amber-500 font-black',
                                        'I' => 'text-sky-500 font-black',
                                        'S' => 'text-indigo-500 font-black',
                                        'A' => 'text-rose-600 font-black',
                                        'L' => 'text-slate-400 bg-slate-100 font-black',
                                        default => 'text-slate-300',
                                    };
                                @endphp
                                <td class="px-2 py-3 text-center {{ $cellClass }}">{{ $code }}</td>
                            @endfor
                            <td class="px-3 py-3 text-center font-black text-emerald-600 border-l border-slate-200">{{ $stat['hadir'] ?? 0 }}</td>
                            <td class="px-3 py-3 text-center font-black text-amber-500">{{ $stat['telat'] ?? 0 }}</td>
                            <td class="px-3 py-3 text-center font-black text-sky-500">{{ $stat['izin'] ?? 0 }}</td>
                            <td class="px-3 py-3 text-center font-black text-indigo-500">{{ $stat['sakit'] ?? 0 }}</td>
                            <td class="px-3 py-3 text-center font-black text-rose-600">{{ $stat['alpa'] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 6 }}" class="py-12 text-center text-slate-500">
                                Tidak ada data siswa terdaftar di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Loading overlay --}}
        <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-sm z-20 flex items-center justify-center">
            <div class="flex items-center gap-2 px-4 py-2 bg-white shadow-md rounded-xl border border-slate-100">
                <svg class="animate-spin h-5 w-5 text-brand-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-sm font-bold text-slate-700">Memuat Data...</span>
            </div>
        </div>
    </div>
    @endif
</div>
