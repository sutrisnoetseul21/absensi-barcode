@php
    $canEdit = (bool) (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'));
@endphp
<div class="space-y-6" x-data="{ 
    canEdit: {{ $canEdit ? 'true' : 'false' }},
    showNewStudentForm: false,
    draggedStudentId: null,
    
    // Lists
    leftStudents: {{ $leftStudentsJson }},
    rightStudents: {{ $rightStudentsJson }},
    
    // Sort logic
    leftSortBy: 'name',
    leftSortDir: 'asc',
    rightSortBy: 'name',
    rightSortDir: 'asc',
    
    get leftSorted() {
        return [...this.leftStudents].sort((a, b) => {
            let va = a[this.leftSortBy] ?? '';
            let vb = b[this.leftSortBy] ?? '';
            if (va < vb) return this.leftSortDir === 'asc' ? -1 : 1;
            if (va > vb) return this.leftSortDir === 'asc' ? 1 : -1;
            return 0;
        });
    },
    
    get rightSorted() {
        return [...this.rightStudents].sort((a, b) => {
            let va = this.rightSortBy === 'kelasSebelumnya' ? (a.kelasSebelumnya ?? '') : (a[this.rightSortBy] ?? '');
            let vb = this.rightSortBy === 'kelasSebelumnya' ? (b.kelasSebelumnya ?? '') : (b[this.rightSortBy] ?? '');
            if (va < vb) return this.rightSortDir === 'asc' ? -1 : 1;
            if (va > vb) return this.rightSortDir === 'asc' ? 1 : -1;
            return 0;
        });
    },
    
    toggleLeft(col) {
        if (this.leftSortBy === col) {
            this.leftSortDir = this.leftSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.leftSortBy = col;
            this.leftSortDir = 'asc';
        }
    },
    
    toggleRight(col) {
        if (this.rightSortBy === col) {
            this.rightSortDir = this.rightSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.rightSortBy = col;
            this.rightSortDir = 'asc';
        }
    },

    async enroll(id) {
        if (!this.canEdit) return;
        let res = await $wire.enrollStudent(id, '{{ $kelas->id }}', '{{ $academicYear->id ?? '' }}');
        if (res) {
            const idx = this.rightStudents.findIndex(s => s.id === id);
            if (idx !== -1) {
                const student = this.rightStudents.splice(idx, 1)[0];
                this.leftStudents.push(student);
            }
        }
    },

    async unenroll(id) {
        if (!this.canEdit) return;
        let res = await $wire.unenrollStudent(id, '{{ $academicYear->id ?? '' }}');
        if (res) {
            const idx = this.leftStudents.findIndex(s => s.id === id);
            if (idx !== -1) {
                const student = this.leftStudents.splice(idx, 1)[0];
                this.rightStudents.push(student);
            }
        }
    }
}">

    <!-- Header Summary -->
    <div class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl flex justify-between items-center flex-wrap gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">Manajemen Rombel: Kelas {{ $kelas->name }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tahun Ajaran: <strong class="text-gray-700 dark:text-gray-200">{{ $academicYear->name ?? '—' }}</strong></p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-success-50 dark:bg-success-500/10 text-success-600 dark:text-success-400 border border-success-200 dark:border-success-500/20">
                <span x-text="leftStudents.length"></span> Siswa Terdaftar
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-500/20">
                <span x-text="rightStudents.length"></span> Kandidat Siswa
            </span>
        </div>
    </div>

    <!-- Dual Pane Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
        
        <!-- PANEL KIRI: Anggota Kelas -->
        <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-5 flex flex-col h-[60vh] min-h-[500px] shadow-sm"
             @dragover.prevent
             @drop="if (!canEdit) return; let id = event.dataTransfer.getData('student_id'); if (id) enroll(id)">
            
            <div class="flex justify-between items-center mb-4 gap-4 flex-wrap">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-success-500"></span>
                    Siswa Kelas Ini (<span x-text="leftStudents.length"></span>)
                </h4>
                <div class="relative w-48">
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchLeft" 
                           placeholder="Cari anggota..." 
                           class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-950 dark:text-white py-1.5 pl-3 pr-8 text-sm" />
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Scrollable Area -->
            <div class="flex-1 overflow-y-auto border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-white/5 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-thumb]:rounded-full pr-[2px]">
                <template x-if="leftStudents.length > 0">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900/90 backdrop-blur z-10 shadow-sm">
                            <tr>
                                {{-- Header: Nama Siswa (sortable) --}}
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                                    @click="toggleLeft('name')">
                                    <span class="inline-flex items-center gap-1">
                                        Nama Siswa
                                        <span x-show="leftSortBy === 'name'" x-cloak>
                                            <svg x-show="leftSortDir === 'asc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                            <svg x-show="leftSortDir === 'desc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </span>
                                        <span x-show="leftSortBy !== 'name'" x-cloak class="opacity-30">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                                        </span>
                                    </span>
                                </th>

                                {{-- Header: NISN (sortable) --}}
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                                    @click="toggleLeft('nisn')">
                                    <span class="inline-flex items-center gap-1">
                                        NISN
                                        <span x-show="leftSortBy === 'nisn'" x-cloak>
                                            <svg x-show="leftSortDir === 'asc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                            <svg x-show="leftSortDir === 'desc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </span>
                                        <span x-show="leftSortBy !== 'nisn'" x-cloak class="opacity-30">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                                        </span>
                                    </span>
                                </th>

                                <template x-if="canEdit">
                                    <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider text-right">Aksi</th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="siswa in leftSorted" :key="siswa.id">
                                <tr :draggable="canEdit"
                                    @dragstart="if (canEdit) $event.dataTransfer.setData('student_id', siswa.id)"
                                    class="border-b border-gray-200 dark:border-white/10 transition-colors hover:bg-gray-50 dark:hover:bg-white/5 cursor-grab active:cursor-grabbing">

                                    {{-- Nama Siswa --}}
                                    <td class="px-3 py-2.5 text-sm align-middle text-gray-900 dark:text-white font-medium">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>
                                            <span x-text="siswa.name"></span>
                                        </span>
                                    </td>

                                    {{-- NISN --}}
                                    <td class="px-3 py-2.5 text-sm align-middle text-gray-500 dark:text-gray-400 font-mono text-xs" x-text="siswa.nisn"></td>

                                    {{-- Aksi: Keluarkan --}}
                                    <template x-if="canEdit">
                                        <td class="px-3 py-2.5 text-sm align-middle text-right">
                                            <button type="button"
                                                    @click="if (confirm('Apakah Anda yakin ingin mengeluarkan ' + siswa.name + ' dari rombel kelas ini?')) { unenroll(siswa.id) }"
                                                    title="Keluarkan dari kelas"
                                                    class="p-1 rounded-md transition-colors inline-flex items-center justify-center border border-transparent bg-danger-50 dark:bg-danger-500/10 text-danger-600 dark:text-danger-400 hover:bg-danger-600 hover:text-white dark:hover:bg-danger-500">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                </svg>
                                            </button>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
                <template x-if="leftStudents.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-11 h-11 text-gray-500 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <p class="text-sm font-bold text-gray-400 dark:text-gray-500 m-0">Belum Ada Anggota</p>
                        <p class="text-[0.65rem] text-gray-500 dark:text-gray-500 max-w-[220px] mt-1">Tarik siswa dari panel kanan ke sini atau klik tombol panah untuk memasukkan anggota.</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- PANEL KANAN: Siswa Tanpa Kelas -->
        <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-5 flex flex-col h-[60vh] min-h-[500px] shadow-sm"
             @dragover.prevent
             @drop="if (!canEdit) return; let id = event.dataTransfer.getData('student_id'); if (id) unenroll(id)">
            
            <div class="flex justify-between items-center mb-2 gap-4 flex-wrap">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                    Kandidat Siswa (<span x-text="rightStudents.length"></span>)
                </h4>
                <div class="flex items-center gap-2">
                    @if($canEdit)
                    <!-- Button Tambah Siswa Baru (Buka sub form) -->
                    <button type="button" 
                            @click="showNewStudentForm = !showNewStudentForm" 
                            class="bg-primary-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 transition-colors hover:bg-primary-500">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                        </svg>
                        + Siswa Baru
                    </button>
                    @endif
                    
                    <div class="relative w-36 sm:w-48">
                        <input type="text" 
                               wire:model.live.debounce.300ms="searchRight" 
                               placeholder="Cari siswa..." 
                               class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-950 dark:text-white py-1.5 pl-3 pr-8 text-sm" />
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Info konteks filter kandidat --}}
            <div class="mb-3 px-3 py-2 rounded-lg bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20 text-[0.68rem] text-primary-700 dark:text-primary-300 leading-relaxed">
                @if($previousYear)
                    Menampilkan: <strong>siswa baru (PPDB)</strong> + siswa kelas
                    @if($targetGradeLevel > 7)
                        <strong>{{ $targetGradeLevel - 1 }} &amp; {{ $targetGradeLevel }}</strong>
                    @else
                        <strong>{{ $targetGradeLevel }}</strong>
                    @endif
                    TA {{ $previousYear->name }} yang belum memiliki kelas di TA {{ $academicYear->name ?? '' }}.
                @else
                    Menampilkan seluruh siswa aktif yang belum memiliki kelas di TA {{ $academicYear->name ?? '' }}.
                @endif
            </div>

            @if($canEdit)
            <!-- Form Pop-down Tambah Siswa Baru (Livewire Inline) -->
            <div x-show="showNewStudentForm" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="mb-3 p-3 bg-white dark:bg-white/10 border border-primary-300 dark:border-primary-500/30 rounded-xl shadow-md">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-primary-700 dark:text-primary-300">Pendaftaran Cepat Siswa Baru</span>
                    <button type="button" @click="showNewStudentForm = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                        <input type="text" wire:model="newStudentName" placeholder="Nama Lengkap Siswa" class="block w-full text-xs rounded-md border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-2.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                        @error('newStudentName') <span class="text-[0.65rem] text-danger-600 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <input type="text" wire:model="newStudentNisn" placeholder="NISN (10 Digit)" class="block w-full text-xs rounded-md border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-2.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                        @error('newStudentNisn') <span class="text-[0.65rem] text-danger-600 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <select wire:model="newStudentGender" class="block w-full text-xs rounded-md border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        <button type="button" @click="showNewStudentForm = false" class="bg-transparent border-none text-[0.7rem] font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 cursor-pointer px-2 py-1">Batal</button>
                        <button type="button" wire:click="registerNewStudent" class="bg-primary-600 text-white px-3 py-1 rounded-md text-xs font-bold transition-colors hover:bg-primary-500">Simpan</button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Scrollable Area -->
            <div class="flex-1 overflow-y-auto border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-white/5 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-thumb]:rounded-full pr-[2px]">
                <template x-if="rightStudents.length > 0">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900/90 backdrop-blur z-10 shadow-sm">
                            <tr>
                                <template x-if="canEdit">
                                    <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider w-[50px]">Aksi</th>
                                </template>

                                {{-- Header: Nama Siswa (sortable) --}}
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                                    @click="toggleRight('name')">
                                    <span class="inline-flex items-center gap-1">
                                        Nama Siswa
                                        <span x-show="rightSortBy === 'name'" x-cloak>
                                            <svg x-show="rightSortDir === 'asc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                            <svg x-show="rightSortDir === 'desc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </span>
                                        <span x-show="rightSortBy !== 'name'" x-cloak class="opacity-30">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                                        </span>
                                    </span>
                                </th>

                                {{-- Header: NISN (sortable) --}}
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                                    @click="toggleRight('nisn')">
                                    <span class="inline-flex items-center gap-1">
                                        NISN
                                        <span x-show="rightSortBy === 'nisn'" x-cloak>
                                            <svg x-show="rightSortDir === 'asc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                            <svg x-show="rightSortDir === 'desc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </span>
                                        <span x-show="rightSortBy !== 'nisn'" x-cloak class="opacity-30">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                                        </span>
                                    </span>
                                </th>

                                {{-- Header: Kelas Sblm (sortable) --}}
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                                    @click="toggleRight('kelasSebelumnya')">
                                    <span class="inline-flex items-center gap-1">
                                        Kelas Sblm
                                        <span x-show="rightSortBy === 'kelasSebelumnya'" x-cloak>
                                            <svg x-show="rightSortDir === 'asc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                            <svg x-show="rightSortDir === 'desc'" class="w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </span>
                                        <span x-show="rightSortBy !== 'kelasSebelumnya'" x-cloak class="opacity-30">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                                        </span>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="siswa in rightSorted" :key="siswa.id">
                                <tr :draggable="canEdit"
                                    @dragstart="if (canEdit) $event.dataTransfer.setData('student_id', siswa.id)"
                                    class="border-b border-gray-200 dark:border-white/10 transition-colors hover:bg-gray-50 dark:hover:bg-white/5 cursor-grab active:cursor-grabbing">

                                    {{-- Aksi --}}
                                    <template x-if="canEdit">
                                        <td class="px-3 py-2.5 text-sm align-middle text-center">
                                            <button type="button"
                                                    @click="enroll(siswa.id)"
                                                    title="Masukkan ke kelas"
                                                    class="p-1 rounded-md transition-colors inline-flex items-center justify-center border border-transparent bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-500">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                                </svg>
                                            </button>
                                        </td>
                                    </template>

                                    {{-- Nama Siswa --}}
                                    <td class="px-3 py-2.5 text-sm align-middle text-gray-900 dark:text-white font-medium">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>
                                            <span x-text="siswa.name"></span>
                                        </span>
                                    </td>

                                    {{-- NISN --}}
                                    <td class="px-3 py-2.5 text-sm align-middle text-gray-500 dark:text-gray-400 font-mono text-xs" x-text="siswa.nisn"></td>

                                    {{-- Kelas Sebelumnya --}}
                                    <td class="px-3 py-2.5 text-sm align-middle text-center">
                                        <template x-if="siswa.kelasSebelumnya">
                                            <span class="px-2 py-0.5 rounded-full text-[0.65rem] font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/20"
                                                  x-text="siswa.kelasSebelumnya"></span>
                                        </template>
                                        <template x-if="!siswa.kelasSebelumnya">
                                            <span class="text-gray-400 dark:text-gray-600 text-xs">—</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
                <template x-if="rightStudents.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-11 h-11 text-gray-500 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-bold text-gray-400 dark:text-gray-500 m-0">Tidak Ada Kandidat</p>
                        <p class="text-[0.65rem] text-gray-500 dark:text-gray-500 max-w-[220px] mt-1">Semua siswa yang relevan sudah terdaftar di kelas untuk tahun ajaran ini.</p>
                    </div>
                </template>
            </div>
        </div>

    </div>
</div>
