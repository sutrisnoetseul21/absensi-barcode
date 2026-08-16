<div class="min-h-screen bg-slate-100 flex items-center justify-center relative overflow-hidden" 
     x-data="kioskData()"
     x-init="initKiosk()"
     wire:ignore>
    
    <!-- Include Html5Qrcode Library -->
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

    <style>
        #reader-nis video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 1rem;
        }
        #reader-nis__scan_region {
            background: transparent !important;
        }
        #reader-nis__dashboard {
            display: none !important;
        }
    </style>

    <!-- Audio Elements -->
    <audio id="audio-success" src="/audio/beep.mp3" preload="auto"></audio>
    <audio id="audio-error" src="/audio/buzz.mp3" preload="auto"></audio>
    <audio id="audio-holiday" src="/audio/chime.mp3" preload="auto"></audio>
    <audio id="audio-network" src="/audio/siren.mp3" preload="auto"></audio>

    <!-- Overlay "Sentuh Layar" -->
    @if(!$isGlobalHoliday)
    <div x-show="!isActive" 
         class="absolute inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer transition-opacity duration-300"
         @click="activateKiosk()">
        <svg class="w-24 h-24 text-white mb-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
        <h1 class="text-4xl font-bold text-white tracking-wider">Sentuh Layar Untuk Mengaktifkan Presensi Digital</h1>
        <p class="text-slate-300 mt-4 text-xl">Sistem Presensi Digital {{ $settings->school_name ?? 'Sekolah' }}</p>
    </div>
    @endif

    <!-- Hidden Input Container -->
    @if(!$isGlobalHoliday)
    <input type="text" 
           x-ref="barcodeInput" 
           x-model="barcode"
           @keydown.enter="submitScan()"
           @keydown.escape="barcode = ''"
           @blur="refocusInput()"
           class="absolute opacity-0 w-0 h-0"
           autofocus
           autocomplete="off">
    @endif

    <!-- Main Container: Wider to accommodate history -->
    <div class="relative w-full max-w-6xl mx-4">
        <!-- Glassmorphism Container with Flex Row -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/40 overflow-hidden flex flex-col md:flex-row min-h-[550px]">
            
            <!-- Left Side: Scanner UI -->
            <div class="w-full md:w-7/12 flex flex-col relative">
                <!-- Header -->
                <div class="bg-blue-600/95 text-white py-8 px-8 text-center relative flex flex-col items-center justify-center">
                    @if($settings && $settings->school_logo_path)
                        <img src="{{ asset('storage/'.$settings->school_logo_path) }}" alt="Logo" class="w-24 h-24 mx-auto mb-3 object-contain drop-shadow-md">
                    @else
                        <!-- Fallback Logo if none uploaded -->
                        <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3 backdrop-blur-sm border border-white/30">
                            <svg class="w-12 h-12 text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>
                        </div>
                    @endif
                    <h2 class="text-3xl font-bold tracking-tight">{{ $settings->school_name ?? 'Presensi Digital' }}</h2>
                    <p class="text-blue-100 mt-2 font-medium bg-blue-700/50 px-4 py-1 rounded-full text-sm inline-block">Mode NIS</p>
                    
                    <!-- Loading Indicator -->
                    <div x-show="isLoading" class="absolute top-4 right-6 flex items-center space-x-2 bg-black/20 rounded-full px-3 py-1">
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>

                <!-- Body / Feedback Area -->
                <div class="flex-1 p-10 pb-20 flex flex-col items-center justify-center relative transition-colors duration-300"
                     :class="{
                         'bg-green-50/80': statusState === 'success',
                         'bg-yellow-50/80': statusState === 'warning',
                         'bg-red-50/80': statusState === 'error',
                         'bg-slate-200/80': statusState === 'holiday',
                         'bg-orange-50/80': statusState === 'network_error',
                         'bg-transparent': statusState === 'idle'
                     }">
                    
                    <!-- Idle State: Hardware Scanner Visualizer -->
                    <div x-show="statusState === 'idle' && !isCameraActive && !isLoading" class="text-center text-slate-400">
                        <div class="w-32 h-32 mx-auto mb-6 flex items-center justify-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300">
                            <svg class="w-16 h-16 text-slate-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <p class="text-2xl font-bold text-slate-500">Silakan Scan NIS</p>
                        <p class="text-slate-400 mt-2">Arahkan kartu pada scanner yang tersedia</p>
                    </div>

                    <!-- Idle State: Camera Visualizer (Replaces central visualizer when camera is active) -->
                    <div x-show="statusState === 'idle' && isCameraActive && !isLoading" class="flex flex-col items-center justify-center w-full max-w-xs mx-auto" style="display: none;">
                        <div id="reader-nis" class="w-full h-56 bg-slate-900 rounded-2xl overflow-hidden shadow-xl border-2 border-blue-500/80 relative"></div>
                        <p class="text-xs font-semibold text-blue-600 mt-3 flex items-center gap-1.5 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            Arahkan Barcode / QR Kartu NIS ke Kamera
                        </p>
                    </div>

                    <!-- Feedback State -->
                    <div x-show="statusState !== 'idle'" class="text-center w-full transform transition-all duration-300"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         style="display: none;">
                        
                        <div class="relative inline-block mb-6">
                            <template x-if="photoUrl">
                                <img :src="photoUrl" class="w-48 h-48 rounded-full border-8 shadow-xl object-cover" 
                                     :class="borderColorClass">
                            </template>
                            <template x-if="!photoUrl && statusState !== 'network_error'">
                                <div class="w-48 h-48 rounded-full border-8 shadow-xl flex items-center justify-center bg-white"
                                     :class="borderColorClass">
                                    <svg class="w-24 h-24 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                            </template>
                            <template x-if="statusState === 'network_error'">
                                <div class="w-48 h-48 rounded-full border-8 shadow-xl flex items-center justify-center bg-white border-orange-500">
                                    <svg class="w-24 h-24 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M9 9a3 3 0 00-3 3v2m3-5a3 3 0 013-3m5 3v-2a3 3 0 00-3-3M21 21a9 9 0 01-9 9m9-9a9 9 0 00-9-9"></path></svg>
                                </div>
                            </template>
                            
                            <!-- Icon Badge -->
                            <div class="absolute bottom-1 right-1 w-14 h-14 rounded-full border-4 border-white flex items-center justify-center text-white shadow-md"
                                 :class="badgeColorClass">
                                 <svg x-show="statusState === 'success'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                 <svg x-show="statusState === 'warning'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                 <svg x-show="statusState === 'error'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                 <svg x-show="statusState === 'holiday'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                 <svg x-show="statusState === 'network_error'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>

                        <h3 class="text-4xl font-bold text-slate-800 mb-2 tracking-tight" x-text="studentName"></h3>
                        <p class="text-2xl font-semibold mt-2" :class="textColorClass" x-text="statusMessage"></p>
                        
                        <template x-if="lateMinutes > 0">
                            <p class="mt-4 text-lg text-yellow-700 bg-yellow-100 inline-block px-5 py-1.5 rounded-full font-bold shadow-sm border border-yellow-200">
                                Terlambat: <span x-text="lateMinutes"></span> Menit
                            </p>
                        </template>
                    </div>
                </div>
                
                <!-- Footer Input Debug & Camera Control (Bottom Right) -->
                <div class="bg-white/80 border-t border-slate-100 p-3 px-4 flex justify-between items-center text-sm text-slate-500 absolute bottom-0 w-full rounded-bl-3xl z-20">
                    @if(!$isGlobalHoliday)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-xs" x-text="barcode || '---'"></span>
                        <div class="flex items-center space-x-1.5 ml-2">
                            <span class="w-2.5 h-2.5 rounded-full shadow-inner animate-pulse" :class="isActive ? 'bg-green-500' : 'bg-red-500'"></span>
                            <span class="font-semibold text-xs" x-text="isActive ? 'Sistem Aktif' : 'Menunggu Aktivasi'"></span>
                        </div>
                    </div>

                    <!-- Bottom Right Controls: Camera Selection & Toggle -->
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
                        
                        <!-- Logout Form -->
                        <form action="{{ route('portal-presensi.logout') }}" method="POST" class="inline-block m-0 p-0 ml-1">
                            @csrf
                            <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-xs transition-all duration-200 shadow-sm border bg-slate-50 border-slate-200 text-slate-500 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200" title="Tutup Kiosk (Logout)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="w-full text-center font-semibold text-slate-600">Sistem Presensi Dinonaktifkan (Hari Libur)</div>
                    @endif
                </div>
            </div>

            <!-- Right Side: Recent History -->
            <div class="w-full md:w-5/12 bg-slate-50/90 border-l border-slate-200/60 flex flex-col z-10 backdrop-blur-md rounded-br-3xl overflow-hidden pb-4">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-white/50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Baru Saja Presensi
                    </h3>
                    <span class="text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-1 rounded-md" x-text="recentScans.length + ' Data'"></span>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 space-y-3 relative" style="scrollbar-width: thin;">
                    <template x-if="recentScans.length === 0">
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 opacity-60">
                            <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            <p class="font-medium text-sm">Belum ada riwayat (Lokal)</p>
                        </div>
                    </template>
                    
                    <template x-for="(scan, index) in recentScans" :key="scan.id">
                        <div class="bg-white border border-slate-100 rounded-xl p-3 flex items-center gap-3 shadow-sm transform transition-all duration-500 ease-out"
                             x-transition:enter="opacity-0 -translate-x-4 scale-95"
                             x-transition:enter-end="opacity-100 translate-x-0 scale-100">
                            <!-- Avatar -->
                            <div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                <template x-if="scan.photo_url">
                                    <img :src="scan.photo_url" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!scan.photo_url">
                                    <span class="text-sm font-bold text-slate-400" x-text="scan.name.substring(0,2).toUpperCase()"></span>
                                </template>
                            </div>
                            
                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 truncate" x-text="scan.name"></h4>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-slate-500 font-medium" x-text="scan.class_name"></span>
                                    <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                    <span class="text-[11px] font-mono text-slate-400" x-text="scan.time"></span>
                                </div>
                            </div>
                            
                            <!-- Badge -->
                            <div class="flex-shrink-0">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-md uppercase tracking-wider"
                                      :class="{
                                          'bg-emerald-100 text-emerald-700': scan.status === 'success_on_time' || scan.status === 'success_out',
                                          'bg-amber-100 text-amber-700': scan.status === 'success_late',
                                          'bg-slate-100 text-slate-600': scan.status === 'already_scanned' || scan.status === 'holiday',
                                          'bg-red-100 text-red-700': scan.status === 'error'
                                      }" x-text="scan.status === 'success_on_time' ? 'Datang' : (scan.status === 'success_out' ? 'Pulang' : (scan.status === 'success_late' ? 'Telat' : (scan.status === 'already_scanned' ? 'Sudah Absen' : 'Libur')))">
                                </span>
                            </div>
                        </div>
                    </template>
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
                statusState: @json($isGlobalHoliday ? 'holiday' : 'idle'),
                studentName: @json($isGlobalHoliday ? 'Hari Ini Libur' : ''),
                photoUrl: null,
                statusMessage: @json($isGlobalHoliday ? 'Sistem Presensi Digital Dinonaktifkan.' : ''),
                lateMinutes: 0,
                
                // History Array
                recentScans: [],
                
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
                    if (!this.isActive) {
                        this.activateKiosk();
                    }

                    // Reset candidate counter
                    this.candidateCameraCode = null;
                    this.candidateCameraCount = 0;
                    this.lastSuccessfulScanCode = null;

                    // 1. Cek ketersediaan navigator.mediaDevices
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        alert('Browser Anda memblokir akses kamera (perlu HTTPS atau alamat http://localhost:8001).');
                        return;
                    }

                    // 2. Minta izin akses kamera secara langsung ke browser terlebih dahulu
                    try {
                        const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
                        tempStream.getTracks().forEach(track => track.stop());
                    } catch (permErr) {
                        console.error("Izin kamera ditolak:", permErr);
                        alert('Akses kamera ditolak oleh browser atau sedang digunakan aplikasi lain. Mohon izinkan akses kamera di browser Anda.');
                        return;
                    }

                    // 3. Ambil daftar perangkat kamera setelah izin diberikan
                    await this.getAvailableCameras();

                    this.isCameraActive = true;
                    this.$nextTick(async () => {
                        try {
                            if (this.html5QrcodeScanner) {
                                await this.html5QrcodeScanner.stop().catch(() => {});
                            }
                            this.html5QrcodeScanner = new Html5Qrcode("reader-nis", {
                                experimentalFeatures: {
                                    useBarCodeDetectorIfSupported: true
                                }
                            });

                            // Jika ada kamera yang terdaftar gunakan ID-nya, jika tidak gunakan facingMode
                            const cameraConfig = (this.cameraList.length > 0 && this.selectedCameraId)
                                ? this.selectedCameraId
                                : { facingMode: "environment" };

                            await this.html5QrcodeScanner.start(
                                cameraConfig,
                                {
                                    fps: 15,
                                    qrbox: { width: 250, height: 140 }, // Rasio memanjang pas untuk Barcode Batang 1D
                                    aspectRatio: 1.333333
                                },
                                (decodedText) => {
                                    this.onCameraScan(decodedText);
                                },
                                (errorMessage) => {}
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
                            } catch(fallbackErr) {
                                console.error("Fallback kamera juga gagal:", fallbackErr);
                                this.isCameraActive = false;
                                alert('Gagal memuat video stream kamera. Pastikan kamera terhubung dan izin telah diberikan di browser.');
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
                    
                    // ANTI-SPAM LOGIC:
                    // Jika barcode SAMA dengan yang baru saja sukses discan, beri jeda panjang (6 detik)
                    // agar tidak terus-terusan mengabsen orang yang sama jika kartunya belum dijauhkan.
                    if (cleanCode === this.lastSuccessfulScanCode) {
                        if (now - this.lastCameraScanTime < 6000) return;
                    } else {
                        // Jika barcode BEDA (siswa lain), cukup jeda 1 detik untuk menghindari request bersamaan
                        if (now - this.lastCameraScanTime < 1000) return;
                    }

                    if (this.isLoading) return;

                    // Konfirmasi Pembacaan Ganda (Double Verification) agar barcode 1D terbaca stabil & utuh
                    if (this.candidateCameraCode !== cleanCode) {
                        this.candidateCameraCode = cleanCode;
                        this.candidateCameraCount = 1;
                        return;
                    } else {
                        this.candidateCameraCount++;
                    }

                    // Hanya kirim ke server setelah terbaca stabil (2 frame berturut-turut persis sama)
                    if (this.candidateCameraCount >= 2) {
                        this.lastCameraScanTime = now;
                        this.lastSuccessfulScanCode = cleanCode;
                        this.candidateCameraCode = null;
                        this.candidateCameraCount = 0;

                        this.submitScan(cleanCode);
                        // Kamera dibiarkan tetap menyala untuk siswa berikutnya!
                    }
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
                
                async submitScan(overrideBarcode = null) {
                    const currentBarcode = (overrideBarcode || this.barcode).trim();
                    this.barcode = ''; 
                    
                    if (currentBarcode.length === 0) return;
                    
                    this.isLoading = true;
                    if (this.resetTimer) clearTimeout(this.resetTimer);
                    
                    try {
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('{{ route('kiosk.process-nis') }}', {
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
                            this.showFeedback('error', 'Error Sistem', null, errorData.message || `Terjadi kesalahan (Kode: ${response.status})`);
                            this.playAudio('error');
                            return;
                        }

                        const data = await response.json();
                        this.handleResponse(data);
                    } catch (error) {
                        this.showFeedback('network_error', 'Gagal Terhubung', null, 'Terjadi gangguan jaringan atau server.');
                        this.playAudio('network');
                    } finally {
                        this.isLoading = false;
                        this.refocusInput();
                    }
                },
                
                handleResponse(data) {
                    const status = data.status;
                    if (status === 'duplicate_request') return;
                    
                    this.lateMinutes = data.late_minutes || 0;
                    
                    // Push to history for specific statuses
                    if (['success_on_time', 'success_late', 'already_scanned', 'success_out'].includes(status) && data.name) {
                        this.addToHistory({
                            id: Date.now() + Math.random(), // unique key
                            name: data.name,
                            class_name: data.class_name || '-',
                            photo_url: data.photo_url,
                            status: status,
                            time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
                        });
                    }
                    
                    switch(status) {
                        case 'success_on_time':
                            this.showFeedback('success', data.name, data.photo_url, 'Berhasil Hadir Tepat Waktu');
                            this.playAudio('success');
                            break;
                        case 'success_late':
                            this.showFeedback('warning', data.name, data.photo_url, 'Berhasil Hadir (Terlambat)');
                            this.playAudio('success'); 
                            break;
                        case 'already_scanned':
                            this.showFeedback('error', data.name || 'Siswa', data.photo_url, 'Sudah Melakukan Presensi Hari Ini');
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
                        case 'success_out':
                            this.showFeedback('success', data.name, data.photo_url, data.message || 'Berhasil Absen Pulang');
                            this.playAudio('success');
                            break;
                        case 'blocked_status':
                        case 'already_scanned_out':
                        case 'too_early_out':
                        case 'rejected_no_scan_in':
                        case 'rejected_late_in':
                            this.showFeedback('error', data.name || 'Ditolak', data.photo_url, data.message || 'Presensi ditolak');
                            this.playAudio('error');
                            break;
                        default:
                            this.showFeedback('error', 'Error Sistem', null, 'Status tidak dikenali.');
                            this.playAudio('error');
                    }
                },
                
                addToHistory(entry) {
                    this.recentScans.unshift(entry);
                    // Keep only top 10
                    if (this.recentScans.length > 10) {
                        this.recentScans.pop();
                    }
                },
                
                showFeedback(state, name, photo, message) {
                    this.statusState = state;
                    this.studentName = name;
                    this.photoUrl = photo;
                    this.statusMessage = message;
                    
                    @if(!$isGlobalHoliday)
                    this.resetTimer = setTimeout(() => {
                        this.statusState = 'idle';
                        this.studentName = '';
                        this.photoUrl = null;
                        this.statusMessage = '';
                        this.lateMinutes = 0;
                    }, 3000);
                    @endif
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
