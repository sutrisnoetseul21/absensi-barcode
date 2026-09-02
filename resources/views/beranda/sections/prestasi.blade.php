{{-- ══════════════════ PRESTASI SISWA & SEKOLAH (1 KOLOM FULL) ══════════════════ --}}
@if($prestasis->count())
<section id="prestasi" class="mb-16 relative overflow-hidden" 
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

    {{-- Header Section --}}
    <div class="text-center mb-10" data-aos="fade-up">
        <span class="text-amber-500 font-bold tracking-wider uppercase text-xs flex items-center justify-center gap-1.5 mb-1">
            <i class="fas fa-trophy text-amber-500"></i> Hall of Fame
        </span>
        <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mt-1">Prestasi Siswa & Sekolah</h2>
        <div class="w-24 h-1 bg-brand-primary mx-auto mt-3 rounded-full"></div>
        <p class="text-slate-500 mt-2 max-w-2xl mx-auto text-sm">
            Bukti nyata dedikasi dan kerja keras siswa-siswi {{ $sekolah?->school_name ?? 'Sekolah' }} dalam meraih prestasi gemilang.
        </p>
    </div>

    {{-- Grid 4 Kolom --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($prestasis as $index => $item)
            <div class="bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1.5 overflow-hidden border border-slate-100 group cursor-pointer flex flex-col h-full"
                 @click="openModal({
                     title: {{ json_encode($item->judul) }},
                     date: '{{ ($item->published_at ?? $item->created_at)?->isoFormat('D MMMM YYYY') }}',
                     img: '{{ $item->thumbnail ? asset('storage/' . $item->thumbnail) : '' }}',
                     desc: {{ json_encode(strip_tags($item->konten)) }},
                     link: '{{ route('beranda.artikel', $item->slug) }}'
                 })"
                 data-aos="fade-up" 
                 data-aos-delay="{{ ($index % 4) * 80 }}">
                
                {{-- Gambar Cover Prestasi --}}
                <div class="relative h-48 overflow-hidden flex-shrink-0 bg-slate-100">
                    @if($item->thumbnail)
                        <img src="{{ asset('storage/' . $item->thumbnail) }}" 
                             alt="{{ $item->judul }}" 
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center">
                            <i class="fas fa-trophy text-4xl text-white opacity-40"></i>
                        </div>
                    @endif
                    
                    {{-- Badge Tanggal Pojok Atas --}}
                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-[11px] font-bold text-slate-700 shadow-sm z-10">
                        {{ ($item->published_at ?? $item->created_at)?->isoFormat('MMM YYYY') }}
                    </div>

                    {{-- Overlay Hover Zoom --}}
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-0">
                        <span class="w-10 h-10 bg-white/30 backdrop-blur rounded-full flex items-center justify-center text-white border border-white/50">
                            <i class="fas fa-search-plus text-sm"></i>
                        </span>
                    </div>
                </div>

                {{-- Konten Card --}}
                <div class="p-6 relative flex flex-col flex-grow">
                    {{-- Ikon Piala Mengambang (Floating Trophy) --}}
                    <div class="absolute -top-5 left-5 w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-500 rounded-xl flex items-center justify-center text-white shadow-md border-2 border-white z-10">
                        <i class="fas fa-trophy text-sm"></i>
                    </div>

                    <div class="mt-3 flex-grow">
                        <h4 class="font-bold text-slate-800 text-base leading-snug mb-2 line-clamp-2 group-hover:text-brand-primary transition-colors">
                            {{ $item->judul }}
                        </h4>
                        <p class="text-slate-500 text-xs line-clamp-3 mb-4 leading-relaxed">
                            {{ Str::limit(strip_tags($item->konten), 90) }}
                        </p>
                    </div>
                    
                    <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-500 group-hover:text-brand-primary transition-colors">
                        <span>Detail Prestasi</span>
                        <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MODAL POP-UP INTERAKTIF (ALPINE.JS) --}}
    <div x-show="modalOpen" 
         style="display: none;" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="closeModal()">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="closeModal()"></div>

        {{-- Modal Box --}}
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col md:flex-row z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             @click.stop>
            
            {{-- Tombol Tutup --}}
            <button @click="closeModal()" class="absolute top-4 right-4 z-20 bg-slate-100 hover:bg-slate-200 text-slate-600 w-9 h-9 rounded-full flex items-center justify-center transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>

            {{-- Foto Besar (Kiri) --}}
            <div class="md:w-1/2 bg-slate-100 flex items-center justify-center relative min-h-[280px] md:min-h-[380px]">
                <template x-if="activeItem?.img">
                    <img :src="activeItem?.img" class="w-full h-full object-cover absolute inset-0">
                </template>
                <template x-if="!activeItem?.img">
                    <div class="w-full h-full flex items-center justify-center bg-amber-50 text-amber-300 flex-col">
                        <i class="fas fa-trophy text-6xl mb-2 opacity-50"></i>
                        <span class="text-xs font-semibold text-slate-400">Tidak ada foto dokumentasi</span>
                    </div>
                </template>
            </div>

            {{-- Info Detail (Kanan) --}}
            <div class="md:w-1/2 p-6 md:p-8 flex flex-col justify-center bg-white">
                <div class="mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-full text-xs font-bold uppercase tracking-wider" x-text="activeItem?.date">
                    </span>
                </div>
                
                <h3 class="text-xl md:text-2xl font-extrabold text-slate-900 leading-snug mb-4" x-text="activeItem?.title"></h3>
                
                <div class="text-slate-600 text-sm leading-relaxed mb-6 max-h-[180px] overflow-y-auto pr-2 custom-scrollbar" x-text="activeItem?.desc"></div>
                
                <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                    <a :href="activeItem?.link" class="inline-flex items-center gap-2 text-xs font-bold text-brand-primary hover:underline">
                        Buka Halaman Lengkap <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                    <button @click="closeModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>
@endif
