<!-- Modal Input Presensi Sholat Dhuhur -->
<div x-show="showInputModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Backdrop -->
        <div x-show="showInputModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" 
             aria-hidden="true" 
             @click="showInputModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div x-show="showInputModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-white/20">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 px-6 sm:px-8 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl backdrop-blur-sm flex items-center justify-center text-white shadow-inner">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">Input Presensi Sholat Dhuhur</h3>
                        <p class="text-xs text-emerald-100">Pencatatan Kehadiran Ibadah Berjamaah Siswa</p>
                    </div>
                </div>
                <button @click="showInputModal = false" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all focus:outline-none">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-4 sm:px-6 py-6 max-h-[65vh] overflow-y-auto [-webkit-overflow-scrolling:touch]">
                <!-- Date Picker & Sync -->
                <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <label class="font-bold text-slate-800 whitespace-nowrap text-sm">Pilih Tanggal:</label>
                        <input type="date" wire:model.live="inputDate" class="block w-full sm:w-auto px-3 py-2 text-slate-800 border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 sm:text-sm rounded-xl shadow-xs font-semibold bg-white">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        @if($isInputDateHoliday)
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <span>Hari Libur / Jumat: {{ $inputDateHolidayDesc ?: 'Libur Sholat' }}</span>
                            </div>
                        @else
                            <button type="button" wire:click="tarikDariPresensiPagi" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 transition-colors shadow-2xs" title="Ambil status siswa dari presensi pagi: Sakit/Izin jadi Ijin, Alpa jadi Tidak Hadir">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                Sinkron Presensi Pagi
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Students List Table (Mirip format portal-guru/akademik) -->
                @if(count($inputStudents) > 0)
                    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm custom-scrollbar [-webkit-overflow-scrolling:touch]">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100 text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <th class="py-3 px-4 w-1/3">Nama Siswa</th>
                                    <th class="py-3 px-4 text-center w-1/3">
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span>Status</span>
                                            <select wire:model.live="bulkStatus" wire:change="applyBulkStatus($event.target.value)" class="text-xs font-bold py-1 px-2.5 rounded-lg border border-emerald-300 bg-white text-slate-800 focus:ring-2 focus:ring-emerald-500 cursor-pointer shadow-2xs normal-case">
                                                <option value="">-- Set Massal --</option>
                                                <option value="hadir">⚡ Hadir Semua</option>
                                                <option value="ijin">Izin Semua</option>
                                                <option value="tidak_hadir">Alpa Semua</option>
                                            </select>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 w-1/3">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($inputStudents as $index => $data)
                                    @if(!empty($data['is_non_muslim']))
                                        {{-- Row Non-Muslim: read-only, tidak bisa diinput --}}
                                        <tr class="bg-indigo-50/60 border-l-4 border-indigo-300" wire:key="sholat-st-{{ $data['id'] }}">
                                            <td class="py-3 px-4 font-bold text-slate-600">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-[11px] text-indigo-600 shrink-0">
                                                        {{ strtoupper(substr($data['name'], 0, 1)) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <span class="font-bold text-slate-700 truncate block text-xs" title="{{ $data['name'] }}">{{ $data['name'] }}</span>
                                                        <div class="flex items-center gap-1.5 mt-0.5">
                                                            <span class="text-[10px] font-semibold {{ ($data['gender'] ?? '') === 'P' ? 'text-pink-600' : 'text-blue-600' }}">
                                                                {{ ($data['gender'] ?? '') === 'P' ? 'Perempuan' : 'Laki-laki' }}
                                                            </span>
                                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                                                Non-Muslim
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-600 border border-indigo-200">
                                                    {{ $data['religion'] ?? 'Non-Muslim' }} — Tidak Wajib
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-xs text-indigo-500 italic">
                                                Tidak diwajibkan presensi sholat dhuhur
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="hover:bg-slate-50 transition-colors" wire:key="sholat-st-{{ $data['id'] }}">
                                            <!-- Nama Siswa -->
                                            <td class="py-3 px-4 font-bold text-slate-800">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center font-bold text-[11px] text-slate-600 shrink-0">
                                                        {{ strtoupper(substr($data['name'], 0, 1)) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <span class="font-bold text-slate-800 truncate block text-xs" title="{{ $data['name'] }}">{{ $data['name'] }}</span>
                                                        <div class="flex items-center gap-1.5 mt-0.5">
                                                            <span class="text-[10px] font-semibold {{ ($data['gender'] ?? '') === 'P' ? 'text-pink-600' : 'text-blue-600' }}">
                                                                {{ ($data['gender'] ?? '') === 'P' ? 'Perempuan' : 'Laki-laki' }}
                                                            </span>
                                                            @if(!empty($data['is_pagi_absent']))
                                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800">
                                                                    Sinkron Pagi
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Status Dropdown (Persis format portal-guru/akademik) -->
                                            <td class="py-3 px-4 text-center">
                                                <select wire:model.live="inputStudents.{{ $index }}.status" class="block w-full pl-3 pr-8 py-1.5 text-xs font-bold rounded-lg border-slate-200 focus:ring-2 focus:ring-emerald-500 cursor-pointer shadow-sm transition-colors
                                                    {{ empty($data['status']) ? 'text-slate-400 bg-white border-slate-200' : '' }}
                                                    {{ ($data['status'] ?? '') === 'hadir' ? 'text-emerald-700 bg-emerald-50 border-emerald-300' : '' }}
                                                    {{ ($data['status'] ?? '') === 'ijin' ? 'text-blue-700 bg-blue-50 border-blue-300' : '' }}
                                                    {{ ($data['status'] ?? '') === 'tidak_hadir' ? 'text-red-700 bg-red-50 border-red-300' : '' }}
                                                    {{ ($data['status'] ?? '') === 'haid' ? 'text-pink-700 bg-pink-50 border-pink-300' : '' }}
                                                ">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="hadir">Hadir</option>
                                                    <option value="ijin">Izin</option>
                                                    <option value="tidak_hadir">Alpa</option>
                                                    @if(($data['gender'] ?? '') === 'P')
                                                        <option value="haid">🩸 Halangan / Haid</option>
                                                    @endif
                                                </select>
                                            </td>

                                            <!-- Keterangan -->
                                            <td class="py-3 px-4">
                                                <input type="text" 
                                                       wire:model="inputStudents.{{ $index }}.keterangan" 
                                                       placeholder="Catatan..." 
                                                       class="block w-full px-2.5 py-1.5 text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 bg-slate-50 rounded-xl border border-slate-200">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <p class="mt-2 text-slate-500 font-semibold">Tidak ada data siswa untuk diinput.</p>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex-1 w-full text-left">
                    @if($isInputDateHoliday)
                        <span class="inline-flex items-center text-xs font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-1.5 w-full sm:w-auto">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Input ditolak: Tanggal ini adalah hari libur / Jumat
                        </span>
                    @endif
                </div>
                <div class="flex gap-3 w-full sm:w-auto justify-end mt-3 sm:mt-0">
                    <button type="button" @click="showInputModal = false" class="inline-flex justify-center px-4 py-2 border border-slate-300 shadow-sm text-xs font-bold rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition-colors">
                        Batal
                    </button>
                    <button type="button" wire:click="saveInput" {{ $isInputDateHoliday ? 'disabled' : '' }} class="inline-flex justify-center items-center px-5 py-2 border border-transparent shadow-sm text-xs font-bold rounded-xl text-white {{ $isInputDateHoliday ? 'bg-slate-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30' }} focus:outline-none transition-all">
                        <svg wire:loading wire:target="saveInput" class="animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Simpan Presensi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
