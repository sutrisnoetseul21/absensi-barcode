<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestasi Siswa & Sekolah — {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="Hall of Fame dan catatan prestasi membanggakan yang diraih siswa-siswi serta sekolah {{ $sekolah?->school_name ?? 'Sekolah' }}.">
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
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col"
      x-data="{ 
          modalOpen: false,
          activeItem: null,
          openModal(item) {
              this.activeItem = item;
              this.modalOpen = true;
              document.body.style.overflow = 'hidden';
          },
          closeModal() {
              this.modalOpen = false;
              this.activeItem = null;
              document.body.style.overflow = 'auto';
          }
      }">

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

{{-- ══════════════════ HERO SECTION ══════════════════ --}}
<div class="pt-32 pb-20 relative overflow-hidden text-white border-b-4 border-amber-400"
     style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">
    
    {{-- Decorative Background Glow --}}
    <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
        <i class="fas fa-trophy text-[220px] text-amber-300 transform rotate-12 translate-x-12 -translate-y-12"></i>
    </div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <!-- Breadcrumb -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-4 shadow-sm">
            <a href="{{ route('beranda') }}" class="text-white hover:text-amber-300 transition-colors">Beranda</a>
            <span class="text-white/40">/</span>
            <span>Prestasi Sekolah</span>
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-white mb-3 tracking-tight">
            Hall of Fame — Prestasi Sekolah
        </h1>
        <div class="w-24 h-1.5 bg-amber-400 mx-auto rounded-full mb-4 shadow-sm"></div>
        
        <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto leading-relaxed font-medium">
            Catatan tinta emas dan bukti nyata dedikasi, kerja keras, serta talenta siswa-siswi {{ $sekolah?->school_name ?? 'Sekolah' }} di berbagai kompetisi.
        </p>
    </div>
</div>

{{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
<div class="container mx-auto px-4 py-12 max-w-7xl flex-grow">
    
    {{-- Bar Filter & Pencarian --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-10">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                @if(request('search'))
                    Hasil Pencarian: <span class="text-brand-primary">"{{ request('search') }}"</span>
                @else
                    Daftar Prestasi & Penghargaan
                @endif
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Total {{ $prestasi->total() }} capaian prestasi tercatat</p>
        </div>

        <form action="{{ route('prestasi.all') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari prestasi / juara..." 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-brand-primary text-white text-xs font-bold rounded-xl shadow-sm hover:opacity-90 transition-all">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('prestasi.all') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center" title="Reset">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Grid Prestasi (3 Kolom) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($prestasi as $item)
            <div class="bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 overflow-hidden flex flex-col group cursor-pointer"
                 @click="openModal({
                     title: '{{ addslashes($item->judul) }}',
                     date: '{{ ($item->published_at ?? $item->created_at)?->isoFormat('D MMMM YYYY') }}',
                     img: '{{ $item->thumbnail ? asset('storage/' . $item->thumbnail) : '' }}',
                     desc: '{{ addslashes(strip_tags($item->konten)) }}',
                     link: '{{ route('beranda.artikel', $item->slug) }}'
                 })">
                
                {{-- Cover Gambar --}}
                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                    @if($item->thumbnail)
                        <img src="{{ asset('storage/' . $item->thumbnail) }}" 
                             alt="{{ $item->judul }}" 
                             class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-amber-300 bg-amber-50/60">
                            <i class="fas fa-trophy text-5xl"></i>
                        </div>
                    @endif

                    {{-- Badge Tanggal --}}
                    <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-bold text-slate-700 shadow-sm flex items-center gap-1.5">
                        <i class="far fa-calendar-alt text-amber-500"></i>
                        <span>{{ ($item->published_at ?? $item->created_at)?->isoFormat('MMM YYYY') }}</span>
                    </div>

                    {{-- Hover Overlay Zoom --}}
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="w-12 h-12 bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/50 text-sm shadow-md">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                {{-- Konten Prestasi --}}
                <div class="p-6 flex-1 flex flex-col">
                    <div class="w-10 h-10 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 mb-3 group-hover:bg-amber-400 group-hover:text-slate-900 transition-colors shadow-sm">
                        <i class="fas fa-medal text-lg"></i>
                    </div>

                    <h3 class="font-bold text-slate-800 text-base leading-snug group-hover:text-brand-primary transition-colors line-clamp-2 mb-2">
                        {{ $item->judul }}
                    </h3>

                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-3 mb-4 flex-grow">
                        {{ Str::limit(strip_tags($item->konten), 120) }}
                    </p>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                        <span class="text-amber-600 uppercase tracking-wider font-extrabold text-[10px]">
                            <i class="fas fa-award mr-1"></i> Kejuaraan
                        </span>
                        <span class="text-brand-primary font-bold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                            Lihat Detail <i class="fas fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full py-16 px-4 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-400">
                    <i class="fas fa-trophy text-2xl"></i>
                </div>
                <h4 class="text-base font-bold text-slate-700 mb-1">Belum Ada Data Prestasi</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    @if(request('search'))
                        Tidak ditemukan prestasi dengan kata kunci "{{ request('search') }}".
                    @else
                        Data prestasi siswa & sekolah akan segera diperbarui.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-12 flex justify-center">
        {{ $prestasi->links() }}
    </div>

</div>

{{-- ══════════════════ MODAL POPUP PRESTASI ══════════════════ --}}
<div x-show="modalOpen" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;">

    <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-100 relative"
         @click.away="closeModal()"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        {{-- Tombol Close --}}
        <button @click="closeModal()" class="absolute top-4 right-4 z-10 w-9 h-9 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center transition-all backdrop-blur-sm">
            <i class="fas fa-times"></i>
        </button>

        {{-- Gambar Modal --}}
        <template x-if="activeItem && activeItem.img">
            <div class="relative aspect-[16/9] bg-slate-100 overflow-hidden">
                <img :src="activeItem.img" :alt="activeItem.title" class="w-full h-full object-cover">
            </div>
        </template>

        {{-- Isi Modal --}}
        <div class="p-6 sm:p-8 max-h-[60vh] overflow-y-auto">
            <div class="flex items-center gap-2 text-xs font-bold text-amber-500 uppercase tracking-wider mb-2">
                <i class="fas fa-trophy"></i>
                <span x-text="activeItem ? activeItem.date : ''"></span>
            </div>

            <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mb-4 leading-snug" x-text="activeItem ? activeItem.title : ''"></h3>

            <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3 mb-6" x-text="activeItem ? activeItem.desc : ''"></div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="closeModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                    Tutup
                </button>
                <template x-if="activeItem && activeItem.link">
                    <a :href="activeItem.link" class="px-5 py-2.5 rounded-xl bg-brand-primary text-white text-xs font-bold hover:opacity-90 transition-all flex items-center gap-1.5">
                        <span>Buka Halaman Lengkap</span>
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                    </a>
                </template>
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

</body>
</html>
