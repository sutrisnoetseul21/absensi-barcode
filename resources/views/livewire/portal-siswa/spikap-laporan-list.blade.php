@php
    $spikapSetting = \App\Models\SpikapNotifSetting::instance();
@endphp

<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Laporan {{ $spikapSetting->getNamaAplikasi() }}</h1>
            <p class="text-slate-500 text-sm mt-0.5">Pantau alur dan perkembangan status laporan {{ strtolower($spikapSetting->getSubJudul()) }} kamu secara transparan.</p>
        </div>
        <a href="{{ route('portal-siswa.spikap.form') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 text-white text-sm font-bold rounded-xl shadow-md shadow-rose-500/25 hover:bg-rose-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Laporan Baru
        </a>
    </div>

    {{-- ── Daftar Laporan ──────────────────────────────────────────────── --}}
    @forelse($laporan as $item)
        <div class="bg-white rounded-2xl border shadow-sm overflow-hidden transition-all
                    {{ $item->sifat_laporan === 'darurat' ? 'border-rose-200' : 'border-slate-200/80' }}">

            {{-- Stripe atas untuk laporan darurat --}}
            @if($item->sifat_laporan === 'darurat')
                <div class="h-1.5 bg-gradient-to-r from-rose-500 to-orange-400"></div>
            @endif

            <div class="p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                    {{-- Info kiri --}}
                    <div class="space-y-2.5 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Badge sifat --}}
                            @if($item->sifat_laporan === 'darurat')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                    🚨 Darurat
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                    Biasa
                                </span>
                            @endif

                            {{-- Badge jenis --}}
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                                {{ $item->label_jenis }}
                            </span>

                            {{-- Badge tujuan (jika biasa) --}}
                            @if($item->tujuan_penerima)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                    → {{ $item->tujuan_penerima === 'guru_bk' ? 'Guru BK' : 'Wali Kelas' }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200/60">
                                    → Wali Kelas + Kepala Sekolah
                                </span>
                            @endif
                        </div>

                        {{-- Uraian (preview atau full) --}}
                        <p class="text-sm text-slate-700 leading-relaxed {{ $selectedLaporanId === $item->id ? 'whitespace-pre-line' : 'line-clamp-2' }}">
                            {{ $item->uraian_kejadian }}
                        </p>

                        {{-- Meta --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Dilaporkan {{ $item->created_at->diffForHumans() }} ({{ $item->created_at->translatedFormat('d M Y H:i') }})
                            </span>
                            @if($item->lokasi_kejadian)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $item->lokasi_kejadian }}
                                </span>
                            @endif
                            @if($item->lampiran->count())
                                <span class="flex items-center gap-1 text-indigo-600 font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    {{ $item->lampiran->count() }} Bukti Lampiran
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Status badge kanan + Tombol Toggle Detail --}}
                    <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                        @php
                            $statusConfig = match($item->status) {
                                'diterima'          => ['bg-amber-50 text-amber-700 border-amber-200',    '⏳', 'Diterima'],
                                'dalam_investigasi' => ['bg-blue-50 text-blue-700 border-blue-200',       '🔍', 'Dalam Investigasi'],
                                'selesai'           => ['bg-emerald-50 text-emerald-700 border-emerald-200', '✅', 'Selesai'],
                                default             => ['bg-slate-100 text-slate-600 border-slate-200',    '•',  $item->status],
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusConfig[0] }}">
                            {{ $statusConfig[1] }} {{ $statusConfig[2] }}
                        </span>

                        <button wire:click="toggleDetail({{ $item->id }})"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline">
                            <span>{{ $selectedLaporanId === $item->id ? 'Sembunyikan' : 'Tracking & Detail' }}</span>
                            <svg class="w-3.5 h-3.5 transition-transform {{ $selectedLaporanId === $item->id ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- ── Panel Tracking & Detail (Expandable) ──────────────────────── --}}
                @if($selectedLaporanId === $item->id)
                    <div class="mt-6 pt-6 border-t border-slate-200/70 space-y-6">

                        {{-- Info Waktu Kejadian Jika Ada --}}
                        @if($item->waktu_kejadian)
                            <div class="p-3 bg-slate-50 rounded-xl text-xs text-slate-600 flex items-center gap-2">
                                <span class="font-semibold text-slate-700">Waktu Kejadian yang Dilaporkan:</span>
                                <span>{{ $item->waktu_kejadian->translatedFormat('l, d F Y - H:i') }} WIB</span>
                            </div>
                        @endif

                        {{-- Bukti Lampiran --}}
                        @if($item->lampiran->count())
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">File Bukti Terlampir:</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($item->lampiran as $lampiran)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="p-1 rounded bg-slate-200 text-slate-700">
                                                    @if($lampiran->tipe_file === 'foto')
                                                        🖼️
                                                    @else
                                                        🎥
                                                    @endif
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-slate-800 truncate" title="{{ $lampiran->nama_asli }}">{{ $lampiran->nama_asli }}</p>
                                                    <p class="text-[10px] text-slate-400">{{ strtoupper($lampiran->tipe_file) }} • {{ $lampiran->ukuran_readable }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0 ml-2">
                                                <a href="{{ route('spikap.lampiran.show', $lampiran->id) }}" target="_blank"
                                                   class="px-2 py-1 bg-white border border-slate-200 hover:border-rose-300 text-slate-700 hover:text-rose-600 rounded-lg text-xs font-semibold">
                                                    Lihat
                                                </a>
                                                <a href="{{ route('spikap.lampiran.download', $lampiran->id) }}"
                                                   class="p-1 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 rounded-lg text-xs"
                                                   title="Download">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Alur Tracking Status (Timeline) --}}
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Perkembangan Penanganan Kasus:</h4>

                            <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                @forelse($item->logStatus->sortBy('created_at') as $log)
                                    <div class="relative">
                                        <div class="absolute -left-6 top-1 w-4 h-4 rounded-full bg-white border-2 border-slate-400 flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 rounded-full {{ $log->status_baru === 'selesai' ? 'bg-emerald-500' : ($log->status_baru === 'dalam_investigasi' ? 'bg-blue-500' : 'bg-amber-500') }}"></div>
                                        </div>

                                        <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-200/60 space-y-1">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="font-bold text-slate-800">
                                                    Status: {{ ucfirst(str_replace('_', ' ', $log->status_baru)) }}
                                                </span>
                                                <span class="text-[11px] text-slate-400">
                                                    {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                                                </span>
                                            </div>

                                            @if($log->catatan)
                                                <div class="text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-100 mt-1 leading-relaxed whitespace-pre-line">
                                                    {{ $log->catatan }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">Laporan baru dibuat dan sedang menunggu respon guru terkait.</p>
                                @endforelse
                            </div>
                        </div>

                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-12 text-center space-y-4">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-700">Belum ada laporan</p>
                <p class="text-sm text-slate-400 mt-1">Kamu belum pernah membuat laporan {{ $spikapSetting->getNamaAplikasi() }}. Jika ada kejadian yang perlu dilaporkan, klik tombol di atas.</p>
            </div>
        </div>
    @endforelse
</div>
