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
            <div class="px-6 py-6 max-h-[65vh] overflow-y-auto custom-scrollbar [touch-action:pan-y] [-webkit-overflow-scrolling:touch] space-y-6">
                <!-- Date Picker & Holiday Notice -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <label class="font-bold text-xs uppercase tracking-wider text-slate-600 whitespace-nowrap">Tanggal:</label>
                        <input type="date" wire:model.live="inputDate" class="block w-full sm:w-auto px-3 py-2 text-sm text-slate-800 bg-white border border-slate-200 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-xs">
                    </div>
                    
                    @if($isInputDateHoliday)
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>Hari Ini Libur Sholat: {{ $inputDateHolidayDesc ?: 'Jumat / Hari Libur' }}</span>
                        </div>
                    @else
                        <div class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200">
                            Hari Efektif Sholat Dhuhur Berjamaah
                        </div>
                    @endif
                </div>

                <!-- Fast Actions Bar -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi Cepat:</span>
                        <button type="button" wire:click="setAllStatus('hadir')" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                            ⚡ Hadir Semua
                        </button>
                        <button type="button" wire:click="setAllStatus('ijin')" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">
                            Ijin Semua
                        </button>
                        <button type="button" wire:click="setAllStatus('tidak_hadir')" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors">
                            Alpa Semua
                        </button>
                    </div>

                    <button type="button" wire:click="tarikDariPresensiPagi" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 transition-colors" title="Ambil status siswa dari presensi pagi: Sakit/Izin jadi Ijin, Alpa jadi Tidak Hadir">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Sinkron Presensi Pagi
                    </button>
                </div>

                <!-- Students List Table -->
                @if(count($inputStudents) > 0)
                    <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-xs">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100/80 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <th class="py-3 px-4 w-5/12">Nama Siswa</th>
                                    <th class="py-3 px-4 text-center w-4/12">Status Kehadiran</th>
                                    <th class="py-3 px-4 w-3/12">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($inputStudents as $index => $data)
                                    <tr class="hover:bg-slate-50/80 transition-colors" wire:key="sholat-st-{{ $data['id'] }}">
                                        <!-- Nama Siswa & Info -->
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center font-bold text-[11px] text-slate-600 shrink-0">
                                                    {{ strtoupper(substr($data['name'], 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="font-bold text-slate-800 truncate block">{{ $data['name'] }}</span>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-[10px] font-semibold {{ ($data['gender'] ?? '') === 'P' ? 'text-pink-600' : 'text-blue-600' }}">
                                                            {{ ($data['gender'] ?? '') === 'P' ? 'Perempuan' : 'Laki-laki' }}
                                                        </span>
                                                        @if(!empty($data['is_pagi_absent']))
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">
                                                                Sinkron Pagi
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Status Radio / Buttons -->
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <!-- Hadir -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" wire:model.live="inputStudents.{{ $index }}.status" value="hadir" class="sr-only peer">
                                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all inline-block border
                                                        peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600
                                                        bg-slate-50 text-slate-600 border-slate-200 hover:bg-emerald-50">
                                                        Hadir
                                                    </span>
                                                </label>

                                                <!-- Ijin -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" wire:model.live="inputStudents.{{ $index }}.status" value="ijin" class="sr-only peer">
                                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all inline-block border
                                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                                        bg-slate-50 text-slate-600 border-slate-200 hover:bg-blue-50">
                                                        Ijin
                                                    </span>
                                                </label>

                                                <!-- Alpa -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" wire:model.live="inputStudents.{{ $index }}.status" value="tidak_hadir" class="sr-only peer">
                                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all inline-block border
                                                        peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600
                                                        bg-slate-50 text-slate-600 border-slate-200 hover:bg-rose-50">
                                                        Alpa
                                                    </span>
                                                </label>

                                                <!-- Khusus Siswi: Tombol Haid / Halangan -->
                                                @if(($data['gender'] ?? '') === 'P')
                                                    <button type="button" wire:click="toggleHalangan({{ $index }})" 
                                                            class="px-2 py-1 rounded-lg text-[11px] font-bold border transition-all inline-flex items-center gap-1
                                                            {{ !empty($data['is_halangan']) ? 'bg-pink-600 text-white border-pink-600 shadow-xs' : 'bg-pink-50 text-pink-700 border-pink-200 hover:bg-pink-100' }}"
                                                            title="Klik untuk menandai siswi sedang berhalangan / haid">
                                                        <span>🩸</span> Haid
                                                    </button>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Keterangan -->
                                        <td class="py-3 px-4">
                                            <input type="text" 
                                                   wire:model="inputStudents.{{ $index }}.keterangan" 
                                                   placeholder="Catatan..." 
                                                   class="block w-full px-2.5 py-1 text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400">
                        Tidak ada siswa untuk diinput.
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200/80 flex items-center justify-end gap-3">
                <button type="button" @click="showInputModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="button" wire:click="saveInput" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Simpan Presensi
                </button>
            </div>
        </div>
    </div>
</div>
