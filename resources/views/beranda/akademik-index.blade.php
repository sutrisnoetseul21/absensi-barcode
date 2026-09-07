<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akademik - {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
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
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-primary-light selection:text-brand-primary-dark min-h-screen flex flex-col">

<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

<!-- Hero Section -->
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
            <span class="text-white font-semibold">Akademik</span>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-5 shadow-sm">
                <i class="fas fa-graduation-cap text-amber-300"></i> INFORMASI AKADEMIK & KURIKULUM
            </div>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 leading-tight tracking-tight text-white">
                Pusat Informasi Akademik
            </h1>
            <div class="w-24 h-1.5 bg-amber-400 rounded-full mb-4 shadow-sm"></div>
            <p class="text-base sm:text-lg text-white/80 font-medium leading-relaxed max-w-2xl">
                Akses resmi struktur kurikulum, kalender akademik, jadwal pembelajaran (KBM), serta regulasi dan panduan belajar di {{ $sekolah?->school_name ?? 'Sekolah' }}.
            </p>
        </div>
    </div>
</div>

<div class="bg-slate-50 min-h-screen py-16 w-full flex-grow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Accordions (Dokumen Akademik) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Section Header -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/60">
                    <div class="flex items-start gap-4 mb-2">
                        <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0 text-xl shadow-sm">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 mb-1.5 tracking-wider uppercase">
                                Dokumen & Panduan
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Program & Regulasi Akademik</h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed mt-3">
                        Berikut adalah dokumen kurikulum, jadwal, dan panduan belajar yang berlaku di {{ $sekolah?->school_name ?? 'sekolah kami' }}. Klik pada masing-masing judul untuk membaca informasi detail atau mengunduh berkas resmi.
                    </p>
                </div>

                <!-- Accordions List -->
                <div class="space-y-4" x-data="{ activeAccordion: {{ $semuaAkademik->first()?->id ?? 'null' }} }">
                    @forelse($semuaAkademik as $index => $akademik)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden transition-all duration-300"
                             :class="activeAccordion === {{ $akademik->id }} ? 'ring-2 ring-brand-primary/20 border-brand-primary/30' : 'hover:border-slate-300'">
                            
                            <!-- Accordion Header -->
                            <button @click="activeAccordion = activeAccordion === {{ $akademik->id }} ? null : {{ $akademik->id }}" 
                                    class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 focus:outline-none bg-white">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                         :class="activeAccordion === {{ $akademik->id }} ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-slate-100 text-slate-400'">
                                        <i class="fas" :class="activeAccordion === {{ $akademik->id }} ? 'fa-folder-open' : 'fa-folder'"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold transition-colors" :class="activeAccordion === {{ $akademik->id }} ? 'text-brand-primary' : 'text-slate-800'">
                                            {{ $akademik->judul }}
                                        </h3>
                                        @if($akademik->kategori)
                                            <span class="inline-block mt-0.5 text-[11px] font-semibold text-slate-400">
                                                Kategori: <span class="text-brand-primary font-bold">{{ $akademik->kategori }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 shrink-0 transition-transform duration-300"
                                     :class="activeAccordion === {{ $akademik->id }} ? 'rotate-180 bg-brand-primary/10 text-brand-primary' : ''">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </button>

                            <!-- Accordion Body -->
                            <div x-show="activeAccordion === {{ $akademik->id }}" 
                                 x-collapse
                                 class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50">
                                
                                @if($akademik->deskripsi_singkat)
                                    <p class="text-sm text-slate-600 leading-relaxed mb-5">
                                        {{ $akademik->deskripsi_singkat }}
                                    </p>
                                @endif

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap items-center gap-3 pt-2">
                                    <a href="{{ route('beranda.akademik', $akademik->slug) }}" 
                                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-primary text-white text-xs font-bold hover:opacity-90 shadow-md shadow-brand-primary/20 transition-all">
                                        <i class="fas fa-external-link-alt text-[10px]"></i> Buka Halaman Lengkap
                                    </a>

                                    @if($akademik->file_pdf)
                                        <a href="{{ asset('storage/' . $akademik->file_pdf) }}" 
                                           target="_blank"
                                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 hover:text-brand-primary hover:border-brand-primary transition-all shadow-sm">
                                            <i class="fas fa-file-pdf text-rose-500"></i> Unduh Berkas Resmi (PDF)
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/60 shadow-sm">
                            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-4 text-2xl">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Dokumen Akademik</h3>
                            <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto">
                                Dokumen kurikulum dan informasi akademik sedang dalam proses penyusunan dan publikasi oleh tim kurikulum sekolah.
                            </p>
                        </div>
                    @endforelse
                </div>

            </div>

            <!-- Right Column: Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Quick Card: Tahun Ajaran & Presensi -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
                    <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                            <i class="fas fa-clock"></i>
                        </span>
                        Akses Cepat Akademik
                    </h4>

                    <div class="space-y-2.5">
                        <a href="{{ route('beranda.microsite') }}" class="flex items-center justify-between p-3 rounded-2xl bg-violet-50/70 hover:bg-violet-100/70 hover:text-violet-800 border border-violet-100 transition-all text-xs font-bold text-violet-800 group">
                            <div class="flex items-center gap-2.5">
                                <i class="fas fa-globe text-violet-600 w-4"></i>
                                <span>Microsite Sekolah & Guru</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-violet-400 group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        <a href="{{ url('/presensi') }}" class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 hover:bg-emerald-50 hover:text-brand-primary border border-slate-100 transition-all text-xs font-bold text-slate-700 group">
                            <div class="flex items-center gap-2.5">
                                <i class="fas fa-clock text-amber-500 w-4"></i>
                                <span>Dashboard Presensi Publik</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-slate-400 group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        <a href="{{ url('/perpustakaan') }}" class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 hover:bg-emerald-50 hover:text-brand-primary border border-slate-100 transition-all text-xs font-bold text-slate-700 group">
                            <div class="flex items-center gap-2.5">
                                <i class="fas fa-book-reader text-indigo-500 w-4"></i>
                                <span>Katalog Perpustakaan</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-slate-400 group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        <a href="{{ route('guru.all') }}" class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 hover:bg-emerald-50 hover:text-brand-primary border border-slate-100 transition-all text-xs font-bold text-slate-700 group">
                            <div class="flex items-center gap-2.5">
                                <i class="fas fa-chalkboard-teacher text-blue-500 w-4"></i>
                                <span>Direktori Guru & PTK</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-slate-400 group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        <a href="{{ route('siswa.all') }}" class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 hover:bg-emerald-50 hover:text-brand-primary border border-slate-100 transition-all text-xs font-bold text-slate-700 group">
                            <div class="flex items-center gap-2.5">
                                <i class="fas fa-user-graduate text-emerald-500 w-4"></i>
                                <span>Direktori Siswa</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-slate-400 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>

                <!-- Card: Konsultasi & Layanan Kurikulum -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-3xl p-6 shadow-md border border-slate-700 relative overflow-hidden">
                    <div class="absolute -bottom-8 -right-8 w-32 h-32 rounded-full bg-brand-primary/20 blur-2xl pointer-events-none"></div>
                    
                    <span class="inline-block px-3 py-1 rounded-full bg-white/10 text-amber-300 font-bold text-[10px] uppercase tracking-wider mb-3">
                        Layanan Akademik
                    </span>
                    <h4 class="text-base font-bold text-white mb-2">Konsultasi Kurikulum & Siswa</h4>
                    <p class="text-xs text-slate-300 leading-relaxed mb-4">
                        Memerlukan informasi lebih lanjut mengenai kurikulum, program pengajaran, atau legalisir dokumen pembelajaran?
                    </p>
                    <a href="{{ route('beranda.layanan.index') }}" class="inline-flex items-center gap-2 w-full justify-center py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs transition-all shadow-sm">
                        <span>Buka Layanan Publik</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

@livewireScripts
</body>
</html>
