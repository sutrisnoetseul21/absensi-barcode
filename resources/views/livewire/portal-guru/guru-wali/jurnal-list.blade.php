<div class="space-y-6" x-data="{ showModalPilihJurnal: false }">
    <!-- Header Page & Metrics -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Buku Catatan Pendampingan Guru Wali</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Jurnal Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelompok: <strong>{{ $kelompok->nama_kelompok }}</strong> &bull; Dokumentasi berkala pembimbingan peserta didik.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" 
                    @click="showModalPilihJurnal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-md shadow-brand-primary/20 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                </svg>
                <span>Catat Jurnal Baru</span>
            </button>
        </div>
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

    <!-- Mini Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Total Sesi Bimbingan</span>
                <span class="text-xl font-black text-slate-800 mt-0.5">{{ $totalAll }} <span class="text-xs font-normal text-slate-400">kali</span></span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Sesi Bulan Ini</span>
                <span class="text-xl font-black text-emerald-600 mt-0.5">{{ $totalBulanIni }} <span class="text-xs font-normal text-slate-400">kali</span></span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Dalam Pemantauan / Lanjutan</span>
                <span class="text-xl font-black text-amber-600 mt-0.5">{{ $totalPemantauan }} <span class="text-xs font-normal text-slate-400">kasus</span></span>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Kata Kunci</label>
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Nama siswa, NISN, atau isi uraian..." 
                           class="w-full bg-slate-50/50 rounded-2xl border-slate-200 text-xs py-2 pl-3.5 pr-8 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Filter Siswa -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Siswa</label>
                <select wire:model.live="studentId" 
                        class="w-full bg-slate-50/50 rounded-2xl border-slate-200 text-xs py-2 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                    <option value="">Semua Siswa</option>
                    @foreach($anggotaList as $anggota)
                        <option value="{{ $anggota->student_id }}">{{ $anggota->siswa?->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kategori -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                <select wire:model.live="kategori" 
                        class="w-full bg-slate-50/50 rounded-2xl border-slate-200 text-xs py-2 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                    <option value="">Semua Kategori</option>
                    <option value="Akademik">Akademik</option>
                    <option value="Karakter & Kedisiplinan">Karakter & Disiplin</option>
                    <option value="Minat & Bakat / Ekskul">Minat & Bakat</option>
                    <option value="Sosial & Psikologis">Sosial & Psikologis</option>
                </select>
            </div>

            <!-- Filter Bulan -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                <input type="month" 
                       wire:model.live="bulan" 
                       class="w-full bg-slate-50/50 rounded-2xl border-slate-200 text-xs py-2 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
            </div>
        </div>

        @if($search || $studentId || $kategori || $statusSesi || $bulan)
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs text-slate-500">
                <span>Filter aktif diterapkan.</span>
                <button type="button" 
                        wire:click="resetFilters" 
                        class="text-brand-primary font-bold hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset Semua Filter</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Journal Table / Cards -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($jurnals->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">Waktu Sesi</th>
                            <th class="py-3.5 px-4">Siswa Binaan</th>
                            <th class="py-3.5 px-4">Kategori & Jenis</th>
                            <th class="py-3.5 px-4">Uraian Ringkas</th>
                            <th class="py-3.5 px-4">Status & Rujukan</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($jurnals as $jurnal)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Waktu Sesi -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 block">
                                        {{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->format('H:i') }} WIB
                                    </span>
                                </td>

                                <!-- Siswa Binaan -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-brand-primary/10 text-brand-primary font-bold flex items-center justify-center shrink-0 text-[11px]">
                                            {{ strtoupper(substr($jurnal->siswa?->name ?? 'S', 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block leading-tight">{{ $jurnal->siswa?->name }}</span>
                                            <span class="text-[11px] text-slate-500">
                                                Kelas {{ $jurnal->siswa?->enrollmentAktif?->kelas?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kategori & Jenis -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $catColors = [
                                            'Akademik'                => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Karakter & Kedisiplinan' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'Minat & Bakat / Ekskul'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'Sosial & Psikologis'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $catColors[$jurnal->kategori_pendampingan] ?? 'bg-slate-100 text-slate-700' }} mb-1">
                                        {{ $jurnal->kategori_pendampingan }}
                                    </span>
                                    <span class="block text-[11px] text-slate-400">
                                        {{ $jurnal->jenis_pendampingan }}
                                    </span>
                                </td>

                                <!-- Uraian Ringkas -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-slate-700 line-clamp-2 leading-relaxed">
                                        {{ $jurnal->uraian_pembahasan }}
                                    </p>
                                    @if($jurnal->rencana_tindak_lanjut)
                                        <span class="text-[10px] text-brand-primary font-medium block mt-0.5 truncate">
                                            RTL: {{ $jurnal->rencana_tindak_lanjut }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Status & Rujukan -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        @if($jurnal->status_sesi === 'Tuntas / Selesai')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Tuntas
                                            </span>
                                        @elseif($jurnal->status_sesi === 'Dalam Pemantauan')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Pemantauan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                                                Lanjutan
                                            </span>
                                        @endif

                                        <div class="text-[11px] text-slate-500">
                                            Rujukan: <span class="font-semibold text-slate-700">{{ $jurnal->rujukan_kolaborasi }}</span>
                                        </div>

                                        @if($jurnal->is_public_note)
                                            <span class="inline-flex items-center gap-1 text-[10px] text-slate-500" title="Dibagikan ke Siswa">
                                                <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                <span>Publik Siswa</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] text-slate-400" title="Catatan Privat Guru">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span>Privat Guru</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Detail Button -->
                                        <button type="button" 
                                                wire:click="openDetail('{{ $jurnal->id }}')" 
                                                title="Lihat Detail Lengkap"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-brand-primary hover:bg-brand-primary/10 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        <!-- Edit Button -->
                                        <a href="{{ route('portal-guru.guru-wali.jurnal.edit', $jurnal->id) }}" 
                                           title="Edit Catatan"
                                           class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Delete Button -->
                                        <button type="button" 
                                                wire:click="confirmDelete('{{ $jurnal->id }}')" 
                                                title="Hapus Jurnal"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-slate-100">
                {{ $jurnals->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum ada catatan jurnal</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    Catatan pendampingan berkala yang Anda buat untuk siswa binaan akan terdata dan tersusun rapi di sini.
                </p>
                <div class="mt-4">
                    <a href="{{ route('portal-guru.guru-wali.jurnal.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                        </svg>
                        <span>Buat Catatan Sekarang</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- MODAL DETAIL JURNAL -->
    @if($showDetailModal && $selectedJurnal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Detail Sesi Pendampingan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ \Carbon\Carbon::parse($selectedJurnal->tanggal_waktu)->translatedFormat('l, d F Y \p\u\k\u\l H:i') }} WIB
                        </p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <!-- Siswa Info -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary font-bold flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($selectedJurnal->siswa?->name ?? 'S', 0, 2)) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $selectedJurnal->siswa?->name }}</h4>
                            <p class="text-slate-500">
                                NISN: {{ $selectedJurnal->siswa?->nisn ?? '-' }} &bull; Kelas: {{ $selectedJurnal->siswa?->enrollmentAktif?->kelas?->name ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Kategori & Jenis Tags -->
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                            {{ $selectedJurnal->kategori_pendampingan }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                            Jenis: {{ $selectedJurnal->jenis_pendampingan }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                            Status: {{ $selectedJurnal->status_sesi }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                            Rujukan: {{ $selectedJurnal->rujukan_kolaborasi }}
                        </span>
                    </div>

                    <!-- Uraian Pembahasan -->
                    <div>
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block mb-1">Uraian Pembahasan & Temuan:</span>
                        <div class="p-3.5 rounded-2xl bg-slate-50 text-slate-800 whitespace-pre-line leading-relaxed border border-slate-200/60">
                            {{ $selectedJurnal->uraian_pembahasan }}
                        </div>
                    </div>

                    <!-- Rencana Tindak Lanjut -->
                    @if($selectedJurnal->rencana_tindak_lanjut)
                        <div>
                            <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block mb-1">Rencana Tindak Lanjut (RTL):</span>
                            <div class="p-3.5 rounded-2xl bg-brand-primary/5 text-slate-800 whitespace-pre-line leading-relaxed border border-brand-primary/20">
                                {{ $selectedJurnal->rencana_tindak_lanjut }}
                            </div>
                        </div>
                    @endif

                    <!-- Privasi -->
                    <div class="flex items-center gap-2 pt-2 text-slate-500">
                        @if($selectedJurnal->is_public_note)
                            <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Catatan dibagikan kepada siswa (terlihat di Portal Siswa).</span>
                        @else
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Catatan bersifat privat (hanya terlihat oleh Guru Wali).</span>
                        @endif
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
                    <a href="{{ route('portal-guru.guru-wali.jurnal.edit', $selectedJurnal->id) }}" 
                       class="px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs transition-all">
                        Edit Catatan Ini
                    </a>
                    <button type="button" wire:click="closeDetail" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DELETE CONFIRMATION -->
    @if($showDeleteModal && $jurnalToDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h4 class="text-base font-black text-slate-900">Hapus Jurnal Pendampingan?</h4>
                <p class="text-xs text-slate-500 mt-1">
                    Catatan pendampingan siswa <strong>{{ $jurnalToDelete->siswa?->name }}</strong> tanggal {{ \Carbon\Carbon::parse($jurnalToDelete->tanggal_waktu)->format('d/m/Y') }} akan dihapus.
                </p>

                <div class="flex items-center justify-center gap-2.5 mt-5">
                    <button type="button" 
                            wire:click="cancelDelete" 
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100 transition-all">
                        Batal
                    </button>
                    <button type="button" 
                            wire:click="deleteJurnal" 
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition-all">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL PILIH TIPE JURNAL (INDIVIDU ATAU KELOMPOK) -->
    <div x-show="showModalPilihJurnal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-pilih-jurnal-title" 
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop with transition -->
        <div x-show="showModalPilihJurnal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showModalPilihJurnal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showModalPilihJurnal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                
                <!-- Modal Header -->
                <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-start justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-primary/10 text-brand-primary text-[11px] font-bold mb-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Pencatatan Jurnal Siswa</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900" id="modal-pilih-jurnal-title">
                            Pilih Bentuk Bimbingan
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pilih format pencatatan jurnal yang ingin dilakukan:
                        </p>
                    </div>
                    <button type="button" 
                            @click="showModalPilihJurnal = false"
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Options Body -->
                <div class="p-6 space-y-3.5">
                    <!-- Opsi 1: Jurnal Individu -->
                    <a href="{{ route('portal-guru.guru-wali.jurnal.create') }}" 
                       class="group block p-4 rounded-2xl border-2 border-slate-200 hover:border-brand-primary hover:bg-brand-primary/[0.02] transition-all relative overflow-hidden">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0 group-hover:bg-brand-primary group-hover:text-white transition-all shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-black text-slate-900 group-hover:text-brand-primary transition-colors">
                                        Jurnal Individu
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 group-hover:bg-brand-primary/10 group-hover:text-brand-primary transition-colors">
                                        1 Siswa
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Pencatatan sesi bimbingan personal, konseling mendalam, atau penanganan kasus khusus untuk 1 orang siswa secara spesifik.
                                </p>
                            </div>
                            <div class="shrink-0 self-center text-slate-300 group-hover:text-brand-primary group-hover:translate-x-0.5 transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Opsi 2: Bimbingan Kelompok (Input Massal) -->
                    <a href="{{ route('portal-guru.guru-wali.jurnal.kelompok') }}" 
                       class="group block p-4 rounded-2xl border-2 border-slate-200 hover:border-brand-primary hover:bg-brand-primary/[0.02] transition-all relative overflow-hidden">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">
                                        Bimbingan Kelompok (Input Massal)
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                        Tabel Terpadu
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Catat sesi pembinaan bersama satu kelompok sekaligus dalam tabel terpadu mirip absensi massal. Cepat & otomatis tersimpan untuk seluruh siswa yang dipilih.
                                </p>
                            </div>
                            <div class="shrink-0 self-center text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                    <button type="button" 
                            @click="showModalPilihJurnal = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
