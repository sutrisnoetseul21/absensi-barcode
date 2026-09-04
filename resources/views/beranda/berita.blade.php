<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita & Artikel — {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="Kumpulan berita, artikel, dan informasi kegiatan terbaru seputar {{ $sekolah?->school_name ?? 'Sekolah' }}.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @if($sekolah)
        <style>
            :root {
                @if($sekolah->theme_primary) --color-brand-primary: {{ $sekolah->theme_primary }}; @endif
                @if($sekolah->theme_secondary) --color-brand-secondary: {{ $sekolah->theme_secondary }}; @endif
            }
        </style>
    @endif
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

{{-- ══════════════════ HERO SECTION ══════════════════ --}}
<div class="pt-32 pb-20 relative overflow-hidden text-white border-b-4 border-amber-400"
     style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">
    
    {{-- Decorative Background Elements --}}
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <!-- Breadcrumb -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-4 shadow-sm">
            <a href="{{ route('beranda') }}" class="text-white hover:text-amber-300 transition-colors">Beranda</a>
            <span class="text-white/40">/</span>
            <span>Berita & Artikel</span>
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-white mb-3 tracking-tight">
            Berita & Artikel Terkini
        </h1>
        <div class="w-24 h-1.5 bg-amber-400 mx-auto rounded-full mb-4 shadow-sm"></div>
        
        <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto leading-relaxed font-medium">
            Kabar terkini seputar dinamika belajar, aktivitas siswa, karya guru, dan inovasi pendidikan di {{ $sekolah?->school_name ?? 'Sekolah' }}.
        </p>
    </div>
</div>

{{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
<div class="container mx-auto px-4 py-12 max-w-7xl flex-grow">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        {{-- Kolom Kiri: Daftar Berita (8 Kolom) --}}
        <div class="lg:col-span-8">
            
            {{-- Header Hasil & Pencarian --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-4 border-b border-slate-200/80">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">
                        @if(request('search'))
                            Hasil Pencarian: <span class="text-brand-primary font-semibold">"{{ request('search') }}"</span>
                        @else
                            Semua Berita
                        @endif
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ $berita->total() }} berita terpublikasi</p>
                </div>

                @if(request('search'))
                    <a href="{{ route('berita.all') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-500 hover:text-rose-600 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-colors self-start sm:self-auto">
                        <i class="fas fa-times"></i> Reset Pencarian
                    </a>
                @endif
            </div>

            {{-- Grid Berita --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($berita as $item)
                    <article class="bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 overflow-hidden flex flex-col group">
                        
                        {{-- Thumbnail Cover --}}
                        <a href="{{ route('beranda.artikel', $item->slug) }}" class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
                            @if($item->thumbnail)
                                <img src="{{ asset('storage/' . $item->thumbnail) }}" 
                                     alt="{{ $item->judul }}" 
                                     onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-slate-300 bg-slate-100\'><i class=\'fas fa-newspaper text-4xl\'></i></div>'"
                                     class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-100">
                                    <i class="fas fa-newspaper text-4xl"></i>
                                </div>
                            @endif

                            {{-- Badge Tanggal --}}
                            <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-bold text-slate-700 shadow-md flex items-center gap-1.5">
                                <i class="far fa-calendar-alt text-amber-500"></i>
                                <span>{{ ($item->published_at ?? $item->created_at)?->isoFormat('D MMM YYYY') }}</span>
                            </div>
                        </a>

                        {{-- Konten Berita --}}
                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="font-bold text-slate-800 text-lg leading-snug group-hover:text-brand-primary transition-colors line-clamp-2 mb-2">
                                <a href="{{ route('beranda.artikel', $item->slug) }}">{{ $item->judul }}</a>
                            </h3>

                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3 mb-4 flex-grow">
                                {{ Str::limit(strip_tags($item->konten), 130) }}
                            </p>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <i class="far fa-user-circle text-slate-400"></i> Redaksi
                                </span>
                                <a href="{{ route('beranda.artikel', $item->slug) }}" class="text-brand-primary font-bold hover:underline flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                    Baca Selengkapnya <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                    </article>
                @empty
                    <div class="col-span-full py-16 px-4 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                            <i class="fas fa-newspaper text-2xl"></i>
                        </div>
                        <h4 class="text-base font-bold text-slate-700 mb-1">Belum Ada Berita</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            @if(request('search'))
                                Tidak ditemukan berita dengan kata kunci "{{ request('search') }}". Silakan coba kata kunci lain.
                            @else
                                Belum ada publikasi berita yang tersedia saat ini.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-10">
                {{ $berita->links() }}
            </div>

        </div>

        {{-- Kolom Kanan: Sidebar Widget (4 Kolom) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Widget Pencarian --}}
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                    <i class="fas fa-search text-brand-primary"></i> Cari Berita
                </h3>
                <form action="{{ route('berita.all') }}" method="GET" class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Ketik kata kunci..." 
                           class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    <button type="submit" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-primary transition-colors">
                        <i class="fas fa-search text-xs"></i>
                    </button>
                </form>
            </div>

            {{-- Widget Berita Terkini --}}
            @if(isset($recent_posts) && $recent_posts->count())
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                    <h3 class="font-bold text-slate-800 text-sm mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Berita Terkini</span>
                        <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                    </h3>
                    <div class="space-y-3.5">
                        @foreach($recent_posts as $recent)
                            <a href="{{ route('beranda.artikel', $recent->slug) }}" class="flex gap-3 group items-center">
                                <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-slate-100 relative">
                                    @if($recent->thumbnail)
                                        <img src="{{ asset('storage/' . $recent->thumbnail) }}" 
                                             alt="{{ $recent->judul }}" 
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                                            <i class="fas fa-newspaper text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-xs text-slate-800 group-hover:text-brand-primary leading-snug line-clamp-2 transition-colors">
                                        {{ $recent->judul }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400 mt-1 block">
                                        {{ ($recent->published_at ?? $recent->created_at)?->diffForHumans() }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Widget Informasi Sekolah --}}
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 text-white shadow-md">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-amber-300 uppercase tracking-wider bg-white/10 px-2.5 py-1 rounded-full mb-3">
                    <i class="fas fa-info-circle"></i> Info Publik
                </span>
                <h4 class="font-bold text-base mb-1">Pusat Informasi Sekolah</h4>
                <p class="text-xs text-white/70 leading-relaxed mb-4">
                    Jelajahi informasi resmi lainnya yang disediakan oleh {{ $sekolah?->school_name ?? 'sekolah' }}.
                </p>
                <div class="space-y-2 text-xs">
                    <a href="{{ route('pengumuman.all') }}" class="flex items-center justify-between p-3 rounded-xl bg-white/10 hover:bg-white/20 transition-all group font-semibold">
                        <span class="flex items-center gap-2"><i class="fas fa-bullhorn text-amber-300"></i> Pengumuman Sekolah</span>
                        <i class="fas fa-chevron-right text-[10px] text-white/50 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="{{ route('prestasi.all') }}" class="flex items-center justify-between p-3 rounded-xl bg-white/10 hover:bg-white/20 transition-all group font-semibold">
                        <span class="flex items-center gap-2"><i class="fas fa-trophy text-amber-300"></i> Prestasi & Kejuaraan</span>
                        <i class="fas fa-chevron-right text-[10px] text-white/50 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="{{ route('galeri.all') }}" class="flex items-center justify-between p-3 rounded-xl bg-white/10 hover:bg-white/20 transition-all group font-semibold">
                        <span class="flex items-center gap-2"><i class="fas fa-images text-amber-300"></i> Galeri Dokumentasi</span>
                        <i class="fas fa-chevron-right text-[10px] text-white/50 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

</body>
</html>
