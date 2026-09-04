<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Kegiatan — {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="Koleksi dokumentasi foto momen berharga dari berbagai aktivitas akademik dan kesiswaan di {{ $sekolah?->school_name ?? 'Sekolah' }}.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glightbox-clean .gslide-description { background: transparent !important; }
        .glightbox-clean .gdesc-inner { padding: 15px 0 !important; }
        .glightbox-clean .gslide-title {
            color: #ffffff !important;
            text-align: center !important;
            font-family: inherit !important;
            font-size: 1.1rem !important;
            font-weight: 700 !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.8) !important;
        }
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
    
    {{-- Decorative Background Glow --}}
    <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
        <i class="fas fa-images text-[220px] text-amber-300 transform rotate-12 translate-x-12 -translate-y-12"></i>
    </div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <!-- Breadcrumb -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-4 shadow-sm">
            <a href="{{ route('beranda') }}" class="text-white hover:text-amber-300 transition-colors">Beranda</a>
            <span class="text-white/40">/</span>
            <span>Galeri Foto</span>
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-white mb-3 tracking-tight">
            Dokumentasi Kegiatan
        </h1>
        <div class="w-24 h-1.5 bg-amber-400 mx-auto rounded-full mb-4 shadow-sm"></div>
        
        <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto leading-relaxed font-medium">
            Merekam memori dan momen penuh inspirasi dari kegiatan belajar, ekstrakurikuler, dan peristiwa penting di {{ $sekolah?->school_name ?? 'Sekolah' }}.
        </p>
    </div>
</div>

{{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
<div class="container mx-auto px-4 py-12 max-w-7xl flex-grow">
    
    {{-- Bar Filter & Pencarian --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-10 pb-4 border-b border-slate-200/80">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                @if(request('search'))
                    Hasil Pencarian: <span class="text-brand-primary">"{{ request('search') }}"</span>
                @else
                    Semua Foto Dokumentasi
                @endif
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ $galleries->total() }} foto dokumentasi</p>
        </div>

        <form action="{{ route('galeri.all') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari dokumentasi foto..." 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-brand-primary text-white text-xs font-bold rounded-xl shadow-sm hover:opacity-90 transition-all">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('galeri.all') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center" title="Reset">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Masonry Grid Layout --}}
    <div class="columns-1 sm:columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
        @forelse($galleries as $item)
            <a href="{{ asset('storage/' . $item->foto_path) }}" 
               class="glightbox block break-inside-avoid rounded-3xl overflow-hidden group relative cursor-pointer shadow-sm hover:shadow-2xl transition-all duration-500 bg-slate-100 border border-slate-100"
               data-title="{{ $item->judul ?? 'Dokumentasi Sekolah' }}">
                
                {{-- Foto Kegiatan --}}
                <img src="{{ asset('storage/' . $item->foto_path) }}" 
                     alt="{{ $item->judul ?? 'Foto Dokumentasi' }}"
                     onerror="this.parentElement.style.display='none'"
                     class="w-full h-auto transform group-hover:scale-105 transition-all duration-700">

                {{-- Overlay Informasi --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-5">
                    <span class="text-amber-300 text-[10px] font-extrabold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                        <i class="fas fa-camera"></i> Dokumentasi
                    </span>
                    <h4 class="text-white font-bold text-sm leading-snug line-clamp-2">
                        {{ $item->judul ?? 'Foto Kegiatan' }}
                    </h4>
                    @if($item->keterangan)
                        <p class="text-white/80 text-xs mt-1 line-clamp-2 leading-relaxed">
                            {{ $item->keterangan }}
                        </p>
                    @endif

                    {{-- Ikon Zoom Tengah --}}
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-0 group-hover:scale-100 transition-transform duration-300">
                        <span class="w-12 h-12 bg-white/25 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/40 shadow-lg">
                            <i class="fas fa-search-plus text-sm"></i>
                        </span>
                    </div>
                </div>

            </a>
        @empty
            <div class="col-span-full py-16 px-4 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200 w-full">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i class="fas fa-images text-2xl"></i>
                </div>
                <h4 class="text-base font-bold text-slate-700 mb-1">Galeri Foto Kosong</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    @if(request('search'))
                        Tidak ditemukan foto dengan kata kunci "{{ request('search') }}".
                    @else
                        Belum ada foto dokumentasi yang diunggah ke galeri.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-12 flex justify-center">
        {{ $galleries->links() }}
    </div>

</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof GLightbox !== 'undefined') {
            GLightbox({
                selector: '.glightbox',
                touchNavigation: true,
                loop: true,
                zoomable: true,
                openEffect: 'zoom',
                closeEffect: 'fade'
            });
        }
    });
</script>

</body>
</html>
