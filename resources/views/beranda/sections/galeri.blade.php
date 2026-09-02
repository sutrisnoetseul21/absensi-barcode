{{-- ══════════════════ GALERI KEGIATAN ══════════════════ --}}
@if($galeris->count())
<section id="galeri" class="mb-16">

    <div class="text-center mb-10" data-aos="fade-up">
        <span class="text-brand-primary font-bold tracking-wider uppercase text-sm">Dokumentasi</span>
        <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mt-2">Galeri Kegiatan</h2>
        <div class="w-24 h-1 bg-brand-primary mx-auto mt-4 rounded-full"></div>
        <p class="text-slate-500 mt-3">Momen-momen berharga dari berbagai aktivitas sekolah.</p>
    </div>

    {{-- Masonry Grid --}}
    <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
        @foreach($galeris as $index => $g)
        <a href="{{ asset('storage/'.$g->foto_path) }}"
           class="glightbox block break-inside-avoid rounded-2xl overflow-hidden group relative cursor-pointer shadow-md hover:shadow-xl transition-all duration-500 bg-slate-100"
           data-title="{{ $g->judul ?? 'Foto Kegiatan' }}"
           data-aos="fade-up"
           data-aos-delay="{{ ($index % 4) * 50 }}">

            {{-- Gambar --}}
            <img src="{{ asset('storage/'.$g->foto_path) }}"
                 alt="{{ $g->judul ?? 'Galeri Foto' }}"
                 onerror="this.parentElement.style.display='none'"
                 class="w-full h-auto transform group-hover:scale-105 transition-all duration-700">

            {{-- Overlay Hover --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                <h4 class="text-white font-bold text-sm leading-snug line-clamp-2">
                    {{ $g->judul ?? 'Foto Kegiatan' }}
                </h4>
                {{-- Ikon Zoom --}}
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 scale-0 group-hover:scale-100 transition-transform duration-300">
                    <i class="fas fa-search-plus text-white text-3xl opacity-80"></i>
                </div>
            </div>
        </a>
        @endforeach
    </div>

</section>
@endif

{{-- GLightbox CDN --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
<script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        GLightbox({
            touchNavigation: true,
            loop: true,
            zoomable: true,
            openEffect: 'zoom',
            closeEffect: 'zoom',
        });
    });
</script>
