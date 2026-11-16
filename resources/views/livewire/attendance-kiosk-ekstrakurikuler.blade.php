<div class="min-h-screen bg-slate-100 flex items-center justify-center relative overflow-hidden font-jakarta"
     x-data="kioskEkskulData()"
     x-init="initKiosk()"
     wire:ignore>

    <!-- Include Html5Qrcode Library -->
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

    <style>
        #reader-ekskul video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 1rem;
        }
        #reader-ekskul__scan_region {
            background: transparent !important;
        }
        #reader-ekskul__dashboard {
            display: none !important;
        }
    </style>

    <!-- Audio Elements -->
    <audio id="audio-success" src="/audio/beep.mp3" preload="auto"></audio>
    <audio id="audio-error" src="/audio/buzz.mp3" preload="auto"></audio>
    <audio id="audio-holiday" src="/audio/chime.mp3" preload="auto"></audio>

    <!-- Overlay "Sentuh Layar" -->
    <div x-show="!isActive"
         class="absolute inset-0 z-50 bg-slate-900/85 backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer transition-opacity duration-300"
         @click="activateKiosk()">
        <div class="w-24 h-24 rounded-3xl bg-indigo-500/20 border border-indigo-400/40 flex items-center justify-center text-indigo-300 mb-6 animate-pulse">
            <svg class="w-14 h-14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-wider text-center px-4">
            Sentuh Layar Untuk Mengaktifkan Kiosk {{ $ekskul->nama_ekskul }}
        </h1>
        <p class="text-indigo-200 mt-3 text-lg font-medium text-center px-4">
            Presensi Ekstrakurikuler &bull; Pertemuan ke-{{ $activePertemuanKe }} &bull; {{ $settings->school_name ?? 'Sekolah' }}
        </p>
        <span class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-900/50">
            <span>Mulai Scan Barcode / NIS</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </span>
    </div>

    <!-- Hidden Input Container untuk USB Barcode Scanner -->
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

    <!-- Main Container -->
    <div class="relative w-full max-w-6xl mx-4 my-6">
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/40 overflow-hidden flex flex-col md:flex-row min-h-[580px]">

            <!-- Left Side: Scanner UI -->
            <div class="w-full md:w-7/12 flex flex-col relative border-r border-slate-100">

                <!-- Header Banner -->
                <div class="bg-gradient-to-r from-indigo-700 via-brand-primary to-violet-800 text-white py-6 px-8 text-center relative flex flex-col items-center justify-center shadow-md">
                    @if($settings && $settings->school_logo_path)
                        <img src="{{ asset('storage/'.$settings->school_logo_path) }}" alt="Logo" class="w-14 h-14 mx-auto mb-2 object-contain drop-shadow">
                    @endif
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-indigo-100 text-xs font-bold mb-1 backdrop-blur-xs">
                        <span>Ekstrakurikuler &bull; Pertemuan ke-<span x-text="pertemuanKe">{{ $activePertemuanKe }}</span></span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-white">
                        {{ $ekskul->nama_ekskul }}
                    </h1>
                    <p class="text-indigo-100/90 text-xs font-medium mt-0.5">
                        Pembina: {{ $ekskul->pembinas->pluck('name')->implode(', ') ?: '-' }} &bull; TA {{ $ekskul->tahunAjaran?->name ?? '-' }}
                    </p>

                    <!-- Status Bar / Jam -->
                    <div class="mt-3 flex items-center justify-center gap-4 text-xs font-bold text-indigo-100 bg-black/20 px-4 py-1.5 rounded-full backdrop-blur-sm">
                        <span x-text="currentTime">--:--:-- WIB</span>
                        <span>&bull;</span>
                        <span>{{ $now->locale('id')->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>

                <!-- Materi Pertemuan Ringkas -->
                <div class="px-6 pt-5 pb-3 border-b border-slate-100 bg-slate-50/70">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input type="text"
                               x-model="judulKegiatan"
                               data-allow-focus="1"
                               @keydown.enter.prevent="saveTopic()"
                               placeholder="Judul / Materi Kegiatan Hari Ini (opsional)..."
                               class="flex-1 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <button type="button"
                                @click="saveTopic()"
                                :disabled="savingTopic"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer shrink-0">
                            <span x-show="!savingTopic">Simpan Materi</span>
                            <span x-show="savingTopic">Menyimpan...</span>
                        </button>
                    </div>
                    <p x-show="topicSavedMsg" x-text="topicSavedMsg" class="text-[11px] font-bold text-emerald-600 mt-1.5"></p>
                </div>

                <!-- Body Content -->
                <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between">
                    <div>
                        <!-- SUCCESS -->
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
                                            Hadir Pertemuan ke-<span x-text="lastResult.pertemuan_ke"></span>
                                        </span>
                                        <h3 class="text-xl font-black text-slate-800 truncate" x-text="lastResult.student.name"></h3>
                                        <p class="text-xs font-bold text-slate-500">
                                            Kelas <span x-text="lastResult.student.class"></span> &bull; NISN: <span x-text="lastResult.student.nisn || '-'"></span>
                                        </p>
                                        <p class="text-[11px] text-emerald-700 font-semibold mt-1">
                                            Tercatat pada <span x-text="lastResult.time"></span> WIB
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ALREADY SCANNED -->
                        <template x-if="lastResult && lastResult.status === 'already_scanned'">
                            <div class="bg-amber-50 border-2 border-amber-300 rounded-3xl p-6 animate-fade-in shadow-md">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-2xl shrink-0">
                                        !
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-600 text-white mb-1">
                                            Sudah Scan Sebelumnya
                                        </span>
                                        <h3 class="text-lg font-black text-slate-800 truncate" x-text="lastResult.student.name"></h3>
                                        <p class="text-xs text-amber-800 font-semibold">
                                            Tercatat hadir pertemuan ke-<span x-text="lastResult.pertemuan_ke"></span> pada pukul <span x-text="lastResult.time"></span> WIB.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- NOT MEMBER -->
                        <template x-if="lastResult && lastResult.status === 'not_member'">
                            <div class="bg-orange-50 border-2 border-orange-300 rounded-3xl p-6 animate-fade-in shadow-md">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-black text-2xl shrink-0">
                                        ?
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-600 text-white mb-1">
                                            Bukan Anggota Ekstrakurikuler
                                        </span>
                                        <h3 class="text-lg font-black text-slate-800 truncate" x-text="lastResult.student.name"></h3>
                                        <p class="text-xs text-orange-900 font-semibold mt-0.5" x-text="lastResult.message"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- NOT FOUND -->
                        <template x-if="lastResult && (lastResult.status === 'not_found' || lastResult.status === 'inactive')">
                            <div class="bg-rose-50 border-2 border-rose-300 rounded-3xl p-6 animate-fade-in shadow-md">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-black text-2xl shrink-0">
                                        X
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-600 text-white mb-1">
                                            Gagal Memproses Scan
                                        </span>
                                        <p class="text-sm font-bold text-rose-900 mt-1" x-text="lastResult.message || 'Kartu tidak dikenali.'"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Default Standby -->
                        <template x-if="!lastResult">
                            <div class="text-center py-8">
                                <div class="w-20 h-20 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4 border border-indigo-200/60 shadow-inner">
                                    <svg class="w-10 h-10 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <h2 class="text-xl font-black text-slate-800">
                                    Siap Memindai Kartu Anggota
                                </h2>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                    Hanya anggota aktif <strong>{{ $ekskul->nama_ekskul }}</strong> yang dapat melakukan presensi pada scanner ini.
                                </p>
                            </div>
                        </template>

                        <!-- Video Container untuk Kamera -->
                        <div x-show="isCameraActive" class="mt-4 rounded-2xl overflow-hidden border-2 border-indigo-400 bg-black aspect-video relative max-w-sm mx-auto shadow-md">
                            <div id="reader-ekskul" class="w-full h-full"></div>
                            <button type="button"
                                    @click="stopCamera()"
                                    class="absolute top-2 right-2 z-20 bg-rose-600 hover:bg-rose-700 text-white rounded-full p-1.5 shadow-md">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Action Bar Bawah -->
                    <div class="pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    x-show="!isCameraActive"
                                    @click="startCamera()"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 text-xs font-bold transition-all shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span>Gunakan Kamera HP/Webcam</span>
                            </button>

                            <button type="button"
                                    x-show="isCameraActive"
                                    @click="stopCamera()"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold transition-all shadow-xs cursor-pointer">
                                <span>Matikan Kamera</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            @php
                                $backUrl = auth()->user()?->teacher && !auth()->user()?->isSuperAdmin()
                                    ? route('portal-guru.ekstrakurikuler')
                                    : url('/portal-akademik/ekstrakurikuler/data');
                            @endphp
                            <a href="{{ $backUrl }}"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                <span>Kembali</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Live Scan Feed & Stats -->
            <div class="w-full md:w-5/12 bg-slate-50/70 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-800">Riwayat Scan Pertemuan ke-<span x-text="pertemuanKe">{{ $activePertemuanKe }}</span></h3>
                            <p class="text-xs text-slate-400">Anggota yang sudah hadir hari ini</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-400 block uppercase">Hadir / Total</span>
                            <span class="text-2xl font-black text-indigo-600"><span x-text="todayCount">{{ $todayScannedCount }}</span><span class="text-sm text-slate-400 font-bold"> / {{ $totalMembers }}</span></span>
                        </div>
                    </div>

                    <div class="space-y-2.5 max-h-[420px] overflow-y-auto custom-scrollbar pr-1">
                        <template x-for="(item, idx) in historyList" :key="item.time + '-' + idx">
                            <div class="bg-white p-3 rounded-2xl border border-slate-200/70 shadow-xs flex items-center justify-between gap-3 animate-fade-in">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0"
                                         x-text="item.name.charAt(0)">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate" x-text="item.name"></p>
                                        <p class="text-[10px] text-slate-400 font-semibold">
                                            Kelas <span x-text="item.class"></span> &bull; NISN: <span x-text="item.nisn || '-'"></span>
                                        </p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg shrink-0" x-text="item.time"></span>
                            </div>
                        </template>

                        <div x-show="historyList.length === 0" class="text-center py-12 text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-xs font-bold">Belum ada scan pada pertemuan hari ini</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200/60 text-center text-[11px] text-slate-400">
                    Kiosk Presensi {{ $ekskul->nama_ekskul }} &bull; Terhubung ke Server Sekolah
                </div>
            </div>

        </div>
    </div>

    <script>
        function kioskEkskulData() {
            return {
                isActive: false,
                barcode: '',
                isLoading: false,
                currentTime: '',
                lastResult: null,
                resetTimer: null,
                isCameraActive: false,
                html5QrcodeScanner: null,
                pertemuanKe: {{ (int) ($activePertemuanKe ?? 1) }},
                judulKegiatan: @json($judulKegiatan),
                savingTopic: false,
                topicSavedMsg: '',
                todayCount: {{ (int) $todayScannedCount }},
                historyList: [
                    @foreach($recentScans as $sc)
                        {
                            name: "{{ addslashes($sc->siswa->name ?? 'Siswa') }}",
                            class: "{{ addslashes($sc->siswa?->enrollments?->first()?->kelas?->name ?? '-') }}",
                            nisn: "{{ $sc->siswa->nisn ?? '-' }}",
                            time: "{{ $sc->scan_time ? substr((string) $sc->scan_time, 0, 8) : Carbon\Carbon::parse($sc->updated_at)->format('H:i:s') }}"
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
                    if (document.activeElement && document.activeElement.dataset && document.activeElement.dataset.allowFocus) {
                        return;
                    }
                    if (this.isActive && this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                    }
                },

                async saveTopic() {
                    this.savingTopic = true;
                    this.topicSavedMsg = '';
                    try {
                        await $wire.set('judulKegiatan', this.judulKegiatan);
                        await $wire.saveJudulKegiatan();
                        this.topicSavedMsg = 'Materi Pertemuan ke-' + this.pertemuanKe + ' tersimpan.';
                        setTimeout(() => { this.topicSavedMsg = ''; }, 4000);
                    } finally {
                        this.savingTopic = false;
                        if (this.$refs.barcodeInput) this.$refs.barcodeInput.focus();
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
                        const response = await fetch('{{ route('kiosk.process-ekstrakurikuler', ['slug' => $ekskul->slug]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                barcode: currentBarcode,
                                judul_kegiatan: this.judulKegiatan
                            })
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

                    if (res.pertemuan_ke) {
                        this.pertemuanKe = res.pertemuan_ke;
                    }

                    if (res.status === 'success') {
                        this.playAudio('success');
                        this.todayCount++;
                        this.historyList.unshift({
                            name: res.student.name,
                            class: res.student.class,
                            nisn: res.student.nisn,
                            time: res.time
                        });
                        if (this.historyList.length > 15) this.historyList.pop();
                    } else if (res.status === 'not_member') {
                        this.playAudio('holiday');
                    } else {
                        this.playAudio('error');
                    }

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
                            this.html5QrcodeScanner = new Html5Qrcode("reader-ekskul");
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

