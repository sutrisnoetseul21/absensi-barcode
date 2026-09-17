<div>
    <div class="mb-6 lg:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Nilai Ujian</h1>
            <p class="text-sm sm:text-base text-slate-500 mt-1">Lihat rekap nilai siswa pada ujian yang Anda ampu.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h3 class="text-sm font-bold text-emerald-800">Berhasil</h3>
                <p class="text-xs text-emerald-600 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h3 class="text-sm font-bold text-rose-800">Gagal</h3>
                <p class="text-xs text-rose-600 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Filter Tahun Ajaran -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran</label>
                <select wire:model.change="selectedAcademicYearId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 outline-none focus:ring-2 focus:ring-brand-primary/20">
                    <option value="">-- Pilih --</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Nama Event -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Event CBT</label>
                <select wire:model.change="selectedEventName" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 outline-none focus:ring-2 focus:ring-brand-primary/20">
                    <option value="">-- Pilih --</option>
                    @foreach($events as $event)
                        <option value="{{ $event }}">{{ $event }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Nama Agenda Ujian (sub dari event) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Agenda Ujian</label>
                <select wire:model.change="selectedAgendaUjianId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 outline-none focus:ring-2 focus:ring-brand-primary/20">
                    <option value="">-- Pilih --</option>
                    @foreach($agendas as $agenda)
                        <option value="{{ $agenda->id }}">{{ $agenda->nama_ujian }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kelas -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kelas</label>
                <select wire:model.change="selectedClassId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 outline-none focus:ring-2 focus:ring-brand-primary/20">
                    <option value="">-- Pilih --</option>
                    @foreach($classes as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div wire:loading wire:target="selectedAcademicYearId, selectedJenisUjianId, selectedEventName, selectedAgendaUjianId, selectedClassId" class="w-full mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-10 flex flex-col items-center justify-center">
            <svg class="animate-spin w-8 h-8 text-brand-primary mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <p class="text-slate-500 font-medium">Memuat data...</p>
        </div>
    </div>

    <div wire:loading.remove wire:target="selectedAcademicYearId, selectedJenisUjianId, selectedEventName, selectedAgendaUjianId, selectedClassId">
        @if(!$activeUjian)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-10 text-center flex flex-col items-center justify-center min-h-[300px]">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Belum Memilih Ujian & Kelas</h3>
                <p class="text-slate-500 max-w-md">Silakan lengkapi filter Tahun Ajaran → Jenis Asesmen → Nama Event → <strong class="text-slate-700">Nama Agenda Ujian</strong> → Kelas di atas untuk memuat daftar nilai.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">
                        Data Nilai Siswa
                    </h3>
                    <button wire:click="pullNilaiDariZenCBT" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary text-white text-sm font-bold rounded-xl hover:bg-brand-primary-light transition-all shadow-sm">
                        <svg wire:loading.remove wire:target="pullNilaiDariZenCBT" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        <svg wire:loading wire:target="pullNilaiDariZenCBT" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span wire:loading.remove wire:target="pullNilaiDariZenCBT">Tarik Nilai dari ZenCBT</span>
                        <span wire:loading wire:target="pullNilaiDariZenCBT">Menarik Nilai...</span>
                    </button>
                </div>
                
                <div class="overflow-x-auto relative min-h-[300px]">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-white border-b border-slate-200 text-slate-600">
                            <tr>
                                <th class="px-4 py-4 font-bold uppercase tracking-wider text-center w-12">No</th>
                                <th class="px-4 py-4 font-bold uppercase tracking-wider">NISN</th>
                                <th class="px-4 py-4 font-bold uppercase tracking-wider">Nama Siswa</th>
                                <th class="px-4 py-4 font-bold uppercase tracking-wider text-center">Nilai Akhir</th>
                                <th class="px-4 py-4 font-bold uppercase tracking-wider text-center">KKM</th>
                                <th class="px-4 py-4 font-bold uppercase tracking-wider text-center">Status</th>
                                <th class="px-4 py-4 font-bold uppercase tracking-wider text-center">Pelanggaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" wire:loading.class="opacity-50">
                            @forelse($students as $index => $student)
                                @php
                                    $nilai = $nilais->get($student->id);
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-4 text-center text-slate-500">{{ $students->firstItem() + $index }}</td>
                                    <td class="px-4 py-4 text-slate-500 font-mono">{{ $student->nisn ?? '-' }}</td>
                                    <td class="px-4 py-4 font-bold text-slate-800">{{ $student->name }}</td>
                                    <td class="px-4 py-4 text-center font-bold {{ $nilai ? 'text-slate-800' : 'text-slate-400' }}">
                                        {{ $nilai ? number_format($nilai->nilai_akhir, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-center text-slate-500">
                                        {{ $activeUjian->kkm }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if($nilai)
                                            @if($nilai->is_tuntas)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Tuntas</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Belum Tuntas</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if($nilai && $nilai->violation_count > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                {{ $nilai->violation_count }} kali
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <p class="font-bold text-slate-500">Tidak ada data siswa ditemukan di kelas ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->hasPages())
                    <div class="px-5 py-4 border-t border-slate-200 bg-white">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
