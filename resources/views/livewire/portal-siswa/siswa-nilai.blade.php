<div>
    <div class="mb-6 lg:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Nilai Akademik</h1>
            <p class="text-sm sm:text-base text-slate-500 mt-1">Lihat rekap nilai ujian Anda yang telah dipublikasikan oleh guru.</p>
        </div>
    </div>

    @forelse($groupedNilai as $tahunAjaran => $events)
        <div class="mb-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 inline-flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                TAHUN AJARAN {{ strtoupper($tahunAjaran) }}
            </h2>

            <div class="grid grid-cols-1 gap-6">
                @foreach($events as $eventName => $nilais)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/50">
                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                                EVENT: {{ strtoupper($eventName) }}
                            </h3>
                        </div>
                        <div class="overflow-x-auto relative">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-white border-b border-slate-200 text-slate-600">
                                    <tr>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider text-center w-12">No</th>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider">Mata Pelajaran</th>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider">Jenis Ujian</th>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider text-center">Nilai Akhir</th>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider text-center">KKM</th>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($nilais as $index => $nilai)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3 text-center text-slate-500">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 font-bold text-slate-800">{{ $nilai['mapel'] }}</td>
                                            <td class="px-4 py-3 text-slate-500">{{ $nilai['jenis_ujian'] }}</td>
                                            <td class="px-4 py-3 text-center font-bold {{ $nilai['is_tuntas'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ number_format($nilai['nilai_akhir'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-slate-500">{{ $nilai['kkm'] }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($nilai['is_tuntas'])
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Tuntas</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Belum Tuntas</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-10 text-center flex flex-col items-center justify-center min-h-[300px]">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Belum Ada Nilai Tersedia</h3>
            <p class="text-slate-500 max-w-md">Belum ada nilai ujian yang dipublikasikan oleh Bapak/Ibu Guru Anda. Silakan cek kembali nanti.</p>
        </div>
    @endforelse
</div>
