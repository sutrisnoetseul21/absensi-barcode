@php
    $canEdit = (bool) (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor') || auth()->user()?->hasRole('admin_akademik') || true);
@endphp

<div class="space-y-6" x-data="{ 
    canEdit: {{ $canEdit ? 'true' : 'false' }},
    isSubmitting: false,
    searchLeft: '',
    tahunMasukBulk: {{ date('Y') }},
    selectedIds: [],

    matchesLeftSearch(text) {
        if (!this.searchLeft.trim()) return true;
        return text.includes(this.searchLeft.toLowerCase().trim());
    },

    isAllCandidatesSelected() {
        let cbs = this.$refs.candidateList ? this.$refs.candidateList.querySelectorAll('input[type=checkbox]') : [];
        return cbs.length > 0 && this.selectedIds.length >= cbs.length;
    },

    toggleSelectAll(checked) {
        if (checked) {
            let cbs = this.$refs.candidateList ? this.$refs.candidateList.querySelectorAll('input[type=checkbox]') : [];
            this.selectedIds = Array.from(cbs).map(cb => cb.value);
        } else {
            this.selectedIds = [];
        }
    },

    async submitBulk() {
        if (!this.canEdit || this.selectedIds.length === 0 || this.isSubmitting) return;
        this.isSubmitting = true;
        try {
            await $wire.tambahAnggotaBulk(this.selectedIds, parseInt(this.tahunMasukBulk) || {{ date('Y') }}, '{{ $kelompok->id }}');
            this.selectedIds = [];
        } catch (e) {
            console.error(e);
        } finally {
            this.isSubmitting = false;
        }
    },

    async keluarkan(anggotaId, namaSiswa) {
        if (!this.canEdit || this.isSubmitting) return;
        if (!confirm('Keluarkan ' + namaSiswa + ' dari kelompok dampingan ini?\n(Data keanggotaan akan diarsipkan)')) {
            return;
        }

        this.isSubmitting = true;
        try {
            await $wire.keluarkanAnggota(anggotaId);
        } catch (e) {
            console.error(e);
        } finally {
            this.isSubmitting = false;
        }
    }
}">

    <!-- Header Summary -->
    <div class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl flex justify-between items-center flex-wrap gap-4 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Kelola Anggota: {{ $kelompok->nama_kelompok }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Guru Wali: <strong class="text-gray-700 dark:text-gray-200">{{ $kelompok->guru?->name ?? '—' }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-success-50 dark:bg-success-500/10 text-success-600 dark:text-success-400 border border-success-200 dark:border-success-500/20">
                <span>{{ count($leftStudents) }}</span> Anggota Aktif
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-500/20">
                <span>{{ count($rightStudents) }}</span> Kandidat Tersedia
            </span>
        </div>
    </div>

    <!-- Dual Pane Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
        
        <!-- PANEL KIRI: Anggota Aktif Kelompok -->
        <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-5 flex flex-col h-[62vh] min-h-[520px] shadow-sm">
            
            <div class="flex justify-between items-center mb-4 gap-4 flex-wrap">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-success-500"></span>
                    Anggota Kelompok ( {{ count($leftStudents) }} )
                </h4>
                <div class="relative w-48">
                    <input type="text" 
                           x-model="searchLeft" 
                           placeholder="Cari nama / NISN..." 
                           class="block w-full rounded-lg shadow-sm border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-950 dark:text-white py-1.5 pl-3 pr-8 text-xs focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Scrollable Table Left -->
            <div class="flex-1 overflow-y-auto border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-white/5 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-thumb]:rounded-full pr-[2px]">
                @if(count($leftStudents) > 0)
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900/90 backdrop-blur z-10 shadow-sm">
                            <tr>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider">Nama Siswa</th>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider">NISN</th>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider text-center">Th Masuk</th>
                                @if($canEdit)
                                    <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider text-right w-[60px]">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leftStudents as $item)
                                <tr wire:key="left-{{ $item->id }}"
                                    x-show="matchesLeftSearch('{{ strtolower(addslashes(($item->siswa?->name ?? '') . ' ' . ($item->siswa?->nisn ?? '') . ' ' . ($item->siswa?->nis ?? ''))) }}')"
                                    class="border-b border-gray-200 dark:border-white/10 transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                                    {{-- Nama Siswa --}}
                                    <td class="px-3 py-2.5 text-xs align-middle text-gray-900 dark:text-white font-medium">
                                        {{ $item->siswa?->name ?? '—' }}
                                    </td>

                                    {{-- NISN --}}
                                    <td class="px-3 py-2.5 text-xs align-middle text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $item->siswa?->nisn ?? '—' }}
                                    </td>

                                    {{-- Tahun Masuk --}}
                                    <td class="px-3 py-2.5 text-xs align-middle text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[0.65rem] font-semibold bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10">
                                            {{ $item->tahun_masuk }}
                                        </span>
                                    </td>

                                    {{-- Aksi: Keluarkan --}}
                                    @if($canEdit)
                                        <td class="px-3 py-2.5 text-xs align-middle text-right">
                                            <button type="button"
                                                    @click="keluarkan('{{ $item->id }}', '{{ addslashes($item->siswa?->name ?? 'Siswa') }}')"
                                                    title="Keluarkan (Arsipkan) dari kelompok"
                                                    class="p-1 rounded-md transition-colors inline-flex items-center justify-center border border-transparent bg-danger-50 dark:bg-danger-500/10 text-danger-600 dark:text-danger-400 hover:bg-danger-600 hover:text-white dark:hover:bg-danger-500 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                                </svg>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-11 h-11 text-gray-400 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-sm font-bold text-gray-400 dark:text-gray-500 m-0">Belum Ada Anggota</p>
                        <p class="text-[0.7rem] text-gray-500 dark:text-gray-500 max-w-[220px] mt-1">Centang siswa dari panel kanan lalu klik tombol "Tambahkan yang Dipilih".</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- PANEL KANAN: Kandidat Siswa -->
        <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-5 flex flex-col h-[62vh] min-h-[520px] shadow-sm">
            
            {{-- Header Panel Kanan: Title & Filters --}}
            <div class="space-y-3 mb-3">
                <div class="flex justify-between items-center gap-4 flex-wrap">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                        Kandidat Siswa ( {{ count($rightStudents) }} )
                    </h4>

                    {{-- Input Tahun Masuk Kolektif --}}
                    <div class="flex items-center gap-2 bg-white dark:bg-white/5 px-2.5 py-1 rounded-lg border border-gray-200 dark:border-white/10 shadow-xs">
                        <label class="text-[0.7rem] font-bold text-gray-600 dark:text-gray-300 uppercase shrink-0">Th Masuk:</label>
                        <input type="number" 
                               x-model="tahunMasukBulk"
                               min="2000" 
                               max="2100" 
                               class="w-20 text-xs font-bold rounded border-gray-300 dark:border-white/10 bg-transparent text-gray-900 dark:text-white py-0.5 px-1.5 focus:border-primary-500 focus:ring-0 text-center" />
                    </div>
                </div>

                {{-- Filter Bar: Dropdown Kelas & Search Box --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {{-- Dropdown Filter Kelas --}}
                    <div>
                        <select wire:model.live="filterKelasId" 
                                @change="selectedIds = []"
                                class="block w-full text-xs rounded-lg border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-2.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 shadow-xs">
                            <option value="">Semua Kelas</option>
                            <option value="none">Belum Punya Kelas (PPDB)</option>
                            @php
                                $groupedKelas = $daftarKelas->groupBy('grade_level');
                            @endphp
                            @foreach($groupedKelas as $grade => $kelasList)
                                <optgroup label="Tingkat {{ $grade }}">
                                    @foreach($kelasList as $k)
                                        <option value="{{ $k->id }}">Kelas {{ $k->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    {{-- Search Box --}}
                    <div class="relative">
                        <input type="text" 
                               wire:model.live.debounce.300ms="searchKandidat" 
                               @input="selectedIds = []"
                               placeholder="Cari nama / NISN..." 
                               class="block w-full rounded-lg border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white py-1.5 pl-3 pr-8 text-xs focus:border-primary-500 focus:ring-1 focus:ring-primary-500 shadow-xs" />
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Loading State Indicator --}}
            <div wire:loading.flex wire:target="filterKelasId, searchKandidat" class="flex-1 flex-col items-center justify-center p-8 text-gray-500 dark:text-gray-400">
                <svg class="animate-spin w-8 h-8 text-primary-600 mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-medium">Menyaring data kandidat...</span>
            </div>

            <!-- Scrollable Table Right -->
            <div wire:loading.remove wire:target="filterKelasId, searchKandidat" class="flex-1 overflow-y-auto border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-white/5 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-thumb]:rounded-full pr-[2px]">
                @if(count($rightStudents) > 0)
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900/90 backdrop-blur z-10 shadow-sm">
                            <tr>
                                @if($canEdit)
                                     <th class="px-3 py-2 text-xs font-bold uppercase border-b border-gray-200 dark:border-white/10 w-9 text-center">
                                         <input type="checkbox" 
                                                @change="toggleSelectAll($event.target.checked)"
                                                :checked="isAllCandidatesSelected()"
                                                title="Pilih semua"
                                                class="rounded border-gray-300 dark:border-white/20 text-primary-600 focus:ring-primary-500 cursor-pointer">
                                     </th>
                                 @endif

                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider">Nama Siswa</th>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider">NISN</th>
                                <th class="px-3 py-2 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10 tracking-wider text-center">Kelas</th>
                            </tr>
                        </thead>
                        <tbody x-ref="candidateList">
                            @foreach($rightStudents as $siswa)
                                <tr wire:key="cand-{{ $siswa->id }}"
                                    class="border-b border-gray-200 dark:border-white/10 transition-colors hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer select-none"
                                    @click="if (canEdit) { 
                                        const val = '{{ $siswa->id }}';
                                        const idx = selectedIds.indexOf(val);
                                        if (idx === -1) selectedIds.push(val); else selectedIds.splice(idx, 1);
                                    }">
                                    
                                    {{-- Checkbox --}}
                                    @if($canEdit)
                                        <td class="px-3 py-2.5 text-xs align-middle text-center w-9" @click.stop>
                                            <input type="checkbox" 
                                                   value="{{ $siswa->id }}"
                                                   x-model="selectedIds"
                                                   class="rounded border-gray-300 dark:border-white/20 text-primary-600 focus:ring-primary-500 cursor-pointer">
                                        </td>
                                    @endif

                                    {{-- Nama Siswa --}}
                                    <td class="px-3 py-2.5 text-xs align-middle text-gray-900 dark:text-white font-medium">
                                        {{ $siswa->name }}
                                    </td>

                                    {{-- NISN --}}
                                    <td class="px-3 py-2.5 text-xs align-middle text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $siswa->nisn ?? '—' }}
                                    </td>

                                    {{-- Kelas --}}
                                    <td class="px-3 py-2.5 text-xs align-middle text-center">
                                        @if($siswa->enrollmentAktif?->kelas)
                                            <span class="px-2 py-0.5 rounded-full text-[0.65rem] font-semibold bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-500/20">
                                                {{ $siswa->enrollmentAktif->kelas->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-600 text-[0.68rem] italic">Tanpa Kelas</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-11 h-11 text-gray-400 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-bold text-gray-400 dark:text-gray-500 m-0">Tidak Ada Kandidat</p>
                        <p class="text-[0.7rem] text-gray-500 dark:text-gray-500 max-w-[220px] mt-1">Semua siswa yang sesuai filter sudah memiliki kelompok Guru Wali aktif.</p>
                    </div>
                @endif
            </div>

            {{-- Footer Panel Kanan: Submit Action --}}
            <div class="pt-3 mt-2 border-t border-gray-200 dark:border-white/10 flex justify-between items-center">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <span class="font-bold text-gray-900 dark:text-white" x-text="selectedIds.length"></span> siswa dipilih
                </div>
                <button type="button"
                        @click="submitBulk()"
                        :disabled="selectedIds.length === 0 || isSubmitting"
                        :class="selectedIds.length === 0 || isSubmitting ? 'opacity-50 cursor-not-allowed bg-gray-400 dark:bg-gray-700' : 'bg-primary-600 hover:bg-primary-500 cursor-pointer shadow-sm active:scale-98'"
                        class="text-white px-4 py-2 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                    </svg>
                    <span x-text="'Tambahkan yang Dipilih (' + selectedIds.length + ')'"></span>
                </button>
            </div>

        </div>

    </div>
</div>
