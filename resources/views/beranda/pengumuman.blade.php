<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Resmi — {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="Informasi penting, edaran resmi, dan pengumuman kegiatan {{ $sekolah?->school_name ?? 'Sekolah' }}.">
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
    
    {{-- Decorative Background Glow --}}
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <!-- Breadcrumb -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-4 shadow-sm">
            <a href="{{ route('beranda') }}" class="text-white hover:text-amber-300 transition-colors">Beranda</a>
            <span class="text-white/40">/</span>
            <span>Pengumuman</span>
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-white mb-3 tracking-tight">
            Pengumuman Sekolah
        </h1>
        <div class="w-24 h-1.5 bg-amber-400 mx-auto rounded-full mb-4 shadow-sm"></div>
        
        <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto leading-relaxed font-medium">
            Pusat informasi resmi, surat edaran, agenda penting, dan pengumuman resmi bagi siswa, guru, dan wali murid.
        </p>
    </div>
</div>

{{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
<div class="container mx-auto px-4 py-12 max-w-4xl flex-grow">
    
    {{-- Bar Pencarian & Filter --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 mb-8">
        <form action="{{ route('pengumuman.all') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari judul atau isi pengumuman..." 
                       class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-3 bg-brand-primary text-white font-bold text-sm rounded-2xl shadow-sm hover:opacity-90 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i>
                    <span>Cari</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('pengumuman.all') }}" class="px-4 py-3 bg-slate-100 text-slate-600 font-semibold text-sm rounded-2xl hover:bg-slate-200 transition-all flex items-center justify-center" title="Reset">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar Pengumuman --}}
    <div class="space-y-4">
        @forelse($pengumuman as $info)
            <div class="bg-white p-6 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 flex flex-col sm:flex-row gap-6 items-start sm:items-center group">
                
                {{-- Kotak Tanggal Elegan --}}
                <div class="flex-shrink-0 flex flex-col items-center justify-center text-white rounded-2xl w-20 h-20 sm:w-24 sm:h-24 shadow-md transform group-hover:scale-105 transition-transform"
                     style="background: linear-gradient(135deg, var(--color-brand-primary, #059669) 0%, var(--color-brand-secondary, #047857) 100%);">
                    <span class="text-2xl sm:text-3xl font-extrabold leading-none">
                        {{ ($info->published_at ?? $info->created_at)?->format('d') }}
                    </span>
                    <span class="text-[10px] sm:text-xs font-bold uppercase mt-1 tracking-wider opacity-90">
                        {{ ($info->published_at ?? $info->created_at)?->isoFormat('MMM YYYY') }}
                    </span>
                </div>

                {{-- Informasi Isi --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 mb-2">
                        <span class="flex items-center gap-1">
                            <i class="far fa-clock text-amber-500"></i>
                            {{ ($info->published_at ?? $info->created_at)?->format('H:i') }} WIB
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i class="far fa-user text-slate-400"></i> Sekolah
                        </span>
                    </div>

                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 group-hover:text-brand-primary transition-colors mb-2 leading-snug">
                        <a href="{{ route('beranda.artikel', $info->slug) }}">
                            {{ $info->judul }}
                        </a>
                    </h3>

                    <p class="text-xs sm:text-sm text-slate-500 line-clamp-2 leading-relaxed mb-4">
                        {{ Str::limit(strip_tags($info->konten), 160) }}
                    </p>

                    <a href="{{ route('beranda.artikel', $info->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-primary hover:underline group-hover:translate-x-1 transition-transform">
                        <span>Baca Detail Pengumuman</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

            </div>
        @empty
            <div class="py-16 px-4 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i class="fas fa-bullhorn text-2xl"></i>
                </div>
                <h4 class="text-base font-bold text-slate-700 mb-1">Belum Ada Pengumuman</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    @if(request('search'))
                        Tidak ditemukan pengumuman dengan kata kunci "{{ request('search') }}".
                    @else
                        Saat ini belum ada pengumuman baru dari pihak sekolah.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-10 flex justify-center">
        {{ $pengumuman->links() }}
    </div>

</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

</body>
</html>
