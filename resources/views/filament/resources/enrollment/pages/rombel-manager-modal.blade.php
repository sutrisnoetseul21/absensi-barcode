<div class="space-y-6" x-data="{ 
    showNewStudentForm: false,
    draggedStudentId: null
}">

    <!-- Header Summary -->
    <div class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl flex justify-between items-center flex-wrap gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">Manajemen Rombel: Kelas {{ $kelas->name }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tahun Ajaran: <strong class="text-gray-700 dark:text-gray-200">{{ $academicYear->name ?? '—' }}</strong></p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-success-50 dark:bg-success-500/10 text-success-600 dark:text-success-400 border border-success-200 dark:border-success-500/20">
                {{ count($leftStudents) }} Siswa Terdaftar
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-500/20">
                {{ count($rightStudents) }} Siswa Tanpa Kelas
            </span>
        </div>
    </div>

    <!-- Dual Pane Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
        
        <!-- PANEL KIRI: Anggota Kelas -->
        <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-5 flex flex-col h-[60vh] min-h-[500px] shadow-sm"
             @dragover.prevent
             @drop="let id = event.dataTransfer.getData('student_id'); if (id) $wire.enrollStudent(id, '{{ $kelas->id }}', '{{ $academicYear->id ?? '' }}')">
            
            <div class="flex justify-between items-center mb-4 gap-4 flex-wrap">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-success-500"></span>
                    Siswa Kelas Ini ({{ count($leftStudents) }})
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
                @if(count($leftStudents) > 0)
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900/90 backdrop-blur z-10 shadow-sm">
                            <tr>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider">Nama Siswa</th>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider">NISN</th>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leftStudents as $siswa)
                                <tr draggable="true" 
                                    @dragstart="event.dataTransfer.setData('student_id', '{{ $siswa->id }}')"
                                    class="border-b border-gray-200 dark:border-white/10 transition-colors hover:bg-gray-50 dark:hover:bg-white/5 cursor-grab active:cursor-grabbing">
                                    <td class="px-3 py-2.5 text-sm align-middle text-gray-900 dark:text-white font-medium flex items-center gap-2">
                                        <!-- Drag Indicator -->
                                        <svg class="w-3.5 h-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        {{ $siswa->name }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm align-middle text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $siswa->nisn }}</td>
                                    <td class="px-3 py-2.5 text-sm align-middle text-right">
                                        <button type="button" 
                                                x-on:click="if (confirm('Apakah Anda yakin ingin mengeluarkan {{ addslashes($siswa->name) }} dari rombel kelas ini?')) { $wire.unenrollStudent('{{ $siswa->id }}', '{{ $academicYear->id ?? '' }}') }" 
                                                title="Keluarkan dari kelas"
                                                class="p-1 rounded-md transition-colors inline-flex items-center justify-center border border-transparent bg-danger-50 dark:bg-danger-500/10 text-danger-600 dark:text-danger-400 hover:bg-danger-600 hover:text-white dark:hover:bg-danger-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-11 h-11 text-gray-500 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <p class="text-sm font-bold text-gray-400 dark:text-gray-500 m-0">Belum Ada Anggota</p>
                        <p class="text-[0.65rem] text-gray-500 dark:text-gray-500 max-w-[220px] mt-1">Tarik siswa dari panel kanan ke sini atau klik tombol panah untuk memasukkan anggota.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- PANEL KANAN: Siswa Tanpa Kelas -->
        <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-5 flex flex-col h-[60vh] min-h-[500px] shadow-sm"
             @dragover.prevent
             @drop="let id = event.dataTransfer.getData('student_id'); if (id && confirm('Apakah Anda yakin ingin mengeluarkan siswa dari rombel kelas ini?')) { $wire.unenrollStudent(id, '{{ $academicYear->id ?? '' }}') }">
            
            <div class="flex justify-between items-center mb-4 gap-4 flex-wrap">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                    Siswa Tanpa Kelas ({{ count($rightStudents) }})
                </h4>
                <div class="flex items-center gap-2">
                    <!-- Button Tambah Siswa Baru (Buka sub form) -->
                    <button type="button" 
                            @click="showNewStudentForm = !showNewStudentForm" 
                            class="bg-primary-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 transition-colors hover:bg-primary-500">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                        </svg>
                        + Siswa Baru
                    </button>
                    
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

            <!-- Collapsible Form Tambah Siswa Baru -->
            <div x-show="showNewStudentForm" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="mb-4 p-4 border border-gray-200 dark:border-white/10 rounded-lg bg-gray-100 dark:bg-white/5 flex flex-col gap-3 shadow-inner">
                <h5 class="text-[0.7rem] font-bold text-gray-900 dark:text-white uppercase m-0 pb-1">Daftar Siswa Baru (Masuk ke Sisi Kanan)</h5>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" wire:model="newStudentName" class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 border-gray-300 dark:border-white/10 bg-white dark:bg-gray-900 text-gray-950 dark:text-white py-1.5 px-3 text-sm" />
                        @error('newStudentName') <span class="text-[0.65rem] text-danger-500 block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">NISN</label>
                        <input type="text" wire:model="newStudentNisn" class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 border-gray-300 dark:border-white/10 bg-white dark:bg-gray-900 text-gray-950 dark:text-white py-1.5 px-3 text-sm font-mono" />
                        @error('newStudentNisn') <span class="text-[0.65rem] text-danger-500 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-between items-center mt-1 flex-wrap gap-2">
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center cursor-pointer text-[0.7rem] text-gray-600 dark:text-gray-400">
                            <input type="radio" wire:model="newStudentGender" value="L" class="border-gray-300 dark:border-white/10 bg-white dark:bg-gray-900 text-primary-600 focus:ring-primary-600" />
                            <span class="ml-1.5">Laki-laki</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer text-[0.7rem] text-gray-600 dark:text-gray-400">
                            <input type="radio" wire:model="newStudentGender" value="P" class="border-gray-300 dark:border-white/10 bg-white dark:bg-gray-900 text-primary-600 focus:ring-primary-600" />
                            <span class="ml-1.5">Perempuan</span>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="showNewStudentForm = false" class="bg-transparent border-none text-[0.7rem] font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 cursor-pointer px-2 py-1">Batal</button>
                        <button type="button" wire:click="registerNewStudent" class="bg-primary-600 text-white px-3 py-1 rounded-md text-xs font-bold transition-colors hover:bg-primary-500">Simpan</button>
                    </div>
                </div>
            </div>

            <!-- Scrollable Area -->
            <div class="flex-1 overflow-y-auto border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-white/5 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-thumb]:rounded-full pr-[2px]">
                @if(count($rightStudents) > 0)
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900/90 backdrop-blur z-10 shadow-sm">
                            <tr>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider w-[50px]">Aksi</th>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider">Nama Siswa</th>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider">NISN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rightStudents as $siswa)
                                <tr draggable="true" 
                                    @dragstart="event.dataTransfer.setData('student_id', '{{ $siswa->id }}')"
                                    class="border-b border-gray-200 dark:border-white/10 transition-colors hover:bg-gray-50 dark:hover:bg-white/5 cursor-grab active:cursor-grabbing">
                                    <td class="px-3 py-2.5 text-sm align-middle text-center">
                                        <button type="button" 
                                                wire:click="enrollStudent('{{ $siswa->id }}', '{{ $kelas->id }}', '{{ $academicYear->id ?? '' }}')" 
                                                title="Masukkan ke kelas"
                                                class="p-1 rounded-md transition-colors inline-flex items-center justify-center border border-transparent bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-3 py-2.5 text-sm align-middle text-gray-900 dark:text-white font-medium flex items-center gap-2">
                                        <!-- Drag Indicator -->
                                        <svg class="w-3.5 h-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        {{ $siswa->name }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm align-middle text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $siswa->nisn }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-11 h-11 text-gray-500 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-bold text-gray-400 dark:text-gray-500 m-0">Tidak Ada Siswa</p>
                        <p class="text-[0.65rem] text-gray-500 dark:text-gray-500 max-w-[220px] mt-1">Semua siswa aktif sudah tuntas terdaftar dalam rombel untuk tahun ajaran terpilih ini.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
