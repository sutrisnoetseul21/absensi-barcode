<div class="p-4 lg:p-8 space-y-6 max-w-7xl mx-auto pb-16">

    {{-- Flash Notifications --}}
    @if(session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs transition-all animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs transition-all animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <p class="text-sm font-semibold">{{ session('error') }}</p>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    {{-- Hero Banner --}}
    <div class="relative bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 overflow-hidden border border-brand-primary/30">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand-secondary/20 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-white/90 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Permendikdasmen No. 11/2025 • Pendampingan Holistik
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                    Bimbingan & Konsultasi Guru Wali
                </h1>
                <p class="text-white/80 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                    Saluran privat dan aman untuk menyampaikan kendala belajar, minat bakat, motivasi, ataupun hal sosial-pribadi langsung kepada Guru Wali Anda.
                </p>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap gap-3 items-center self-start md:self-center">
                @if($guruWali)
                    <button wire:click="openModal" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-slate-900 hover:bg-slate-100 font-extrabold text-sm shadow-xl transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajukan Konsultasi Baru
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Kartu Guru Wali Pendamping --}}
    @if($guruWali)
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 transition-all hover:shadow-md">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        @if($guruWali->photo_path)
                            <img src="{{ asset('storage/'.$guruWali->photo_path) }}" alt="{{ $guruWali->name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-brand-primary/20 shadow-sm">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-black text-xl border border-brand-primary/20 shadow-inner">
                                {{ substr($guruWali->name, 0, 2) }}
                            </div>
                        @endif
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full shadow-xs" title="Guru Wali Aktif"></span>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-brand-primary/10 text-brand-primary">
                                Guru Wali Pendamping Anda
                            </span>
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                {{ $kelompok->nama_kelompok ?? 'Kelompok Bimbingan' }}
                            </span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mt-1">
                            {{ $guruWali->name }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                            <span>NIP: {{ $guruWali->nip ?? '-' }}</span>
                            <span>•</span>
                            <span>Mulai Mendampingi: {{ $keanggotaan->created_at ? $keanggotaan->created_at->translatedFormat('d M Y') : '-' }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 justify-end">
                    <div class="text-right hidden md:block">
                        <p class="text-xs text-slate-400 font-medium">Kerahasiaan Terjamin</p>
                        <p class="text-xs font-bold text-slate-700">Ruang Diskusi & Solusi Pribadi</p>
                    </div>
                    <button wire:click="openModal" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-primary/10 hover:bg-brand-primary/20 text-brand-primary font-bold text-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Kirim Pesan / Usul Pertemuan
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 text-amber-900 flex items-start gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-2xl bg-amber-200/80 text-amber-800 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="font-extrabold text-base">Belum Terdaftar di Kelompok Guru Wali</h3>
                <p class="text-sm text-amber-800/90 mt-1 leading-relaxed">
                    Akun Anda saat ini belum dipetakan ke dalam kelompok dampingan Guru Wali aktif. Silakan menghubungi wali kelas atau staf bagian akademik sekolah untuk penempatan kelompok pembimbingan Anda.
                </p>
            </div>
        </div>
    @endif

    {{-- Tabs & Filters Bar --}}
    <div class="space-y-4">
        <!-- Tab Navigation -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
            <button wire:click="$set('activeTab', 'semua')" class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'semua' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Semua Pengajuan</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['semua'] }}</span>
            </button>

            <button wire:click="$set('activeTab', 'menunggu')" class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'menunggu' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Menunggu Respon</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'menunggu' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $counts['menunggu'] }}</span>
            </button>

            <button wire:click="$set('activeTab', 'dijadwalkan')" class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'dijadwalkan' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Dijadwalkan / Dibalas</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'dijadwalkan' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">{{ $counts['dijadwalkan'] }}</span>
            </button>

            <button wire:click="$set('activeTab', 'selesai')" class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'selesai' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Selesai / Dikonversi</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'selesai' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">{{ $counts['selesai'] }}</span>
            </button>

            <button wire:click="$set('activeTab', 'ditolak')" class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'ditolak' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <span>Ditolak</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'ditolak' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">{{ $counts['ditolak'] }}</span>
            </button>

            <button wire:click="$set('activeTab', 'catatan_guru')" class="px-4 py-2.5 rounded-2xl text-xs font-extrabold transition-all shrink-0 cursor-pointer flex items-center gap-2 {{ $activeTab === 'catatan_guru' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
                <svg class="w-4 h-4 text-purple-500 {{ $activeTab === 'catatan_guru' ? 'text-white' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                <span>Catatan & Pesan Guru</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'catatan_guru' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800' }}">{{ $counts['catatan_guru'] }}</span>
            </button>
        </div>

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari topik permasalahan atau kata kunci..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all">
            </div>

            <div class="w-full sm:w-auto">
                <select wire:model.live="kategoriFilter" class="w-full sm:w-56 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all cursor-pointer">
                    <option value="">Semua Kategori (4 Pilar)</option>
                    <option value="Akademik">📚 Akademik & Belajar</option>
                    <option value="Karakter & Kedisiplinan">🛡️ Karakter & Kedisiplinan</option>
                    <option value="Minat & Bakat / Ekskul">🎨 Minat & Bakat / Ekskul</option>
                    <option value="Sosial & Psikologis">🤝 Sosial & Psikologis</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Content List --}}
    @if($activeTab === 'catatan_guru')
        {{-- DAFTAR CATATAN DAMPINGAN PUBLIK DARI GURU WALI --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                    Catatan & Arahan Motivasi dari Guru Wali
                </h3>
                <span class="text-xs text-slate-400">Total: {{ $catatanPublikList->total() }} catatan</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($catatanPublikList as $catatan)
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-200/60">
                                    {{ $catatan->kategori_pendampingan }}
                                </span>
                                <span class="text-xs text-slate-400 font-medium">
                                    {{ $catatan->tanggal_waktu ? $catatan->tanggal_waktu->translatedFormat('d M Y, H:i') : '-' }}
                                </span>
                            </div>

                            <div>
                                <h4 class="text-sm font-extrabold text-slate-900 group-hover:text-purple-600 transition-colors">
                                    Sesi Bimbingan {{ $catatan->jenis_pendampingan }}
                                </h4>
                                <p class="text-xs text-slate-600 mt-2 line-clamp-3 leading-relaxed">
                                    {{ $catatan->uraian_pembahasan }}
                                </p>
                            </div>

                            @if($catatan->rencana_tindak_lanjut)
                                <div class="bg-emerald-50/70 border border-emerald-200/60 rounded-2xl p-3">
                                    <p class="text-[10px] font-extrabold text-emerald-800 uppercase tracking-wider">Tindak Lanjut / Arahan:</p>
                                    <p class="text-xs text-emerald-900 mt-0.5 line-clamp-2">{{ $catatan->rencana_tindak_lanjut }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                                <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-[10px]">
                                    {{ substr($catatan->guru?->name ?? 'GW', 0, 2) }}
                                </div>
                                <span class="truncate max-w-[150px]">{{ $catatan->guru?->name ?? 'Guru Wali' }}</span>
                            </div>

                            <button wire:click="openCatatanDetail('{{ $catatan->id }}')" class="inline-flex items-center gap-1.5 text-xs font-bold text-purple-600 hover:text-purple-800 transition-colors">
                                <span>Baca Lengkap</span>
                                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-purple-50 text-purple-600 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <h4 class="text-base font-extrabold text-slate-800">Belum Ada Catatan Publik</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Catatan motivasi atau rekomendasi belajar yang dipublikasikan oleh Guru Wali Anda akan tampil di sini.
                        </p>
                    </div>
                @endforelse
            </div>

            @if($catatanPublikList->hasPages())
                <div class="pt-4">
                    {{ $catatanPublikList->links() }}
                </div>
            @endif
        </div>
    @else
        {{-- DAFTAR PENGAJUAN KONSULTASI SISWA --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                    Daftar Pengajuan Konsultasi Mandiri
                </h3>
                <span class="text-xs text-slate-400">Total: {{ $konsultasiList->total() }} pengajuan</span>
            </div>

            <div class="grid grid-cols-1 gap-4">
                @forelse($konsultasiList as $item)
                    @php
                        $statusBadge = match($item->status_pengajuan) {
                            'Menunggu Konfirmasi' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'dot' => 'bg-amber-500', 'label' => 'Menunggu Konfirmasi'],
                            'Dijadwalkan'         => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'dot' => 'bg-blue-500', 'label' => 'Dijadwalkan / Ada Respon'],
                            'Selesai', 'Dikonversi ke Jurnal' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-500', 'label' => 'Selesai / Dicatat di Jurnal'],
                            'Ditolak'             => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-800', 'dot' => 'bg-rose-500', 'label' => 'Ditolak'],
                            default               => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'text' => 'text-slate-800', 'dot' => 'bg-slate-500', 'label' => $item->status_pengajuan],
                        };

                        $kategoriColor = match($item->kategori_pendampingan) {
                            'Akademik'                => 'text-blue-700 bg-blue-50 border-blue-200',
                            'Karakter & Kedisiplinan' => 'text-amber-700 bg-amber-50 border-amber-200',
                            'Minat & Bakat / Ekskul'  => 'text-purple-700 bg-purple-50 border-purple-200',
                            'Sosial & Psikologis'     => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                            default                   => 'text-slate-700 bg-slate-50 border-slate-200',
                        };
                    @endphp

                    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs hover:shadow-md transition-all">
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <div class="space-y-3 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} border {{ $statusBadge['border'] }}">
                                        <span class="w-2 h-2 rounded-full {{ $statusBadge['dot'] }}"></span>
                                        {{ $statusBadge['label'] }}
                                    </span>

                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $kategoriColor }}">
                                        {{ $item->kategori_pendampingan }}
                                    </span>

                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        {{ $item->mode_konsultasi === 'Tatap Muka' ? '🏫 Tatap Muka' : '💬 Pesan Portal' }}
                                    </span>

                                    <span class="text-xs text-slate-400 ml-auto">
                                        {{ $item->created_at ? $item->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                    </span>
                                </div>

                                <div>
                                    <h4 class="text-base font-extrabold text-slate-900">
                                        {{ $item->topik_konsultasi }}
                                    </h4>
                                    <p class="text-xs text-slate-600 mt-1 line-clamp-2 leading-relaxed">
                                        {{ $item->detail_permasalahan }}
                                    </p>
                                </div>

                                {{-- Jadwal Pertemuan Disetujui --}}
                                @if($item->jadwal_pasti)
                                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-50 border border-blue-200/80 text-blue-900 text-xs font-bold">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span>Jadwal Pertemuan: <strong class="text-blue-700">{{ $item->jadwal_pasti->translatedFormat('l, d F Y • H:i') }} WIB</strong></span>
                                    </div>
                                @elseif($item->usulan_tanggal_waktu)
                                    <div class="text-xs text-slate-500 font-medium">
                                        Usulan Waktu: {{ $item->usulan_tanggal_waktu->translatedFormat('d M Y, H:i') }}
                                    </div>
                                @endif

                                {{-- Kotak Cuplikan Balasan Guru --}}
                                @if($item->tanggapan_guru)
                                    <div class="bg-brand-primary/5 border border-brand-primary/20 rounded-2xl p-3.5 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-brand-primary/20 text-brand-primary">Tanggapan Guru Wali:</span>
                                            <span class="text-xs font-bold text-slate-700">{{ $item->guru?->name }}</span>
                                        </div>
                                        <p class="text-xs text-slate-700 line-clamp-2 italic leading-relaxed">
                                            "{{ $item->tanggapan_guru }}"
                                        </p>
                                    </div>
                                @endif

                                {{-- Alasan Penolakan --}}
                                @if($item->status_pengajuan === 'Ditolak' && $item->alasan_penolakan)
                                    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-3 text-xs text-rose-800">
                                        <strong>Alasan Belum Dapat Diterima:</strong> {{ $item->alasan_penolakan }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 pt-3 lg:pt-0 lg:border-t-0 border-t border-slate-100 shrink-0 self-end lg:self-start">
                                @if($item->status_pengajuan === 'Menunggu Konfirmasi')
                                    <button wire:click="batalkanKonsultasi('{{ $item->id }}')" wire:confirm="Yakin ingin membatalkan permohonan konsultasi ini?" class="px-3 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-colors">
                                        Batalkan
                                    </button>
                                @endif

                                <button wire:click="openDetail('{{ $item->id }}')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-brand-primary hover:text-white text-slate-700 text-xs font-extrabold transition-all cursor-pointer">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-brand-primary/10 text-brand-primary mx-auto flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <h4 class="text-base font-extrabold text-slate-800">Belum Ada Pengajuan Konsultasi</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Anda dapat mengajukan konsultasi mandiri terkait akademik, minat bakat, atau kendala pribadi kepada Guru Wali kapan saja.
                        </p>
                        @if($guruWali)
                            <button wire:click="openModal" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-primary text-white text-xs font-extrabold shadow-md hover:bg-brand-secondary transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                Ajukan Konsultasi Sekarang
                            </button>
                        @endif
                    </div>
                @endforelse
            </div>

            @if($konsultasiList->hasPages())
                <div class="pt-4">
                    {{ $konsultasiList->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- MODAL FORM PENGAJUAN KONSULTASI BARU --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

                <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-100">
                    
                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-brand-primary to-brand-secondary p-6 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold">Form Pengajuan Konsultasi</h3>
                                <p class="text-xs text-white/80">Guru Wali: {{ $guruWali?->name ?? 'Pembimbing' }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <form wire:submit.prevent="simpan" class="p-6 space-y-5">
                        
                        {{-- Kategori 4 Pilar --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Kategori Pendampingan (4 Pilar) <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach([
                                    'Akademik'                => ['label' => 'Akademik & Belajar', 'desc' => 'Tugas, pemahaman materi, target nilai', 'icon' => '📚'],
                                    'Karakter & Kedisiplinan' => ['label' => 'Karakter & Disiplin', 'desc' => 'Kebiasaan diri, motivasi, ketertiban', 'icon' => '🛡️'],
                                    'Minat & Bakat / Ekskul'  => ['label' => 'Minat, Bakat & Ekskul', 'desc' => 'Hobi, ekstrakurikuler, potensi diri', 'icon' => '🎨'],
                                    'Sosial & Psikologis'     => ['label' => 'Sosial & Emosional', 'desc' => 'Pertemanan, kecemasan, keluarga', 'icon' => '🤝'],
                                ] as $key => $val)
                                    <label class="flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $kategori === $key ? 'bg-brand-primary/5 border-brand-primary text-brand-primary shadow-xs' : 'bg-slate-50/50 border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                        <input type="radio" wire:model="kategori" value="{{ $key }}" class="mt-1 text-brand-primary focus:ring-brand-primary">
                                        <div>
                                            <p class="text-xs font-extrabold flex items-center gap-1.5">{{ $val['icon'] }} {{ $val['label'] }}</p>
                                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $val['desc'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('kategori') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Mode Konsultasi --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Mode Konsultasi yang Diinginkan <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all {{ $mode === 'Tatap Muka' ? 'bg-brand-primary/5 border-brand-primary text-brand-primary font-bold shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                                    <input type="radio" wire:model.live="mode" value="Tatap Muka" class="text-brand-primary focus:ring-brand-primary">
                                    <span class="text-xs">🏫 Tatap Muka di Sekolah</span>
                                </label>
                                <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all {{ $mode === 'Pesan Portal' ? 'bg-brand-primary/5 border-brand-primary text-brand-primary font-bold shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                                    <input type="radio" wire:model.live="mode" value="Pesan Portal" class="text-brand-primary focus:ring-brand-primary">
                                    <span class="text-xs">💬 Pesan Singkat Portal</span>
                                </label>
                            </div>
                            @error('mode') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Usulan Tanggal & Waktu (Opsional jika Tatap Muka) --}}
                        @if($mode === 'Tatap Muka')
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Usulan Waktu Pertemuan <span class="text-slate-400 font-normal normal-case">(Opsional - akan dikonfirmasi oleh guru)</span>
                                </label>
                                <input type="datetime-local" wire:model="usulan_tanggal_waktu" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all">
                                @error('usulan_tanggal_waktu') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Topik Konsultasi --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Topik Konsultasi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="topik" placeholder="Contoh: Kesulitan mengatur ritme belajar dan jadwal ekskul" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all">
                            @error('topik') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Detail Permasalahan --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Cerita / Uraian Kendala yang Dihadapi <span class="text-rose-500">*</span>
                            </label>
                            <textarea wire:model="detail" rows="4" placeholder="Ceritakan secara terbuka apa yang sedang kamu rasakan atau alami. Pesan ini bersifat rahasia dan hanya dapat dibaca oleh Guru Wali Anda..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all"></textarea>
                            @error('detail') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Privacy Notice --}}
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-500 flex items-center gap-3">
                            <svg class="w-5 h-5 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <span>Data konsultasi ini bersifat privat. Hanya Anda dan Guru Wali Anda yang memiliki akses.</span>
                        </div>

                        {{-- Actions --}}
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeModal" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-xs transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-extrabold text-xs shadow-md shadow-brand-primary/20 transition-all cursor-pointer">
                                <svg wire:loading wire:target="simpan" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL KONSULTASI --}}
    @if($showDetailModal && $selectedKonsultasi)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

                <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-100">
                    
                    {{-- Header --}}
                    <div class="bg-slate-900 p-6 text-white flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-white/20 text-white">Detail Konsultasi</span>
                            <h3 class="text-base font-extrabold mt-1 text-white">{{ $selectedKonsultasi->topik_konsultasi }}</h3>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-bold text-slate-700">Kategori: {{ $selectedKonsultasi->kategori_pendampingan }}</span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold text-slate-600">Mode: {{ $selectedKonsultasi->mode_konsultasi }}</span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold text-slate-600">Dikirim: {{ $selectedKonsultasi->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>

                        {{-- Pertanyaan / Cerita Siswa --}}
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-1.5">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pesan / Permasalahan yang Diajukan:</p>
                            <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed font-medium">
                                {{ $selectedKonsultasi->detail_permasalahan }}
                            </p>
                        </div>

                        {{-- Jadwal Pertemuan Pasti --}}
                        @if($selectedKonsultasi->jadwal_pasti)
                            <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 space-y-1">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-blue-700">🗓️ Jadwal Pertemuan Tatap Muka:</p>
                                <p class="text-sm font-extrabold text-blue-800">
                                    {{ $selectedKonsultasi->jadwal_pasti->translatedFormat('l, d F Y') }} pukul {{ $selectedKonsultasi->jadwal_pasti->format('H:i') }} WIB
                                </p>
                            </div>
                        @endif

                        {{-- Respon Guru Wali --}}
                        @if($selectedKonsultasi->tanggapan_guru)
                            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Tanggapan / Arahan Guru Wali:</p>
                                    <span class="text-[10px] font-bold text-emerald-800">{{ $selectedKonsultasi->guru?->name }}</span>
                                </div>
                                <p class="text-xs text-emerald-950 whitespace-pre-line leading-relaxed font-medium">
                                    {{ $selectedKonsultasi->tanggapan_guru }}
                                </p>
                            </div>
                        @endif

                        {{-- Alasan Penolakan --}}
                        @if($selectedKonsultasi->status_pengajuan === 'Ditolak' && $selectedKonsultasi->alasan_penolakan)
                            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs text-rose-800 space-y-1">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700">Alasan Belum Dapat Dipenuhi:</p>
                                <p class="font-medium">{{ $selectedKonsultasi->alasan_penolakan }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button type="button" wire:click="closeDetailModal" class="px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL CATATAN DAMPINGAN PUBLIK DARI GURU --}}
    @if($showCatatanModal && $selectedCatatan)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeCatatanModal"></div>

                <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-slate-100">
                    
                    {{-- Header --}}
                    <div class="bg-purple-900 p-6 text-white flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-white/20 text-white">Catatan Dampingan Guru</span>
                            <h3 class="text-base font-extrabold mt-1 text-white">Sesi {{ $selectedCatatan->jenis_pendampingan }} • {{ $selectedCatatan->kategori_pendampingan }}</h3>
                        </div>
                        <button type="button" wire:click="closeCatatanModal" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="flex items-center justify-between text-xs text-slate-500 pb-3 border-b border-slate-100">
                            <span>Guru Wali: <strong>{{ $selectedCatatan->guru?->name }}</strong></span>
                            <span>Tanggal: {{ $selectedCatatan->tanggal_waktu ? $selectedCatatan->tanggal_waktu->translatedFormat('d F Y, H:i') : '-' }}</span>
                        </div>

                        <div class="space-y-1.5">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Ringkasan Sesi Dampingan:</p>
                            <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed font-medium bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                {{ $selectedCatatan->uraian_pembahasan }}
                            </p>
                        </div>

                        @if($selectedCatatan->rencana_tindak_lanjut)
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">🎯 Rencana Tindak Lanjut & Motivasi:</p>
                                <p class="text-xs font-semibold whitespace-pre-line leading-relaxed">
                                    {{ $selectedCatatan->rencana_tindak_lanjut }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button type="button" wire:click="closeCatatanModal" class="px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
