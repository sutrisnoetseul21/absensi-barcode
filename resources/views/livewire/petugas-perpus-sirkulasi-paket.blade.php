<div class="p-4 lg:p-6 max-w-6xl mx-auto space-y-4">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Sirkulasi Buku Paket</h1>
                <span class="px-2.5 py-1 text-[11px] font-black uppercase tracking-wider rounded-lg bg-indigo-100 text-indigo-700 border border-indigo-200">
                    Durasi 1 Tahun
                </span>
            </div>
            <p class="text-slate-500 text-sm mt-1">Layanan Peminjaman Multi-Buku Paket Pelajaran Tahunan Siswa (1 Tahun Ajaran).</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('portal-perpustakaan.peminjaman-paket') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-2xl font-bold text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Data Pinjaman Paket
            </a>
        </div>
    </div>

    <!-- Embedded Kiosk Widget -->
    <div class="bg-slate-100 rounded-3xl overflow-hidden shadow-lg border border-slate-200"
         x-data="sirkulasiPaketData()"
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

        <!-- Hidden Barcode Input for Hardware Scanner -->
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
        <div class="flex flex-col md:flex-row min-h-[580px]">

            <!-- Left Panel (Gradient Card) -->
            <div class="w-full md:w-5/12 bg-gradient-to-b from-indigo-700 via-indigo-800 to-indigo-950 text-white p-6 lg:p-8 flex flex-col relative transition-all duration-300">
                <div class="mb-6">
                    @if($settings && $settings->school_logo_path)
                        <img src="{{ asset('storage/'.$settings->school_logo_path) }}" alt="Logo" class="w-12 h-12 mb-3 object-contain drop-shadow-md">
                    @endif
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="text-2xl font-black leading-tight">Peminjaman<br/>Buku Paket</h2>
                    </div>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-indigo-100 border border-white/20">
                        Durasi Pinjam 1 Tahun
                    </span>
                    <p class="text-xs text-indigo-200 font-medium mt-2">Peminjaman paket buku pelajaran siswa selama 1 tahun ajaran.</p>
                </div>

                <!-- Member Info (Step 2: When Member is Scanned) -->
                <div x-show="scanState === 'BUKU'" style="display:none;" class="mb-6 space-y-4">
                    <div class="p-4 bg-white/10 rounded-2xl border border-white/20 flex items-center gap-3 shadow-inner">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center flex-shrink-0 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="flex-grow overflow-hidden">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest block" x-text="peminjamInfo.type"></span>
                                <template x-if="peminjamInfo.kelas">
                                    <span class="px-1.5 py-0.2 rounded bg-amber-400/30 text-amber-200 text-[10px] font-bold" x-text="'Kelas ' + peminjamInfo.kelas"></span>
                                </template>
                            </div>
                            <h4 class="text-sm font-extrabold text-white truncate" x-text="peminjamInfo.name"></h4>
                            <p class="text-xs text-indigo-100 truncate" x-text="peminjamInfo.sub_info"></p>
                        </div>
                    </div>

                    <!-- Active Package Loans (Buku Paket yang Sedang Dipinjam) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-300">
                            <span>Buku Paket Aktif (1 Tahun)</span>
                            <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[11px]" x-text="activeLoans.length + ' Buku'"></span>
                        </div>
                        <div class="max-h-44 overflow-y-auto space-y-2 pr-1">
                            <template x-for="loan in activeLoans" :key="loan.peminjaman_id">
                                <div class="w-full p-2.5 rounded-xl border border-white/15 bg-white/10 text-xs flex justify-between items-center gap-2 text-left">
                                    <div class="overflow-hidden">
                                        <p class="font-bold text-white truncate" x-text="loan.buku_title"></p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-[10px] text-slate-300 font-mono" x-text="loan.kode_eksemplar"></span>
                                            <template x-if="loan.grade_level">
                                                <span class="text-[9px] px-1 py-0.2 rounded bg-white/20 text-indigo-200" x-text="'Kls ' + loan.grade_level"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                              :class="loan.is_terlambat ? 'bg-rose-500/80 text-white' : 'bg-emerald-500/30 text-emerald-200'"
                                              x-text="loan.is_terlambat ? 'Lewat Tahun' : loan.tanggal_jatuh_tempo"></span>
                                    </div>
                                </div>
                            </template>
                            <div x-show="activeLoans.length === 0" class="text-center py-3 text-xs text-slate-400 italic bg-white/5 rounded-xl">
                                Belum meminjam buku paket.
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
                            <h3 class="font-bold text-sm">2. Scan Buku Paket &amp; Selesaikan</h3>
                            <p class="text-[11px] opacity-80" x-text="scanState === 'BUKU' ? draftCart.length + ' buku paket di keranjang' : 'Menunggu anggota...'"></p>
                        </div>
                    </div>
                </div>

                <!-- Loading Overlay (Left Panel) -->
                <div x-show="isLoading" class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center backdrop-blur-sm z-20">
                    <svg class="w-10 h-10 text-white animate-spin mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="font-bold text-sm text-white">Memproses...</span>
                </div>
            </div>

            <!-- Right Panel (Interactive Area) -->
            <div class="w-full md:w-7/12 p-6 lg:p-8 flex flex-col relative bg-slate-50/60">

                <!-- Reset Button -->
                <button @click="resetToPeminjam()" x-show="scanState === 'BUKU'"
                        class="absolute top-4 right-4 text-white bg-rose-600 hover:bg-rose-700 font-bold text-xs px-3.5 py-1.5 rounded-full shadow transition flex items-center gap-1.5 z-10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Ganti Anggota
                </button>

                <!-- Camera Scanner Visualizer -->
                <div x-show="isCameraActive" class="flex flex-col items-center justify-center w-full max-w-sm mx-auto mb-4" style="display: none;">
                    <div id="reader" class="w-full h-52 bg-slate-900 rounded-3xl overflow-hidden shadow-xl border-2 border-emerald-500/80 relative"></div>
                    <p class="text-xs font-semibold text-emerald-600 mt-3 flex items-center gap-1.5 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span x-text="scanState === 'PEMINJAM' ? 'Arahkan Barcode Kartu Siswa ke Kamera' : 'Arahkan Barcode Buku Paket ke Kamera'"></span>
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
                        <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 mb-2">Silakan Scan Kartu Anggota</h3>
                    <p class="text-slate-500 text-sm max-w-sm">Dekatkan stiker barcode pada kartu presensi siswa atau guru ke scanner.</p>
                    
                    <div class="mt-5 flex items-center gap-2 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-full font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        Scanner Aktif & Siap
                    </div>

                    <!-- Manual Input Fallback -->
                    <div class="mt-6 w-full max-w-xs flex gap-2">
                        <input type="text"
                               x-model="manualMemberInput"
                               @keydown.enter="submitManualMember()"
                               placeholder="Ketik NISN / Barcode manual..."
                               class="flex-1 px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-200 focus:border-indigo-600">
                        <button type="button" @click="submitManualMember()" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                            Cari
                        </button>
                    </div>
                </div>

                <!-- STATE 2: Keranjang Peminjaman Paket (Multi-Buku) -->
                <div x-show="scanState === 'BUKU'" style="display:none;" class="flex-grow flex flex-col space-y-3">
                    <!-- Scan Prompt & Manual Barcode Input -->
                    <div class="p-3 bg-white border border-slate-200 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-xs">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Siap Scan Barcode Buku Paket</span>
                        </div>
                        <div class="flex items-center gap-1.5 w-full sm:w-auto">
                            <input type="text"
                                   x-model="manualBookInput"
                                   @keydown.enter="submitManualBook()"
                                   placeholder="Kode eksemplar..."
                                   class="px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg w-36 font-mono">
                            <button type="button" @click="submitManualBook()" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">
                                + Masukkan
                            </button>
                        </div>
                    </div>

                    <!-- Cart Table -->
                    <div class="flex-grow flex flex-col bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                        <div class="px-4 py-3 bg-slate-100/70 border-b border-slate-200 flex justify-between items-center text-xs font-extrabold text-slate-700">
                            <span>Daftar Buku Paket yang Diambil</span>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-[11px]" x-text="draftCart.length + ' Buku'"></span>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold">Masa Pinjam: 1 Tahun</span>
                            </div>
                        </div>

                        <div class="flex-grow overflow-y-auto max-h-64 p-3 space-y-2">
                            <template x-for="(item, index) in draftCart" :key="item.kode_eksemplar">
                                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-white flex items-center justify-between gap-3 transition-all">
                                    <div class="flex-grow overflow-hidden space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                PAKET 1 TAHUN
                                            </span>
                                            <span class="font-mono text-[11px] font-bold text-slate-600" x-text="item.kode_eksemplar"></span>
                                            <template x-if="item.grade_level">
                                                <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[9px] font-bold" x-text="'Tingkat ' + item.grade_level"></span>
                                            </template>
                                        </div>
                                        <h5 class="font-bold text-slate-900 text-xs truncate" x-text="item.buku_title"></h5>
                                        <p class="text-[10px] text-slate-400">Jatuh Tempo: <strong class="text-indigo-600" x-text="item.tanggal_jatuh_tempo || '1 Tahun ke depan'"></strong></p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <button type="button" @click="removeFromCart(index)"
                                                class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 rounded-lg transition-all" title="Hapus Buku Ini">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="draftCart.length === 0" class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                                <svg class="w-12 h-12 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <p class="text-xs font-bold text-slate-500">Keranjang Buku Paket Kosong</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Scan barcode buku paket satu per satu untuk menambahkannya ke keranjang.</p>
                            </div>
                        </div>

                        <!-- Submit Bar -->
                        <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
                            <button type="button" @click="clearCart()" x-show="draftCart.length > 0"
                                    class="px-3 py-2 text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold transition-all">
                                Kosongkan Keranjang
                            </button>
                            <button type="button" @click="submitBatchTransaction()" :disabled="draftCart.length === 0"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="'Selesaikan Pinjaman Paket (' + draftCart.length + ' Buku - 1 Tahun)'"></span>
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
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-indigo-100">
                    <!-- Header Indigo -->
                    <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 px-8 pt-10 pb-8 text-center">
                        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-5 animate-pulse">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-2xl font-extrabold text-white">Pinjaman Paket Berhasil!</h3>
                        <p class="text-indigo-100 text-base mt-1 font-medium" x-text="successModal.memberName"></p>
                        <span class="inline-block mt-2 px-3 py-1 bg-white/20 rounded-full text-xs font-bold text-white">Durasi 1 Tahun Ajaran</span>
                    </div>

                    <!-- Daftar Item Transaksi -->
                    <div class="px-8 py-6">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Ringkasan Buku Paket</p>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <template x-for="item in successModal.items" :key="item.eksemplar_id">
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-indigo-100 text-indigo-700">1 TAHUN</span>
                                    <p class="font-semibold text-slate-800 truncate" x-text="item.buku_title"></p>
                                </div>
                            </template>
                        </div>

                        <div class="mt-6 flex gap-2">
                            <button type="button" @click="successModal.open = false; resetToPeminjam()"
                                    class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                                Pinjam Siswa Lain
                            </button>
                            <a href="{{ route('portal-perpustakaan.peminjaman-paket') }}"
                               class="py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center">
                                Ke Tabel Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Click to refocus anywhere -->
    <div class="fixed inset-0 -z-10" @click="$refs.barcodeInput && $refs.barcodeInput.focus()"></div>

    <script>
        function sirkulasiPaketData() {
            return {
                barcode: '',
                manualMemberInput: '',
                manualBookInput: '',
                isLoading: false,

                scanState: 'PEMINJAM',

                peminjamInfo: { id: null, type: null, name: '', kelas: '', sub_info: '' },
                activeLoans: [],
                draftCart: [],

                // Success Modal
                successModal: { open: false, memberName: '', items: [] },

                // Feedback Banner
                feedbackState: 'idle',
                feedbackTitle: '',
                feedbackMessage: '',
                feedbackTimer: null,

                // Camera Scanner
                isCameraActive: false,
                html5QrcodeScanner: null,
                cameraList: [],
                selectedCameraId: null,
                candidateCameraCode: null,
                candidateCameraCount: 0,
                lastCameraScanTime: 0,
                lastSuccessfulScanCode: null,

                initKiosk() {
                    setTimeout(() => this.refocusInput(), 300);
                    this.refocusInterval = setInterval(() => {
                        this.refocusInput();
                    }, 3000);
                },

                refocusInput() {
                    if (this.$refs.barcodeInput && !this.isCameraActive) {
                        this.$refs.barcodeInput.focus();
                    }
                },

                resetToPeminjam() {
                    this.scanState = 'PEMINJAM';
                    this.peminjamInfo = { id: null, type: null, name: '', kelas: '', sub_info: '' };
                    this.activeLoans = [];
                    this.draftCart = [];
                    this.manualMemberInput = '';
                    this.manualBookInput = '';
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

                submitManualMember() {
                    if (this.manualMemberInput.trim().length > 0) {
                        this.submitScan(this.manualMemberInput.trim());
                        this.manualMemberInput = '';
                    }
                },

                submitManualBook() {
                    if (this.manualBookInput.trim().length > 0) {
                        this.submitScan(this.manualBookInput.trim());
                        this.manualBookInput = '';
                    }
                },

                async submitScan(overrideBarcode = null) {
                    const currentBarcode = (overrideBarcode || this.barcode).trim();
                    this.barcode = '';
                    if (this.$refs.barcodeInput) this.$refs.barcodeInput.value = '';

                    if (currentBarcode.length === 0) return;

                    this.isLoading = true;

                    try {
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";

                        if (this.scanState === 'PEMINJAM') {
                            const response = await fetch("{{ route('portal-perpustakaan.peminjaman-paket.process') }}", {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({ jenis_scan: 'PEMINJAM', barcode: currentBarcode })
                            });

                            if (!response.ok) {
                                if (response.status === 419) { window.location.reload(); return; }
                                const errData = await response.json().catch(() => ({ message: `Kesalahan server (${response.status})` }));
                                this.showFeedback('error', 'Validasi Gagal', errData.message || `HTTP ${response.status}`);
                                this.playAudio('error');
                                return;
                            }

                            const data = await response.json();
                            if (data.status === 'success') {
                                this.peminjamInfo = {
                                    id: data.peminjam_id,
                                    type: data.peminjam_type,
                                    name: data.name,
                                    kelas: data.kelas,
                                    sub_info: data.sub_info
                                };
                                this.activeLoans = data.active_loans || [];
                                this.draftCart = [];
                                this.showFeedback('success', data.name, 'Kartu Anggota Tervalidasi (' + data.sub_info + ')');
                                this.playAudio('success');
                                this.scanState = 'BUKU';
                            } else {
                                this.showFeedback('error', 'Validasi Gagal', data.message || 'Kartu anggota tidak ditemukan.');
                                this.playAudio('error');
                            }

                        } else if (this.scanState === 'BUKU') {
                            const exists = this.draftCart.some(item => (item.kode_eksemplar || '').toLowerCase() === currentBarcode.toLowerCase());
                            if (exists) {
                                this.showFeedback('error', 'Buku Sudah Ada di Keranjang', `Kode <strong>${currentBarcode}</strong> sudah masuk ke dalam daftar.`);
                                this.playAudio('error');
                                this.isLoading = false;
                                this.refocusInput();
                                return;
                            }

                            const response = await fetch("{{ route('portal-perpustakaan.peminjaman-paket.process') }}", {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({
                                    jenis_scan: 'CHECK_BUKU',
                                    barcode: currentBarcode,
                                    peminjam_id: this.peminjamInfo.id,
                                    peminjam_type: this.peminjamInfo.type
                                })
                            });

                            if (!response.ok) {
                                const errData = await response.json().catch(() => ({ message: 'Gagal menghubungi server' }));
                                this.showFeedback('error', 'Kesalahan', errData.message || 'Gagal memproses buku.');
                                this.playAudio('error');
                                return;
                            }

                            const data = await response.json();
                            if (data.status === 'success') {
                                this.draftCart.push({
                                    eksemplar_id: data.eksemplar_id,
                                    kode_eksemplar: data.kode_eksemplar,
                                    buku_title: data.buku_title,
                                    grade_level: data.grade_level,
                                    action_type: data.action_type || 'PINJAM',
                                    tanggal_jatuh_tempo: data.tanggal_jatuh_tempo || '1 Tahun ke depan',
                                });
                                this.showFeedback('success', 'Buku Paket Ditambahkan', `<strong>${data.buku_title}</strong> [1 Tahun]`);
                                this.playAudio('success');
                            } else {
                                this.showFeedback('error', 'Buku Tidak Tersedia', data.message || 'Buku tidak dapat dipinjam.');
                                this.playAudio('error');
                            }
                        }
                    } catch (e) {
                        console.error(e);
                        this.showFeedback('network_error', 'Gangguan Jaringan', 'Terjadi kesalahan saat memproses data.');
                        this.playAudio('network_error');
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
                        const response = await fetch("{{ route('portal-perpustakaan.peminjaman-paket.process') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({
                                jenis_scan: 'SUBMIT_BATCH',
                                peminjam_id: this.peminjamInfo.id,
                                peminjam_type: this.peminjamInfo.type,
                                items: this.draftCart
                            })
                        });

                        const data = await response.json();
                        if (data.status === 'success') {
                            this.successModal = {
                                open: true,
                                memberName: this.peminjamInfo.name,
                                items: [...this.draftCart]
                            };
                            this.playAudio('success');
                        } else {
                            this.showFeedback('error', 'Transaksi Gagal', data.message || 'Gagal menyelesaikan pinjaman paket.');
                            this.playAudio('error');
                        }
                    } catch (e) {
                        console.error(e);
                        this.showFeedback('network_error', 'Gangguan Jaringan', 'Gagal memproses transaksi.');
                        this.playAudio('network_error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                showFeedback(state, title, message) {
                    this.feedbackState = state;
                    this.feedbackTitle = title;
                    this.feedbackMessage = message;
                    if (this.feedbackTimer) clearTimeout(this.feedbackTimer);
                    if (state !== 'idle') {
                        this.feedbackTimer = setTimeout(() => {
                            this.feedbackState = 'idle';
                        }, 5000);
                    }
                },

                playAudio(type) {
                    try {
                        let audioId = 'audio-success-p';
                        if (type === 'error') audioId = 'audio-error-p';
                        if (type === 'network_error') audioId = 'audio-network-p';
                        const el = document.getElementById(audioId);
                        if (el) {
                            el.currentTime = 0;
                            el.play().catch(() => {});
                        }
                    } catch (e) {}
                },

                // Camera methods
                async getAvailableCameras() {
                    try {
                        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        const videoDevices = devices.filter(d => d.kind === 'videoinput');
                        this.cameraList = videoDevices.map(d => ({ id: d.deviceId, label: d.label }));
                        if (videoDevices.length > 0 && !this.selectedCameraId) {
                            this.selectedCameraId = videoDevices[0].id;
                        }
                    } catch (err) {}
                },

                async toggleCamera() {
                    if (this.isCameraActive) {
                        await this.stopCamera();
                    } else {
                        await this.startCamera();
                    }
                },

                async startCamera() {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        alert('Browser Anda memblokir akses kamera.');
                        return;
                    }

                    await this.getAvailableCameras();
                    this.isCameraActive = true;

                    this.$nextTick(async () => {
                        try {
                            if (this.html5QrcodeScanner) {
                                await this.html5QrcodeScanner.stop().catch(() => {});
                            }
                            this.html5QrcodeScanner = new Html5Qrcode("reader");
                            await this.html5QrcodeScanner.start(
                                this.selectedCameraId || { facingMode: "environment" },
                                { fps: 15, qrbox: { width: 250, height: 140 } },
                                (decodedText) => { this.submitScan(decodedText); },
                                () => {}
                            );
                        } catch (err) {
                            console.error("Kamera gagal:", err);
                            this.isCameraActive = false;
                        }
                    });
                },

                async stopCamera() {
                    if (this.html5QrcodeScanner) {
                        try { await this.html5QrcodeScanner.stop(); } catch (e) {}
                        this.html5QrcodeScanner = null;
                    }
                    this.isCameraActive = false;
                }
            };
        }
    </script>
</div>
