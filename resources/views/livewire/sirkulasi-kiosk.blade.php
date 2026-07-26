<div class="min-h-screen bg-slate-100 flex items-center justify-center relative overflow-hidden" 
     x-data="sirkulasiKioskData()"
     x-init="initKiosk()"
     wire:ignore>
    
    <!-- Audio Elements -->
    <audio id="audio-success" src="/audio/beep.mp3" preload="auto"></audio>
    <audio id="audio-error" src="/audio/buzz.mp3" preload="auto"></audio>
    <audio id="audio-network" src="/audio/siren.mp3" preload="auto"></audio>

    <!-- Overlay "Sentuh Layar" -->
    <div x-show="!isActive" 
         class="absolute inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer transition-opacity duration-300"
         @click="activateKiosk()">
        <svg class="w-24 h-24 text-white mb-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
        <h1 class="text-4xl font-bold text-white tracking-wider">Sentuh Layar Untuk Mengaktifkan Kiosk Sirkulasi</h1>
        <p class="text-slate-300 mt-4 text-xl">Modul Perpustakaan {{ $settings->school_name ?? 'Sekolah' }}</p>
    </div>

    <!-- Hidden Input Container -->
    <input type="text" 
           x-ref="barcodeInput" 
           x-model="barcode"
           @keydown.enter="submitScan()"
           @keydown.escape="resetToPeminjam()"
           @blur="refocusInput()"
           class="absolute opacity-0 w-0 h-0"
           autofocus
           autocomplete="off">

    <!-- Main Card -->
    <div class="relative w-full max-w-4xl mx-4">
        <!-- Top Toolbar -->
        <div class="absolute -top-12 left-0 right-0 flex justify-between items-center px-2">
            <a href="/admin-perpustakaan" class="text-slate-500 hover:text-slate-800 font-medium flex items-center bg-white/50 px-4 py-2 rounded-full backdrop-blur-sm transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Dashboard
            </a>
            <button @click="resetToPeminjam()" x-show="scanState === 'BUKU'" class="text-white bg-red-500 hover:bg-red-600 font-medium px-4 py-2 rounded-full shadow transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Batalkan & Reset
            </button>
        </div>

        <!-- Glassmorphism Card -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white overflow-hidden flex flex-col md:flex-row min-h-[500px]">
            
            <!-- Left Side: State Info -->
            <div class="w-full md:w-1/3 bg-indigo-700 text-white p-8 flex flex-col relative transition-all duration-300"
                 :class="{
                    'bg-indigo-700': scanState === 'PEMINJAM',
                    'bg-green-700': scanState === 'BUKU'
                 }">
                <div class="mb-8">
                    @if($settings && $settings->school_logo_path)
                        <img src="{{ asset('storage/'.$settings->school_logo_path) }}" alt="Logo" class="w-16 h-16 mb-4 object-contain drop-shadow-md">
                    @endif
                    <h2 class="text-2xl font-bold leading-tight">Sirkulasi<br/>Perpustakaan</h2>
                </div>
                
                <div class="flex-grow flex flex-col justify-center">
                    <div class="space-y-6">
                        <!-- Step 1 Indicator -->
                        <div class="flex items-center space-x-4 transition-opacity" :class="scanState === 'PEMINJAM' ? 'opacity-100' : 'opacity-50'">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg"
                                 :class="scanState === 'PEMINJAM' ? 'bg-white text-indigo-700 ring-4 ring-indigo-300' : 'bg-indigo-800 text-indigo-300'">
                                <svg x-show="scanState === 'BUKU'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                <span x-show="scanState === 'PEMINJAM'">1</span>
                            </div>
                            <div class="text-left">
                                <h3 class="font-bold text-lg">Scan Anggota</h3>
                                <p class="text-sm opacity-80" x-text="scanState === 'BUKU' ? 'Selesai' : 'Scan kartu siswa/guru'"></p>
                            </div>
                        </div>

                        <!-- Vertical Line -->
                        <div class="w-0.5 h-8 bg-white/20 ml-5"></div>

                        <!-- Step 2 Indicator -->
                        <div class="flex items-center space-x-4 transition-opacity" :class="scanState === 'BUKU' ? 'opacity-100' : 'opacity-40'">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg"
                                 :class="scanState === 'BUKU' ? 'bg-white text-green-700 ring-4 ring-green-300' : 'bg-indigo-800 text-indigo-300'">
                                2
                            </div>
                            <div class="text-left">
                                <h3 class="font-bold text-lg">Scan Buku</h3>
                                <p class="text-sm opacity-80" x-text="scanState === 'BUKU' ? 'Pinjam atau Kembali' : 'Menunggu anggota...'"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Overlay on Left Side -->
                <div x-show="isLoading" class="absolute inset-0 bg-black/20 flex flex-col items-center justify-center backdrop-blur-sm z-10">
                    <svg class="w-12 h-12 text-white animate-spin mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="font-medium tracking-wider">Memproses...</span>
                </div>
            </div>

            <!-- Right Side: Interaction & Feedback -->
            <div class="w-full md:w-2/3 p-10 flex flex-col relative transition-colors duration-300"
                 :class="{
                     'bg-green-50': feedbackState === 'success',
                     'bg-red-50': feedbackState === 'error',
                     'bg-orange-50': feedbackState === 'network_error',
                     'bg-white': feedbackState === 'idle'
                 }">

                <!-- Active Peminjam Card (Shown during Step 2) -->
                <div x-show="scanState === 'BUKU'" style="display: none;" class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center shadow-sm">
                    <div class="w-14 h-14 bg-indigo-200 rounded-full flex items-center justify-center mr-4 text-indigo-700 flex-shrink-0">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm text-indigo-600 font-semibold uppercase tracking-wider" x-text="peminjamInfo.type"></p>
                        <h4 class="text-xl font-bold text-slate-800 leading-tight" x-text="peminjamInfo.name"></h4>
                        <p class="text-sm text-slate-500" x-text="peminjamInfo.sub_info"></p>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex-grow flex flex-col items-center justify-center text-center">
                    
                    <!-- IDLE STATE -->
                    <div x-show="feedbackState === 'idle'" class="w-full">
                        <div class="w-32 h-32 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-6 animate-pulse">
                            <svg x-show="scanState === 'PEMINJAM'" class="w-16 h-16 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            <svg x-show="scanState === 'BUKU'" style="display: none;" class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-700 mb-2" x-text="scanState === 'PEMINJAM' ? 'Silakan Scan Kartu Anggota' : 'Silakan Scan Barcode Buku'"></h3>
                        <p class="text-slate-500 text-lg">Input otomatis terfokus. Gunakan alat scanner Anda.</p>
                    </div>

                    <!-- FEEDBACK STATE -->
                    <div x-show="feedbackState !== 'idle'" style="display: none;" class="w-full transform transition-all duration-300"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                        
                        <!-- Icon -->
                        <div class="relative inline-block mb-6">
                            <div class="w-32 h-32 rounded-full border-4 shadow-lg flex items-center justify-center bg-white"
                                 :class="borderColorClass">
                                <!-- Success Peminjam -->
                                <svg x-show="feedbackState === 'success' && scanState === 'BUKU'" class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                
                                <!-- Success Buku (Pinjam/Kembali) -->
                                <svg x-show="feedbackState === 'success' && scanState === 'PEMINJAM'" class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                                <!-- Error -->
                                <svg x-show="feedbackState === 'error'" class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                
                                <!-- Network Error -->
                                <svg x-show="feedbackState === 'network_error'" class="w-16 h-16 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M9 9a3 3 0 00-3 3v2m3-5a3 3 0 013-3m5 3v-2a3 3 0 00-3-3M21 21a9 9 0 01-9 9m9-9a9 9 0 00-9-9"></path></svg>
                            </div>
                        </div>

                        <!-- Message -->
                        <h3 class="text-3xl font-bold text-slate-800 mb-3" x-text="feedbackTitle"></h3>
                        <p class="text-xl font-medium" :class="textColorClass" x-html="feedbackMessage"></p>
                    </div>

                </div>
            </div>
        </div>
        
        <!-- Footer Input Debug -->
        <div class="mt-4 bg-white/50 border border-slate-200 rounded-xl p-4 flex justify-between items-center text-sm text-slate-500 backdrop-blur-sm shadow-sm">
            <div>Scanner Buffer: <span class="font-mono text-slate-800 font-bold bg-slate-200 px-2 py-0.5 rounded" x-text="barcode || '...'"></span></div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full" :class="isActive ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></span>
                <span x-text="isActive ? 'Kiosk Aktif & Siap Scan' : 'Menunggu Aktivasi'"></span>
            </div>
        </div>
    </div>

    <!-- Click handler for anywhere outside to refocus -->
    <div class="fixed inset-0 -z-10" @click="refocusInput()"></div>

    <script>
        function sirkulasiKioskData() {
            return {
                isActive: false,
                barcode: '',
                isLoading: false,
                
                // STATE: 'PEMINJAM' atau 'BUKU'
                scanState: 'PEMINJAM',
                
                // Data state peminjam yg sedang aktif (didapat dr scan pertama)
                peminjamInfo: {
                    id: null,
                    type: null,
                    name: '',
                    sub_info: ''
                },
                
                // Feedback UI State: 'idle', 'success', 'error', 'network_error'
                feedbackState: 'idle',
                feedbackTitle: '',
                feedbackMessage: '',
                
                // Timers
                resetTimer: null,
                refocusInterval: null,
                
                initKiosk() {
                    // Cek autofocus berkala untuk memastikan selalu fokus
                    this.refocusInterval = setInterval(() => {
                        this.refocusInput();
                    }, 2000);
                    
                    // Listen to barcode length
                    this.$watch('barcode', (val) => {
                        if (val.length >= 4 && this.isActive && !this.isLoading) {
                            // Tunggu sejenak jika scanner ngetik cepat sebelum auto-submit.
                            // Tapi karena pakai @keydown.enter, kita andalkan Enter saja.
                        }
                    });
                },
                
                activateKiosk() {
                    this.isActive = true;
                    try {
                        let audio = document.getElementById('audio-success');
                        if (audio) {
                            audio.volume = 0;
                            audio.play().then(() => {
                                audio.pause();
                                audio.currentTime = 0;
                                audio.volume = 1;
                            }).catch(e => console.log('Audio unlock failed:', e));
                        }
                    } catch (e) {}
                    
                    setTimeout(() => this.refocusInput(), 100);
                },
                
                refocusInput() {
                    if (this.isActive && this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                    }
                },
                
                resetToPeminjam() {
                    this.scanState = 'PEMINJAM';
                    this.peminjamInfo = { id: null, type: null, name: '', sub_info: '' };
                    this.showFeedback('idle', '', '');
                    this.barcode = '';
                    this.refocusInput();
                },
                
                async submitScan() {
                    const currentBarcode = this.barcode.trim();
                    this.barcode = ''; // Langsung bersihkan untuk input berikutnya
                    
                    if (currentBarcode.length === 0) return;
                    
                    this.isLoading = true;
                    
                    // Jika ada timer reset, matikan (interrupt)
                    if (this.resetTimer) {
                        clearTimeout(this.resetTimer);
                    }
                    
                    try {
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
                        
                        const payload = {
                            jenis_scan: this.scanState,
                            barcode: currentBarcode,
                            // Jika scan buku, kirimkan juga data peminjam
                            peminjam_id: this.peminjamInfo.id,
                            peminjam_type: this.peminjamInfo.type
                        };
                        
                        const response = await fetch('/admin-perpustakaan/sirkulasi/process', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        if (!response.ok) {
                            if (response.status === 419) {
                                window.location.reload();
                                return;
                            }
                            
                            const errorData = await response.json().catch(() => ({}));
                            this.showFeedback('error', 'Transaksi Gagal', errorData.message || `Terjadi kesalahan (Kode: ${response.status})`);
                            this.playAudio('error');
                            this.scheduleReset('idle', 4000); // Reset ke idle setelah error (tidak reset state peminjam!)
                            return;
                        }

                        const data = await response.json();
                        this.handleResponse(data);
                        
                    } catch (error) {
                        console.error('Network error:', error);
                        this.showFeedback('network_error', 'Gagal Terhubung', 'Terjadi gangguan jaringan atau server.');
                        this.playAudio('network');
                        this.scheduleReset('idle', 4000);
                    } finally {
                        this.isLoading = false;
                        this.refocusInput();
                    }
                },
                
                handleResponse(data) {
                    const status = data.status;
                    
                    if (this.scanState === 'PEMINJAM') {
                        if (status === 'success') {
                            // Anggota ditemukan
                            this.peminjamInfo = {
                                id: data.peminjam_id,
                                type: data.peminjam_type,
                                name: data.name,
                                sub_info: data.sub_info
                            };
                            this.showFeedback('success', data.name, 'Kartu Anggota Tervalidasi');
                            this.playAudio('success');
                            
                            // Lanjut ke state BUKU setelah 1 detik
                            this.scheduleReset('idle', 1500, () => {
                                this.scanState = 'BUKU';
                            });
                        } else if (status === 'inactive') {
                            // Kartu nonaktif
                            this.showFeedback('error', 'Kartu Nonaktif', data.message || 'Kartu ini telah dinonaktifkan/hilang.');
                            this.playAudio('error');
                            this.scheduleReset('idle', 4000);
                        } else {
                            // Tidak ditemukan
                            this.showFeedback('error', 'Tidak Ditemukan', data.message || 'Kartu anggota tidak terdaftar.');
                            this.playAudio('error');
                            this.scheduleReset('idle', 4000);
                        }
                    } else if (this.scanState === 'BUKU') {
                        if (status === 'success_pinjam') {
                            this.showFeedback('success', 'Buku Berhasil Dipinjam', `<strong>${data.buku_title}</strong><br>Jatuh Tempo: ${data.jatuh_tempo}`);
                            this.playAudio('success');
                            this.scheduleResetToPeminjam();
                        } else if (status === 'success_kembali') {
                            this.showFeedback('success', 'Buku Berhasil Dikembalikan', `<strong>${data.buku_title}</strong><br>Terima kasih.`);
                            this.playAudio('success');
                            this.scheduleResetToPeminjam();
                        } else {
                            // Error (misal: buku dipinjam orang lain, dll)
                            this.showFeedback('error', 'Transaksi Gagal', data.message || 'Buku tidak dapat diproses.');
                            this.playAudio('error');
                            this.scheduleReset('idle', 4000);
                        }
                    }
                },
                
                showFeedback(state, title, message) {
                    this.feedbackState = state;
                    this.feedbackTitle = title;
                    this.feedbackMessage = message;
                },
                
                scheduleReset(targetFeedbackState, ms, callback = null) {
                    if (this.resetTimer) clearTimeout(this.resetTimer);
                    this.resetTimer = setTimeout(() => {
                        this.feedbackState = targetFeedbackState;
                        if (targetFeedbackState === 'idle') {
                            this.feedbackTitle = '';
                            this.feedbackMessage = '';
                        }
                        if (callback) callback();
                    }, ms);
                },
                
                scheduleResetToPeminjam() {
                    if (this.resetTimer) clearTimeout(this.resetTimer);
                    this.resetTimer = setTimeout(() => {
                        this.resetToPeminjam();
                    }, 4000); // 4 detik agar bisa dibaca
                },
                
                playAudio(type) {
                    const id = `audio-${type}`;
                    const audio = document.getElementById(id);
                    if (audio) {
                        audio.currentTime = 0;
                        audio.play().catch(e => console.log('Autoplay prevented:', e));
                    }
                },
                
                get borderColorClass() {
                    return {
                        'border-green-500': this.feedbackState === 'success',
                        'border-red-500': this.feedbackState === 'error',
                        'border-orange-500': this.feedbackState === 'network_error',
                        'border-slate-200': this.feedbackState === 'idle',
                    };
                },
                
                get textColorClass() {
                    return {
                        'text-green-600': this.feedbackState === 'success',
                        'text-red-600': this.feedbackState === 'error',
                        'text-orange-600': this.feedbackState === 'network_error',
                        'text-slate-600': this.feedbackState === 'idle',
                    };
                }
            }
        }
    </script>
</div>
