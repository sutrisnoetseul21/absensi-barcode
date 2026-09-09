@php
    $spikapSetting = \App\Models\SpikapNotifSetting::instance();
@endphp

<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('portal-siswa.spikap') }}"
               class="group flex items-center justify-center w-11 h-11 rounded-2xl bg-white border border-slate-200/80 text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50/50 shadow-sm transition-all duration-200"
               title="Kembali ke Daftar Laporan">
                <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buat Laporan {{ $spikapSetting->getNamaAplikasi() }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                        Formulir Digital
                    </span>
                </div>
                <p class="text-slate-500 text-sm mt-0.5">{{ $spikapSetting->getPenjelasanAplikasi() }}</p>
            </div>
        </div>

        @if($student)
            <div class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-white border border-slate-200/80 shadow-sm self-start sm:self-auto">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 font-bold text-xs flex items-center justify-center border border-rose-100 uppercase">
                    {{ substr($student->name ?? 'S', 0, 2) }}
                </div>
                <div class="text-left text-xs">
                    <div class="font-bold text-slate-800 line-clamp-1">{{ $student->name }}</div>
                    <div class="text-slate-400 font-medium">
                        NISN: {{ $student->nisn }}
                        @if($student->enrollmentAktif?->kelas)
                            • Kelas {{ $student->enrollmentAktif->kelas->name }}
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ── Sukses ──────────────────────────────────────────────────────── --}}
    @if($submitted)
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 text-center space-y-3">
            <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-7 h-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-emerald-800">Laporan Berhasil Dikirim</h2>
            <p class="text-sm text-emerald-700">
                Laporan kamu sudah masuk ke sistem dan sedang menunggu ditindaklanjuti. Kamu bisa memantau statusnya di halaman Riwayat Laporan.
            </p>
            <div class="flex justify-center gap-3 pt-2">
                <a href="{{ route('portal-siswa.spikap') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    Lihat Riwayat Laporan
                </a>
                <button wire:click="$set('submitted', false)"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                    Buat Laporan Baru
                </button>
            </div>
        </div>
    @else

    {{-- ── Form ────────────────────────────────────────────────────────── --}}
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- ── Kolom Kiri (8 cols): isi laporan ──────────────────── --}}
            <div class="lg:col-span-8 space-y-5">

                {{-- Sifat Laporan (Biasa / Darurat) --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">
                            Sifat Laporan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/60">
                            {{-- Biasa --}}
                            <button type="button"
                                    wire:click="$set('sifat_laporan', 'biasa')"
                                    class="flex items-center justify-center gap-2.5 py-2.5 px-4 rounded-xl text-sm font-bold transition-all duration-200 cursor-pointer {{ $sifat_laporan === 'biasa' ? 'bg-white text-blue-600 shadow-sm border border-slate-200/60 ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}">
                                <svg class="w-4 h-4 {{ $sifat_laporan === 'biasa' ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Biasa</span>
                                @if($sifat_laporan === 'biasa')
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                @endif
                            </button>
                            {{-- Darurat --}}
                            <button type="button"
                                    wire:click="$set('sifat_laporan', 'darurat')"
                                    class="flex items-center justify-center gap-2.5 py-2.5 px-4 rounded-xl text-sm font-bold transition-all duration-200 cursor-pointer {{ $sifat_laporan === 'darurat' ? 'bg-white text-rose-600 shadow-sm border border-slate-200/60 ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}">
                                <svg class="w-4 h-4 {{ $sifat_laporan === 'darurat' ? 'text-rose-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>🚨 Darurat</span>
                                @if($sifat_laporan === 'darurat')
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                @endif
                            </button>
                        </div>
                        @error('sifat_laporan') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror

                        {{-- Penjelasan kontekstual per sifat --}}
                        @if($sifat_laporan === 'darurat')
                            <div class="mt-3 flex items-start gap-2.5 p-3.5 bg-rose-50 border border-rose-200/70 rounded-xl text-xs text-rose-700">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Laporan <strong>Darurat</strong> otomatis diteruskan ke <strong>Wali Kelas</strong> dan <strong>Kepala Sekolah</strong> sekaligus. Notifikasi WhatsApp akan langsung dikirim.</span>
                            </div>
                        @else
                            <div class="mt-3 flex items-start gap-2.5 p-3.5 bg-blue-50 border border-blue-200/70 rounded-xl text-xs text-blue-700">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Laporan <strong>Biasa</strong> akan diteruskan ke penerima yang kamu pilih di bawah.</span>
                            </div>
                        @endif
                    </div>

                    {{-- Tujuan Penerima (hanya muncul untuk sifat=biasa) --}}
                    @if($sifat_laporan === 'biasa')
                        <div wire:key="tujuan-penerima">
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">
                                Tujuan Laporan <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button"
                                        wire:click="$set('tujuan_penerima', 'guru_bk')"
                                        class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer text-center
                                               {{ $tujuan_penerima === 'guru_bk' ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50/50' }}">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                                    </svg>
                                    <span class="text-sm font-bold">Guru BK</span>
                                    <span class="text-xs opacity-70">Bimbingan & Konseling</span>
                                </button>
                                <button type="button"
                                        wire:click="$set('tujuan_penerima', 'wali_kelas')"
                                        class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer text-center
                                               {{ $tujuan_penerima === 'wali_kelas' ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50/50' }}">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                    <span class="text-sm font-bold">Wali Kelas</span>
                                    <span class="text-xs opacity-70">Wali kelas kamu</span>
                                </button>
                            </div>
                            @error('tujuan_penerima') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                {{-- Jenis Perundungan --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-3">
                        Jenis Perundungan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach([
                            'fisik'   => ['label' => 'Fisik',                'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                            'verbal'  => ['label' => 'Verbal',               'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                            'sosial'  => ['label' => 'Sosial',               'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                            'digital' => ['label' => 'Digital/Cyberbullying', 'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                            'lainnya' => ['label' => 'Lainnya',              'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ] as $key => $item)
                            <button type="button"
                                    wire:click="$set('jenis_perundungan', '{{ $key }}')"
                                    class="flex items-center gap-2.5 px-3.5 py-3 rounded-xl border-2 text-sm font-semibold transition-all duration-150 cursor-pointer text-left
                                           {{ $jenis_perundungan === $key ? 'border-rose-400 bg-rose-50 text-rose-700' : 'border-slate-200 bg-white text-slate-600 hover:border-rose-200 hover:bg-rose-50/40' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                </svg>
                                {{ $item['label'] }}
                            </button>
                        @endforeach
                    </div>
                    @error('jenis_perundungan') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                {{-- Uraian Kejadian --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
                    <label for="uraian_kejadian" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">
                        Uraian Kejadian <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="uraian_kejadian"
                              wire:model="uraian_kejadian"
                              rows="6"
                              placeholder="Ceritakan kejadian yang dialami atau disaksikan secara detail. Informasi yang lengkap akan membantu proses penanganan..."
                              class="w-full px-4 py-3 text-sm text-slate-800 bg-slate-50/70 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-400/30 focus:border-rose-400 transition resize-none placeholder:text-slate-400"
                    ></textarea>
                    <div class="flex justify-between items-center mt-1.5">
                        @error('uraian_kejadian')
                            <p class="text-xs text-rose-600">{{ $message }}</p>
                        @else
                            <p class="text-xs text-slate-400">Minimal 20 karakter.</p>
                        @enderror
                        <p class="text-xs text-slate-400 ml-auto">{{ strlen($uraian_kejadian) }} karakter</p>
                    </div>
                </div>
            </div>

            {{-- ── Kolom Kanan (4 cols): lokasi, waktu, upload ─────────── --}}
            <div class="lg:col-span-4 space-y-5">

                {{-- Lokasi & Waktu (opsional) --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Detail Kejadian <span class="font-normal text-slate-400">(opsional)</span></p>

                    <div>
                        <label for="lokasi_kejadian" class="block text-xs font-medium text-slate-600 mb-1.5">Lokasi</label>
                        <input type="text" id="lokasi_kejadian"
                               wire:model="lokasi_kejadian"
                               placeholder="cth: Kantin, kelas 8A, lapangan..."
                               class="w-full px-3.5 py-2.5 text-sm text-slate-800 bg-slate-50/70 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-400/30 focus:border-rose-400 transition placeholder:text-slate-400">
                        @error('lokasi_kejadian') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="waktu_kejadian" class="block text-xs font-medium text-slate-600 mb-1.5">Waktu Kejadian</label>
                        <input type="datetime-local" id="waktu_kejadian"
                               wire:model="waktu_kejadian"
                               class="w-full px-3.5 py-2.5 text-sm text-slate-800 bg-slate-50/70 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-400/30 focus:border-rose-400 transition">
                        @error('waktu_kejadian') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Upload Foto --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Foto Bukti</p>
                        <span class="text-xs text-slate-400">Maks 5 foto · 5 MB/file</span>
                    </div>
                    <label for="foto_bukti"
                           class="flex flex-col items-center justify-center gap-2 w-full h-24 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition group">
                        <svg class="w-7 h-7 text-slate-400 group-hover:text-blue-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="text-xs text-slate-500 group-hover:text-blue-600 font-medium">Klik untuk pilih foto</span>
                        <input id="foto_bukti" type="file" wire:model="foto_bukti" multiple accept=".jpg,.jpeg,.png,.webp" class="hidden">
                    </label>
                    @error('foto_bukti') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    @error('foto_bukti.*') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    @if(count($foto_bukti))
                        <ul class="space-y-1.5">
                            @foreach($foto_bukti as $i => $foto)
                                <li class="flex items-center justify-between gap-2 text-xs text-slate-600 bg-slate-50 rounded-lg px-3 py-2 border border-slate-200/60">
                                    <span class="truncate font-medium">{{ $foto->getClientOriginalName() }}</span>
                                    <span class="shrink-0 text-slate-400">{{ round($foto->getSize() / 1024) }} KB</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Upload Video --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Video Bukti</p>
                        <span class="text-xs text-slate-400">Maks 2 video · 50 MB/file</span>
                    </div>
                    <label for="video_bukti"
                           class="flex flex-col items-center justify-center gap-2 w-full h-24 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 cursor-pointer hover:border-purple-400 hover:bg-purple-50/50 transition group">
                        <svg class="w-7 h-7 text-slate-400 group-hover:text-purple-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <span class="text-xs text-slate-500 group-hover:text-purple-600 font-medium">Klik untuk pilih video</span>
                        <input id="video_bukti" type="file" wire:model="video_bukti" multiple accept=".mp4,.mov,.avi" class="hidden">
                    </label>
                    @error('video_bukti') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    @error('video_bukti.*') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    @if(count($video_bukti))
                        <ul class="space-y-1.5">
                            @foreach($video_bukti as $i => $video)
                                <li class="flex items-center justify-between gap-2 text-xs text-slate-600 bg-slate-50 rounded-lg px-3 py-2 border border-slate-200/60">
                                    <span class="truncate font-medium">{{ $video->getClientOriginalName() }}</span>
                                    <span class="shrink-0 text-slate-400">{{ round($video->getSize() / 1024 / 1024, 1) }} MB</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Tombol Submit --}}
                <div class="pt-1">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-70 cursor-not-allowed"
                            class="w-full inline-flex items-center justify-center gap-2.5 px-6 py-3.5 bg-rose-600 text-white font-bold text-sm rounded-xl shadow-md shadow-rose-500/30 hover:bg-rose-700 active:scale-95 transition-all duration-150">
                        <span wire:loading.remove>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </span>
                        <span wire:loading>
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove>Kirim Laporan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                    <p class="text-center text-xs text-slate-400 mt-2">Identitas kamu tersimpan di sistem dan hanya dapat diakses oleh penerima resmi laporan ini.</p>
                </div>

            </div>
        </div>
    </form>
    @endif
</div>
