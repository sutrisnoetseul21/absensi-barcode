<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span>SI-WALI • Ruang Aman Pendampingan Holistik</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">SI-WALI: Ruang Aman Konsultasi & Curhat Murid</h1>
            <p class="text-xs text-brand-primary font-bold mt-1">
                "Satu Klik Ruang Aman, Dampingi Murid Wujudkan Masa Depan"
            </p>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelompok: <strong>{{ $kelompok->nama_kelompok }}</strong> &bull; Respon ruang aman cerita dan pertanyaan dari peserta didik binaan.
            </p>
        </div>

        <a href="{{ route('portal-guru.guru-wali.jurnal') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span>Buka Jurnal Pendampingan</span>
        </a>
    </div>

    <!-- Flash Alert -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <!-- Tabs & Filter Bar -->
    <div class="space-y-4">
        <!-- Tab Navigation -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
            <button type="button" 
                    wire:click="setTab('menunggu')" 
                    class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'menunggu' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Menunggu Respon</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full font-black {{ $activeTab === 'menunggu' ? 'bg-white/20 text-white' : ($countMenunggu > 0 ? 'bg-rose-500 text-white animate-pulse' : 'bg-amber-100 text-amber-800') }}">
                    {{ $countMenunggu }}
                </span>
            </button>

            <button type="button" 
                    wire:click="setTab('dijadwalkan')" 
                    class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'dijadwalkan' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Dijadwalkan / Aktif</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full {{ $activeTab === 'dijadwalkan' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">
                    {{ $countDijadwalkan }}
                </span>
            </button>

            <button type="button" 
                    wire:click="setTab('selesai')" 
                    class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'selesai' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Selesai / Jurnal</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full {{ $activeTab === 'selesai' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $countSelesai }}
                </span>
            </button>

            <button type="button" 
                    wire:click="setTab('ditolak')" 
                    class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'ditolak' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Ditolak</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full {{ $activeTab === 'ditolak' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                    {{ $countDitolak }}
                </span>
            </button>

            <button type="button" 
                    wire:click="setTab('semua')" 
                    class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'semua' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Semua Sesi</span>
            </button>
        </div>

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Cari nama murid, NISN, atau topik permasalahan..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all">
            </div>

            <div class="w-full sm:w-auto">
                <select wire:model.live="kategori" 
                        class="w-full sm:w-60 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all cursor-pointer">
                    <option value="">Semua Kategori (4 Pilar)</option>
                    <option value="Akademik">📚 Akademik & Belajar</option>
                    <option value="Karakter & Kedisiplinan">🌟 Karakter & Disiplin</option>
                    <option value="Minat & Bakat / Ekskul">🎨 Minat & Bakat</option>
                    <option value="Sosial & Psikologis">🤝 Sosial & Psikologis</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Consultation Cards List -->
    <div class="space-y-4">
        @if($konsultasis->count() > 0)
            @foreach($konsultasis as $item)
                @php
                    $statusBadge = match($item->status_pengajuan?->value) {
                        'Menunggu Konfirmasi' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'dot' => 'bg-amber-500 animate-pulse', 'label' => 'Menunggu Respon Guru'],
                        'Dijadwalkan'         => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'dot' => 'bg-blue-500', 'label' => 'Dijadwalkan / Aktif'],
                        'Selesai'             => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-500', 'label' => 'Selesai'],
                        'Dikonversi ke Jurnal'=> ['bg' => 'bg-teal-50', 'border' => 'border-teal-200', 'text' => 'text-teal-800', 'dot' => 'bg-teal-500', 'label' => 'Dicatat di Jurnal'],
                        'Ditolak'             => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-800', 'dot' => 'bg-rose-500', 'label' => 'Ditolak'],
                        default               => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'text' => 'text-slate-800', 'dot' => 'bg-slate-500', 'label' => $item->status_pengajuan?->value ?? '-'],
                    };

                    $catConfig = match($item->kategori_pendampingan) {
                        'Akademik'                => ['class' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => '📚'],
                        'Karakter & Kedisiplinan' => ['class' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => '🌟'],
                        'Minat & Bakat / Ekskul'  => ['class' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => '🎨'],
                        'Sosial & Psikologis'     => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => '🤝'],
                        default                   => ['class' => 'bg-slate-50 text-slate-700 border-slate-200', 'icon' => '📌'],
                    };
                @endphp

                <div 
                    x-data="{ expanded: false }"
                    class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all overflow-hidden"
                    :class="{ 'ring-2 ring-brand-primary/30 border-brand-primary/60 shadow-md': expanded }"
                >
                    {{-- Collapsed Card Header (Touch & Click Friendly) --}}
                    <div 
                        @click="expanded = !expanded" 
                        class="p-4 sm:p-5 cursor-pointer select-none transition-colors hover:bg-slate-50/70"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-2.5 flex-1 min-w-0">
                                {{-- Badges Bar --}}
                                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                    {{-- Status Badge --}}
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[11px] sm:text-xs font-extrabold {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} border {{ $statusBadge['border'] }}">
                                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full {{ $statusBadge['dot'] }}"></span>
                                        {{ $statusBadge['label'] }}
                                    </span>

                                    {{-- Category Badge --}}
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-bold border {{ $catConfig['class'] }}">
                                        {{ $catConfig['icon'] }} {{ $item->kategori_pendampingan }}
                                    </span>

                                    {{-- Mode Badge --}}
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-semibold bg-slate-100 text-slate-700 inline-flex items-center gap-1">
                                        @if($item->mode_konsultasi === 'Tatap Muka')
                                            <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>🏫 Tatap Muka</span>
                                        @else
                                            <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            <span>💬 Ruang Aman (Chat)</span>
                                        @endif
                                    </span>

                                    <span class="text-[11px] sm:text-xs text-slate-400 ml-auto shrink-0 font-medium">
                                        {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                                    </span>
                                </div>

                                {{-- Student Info & Topic --}}
                                <div class="flex items-start gap-3 pt-0.5">
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-br from-brand-primary/10 to-brand-primary/20 text-brand-primary font-black flex items-center justify-center shrink-0 text-sm shadow-xs border border-brand-primary/20">
                                        {{ strtoupper(substr($item->siswa?->name ?? 'S', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            <h3 class="font-extrabold text-sm sm:text-base text-slate-900 truncate">{{ $item->siswa?->name }}</h3>
                                            <span class="text-xs text-slate-400 font-normal">
                                                (NISN: {{ $item->siswa?->nisn ?? '-' }} &bull; Kelas {{ $item->siswa?->enrollmentAktif?->kelas?->name ?? '-' }})
                                            </span>
                                        </div>

                                        <h4 class="text-sm font-bold text-slate-800 mt-1">
                                            {{ $item->topik_konsultasi }}
                                        </h4>

                                        {{-- Snippet preview when collapsed --}}
                                        <p x-show="!expanded" class="text-xs text-slate-500 mt-1 line-clamp-1 leading-relaxed">
                                            @if($item->mode_konsultasi === 'Pesan Portal' && $item->pesan->count() > 0)
                                                <span class="font-bold text-slate-700">Pesan terakhir:</span> "{{ Str::limit($item->pesan->last()->pesan, 75) }}"
                                            @elseif($item->jadwal_pasti)
                                                <span class="font-bold text-blue-700">🗓️ Jadwal Pertemuan:</span> {{ \Carbon\Carbon::parse($item->jadwal_pasti)->translatedFormat('l, d M Y • H:i') }} WIB
                                            @else
                                                {{ Str::limit($item->detail_permasalahan, 95) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                {{-- Quick Info Tag (Pesan & Feedback) --}}
                                <div class="flex flex-wrap items-center gap-2 pt-0.5 pl-13 sm:pl-14">
                                    @if($item->mode_konsultasi === 'Pesan Portal')
                                        <span class="text-[11px] text-slate-500 font-medium inline-flex items-center gap-1 bg-slate-50 border border-slate-200/60 px-2.5 py-0.5 rounded-lg">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                            {{ $item->pesan->count() }} pesan obrolan
                                        </span>
                                    @endif

                                    @if(in_array($item->status_pengajuan?->value, ['Selesai', 'Dikonversi ke Jurnal']))
                                        @if($item->student_feedback_rating)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-bold">
                                                ⭐ {{ $item->student_feedback_rating }}/5 ({{ $item->student_feedback_emoji?->value ?? 'Lega' }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-semibold">
                                                ⏳ Menunggu ulasan murid
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- Toggle Button Chevron --}}
                            <div class="flex items-center gap-2 shrink-0 self-center">
                                <button 
                                    type="button" 
                                    class="p-2 sm:px-3.5 sm:py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                                    :class="expanded ? 'bg-brand-primary text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                >
                                    <span class="hidden sm:inline" x-text="expanded ? 'Tutup' : 'Buka Sesi / Respon'">Buka Sesi / Respon</span>
                                    <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Expanded Accordion Body --}}
                    <div 
                        x-show="expanded" 
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="border-t border-slate-100 bg-slate-50/50 p-4 sm:p-6 space-y-5"
                    >
                        {{-- 1. Mini Status Stepper --}}
                        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs">
                            <div class="flex items-center justify-between text-[11px] sm:text-xs">
                                <!-- Step 1: Diajukan Murid -->
                                <div class="flex items-center gap-1.5 text-emerald-600 font-extrabold">
                                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]">✓</span>
                                    <span>Diajukan Murid</span>
                                </div>
                                
                                <div class="flex-1 h-0.5 mx-2 {{ in_array($item->status_pengajuan?->value, ['Dijadwalkan', 'Selesai', 'Dikonversi ke Jurnal', 'Ditolak']) ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                                <!-- Step 2: Direspon Guru Wali -->
                                <div class="flex items-center gap-1.5 {{ in_array($item->status_pengajuan?->value, ['Dijadwalkan', 'Selesai', 'Dikonversi ke Jurnal']) ? 'text-brand-primary font-extrabold' : ($item->status_pengajuan?->value === 'Ditolak' ? 'text-rose-600 font-extrabold' : 'text-slate-400 font-semibold') }}">
                                    <span class="w-5 h-5 rounded-full {{ in_array($item->status_pengajuan?->value, ['Dijadwalkan', 'Selesai', 'Dikonversi ke Jurnal']) ? 'bg-brand-primary text-white' : ($item->status_pengajuan?->value === 'Ditolak' ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-500') }} flex items-center justify-center text-[10px]">
                                        {{ in_array($item->status_pengajuan?->value, ['Dijadwalkan', 'Selesai', 'Dikonversi ke Jurnal']) ? '✓' : '2' }}
                                    </span>
                                    <span>Direspon Guru</span>
                                </div>

                                <div class="flex-1 h-0.5 mx-2 {{ in_array($item->status_pengajuan?->value, ['Selesai', 'Dikonversi ke Jurnal']) ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                                <!-- Step 3: Selesai / Jurnal -->
                                <div class="flex items-center gap-1.5 {{ in_array($item->status_pengajuan?->value, ['Selesai', 'Dikonversi ke Jurnal']) ? 'text-emerald-600 font-extrabold' : 'text-slate-400 font-semibold' }}">
                                    <span class="w-5 h-5 rounded-full {{ in_array($item->status_pengajuan?->value, ['Selesai', 'Dikonversi ke Jurnal']) ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-[10px]">
                                        {{ in_array($item->status_pengajuan?->value, ['Selesai', 'Dikonversi ke Jurnal']) ? '✓' : '3' }}
                                    </span>
                                    <span>Selesai / Jurnal</span>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Uraian Cerita / Keluhan Awal Murid --}}
                        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-2">
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span class="font-extrabold uppercase text-[10px] text-brand-primary tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    Cerita Awal dari Murid:
                                </span>
                                <span>{{ $item->created_at ? $item->created_at->translatedFormat('l, d F Y • H:i') : '' }} WIB</span>
                            </div>
                            <h4 class="font-extrabold text-sm text-slate-900">{{ $item->topik_konsultasi }}</h4>
                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-normal whitespace-pre-line bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                                {{ $item->detail_permasalahan }}
                            </p>
                        </div>

                        {{-- 3. Konten Berdasarkan Mode Konsultasi --}}
                        @if($item->mode_konsultasi === 'Tatap Muka')
                            {{-- Card Tiket Janji Temu (Appointment Pass) --}}
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-2xl p-4 sm:p-5 border border-blue-200/80 shadow-xs space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-extrabold uppercase tracking-wider text-blue-900">Jadwal Pertemuan di Sekolah</h5>
                                            <p class="text-[11px] text-blue-700/80">Siswa: {{ $item->siswa?->name }}</p>
                                        </div>
                                    </div>
                                    @if(in_array($item->status_pengajuan?->value, ['Menunggu Konfirmasi', 'Dijadwalkan']))
                                        <button type="button" 
                                                wire:click="openScheduleModal('{{ $item->id }}')" 
                                                class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                                            {{ $item->jadwal_pasti ? 'Ubah Jadwal' : 'Tetapkan Jadwal Pasti' }}
                                        </button>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="bg-white/90 backdrop-blur-xs rounded-xl p-3.5 border border-blue-200/60">
                                        <p class="text-[11px] text-slate-500 font-semibold">Usulan Waktu dari Murid:</p>
                                        <p class="text-xs font-bold text-slate-800 mt-0.5">
                                            {{ $item->usulan_tanggal_waktu ? \Carbon\Carbon::parse($item->usulan_tanggal_waktu)->translatedFormat('l, d M Y • H:i') . ' WIB' : 'Tidak ditentukan' }}
                                        </p>
                                    </div>

                                    <div class="bg-white/90 backdrop-blur-xs rounded-xl p-3.5 border border-blue-200/60">
                                        <p class="text-[11px] text-slate-500 font-semibold">Jadwal Pasti Ditetapkan Guru:</p>
                                        <p class="text-xs font-black {{ $item->jadwal_pasti ? 'text-blue-900' : 'text-amber-600' }} mt-0.5">
                                            {{ $item->jadwal_pasti ? \Carbon\Carbon::parse($item->jadwal_pasti)->translatedFormat('l, d F Y • H:i') . ' WIB' : 'Belum Dikonfirmasi' }}
                                        </p>
                                    </div>
                                </div>

                                @if($item->tanggapan_guru)
                                    <div class="bg-white/90 rounded-xl p-3.5 border border-blue-200/60 space-y-1">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-blue-800">Catatan / Arahan Lokasi untuk Murid:</p>
                                        <p class="text-xs text-slate-800 italic leading-relaxed whitespace-pre-line">"{{ $item->tanggapan_guru }}"</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Ruang Chat Interaktif --}}
                            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                                {{-- Chat Stream Header --}}
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-brand-primary text-white flex items-center justify-center font-black text-xs">
                                            {{ strtoupper(substr($item->siswa?->name ?? 'S', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-extrabold text-slate-800 leading-none">{{ $item->siswa?->name }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">Ruang Aman Obrolan Murid & Guru Wali</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400 flex items-center gap-1" wire:poll.5s>
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Live Sync
                                    </span>
                                </div>

                                {{-- Chat Bubbles Stream --}}
                                <div class="p-4 space-y-3 max-h-80 overflow-y-auto custom-scrollbar bg-slate-50/30" wire:poll.5s>
                                    @forelse($item->pesan as $msg)
                                        <div class="flex {{ $msg->sender_type === 'guru' ? 'justify-end' : 'justify-start' }}">
                                            <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-3 shadow-xs {{ $msg->sender_type === 'guru' ? 'bg-gradient-to-r from-brand-primary to-brand-secondary text-white rounded-tr-xs' : 'bg-white border border-slate-200/80 text-slate-800 rounded-tl-xs' }}">
                                                @if($msg->sender_type === 'siswa')
                                                    <p class="text-[10px] font-extrabold text-brand-primary mb-1">{{ $item->siswa?->name }} (Murid)</p>
                                                @else
                                                    <p class="text-[10px] font-extrabold text-white/80 mb-1">{{ $teacher->name }} (Guru Wali)</p>
                                                @endif
                                                <p class="text-xs leading-relaxed whitespace-pre-line">{{ $msg->pesan }}</p>
                                                <p class="text-[10px] mt-1 text-right {{ $msg->sender_type === 'guru' ? 'text-white/70' : 'text-slate-400' }}">{{ $msg->created_at->format('H:i') }}</p>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-6 text-slate-400 text-xs">
                                            <p>Belum ada pesan obrolan.</p>
                                            <p class="text-[11px] mt-1">Tulis tanggapan atau sapaan pertama untuk memulai obrolan bimbingan.</p>
                                        </div>
                                    @endforelse
                                </div>

                                {{-- Chat Input / Selesai Banner --}}
                                @if(in_array($item->status_pengajuan?->value, ['Menunggu Konfirmasi', 'Dijadwalkan']))
                                    <div class="p-3 bg-white border-t border-slate-100 flex items-center gap-2">
                                        <input type="text" 
                                               wire:model="pesanInputs.{{ $item->id }}" 
                                               wire:keydown.enter.prevent="kirimPesanInline('{{ $item->id }}')"
                                               placeholder="Tulis tanggapan / bimbingan untuk {{ $item->siswa?->name }}... (Tekan Enter)" 
                                               class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all">
                                        <button type="button" 
                                                wire:click="kirimPesanInline('{{ $item->id }}')" 
                                                class="px-5 py-2.5 bg-brand-primary hover:bg-brand-secondary text-white rounded-xl font-bold text-xs shadow-md shadow-brand-primary/20 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                                            <span>Kirim</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </button>
                                    </div>
                                    @error("pesanInputs.{$item->id}")
                                        <div class="px-4 pb-2 text-[11px] text-rose-500 font-semibold">{{ $message }}</div>
                                    @enderror
                                @else
                                    <div class="p-3 bg-emerald-50 border-t border-emerald-100 text-emerald-800 text-xs font-medium text-center flex items-center justify-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>Sesi obrolan ini telah ditandai selesai. Ruang aman telah ditutup.</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- 4. Alasan Penolakan (Jika ditolak) --}}
                        @if($item->alasan_penolakan)
                            <div class="bg-rose-50 rounded-2xl p-4 border border-rose-200 text-xs space-y-1">
                                <span class="font-extrabold text-rose-700 uppercase tracking-wider text-[10px] block">Alasan Penolakan / Pengalihan Jadwal:</span>
                                <p class="text-rose-950 whitespace-pre-line leading-relaxed">{{ $item->alasan_penolakan }}</p>
                            </div>
                        @endif

                        {{-- 5. Refleksi & Ulasan Siswa --}}
                        @if(in_array($item->status_pengajuan?->value, ['Selesai', 'Dikonversi ke Jurnal']) && $item->student_feedback_rating)
                            <div class="bg-amber-50/80 rounded-2xl p-4 border border-amber-200/80 shadow-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-extrabold text-amber-800 text-xs uppercase tracking-wider flex items-center gap-1">
                                        <span>⭐</span> Refleksi & Evaluasi dari Murid:
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black text-amber-700 bg-amber-100 px-2.5 py-0.5 rounded-full border border-amber-300">
                                            ⭐ {{ $item->student_feedback_rating }} / 5
                                        </span>
                                        @if($item->student_feedback_emoji)
                                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-white text-amber-800 border border-amber-200">
                                                {{ $item->student_feedback_emoji->value }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if($item->student_feedback_note)
                                    <p class="text-xs text-amber-900 mt-1 leading-relaxed italic border-l-2 border-amber-400 pl-3 py-0.5 bg-white/60 rounded-r-lg">
                                        "{{ $item->student_feedback_note }}"
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- 6. Action Hub Guru Wali --}}
                        <div class="pt-3 border-t border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-[11px] text-slate-400 font-medium">
                                ID Konsultasi: <code class="text-slate-600 font-mono">{{ substr($item->id, 0, 8) }}</code>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if($item->status_pengajuan?->value === 'Menunggu Konfirmasi')
                                    @if($item->mode_konsultasi === 'Tatap Muka')
                                        <button type="button" 
                                                wire:click="openScheduleModal('{{ $item->id }}')" 
                                                class="px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-md shadow-brand-primary/20 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>Jadwalkan Pertemuan</span>
                                        </button>
                                    @endif

                                    <button type="button" 
                                            wire:click="openRujukModal('{{ $item->id }}')" 
                                            class="px-4 py-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 font-bold text-xs shadow-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        <span>Rujuk ke Guru BK</span>
                                    </button>

                                    <button type="button" 
                                            wire:click="openRejectModal('{{ $item->id }}')" 
                                            class="px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Tolak Permintaan</span>
                                    </button>

                                @elseif($item->status_pengajuan?->value === 'Dijadwalkan')
                                    @if($item->mode_konsultasi === 'Tatap Muka')
                                        <button type="button" 
                                                wire:click="openScheduleModal('{{ $item->id }}')" 
                                                class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold text-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Ubah Jadwal</span>
                                        </button>
                                    @endif

                                    <!-- Konversi / Catat ke Jurnal -->
                                    <a href="{{ route('portal-guru.guru-wali.jurnal.create', ['konsultasi_id' => $item->id]) }}" 
                                       class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        <span>Catat ke Jurnal Pendampingan</span>
                                    </a>

                                    <button type="button" 
                                            wire:click="openBadgeModal('{{ $item->id }}')" 
                                            class="px-4 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-bold text-xs shadow-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        <span>Selesaikan & Beri Badge</span>
                                    </button>

                                    <button type="button" 
                                            wire:click="openRujukModal('{{ $item->id }}')" 
                                            class="px-3.5 py-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 font-bold text-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        <span>Rujuk ke BK</span>
                                    </button>

                                @else
                                    @if($item->jurnal)
                                        <a href="{{ route('portal-guru.guru-wali.jurnal') }}" 
                                           class="px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs shadow-xs transition-all flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Terdokumentasi di Jurnal</span>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Sesi Telah Selesai</span>
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-4">
                {{ $konsultasis->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak ada permintaan konsultasi</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    Saat siswa di kelompok Anda mengajukan topik bimbingan melalui portal siswa, daftarnya akan muncul di sini.
                </p>
            </div>
        @endif
    </div>

    <!-- MODAL JADWALKAN PERTEMUAN -->
    @if($showScheduleModal && $consultationToSchedule)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">Konfirmasi Jadwal Pertemuan</h3>
                    <button type="button" wire:click="$set('showScheduleModal', false)" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="p-3 rounded-2xl bg-brand-primary/5 border border-brand-primary/20">
                        <span class="font-bold text-brand-primary block">{{ $consultationToSchedule->siswa?->name }}</span>
                        <span class="text-slate-600 block mt-0.5">Topik: {{ $consultationToSchedule->topik_konsultasi }}</span>
                        @if($consultationToSchedule->usulan_tanggal_waktu)
                            <span class="text-slate-500 block mt-0.5">Usulan: {{ \Carbon\Carbon::parse($consultationToSchedule->usulan_tanggal_waktu)->translatedFormat('d F Y, H:i') }} WIB</span>
                        @endif
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Tetapkan Jadwal Pasti <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local" 
                               wire:model="jadwal_pasti" 
                               class="w-full rounded-2xl border-slate-200 text-sm py-2 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                        @error('jadwal_pasti')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Pesan Petunjuk / Lokasi untuk Siswa
                        </label>
                        <textarea wire:model="tanggapan_jadwal" 
                                  rows="3" 
                                  placeholder="Contoh: Silakan datang ke ruang Guru Wali pada jam istirahat pertama..." 
                                  class="w-full rounded-2xl border-slate-200 text-xs p-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" 
                            wire:click="$set('showScheduleModal', false)" 
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="button" 
                            wire:click="saveSchedule" 
                            class="px-5 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-md shadow-brand-primary/20">
                        Konfirmasi & Jadwalkan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL BALAS PESAN / TANGGAPAN -->
    @if($showReplyModal && $consultationToReply)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">Balas Pesan Konsultasi</h3>
                    <button type="button" wire:click="$set('showReplyModal', false)" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="font-bold text-slate-800 block">{{ $consultationToReply->siswa?->name }}</span>
                        <p class="text-slate-600 mt-1 whitespace-pre-line">{{ $consultationToReply->detail_permasalahan }}</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Tanggapan / Jawaban Guru <span class="text-rose-500">*</span>
                        </label>
                        <textarea wire:model="tanggapan_pesan" 
                                  rows="4" 
                                  placeholder="Tuliskan arahan, bimbingan, atau saran untuk peserta didik..." 
                                  class="w-full rounded-2xl border-slate-200 text-xs p-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary"></textarea>
                        @error('tanggapan_pesan')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="markAsCompleted" class="rounded border-slate-300 text-brand-primary focus:ring-brand-primary">
                        <span class="text-slate-700 font-semibold">Tandai konsultasi ini langsung selesai setelah dibalas</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" 
                            wire:click="$set('showReplyModal', false)" 
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="button" 
                            wire:click="saveReply" 
                            class="px-5 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-md shadow-brand-primary/20">
                        Kirim Tanggapan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL TOLAK PERMINTAAN -->
    @if($showRejectModal && $consultationToReject)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-rose-700">Tolak Permintaan Konsultasi</h3>
                    <button type="button" wire:click="$set('showRejectModal', false)" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <p class="text-slate-600">
                        Berikan alasan penolakan atau petunjuk jadwal lain bagi <strong>{{ $consultationToReject->siswa?->name }}</strong>:
                    </p>
                    <textarea wire:model="alasan_penolakan" 
                              rows="3" 
                              placeholder="Misal: Bapak sedang tugas dinas luar kota pada hari tersebut, silakan ajukan ulang untuk hari Kamis..." 
                              class="w-full rounded-2xl border-slate-200 text-xs p-3 focus:border-rose-500 focus:ring-1 focus:ring-rose-500"></textarea>
                    @error('alasan_penolakan')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" 
                            wire:click="$set('showRejectModal', false)" 
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="button" 
                            wire:click="saveReject" 
                            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20">
                        Tolak Permintaan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DETAIL & OBROLAN -->
    @if($showDetailModal && $selectedConsultation)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeDetail"></div>

                <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-100">
                    
                    {{-- Header --}}
                    <div class="bg-brand-primary p-6 text-white flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-white/20 text-white">
                                {{ $selectedConsultation->mode_konsultasi === 'Pesan Portal' ? 'Ruang Aman Obrolan' : 'Detail Sesi Konsultasi' }}
                            </span>
                            <h3 class="text-base font-extrabold mt-1 text-white">{{ $selectedConsultation->siswa?->name }}</h3>
                        </div>
                        <button type="button" wire:click="closeDetail" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-bold text-slate-700">Topik: {{ $selectedConsultation->topik_konsultasi }}</span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold text-slate-600">Mode: {{ $selectedConsultation->mode_konsultasi === 'Pesan Portal' ? 'Ruang Aman (Chat)' : 'Tatap Muka' }}</span>
                        </div>

                        {{-- Permasalahan Siswa --}}
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-1.5">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pesan / Permasalahan Awal:</p>
                            <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed font-medium">
                                {{ $selectedConsultation->detail_permasalahan }}
                            </p>
                        </div>

                        {{-- Chat Thread / Tanggapan --}}
                        @if($selectedConsultation->mode_konsultasi === 'Pesan Portal')
                            <div class="mt-4 pt-4 border-t border-slate-100 space-y-4">
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Riwayat Obrolan</h4>
                                
                                <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar" wire:poll.5s>
                                    @forelse($selectedConsultation->pesan as $msg)
                                        <div class="flex {{ $msg->sender_type === 'guru' ? 'justify-end' : 'justify-start' }}">
                                            <div class="max-w-[85%] rounded-2xl p-3 {{ $msg->sender_type === 'guru' ? 'bg-brand-primary text-white rounded-tr-sm' : 'bg-slate-100 text-slate-800 rounded-tl-sm' }}">
                                                @if($msg->sender_type === 'siswa')
                                                    <p class="text-[10px] font-extrabold text-slate-600 mb-1">{{ $selectedConsultation->siswa?->name }}</p>
                                                @endif
                                                <p class="text-xs leading-relaxed whitespace-pre-line">{{ $msg->pesan }}</p>
                                                <p class="text-[10px] mt-1.5 text-right opacity-70">{{ $msg->created_at->format('H:i') }}</p>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-500 italic text-center">Belum ada obrolan tambahan.</p>
                                    @endforelse
                                </div>

                                @if(in_array($selectedConsultation->status_pengajuan?->value, ['Menunggu Konfirmasi', 'Dijadwalkan']))
                                    <form wire:submit.prevent="kirimPesanObrolan" class="mt-3 flex gap-2">
                                        <input type="text" wire:model="pesanBaru" placeholder="Ketik balasan untuk siswa di sini..." class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all">
                                        <button type="submit" class="px-4 py-2.5 bg-brand-primary hover:bg-brand-secondary text-white rounded-xl font-bold text-xs shadow-md transition-all cursor-pointer">
                                            Kirim
                                        </button>
                                    </form>
                                    @error('pesanBaru') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                                @else
                                    <div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs text-center rounded-xl font-medium">
                                        Konsultasi ini telah ditandai Selesai.
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Respon Guru Wali (Legacy / Tatap Muka) --}}
                            @if($selectedConsultation->tanggapan_guru)
                                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Tanggapan / Arahan Guru Wali:</p>
                                    </div>
                                    <p class="text-xs text-emerald-950 whitespace-pre-line leading-relaxed font-medium">
                                        {{ $selectedConsultation->tanggapan_guru }}
                                    </p>
                                </div>
                            @endif
                        @endif

                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        @if(in_array($selectedConsultation->status_pengajuan?->value, ['Menunggu Konfirmasi', 'Dijadwalkan']))
                            <button type="button" wire:click="markCompleted('{{ $selectedConsultation->id }}')" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-md transition-colors">
                                Tandai Selesai & Tutup Obrolan
                            </button>
                        @else
                            <div></div>
                        @endif
                        
                        <button type="button" wire:click="closeDetail" class="px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL RUJUK KE BK -->
    @if($showRujukModal && $consultationToRujuk)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="$set('showRujukModal', false)"></div>

                <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-slate-100">
                    <form wire:submit.prevent="saveRujuk">
                        {{-- Header --}}
                        <div class="bg-orange-500 p-6 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Rujuk Kasus ke Guru BK</h3>
                                <p class="text-orange-100 text-sm mt-0.5">Eskalasi untuk penanganan lanjutan.</p>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="p-6 space-y-5">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Siswa:</p>
                                <p class="text-sm font-bold text-slate-800">{{ $consultationToRujuk->siswa?->name }}</p>
                                
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 mt-3">Topik Konsultasi:</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $consultationToRujuk->topik_konsultasi }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Alasan Merujuk Kasus Ini <span class="text-rose-500">*</span></label>
                                <textarea wire:model="alasan_rujukan" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50" placeholder="Jelaskan alasan mengapa kasus ini memerlukan penanganan Guru BK..."></textarea>
                                @error('alasan_rujukan') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button type="button" wire:click="$set('showRujukModal', false)" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-sm transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md transition-colors">
                                Kirim Rujukan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL TANDAI SELESAI & BADGE KARAKTER -->
    @if($showBadgeModal && $consultationToComplete)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="$set('showBadgeModal', false)"></div>

                <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-slate-100">
                    <form wire:submit.prevent="markCompletedWithBadge">
                        {{-- Header --}}
                        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 p-6 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Selesaikan Konsultasi</h3>
                                <p class="text-emerald-100 text-sm mt-0.5">Berikan apresiasi (badge) opsional untuk siswa.</p>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="p-6 space-y-5">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Siswa:</p>
                                <p class="text-sm font-bold text-slate-800">{{ $consultationToComplete->siswa?->name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Badge Apresiasi (Opsional)</label>
                                <div class="grid grid-cols-2 gap-3">
                                    @php
                                        $badges = [
                                            ['name' => 'Pemberani', 'icon' => '🦁', 'desc' => 'Berani mengemukakan masalah'],
                                            ['name' => 'Jujur', 'icon' => '🕊️', 'desc' => 'Terbuka dan apa adanya'],
                                            ['name' => 'Proaktif', 'icon' => '🔥', 'desc' => 'Aktif mencari solusi'],
                                            ['name' => 'Inspiratif', 'icon' => '🌟', 'desc' => 'Memberikan inspirasi positif'],
                                        ];
                                    @endphp
                                    @foreach($badges as $badge)
                                        <label class="relative cursor-pointer">
                                            <input type="radio" wire:model="selectedBadge" value="{{ $badge['name'] }}" class="peer sr-only" name="badge_selection">
                                            <div class="p-3 rounded-xl border-2 border-slate-100 bg-white hover:bg-slate-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all flex flex-col items-center text-center gap-1">
                                                <span class="text-2xl">{{ $badge['icon'] }}</span>
                                                <span class="font-bold text-xs text-slate-800">{{ $badge['name'] }}</span>
                                                <span class="text-[10px] text-slate-500">{{ $badge['desc'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            @if($selectedBadge)
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Catatan Apresiasi (Opsional)</label>
                                    <textarea wire:model="catatan_apresiasi" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50" placeholder="Tuliskan pesan singkat untuk siswa..."></textarea>
                                </div>
                            @endif
                            
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl flex gap-3 text-xs text-blue-800">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p>Menyelesaikan konsultasi akan menutup ruang obrolan. Anda dapat melewatinya tanpa memberi badge dengan langsung menekan tombol "Selesaikan Saja".</p>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                            <button type="button" wire:click="$set('showBadgeModal', false)" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-200 font-bold text-xs transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-colors">
                                {{ $selectedBadge ? 'Beri Badge & Selesaikan' : 'Selesaikan Saja' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
