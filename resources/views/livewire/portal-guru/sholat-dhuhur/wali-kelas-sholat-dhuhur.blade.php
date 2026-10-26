<div class="min-h-full bg-slate-50 font-jakarta pb-12" x-data="{ 
    showInputModal: @entangle('showInputModal').live, 
    showCetakModal: @entangle('showCetakModal').live,
    rekapMode: 'harian',
    dailyFilter: 'all',
    searchQuery: '',
    showWaModal: false,
    waStudentId: '',
    waStudentName: '',
    waStudentClass: '',
    waStudentNisn: '',
    waRecipientPhone: '',
    waOriginalMessage: '',
    waMessage: '',
    waStatusType: 'sholat_dhuhur',
    waStatusLabel: '',
    isSending: false,
    openWaModal(data) {
        this.waStudentId = data.id;
        this.waStudentName = data.name;
        this.waStudentClass = data.className;
        this.waStudentNisn = data.nisn || '-';
        this.waRecipientPhone = data.cleanPhone || data.phone || '';
        this.waOriginalMessage = data.rawText || '';
        this.waMessage = data.rawText || '';
        this.waStatusType = data.type || 'sholat_dhuhur';
        this.waStatusLabel = data.statusLabel || 'Belum Presensi Sholat';
        this.isSending = false;
        this.showWaModal = true;
    },
    resetTemplate() {
        this.waMessage = this.waOriginalMessage;
    },
    submitSend() {
        if (!this.waRecipientPhone.trim()) {
            alert('Silakan masukkan nomor WhatsApp tujuan!');
            return;
        }
        if (!this.waMessage.trim()) {
            alert('Isi pesan tidak boleh kosong!');
            return;
        }
        this.isSending = true;
        $wire.sendWaDirect(this.waStudentId, this.waRecipientPhone, this.waMessage, this.waStatusType)
            .finally(() => {
                this.isSending = false;
            });
    }
}"
@wa-modal-close.window="showWaModal = false">
    @include('livewire.portal-guru.sholat-dhuhur.header')

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10 space-y-6">
        @include('livewire.portal-guru.sholat-dhuhur.filters')

        @if(!$selectedAcademicYearId || !$selectedClassId)
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-12 text-center border border-white/40 shadow-xl">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Pilih Kelas dan Tahun Ajaran</h3>
                <p class="text-xs text-slate-400 mt-1">Silakan pilih kelas binaan Anda untuk menampilkan matriks presensi sholat dhuhur.</p>
            </div>
        @else
            @include('livewire.portal-guru.sholat-dhuhur.stats-monthly')
            @include('livewire.portal-guru.sholat-dhuhur.table')
        @endif
    </div>

    @include('livewire.portal-guru.sholat-dhuhur.modal-input')
    @include('livewire.portal-guru.sholat-dhuhur.modal-cetak')

    <!-- Modal Pop-Up Edit & Kirim WhatsApp Sholat Dhuhur ke Orang Tua Siswa -->
    <div x-show="showWaModal" 
        x-cloak
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-wa-title" 
        role="dialog" 
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background Backdrop -->
            <div x-show="showWaModal" 
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                @click="if(!isSending) showWaModal = false"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="showWaModal" 
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-slate-200">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black leading-tight" id="modal-wa-title">Kirim Pesan WhatsApp Sholat Dhuhur</h3>
                            <p class="text-xs text-emerald-100 font-medium">Kirim langsung dari sistem gateway aplikasi tanpa WhatsApp Web</p>
                        </div>
                    </div>
                    <button type="button" @click="if(!isSending) showWaModal = false" class="text-white/70 hover:text-white p-1 rounded-xl hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <!-- Info Target Siswa -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 font-bold block uppercase tracking-wider text-[10px]">Penerima (Siswa)</span>
                            <span class="text-slate-900 font-black text-sm block" x-text="waStudentName"></span>
                            <span class="text-slate-500 font-medium">Kelas <span x-text="waStudentClass" class="font-bold text-emerald-700"></span> &bull; NISN: <span x-text="waStudentNisn"></span></span>
                        </div>
                        <div class="sm:text-right">
                            <span class="text-slate-400 font-bold block uppercase tracking-wider text-[10px]">Status Sholat</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-100 text-indigo-800 border border-indigo-200"
                                  x-text="waStatusLabel">
                            </span>
                        </div>
                    </div>

                    <!-- Input Nomor WhatsApp Tujuan -->
                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor WhatsApp Orang Tua / Wali
                        </label>
                        <div class="relative">
                            <input type="text" x-model="waRecipientPhone" placeholder="Contoh: 08123456789 atau 628123456789"
                                class="w-full pl-10 pr-4 py-2.5 text-sm bg-white rounded-xl border border-slate-300 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Format otomatis disesuaikan (08... otomatis diubah menjadi 628...). Dapat diedit langsung jika nomor orang tua berganti.</p>
                    </div>

                    <!-- Textarea Isi Pesan -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                                Isi Pesan WhatsApp (Dapat Diedit Bebas)
                            </label>
                            <button type="button" @click="resetTemplate" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1 hover:underline cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Reset Format Asli</span>
                            </button>
                        </div>
                        <textarea x-model="waMessage" rows="8"
                            class="w-full p-3.5 text-xs sm:text-sm bg-slate-50 focus:bg-white rounded-2xl border border-slate-300 font-sans text-slate-800 leading-relaxed focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1 italic">💡 Pesan di atas diambil otomatis dari template presensi sholat dhuhur {{ \App\Models\PengaturanSekolah::current()?->school_name ?? 'Sekolah' }}.</p>
                    </div>
                </div>

                <!-- Modal Footer Actions -->
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                    <button type="button" @click="showWaModal = false" :disabled="isSending"
                        class="w-full sm:w-auto px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-200/60 rounded-xl transition-all cursor-pointer">
                        Batal
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="submitSend" :disabled="isSending"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/20 transition-all cursor-pointer disabled:opacity-50">
                            <svg x-show="!isSending" class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            <svg x-show="isSending" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="isSending ? 'Mengirim via Gateway...' : 'Kirim Sekarang (via Sistem)'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification Listener -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (event) => {
                const data = event[0] || event;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: data.type || 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                } else {
                    alert(data.message);
                }
            });
        });
    </script>
</div>
