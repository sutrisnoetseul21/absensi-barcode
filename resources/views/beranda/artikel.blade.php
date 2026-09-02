<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $artikel->judul }} — {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="{{ $artikel->meta_description ?? Str::limit(strip_tags($artikel->konten), 160) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .prose-content img { max-width: 100%; border-radius: 1rem; margin: 1.5rem 0; }
        .prose-content p { line-height: 1.8; margin-bottom: 1.25rem; color: #334155; }
        .prose-content h2, .prose-content h3 { font-weight: 700; color: #1e293b; margin-top: 1.5rem; margin-bottom: 0.75rem; }
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
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-primary-light selection:text-brand-primary-dark min-h-screen flex flex-col">

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full flex-grow">
    
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-slate-400 mb-8">
        <a href="{{ route('beranda') }}" class="hover:text-brand-primary transition-colors">Beranda</a>
        <span>/</span>
        <span class="capitalize text-slate-500">{{ $artikel->tipe }}</span>
        <span>/</span>
        <span class="text-slate-700 font-semibold truncate max-w-xs">{{ $artikel->judul }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        {{-- Konten Utama (Kiri - 8 Kolom) --}}
        <div class="lg:col-span-8">
            <article class="bg-white p-6 sm:p-10 rounded-3xl shadow-sm border border-slate-100">
                
                {{-- Badge & Judul --}}
                <div class="mb-4">
                    <span class="inline-block bg-emerald-50 text-brand-primary border border-emerald-100 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                        {{ $artikel->tipe }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 leading-tight mb-6">
                    {{ $artikel->judul }}
                </h1>

                {{-- Meta Info --}}
                <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-xs sm:text-sm text-slate-400 mb-8 border-b border-slate-100 pb-6">
                    <span class="flex items-center gap-2">
                        <i class="far fa-calendar-alt text-amber-500"></i>
                        {{ ($artikel->published_at ?? $artikel->created_at)?->isoFormat('D MMMM YYYY') }}
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="far fa-folder text-amber-500"></i>
                        {{ ucfirst($artikel->tipe) }}
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="far fa-user text-amber-500"></i>
                        Admin Sekolah
                    </span>
                </div>

                {{-- Gambar Cover Utama --}}
                @if($artikel->thumbnail)
                    <div class="rounded-2xl overflow-hidden mb-8 shadow-sm bg-slate-100 max-h-[450px]">
                        <img src="{{ asset('storage/' . $artikel->thumbnail) }}" 
                             alt="{{ $artikel->judul }}" 
                             class="w-full h-full object-cover">
                    </div>
                @endif

                {{-- Isi Tulisan --}}
                <div class="prose max-w-none text-slate-700 prose-content text-base sm:text-lg">
                    {!! $artikel->konten !!}
                </div>

                {{-- Tombol Bagikan --}}
                <div class="mt-12 pt-8 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Bagikan tulisan ini:</h4>
                        <p class="text-xs text-slate-400">Sebarkan informasi ini kepada yang lain</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-all shadow-sm">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($artikel->judul . ' ' . url()->current()) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition-all shadow-sm">
                            <i class="fab fa-whatsapp text-sm"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($artikel->judul) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-sky-50 text-sky-500 hover:bg-sky-500 hover:text-white flex items-center justify-center transition-all shadow-sm">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan berhasil disalin!');" class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-700 hover:text-white flex items-center justify-center transition-all shadow-sm" title="Salin Tautan">
                            <i class="fas fa-link text-sm"></i>
                        </button>
                    </div>
                </div>

            </article>
        </div>

        {{-- Sidebar (Kanan - 4 Kolom) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Widget Berita Lainnya --}}
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 border-t-4 border-t-brand-primary sticky top-24">
                <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fas fa-newspaper text-brand-primary"></i> Berita Lainnya
                    </h3>
                    <a href="{{ route('beranda') }}#berita" class="text-xs font-bold text-brand-primary hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="space-y-4">
                    @forelse($recentPosts as $recent)
                        <a href="{{ route('beranda.artikel', $recent->slug) }}" class="flex gap-3 group items-center p-2 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden bg-slate-100 relative">
                                @if($recent->thumbnail)
                                    <img src="{{ asset('storage/' . $recent->thumbnail) }}" alt="{{ $recent->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[10px] font-bold text-amber-500 block mb-0.5">
                                    {{ ($recent->published_at ?? $recent->created_at)?->isoFormat('D MMM YYYY') }}
                                </span>
                                <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
                                    {{ $recent->judul }}
                                </h4>
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada berita lainnya.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

</body>
</html>
