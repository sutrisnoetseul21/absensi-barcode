<!-- Modal Cetak Laporan Sholat Dhuhur -->
<div x-show="showCetakModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Backdrop -->
        <div x-show="showCetakModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" 
             aria-hidden="true" 
             @click="showCetakModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div x-show="showCetakModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-white/20">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 sm:px-8 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl backdrop-blur-sm flex items-center justify-center text-white shadow-inner">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">Cetak Laporan Sholat Dhuhur</h3>
                        <p class="text-xs text-emerald-100">Ekspor Laporan Resmi Format PDF Landscape</p>
                    </div>
                </div>
                <button @click="showCetakModal = false" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all focus:outline-none">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6 space-y-6">
                <!-- Jenis Laporan Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Format Laporan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="cetakJenis" value="bulanan" class="sr-only peer">
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 transition-all text-center">
                                <span class="block text-sm font-bold text-slate-800 peer-checked:text-emerald-800">Bulanan</span>
                                <span class="block text-[11px] text-slate-400 mt-0.5">Matriks Harian Lengkap</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="cetakJenis" value="semester" class="sr-only peer">
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 transition-all text-center">
                                <span class="block text-sm font-bold text-slate-800 peer-checked:text-emerald-800">Semester</span>
                                <span class="block text-[11px] text-slate-400 mt-0.5">Rekapitulasi 6 Bulan</span>
                            </div>
                        </label>
                    </div>
                </div>

                @if($cetakJenis === 'bulanan')
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Bulan Laporan</label>
                        <select wire:model="cetakBulanYear" class="block w-full px-3 py-2.5 text-sm font-semibold text-slate-800 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @foreach($availableMonths as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Semester</label>
                        <select wire:model="cetakSemester" class="block w-full px-3 py-2.5 text-sm font-semibold text-slate-800 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="ganjil">Semester Ganjil (Juli - Desember)</option>
                            <option value="genap">Semester Genap (Januari - Juni)</option>
                        </select>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200/80 flex items-center justify-end gap-3">
                <button type="button" @click="showCetakModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl transition-colors">
                    Tutup
                </button>
                <button type="button" wire:click="downloadPdf" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Unduh PDF
                </button>
            </div>
        </div>
    </div>
</div>
