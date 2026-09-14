<div class="min-h-screen bg-slate-100 flex items-center justify-center relative overflow-hidden font-jakarta" 
     x-data="kioskSholatData()"
     x-init="initKiosk()"
     wire:ignore>
    
    <!-- Include Html5Qrcode Library -->
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

    <style>
        #reader-sholat video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 1rem;
        }
        #reader-sholat__scan_region {
            background: transparent !important;
        }
        #reader-sholat__dashboard {
            display: none !important;
        }
    </style>

    <!-- Audio Elements -->
    <audio id="audio-success" src="/audio/beep.mp3" preload="auto"></audio>
    <audio id="audio-error" src="/audio/buzz.mp3" preload="auto"></audio>
    <audio id="audio-holiday" src="/audio/chime.mp3" preload="auto"></audio>

    <!-- Overlay "Sentuh Layar" -->
    @if($isHariSholat)
    <div x-show="!isActive" 
         class="absolute inset-0 z-50 bg-slate-900/85 backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer transition-opacity duration-300"
         @click="activateKiosk()">
        <div class="w-24 h-24 rounded-3xl bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center text-emerald-400 mb-6 animate-pulse">
            <svg class="w-14 h-14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-wider text-center px-4">
            Sentuh Layar Untuk Mengaktifkan Kiosk Sholat Dhuhur
        </h1>
        <p class="text-emerald-200 mt-3 text-lg font-medium">
            Sistem Presensi Sholat Dhuhur Berjamaah &bull; {{ $settings->school_name ?? 'Sekolah' }}
        </p>
        <span class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-600 text-white font-bold text-sm shadow-lg shadow-emerald-900/50">
            <span>Mulai Scan Barcode / NIS</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </span>
    </div>
    @endif

    <!-- Hidden Input Container untuk USB Barcode Scanner -->
    @if($isHariSholat)
    <input type="text" 
           x-ref="barcodeInput" 
           @input="barcode = $event.target.value"
           @keydown.enter="submitScan()"
           @keydown.escape="barcode = ''; $event.target.value = ''"
           @blur="refocusInput()"
           class="fixed top-0 left-0 opacity-0 w-px h-px"
           autofocus
           autocomplete="off"
           autocorrect="off"
           autocapitalize="off"
           spellcheck="false">
    @endif

    <!-- Main Container -->
    <div class="relative w-full max-w-6xl mx-4 my-6">
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/40 overflow-hidden flex flex-col md:flex-row min-h-[560px]">
            
            <!-- Left Side: Scanner UI -->
            <div class="w-full md:w-7/12 flex flex-col relative border-r border-slate-100">
                
                <!-- Header Banner -->
                <div class="bg-gradient-to-r from-emerald-700 via-teal-800 to-emerald-900 text-white py-6 px-8 text-center relative flex flex-col items-center justify-center shadow-md">
                    @if($settings && $settings->school_logo_path)
                        <img src="{{ asset('storage/'.$settings->school_logo_path) }}" alt="Logo" class="w-16 h-16 mx-auto mb-2 object-contain drop-shadow">
                    @endif
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-emerald-100 text-xs font-bold mb-1 backdrop-blur-xs">
                        <span>🕌 Pembiasaan Ibadah Siswa</span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-white">
                        Presensi Sholat Dhuhur
                    </h1>
                    <p class="text-emerald-100/90 text-xs font-medium">
                        {{ $settings->school_name ?? 'SMP Negeri 3 Kedungreja' }}
                    </p>

                    <!-- Status Bar / Jam -->
                    <div class="mt-3 flex items-center justify-center gap-4 text-xs font-bold text-emerald-200 bg-black/20 px-4 py-1.5 rounded-full backdrop-blur-sm">
                        <span x-text="currentTime">--:--:-- WIB</span>
                        <span>&bull;</span>
                        <span>{{ $now->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>

                <!-- Body Content -->
                <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between">
                    
                    <!-- Peringatan Jika Hari Libur / Jumat -->
                    @if(!$isHariSholat)
                        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-6 text-center my-auto">
                            <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-amber-900">
                                {{ $isFriday ? 'Hari Ini Hari Sholat Jumat' : 'Hari Ini Libur Sholat Dhuhur' }}
                            </h3>
                            <p class="text-sm text-amber-800 mt-1 max-w-md mx-auto">
                                {{ $holidayDesc ?: ($isFriday ? 'Jadwal sholat dhuhur di sekolah ditiadakan karena pelaksanaan Sholat Jumat di lingkungan masing-masing.' : 'Presensi sholat dhuhur di sekolah tidak aktif pada hari libur.') }}
                            </p>
                        </div>
                    @else
                        <!-- State: Waiting for scan / Result Card -->
                        <div>
                            <!-- Hasil Scan: SUCCESS -->
                            <template x-if="lastResult && lastResult.status === 'success'">
                                <div class="bg-emerald-50 border-2 border-emerald-400 rounded-3xl p-6 animate-fade-in shadow-lg">
                                    <div class="flex items-center gap-4">
                                        <template x-if="lastResult.student.avatar_url">
                                            <img :src="lastResult.student.avatar_url" class="w-20 h-20 rounded-2xl object-cover border-2 border-emerald-300 shadow-md">
                                        </template>
                                        <template x-if="!lastResult.student.avatar_url">
                                            <div class="w-20 h-20 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-2xl shadow-md"
                                                 x-text="lastResult.student.name.charAt(0)">
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600 text-white mb-1">
                                                ✅ Hadir Sholat Dhuhur
                                            </span>
                                            <h3 class="text-xl font-black text-slate-800 truncate" x-text="lastResult.student.name"></h3>
                                            <p class="text-xs font-bold text-slate-500">
                                                Kelas <span x-text="lastResult.student.class"></span> &bull; NIS: <span x-text="lastResult.student.nis || '-'"></span>
                                            </p>
                                            <p class="text-[11px] text-emerald-700 font-semibold mt-1">
                                                Tercatat pada <span x-text="lastResult.time"></span> WIB
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Hasil Scan: ALREADY SCANNED -->
                            <template x-if="lastResult && lastResult.status === 'already_scanned'">
                                <div class="bg-amber-50 border-2 border-amber-300 rounded-3xl p-6 animate-fade-in shadow-md">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-2xl shrink-0">
                                            ⚠️
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-600 text-white mb-1">
                                                Sudah Scan Sebelumnya
                                            </span>
                                            <h3 class="text-lg font-black text-slate-800 truncate" x-text="lastResult.student.name"></h3>
                                            <p class="text-xs text-amber-800 font-semibold">
                                                Siswa telah tercatat hadir sholat dhuhur hari ini pada pukul <span x-text="lastResult.time"></span> WIB.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Hasil Scan: NOT FOUND -->
                            <template x-if="lastResult && lastResult.status === 'not_found'">
                                <div class="bg-rose-50 border-2 border-rose-300 rounded-3xl p-6 animate-fade-in shadow-md">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-black text-2xl shrink-0">
                                            ❌
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-600 text-white mb-1">
                                                Kartu Tidak Dikenali
                                            </span>
                                            <p class="text-sm font-bold text-rose-900 mt-1" x-text="lastResult.message || 'Nomor kartu barcode / NIS tidak ditemukan di database.'"></p>
                                            <p class="text-xs text-rose-700 mt-0.5">Pastikan kartu siswa telah aktif di semester ini.</p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Default State: Standby / Petunjuk Scan -->
                            <template x-if="!lastResult">
                                <div class="text-center py-8">
                                    <div class="w-20 h-20 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4 border border-emerald-200/60 shadow-inner">
                                        <svg class="w-10 h-10 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-black text-slate-800">
                                        Siap Memindai Barcode / NIS
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                        Arahkan kartu siswa ke scanner barcode USB atau gunakan kamera perangkat di bawah ini.
                                    </p>
                                </div>
                            </template>

                            <!-- Video Container untuk Kamera (HTML5-QRCode) -->
                            <div x-show="isCameraActive" class="mt-4 rounded-2xl overflow-hidden border-2 border-emerald-400 bg-black aspect-video relative max-w-sm mx-auto shadow-md">
                                <div id="reader-sholat" class="w-full h-full"></div>
                                <button type="button" 
                                        @click="stopCamera()" 
                                        class="absolute top-2 right-2 z-20 bg-rose-600 hover:bg-rose-700 text-white rounded-full p-1.5 shadow-md">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Action Bar Bawah (Kamera & Manual) -->
                        <div class="pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        x-show="!isCameraActive"
                                        @click="startCamera()" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition-all shadow-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>Gunakan Kamera HP/Webcam</span>
                                </button>

                                <button type="button" 
                                        x-show="isCameraActive"
                                        @click="stopCamera()" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold transition-all shadow-xs">
                                    <span>Matikan Kamera</span>
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('portal-guru.sholat-dhuhur') }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                    <span>Kembali ke Rekap Guru</span>
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <!-- Right Side: Live Scan Feed & Stats -->
            <div class="w-full md:w-5/12 bg-slate-50/70 p-6 flex flex-col justify-between">
                
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-800">Riwayat Scan Sholat</h3>
                            <p class="text-xs text-slate-400">Siswa yang baru saja presensi</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-400 block uppercase">Total Hari Ini</span>
                            <span class="text-2xl font-black text-emerald-600" x-text="todayCount">{{ $todayScannedCount }}</span>
                        </div>
                    </div>

                    <!-- Scan History List -->
                    <div class="space-y-2.5 max-h-[420px] overflow-y-auto custom-scrollbar pr-1">
                        <template x-for="(item, idx) in historyList" :key="item.time + '-' + idx">
                            <div class="bg-white p-3 rounded-2xl border border-slate-200/70 shadow-xs flex items-center justify-between gap-3 animate-fade-in">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0"
                                         x-text="item.name.charAt(0)">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate" x-text="item.name"></p>
                                        <p class="text-[10px] text-slate-400 font-semibold">
                                            Kelas <span x-text="item.class"></span> &bull; NIS: <span x-text="item.nis || '-'"></span>
                                        </p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg shrink-0" x-text="item.time">
                                </span>
                            </div>
                        </template>

                        <div x-show="historyList.length === 0" class="text-center py-12 text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-xs font-bold">Belum ada scan sholat dhuhur sesi ini</p>
                        </div>
                    </div>
                </div>

                <!-- Footer info -->
                <div class="pt-4 border-t border-slate-200/60 text-center text-[11px] text-slate-400">
                    Kiosk Presensi Sholat Dhuhur &bull; Terhubung ke Server Sekolah
                </div>
            </div>

        </div>
    </div>

    <!-- Alpine.js Component Script -->
    <script>
        function kioskSholatData() {
            return {
                isActive: false,
                barcode: '',
                isLoading: false,
                currentTime: '',
                lastResult: null,
                resetTimer: null,
                isCameraActive: false,
                html5QrcodeScanner: null,
                todayCount: {{ $todayScannedCount }},
                historyList: [
                    @foreach($recentScans as $sc)
                        {
                            name: "{{ addslashes($sc->siswa->name ?? 'Siswa') }}",
                            class: "{{ addslashes($sc->kelas->name ?? '-') }}",
                            nis: "{{ $sc->siswa->nis ?? '-' }}",
                            time: "{{ Carbon\Carbon::parse($sc->updated_at)->format('H:i:s') }}"
                        },
                    @endforeach
                ],

                initKiosk() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                },

                updateClock() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                activateKiosk() {
                    this.isActive = true;
                    this.refocusInput();
                    
                    // Unlock audio
                    try {
                        const audio = document.getElementById('audio-success');
                        if (audio) {
                            audio.volume = 0;
                            audio.play().then(() => {
                                audio.pause();
                                audio.currentTime = 0;
                                audio.volume = 1;
                            }).catch(() => {});
                        }
                    } catch (e) {}
                },

                refocusInput() {
                    if (this.isActive && this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                    }
                },

                async submitScan(overrideBarcode = null) {
                    const currentBarcode = (overrideBarcode || this.barcode).trim();
                    this.barcode = '';
                    if (this.$refs.barcodeInput) this.$refs.barcodeInput.value = '';

                    if (!currentBarcode) return;

                    this.isLoading = true;
                    if (this.resetTimer) clearTimeout(this.resetTimer);

                    try {
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                        const response = await fetch('{{ route('kiosk.process-sholat-dhuhur') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ barcode: currentBarcode })
                        });

                        if (response.status === 401 || response.status === 419) {
                            window.location.reload();
                            return;
                        }

                        const data = await response.json();
                        this.handleScanResult(data);

                    } catch (err) {
                        console.error('Scan error:', err);
                        this.playAudio('error');
                        this.lastResult = {
                            status: 'not_found',
                            message: 'Gagal terhubung ke server atau jaringan terputus.'
                        };
                    } finally {
                        this.isLoading = false;
                        this.refocusInput();
                    }
                },

                handleScanResult(res) {
                    this.lastResult = res;

                    if (res.status === 'success') {
                        this.playAudio('success');
                        this.todayCount++;
                        this.historyList.unshift({
                            name: res.student.name,
                            class: res.student.class,
                            nis: res.student.nis,
                            time: res.time
                        });
                        if (this.historyList.length > 15) this.historyList.pop();

                    } else if (res.status === 'already_scanned') {
                        this.playAudio('error');
                    } else if (res.status === 'holiday') {
                        this.playAudio('holiday');
                    } else {
                        this.playAudio('error');
                    }

                    // Reset result view after 6 seconds
                    this.resetTimer = setTimeout(() => {
                        this.lastResult = null;
                    }, 6000);
                },

                playAudio(type) {
                    try {
                        const el = document.getElementById('audio-' + type);
                        if (el) {
                            el.currentTime = 0;
                            el.play().catch(() => {});
                        }
                    } catch (e) {}
                },

                async startCamera() {
                    if (!this.isActive) this.activateKiosk();
                    this.isCameraActive = true;

                    this.$nextTick(async () => {
                        try {
                            if (this.html5QrcodeScanner) {
                                await this.html5QrcodeScanner.stop().catch(() => {});
                            }
                            this.html5QrcodeScanner = new Html5Qrcode("reader-sholat");
                            await this.html5QrcodeScanner.start(
                                { facingMode: "environment" },
                                { fps: 15, qrbox: { width: 260, height: 150 }, aspectRatio: 1.333333 },
                                (decodedText) => {
                                    if (decodedText && !this.isLoading) {
                                        this.submitScan(decodedText);
                                    }
                                },
                                () => {}
                            );
                        } catch (err) {
                            console.error('Kamera error:', err);
                            this.isCameraActive = false;
                            alert('Gagal mengakses kamera browser. Pastikan izin kamera aktif.');
                        }
                    });
                },

                async stopCamera() {
                    if (this.html5QrcodeScanner) {
                        await this.html5QrcodeScanner.stop().catch(() => {});
                        this.html5QrcodeScanner = null;
                    }
                    this.isCameraActive = false;
                    this.refocusInput();
                }
            };
        }
    </script>
</div>
