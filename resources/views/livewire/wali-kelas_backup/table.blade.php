<!-- Data Table Grid -->
<div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="py-4 px-6 sticky left-0 bg-slate-50 z-10 shadow-[1px_0_0_0_#e2e8f0]">Nama Siswa</th>
                    @for($i = 1; $i <= $daysInMonth; $i++)
                        <th class="py-4 px-2 text-center min-w-[35px]">{{ $i }}</th>
                    @endfor
                    <th class="py-4 px-3 text-center text-emerald-600 bg-emerald-50">H</th>
                    <th class="py-4 px-3 text-center text-amber-600 bg-amber-50">T</th>
                    <th class="py-4 px-3 text-center text-blue-600 bg-blue-50">I</th>
                    <th class="py-4 px-3 text-center text-indigo-600 bg-indigo-50">S</th>
                    <th class="py-4 px-3 text-center text-red-600 bg-red-50">A</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($students as $student)
                    @php
                        $isAlpaWarning = in_array($student->id, $alerts['alpa'] ?? []);
                        $isTelatWarning = in_array($student->id, $alerts['telat'] ?? []);
                        $rowBg = ($isAlpaWarning || $isTelatWarning) ? 'bg-red-50/50 hover:bg-red-50' : 'bg-white hover:bg-slate-50';
                        $stickyBg = ($isAlpaWarning || $isTelatWarning) ? 'bg-red-50' : 'bg-white';
                    @endphp
                    <tr class="{{ $rowBg }} transition-colors">
                        <td class="py-3 px-6 sticky left-0 z-10 shadow-[1px_0_0_0_#f1f5f9] {{ $stickyBg }}">
                            <div class="flex flex-col">
                                <a href="{{ route('wali-kelas.student-detail', ['id' => $student->id]) }}" class="font-bold text-indigo-700 hover:text-indigo-900 hover:underline transition-colors whitespace-nowrap cursor-pointer" title="Lihat Detail Siswa">
                                    {{ $student->name }}
                                </a>
                                <div class="flex gap-1 mt-1">
                                    @if($isAlpaWarning)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">⚠️ ≥3 Alpa</span>
                                    @endif
                                    @if($isTelatWarning)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">⚠️ ≥100mnt Telat</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @for($i = 1; $i <= $daysInMonth; $i++)
                            @php
                                $code = $monthlyStats[$student->id]['daily'][$i] ?? '-';
                                $colorClass = match($code) {
                                    'H' => 'text-emerald-600 font-black bg-emerald-50 rounded',
                                    'T' => 'text-amber-600 font-black bg-amber-50 rounded',
                                    'I' => 'text-blue-600 font-black bg-blue-50 rounded',
                                    'S' => 'text-indigo-600 font-black bg-indigo-50 rounded',
                                    'A' => 'text-red-600 font-black bg-red-50 rounded',
                                    'L' => 'text-slate-500 font-black bg-slate-200 rounded cursor-not-allowed',
                                    default => 'text-slate-300',
                                };
                            @endphp
                            <td class="py-3 px-1 text-center" title="{{ $code === 'L' ? 'Libur' : '' }}">
                                <div class="w-7 h-7 mx-auto flex items-center justify-center text-xs {{ $colorClass }}">
                                    {{ $code === 'L' ? 'L' : $code }}
                                </div>
                            </td>
                        @endfor
                        <td class="py-3 px-3 text-center font-black text-emerald-600 bg-emerald-50/50">{{ $monthlyStats[$student->id]['hadir'] ?? 0 }}</td>
                        <td class="py-3 px-3 text-center font-black text-amber-600 bg-amber-50/50">{{ $monthlyStats[$student->id]['telat'] ?? 0 }}</td>
                        <td class="py-3 px-3 text-center font-black text-blue-600 bg-blue-50/50">{{ $monthlyStats[$student->id]['izin'] ?? 0 }}</td>
                        <td class="py-3 px-3 text-center font-black text-indigo-600 bg-indigo-50/50">{{ $monthlyStats[$student->id]['sakit'] ?? 0 }}</td>
                        <td class="py-3 px-3 text-center font-black text-red-600 bg-red-50/50">{{ $monthlyStats[$student->id]['alpa'] ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $daysInMonth + 6 }}" class="py-12 text-center text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <p class="mt-3 font-semibold">Belum ada siswa terdaftar di kelas ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
