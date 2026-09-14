<!-- Data Table Grid -->
<div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/40 overflow-hidden relative z-20">
    <div class="p-5 border-b border-slate-100/80 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
            <h3 class="text-base font-bold text-slate-800">Matriks Presensi Sholat Dhuhur</h3>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                Jumat & Hari Libur Terdata Otomatis (L)
            </span>
        </div>
        <div class="flex items-center gap-3 text-xs font-semibold">
            <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> H: Hadir
            </span>
            <span class="inline-flex items-center gap-1.5 text-blue-700 bg-blue-50 px-2 py-1 rounded-md">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span> I: Ijin / Halangan
            </span>
            <span class="inline-flex items-center gap-1.5 text-rose-700 bg-rose-50 px-2 py-1 rounded-md">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span> A: Tidak Hadir
            </span>
            <span class="inline-flex items-center gap-1.5 text-slate-600 bg-slate-200 px-2 py-1 rounded-md">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span> L: Libur / Jumat
            </span>
        </div>
    </div>

    <div class="overflow-x-auto custom-scrollbar [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50/80 backdrop-blur-sm border-b border-slate-200/60 font-black text-slate-500 uppercase tracking-wider">
                    <th class="py-4 px-4 sticky left-0 bg-slate-50/95 backdrop-blur-md z-10 shadow-[1px_0_0_0_#e2e8f0] min-w-[200px]">
                        Nama Siswa
                    </th>
                    @for($i = 1; $i <= $daysInMonth; $i++)
                        <th class="py-4 px-1.5 text-center min-w-[32px]">{{ $i }}</th>
                    @endfor
                    <th class="py-4 px-3 text-center text-emerald-700 bg-emerald-50/90 backdrop-blur-sm">H</th>
                    <th class="py-4 px-3 text-center text-blue-700 bg-blue-50/90 backdrop-blur-sm">I</th>
                    <th class="py-4 px-3 text-center text-rose-700 bg-rose-50/90 backdrop-blur-sm">A</th>
                    <th class="py-4 px-3 text-center text-slate-700 bg-slate-100/90 backdrop-blur-sm">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60">
                @forelse($students as $student)
                    @php
                        $stStats = $monthlyStats[$student->id] ?? null;
                        $hadirCount = $stStats['hadir'] ?? 0;
                        $ijinCount = $stStats['ijin'] ?? 0;
                        $alpaCount = $stStats['tidak_hadir'] ?? 0;
                        $effDays = $classMonthlyStats['effective_days'] ?? 0;
                        $pct = $effDays > 0 ? round(($hadirCount / $effDays) * 100, 1) : 0;
                    @endphp
                    <tr class="bg-white hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 sticky left-0 z-10 shadow-[1px_0_0_0_#f1f5f9] bg-white group-hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center font-bold text-[11px] text-slate-600 shrink-0">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-800 truncate block">{{ $student->name }}</span>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                        <span>{{ $student->nisn ?? '-' }}</span>
                                        <span>&bull;</span>
                                        <span class="{{ $student->gender === 'P' ? 'text-pink-600 font-semibold' : 'text-blue-600 font-semibold' }}">
                                            {{ $student->gender === 'P' ? 'Pr' : 'Lk' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @for($i = 1; $i <= $daysInMonth; $i++)
                            @php
                                $code = $stStats['daily'][$i] ?? '-';
                                $colorClass = match($code) {
                                    'H' => 'text-emerald-700 font-black bg-emerald-50 rounded border border-emerald-200/50',
                                    'I' => 'text-blue-700 font-black bg-blue-50 rounded border border-blue-200/50',
                                    'A' => 'text-rose-700 font-black bg-rose-50 rounded border border-rose-200/50',
                                    'L' => 'text-slate-400 font-semibold bg-slate-100/90 rounded border border-slate-200/50 cursor-not-allowed',
                                    default => 'text-slate-300',
                                };
                            @endphp
                            <td class="py-2 px-1 text-center" title="{{ $code === 'L' ? 'Hari Libur / Jumat' : ($code === 'I' ? 'Ijin / Halangan' : ($code === 'H' ? 'Hadir' : ($code === 'A' ? 'Tidak Hadir' : ''))) }}">
                                <div class="w-6 h-6 mx-auto flex items-center justify-center text-[11px] {{ $colorClass }}">
                                    {{ $code }}
                                </div>
                            </td>
                        @endfor
                        <td class="py-3 px-3 text-center font-black text-emerald-700 bg-emerald-50/40">{{ $hadirCount }}</td>
                        <td class="py-3 px-3 text-center font-black text-blue-700 bg-blue-50/40">{{ $ijinCount }}</td>
                        <td class="py-3 px-3 text-center font-black text-rose-700 bg-rose-50/40">{{ $alpaCount }}</td>
                        <td class="py-3 px-3 text-center font-bold text-slate-700 bg-slate-50/50">
                            {{ $pct }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $daysInMonth + 5 }}" class="py-12 text-center text-slate-500">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-300">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                            <p class="font-bold text-slate-700">Belum ada siswa di kelas ini</p>
                            <p class="text-xs text-slate-400 mt-0.5">Pastikan rombel siswa aktif di tahun ajaran yang dipilih.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
