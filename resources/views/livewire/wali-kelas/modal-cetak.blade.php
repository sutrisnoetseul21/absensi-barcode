<!-- Modal Cetak Laporan -->
<div x-show="showCetakModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Backdrop -->
        <div x-show="showCetakModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" aria-hidden="true" @click="showCetakModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div x-show="showCetakModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-white/20">
            
            <div class="bg-gradient-to-r from-emerald-600 to-teal-500 px-8 py-5 flex items-center justify-between">
                <h3 class="text-xl font-extrabold text-white flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl backdrop-blur-sm flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6 text-emerald-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </div>
                    Cetak Laporan Presensi
                </h3>
                <button @click="showCetakModal = false" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-emerald-100 hover:text-white hover:bg-white/20 transition-all focus:outline-none">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="px-6 py-8 max-h-[60vh] overflow-y-auto">
                <div class="space-y-8">
                    <!-- Jenis Laporan -->
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 mb-3 uppercase tracking-wider">Pilih Jenis Laporan</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Bulanan -->
                            <div>
                                <input type="radio" wire:model.live="cetakJenis" id="jenis_bulanan" value="bulanan" class="peer sr-only">
                                <label for="jenis_bulanan" class="flex flex-col cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300 hover:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 peer-checked:bg-emerald-50/50 transition-all group">
                                    <span class="block text-sm font-extrabold text-slate-800 group-hover:text-emerald-700 peer-checked:text-emerald-800">Bulanan</span>
                                    <span class="mt-1 text-xs font-semibold text-slate-500">Bulan spesifik</span>
                                </label>
                            </div>
                            <!-- Semester -->
                            <div>
                                <input type="radio" wire:model.live="cetakJenis" id="jenis_semester" value="semester" class="peer sr-only">
                                <label for="jenis_semester" class="flex flex-col cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300 hover:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 peer-checked:bg-emerald-50/50 transition-all group">
                                    <span class="block text-sm font-extrabold text-slate-800 group-hover:text-emerald-700 peer-checked:text-emerald-800">Semester</span>
                                    <span class="mt-1 text-xs font-semibold text-slate-500">Per semester</span>
                                </label>
                            </div>
                            <!-- Tahunan -->
                            <div>
                                <input type="radio" wire:model.live="cetakJenis" id="jenis_tahunan" value="tahunan" class="peer sr-only">
                                <label for="jenis_tahunan" class="flex flex-col cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300 hover:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 peer-checked:bg-emerald-50/50 transition-all group">
                                    <span class="block text-sm font-extrabold text-slate-800 group-hover:text-emerald-700 peer-checked:text-emerald-800">Tahunan</span>
                                    <span class="mt-1 text-xs font-semibold text-slate-500">Satu tahun penuh</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    @if($cetakJenis === 'bulanan')
                        <div class="animate-fade-in-up">
                            <label class="block text-xs font-extrabold text-slate-500 mb-3 uppercase tracking-wider">Pilih Bulan Kehadiran</label>
                            <div class="relative max-w-sm">
                                <select wire:model="cetakBulanYear" class="block w-full pl-4 pr-10 py-3.5 text-slate-800 border-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 sm:text-sm rounded-xl font-bold cursor-pointer appearance-none shadow-sm transition-all bg-white hover:border-emerald-300">
                                    @foreach($availableMonths as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-emerald-500">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($cetakJenis === 'semester')
                        <div class="animate-fade-in-up">
                            <label class="block text-xs font-extrabold text-slate-500 mb-3 uppercase tracking-wider">Pilih Semester</label>
                            <div class="grid grid-cols-2 gap-3 max-w-sm">
                                <div>
                                    <input type="radio" wire:model="cetakSemester" id="smt_ganjil" value="ganjil" class="peer sr-only">
                                    <label for="smt_ganjil" class="flex items-center justify-center cursor-pointer rounded-xl border border-slate-200 bg-white py-3 px-4 shadow-sm hover:border-emerald-300 hover:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 peer-checked:bg-emerald-50/50 transition-all">
                                        <span class="text-sm font-extrabold text-slate-700 peer-checked:text-emerald-800">Semester Ganjil</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" wire:model="cetakSemester" id="smt_genap" value="genap" class="peer sr-only">
                                    <label for="smt_genap" class="flex items-center justify-center cursor-pointer rounded-xl border border-slate-200 bg-white py-3 px-4 shadow-sm hover:border-emerald-300 hover:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 peer-checked:bg-emerald-50/50 transition-all">
                                        <span class="text-sm font-extrabold text-slate-700 peer-checked:text-emerald-800">Semester Genap</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 mt-2">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-blue-800/80 leading-relaxed">
                                    Laporan ini akan secara otomatis disesuaikan dengan <strong class="text-blue-900">Tahun Ajaran</strong> dan <strong class="text-blue-900">Kelas</strong> yang sedang Anda pilih di Filter Dashboard.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex-1 w-full text-left">
                </div>
                <div class="flex gap-3 w-full sm:w-auto justify-end mt-3 sm:mt-0">
                    <button type="button" @click="showCetakModal = false" class="inline-flex justify-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-bold rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                        Batal
                    </button>
                    <button type="button" wire:click="downloadCetakExcel" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors" onclick="setTimeout(() => { showCetakModal = false; }, 1500)">
                        <svg wire:loading wire:target="downloadCetakExcel" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg wire:loading.remove wire:target="downloadCetakExcel" class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Excel
                    </button>
                    <button type="button" wire:click="downloadCetakPdf" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors" onclick="setTimeout(() => { showCetakModal = false; }, 1500)">
                        <svg wire:loading wire:target="downloadCetakPdf" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg wire:loading.remove wire:target="downloadCetakPdf" class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
