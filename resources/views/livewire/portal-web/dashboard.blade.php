<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Dashboard Portal Web</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</p>
            </div>
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-violet-50 text-violet-700 border border-violet-200 rounded-xl text-sm font-bold hover:bg-violet-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Lihat Web Publik
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistik Konten Web --}}
    <div>
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 px-0.5">Konten Web Sekolah</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                $stats = [
                    ['label' => 'Berita', 'count' => $totalBerita, 'route' => 'portal-web.artikel', 'color' => 'blue', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
                    ['label' => 'Pengumuman', 'count' => $totalPengumuman, 'route' => 'portal-web.artikel', 'color' => 'amber', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
                    ['label' => 'Prestasi', 'count' => $totalPrestasi, 'route' => 'portal-web.prestasi', 'color' => 'yellow', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    ['label' => 'Galeri Foto', 'count' => $totalGaleri, 'route' => 'portal-web.galeri', 'color' => 'violet', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['label' => 'Alumni', 'count' => $totalAlumni, 'route' => 'portal-web.alumni', 'color' => 'indigo', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
                    ['label' => 'Pelayanan', 'count' => $totalPelayanan, 'route' => 'portal-web.pelayanan', 'color' => 'emerald', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ];
            @endphp

            @foreach($stats as $stat)
            <a href="{{ route($stat['route']) }}"
               class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 hover:shadow-md hover:-translate-y-0.5 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-{{ $stat['color'] }}-50 flex items-center justify-center mb-3 group-hover:bg-{{ $stat['color'] }}-100 transition-colors">
                    <svg class="w-5 h-5 text-{{ $stat['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">{{ $stat['count'] }}</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">{{ $stat['label'] }}</div>
            </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Artikel Terbaru --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-extrabold text-slate-900">Artikel Terbaru</h3>
                <a href="{{ route('portal-web.artikel') }}" class="text-xs text-violet-600 hover:text-violet-700 font-bold">Lihat Semua →</a>
            </div>
            @forelse($artikelTerbaru as $item)
            <div class="flex items-start gap-3 py-3 border-b border-slate-100 last:border-0">
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wide mt-0.5 flex-shrink-0
                    {{ $item->tipe === 'berita' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $item->tipe }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $item->judul }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $item->created_at?->diffForHumans() }}</p>
                </div>
                <a href="{{ route('beranda.artikel', $item->slug) }}" target="_blank" class="text-slate-400 hover:text-violet-600 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
            @empty
            <p class="text-sm text-slate-400 py-4 text-center">Belum ada artikel yang dipublikasikan.</p>
            @endforelse
        </div>

        {{-- Presensi Hari Ini --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-extrabold text-slate-900 mb-4">Presensi Hari Ini</h3>
            @if(!empty($presensiHariIni))
                @php
                    $presensiItems = [
                        ['label' => 'Hadir', 'count' => $presensiHariIni['hadir'], 'color' => 'emerald'],
                        ['label' => 'Terlambat', 'count' => $presensiHariIni['telat'], 'color' => 'amber'],
                        ['label' => 'Sakit', 'count' => $presensiHariIni['sakit'], 'color' => 'sky'],
                        ['label' => 'Izin', 'count' => $presensiHariIni['izin'], 'color' => 'indigo'],
                        ['label' => 'Alpa', 'count' => $presensiHariIni['alpa'], 'color' => 'red'],
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach($presensiItems as $p)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-{{ $p['color'] }}-500"></span>
                            <span class="text-sm text-slate-600">{{ $p['label'] }}</span>
                        </div>
                        <span class="font-extrabold text-slate-900 text-sm">{{ $p['count'] }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 text-center py-4">Tidak ada data presensi.</p>
            @endif
        </div>
    </div>
</div>
