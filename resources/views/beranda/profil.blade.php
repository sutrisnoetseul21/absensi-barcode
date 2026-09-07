<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Sekolah - {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    @php
        $favicon = $sekolah?->school_logo_path ? asset('storage/' . $sekolah->school_logo_path) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ $favicon }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @if($sekolah)
        <style>
            :root {
                @if($sekolah->theme_primary) --color-brand-primary: {{ $sekolah->theme_primary }}; @endif
                @if($sekolah->theme_secondary) --color-brand-secondary: {{ $sekolah->theme_secondary }}; @endif
                @if($sekolah->theme_accent) --color-brand-accent: {{ $sekolah->theme_accent }}; @endif
            }
        </style>
    @endif
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-primary-light selection:text-brand-primary-dark min-h-screen flex flex-col">

<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

<!-- Hero Section (Persis Desain Portal Layanan Publik) -->
<div class="pt-32 pb-20 relative overflow-hidden text-white border-b-4 border-amber-400 bg-gradient-to-br from-brand-primary to-blue-900">
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-blue-100/70 mb-4 font-medium flex-wrap">
            <a href="{{ route('beranda') }}" class="hover:text-amber-300 transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-white font-semibold">Profil Sekolah</span>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-6">
                <i class="fas fa-university text-amber-300"></i> PROFIL & IDENTITAS
            </div>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 leading-tight tracking-tight">
                Profil Sekolah
            </h1>
            <p class="text-lg text-blue-100 font-medium leading-relaxed max-w-2xl">
                Mengenal lebih dekat sejarah, visi & misi, sambutan kepala sekolah, serta bagan struktur organisasi {{ $sekolah?->school_name ?? 'sekolah kami' }}.
            </p>
        </div>
    </div>
</div>

<div class="bg-slate-50 min-h-screen py-16 w-full flex-grow"
     x-data="{
         activeAccordion: (window.location.hash ? window.location.hash.replace('#', '') : 'sejarah'),
         setAccordion(id) {
             this.activeAccordion = (this.activeAccordion === id ? null : id);
         },
         openAccordion(id) {
             this.activeAccordion = id;
             setTimeout(() => {
                 const el = document.getElementById(id);
                 if (el) {
                     el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                 }
             }, 100);
         },
         init() {
             if (window.location.hash) {
                 const hash = window.location.hash.replace('#', '');
                 if (['sejarah', 'visimisi', 'sambutan', 'struktur'].includes(hash)) {
                     this.activeAccordion = hash;
                 }
             }
             window.addEventListener('hashchange', () => {
                 if (window.location.hash) {
                     const hash = window.location.hash.replace('#', '');
                     if (['sejarah', 'visimisi', 'sambutan', 'struktur'].includes(hash)) {
                         this.activeAccordion = hash;
                     }
                 }
             });
         }
     }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Accordions (Model Alur Layanan Ijazah) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Section Header Card -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/60">
                    <div class="flex items-start gap-4 mb-2">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-primary flex items-center justify-center shrink-0">
                            <i class="fas fa-landmark text-2xl"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 mb-2 tracking-wider">
                                INFORMASI RESMI
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
                                {{ $sekolah?->school_name ?? 'SMP Negeri 3 Kedungreja' }}
                            </h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed mt-4">
                        Berikut adalah informasi profil dan identitas resmi {{ $sekolah?->school_name ?? 'sekolah kami' }}. Klik pada masing-masing bagian untuk membuka detail informasi.
                    </p>
                </div>

                <!-- Accordions List -->
                <div class="space-y-4">
                    
                    <!-- 1. Sejarah Singkat -->
                    <div id="sejarah" class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden transition-all duration-300 scroll-mt-28"
                         :class="activeAccordion === 'sejarah' ? 'ring-2 ring-brand-primary/20 border-brand-primary/30' : 'hover:border-slate-300'">
                        
                        <!-- Accordion Header -->
                        <button @click="setAccordion('sejarah')" 
                                class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 focus:outline-none bg-white transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                     :class="activeAccordion === 'sejarah' ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-400'">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold transition-colors" :class="activeAccordion === 'sejarah' ? 'text-brand-primary' : 'text-slate-800'">
                                        Sejarah Singkat
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wider">
                                            SEJARAH
                                        </span>
                                        <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                                            Latar Belakang & Perjalanan Sekolah
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 transition-transform duration-300"
                                 :class="activeAccordion === 'sejarah' ? 'rotate-180 bg-brand-50 border-brand-200 text-brand-primary' : ''">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </button>

                        <!-- Accordion Body -->
                        <div x-show="activeAccordion === 'sejarah'" x-collapse style="display: none;">
                            <div class="px-6 pb-6 pt-2 border-t border-slate-100 mt-2">
                                <div class="prose prose-slate max-w-none text-slate-600 text-sm sm:text-base leading-relaxed text-justify">
                                    @if($setting?->profil_singkat)
                                        @php
                                            $isProfilHtml = strip_tags($setting->profil_singkat) !== $setting->profil_singkat;
                                        @endphp
                                        @if($isProfilHtml)
                                            <div class="rich-content-profil">
                                                {!! $setting->profil_singkat !!}
                                            </div>
                                        @else
                                            {!! nl2br(e($setting->profil_singkat)) !!}
                                        @endif
                                    @else
                                        <p class="text-slate-400 italic">Belum ada data sejarah singkat sekolah yang ditambahkan.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Visi & Misi -->
                    <div id="visimisi" class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden transition-all duration-300 scroll-mt-28"
                         :class="activeAccordion === 'visimisi' ? 'ring-2 ring-brand-primary/20 border-brand-primary/30' : 'hover:border-slate-300'">
                        
                        <!-- Accordion Header -->
                        <button @click="setAccordion('visimisi')" 
                                class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 focus:outline-none bg-white transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                     :class="activeAccordion === 'visimisi' ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-400'">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold transition-colors" :class="activeAccordion === 'visimisi' ? 'text-brand-primary' : 'text-slate-800'">
                                        Visi & Misi
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wider">
                                            VISI & MISI
                                        </span>
                                        <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                                            Arah & Sasaran Pendidikan
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 transition-transform duration-300"
                                 :class="activeAccordion === 'visimisi' ? 'rotate-180 bg-brand-50 border-brand-200 text-brand-primary' : ''">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </button>

                        <!-- Accordion Body -->
                        <div x-show="activeAccordion === 'visimisi'" x-collapse style="display: none;">
                            <div class="px-6 pb-6 pt-2 border-t border-slate-100 mt-2 space-y-6">
                                {{-- Visi --}}
                                <div class="bg-gradient-to-br from-brand-primary/10 to-blue-50/50 rounded-2xl p-6 border border-brand-primary/20">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-brand-primary text-white uppercase tracking-wider mb-3">
                                        <i class="fas fa-eye text-xs"></i> VISI SEKOLAH
                                    </div>
                                    @if($setting?->visi)
                                        @php
                                            $isVisiHtml = strip_tags($setting->visi) !== $setting->visi;
                                        @endphp
                                        @if($isVisiHtml)
                                            <div class="text-slate-800 italic font-semibold leading-relaxed text-base sm:text-lg rich-content-visi">
                                                {!! $setting->visi !!}
                                            </div>
                                        @else
                                            <p class="text-slate-800 italic font-semibold leading-relaxed text-base sm:text-lg">
                                                "{{ $setting->visi }}"
                                            </p>
                                        @endif
                                    @else
                                        <p class="text-slate-800 italic font-semibold leading-relaxed text-base sm:text-lg">
                                            "Terwujudnya peserta didik yang beriman, cerdas, terampil, dan berkarakter."
                                        </p>
                                    @endif
                                </div>

                                {{-- Misi --}}
                                <div class="space-y-3">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 uppercase tracking-wider mb-1">
                                        <i class="fas fa-list-check text-xs text-brand-primary"></i> MISI SEKOLAH
                                    </div>
                                    @if($setting?->misi)
                                        @php
                                            $isHtml = strip_tags($setting->misi) !== $setting->misi;
                                        @endphp
                                        @if($isHtml)
                                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 text-slate-700 text-sm leading-relaxed rich-content-misi">
                                                {!! $setting->misi !!}
                                            </div>
                                        @else
                                            <div class="space-y-2.5">
                                                @foreach(explode("\n", $setting->misi) as $misi)
                                                    @if(trim($misi) != '')
                                                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                                                        <div class="w-6 h-6 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                                                            {{ $loop->iteration }}
                                                        </div>
                                                        <p class="text-slate-700 text-sm leading-relaxed">{{ trim($misi) }}</p>
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-slate-400 italic">Belum ada data misi sekolah yang ditambahkan.</p>
                                    @endif
                                </div>
                            </div>
                            
                            <style>
                                .rich-content-misi ol { list-style-type: decimal; padding-left: 1.5rem; margin: 0.5rem 0; }
                                .rich-content-misi ul { list-style-type: disc; padding-left: 1.5rem; margin: 0.5rem 0; }
                                .rich-content-misi li { margin-bottom: 0.5rem; line-height: 1.6; }
                                .rich-content-misi p { margin-bottom: 0.75rem; line-height: 1.6; }
                                .rich-content-misi p:last-child { margin-bottom: 0; }

                                .rich-content-profil p { margin-bottom: 0.75rem; line-height: 1.7; }
                                .rich-content-profil p:last-child { margin-bottom: 0; }
                                .rich-content-profil ol { list-style-type: decimal; padding-left: 1.5rem; margin: 0.5rem 0; }
                                .rich-content-profil ul { list-style-type: disc; padding-left: 1.5rem; margin: 0.5rem 0; }
                                .rich-content-profil li { margin-bottom: 0.4rem; }

                                .rich-content-visi p { margin-bottom: 0.5rem; }
                                .rich-content-visi p:last-child { margin-bottom: 0; }
                            </style>
                        </div>
                    </div>

                    <!-- 3. Sambutan Kepala Sekolah -->
                    <div id="sambutan" class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden transition-all duration-300 scroll-mt-28"
                         :class="activeAccordion === 'sambutan' ? 'ring-2 ring-brand-primary/20 border-brand-primary/30' : 'hover:border-slate-300'">
                        
                        <!-- Accordion Header -->
                        <button @click="setAccordion('sambutan')" 
                                class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 focus:outline-none bg-white transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                     :class="activeAccordion === 'sambutan' ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-400'">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold transition-colors" :class="activeAccordion === 'sambutan' ? 'text-brand-primary' : 'text-slate-800'">
                                        Sambutan Kepala Sekolah
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wider">
                                            PIMPINAN
                                        </span>
                                        <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                                            Prakata & Amanat
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 transition-transform duration-300"
                                 :class="activeAccordion === 'sambutan' ? 'rotate-180 bg-brand-50 border-brand-200 text-brand-primary' : ''">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </button>

                        <!-- Accordion Body -->
                        <div x-show="activeAccordion === 'sambutan'" x-collapse style="display: none;">
                            <div class="px-6 pb-6 pt-2 border-t border-slate-100 mt-2">
                                <div class="flex flex-col md:flex-row gap-6 items-start">
                                    <div class="w-full md:w-48 shrink-0 flex flex-col items-center text-center p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                        <div class="w-32 aspect-[3/4] rounded-xl overflow-hidden shadow-sm bg-slate-200 mb-3 border border-slate-200">
                                            @if($setting?->foto_kepsek)
                                                <img src="{{ asset('storage/' . $setting->foto_kepsek) }}" alt="Kepala Sekolah" class="w-full h-full object-cover" style="object-position: center {{ $setting->posisi_foto_kepsek ?? 'top' }};">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                                    <i class="fas fa-user-tie text-4xl"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-800 leading-snug">{{ $sekolah?->principal_name ?? 'Nama Kepala Sekolah' }}</h4>
                                        <p class="text-[11px] text-brand-primary font-semibold mt-0.5">Kepala Sekolah</p>
                                        @if($sekolah?->principal_nip)
                                            <p class="text-[10px] text-slate-400 mt-0.5 font-mono">NIP. {{ $sekolah->principal_nip }}</p>
                                        @endif
                                    </div>
                                    <div class="flex-1 prose prose-slate max-w-none text-slate-600 text-sm sm:text-base leading-relaxed text-justify">
                                        @if($setting?->kutipan_kepsek)
                                        <blockquote class="not-italic text-slate-700 font-medium mb-4 pl-4 border-l-4 border-amber-400 bg-amber-50/50 py-2.5 rounded-r-xl">
                                            "{{ $setting->kutipan_kepsek }}"
                                        </blockquote>
                                        @endif

                                        @if($setting?->sambutan_kepsek)
                                            {!! $setting->sambutan_kepsek !!}
                                        @else
                                            <p class="text-slate-400 italic">Belum ada data sambutan kepala sekolah yang ditambahkan.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Struktur Organisasi -->
                    <div id="struktur" class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden transition-all duration-300 scroll-mt-28"
                         :class="activeAccordion === 'struktur' ? 'ring-2 ring-brand-primary/20 border-brand-primary/30' : 'hover:border-slate-300'">
                        
                        <!-- Accordion Header -->
                        <button @click="setAccordion('struktur')" 
                                class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 focus:outline-none bg-white transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                     :class="activeAccordion === 'struktur' ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-400'">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold transition-colors" :class="activeAccordion === 'struktur' ? 'text-brand-primary' : 'text-slate-800'">
                                        Struktur Organisasi
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wider">
                                            BAGAN ORGANISASI
                                        </span>
                                        <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                                            Tata Kelola & Manajemen Sekolah
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 transition-transform duration-300"
                                 :class="activeAccordion === 'struktur' ? 'rotate-180 bg-brand-50 border-brand-200 text-brand-primary' : ''">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </button>

                        <!-- Accordion Body -->
                        <div x-show="activeAccordion === 'struktur'" x-collapse style="display: none;">
                            <div class="px-6 pb-6 pt-2 border-t border-slate-100 mt-2">
                                @if($setting?->struktur_organisasi)
                                    <div class="relative group rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                                        <img src="{{ asset('storage/' . $setting->struktur_organisasi) }}" alt="Struktur Organisasi" class="w-full h-auto object-cover transform group-hover:scale-[1.02] transition-transform duration-500">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm">
                                            <a href="{{ asset('storage/' . $setting->struktur_organisasi) }}" target="_blank" class="text-white font-bold text-xs bg-white/20 px-4 py-2 rounded-full backdrop-blur-md border border-white/30 shadow-lg flex items-center gap-2">
                                                <i class="fas fa-search-plus"></i> Perbesar Gambar
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                        <span><i class="fas fa-info-circle mr-1"></i> Klik gambar untuk melihat dalam ukuran penuh</span>
                                        <a href="{{ asset('storage/' . $setting->struktur_organisasi) }}" target="_blank" download class="font-bold text-brand-primary hover:underline flex items-center gap-1">
                                            <i class="fas fa-download"></i> Unduh Gambar
                                        </a>
                                    </div>
                                @else
                                    <div class="p-10 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-400">
                                        <i class="fas fa-sitemap text-4xl mb-3 text-slate-300"></i>
                                        <p class="text-sm font-medium text-slate-600">Bagan Struktur Organisasi belum diunggah.</p>
                                        <p class="text-xs text-slate-400 mt-1">Admin sekolah dapat mengunggah bagan struktur melalui panel admin.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Direktori Guru Banner (Persis Card Survei di Portal Layanan Publik) -->
                <div class="bg-gradient-to-r from-blue-600 to-brand-primary rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden mt-8">
                    <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-1/4 translate-y-1/4">
                        <i class="fas fa-chalkboard-teacher text-9xl"></i>
                    </div>
                    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white mb-2 tracking-wider">
                                TENAGA PENDIDIK
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold mb-2">Direktori Guru & Karyawan</h3>
                            <p class="text-blue-100 text-sm max-w-md">Mengenal lebih dekat para guru dan staf berdedikasi yang membimbing siswa-siswi di {{ $sekolah?->school_name ?? 'sekolah kami' }}.</p>
                        </div>
                        <div class="shrink-0">
                            <a href="{{ route('guru.all') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white text-sm font-bold text-brand-primary hover:bg-slate-50 transition-colors shadow-sm">
                                <i class="fas fa-users"></i> Lihat Direktori Guru
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sidebar -->
            <div class="lg:col-span-4 space-y-6">

                <!-- 1. Daftar Bagian Profil (Sinkron dengan Dropdown Accordion) -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60">
                    <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100">
                        <i class="fas fa-th-list text-slate-400 text-xs"></i>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Bagian Profil</span>
                    </div>
                    <div class="space-y-1.5">
                        <button @click="openAccordion('sejarah')" class="w-full flex items-center justify-between p-3 rounded-xl text-xs font-bold transition-all text-left"
                                :class="activeAccordion === 'sejarah' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100'">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-history text-xs w-4"></i> Sejarah Singkat
                            </span>
                            <i class="fas fa-chevron-right text-[10px]" :class="activeAccordion === 'sejarah' ? 'text-white' : 'text-slate-400'"></i>
                        </button>
                        <button @click="openAccordion('visimisi')" class="w-full flex items-center justify-between p-3 rounded-xl text-xs font-bold transition-all text-left"
                                :class="activeAccordion === 'visimisi' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100'">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-bullseye text-xs w-4"></i> Visi & Misi
                            </span>
                            <i class="fas fa-chevron-right text-[10px]" :class="activeAccordion === 'visimisi' ? 'text-white' : 'text-slate-400'"></i>
                        </button>
                        <button @click="openAccordion('sambutan')" class="w-full flex items-center justify-between p-3 rounded-xl text-xs font-bold transition-all text-left"
                                :class="activeAccordion === 'sambutan' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100'">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-user-tie text-xs w-4"></i> Sambutan Kepala Sekolah
                            </span>
                            <i class="fas fa-chevron-right text-[10px]" :class="activeAccordion === 'sambutan' ? 'text-white' : 'text-slate-400'"></i>
                        </button>
                        <button @click="openAccordion('struktur')" class="w-full flex items-center justify-between p-3 rounded-xl text-xs font-bold transition-all text-left"
                                :class="activeAccordion === 'struktur' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100'">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-sitemap text-xs w-4"></i> Struktur Organisasi
                            </span>
                            <i class="fas fa-chevron-right text-[10px]" :class="activeAccordion === 'struktur' ? 'text-white' : 'text-slate-400'"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. Preview Bagan Struktur Organisasi (Persis Card Visi Misi / Maklumat di Portal Layanan Publik) -->
                @if($setting?->struktur_organisasi)
                <div class="bg-white rounded-2xl p-1 shadow-sm border border-slate-200/60 overflow-hidden group">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-sitemap text-brand-primary"></i> STRUKTUR ORGANISASI
                        </h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-brand-primary tracking-wider">BAGAN RESMI</span>
                    </div>
                    <a href="{{ asset('storage/' . $setting->struktur_organisasi) }}" target="_blank" class="block relative overflow-hidden bg-slate-100">
                        <img src="{{ asset('storage/' . $setting->struktur_organisasi) }}" alt="Struktur Organisasi" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm">
                            <span class="text-white font-bold text-sm bg-white/20 px-4 py-2 rounded-full backdrop-blur-md border border-white/30">
                                <i class="fas fa-search-plus mr-1"></i> Perbesar Gambar
                            </span>
                        </div>
                    </a>
                </div>
                @endif

                <!-- 3. Menu Informasi & Kegiatan Terkait (Persis Tampilan Kanal Lapor di Portal Layanan Publik) -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60">
                    <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100">
                        <i class="fas fa-compass text-slate-400 text-xs"></i>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Informasi & Kegiatan Sekolah</span>
                    </div>

                    <div class="space-y-2.5">
                        <a href="{{ route('guru.all') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200/70 hover:border-blue-200 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-id-badge text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Direktori Guru</div>
                                    <h5 class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Pendidik & Tenaga Kependidikan</h5>
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-slate-300 group-hover:text-blue-600 text-xs transition-colors"></i>
                        </a>

                        <a href="{{ route('prestasi.all') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200/70 hover:border-amber-200 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-trophy text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Capaian Siswa</div>
                                    <h5 class="text-xs font-bold text-slate-800 group-hover:text-amber-600 transition-colors">Prestasi Sekolah</h5>
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-slate-300 group-hover:text-amber-600 text-xs transition-colors"></i>
                        </a>

                        <a href="{{ route('galeri.all') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200/70 hover:border-indigo-200 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-images text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dokumentasi</div>
                                    <h5 class="text-xs font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">Galeri Foto Kegiatan</h5>
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-slate-300 group-hover:text-indigo-600 text-xs transition-colors"></i>
                        </a>
                    </div>
                </div>

                <!-- 4. Layanan Aspirasi & Pengaduan (Widget Gradient Persis Layanan Publik) -->
                <div class="bg-gradient-to-br from-brand-primary to-blue-900 rounded-3xl p-6 text-white shadow-lg shadow-brand-primary/20 relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center mb-4 text-white text-base">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4 class="text-base font-extrabold mb-1">Layanan Informasi & Kontak</h4>
                    <p class="text-blue-100/90 text-xs mb-5 leading-relaxed">
                        Punya pertanyaan, aspirasi, atau membutuhkan informasi lebih lanjut mengenai sekolah kami?
                    </p>
                    <a href="{{ route('pengaduan.index') }}" class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-white text-slate-900 hover:bg-amber-300 font-bold text-xs uppercase tracking-wider transition-colors shadow-sm">
                        <i class="fas fa-comment-dots text-brand-primary"></i> Hubungi / Sampaikan Pengaduan
                    </a>
                </div>

                <!-- 5. Berita Terbaru -->
                @if(isset($artikels) && $artikels->count() > 0)
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-newspaper text-slate-400 text-xs"></i>
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Berita Terbaru</span>
                        </div>
                        <a href="{{ route('berita.all') }}" class="text-[10px] font-bold text-brand-primary hover:underline">Semua</a>
                    </div>
                    <div class="space-y-4">
                        @foreach($artikels as $artikel)
                            <a href="{{ route('beranda.artikel', $artikel->slug) }}" class="flex gap-3 group">
                                <div class="w-16 h-16 rounded-xl bg-slate-100 overflow-hidden shrink-0 shadow-sm border border-slate-200/50">
                                    @if($artikel->gambar)
                                        <img src="{{ asset('storage/' . $artikel->gambar) }}" alt="{{ $artikel->judul }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                                            <i class="fas fa-image text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">
                                        <i class="far fa-clock mr-1"></i> {{ $artikel->published_at ? $artikel->published_at->format('d M Y') : $artikel->created_at->format('d M Y') }}
                                    </p>
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
                                        {{ $artikel->judul }}
                                    </h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

        </div>

    </div>
</div>

{{-- FOOTER --}}
@include('beranda.sections.footer')

@livewireScripts
</body>
</html>
