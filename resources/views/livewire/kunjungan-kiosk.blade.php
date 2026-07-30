<div class="min-h-screen bg-slate-100 flex items-center justify-center relative overflow-hidden font-sans text-slate-800" 
     x-data="kunjunganKioskData()"
     x-init="initKiosk()"
     wire:ignore>
    
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

    <!-- Audio Elements -->
    <audio id="audio-success" src="/audio/beep.mp3" preload="auto"></audio>
    <audio id="audio-error" src="/audio/buzz.mp3" preload="auto"></audio>

    <!-- Soft Ambient Light Background Blobs -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-300/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-300/20 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Hidden Barcode Input -->
    <input type="text" 
           x-ref="barcodeInput" 
           x-model="barcode"
           @keydown.enter="submitScan()"
           @keydown.escape="barcode = ''"
           @blur="refocusInput()"
           class="absolute opacity-0 w-0 h-0"
           autofocus
           autocomplete="off">

    <!-- Main Container -->
    <div class="relative w-full max-w-6xl mx-4 my-6">
        <!-- Light Glassmorphism Card -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/60 overflow-hidden flex flex-col md:flex-row min-h-[580px]">
            
            <!-- Left Side: Scanner UI & Feedback -->
            <div class="w-full md:w-7/12 flex flex-col relative border-b md:border-b-0 md:border-r border-slate-200">
                <!-- Header -->
                <div class="bg-blue-600/95 text-white py-6 px-8 text-center relative flex flex-col items-center justify-center shadow-sm">
                    <a href="{{ route('perpustakaan.dashboard') }}" class="absolute left-6 top-6 text-blue-100 hover:text-white transition flex items-center gap-1 text-xs font-semibold bg-white/20 px-3 py-1.5 rounded-full border border-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Portal Perpustakaan
                    </a>

                    @if($settings && $settings->school_logo_path)
                        <img src="{{ asset('storage/'.$settings->school_logo_path) }}" alt="Logo" class="w-16 h-16 mx-auto mb-2 object-contain drop-shadow-md">
                    @else
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-2 backdrop-blur-sm border border-white/30">
                            <svg class="w-8 h-8 text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                    @endif
                    <h2 class="text-2xl font-bold tracking-tight">Presensi Kunjungan Perpustakaan</h2>
                    <p class="text-blue-100 mt-1 text-xs font-medium bg-blue-700/50 px-3 py-0.5 rounded-full inline-block">
                        {{ $settings->school_name ?? 'Sistem Presensi Digital' }}
                    </p>
                    
                    <!-- Loading Indicator -->
                    <div x-show="isLoading" class="absolute top-6 right-6 flex items-center space-x-1.5 bg-black/20 rounded-full px-3 py-1">
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>

                <!-- Feedback & Scanner Body -->
                <div class="flex-1 p-8 pb-20 flex flex-col items-center justify-center relative transition-colors duration-300"
                     :class="{
                         'bg-emerald-50/80': statusState === 'success',
                         'bg-amber-50/80': statusState === 'already_scanned',
                         'bg-rose-50/80': statusState === 'error',
                         'bg-transparent': statusState === 'idle'
                     }">
                    
                    <!-- Idle State: Physical Barcode Scanner Visualizer -->
                    <div x-show="statusState === 'idle' && !isCameraActive && !isLoading" class="text-center text-slate-400 my-auto">
                        <div class="w-28 h-28 mx-auto mb-5 flex items-center justify-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 shadow-sm">
                            <svg class="w-14 h-14 text-slate-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-600">Silakan Scan Kartu Anggota</h3>
                        <p class="text-slate-400 text-sm mt-1">Tempelkan barcode NISN / NIS / NIP pada alat scanner</p>
                    </div>

                    <!-- Idle State: Camera Scanner Visualizer -->
                    <div x-show="statusState === 'idle' && isCameraActive && !isLoading" class="flex flex-col items-center justify-center w-full max-w-xs mx-auto my-auto" style="display: none;">
                        <div id="reader" class="w-full h-52 bg-slate-900 rounded-2xl overflow-hidden shadow-xl border-2 border-blue-500/80 relative"></div>
                        <p class="text-xs font-semibold text-blue-600 mt-3 flex items-center gap-1.5 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            Arahkan Barcode Kartu Anggota ke Kamera
                        </p>
                    </div>

                    <!-- Feedback State -->
                    <div x-show="statusState !== 'idle'" class="text-center w-full transform transition-all duration-300 my-auto"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         style="display: none;">
                        
                        <div class="relative inline-block mb-4">
                            <template x-if="photoUrl">
                                <img :src="photoUrl" class="w-40 h-40 rounded-full border-4 shadow-xl object-cover" 
                                     :class="borderColorClass">
                            </template>
                            <template x-if="!photoUrl">
                                <div class="w-40 h-40 rounded-full border-4 shadow-xl flex items-center justify-center bg-white"
                                     :class="borderColorClass">
                                    <svg class="w-20 h-20 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                            </template>
                            
                            <!-- Icon Badge -->
                            <div class="absolute bottom-1 right-1 w-12 h-12 rounded-full border-4 border-white flex items-center justify-center text-white shadow-md"
                                 :class="badgeColorClass">
                                 <svg x-show="statusState === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                 <svg x-show="statusState === 'already_scanned'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                 <svg x-show="statusState === 'error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                        </div>

                        <h3 class="text-3xl font-extrabold text-slate-800 tracking-tight" x-text="visitorName"></h3>
                        <p class="text-sm font-semibold mt-1 text-slate-600" x-text="visitorClass"></p>
                        <p class="text-lg font-bold mt-3 px-4 py-1.5 rounded-full inline-block shadow-sm border" :class="statusPillClass" x-text="statusMessage"></p>
                    </div>
                </div>
                
                <!-- Footer Controls & Input Indicator -->
                <div class="bg-white/80 border-t border-slate-100 p-3 px-5 flex justify-between items-center text-xs text-slate-500 absolute bottom-0 w-full rounded-bl-3xl z-20">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-[11px] text-slate-600" x-text="barcode || '---'"></span>
                    </div>

                    <!-- Camera Toggle & Controls -->
                    <div class="flex items-center gap-2">
                        <template x-if="isCameraActive && cameraList.length > 1">
                            <select x-model="selectedCameraId" @change="changeCamera()" class="text-xs bg-slate-100 border border-slate-300 text-slate-700 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <template x-for="cam in cameraList" :key="cam.id">
                                    <option :value="cam.id" x-text="cam.label || 'Kamera (' + cam.id.substring(0, 5) + ')'"></option>
                                </template>
                            </select>
                        </template>

                        <button @click="toggleCamera()" 
                                type="button"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-xs transition-all duration-200 shadow-sm border"
                                :class="isCameraActive 
                                    ? 'bg-rose-50 border-rose-200 text-rose-600 hover:bg-rose-100' 
                                    : 'bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-100'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span x-text="isCameraActive ? 'Matikan Kamera' : 'Gunakan Kamera'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Recent Visits Panel -->
            <div class="w-full md:w-5/12 bg-slate-50/90 border-l border-slate-200/60 flex flex-col z-10 backdrop-blur-md rounded-br-3xl overflow-hidden pb-4">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-white/50">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Pengunjung Terbaru Hari Ini
                    </h3>
                    <span class="text-xs font-semibold bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full" x-text="recentScans.length + ' Orang'"></span>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 space-y-3 relative" style="scrollbar-width: thin;">
                    <template x-if="recentScans.length === 0">
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 opacity-60">
                            <svg class="w-14 h-14 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <p class="font-medium text-xs">Belum ada kunjungan tercatat hari ini</p>
                        </div>
                    </template>
                    
                    <template x-for="scan in recentScans" :key="scan.id">
                        <div class="bg-white border border-slate-100 rounded-xl p-3 flex items-center gap-3 shadow-sm transform transition-all duration-300">
                            <!-- Avatar -->
                            <div class="w-11 h-11 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-slate-500">
                                <template x-if="scan.photo_url">
                                    <img :src="scan.photo_url" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!scan.photo_url">
                                    <span class="text-xs font-bold text-slate-400" x-text="scan.name.substring(0,2).toUpperCase()"></span>
                                </template>
                            </div>
                            
                            <!-- Visitor Info -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-slate-800 truncate" x-text="scan.name"></h4>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[11px] text-slate-500 font-medium" x-text="scan.class_name"></span>
                                    <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                    <span class="text-[10px] font-mono text-slate-400" x-text="scan.time"></span>
                                </div>
                            </div>
                            
                            <!-- Badge Status -->
                            <div class="flex-shrink-0">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md uppercase tracking-wider bg-emerald-100 text-emerald-700">
                                    Hadir
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Click anywhere overlay to refocus scanner input -->
    <div class="fixed inset-0 -z-10" @click="refocusInput()"></div>

    <!-- Alpine Logic -->
    <script>
        function kunjunganKioskData() {
            return {
                barcode: '',
                isLoading: false,
                
                // Feedback state
                statusState: 'idle',
                visitorName: '',
                visitorClass: '',
                photoUrl: null,
                statusMessage: '',
                
                // History List
                recentScans: @json($recentScansData),
                
                // Timers
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
                
                initKiosk() {
                    this.refocusInterval = setInterval(() => {
                        this.refocusInput();
                    }, 2000);
                },

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
                                    this.selectedCameraId = devices[0].id;
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
                                : { facingMode: "user" };

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
                            console.error("Gagal memulai kamera:", err);
                            this.isCameraActive = false;
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
                
                refocusInput() {
                    if (this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                    }
                },
                
                async submitScan(overrideBarcode = null) {
                    const currentBarcode = (overrideBarcode || this.barcode).trim();
                    this.barcode = ''; 
                    
                    if (currentBarcode.length === 0) return;
                    
                    this.isLoading = true;
                    if (this.resetTimer) clearTimeout(this.resetTimer);
                    
                    try {
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('/perpustakaan/kunjungan/process', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ barcode: currentBarcode })
                        });
                        
                        if (!response.ok) {
                            if (response.status === 419) {
                                window.location.reload();
                                return;
                            }
                            const errorData = await response.json().catch(() => ({}));
                            this.showFeedback('error', 'Error Sistem', '', null, errorData.message || `Terjadi kesalahan (Kode: ${response.status})`);
                            this.playAudio('error');
                            return;
                        }

                        const data = await response.json();
                        this.handleResponse(data);
                    } catch (error) {
                        this.showFeedback('error', 'Gagal Terhubung', '', null, 'Terjadi gangguan jaringan atau server.');
                        this.playAudio('error');
                    } finally {
                        this.isLoading = false;
                        this.refocusInput();
                    }
                },
                
                handleResponse(data) {
                    const status = data.status;
                    if (status === 'duplicate_request') return;
                    
                    if (status === 'success') {
                        this.addToHistory({
                            id: Date.now() + Math.random(),
                            name: data.name,
                            class_name: data.class_name || 'Anggota',
                            photo_url: data.photo_url,
                            time: data.time || new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
                        });
                        this.showFeedback('success', data.name, data.class_name, data.photo_url, data.message || 'Selamat Datang di Perpustakaan!');
                        this.playAudio('success');
                    } else if (status === 'already_scanned') {
                        this.showFeedback('already_scanned', data.name, data.class_name, data.photo_url, data.message || 'Sudah mencatat kunjungan.');
                        this.playAudio('error');
                    } else {
                        this.showFeedback('error', 'Gagal', '', null, data.message || 'Kartu tidak dikenali.');
                        this.playAudio('error');
                    }
                },
                
                addToHistory(entry) {
                    this.recentScans.unshift(entry);
                    if (this.recentScans.length > 10) {
                        this.recentScans.pop();
                    }
                },
                
                showFeedback(state, name, class_name, photo, message) {
                    this.statusState = state;
                    this.visitorName = name;
                    this.visitorClass = class_name;
                    this.photoUrl = photo;
                    this.statusMessage = message;
                    
                    this.resetTimer = setTimeout(() => {
                        this.statusState = 'idle';
                        this.visitorName = '';
                        this.visitorClass = '';
                        this.photoUrl = null;
                        this.statusMessage = '';
                    }, 3500);
                },
                
                playAudio(type) {
                    const id = `audio-${type}`;
                    const audio = document.getElementById(id);
                    if (audio) {
                        audio.currentTime = 0;
                        audio.play().catch(e => console.log('Audio autoplay prevented:', e));
                    }
                },
                
                get borderColorClass() {
                    return {
                        'border-emerald-500': this.statusState === 'success',
                        'border-amber-500': this.statusState === 'already_scanned',
                        'border-rose-500': this.statusState === 'error',
                    };
                },
                
                get badgeColorClass() {
                    return {
                        'bg-emerald-500': this.statusState === 'success',
                        'bg-amber-500': this.statusState === 'already_scanned',
                        'bg-rose-500': this.statusState === 'error',
                    };
                },
                
                get statusPillClass() {
                    return {
                        'bg-emerald-100 border-emerald-200 text-emerald-800': this.statusState === 'success',
                        'bg-amber-100 border-amber-200 text-amber-800': this.statusState === 'already_scanned',
                        'bg-rose-100 border-rose-200 text-rose-800': this.statusState === 'error',
                    };
                }
            }
        }
    </script>
</div>
