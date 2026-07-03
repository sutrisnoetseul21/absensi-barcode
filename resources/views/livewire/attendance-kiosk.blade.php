<div class="min-h-screen bg-slate-100 flex items-center justify-center relative overflow-hidden" 
     x-data="kioskData()"
     x-init="initKiosk()"
     wire:ignore>
    
    <!-- Audio Elements -->
    <audio id="audio-success" src="/audio/beep.mp3" preload="auto"></audio>
    <audio id="audio-error" src="/audio/buzz.mp3" preload="auto"></audio>
    <audio id="audio-holiday" src="/audio/chime.mp3" preload="auto"></audio>
    <audio id="audio-network" src="/audio/siren.mp3" preload="auto"></audio>

    <!-- Overlay "Sentuh Layar" -->
    <div x-show="!isActive" 
         class="absolute inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer transition-opacity duration-300"
         @click="activateKiosk()">
        <svg class="w-24 h-24 text-white mb-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
        <h1 class="text-4xl font-bold text-white tracking-wider">Sentuh Layar Untuk Mengaktifkan Kios</h1>
        <p class="text-slate-300 mt-4 text-xl">Sistem Absensi {{ $settings->school_name ?? 'Sekolah' }}</p>
    </div>

    <!-- Hidden Input Container -->
    <input type="text" 
           x-ref="barcodeInput" 
           x-model="barcode"
           @keydown.enter="submitScan()"
           @keydown.escape="barcode = ''"
           @blur="refocusInput()"
           class="absolute opacity-0 w-0 h-0"
           autofocus
           autocomplete="off">

    <!-- Main Card -->
    <div class="relative w-full max-w-3xl mx-4">
        <!-- Glassmorphism Card -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/40 overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600/90 text-white py-6 px-8 text-center relative">
                @if($settings && $settings->school_logo_path)
                    <img src="{{ asset('storage/'.$settings->school_logo_path) }}" alt="Logo" class="w-16 h-16 mx-auto mb-2 object-contain">
                @endif
                <h2 class="text-3xl font-bold">{{ $settings->school_name ?? 'Kios Absensi' }}</h2>
                <p class="text-blue-100 mt-1">Silakan scan kartu Anda pada alat scanner</p>
                
                <!-- Loading Indicator -->
                <div x-show="isLoading" class="absolute top-4 right-6 flex items-center space-x-2 bg-black/20 rounded-full px-3 py-1">
                    <div class="w-2 h-2 bg-white rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
            </div>

            <!-- Body / Feedback Area -->
            <div class="p-10 min-h-[350px] flex flex-col items-center justify-center relative transition-colors duration-300"
                 :class="{
                     'bg-green-50': statusState === 'success',
                     'bg-yellow-50': statusState === 'warning',
                     'bg-red-50': statusState === 'error',
                     'bg-slate-200': statusState === 'holiday',
                     'bg-orange-50': statusState === 'network_error',
                     'bg-transparent': statusState === 'idle'
                 }">
                
                <!-- Idle State -->
                <div x-show="statusState === 'idle' && !isLoading" class="text-center text-slate-400">
                    <svg class="w-32 h-32 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    <p class="text-2xl font-medium text-slate-500 animate-pulse">Menunggu Scan Barcode...</p>
                </div>

                <!-- Feedback State -->
                <div x-show="statusState !== 'idle'" class="text-center w-full transform transition-all duration-300"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     style="display: none;">
                    
                    <div class="relative inline-block mb-6">
                        <template x-if="photoUrl">
                            <img :src="photoUrl" class="w-40 h-40 rounded-full border-4 shadow-lg object-cover" 
                                 :class="borderColorClass">
                        </template>
                        <template x-if="!photoUrl && statusState !== 'network_error'">
                            <div class="w-40 h-40 rounded-full border-4 shadow-lg flex items-center justify-center bg-white"
                                 :class="borderColorClass">
                                <svg class="w-20 h-20 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                        </template>
                        <template x-if="statusState === 'network_error'">
                            <div class="w-40 h-40 rounded-full border-4 shadow-lg flex items-center justify-center bg-white border-orange-500">
                                <svg class="w-20 h-20 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M9 9a3 3 0 00-3 3v2m3-5a3 3 0 013-3m5 3v-2a3 3 0 00-3-3M21 21a9 9 0 01-9 9m9-9a9 9 0 00-9-9"></path></svg>
                            </div>
                        </template>
                        
                        <!-- Icon Badge -->
                        <div class="absolute bottom-0 right-0 w-12 h-12 rounded-full border-4 border-white flex items-center justify-center text-white"
                             :class="badgeColorClass">
                             <svg x-show="statusState === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                             <svg x-show="statusState === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             <svg x-show="statusState === 'error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                             <svg x-show="statusState === 'holiday'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                             <svg x-show="statusState === 'network_error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>

                    <h3 class="text-4xl font-bold text-slate-800 mb-2" x-text="studentName"></h3>
                    <p class="text-2xl font-medium" :class="textColorClass" x-text="statusMessage"></p>
                    
                    <template x-if="lateMinutes > 0">
                        <p class="mt-3 text-lg text-yellow-700 bg-yellow-100 inline-block px-4 py-1 rounded-full font-semibold">
                            Terlambat: <span x-text="lateMinutes"></span> Menit
                        </p>
                    </template>
                </div>
            </div>
            
            <!-- Footer Input Debug -->
            <div class="bg-slate-50 border-t border-slate-100 p-4 flex justify-between items-center text-sm text-slate-500">
                <div>Input Buffer: <span class="font-mono text-slate-800" x-text="barcode"></span></div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full" :class="isActive ? 'bg-green-500' : 'bg-red-500'"></span>
                    <span x-text="isActive ? 'Kios Aktif' : 'Menunggu Aktivasi'"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Click handler for anywhere outside to refocus -->
    <div class="fixed inset-0 -z-10" @click="refocusInput()"></div>

    <script>
        function kioskData() {
            return {
                isActive: false,
                barcode: '',
                isLoading: false,
                
                // Feedback state
                statusState: 'idle', // idle, success, warning, error, holiday, network_error
                studentName: '',
                photoUrl: null,
                statusMessage: '',
                lateMinutes: 0,
                
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
                        if (val.length >= 10 && this.isActive && !this.isLoading) {
                            this.submitScan();
                        }
                    });
                },
                
                activateKiosk() {
                    this.isActive = true;
                    // Play a silent sound to unlock audio context in browsers
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
                        
                        const response = await fetch('/scan', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ barcode: currentBarcode })
                        });
                        
                        // Handle CSRF Token Mismatch or other HTTP errors
                        if (!response.ok) {
                            if (response.status === 419) {
                                // CSRF Expired, reload page
                                window.location.reload();
                                return;
                            }
                            
                            const errorData = await response.json().catch(() => ({}));
                            this.showFeedback('error', 'Error Sistem', null, errorData.message || `Terjadi kesalahan (Kode: ${response.status})`);
                            this.playAudio('error');
                            return;
                        }

                        const data = await response.json();
                        this.handleResponse(data);
                    } catch (error) {
                        console.error('Network error:', error);
                        this.showFeedback('network_error', 'Gagal Terhubung', null, 'Terjadi gangguan jaringan atau server.');
                        this.playAudio('network');
                    } finally {
                        this.isLoading = false;
                        this.refocusInput();
                    }
                },
                
                handleResponse(data) {
                    const status = data.status;
                    
                    if (status === 'duplicate_request') {
                        // Silent ignore
                        return;
                    }
                    
                    this.lateMinutes = data.late_minutes || 0;
                    
                    switch(status) {
                        case 'success_on_time':
                            this.showFeedback('success', data.name, data.photo_url, 'Berhasil Hadir Tepat Waktu');
                            this.playAudio('success');
                            break;
                        case 'success_late':
                            this.showFeedback('warning', data.name, data.photo_url, 'Berhasil Hadir (Terlambat)');
                            this.playAudio('success'); // Pakai beep biasa, tapi UI beda
                            break;
                        case 'already_scanned':
                            this.showFeedback('error', data.name || 'Siswa', data.photo_url, 'Sudah Melakukan Absensi Hari Ini');
                            this.playAudio('error');
                            break;
                        case 'not_found':
                            this.showFeedback('error', 'Barcode Tidak Dikenali', null, data.message || 'Siswa tidak terdaftar di sistem.');
                            this.playAudio('error');
                            break;
                        case 'barcode_inactive':
                            this.showFeedback('error', 'Kartu Dinonaktifkan', null, 'Silakan hubungi administrator.');
                            this.playAudio('error');
                            break;
                        case 'holiday':
                            this.showFeedback('holiday', data.name || 'Informasi', data.photo_url, 'Hari Ini Libur');
                            this.playAudio('holiday');
                            break;
                        default:
                            this.showFeedback('error', 'Error Sistem', null, 'Status tidak dikenali.');
                            this.playAudio('error');
                    }
                },
                
                showFeedback(state, name, photo, message) {
                    this.statusState = state;
                    this.studentName = name;
                    this.photoUrl = photo;
                    this.statusMessage = message;
                    
                    // Set auto-reset ke idle setelah 3 detik
                    this.resetTimer = setTimeout(() => {
                        this.statusState = 'idle';
                        this.studentName = '';
                        this.photoUrl = null;
                        this.statusMessage = '';
                        this.lateMinutes = 0;
                    }, 3000);
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
                        'border-green-500': this.statusState === 'success',
                        'border-yellow-500': this.statusState === 'warning',
                        'border-red-500': this.statusState === 'error',
                        'border-slate-500': this.statusState === 'holiday',
                    };
                },
                
                get badgeColorClass() {
                    return {
                        'bg-green-500 border-green-50': this.statusState === 'success',
                        'bg-yellow-500 border-yellow-50': this.statusState === 'warning',
                        'bg-red-500 border-red-50': this.statusState === 'error',
                        'bg-slate-500 border-slate-50': this.statusState === 'holiday',
                        'bg-orange-500 border-orange-50': this.statusState === 'network_error',
                    };
                },
                
                get textColorClass() {
                    return {
                        'text-green-600': this.statusState === 'success',
                        'text-yellow-600': this.statusState === 'warning',
                        'text-red-600': this.statusState === 'error',
                        'text-slate-600': this.statusState === 'holiday',
                        'text-orange-600': this.statusState === 'network_error',
                    };
                }
            }
        }
    </script>
</div>
