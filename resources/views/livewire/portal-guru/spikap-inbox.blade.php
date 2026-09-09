<div class="space-y-6 pb-12">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-rose-100 text-rose-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Inbox Laporan SPIKAP</h1>
                    <p class="text-slate-500 text-sm">Sistem Pelaporan dan Penanganan Kasus Perundungan Siswa</p>
                </div>
            </div>
        </div>

        @php
            $canViewAnalytics = Auth::user()?->can('spikap.view_analytics') 
                || Auth::user()?->hasAnyRole(['super_admin', 'spikap_admin', 'spikap_kepala_sekolah', 'spikap_guru_bk'])
                || Auth::user()?->isGuruBk()
                || Auth::user()?->isKepalaSekolah();
        @endphp
        @if($canViewAnalytics)
            <div class="flex items-center gap-2">
                <a href="{{ route('portal-guru.spikap.analitik') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-md shadow-indigo-600/20 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Dashboard Analitik
                </a>
            </div>
        @endif
    </div>

    {{-- ── Kartu Ringkasan Metrik ───────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Laporan</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $totalCount }}</p>
            </div>
        </div>

        {{-- Laporan Darurat --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border {{ $daruratCount > 0 ? 'border-rose-300 ring-2 ring-rose-500/20 bg-rose-50/20' : 'border-slate-200/80' }} shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $daruratCount > 0 ? 'bg-rose-500 text-white animate-pulse' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Laporan Darurat</p>
                <p class="text-2xl font-extrabold text-rose-700 mt-0.5">{{ $daruratCount }}</p>
            </div>
        </div>

        {{-- Perlu Tindak Lanjut --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Perlu Penanganan</p>
                <p class="text-2xl font-extrabold text-amber-700 mt-0.5">{{ $aktifCount }}</p>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Selesai</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $selesaiCount }}</p>
            </div>
        </div>
    </div>

    {{-- ── Filter & Search Toolbar ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Search --}}
            <div class="lg:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Cari siswa, NISN, lokasi, atau kata kunci..."
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-rose-500 focus:ring-rose-500 transition-colors">
                </div>
            </div>

            {{-- Filter Status --}}
            <div>
                <select wire:model.live="filterStatus"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-rose-500 focus:ring-rose-500 transition-colors">
                    <option value="">Semua Status</option>
                    <option value="diterima">Diterima</option>
                    <option value="dalam_investigasi">Dalam Investigasi</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>

            {{-- Filter Sifat --}}
            <div>
                <select wire:model.live="filterSifat"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-rose-500 focus:ring-rose-500 transition-colors">
                    <option value="">Semua Sifat</option>
                    <option value="darurat">🚨 Darurat</option>
                    <option value="biasa">Biasa</option>
                </select>
            </div>

            {{-- Filter Jenis --}}
            <div>
                <select wire:model.live="filterJenis"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-rose-500 focus:ring-rose-500 transition-colors">
                    <option value="">Semua Jenis Perundungan</option>
                    <option value="fisik">Fisik</option>
                    <option value="verbal">Verbal</option>
                    <option value="sosial">Sosial</option>
                    <option value="digital">Digital / Cyber</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
        </div>

        @if($search || $filterStatus || $filterSifat || $filterJenis)
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Filter aktif diterapkan</span>
                <button wire:click="resetFilters" class="text-rose-600 hover:text-rose-700 font-semibold inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset Filter
                </button>
            </div>
        @endif
    </div>

    {{-- ── Tabel / Daftar Laporan ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider font-semibold border-b border-slate-200/60">
                    <tr>
                        <th class="px-6 py-4">Siswa Pelapor</th>
                        <th class="px-6 py-4">Sifat & Jenis</th>
                        <th class="px-6 py-4">Ringkasan Kejadian</th>
                        <th class="px-6 py-4">Waktu Lapor</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($laporan as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $item->sifat_laporan === 'darurat' && $item->status !== 'selesai' ? 'bg-rose-50/30' : '' }}">
                            {{-- Siswa Pelapor --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                                        <img src="{{ $item->siswa->avatar_url }}" alt="{{ $item->siswa->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 leading-snug">{{ $item->siswa->name }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            NISN: {{ $item->siswa->nisn }}
                                            @if($item->siswa->enrollmentAktif?->kelas)
                                                • Kelas {{ $item->siswa->enrollmentAktif->kelas->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Sifat & Jenis --}}
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div>
                                        @if($item->sifat_laporan === 'darurat')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                                🚨 Darurat
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                Biasa
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-semibold text-slate-700">
                                        {{ $item->label_jenis }}
                                    </div>
                                    @if($item->tujuan_penerima)
                                        <div class="text-[11px] text-slate-400">
                                            Tujuan: {{ $item->tujuan_penerima === 'guru_bk' ? 'Guru BK' : 'Wali Kelas' }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Ringkasan Kejadian --}}
                            <td class="px-6 py-4 max-w-xs sm:max-w-sm">
                                <p class="text-xs text-slate-700 line-clamp-2 leading-relaxed">
                                    {{ $item->uraian_kejadian }}
                                </p>
                                <div class="flex items-center gap-3 mt-1.5 text-[11px] text-slate-400">
                                    @if($item->lokasi_kejadian)
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            {{ $item->lokasi_kejadian }}
                                        </span>
                                    @endif
                                    @if($item->lampiran->count())
                                        <span class="inline-flex items-center gap-1 text-indigo-600 font-medium">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            {{ $item->lampiran->count() }} Bukti
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Waktu Lapor --}}
                            <td class="px-6 py-4 text-xs whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $item->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $item->created_at->format('H:i') }} ({{ $item->created_at->diffForHumans() }})</div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $badge = match($item->status) {
                                        'diterima'          => ['bg-amber-50 text-amber-700 border-amber-200', '⏳ Diterima'],
                                        'dalam_investigasi' => ['bg-blue-50 text-blue-700 border-blue-200',    '🔍 Investigasi'],
                                        'selesai'           => ['bg-emerald-50 text-emerald-700 border-emerald-200', '✅ Selesai'],
                                        default             => ['bg-slate-100 text-slate-600 border-slate-200', $item->status],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                                @if($item->lastHandledBy)
                                    <div class="text-[10px] text-slate-400 mt-1 max-w-[120px] truncate" title="Ditangani: {{ $item->lastHandledBy->name }}">
                                        Oleh: {{ $item->lastHandledBy->name }}
                                    </div>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('portal-guru.spikap.detail', $item->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-700 text-xs font-semibold rounded-xl transition-all shadow-sm">
                                    <span>Tindak Lanjut</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <p class="font-semibold text-slate-600">Tidak ada laporan ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Belum ada laporan SPIKAP yang sesuai dengan kriteria filter saat ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($laporan->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $laporan->links() }}
            </div>
        @endif
    </div>

</div>
