{{-- ══════════════════ HERO SECTION ══════════════════ --}}
<div class="relative overflow-hidden min-h-[70vh] flex items-center pt-20">
    <!-- Background Photo (Full, Prominent) -->
    <div class="absolute inset-0">
        @php
            $heroSrc = asset('hero-bg-school.png');
            if ($setting->hero_image) $heroSrc = asset('storage/'.$setting->hero_image);
            elseif ($sekolah?->hero_image_path) $heroSrc = asset('storage/'.$sekolah->hero_image_path);
        @endphp
        <img src="{{ $heroSrc }}" alt="School Background" class="w-full h-full object-cover object-top">
        
        <!-- Dark gradient overlay agar teks tetap terbaca -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/40 to-transparent"></div>
        <!-- Warna accent indigo di atas overlay -->
        <div class="absolute inset-0 bg-gradient-to-tr from-brand-primary-950/60 via-transparent to-brand-secondary-950/30"></div>
    </div>
    
    <!-- Subtle animated color blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -left-20 w-96 h-96 rounded-full bg-brand-primary/20 blur-3xl animate-[moveblob1_15s_ease-in-out_infinite]"></div>
        <div class="absolute -bottom-20 right-0 w-80 h-80 rounded-full bg-brand-secondary/15 blur-3xl animate-[moveblob2_18s_ease-in-out_infinite]"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="max-w-3xl">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 backdrop-blur-sm text-brand-primary-light text-sm font-semibold px-4 py-2 rounded-full mb-8 shadow-lg">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Portal Resmi Sekolah
            </div>

            <!-- Main Title -->
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.1] tracking-tight mb-6">
                Selamat Datang di 
                <span class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-200 mt-2 block">
                    {{ $sekolah?->school_name ?? 'Portal Pendidikan' }}
                </span>
            </h1>

            <p class="text-lg text-slate-400 mb-10 max-w-xl leading-relaxed">
                Mewujudkan generasi berprestasi, berkarakter, dan berwawasan global melalui pendidikan berkualitas berbasis teknologi.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Tombol Utama -->
                <a href="#tentang" class="px-8 py-3 bg-brand-primary hover:bg-brand-primary-dark text-white font-bold rounded-full shadow-lg transition-transform transform hover:-translate-y-1 inline-flex items-center gap-2 justify-center">
                    <span>Profil Sekolah</span> 
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <!-- Tombol Kedua -->
                <a href="#kontak" class="px-8 py-3 bg-white/10 backdrop-blur-sm border border-white/30 hover:bg-white hover:text-slate-900 text-white font-bold rounded-full transition-all inline-flex items-center gap-2 justify-center">
                    <span>Hubungi Kami</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Decorative Side Orb (Desktop Only) -->
    <div class="hidden lg:block absolute right-0 top-0 bottom-0 w-1/3 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-l from-brand-primary-dark/20 to-transparent"></div>
        <div class="absolute top-1/2 right-20 -translate-y-1/2 w-72 h-72 rounded-full bg-gradient-to-br from-brand-primary/20 to-brand-secondary/20 blur-2xl"></div>
        <div class="absolute top-1/2 right-32 -translate-y-1/2 w-48 h-48 rounded-full bg-gradient-to-br from-brand-primary/10 to-brand-info-light/10 blur-xl animate-[spin_20s_linear_infinite]"></div>
    </div>
</div>

{{-- ══════════════════ RUNNING TEXT ══════════════════ --}}
@if($setting->running_text)
<div class="bg-brand-primary text-white py-3 border-y border-brand-primary-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 overflow-hidden flex whitespace-nowrap">
        <div class="animate-[marquee_20s_linear_infinite] flex items-center font-semibold text-sm tracking-wide">
            @for($i=0; $i<4; $i++)
                <span class="mx-8 flex items-center gap-3">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    {{ $setting->running_text }}
                </span>
            @endfor
        </div>
    </div>
</div>
<style>
    @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
</style>
@endif
