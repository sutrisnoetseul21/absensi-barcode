<div>
    <div class="mb-6 lg:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Cetak Kartu Siswa</h1>
            <p class="text-sm sm:text-base text-slate-500 mt-1">Cetak kartu presensi siswa yang dilengkapi dengan barcode.</p>
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
    @if (session()->has('warning'))
        <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <div>
                <h3 class="text-sm font-bold text-amber-800">Perhatian</h3>
                <p class="text-xs text-amber-600 mt-0.5">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-end gap-4">
            <!-- Filter Tahun Ajaran -->
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran</label>
                <select wire:model.change="selectedAcademicYearId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kelas -->
            @if($classes->isNotEmpty())
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">Kelas</label>
                <select wire:model.change="selectedClassId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Tombol Proses -->
            <div>
                <button wire:click="filterData" wire:loading.attr="disabled" class="w-full md:w-auto flex items-center justify-center gap-2 bg-brand-primary hover:bg-brand-primary-light text-white px-5 py-2.5 rounded-xl font-bold shadow-md shadow-brand-primary/20 transition-all">
                    <svg wire:loading.remove wire:target="filterData" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <svg wire:loading wire:target="filterData" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span wire:loading.remove wire:target="filterData">Tampilkan Data</span>
                    <span wire:loading wire:target="filterData">Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    @if(!$hasSubmittedFilter)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-10 text-center flex flex-col items-center justify-center min-h-[300px]">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Daftar Siswa Belum Dimuat</h3>
            <p class="text-slate-500 max-w-md">Silakan pilih Tahun Ajaran dan Kelas di atas, lalu klik tombol <strong class="text-slate-700">Tampilkan Data</strong> untuk memuat daftar siswa.</p>
        </div>
    @elseif($selectedClassId)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
            <!-- Table Toolbar -->
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <!-- Global Search -->
                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari NISN, NIS, atau Nama..." class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm pl-10 pr-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if(count($selectedStudents) > 0)
                    <button wire:click="cetakTerpilih" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Cetak Terpilih ({{ count($selectedStudents) }})
                    </button>
                    @endif
                    <button wire:click="cetakSemua" class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Cetak Semua Kelas Ini
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto relative min-h-[300px]">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-white border-b border-slate-200 text-slate-600">
                        <tr>
                            <th class="px-6 py-4 w-12">
                                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-brand-primary focus:ring-brand-primary cursor-pointer">
                            </th>
                            <th class="px-4 py-4 font-bold uppercase tracking-wider">Foto</th>
                            <th class="px-4 py-4 font-bold uppercase tracking-wider">NISN / NIS</th>
                            <th class="px-4 py-4 font-bold uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-4 py-4 font-bold uppercase tracking-wider">Barcode</th>
                            <th class="px-4 py-4 font-bold uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 relative" wire:loading.class="opacity-50">
                        @forelse($students as $student)
                            <tr class="hover:bg-slate-50 transition-colors" wire:key="student-{{ $student->id }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student->id }}" class="w-4 h-4 rounded border-slate-300 text-brand-primary focus:ring-brand-primary cursor-pointer">
                                </td>
                                <td class="px-4 py-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden">
                                        @if($student->photo_path)
                                            <img src="{{ Storage::url($student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                                        @else
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ $student->name }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-800">{{ $student->nisn ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $student->nis ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-4 font-bold text-slate-800">
                                    {{ $student->name }}
                                </td>
                                <td class="px-4 py-4">
                                    @php
                                        $barcode = $student->presensiProfile?->barcode_code;
                                        $isActive = $student->presensiProfile?->barcode_active;
                                    @endphp
                                    @if($barcode)
                                        <div class="flex items-center gap-2">
                                            <code class="px-2 py-1 bg-slate-100 text-slate-700 rounded border border-slate-200 font-mono text-xs">{{ $barcode }}</code>
                                            @if($isActive)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Aktif</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Nonaktif</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum ada barcode</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <button wire:click="cetakKartu('{{ $student->id }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded-lg text-xs font-bold transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        Cetak
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    <p class="font-bold text-slate-500">Tidak ada data siswa ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div wire:loading.flex class="absolute inset-0 bg-white/50 backdrop-blur-sm items-center justify-center z-20" style="display: none;">
                    <svg class="animate-spin w-8 h-8 text-brand-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
                <div class="px-5 py-4 border-t border-slate-200 bg-white">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-url', (event) => {
                window.open(event.url, '_blank');
            });
        });
    </script>
</div>
