<div>
    <div class="mb-6 lg:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Data Siswa</h1>
            <p class="text-sm sm:text-base text-slate-500 mt-1">Kelola data siswa, cetak kartu barcode, dan perbarui data via Excel.</p>
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

                <div class="flex items-center flex-wrap gap-2">
                    <!-- Export / Import Buttons (Full Data) -->
                    <button wire:click="downloadDataExcel" class="flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all" title="Download Semua Biodata Siswa (Excel)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download Data
                    </button>
                    <button wire:click="openUploadModal('data')" class="flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all" title="Upload File Biodata Siswa">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Upload Data
                    </button>

                    <div class="h-6 w-px bg-slate-300 mx-1 hidden md:block"></div>

                    <!-- Export / Import Buttons (No HP Only) -->
                    <button wire:click="downloadNoHpExcel" class="flex items-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all" title="Download Template No. HP (Excel)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download No. HP
                    </button>
                    <button wire:click="openUploadModal('nohp')" class="flex items-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all" title="Upload File No. HP Siswa & Ortu">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Upload No. HP
                    </button>

                    @if(count($selectedStudents) > 0)
                    <button wire:click="cetakTerpilih" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all ml-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Cetak Terpilih ({{ count($selectedStudents) }})
                    </button>
                    @endif
                    <button wire:click="cetakSemua" class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all ml-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Cetak Kartu Semua
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
                            <th class="px-4 py-4 font-bold uppercase tracking-wider">Kontak & Info</th>
                            <th class="px-4 py-4 font-bold uppercase tracking-wider">Barcode</th>
                            <th class="px-4 py-4 font-bold uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 relative" wire:loading.class="opacity-50" wire:target="filterData, search, gotoPage, next, previous">
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
                                    <div class="text-xs text-slate-700 flex flex-col gap-1">
                                        <span class="flex items-center gap-1"><svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg> {{ $student->no_hp ?? '-' }}</span>
                                        <span class="flex items-center gap-1"><svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg> <span class="truncate max-w-[150px]" title="{{ $student->address }}">{{ $student->address ?? '-' }}</span></span>
                                    </div>
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
                                    <div class="flex items-center gap-1.5 justify-center">
                                        <button wire:click="openEditDataModal('{{ $student->id }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-lg text-[10px] sm:text-xs font-bold transition-colors" title="Edit Data Siswa">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            Edit
                                        </button>
                                        <button wire:click="openEditNoHpModal('{{ $student->id }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-[10px] sm:text-xs font-bold transition-colors" title="Edit No. HP">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                            No. HP
                                        </button>
                                        <button wire:click="openChangePasswordModal('{{ $student->id }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-[10px] sm:text-xs font-bold transition-colors" title="Ganti Password">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                            Pass
                                        </button>
                                        <button wire:click="cetakKartu('{{ $student->id }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded-lg text-[10px] sm:text-xs font-bold transition-colors" title="Cetak Kartu">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                            Cetak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    <p class="font-bold text-slate-500">Tidak ada data siswa ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
                <div class="px-5 py-4 border-t border-slate-200 bg-white">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Upload Modal -->
    @if($showUploadModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="closeUploadModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-200">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start mb-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">
                                @if($uploadType === 'nohp') Upload & Update No. HP Siswa @else Upload & Update Data Siswa @endif
                            </h3>
                            <div class="mt-2 text-sm text-slate-500">
                                @if($uploadType === 'nohp')
                                Unggah file Excel yang berisi update Nomor HP Siswa & Orang Tua. Pastikan file ini berasal dari unduhan <strong class="text-slate-800">Download No. HP</strong>.
                                @else
                                Unggah file Excel yang berisi update Biodata Lengkap. Pastikan file ini berasal dari unduhan <strong class="text-slate-800">Download Data</strong>.
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($importError)
                        <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium flex gap-2">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            {{ $importError }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pilih File Excel (.xlsx)</label>
                        <input type="file" wire:model.live="uploadFile" accept=".xlsx, .xls" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20 transition-all border border-slate-300 rounded-xl bg-slate-50">
                        <div wire:loading wire:target="uploadFile" class="text-sm text-brand-primary mt-2 flex items-center gap-2 font-medium">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses file...
                        </div>
                    </div>

                    @if(!empty($previewRows))
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-bold text-slate-800 mb-3">Preview Update Data</h4>
                        <div class="flex gap-2 mb-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                ✅ Ditemukan {{ $previewSummary['total'] }} baris data
                            </span>
                            @if($previewSummary['truncated'])
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                ⚠️ Menampilkan 100 baris pertama
                            </span>
                            @endif
                        </div>
                        
                        <div class="overflow-x-auto max-h-[300px] border border-slate-200 rounded-lg">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-600">ID</th>
                                        @if($uploadType === 'data')
                                        <th class="px-4 py-2 text-left font-semibold text-slate-600">NISN</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-600">Nama Siswa</th>
                                        @else
                                        <th class="px-4 py-2 text-left font-semibold text-slate-600">Nama Siswa</th>
                                        <th class="px-4 py-2 text-left font-semibold text-slate-600">No. HP</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($previewRows as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-2 text-slate-500 font-mono">{{ $row['id'] }}</td>
                                        <td class="px-4 py-2 text-slate-700">{{ $row['col1'] }}</td>
                                        <td class="px-4 py-2 font-medium text-slate-800">{{ $row['col2'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-slate-200">
                    <button type="button" wire:click="processUpload" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-brand-primary text-base font-bold text-white hover:bg-brand-primary-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        <svg wire:loading wire:target="processUpload" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span wire:loading.remove wire:target="processUpload">Submit & Update</span>
                        <span wire:loading wire:target="processUpload">Menyimpan...</span>
                    </button>
                    <button type="button" wire:click="closeUploadModal" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Data Modal -->
    @if($showEditDataModal)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="closeEditDataModal"></div>
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all my-8 max-w-2xl w-full border border-slate-200 z-10">
                <form wire:submit.prevent="saveEditData">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start mb-4 border-b border-slate-100 pb-4">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-slate-900">Edit Data Siswa</h3>
                                <div class="mt-1 text-sm text-slate-500">Perbarui informasi detail siswa.</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NISN</label>
                                <input type="text" wire:model="editDataNisn" class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('editDataNisn') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NIS</label>
                                <input type="text" wire:model="editDataNis" class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('editDataNis') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="editDataName" required class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('editDataName') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tempat Lahir</label>
                                <input type="text" wire:model="editDataBirthPlace" class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('editDataBirthPlace') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Lahir</label>
                                <input type="date" wire:model="editDataBirthDate" class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('editDataBirthDate') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nomor HP</label>
                                <input type="text" wire:model="editDataNoHp" placeholder="Contoh: 081234..." class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('editDataNoHp') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                                <textarea wire:model="editDataAddress" rows="2" class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all"></textarea>
                                @error('editDataAddress') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-slate-200">
                        <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 transition-all">
                            <span wire:loading.remove wire:target="saveEditData">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveEditData">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="closeEditDataModal" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit No HP Modal -->
    @if($showEditNoHpModal)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="closeEditNoHpModal"></div>
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all my-8 max-w-md w-full border border-slate-200 z-10">
                <form wire:submit.prevent="saveEditNoHp">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start mb-4 border-b border-slate-100 pb-4">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-slate-900">Update No. HP</h3>
                                <div class="mt-1 text-sm text-slate-500">Perbarui informasi kontak secara cepat. Awalan 0 atau 62 diterima.</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">No. HP Siswa</label>
                                <input type="text" wire:model="editNoHpSiswa" placeholder="Contoh: 081234..." class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('editNoHpSiswa') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">No. HP Orang Tua / Wali</label>
                                <input type="text" wire:model="editNoHpOrtu" placeholder="Contoh: 081234..." class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('editNoHpOrtu') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-slate-200">
                        <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-bold text-white hover:bg-emerald-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 transition-all">
                            <span wire:loading.remove wire:target="saveEditNoHp">Simpan</span>
                            <span wire:loading wire:target="saveEditNoHp">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="closeEditNoHpModal" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Change Password Modal -->
    @if($showChangePasswordModal)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="closeChangePasswordModal"></div>
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all my-8 max-w-md w-full border border-slate-200 z-10">
                <form wire:submit.prevent="saveNewPassword">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start mb-4">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-slate-900">Ganti Password Siswa</h3>
                                <div class="mt-2 text-sm text-slate-500">
                                    Masukkan password baru untuk siswa ini. Mereka akan menggunakan password ini untuk login.
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Password Baru</label>
                                <input type="password" wire:model="newPassword" class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('newPassword') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password Baru</label>
                                <input type="password" wire:model="newPasswordConfirmation" class="block w-full rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 focus:ring-2 outline-none transition-all">
                                @error('newPasswordConfirmation') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-slate-200">
                        <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-base font-bold text-white hover:bg-rose-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 transition-all">
                            <span wire:loading.remove wire:target="saveNewPassword">Ubah Password</span>
                            <span wire:loading wire:target="saveNewPassword">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="closeChangePasswordModal" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-url', (event) => {
                window.open(event.url, '_blank');
            });

            // Fix: body has overflow:hidden for layout, so we temporarily allow overflow when modal is open
            Livewire.on('modal-open', () => {
                document.body.style.overflow = 'visible';
                document.documentElement.style.overflow = 'visible';
            });
            Livewire.on('modal-close', () => {
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            });
        });
    </script>
</div>
