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

                                @if($item->status_pengajuan?->value === 'Menunggu Konfirmasi')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Konfirmasi
                                    </span>
                                @elseif($item->status_pengajuan?->value === 'Dijadwalkan')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Dijadwalkan
                                    </span>
                                @elseif($item->status_pengajuan?->value === 'Dikonversi ke Jurnal')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                        Dikonversi ke Jurnal
                                    </span>
                                @elseif($item->status_pengajuan?->value === 'Selesai')
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

                            <!-- Tanggapan Guru / Chat Thread Inline -->
                            @if($item->mode_konsultasi === 'Pesan Portal' && $item->pesan->count() > 0)
                                <div class="mt-4 pt-4 border-t border-slate-100 space-y-4 w-full">
                                    <div class="space-y-4 pr-2 max-h-60 overflow-y-auto custom-scrollbar">
                                        @foreach($item->pesan as $msg)
                                            <div class="flex {{ $msg->sender_type === 'guru' ? 'justify-end' : 'justify-start' }}">
                                                <div class="max-w-[85%] rounded-2xl p-3 {{ $msg->sender_type === 'guru' ? 'bg-brand-primary text-white rounded-tr-sm' : 'bg-slate-100 text-slate-800 rounded-tl-sm' }}">
                                                    @if($msg->sender_type === 'siswa')
                                                        <p class="text-[10px] font-extrabold text-slate-600 mb-1">{{ $item->siswa?->name }} (Siswa)</p>
                                                    @endif
                                                    <p class="text-xs leading-relaxed whitespace-pre-line">{{ $msg->pesan }}</p>
                                                    <p class="text-[10px] mt-1.5 text-right opacity-70">{{ $msg->created_at->format('H:i') }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(in_array($item->status_pengajuan?->value, ['Menunggu Konfirmasi', 'Dijadwalkan']))
                                        <div class="mt-3 flex gap-2">
                                            <input type="text" wire:model="pesanBaru" placeholder="Ketik balasan untuk siswa..." class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white transition-all">
                                            <button type="button" wire:click="kirimPesanInline('{{ $item->id }}')" class="px-4 py-2 bg-brand-primary hover:bg-brand-secondary text-white rounded-xl font-bold text-xs shadow-md transition-all cursor-pointer">
                                                Kirim
                                            </button>
                                        </div>
                                        @error('pesanBaru') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                                    @elseif(in_array($item->status_pengajuan?->value, ['Selesai', 'Dikonversi ke Jurnal']))
                                        <div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs text-center rounded-xl font-medium">
                                            Konsultasi ini telah ditandai Selesai. Ruang obrolan ditutup.
                                        </div>
                                    @endif
                                </div>
                            @elseif($item->tanggapan_guru)
                                <div class="bg-brand-primary/5 rounded-2xl p-3 border border-brand-primary/20 text-xs mt-2">
                                    <span class="font-bold text-brand-primary block mb-0.5">Tanggapan Anda:</span>
                                    <p class="text-slate-700 whitespace-pre-line line-clamp-3">{{ $item->tanggapan_guru }}</p>
                                </div>
                            @endif

                            @if($item->alasan_penolakan)
                                <div class="bg-rose-50 rounded-2xl p-3 border border-rose-200 text-xs">
                                    <span class="font-bold text-rose-700 block mb-0.5">Alasan Penolakan:</span>
                                    <p class="text-slate-700 whitespace-pre-line">{{ $item->alasan_penolakan }}</p>
                                </div>
                            @endif

                            <!-- Ulasan / Feedback Siswa -->
                            @if(in_array($item->status_pengajuan?->value, ['Selesai', 'Dikonversi ke Jurnal']) && $item->student_feedback_rating)
                                <div class="mt-3 bg-amber-50/70 rounded-2xl p-3 border border-amber-200">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-bold text-amber-700 text-xs">Refleksi & Ulasan Siswa:</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-black text-amber-500">⭐ {{ $item->student_feedback_rating }}/5</span>
                                            @if($item->student_feedback_emoji)
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-amber-200/50 text-amber-800">{{ $item->student_feedback_emoji->value }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($item->student_feedback_note)
                                        <p class="text-xs text-amber-900 mt-1.5 leading-relaxed italic border-l-2 border-amber-300 pl-2">"{{ $item->student_feedback_note }}"</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Right: Action Buttons -->
                        <div class="flex md:flex-col items-center gap-2 shrink-0 self-end md:self-start">
                            @if($item->status_pengajuan?->value === 'Menunggu Konfirmasi')
                                @if($item->mode_konsultasi === 'Tatap Muka')
                                    <button type="button" 
                                            wire:click="openScheduleModal('{{ $item->id }}')" 
                                            class="w-full px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                        Jadwalkan Pertemuan
                                    </button>
                                @else
                                    <button type="button" 
                                            wire:click="openDetail('{{ $item->id }}')" 
                                            class="w-full px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                        Buka Ruang Obrolan
                                    </button>
                                @endif

                                <button type="button" 
                                        wire:click="openRujukModal('{{ $item->id }}')" 
                                        class="w-full px-4 py-2 rounded-xl bg-orange-100 hover:bg-orange-200 text-orange-700 font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                    Rujuk ke BK
                                </button>

                                <button type="button" 
                                        wire:click="openRejectModal('{{ $item->id }}')" 
                                        class="w-full px-4 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition-all text-center">
                                    Tolak Permintaan
                                </button>
                            @elseif($item->status_pengajuan?->value === 'Dijadwalkan')
                                @if($item->mode_konsultasi === 'Pesan Portal')
                                    <button type="button" 
                                            wire:click="openDetail('{{ $item->id }}')" 
                                            class="w-full px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                        Buka Ruang Obrolan
                                    </button>
                                @else
                                    <button type="button" 
                                            wire:click="openScheduleModal('{{ $item->id }}')" 
                                            class="w-full px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-all text-center">
                                        Ubah Jadwal
                                    </button>
                                @endif

                                <button type="button" 
                                        wire:click="openRujukModal('{{ $item->id }}')" 
                                        class="w-full px-4 py-2 rounded-xl bg-orange-100 hover:bg-orange-200 text-orange-700 font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                    Rujuk ke BK
                                </button>

                                <!-- Konversi ke Jurnal -->
                                <a href="{{ route('portal-guru.guru-wali.jurnal.create', ['konsultasi_id' => $item->id]) }}" 
                                   class="w-full px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all text-center">
                                    Catat ke Jurnal
                                </a>

                                <button type="button" 
                                        wire:click="openBadgeModal('{{ $item->id }}')" 
                                        class="w-full px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-all text-center">
                                    Selesaikan & Beri Badge
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
                                {{ $selectedConsultation->mode_konsultasi === 'Pesan Portal' ? 'Ruang Obrolan' : 'Detail Konsultasi' }}
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
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold text-slate-600">Mode: {{ $selectedConsultation->mode_konsultasi }}</span>
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
