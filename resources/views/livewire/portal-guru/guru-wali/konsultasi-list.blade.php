<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span>Konsultasi Dua Arah — Siswa & Guru Wali</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Permintaan Konsultasi Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelompok: <strong>{{ $kelompok->nama_kelompok }}</strong> &bull; Respon usulan bimbingan dan pertanyaan dari peserta didik binaan.
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

    <!-- Tabs Nav Bar -->
    <div class="flex flex-wrap items-center gap-2 p-1.5 rounded-2xl bg-slate-200/70 dark:bg-slate-800">
        <button type="button" 
                wire:click="setTab('menunggu')" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'menunggu' ? 'bg-white text-brand-primary shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Menunggu Konfirmasi</span>
            @if($countMenunggu > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-500 text-white font-black animate-pulse">
                    {{ $countMenunggu }}
                </span>
            @endif
        </button>

        <button type="button" 
                wire:click="setTab('dijadwalkan')" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'dijadwalkan' ? 'bg-white text-brand-primary shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Dijadwalkan</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'dijadwalkan' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-300 text-slate-700' }}">
                {{ $countDijadwalkan }}
            </span>
        </button>

        <button type="button" 
                wire:click="setTab('selesai')" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'selesai' ? 'bg-white text-brand-primary shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Selesai / Jurnal</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'selesai' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-300 text-slate-700' }}">
                {{ $countSelesai }}
            </span>
        </button>

        <button type="button" 
                wire:click="setTab('ditolak')" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'ditolak' ? 'bg-white text-brand-primary shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Ditolak</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'ditolak' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-300 text-slate-700' }}">
                {{ $countDitolak }}
            </span>
        </button>

        <button type="button" 
                wire:click="setTab('semua')" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'semua' ? 'bg-white text-brand-primary shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Semua</span>
        </button>
    </div>

    <!-- Filter Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-72">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Cari siswa atau topik..." 
                   class="w-full bg-white rounded-2xl border-slate-200 text-xs py-2.5 pl-3.5 pr-9 shadow-xs focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
        </div>

        <select wire:model.live="kategori" 
                class="w-full sm:w-56 bg-white rounded-2xl border-slate-200 text-xs py-2.5 px-3.5 shadow-xs focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
            <option value="">Semua Kategori</option>
            <option value="Akademik">Akademik</option>
            <option value="Karakter & Kedisiplinan">Karakter & Disiplin</option>
            <option value="Minat & Bakat / Ekskul">Minat & Bakat</option>
            <option value="Sosial & Psikologis">Sosial & Psikologis</option>
        </select>
    </div>

    <!-- Consultation Cards List -->
    <div class="space-y-4">
        @if($konsultasis->count() > 0)
            @foreach($konsultasis as $item)
                <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs hover:shadow-md transition-all">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <!-- Left: Info Siswa & Topik -->
                        <div class="space-y-3 flex-1 min-w-0">
                            <!-- Siswa & Timestamp Header -->
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-brand-primary/10 text-brand-primary font-black flex items-center justify-center shrink-0 text-sm">
                                    {{ strtoupper(substr($item->siswa?->name ?? 'S', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-sm text-slate-900 truncate">{{ $item->siswa?->name }}</h3>
                                        <span class="text-[11px] text-slate-400">&bull; {{ $item->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500">
                                        NISN: {{ $item->siswa?->nisn ?? '-' }} &bull; Kelas {{ $item->siswa?->enrollmentAktif?->kelas?->name ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Badges (Kategori, Mode, Status) -->
                            <div class="flex flex-wrap items-center gap-2">
                                @php
                                    $catColors = [
                                        'Akademik'                => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Karakter & Kedisiplinan' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'Minat & Bakat / Ekskul'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Sosial & Psikologis'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    ];
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $catColors[$item->kategori_pendampingan] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $item->kategori_pendampingan }}
                                </span>

                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 inline-flex items-center gap-1">
                                    @if($item->mode_konsultasi === 'Tatap Muka')
                                        <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @else
                                        <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    @endif
                                    <span>Mode: {{ $item->mode_konsultasi }}</span>
                                </span>

                                @if($item->status_pengajuan === 'Menunggu Konfirmasi')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Konfirmasi
                                    </span>
                                @elseif($item->status_pengajuan === 'Dijadwalkan')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Dijadwalkan
                                    </span>
                                @elseif($item->status_pengajuan === 'Dikonversi ke Jurnal')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                        Dikonversi ke Jurnal
                                    </span>
                                @elseif($item->status_pengajuan === 'Selesai')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @endif
                            </div>

                            <!-- Topik & Isi Pesan Siswa -->
                            <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200/60">
                                <h4 class="font-bold text-xs text-slate-900 mb-1">
                                    {{ $item->topik_konsultasi }}
                                </h4>
                                <p class="text-xs text-slate-600 whitespace-pre-line leading-relaxed">
                                    {{ $item->detail_permasalahan }}
                                </p>
                            </div>

                            <!-- Waktu Usulan / Jadwal Pasti -->
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                                @if($item->usulan_tanggal_waktu)
                                    <div>
                                        <span class="font-semibold text-slate-700">Usulan Siswa:</span>
                                        <span>{{ \Carbon\Carbon::parse($item->usulan_tanggal_waktu)->translatedFormat('d M Y, H:i') }} WIB</span>
                                    </div>
                                @endif

                                @if($item->jadwal_pasti)
                                    <div class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 font-semibold">
                                        <span>Jadwal Pasti: </span>
                                        <strong>{{ \Carbon\Carbon::parse($item->jadwal_pasti)->translatedFormat('l, d M Y, H:i') }} WIB</strong>
                                    </div>
                                @endif
                            </div>

                            <!-- Tanggapan Guru / Alasan Penolakan -->
                            @if($item->tanggapan_guru)
                                <div class="bg-brand-primary/5 rounded-2xl p-3 border border-brand-primary/20 text-xs">
                                    <span class="font-bold text-brand-primary block mb-0.5">Tanggapan Anda:</span>
                                    <p class="text-slate-700 whitespace-pre-line">{{ $item->tanggapan_guru }}</p>
                                </div>
                            @endif

                            @if($item->alasan_penolakan)
                                <div class="bg-rose-50 rounded-2xl p-3 border border-rose-200 text-xs">
                                    <span class="font-bold text-rose-700 block mb-0.5">Alasan Penolakan:</span>
                                    <p class="text-slate-700 whitespace-pre-line">{{ $item->alasan_penolakan }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Action Buttons -->
                        <div class="flex md:flex-col items-center gap-2 shrink-0 self-end md:self-start">
                            @if($item->status_pengajuan === 'Menunggu Konfirmasi')
                                @if($item->mode_konsultasi === 'Tatap Muka')
                                    <button type="button" 
                                            wire:click="openScheduleModal('{{ $item->id }}')" 
                                            class="w-full px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                        Jadwalkan Pertemuan
                                    </button>
                                @else
                                    <button type="button" 
                                            wire:click="openReplyModal('{{ $item->id }}')" 
                                            class="w-full px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                        Balas Pesan Siswa
                                    </button>
                                @endif

                                <button type="button" 
                                        wire:click="openRejectModal('{{ $item->id }}')" 
                                        class="w-full px-4 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition-all text-center">
                                    Tolak Permintaan
                                </button>
                            @elseif($item->status_pengajuan === 'Dijadwalkan')
                                <!-- Konversi ke Jurnal -->
                                <a href="{{ route('portal-guru.guru-wali.jurnal.create', ['konsultasi_id' => $item->id]) }}" 
                                   class="w-full px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                    Catat ke Jurnal
                                </a>

                                <button type="button" 
                                        wire:click="openScheduleModal('{{ $item->id }}')" 
                                        class="w-full px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-all text-center">
                                    Ubah Jadwal
                                </button>

                                <button type="button" 
                                        wire:click="markCompleted('{{ $item->id }}')" 
                                        class="w-full px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-all text-center">
                                    Tandai Selesai
                                </button>
                            @else
                                <button type="button" 
                                        wire:click="openDetail('{{ $item->id }}')" 
                                        class="w-full px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all text-center">
                                    Lihat Riwayat
                                </button>
                            @endif
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
</div>
