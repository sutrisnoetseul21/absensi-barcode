<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sekolah?->school_name ?? 'Beranda Sekolah' }}</title>
    <meta name="description" content="Portal Resmi {{ $sekolah?->school_name ?? 'Sekolah' }}. Informasi profil, fasilitas, berita, dan layanan sekolah.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
        
        @keyframes moveblob1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(60px, -40px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.95); }
        }
        @keyframes moveblob2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-50px, 30px) scale(1.05); }
            66% { transform: translate(40px, -50px) scale(1.1); }
        }
        .guru-slider .swiper-pagination-bullet { width: 10px; height: 10px; background: #cbd5e1; opacity: 1; }
        .guru-slider .swiper-pagination-bullet-active { background: var(--color-brand-primary, #059669); width: 24px; border-radius: 5px; }
    </style>

    @if($sekolah)
        <style>
            :root {
                @if($sekolah->theme_primary) --color-brand-primary: {{ $sekolah->theme_primary }}; @endif
                @if($sekolah->theme_secondary) --color-brand-secondary: {{ $sekolah->theme_secondary }}; @endif
                @if($sekolah->theme_accent) --color-brand-accent: {{ $sekolah->theme_accent }}; @endif
                @if($sekolah->theme_warning) --color-brand-warning: {{ $sekolah->theme_warning }}; @endif
                @if($sekolah->theme_danger) --color-brand-danger: {{ $sekolah->theme_danger }}; @endif
                @if($sekolah->theme_info) --color-brand-info: {{ $sekolah->theme_info }}; @endif
            }
        </style>
    @endif
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-primary-light selection:text-brand-primary-dark">

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

{{-- ══════════════════ HERO + RUNNING TEXT ══════════════════ --}}
@include('beranda.sections.hero')

{{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full flex-1">

    {{-- Sambutan Kepala Sekolah --}}
    @include('beranda.sections.sambutan')

    {{-- Profil, Visi & Misi --}}
    @include('beranda.sections.profil')

    {{-- Widget: Statistik, Akses Cepat & YouTube --}}
    @include('beranda.sections.widget')

    {{-- Data Sekolah (Statistik Angka) --}}
    @include('beranda.sections.statistik')

    {{-- Fasilitas / Sarana Prasarana --}}
    @include('beranda.sections.fasilitas')

    {{-- Tenaga Pendidik (Guru & Staff) --}}
    @include('beranda.sections.guru')

    {{-- Prestasi Siswa & Sekolah --}}
    @include('beranda.sections.prestasi')

    {{-- Berita & Pengumuman (2 Kolom Sejajar) --}}
    @include('beranda.sections.berita')

    {{-- Galeri --}}
    @include('beranda.sections.galeri')

</div><!-- End Main Content -->



{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

<script>
    // Realtime Clock
    function updateClock() {
        const el = document.getElementById('jam-sekarang');
        if (el) el.textContent = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
    }
    updateClock(); setInterval(updateClock, 1000);
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if(document.querySelector('.guru-slider')) {
            new Swiper('.guru-slider', {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: false,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                }
            });
        }
    });
</script>

@livewireScripts
</body>
</html>
