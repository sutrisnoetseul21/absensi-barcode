<div class="p-4 lg:p-6 max-w-6xl mx-auto space-y-4">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Sirkulasi</h1>
            <p class="text-slate-500 text-sm mt-1">Layanan Peminjaman &amp; Pengembalian Buku.</p>
        </div>
        <a href="{{ route('portal-perpustakaan.sirkulasi-kiosk') }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-2xl font-bold text-xs shadow-sm transition-all">
            <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            Mode Layar Penuh
        </a>
    </div>

    <!-- Embedded Kiosk Widget -->
    <div class="bg-slate-100 rounded-3xl overflow-hidden"
         x-data="sirkulasiKioskData()"
         x-init="initKiosk()"
         wire:ignore>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Audio Elements -->
        <audio id="audio-success-p" src="/audio/beep.mp3" preload="auto"></audio>
        <audio id="audio-error-p" src="/audio/buzz.mp3" preload="auto"></audio>
        <audio id="audio-network-p" src="/audio/siren.mp3" preload="auto"></audio>

        <!-- Include Html5Qrcode Library -->
        <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

        <style>
            #reader video {
                object-fit: cover !important;
                width: 100% !important;
                height: 100% !important;
                border-radius: 1rem;
            }
            #reader__scan_region {
                background: transparent !important;
            }
            #reader__dashboard {
                display: none !important;
            }
        </style>

        <!-- Hidden Barcode Input -->
        <input type="text"
               x-ref="barcodeInput"
               @input="barcode = $event.target.value"
               @keydown.enter="submitScan()"
               @keydown.escape="resetToPeminjam(); $event.target.value = ''"
               @blur="refocusInput()"
               class="fixed top-0 left-0 opacity-0 w-px h-px"
               autocomplete="off"
               autocorrect="off"
               autocapitalize="off"
               spellcheck="false">

        <!-- Main Kiosk Card -->
        <div class="flex flex-col md:flex-row min-h-[560px]">

            <!-- Left Panel -->
            <div class="w-full md:w-5/12 bg-gradient-to-b from-indigo-700 to-indigo-900 text-white p-6 lg:p-8 flex flex-col relative transition-all duration-300">
                <div class="mb-6">
                    @if($settings && $settings->school_logo_path)
                        <img src="{{ asset('storage/'.$settings->school_logo_path) }}" alt="Logo" class="w-12 h-12 mb-3 object-contain drop-shadow-md">
                    @endif
                    <h2 class="text-2xl font-black leading-tight">Sirkulasi<br/>Perpustakaan</h2>
                    <p class="text-xs text-indigo-200 font-medium mt-1">Layanan Peminjaman &amp; Pengembalian Buku</p>
                </div>

                <!-- Member Info (Step 2) -->
                <div x-show="scanState === 'BUKU'" style="display:none;" class="mb-6 space-y-4">
                    <div class="p-4 bg-white/10 rounded-2xl border border-white/20 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="flex-grow overflow-hidden">
                            <span class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest block" x-text="peminjamInfo.type"></span>
                            <h4 class="text-sm font-extrabold text-white truncate" x-text="peminjamInfo.name"></h4>
                            <p class="text-xs text-indigo-100 truncate" x-text="peminjamInfo.sub_info"></p>
                        </div>
                    </div>

                    <!-- Active Loans -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-300">
                            <span>Pinjaman Aktif Saat Ini</span>
                            <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[11px]" x-text="activeLoans.length + ' Buku'"></span>
                        </div>
                        <div class="max-h-44 overflow-y-auto space-y-2 pr-1">
                            <template x-for="loan in activeLoans" :key="loan.peminjaman_id">
                                <button type="button"
                                        @click="openLoanActionModal(loan)"
                                        :disabled="isInCart(loan.eksemplar_id)"
                                        class="w-full p-2.5 rounded-xl border text-xs flex justify-between items-center gap-2 transition-all duration-200 text-left"
                                        :class="isInCart(loan.eksemplar_id)
                                            ? 'bg-white/5 border-white/10 opacity-50 cursor-not-allowed'
                                            : 'bg-white/10 border-white/15 hover:bg-white/25 hover:border-white/35 cursor-pointer'">
                                    <div class="overflow-hidden">
                                        <p class="font-bold text-white truncate" x-text="loan.buku_title"></p>
                                        <p class="text-[11px] text-slate-300 font-mono" x-text="loan.kode_eksemplar"></p>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                              :class="loan.is_terlambat ? 'bg-rose-500/80 text-white' : 'bg-emerald-500/30 text-emerald-200'"
                                              x-text="loan.is_terlambat ? 'Terlambat' : loan.tanggal_jatuh_tempo"></span>
                                        <span x-show="isInCart(loan.eksemplar_id)" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-400/30 text-amber-200">✓</span>
                                        <svg x-show="!isInCart(loan.eksemplar_id)" class="w-3 h-3 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </button>
                            </template>
                            <div x-show="activeLoans.length === 0" class="text-center py-3 text-xs text-slate-400 italic bg-white/5 rounded-xl">
                                Tidak ada pinjaman aktif.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step Indicators -->
                <div class="mt-auto pt-4 space-y-4 border-t border-white/10">
                    <div class="flex items-center space-x-3 transition-opacity" :class="scanState === 'PEMINJAM' ? 'opacity-100' : 'opacity-60'">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs"
                             :class="scanState === 'PEMINJAM' ? 'bg-white text-indigo-700 ring-2 ring-indigo-300' : 'bg-white/20 text-white'">
                            <span x-show="scanState === 'PEMINJAM'">1</span>
                            <svg x-show="scanState === 'BUKU'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-bold text-sm">1. Scan Kartu Anggota</h3>
                            <p class="text-[11px] opacity-80" x-text="scanState === 'BUKU' ? 'Selesai Tervalidasi' : 'Kartu siswa atau guru'"></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 transition-opacity" :class="scanState === 'BUKU' ? 'opacity-100' : 'opacity-40'">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs"
                             :class="scanState === 'BUKU' ? 'bg-emerald-500 text-white ring-2 ring-emerald-300' : 'bg-white/20 text-white'">2</div>
                        <div class="text-left">
                            <h3 class="font-bold text-sm">2. Scan Buku &amp; Submit</h3>
                            <p class="text-[11px] opacity-80" x-text="scanState === 'BUKU' ? draftCart.length + ' buku di keranjang' : 'Menunggu anggota...'"></p>
                        </div>
                    </div>
                </div>

                <!-- Loading Overlay (Left Panel) -->
                <div x-show="isLoading" class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center backdrop-blur-sm z-20">
                    <svg class="w-10 h-10 text-white animate-spin mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="font-bold text-sm text-white">Memproses...</span>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="w-full md:w-7/12 p-6 lg:p-8 flex flex-col relative bg-slate-50/50">

                <!-- Loan Action Modal Popup (In-Panel) -->
                <div x-show="loanActionModal.open"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     style="display:none;"
                     class="absolute inset-0 z-30 flex items-center justify-center bg-slate-900/20 backdrop-blur-xs rounded-r-none md:rounded-r-3xl p-5 sm:p-6">
                    <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm p-6 border border-slate-100 relative">
                        <!-- Header -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <div class="overflow-hidden flex-1">
                                <h4 class="text-sm font-extrabold text-slate-900 leading-tight truncate" x-text="loanActionModal.loan?.buku_title"></h4>
                                <p class="text-[11px] font-mono text-slate-500 mt-0.5" x-text="loanActionModal.loan?.kode_eksemplar"></p>
                            </div>
                        </div>

                        <!-- Jatuh Tempo -->
                        <div class="flex items-center justify-between text-xs bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5 mb-4">
                            <span class="text-slate-500 font-medium">Jatuh Tempo</span>
                            <span class="font-extrabold"
                                  :class="loanActionModal.loan?.is_terlambat ? 'text-rose-600' : 'text-emerald-700'"
                                  x-text="(loanActionModal.loan?.is_terlambat ? '⚠ Terlambat — ' : '') + (loanActionModal.loan?.tanggal_jatuh_tempo ?? '-')"></span>
                        </div>

                        <p class="text-xs text-slate-500 mb-3 text-center font-medium">Pilih tindakan untuk buku ini:</p>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <!-- Mengembalikan Buku -->
                            <button type="button" @click="addLoanToCart('KEMBALI')"
                                    class="flex flex-col items-center justify-center text-center p-3.5 rounded-2xl bg-sky-50 hover:bg-sky-100 border-2 border-sky-200 hover:border-sky-300 text-sky-800 transition-all group active:scale-95">
                                <div class="w-10 h-10 rounded-xl bg-sky-100 group-hover:bg-sky-200 text-sky-600 flex items-center justify-center mb-1.5 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                </div>
                                <span class="text-xs font-extrabold leading-tight">Mengembalikan Buku</span>
                            </button>

                            <!-- Perpanjang Buku -->
                            <button type="button" @click="addLoanToCart('PERPANJANG')"
                                    x-show="loanActionModal.loan?.can_extend"
                                    class="flex flex-col items-center justify-center text-center p-3.5 rounded-2xl bg-purple-50 hover:bg-purple-100 border-2 border-purple-200 hover:border-purple-300 text-purple-800 transition-all group active:scale-95">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 group-hover:bg-purple-200 text-purple-600 flex items-center justify-center mb-1.5 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </div>
                                <span class="text-xs font-extrabold leading-tight">Perpanjang Buku</span>
                            </button>
                        </div>

                        <!-- Cancel Button -->
                        <button type="button" @click="loanActionModal.open = false; refocusInput()"
                                class="w-full py-2 text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition-colors text-center">
                            Batal
                        </button>
                    </div>
                </div>

                <!-- Reset Button -->
                <button @click="resetToPeminjam()" x-show="scanState === 'BUKU'"
                        class="absolute top-4 right-4 text-white bg-rose-600 hover:bg-rose-700 font-bold text-xs px-3 py-1.5 rounded-full shadow transition flex items-center gap-1.5 z-10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Reset
                </button>

                <!-- Camera Scanner Visualizer -->
                <div x-show="isCameraActive" class="flex flex-col items-center justify-center w-full max-w-sm mx-auto mb-4" style="display: none;">
                    <div id="reader" class="w-full h-52 bg-slate-900 rounded-3xl overflow-hidden shadow-xl border-2 border-emerald-500/80 relative"></div>
                    <p class="text-xs font-semibold text-emerald-600 mt-3 flex items-center gap-1.5 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span x-text="scanState === 'PEMINJAM' ? 'Arahkan Barcode Kartu Anggota ke Kamera' : 'Arahkan Barcode Buku ke Kamera'"></span>
                    </p>
                </div>

                <!-- Feedback Alert -->
                <div x-show="feedbackState !== 'idle'" style="display:none;" class="mb-4 p-3.5 rounded-2xl border flex items-center gap-3 shadow-xs"
                     :class="{
                         'bg-emerald-50 border-emerald-200 text-emerald-800': feedbackState === 'success',
                         'bg-rose-50 border-rose-200 text-rose-800': feedbackState === 'error',
                         'bg-amber-50 border-amber-200 text-amber-800': feedbackState === 'network_error' || feedbackState === 'referensi'
                     }">
                    <div class="p-1.5 rounded-xl flex-shrink-0"
                         :class="{ 'bg-emerald-100 text-emerald-700': feedbackState === 'success', 'bg-rose-100 text-rose-700': feedbackState === 'error', 'bg-amber-100 text-amber-700': feedbackState === 'network_error' || feedbackState === 'referensi' }">
                        <svg x-show="feedbackState === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="feedbackState !== 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex-grow text-xs">
                        <h5 class="font-extrabold" x-text="feedbackTitle"></h5>
                        <p class="mt-0.5" x-html="feedbackMessage"></p>
                    </div>
                </div>

                <!-- STATE 1: Scan Anggota -->
                <div x-show="scanState === 'PEMINJAM' && !isCameraActive" class="flex-grow flex flex-col items-center justify-center text-center p-6">
                    <div class="w-24 h-24 mx-auto rounded-3xl bg-indigo-50 border border-indigo-100 flex items-center justify-center mb-5 shadow-inner animate-pulse">
                        <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 mb-2">Silakan Scan Kartu Anggota</h3>
                    <p class="text-slate-500 text-sm max-w-sm">Dekatkan stiker barcode pada kartu presensi siswa atau guru ke scanner.</p>
                    <div class="mt-6 flex items-center gap-2 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-full font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        Scanner Aktif & Siap
                    </div>
                </div>

                <!-- STATE 2: Draft Cart -->
                <div x-show="scanState === 'BUKU'" style="display:none;" class="flex-grow flex flex-col space-y-3">
                    <!-- Scan Prompt -->
                    <div class="p-3 bg-white border border-slate-200 rounded-2xl flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Siap Scan Barcode Buku</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">Scanner Otomatis Terfokus</span>
                    </div>

                    <!-- Cart Table -->
                    <div class="flex-grow flex flex-col bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                        <div class="px-4 py-3 bg-slate-100/70 border-b border-slate-200 flex justify-between items-center text-xs font-extrabold text-slate-700">
                            <span>Daftar Transaksi (Keranjang)</span>
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-[11px]" x-text="draftCart.length + ' Buku'"></span>
                        </div>

                        <div class="flex-grow overflow-y-auto max-h-56 p-3 space-y-2">
                            <template x-for="(item, index) in draftCart" :key="item.kode_eksemplar">
                                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white flex items-center justify-between gap-3 transition-all">
                                    <div class="flex-grow overflow-hidden space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span x-show="item.action_type === 'PINJAM'" class="px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">PINJAM BARU</span>
                                            <span x-show="item.action_type === 'KEMBALI'" class="px-2 py-0.5 rounded-md text-[10px] font-black bg-sky-100 text-sky-800 border border-sky-200">PENGEMBALIAN</span>
                                            <span x-show="item.action_type === 'PERPANJANG'" class="px-2 py-0.5 rounded-md text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-200">PERPANJANG +7 HARI</span>
                                            <span class="font-mono text-[11px] font-bold text-slate-500" x-text="item.kode_eksemplar"></span>
                                        </div>
                                        <h5 class="font-bold text-slate-900 text-xs truncate" x-text="item.buku_title"></h5>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <button x-show="item.can_extend" type="button" @click="toggleItemAction(index)"
                                                class="px-2.5 py-1 text-[11px] font-bold rounded-lg border transition-all"
                                                :class="item.action_type === 'KEMBALI' ? 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100' : 'bg-sky-50 text-sky-700 border-sky-200 hover:bg-sky-100'">
                                            <span x-text="item.action_type === 'KEMBALI' ? 'Ubah ke Perpanjang' : 'Ubah ke Kembalikan'"></span>
                                        </button>
                                        <button type="button" @click="removeFromCart(index)"
                                                class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 rounded-lg transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="draftCart.length === 0" class="flex flex-col items-center justify-center py-8 text-center text-slate-400">
                                <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="text-xs font-bold text-slate-500">Keranjang Kosong</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Scan barcode buku untuk menambahkan.</p>
                            </div>
                        </div>

                        <!-- Submit Bar -->
                        <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
                            <button type="button" @click="clearCart()" x-show="draftCart.length > 0"
                                    class="px-3 py-2 text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold transition-all">
                                Kosongkan
                            </button>
                            <button type="button" @click="submitBatchTransaction()" :disabled="draftCart.length === 0"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="'Selesaikan Transaksi (' + draftCart.length + ' Buku)'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="bg-white/70 border-t border-slate-200 px-4 py-3 flex flex-col md:flex-row justify-between items-center gap-3 text-xs text-slate-500 backdrop-blur-sm">
            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
                <div>Scanner Buffer: <span class="font-mono text-slate-800 font-bold bg-slate-200 px-2 py-0.5 rounded" x-text="barcode || '...'"></span></div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="hidden md:inline">Sistem Aktif &amp; Siap Scan</span>
                </div>
            </div>

            <!-- Camera Toggle & Controls -->
            <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                <template x-if="isCameraActive && cameraList.length > 1">
                    <select x-model="selectedCameraId" @change="changeCamera()" class="text-xs bg-slate-100 border border-slate-300 text-slate-700 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <template x-for="cam in cameraList" :key="cam.id">
                            <option :value="cam.id" x-text="cam.label || 'Kamera (' + cam.id.substring(0, 5) + ')'"></option>
                        </template>
                    </select>
                </template>

                <button @click="toggleCamera()" 
                        type="button"
                        class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-xs transition-all duration-200 shadow-sm border"
                        :class="isCameraActive 
                            ? 'bg-rose-50 border-rose-200 text-rose-600 hover:bg-rose-100' 
                            : 'bg-emerald-50 border-emerald-200 text-emerald-600 hover:bg-emerald-100'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="isCameraActive ? 'Matikan Kamera' : 'Gunakan Kamera'"></span>
                </button>
            </div>
        </div>

        <!-- Success Transaction Popup (Teleported to Body) -->
        <template x-teleport="body">
            <div x-show="successModal.open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 style="display:none;"
                 class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-emerald-100">
                    <!-- Header Hijau -->
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 px-8 pt-10 pb-8 text-center">
                        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-5 animate-pulse">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-2xl font-extrabold text-white">Transaksi Berhasil!</h3>
                        <p class="text-emerald-100 text-base mt-1 font-medium" x-text="successModal.memberName"></p>
                    </div>

                    <!-- Daftar Item Transaksi -->
                    <div class="px-8 py-6">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Ringkasan Transaksi</p>
                        <div class="space-y-2.5 max-h-52 overflow-y-auto">
                            <template x-for="item in successModal.items" :key="item.eksemplar_id">
                                <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                    <span class="flex-shrink-0 px-2.5 py-1 rounded-lg text-xs font-extrabold"
                                          :class="item.action_type === 'KEMBALI' ? 'bg-sky-100 text-sky-700' : item.action_type === 'PERPANJANG' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700'"
                                          x-text="item.action_type === 'KEMBALI' ? 'Dikembalikan' : item.action_type === 'PERPANJANG' ? 'Diperpanjang' : 'Dipinjam'"></span>
                                    <p class="text-sm font-semibold text-slate-800 truncate" x-text="item.buku_title"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Auto-dismiss hint -->
                    <div class="pb-7 text-center">
                        <p class="text-sm text-slate-400">Menutup otomatis dalam 5 detik...</p>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Click to refocus anywhere -->
    <div class="fixed inset-0 -z-10" @click="$refs.barcodeInput && $refs.barcodeInput.focus()"></div>

    <script>
        function sirkulasiKioskData() {
            return {
                isActive: true, // Langsung aktif di dalam portal
                barcode: '',
                isLoading: false,

                scanState: 'PEMINJAM',

                peminjamInfo: { id: null, type: null, name: '', sub_info: '' },
                activeLoans: [],
                draftCart: [],

                // Loan Action Modal
                loanActionModal: { open: false, loan: null },

                // Success Transaction Modal
                successModal: { open: false, memberName: '', items: [] },

                openLoanActionModal(loan) {
                    if (this.isInCart(loan.eksemplar_id)) return;
                    this.loanActionModal = { open: true, loan: loan };
                },

                addLoanToCart(actionType) {
                    const loan = this.loanActionModal.loan;
                    if (!loan) return;
                    const label = actionType === 'KEMBALI' ? 'Pengembalian' : 'Perpanjang +7 Hari';
                    this.draftCart.push({
                        eksemplar_id: loan.eksemplar_id,
                        kode_eksemplar: loan.kode_eksemplar,
                        buku_title: loan.buku_title,
                        action_type: actionType,
                        action_label: label,
                        can_extend: actionType === 'KEMBALI',
                    });
                    this.loanActionModal = { open: false, loan: null };
                    this.showFeedback('success', 'Ditambahkan ke Keranjang', `<strong>${loan.buku_title}</strong> — ${label}`);
                    this.playAudio('success');
                    this.refocusInput();
                },

                isInCart(eksemplarId) {
                    return this.draftCart.some(item => item.eksemplar_id === eksemplarId);
                },

                feedbackState: 'idle',
                feedbackTitle: '',
                feedbackMessage: '',

                resetTimer: null,
                refocusInterval: null,

                // Camera State
                isCameraActive: false,
                cameraList: [],
                selectedCameraId: '',
                html5QrcodeScanner: null,
                lastCameraScanTime: 0,
                lastSuccessfulScanCode: null,
                candidateCameraCode: null,
                candidateCameraCount: 0,

                async toggleCamera() {
                    if (this.isCameraActive) {
                        await this.stopCamera();
                    } else {
                        await this.startCamera();
                    }
                },
                
                async getAvailableCameras() {
                    try {
                        if (typeof Html5Qrcode !== 'undefined') {
                            const devices = await Html5Qrcode.getCameras();
                            if (devices && devices.length) {
                                this.cameraList = devices;
                                if (!this.selectedCameraId) {
                                    // 1. Cari kamera dengan label belakang (back/rear/environment/belakang)
                                    const backCam = devices.find(d => {
                                        const label = (d.label || '').toLowerCase();
                                        return /back|rear|environment|belakang|facing back|camera2 0/i.test(label);
                                    });

                                    if (backCam) {
                                        this.selectedCameraId = backCam.id;
                                    } else if (devices.length > 1) {
                                        // 2. Jika ada >1 kamera tanpa kata kunci eksplisit, prioritaskan yang bukan kamera depan
                                        const nonFrontCam = devices.find(d => {
                                            const label = (d.label || '').toLowerCase();
                                            return !/front|user|selfie|depan|facing front|camera2 1/i.test(label);
                                        });
                                        this.selectedCameraId = nonFrontCam ? nonFrontCam.id : devices[devices.length - 1].id;
                                    } else {
                                        this.selectedCameraId = devices[0].id;
                                    }
                                }
                            }
                        }
                    } catch (err) {
                        console.warn("Daftar kamera belum bisa diambil:", err);
                    }
                },
                
                async startCamera() {
                    this.candidateCameraCode = null;
                    this.candidateCameraCount = 0;
                    this.lastSuccessfulScanCode = null;

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        alert('Browser Anda memblokir akses kamera (perlu HTTPS atau http://localhost:8001).');
                        return;
                    }

                    try {
                        const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
                        tempStream.getTracks().forEach(track => track.stop());
                    } catch (permErr) {
                        alert('Akses kamera ditolak oleh browser atau sedang digunakan aplikasi lain.');
                        return;
                    }

                    await this.getAvailableCameras();

                    this.isCameraActive = true;
                    this.$nextTick(async () => {
                        try {
                            if (this.html5QrcodeScanner) {
                                await this.html5QrcodeScanner.stop().catch(() => {});
                            }
                            this.html5QrcodeScanner = new Html5Qrcode("reader", {
                                experimentalFeatures: {
                                    useBarCodeDetectorIfSupported: true
                                }
                            });

                            const cameraConfig = (this.cameraList.length > 0 && this.selectedCameraId)
                                ? this.selectedCameraId
                                : { facingMode: "environment" };

                            await this.html5QrcodeScanner.start(
                                cameraConfig,
                                {
                                    fps: 15,
                                    qrbox: { width: 250, height: 140 },
                                    aspectRatio: 1.333333
                                },
                                (decodedText) => {
                                    this.onCameraScan(decodedText);
                                },
                                () => {}
                            );
                        } catch (err) {
                            console.error("Gagal memulai kamera via ID, mencoba fallback mode:", err);
                            try {
                                const fallbackConfig = { facingMode: "environment" };
                                await this.html5QrcodeScanner.start(
                                    fallbackConfig,
                                    { fps: 15, qrbox: { width: 250, height: 140 }, aspectRatio: 1.333333 },
                                    (decodedText) => { this.onCameraScan(decodedText); },
                                    () => {}
                                );
                            } catch (fallbackErr) {
                                console.error("Fallback kamera juga gagal:", fallbackErr);
                                this.isCameraActive = false;
                            }
                        }
                    });
                },
                
                async changeCamera() {
                    if (this.isCameraActive && this.selectedCameraId) {
                        if (this.html5QrcodeScanner) {
                            try {
                                await this.html5QrcodeScanner.stop();
                            } catch (e) {}
                            this.html5QrcodeScanner = null;
                        }
                        await this.startCamera();
                    }
                },
                
                async stopCamera() {
                    if (this.html5QrcodeScanner) {
                        try {
                            await this.html5QrcodeScanner.stop();
                        } catch (e) {}
                        this.html5QrcodeScanner = null;
                    }
                    this.isCameraActive = false;
                },
                
                onCameraScan(decodedText) {
                    const cleanCode = decodedText ? decodedText.trim() : '';
                    if (!cleanCode || cleanCode.length < 3) return;

                    const now = Date.now();
                    
                    if (cleanCode === this.lastSuccessfulScanCode) {
                        if (now - this.lastCameraScanTime < 6000) return;
                    } else {
                        if (now - this.lastCameraScanTime < 1000) return;
                    }

                    if (this.isLoading) return;

                    if (this.candidateCameraCode !== cleanCode) {
                        this.candidateCameraCode = cleanCode;
                        this.candidateCameraCount = 1;
                        return;
                    } else {
                        this.candidateCameraCount++;
                    }

                    if (this.candidateCameraCount >= 2) {
                        this.lastCameraScanTime = now;
                        this.lastSuccessfulScanCode = cleanCode;
                        this.candidateCameraCode = null;
                        this.candidateCameraCount = 0;

                        this.submitScan(cleanCode);
                    }
                },
                initKiosk() {
                    // Langsung fokus & aktif, tidak perlu overlay
                    setTimeout(() => this.refocusInput(), 300);
                    this.refocusInterval = setInterval(() => {
                        this.refocusInput();
                    }, 3000);
                },

                refocusInput() {
                    if (this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                    }
                },

                resetToPeminjam() {
                    this.scanState = 'PEMINJAM';
                    this.peminjamInfo = { id: null, type: null, name: '', sub_info: '' };
                    this.activeLoans = [];
                    this.draftCart = [];
                    this.loanActionModal = { open: false, loan: null };
                    this.successModal = { open: false, memberName: '', items: [] };
                    this.showFeedback('idle', '', '');
                    if (this.$refs.barcodeInput) this.$refs.barcodeInput.value = '';
                    this.barcode = '';
                    this.refocusInput();
                },

                removeFromCart(index) {
                    this.draftCart.splice(index, 1);
                    this.refocusInput();
                },

                clearCart() {
                    this.draftCart = [];
                    this.refocusInput();
                },

                toggleItemAction(index) {
                    if (this.draftCart[index]) {
                        const current = this.draftCart[index].action_type;
                        if (current === 'KEMBALI') {
                            this.draftCart[index].action_type = 'PERPANJANG';
                            this.draftCart[index].action_label = 'Perpanjang +7 Hari';
                        } else {
                            this.draftCart[index].action_type = 'KEMBALI';
                            this.draftCart[index].action_label = 'Pengembalian';
                        }
                    }
                    this.refocusInput();
                },

                async submitScan(overrideBarcode = null) {
                    const currentBarcode = (overrideBarcode || this.barcode).trim();
                    this.barcode = '';
                    if (this.$refs.barcodeInput) this.$refs.barcodeInput.value = '';

                    if (currentBarcode.length === 0) return;

                    this.isLoading = true;
                    if (this.resetTimer) clearTimeout(this.resetTimer);

                    try {
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";

                        if (this.scanState === 'PEMINJAM') {
                            const response = await fetch('/portal-perpustakaan/sirkulasi-kiosk/process', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({ jenis_scan: 'PEMINJAM', barcode: currentBarcode })
                            });

                            if (!response.ok) {
                                if (response.status === 419) { window.location.reload(); return; }
                                const errData = await response.json().catch(() => ({ message: `Kesalahan server (${response.status})` }));
                                this.showFeedback('error', 'Validasi Gagal', errData.message || `HTTP ${response.status}`);
                                this.playAudio('error'); return;
                            }

                            const data = await response.json();
                            if (data.status === 'success') {
                                this.peminjamInfo = { id: data.peminjam_id, type: data.peminjam_type, name: data.name, sub_info: data.sub_info };
                                this.activeLoans = data.active_loans || [];
                                this.draftCart = [];
                                this.showFeedback('success', data.name, 'Kartu Anggota Tervalidasi');
                                this.playAudio('success');
                                this.scanState = 'BUKU';
                            } else {
                                this.showFeedback('error', 'Validasi Gagal', data.message || 'Kartu anggota tidak ditemukan.');
                                this.playAudio('error');
                            }

                        } else if (this.scanState === 'BUKU') {
                            const exists = this.draftCart.some(item => (item.kode_eksemplar || '').toLowerCase() === currentBarcode.toLowerCase());
                            if (exists) {
                                this.showFeedback('error', 'Buku Sudah Ada', `Kode <strong>${currentBarcode}</strong> sudah ada di keranjang.`);
                                this.playAudio('error'); this.isLoading = false; return;
                            }

                            // 1. Cek langsung apakah buku ini sedang dipinjam oleh anggota ini
                            const matchingLoan = this.activeLoans.find(loan => 
                                (loan.kode_eksemplar || '').toLowerCase() === currentBarcode.toLowerCase()
                            );

                            if (matchingLoan) {
                                if (this.isInCart(matchingLoan.eksemplar_id)) {
                                    this.showFeedback('error', 'Buku Sudah Ada', `Buku <strong>${matchingLoan.buku_title}</strong> sudah ada di keranjang.`);
                                    this.playAudio('error');
                                    this.isLoading = false;
                                    return;
                                }

                                // Buka popup modal tindakan (Mengembalikan Buku / Perpanjang Buku)
                                this.openLoanActionModal(matchingLoan);
                                this.showFeedback('idle', '', '');
                                this.playAudio('success');
                                this.isLoading = false;
                                return;
                            }

                            // 2. Jika bukan pinjaman aktif, kirim ke server (buku baru / tersedia)
                            const response = await fetch('/portal-perpustakaan/sirkulasi-kiosk/process', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({ jenis_scan: 'CHECK_BUKU', barcode: currentBarcode, peminjam_id: this.peminjamInfo.id, peminjam_type: this.peminjamInfo.type })
                            });

                            if (!response.ok) {
                                if (response.status === 419) { window.location.reload(); return; }
                                const errData = await response.json().catch(() => ({ message: `Kesalahan server (${response.status})` }));
                                this.showFeedback('error', 'Pengecekan Gagal', errData.message || `HTTP ${response.status}`);
                                this.playAudio('error'); return;
                            }

                            const data = await response.json();
                            if (data.status === 'success') {
                                if (data.action_type === 'KEMBALI') {
                                    // Jika server mendeteksi buku ini sedang dipinjam, buka popup modal tindakan
                                    const loanObj = this.activeLoans.find(l => l.eksemplar_id === data.eksemplar_id) || {
                                        peminjaman_id: data.peminjaman_id,
                                        eksemplar_id: data.eksemplar_id,
                                        kode_eksemplar: data.kode_eksemplar,
                                        buku_title: data.buku_title,
                                        tanggal_jatuh_tempo: data.tanggal_jatuh_tempo || '-',
                                        is_terlambat: data.is_terlambat || false,
                                        can_extend: data.can_extend !== false
                                    };
                                    this.openLoanActionModal(loanObj);
                                    this.showFeedback('idle', '', '');
                                    this.playAudio('success');
                                } else {
                                    // Buku baru/tersedia: langsung masuk keranjang pinjaman baru
                                    this.draftCart.push({
                                        eksemplar_id: data.eksemplar_id,
                                        kode_eksemplar: data.kode_eksemplar,
                                        buku_title: data.buku_title,
                                        action_type: data.action_type,
                                        action_label: data.action_label,
                                        can_extend: data.can_extend || false
                                    });
                                    this.showFeedback('success', 'Buku Masuk Keranjang', `<strong>${data.buku_title}</strong> (${data.action_label})`);
                                    this.playAudio('success');
                                }
                            } else if (data.status === 'referensi') {
                                this.showFeedback('referensi', '⚠️ Koleksi Referensi', data.message || 'Buku referensi tidak boleh dipinjam.');
                                this.playAudio('error');
                            } else {
                                this.showFeedback('error', 'Pengecekan Gagal', data.message || 'Buku tidak dapat diproses.');
                                this.playAudio('error');
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showFeedback('network_error', 'Gagal Terhubung', 'Terjadi gangguan jaringan atau error pada server.');
                        this.playAudio('network');
                    } finally {
                        this.isLoading = false;
                        this.refocusInput();
                    }
                },

                async submitBatchTransaction() {
                    if (this.draftCart.length === 0) return;
                    this.isLoading = true;
                    try {
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";
                        const response = await fetch('/portal-perpustakaan/sirkulasi-kiosk/process', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ jenis_scan: 'SUBMIT_BATCH', peminjam_id: this.peminjamInfo.id, peminjam_type: this.peminjamInfo.type, items: this.draftCart })
                        });

                        if (!response.ok) {
                            if (response.status === 419) { window.location.reload(); return; }
                            const errData = await response.json().catch(() => ({ message: `Kesalahan (${response.status})` }));
                            this.showFeedback('error', 'Transaksi Gagal', errData.message || `HTTP ${response.status}`);
                            this.playAudio('error'); return;
                        }

                        const data = await response.json();
                        if (data.status === 'success_batch') {
                            // Hapus buku yang sudah diproses dari daftar pinjaman aktif
                            const processedIds = this.draftCart.map(item => item.eksemplar_id);
                            this.activeLoans = this.activeLoans.filter(loan => !processedIds.includes(loan.eksemplar_id));
                            // Simpan ringkasan transaksi untuk popup
                            this.successModal = {
                                open: true,
                                memberName: this.peminjamInfo.name,
                                items: [...this.draftCart],
                            };
                            this.draftCart = [];
                            this.showFeedback('idle', '', '');
                            this.playAudio('success');
                            this.scheduleResetToPeminjam();
                        } else {
                            this.showFeedback('error', 'Transaksi Gagal', data.message || 'Gagal memproses transaksi.');
                            this.playAudio('error');
                        }
                    } catch (error) {
                        this.showFeedback('network_error', 'Gagal Terhubung', 'Terjadi kesalahan saat memproses submit.');
                        this.playAudio('network');
                    } finally {
                        this.isLoading = false;
                        this.refocusInput();
                    }
                },

                showFeedback(state, title, message) {
                    this.feedbackState = state;
                    this.feedbackTitle = title;
                    this.feedbackMessage = message;
                },

                playAudio(type) {
                    try {
                        const idMap = { success: 'audio-success-p', error: 'audio-error-p', network: 'audio-network-p' };
                        const el = document.getElementById(idMap[type]);
                        if (el) { el.currentTime = 0; el.play().catch(e => {}); }
                    } catch (e) {}
                },

                scheduleResetToPeminjam() {
                    if (this.resetTimer) clearTimeout(this.resetTimer);
                    this.resetTimer = setTimeout(() => { this.resetToPeminjam(); }, 5000);
                }
            }
        }
    </script>
</div>
