<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Microsite Sekolah & Guru - {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
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
            }
        </style>
    @endif
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-primary-light selection:text-brand-primary-dark min-h-screen flex flex-col"
      x-data="{
          search: '{{ addslashes($search ?? '') }}',
          filter: '{{ $filter ?? 'semua' }}'
      }">

<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

<!-- Hero Section (Mengikuti Thema Sekolah) -->
<div class="pt-32 pb-20 relative overflow-hidden text-white border-b-4 border-amber-400 bg-gradient-to-br from-brand-primary to-slate-900"
     style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">
    
    {{-- Decorative Background Glow --}}
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-white/70 mb-4 font-medium flex-wrap">
            <a href="{{ route('beranda') }}" class="hover:text-amber-300 transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('beranda.akademik.index') }}" class="hover:text-amber-300 transition-colors">Akademik</a>
            <span>/</span>
            <span class="text-white font-semibold">Microsite Sekolah</span>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-5 shadow-sm">
                <i class="fas fa-globe text-amber-300"></i> HUB DIGITAL & MATERI PEMBELAJARAN
            </div>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 leading-tight tracking-tight text-white">
                Microsite Sekolah & Guru
            </h1>
            <div class="w-24 h-1.5 bg-amber-400 rounded-full mb-4 shadow-sm"></div>
            <p class="text-base sm:text-lg text-white/80 font-medium leading-relaxed max-w-2xl">
                Akses cepat tautan situs resmi, modul kurikulum interaktif, Google Sites, materi presentasi Canva, serta portofolio pembelajaran digital dewan guru {{ $sekolah?->school_name ?? 'Sekolah' }}.
            </p>
        </div>
    </div>
</div>

<main class="bg-slate-50 min-h-screen py-16 w-full flex-grow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

        {{-- ======================================================== --}}
        {{-- SECTION 1: MICROSITE RESMI SEKOLAH                       --}}
        {{-- ======================================================== --}}
        <section id="microsite-sekolah">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20 mb-2">
                        <i class="fas fa-school"></i>
                        <span>PORTAL RESMI LEMBAGA</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Microsite Resmi Sekolah
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                        Tautan layanan publik, modul program sekolah, pengumuman khusus, dan media informasi digital interaktif {{ $sekolah?->school_name ?? 'sekolah' }}.
                    </p>
                </div>
            </div>

            @if($micrositesSekolah->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($micrositesSekolah as $site)
                        <div class="group bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-brand-primary/40 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
                            {{-- Top Accent Bar --}}
                            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-brand-primary via-emerald-400 to-amber-400 group-hover:h-2 transition-all"></div>

                            <div>
                                <div class="flex items-center justify-between gap-3 mb-5">
                                    <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary group-hover:bg-brand-primary group-hover:text-white flex items-center justify-center text-xl transition-all duration-300 shadow-sm">
                                        <i class="{{ $site->icon ?: 'fas fa-globe' }}"></i>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 group-hover:bg-brand-primary/10 group-hover:text-brand-primary transition-colors">
                                        {{ $site->kategori ?: 'Utama' }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-brand-primary transition-colors">
                                    {{ $site->judul }}
                                </h3>

                                <p class="text-xs text-slate-500 leading-relaxed line-clamp-3 mb-6">
                                    {{ $site->deskripsi ?: 'Kunjungi tautan microsite resmi untuk mengakses informasi lengkap dan media interaktif sekolah.' }}
                                </p>
                            </div>

                            <div class="pt-4 border-t border-slate-100">
                                <a href="{{ $site->url }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center gap-2 w-full py-3 px-4 rounded-2xl bg-brand-primary hover:bg-brand-secondary text-white text-xs font-bold shadow-md shadow-brand-primary/20 group-hover:shadow-lg transition-all">
                                    <span>{{ $site->button_text ?: 'Kunjungi Microsite' }}</span>
                                    <i class="fas fa-external-link-alt text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-3xl p-10 text-center border border-slate-200/80">
                    <div class="w-16 h-16 rounded-3xl bg-brand-primary/10 text-brand-primary flex items-center justify-center text-2xl mx-auto mb-3">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1">Belum Ada Microsite Sekolah</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Tautan microsite resmi sekolah sedang dipersiapkan dan dapat ditambahkan sewaktu-waktu melalui Portal Web.
                    </p>
                </div>
            @endif
        </section>

        {{-- ======================================================== --}}
        {{-- SECTION 2: DIREKTORI MICROSITE GURU                      --}}
        {{-- ======================================================== --}}
        <section id="microsite-guru" class="pt-8 border-t border-slate-200/80">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200 mb-2">
                        <i class="fas fa-chalkboard-teacher text-amber-700"></i>
                        <span>RUANG BELAJAR DIGITAL</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Direktori Microsite Guru
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                        Kunjungi modul belajar, Google Sites, Canva, dan portofolio pembelajaran digital yang disusun mandiri oleh dewan guru untuk siswa.
                    </p>
                </div>

                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-xs self-start md:self-auto">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span><strong>{{ $totalGuruMicrosite }}</strong> dari {{ $gurus->count() }} guru memiliki microsite</span>
                </div>
            </div>

            {{-- Filter & Search Toolbar --}}
            <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-sm mb-8 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                {{-- Search Box --}}
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" x-model="search"
                           placeholder="Cari nama guru atau mata pelajaran..." 
                           class="w-full pl-11 pr-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-medium text-slate-800 focus:outline-none focus:bg-white focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-all">
                </div>

                {{-- Filter Buttons --}}
                <div class="flex items-center gap-2 bg-slate-100/80 p-1.5 rounded-2xl shrink-0">
                    <button type="button" @click="filter = 'semua'"
                            :class="filter === 'semua' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition-all">
                        Semua Guru ({{ $gurus->count() }})
                    </button>
                    <button type="button" @click="filter = 'ada'"
                            :class="filter === 'ada' ? 'bg-brand-primary text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-1.5">
                        <i class="fas fa-link text-[10px]"></i>
                        <span>Ada Microsite ({{ $totalGuruMicrosite }})</span>
                    </button>
                </div>
            </div>

            {{-- Teacher Cards Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($gurus as $guru)
                    @php
                        $hasMicrosite = !empty($guru->microsite_url);
                        $mapelList = $guru->mapel_aktif;
                    @endphp

                    <div x-show="(filter === 'semua' || (filter === 'ada' && {{ $hasMicrosite ? 'true' : 'false' }})) && 
                                (!search || '{{ strtolower(addslashes($guru->name)) }}'.includes(search.toLowerCase()) || 
                                 '{{ strtolower(addslashes(implode(' ', $mapelList))) }}'.includes(search.toLowerCase()))"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="bg-white rounded-3xl p-6 border {{ $hasMicrosite ? 'border-slate-200/80 hover:border-brand-primary/40 shadow-sm hover:shadow-xl' : 'border-slate-200/50 opacity-80' }} transition-all duration-300 flex flex-col justify-between group">
                        
                        <div>
                            {{-- Avatar & Badge Row --}}
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="relative">
                                    <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-100 border-2 {{ $hasMicrosite ? 'border-brand-primary/30 group-hover:border-brand-primary' : 'border-slate-200' }} transition-colors shadow-sm">
                                        @if($guru->photo_path)
                                            <img src="{{ asset('storage/' . $guru->photo_path) }}" alt="{{ $guru->name }}" class="w-full h-full object-cover">
                                        @else
                                            <img src="{{ $guru->jenis_kelamin === 'P' ? asset('images/avatar-f.svg') : asset('images/avatar-m.svg') }}" alt="{{ $guru->name }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    @if($hasMicrosite)
                                        <span class="absolute -bottom-1.5 -right-1.5 w-5 h-5 rounded-full bg-brand-primary text-white flex items-center justify-center text-[10px] border-2 border-white shadow-xs" title="Microsite Aktif">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    @endif
                                </div>

                                @if($hasMicrosite)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                        <i class="fas fa-laptop-code"></i> Microsite
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Belum Ada
                                    </span>
                                @endif
                            </div>

                            {{-- Name --}}
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-brand-primary transition-colors line-clamp-1 mb-2">
                                {{ $guru->name }}
                            </h3>

                            {{-- Mapel / Jabatan Badges --}}
                            <div class="flex flex-wrap gap-1.5 mb-4 min-h-[22px]">
                                @if(!empty($mapelList))
                                    @foreach(array_slice($mapelList, 0, 2) as $mp)
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $mp }}
                                        </span>
                                    @endforeach
                                    @if(count($mapelList) > 2)
                                        <span class="px-1.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-500">
                                            +{{ count($mapelList) - 2 }}
                                        </span>
                                    @endif
                                @elseif(!empty($guru->semua_jabatan))
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $guru->semua_jabatan[0] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-3 border-t border-slate-100">
                            @if($hasMicrosite)
                                <a href="{{ $guru->microsite_url }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-3.5 rounded-xl bg-brand-primary/10 hover:bg-brand-primary text-brand-primary hover:text-white text-xs font-bold border border-brand-primary/20 hover:border-brand-primary transition-all shadow-xs group-hover:shadow-md">
                                    <span>Kunjungi Microsite</span>
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            @else
                                <button disabled 
                                        class="inline-flex items-center justify-center gap-1.5 w-full py-2.5 px-3.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed">
                                    <i class="fas fa-hourglass-start text-[10px]"></i>
                                    <span>Tautan Belum Diatur</span>
                                </button>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        </section>

    </div>
</main>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

@livewireScripts
</body>
</html>
