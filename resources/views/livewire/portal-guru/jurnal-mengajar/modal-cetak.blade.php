<!-- Modal Cetak Laporan Jurnal Mengajar -->
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
             class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-slate-200">
            
            <!-- Modal Header (Sesuai Tema Sistem) -->
            <div class="bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary px-6 sm:px-8 py-5 flex items-center justify-between text-white border-b border-brand-primary/30">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 bg-white/20 rounded-2xl backdrop-blur-md flex items-center justify-center border border-white/30 shadow-inner">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black leading-tight text-white">Cetak Rekap Jurnal Mengajar</h3>
                        <p class="text-xs text-white/80 mt-0.5 font-medium">Pilih periode dan target kelas KBM yang ingin dicetak</p>
                    </div>
                </div>
                <button type="button" @click="showCetakModal = false" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all focus:outline-none cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6 sm:px-8 max-h-[70vh] overflow-y-auto space-y-6">
                <!-- 1. Pilih Jenis Periode Laporan -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2.5">
                        1. Pilih Periode Laporan
                    </label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <!-- Bulanan -->
                        <div>
                            <input type="radio" wire:model.live="cetakJenis" id="jurnal_cetak_bulanan" value="bulanan" class="peer sr-only">
                            <label for="jurnal_cetak_bulanan" class="flex flex-col items-center text-center cursor-pointer rounded-2xl border-2 border-slate-200 bg-white p-3.5 shadow-xs hover:border-brand-primary/40 hover:bg-brand-primary/5 peer-checked:border-brand-primary peer-checked:bg-brand-primary/10 peer-checked:ring-1 peer-checked:ring-brand-primary transition-all group">
                                <span class="block text-xs sm:text-sm font-black text-slate-800 peer-checked:text-brand-primary">Bulanan</span>
                                <span class="text-[10px] text-slate-400 font-semibold mt-0.5">Per Bulan</span>
                            </label>
                        </div>
                        <!-- Semester -->
                        <div>
                            <input type="radio" wire:model.live="cetakJenis" id="jurnal_cetak_semester" value="semester" class="peer sr-only">
                            <label for="jurnal_cetak_semester" class="flex flex-col items-center text-center cursor-pointer rounded-2xl border-2 border-slate-200 bg-white p-3.5 shadow-xs hover:border-brand-primary/40 hover:bg-brand-primary/5 peer-checked:border-brand-primary peer-checked:bg-brand-primary/10 peer-checked:ring-1 peer-checked:ring-brand-primary transition-all group">
                                <span class="block text-xs sm:text-sm font-black text-slate-800 peer-checked:text-brand-primary">Semester</span>
                                <span class="text-[10px] text-slate-400 font-semibold mt-0.5">Ganjil / Genap</span>
                            </label>
                        </div>
                        <!-- Tahunan -->
                        <div>
                            <input type="radio" wire:model.live="cetakJenis" id="jurnal_cetak_tahunan" value="tahunan" class="peer sr-only">
                            <label for="jurnal_cetak_tahunan" class="flex flex-col items-center text-center cursor-pointer rounded-2xl border-2 border-slate-200 bg-white p-3.5 shadow-xs hover:border-brand-primary/40 hover:bg-brand-primary/5 peer-checked:border-brand-primary peer-checked:bg-brand-primary/10 peer-checked:ring-1 peer-checked:ring-brand-primary transition-all group">
                                <span class="block text-xs sm:text-sm font-black text-slate-800 peer-checked:text-brand-primary">Tahunan</span>
                                <span class="text-[10px] text-slate-400 font-semibold mt-0.5">1 Tahun Ajaran</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sub-Input Dinamis: Bulan -->
                @if($cetakJenis === 'bulanan')
                    <div class="bg-brand-primary/5 border border-brand-primary/20 rounded-2xl p-4 space-y-1.5 animate-fade-in">
                        <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            Pilih Bulan KBM
                        </label>
                        <select wire:model="cetakBulanYear" class="w-full px-3.5 py-2.5 rounded-xl border border-brand-primary/30 bg-white text-slate-900 font-bold text-sm outline-none focus:ring-2 focus:ring-brand-primary shadow-xs cursor-pointer">
                            @foreach($availableMonths as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Sub-Input Dinamis: Semester -->
                @if($cetakJenis === 'semester')
                    <div class="bg-brand-primary/5 border border-brand-primary/20 rounded-2xl p-4 space-y-2 animate-fade-in">
                        <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            Pilih Semester
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <input type="radio" wire:model="cetakSemester" id="jurnal_smt_ganjil" value="ganjil" class="peer sr-only">
                                <label for="jurnal_smt_ganjil" class="flex items-center justify-center cursor-pointer rounded-xl border-2 border-slate-200 bg-white py-2.5 px-3 shadow-xs hover:bg-brand-primary/5 peer-checked:border-brand-primary peer-checked:bg-brand-primary/10 peer-checked:text-brand-primary transition-all text-xs font-black text-slate-700">
                                    Semester Ganjil
                                </label>
                            </div>
                            <div>
                                <input type="radio" wire:model="cetakSemester" id="jurnal_smt_genap" value="genap" class="peer sr-only">
                                <label for="jurnal_smt_genap" class="flex items-center justify-center cursor-pointer rounded-xl border-2 border-slate-200 bg-white py-2.5 px-3 shadow-xs hover:bg-brand-primary/5 peer-checked:border-brand-primary peer-checked:bg-brand-primary/10 peer-checked:text-brand-primary transition-all text-xs font-black text-slate-700">
                                    Semester Genap
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 2. Filter Target: Guru • Mapel • Kelas -->
                <div class="border-t border-slate-100 pt-4 space-y-4">
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                        2. Sasaran Cetak (Guru &bull; Mapel &bull; Kelas)
                    </label>

                    <!-- Guru Pengampu: Paten (Guru Biasa) atau Dropdown Pilihan (Mode Supervisi) -->
                    @if($isSupervisor)
                        <div>
                            <label class="block text-xs font-black text-amber-900 mb-1 flex items-center justify-between">
                                <span>Guru Pengampu</span>
                                <span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.2 rounded font-black">Supervisi</span>
                            </label>
                            <select wire:model.live="cetakTeacherId" class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 bg-amber-50/50 text-slate-900 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500 shadow-xs cursor-pointer">
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <!-- Guru Tunggal (Paten) -->
                        <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3 sm:p-3.5 flex items-center justify-between shadow-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-black text-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Guru Pengampu</span>
                                    <span class="text-xs sm:text-sm font-black text-slate-800">{{ $currentTeacherName ?: Auth::user()->name }}</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-600 bg-slate-200/80 px-2.5 py-1 rounded-lg border border-slate-300/50">
                                <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Paten
                            </span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Pilihan Mata Pelajaran (Pertama) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model.live="cetakMapelId" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm font-semibold outline-none focus:ring-2 focus:ring-brand-primary shadow-xs cursor-pointer">
                                @if(count($cetakAvailableMapels) > 1)
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                @endif
                                @foreach($cetakAvailableMapels as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilihan Kelas Rombel (Baru aktif setelah Mapel dipilih) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Kelas Rombel
                            </label>
                            @if(!$cetakMapelId)
                                <select disabled class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 text-sm font-semibold cursor-not-allowed shadow-inner opacity-75">
                                    <option value="">-- Pilih Mapel Terlebih Dahulu --</option>
                                </select>
                            @else
                                <select wire:model="cetakClassId" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm font-semibold outline-none focus:ring-2 focus:ring-brand-primary shadow-xs cursor-pointer animate-fade-in">
                                    <option value="">-- Semua Kelas Rombel --</option>
                                    @foreach($cetakAvailableClasses as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informasi Ringkas -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 flex items-start gap-3">
                    <svg class="w-5 h-5 text-brand-primary shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Laporan mencakup seluruh sesi KBM, uraian aktivitas, catatan refleksi, serta statistik kehadiran siswa pada periode terpilih.
                    </p>
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <button type="button" @click="showCetakModal = false" class="w-full sm:w-auto px-5 py-2.5 text-xs sm:text-sm font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-200/60 rounded-xl transition-all cursor-pointer">
                    Batal
                </button>

                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <!-- Tombol Download Excel -->
                    <button type="button" wire:click="downloadCetakExcel" 
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25 transition-all cursor-pointer hover:scale-[1.02] active:scale-[0.98]">
                        <svg wire:loading wire:target="downloadCetakExcel" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <svg wire:loading.remove wire:target="downloadCetakExcel" class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        <span>Unduh Excel</span>
                    </button>

                    <!-- Tombol Download / Cetak PDF -->
                    <button type="button" wire:click="downloadCetakPdf" 
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black text-white bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 shadow-md shadow-rose-600/25 transition-all cursor-pointer hover:scale-[1.02] active:scale-[0.98]">
                        <svg wire:loading wire:target="downloadCetakPdf" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <svg wire:loading.remove wire:target="downloadCetakPdf" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        <span>Cetak PDF</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
