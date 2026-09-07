<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $halaman->judul }} - {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="{{ $halaman->deskripsi_singkat ?? 'Informasi akademik dan kurikulum ' . $halaman->judul . ' di ' . ($sekolah?->school_name ?? 'Sekolah') }}">
    
    <!-- Favicon -->
    @php
        $favicon = $sekolah?->school_logo_path ? asset('storage/' . $sekolah->school_logo_path) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ $favicon }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .prose-akademik img {
            max-width: 100%;
            height: auto;
            border-radius: 1rem;
            margin: 1.5rem 0;
            box-shadow: 0 4px 15px -3px rgb(0 0 0 / 0.08);
        }
        .prose-akademik p {
            line-height: 1.8;
            margin-bottom: 1.25rem;
            color: #334155;
            font-size: 1rem;
        }
        .prose-akademik h1, .prose-akademik h2, .prose-akademik h3, .prose-akademik h4 {
            font-weight: 700;
            color: #0f172a;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
        }
        .prose-akademik h2 { font-size: 1.5rem; }
        .prose-akademik h3 { font-size: 1.25rem; }
        .prose-akademik ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-bottom: 1.25rem;
            color: #334155;
        }
        .prose-akademik ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
            margin-bottom: 1.25rem;
            color: #334155;
        }
        .prose-akademik li {
            margin-bottom: 0.5rem;
            line-height: 1.7;
        }
        .prose-akademik a {
            color: var(--color-brand-primary, #059669);
            text-decoration: underline;
            font-weight: 600;
        }
        .prose-akademik table {
            width: 100%;
            margin: 1.5rem 0;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .prose-akademik th, .prose-akademik td {
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            text-align: left;
        }
        .prose-akademik th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #1e293b;
        }
        .prose-akademik blockquote {
            border-left: 4px solid var(--color-brand-primary, #059669);
            padding-left: 1.25rem;
            font-style: italic;
            color: #475569;
            margin: 1.5rem 0;
        }
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

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

{{-- ══════════════════ HEADER SECTION ══════════════════ --}}
<div class="pt-32 pb-16 relative overflow-hidden text-white border-b-4 border-amber-400 bg-gradient-to-br from-brand-primary to-slate-900"
     style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">
    
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-white/70 mb-4 font-medium flex-wrap">
            <a href="{{ route('beranda') }}" class="hover:text-amber-300 transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('beranda.akademik.index') }}" class="hover:text-amber-300 transition-colors">Akademik</a>
            <span>/</span>
            <span class="text-white font-semibold truncate max-w-[200px] sm:max-w-none">{{ $halaman->judul }}</span>
        </nav>

        <div class="max-w-4xl">
            @if($halaman->kategori)
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-4 shadow-sm">
                    <i class="fas fa-tag text-[10px]"></i> {{ $halaman->kategori }}
                </div>
            @endif

            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white mb-4 leading-tight tracking-tight">
                {{ $halaman->judul }}
            </h1>
            <div class="w-20 h-1.5 bg-amber-400 rounded-full mb-4 shadow-sm"></div>

            <div class="flex items-center gap-4 text-xs text-white/80 font-medium">
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-calendar-alt text-amber-300"></i>
                    Diperbarui: {{ $halaman->updated_at->format('d M Y') }}
                </span>
                @if($halaman->file_pdf)
                    <span>&bull;</span>
                    <span class="flex items-center gap-1.5 text-amber-300 font-bold">
                        <i class="fas fa-file-pdf"></i> Lampiran PDF Tersedia
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
<div class="bg-slate-50 py-12 flex-grow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Content -->
            <div class="lg:col-span-8 space-y-6">
                
                @if($halaman->deskripsi_singkat)
                    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border-l-4 border-brand-primary p-5 rounded-2xl shadow-xs">
                        <p class="text-slate-700 text-sm sm:text-base leading-relaxed font-medium">
                            {{ $halaman->deskripsi_singkat }}
                        </p>
                    </div>
                @endif

                <!-- Main Content Card -->
                <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-100">
                    
                    @if($halaman->file_pdf)
                        <div class="mb-8 p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Dokumen Lampiran Resmi</h4>
                                    <p class="text-xs text-slate-500">Unduh dokumen berkas resmi format PDF</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $halaman->file_pdf) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-brand-primary text-white font-bold text-xs hover:opacity-90 shadow-md shadow-brand-primary/20 transition-all">
                                <i class="fas fa-download"></i> Unduh Berkas PDF
                            </a>
                        </div>
                    @endif

                    @if(!empty($halaman->konten))
                        <div class="prose-akademik">
                            {!! $halaman->konten !!}
                        </div>
                    @else
                        @if(!$halaman->file_pdf)
                            <div class="text-center py-12">
                                <i class="fas fa-info-circle text-4xl text-slate-300 mb-3"></i>
                                <p class="text-slate-500 text-sm">Rincian konten akademik ini belum ditambahkan.</p>
                            </div>
                        @endif
                    @endif

                    <!-- Embedded PDF Viewer jika ada berkas PDF -->
                    @if($halaman->file_pdf)
                        <div class="mt-10 pt-8 border-t border-slate-100">
                            <h4 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-file-alt text-brand-primary"></i> Pratinjau Dokumen PDF
                            </h4>
                            <div class="w-full h-[650px] rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100">
                                <iframe src="{{ asset('storage/' . $halaman->file_pdf) }}" class="w-full h-full" frameborder="0"></iframe>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Back Link -->
                <div>
                    <a href="{{ route('beranda.akademik.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-brand-primary hover:underline">
                        <i class="fas fa-arrow-left"></i> Kembali ke Pusat Informasi Akademik
                    </a>
                </div>

            </div>

            <!-- Right Column: Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Dokumen Akademik Lainnya -->
                @if($semuaAkademik->count() > 0)
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                <i class="fas fa-layer-group"></i>
                            </span>
                            Informasi Akademik Lainnya
                        </h4>

                        <div class="space-y-2">
                            @foreach($semuaAkademik as $lainnya)
                                <a href="{{ route('beranda.akademik', $lainnya->slug) }}" class="block p-3 rounded-2xl bg-slate-50 hover:bg-emerald-50 hover:text-brand-primary border border-slate-100 transition-all text-xs font-bold text-slate-700 group">
                                    <div class="flex items-center justify-between">
                                        <span class="line-clamp-1">{{ $lainnya->judul }}</span>
                                        <i class="fas fa-chevron-right text-[10px] text-slate-400 group-hover:translate-x-1 transition-transform ml-2 shrink-0"></i>
                                    </div>
                                    @if($lainnya->kategori)
                                        <span class="inline-block text-[10px] text-slate-400 font-normal mt-0.5">{{ $lainnya->kategori }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quick Card Akses Cepat -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                            <i class="fas fa-compass"></i>
                        </span>
                        Pintasan Terkait
                    </h4>

                    <div class="space-y-2 text-xs font-bold">
                        <a href="{{ url('/presensi') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition-all">
                            <span><i class="fas fa-clock text-amber-500 mr-2"></i> Presensi Siswa</span>
                            <i class="fas fa-arrow-right text-[10px] text-slate-400"></i>
                        </a>
                        <a href="{{ route('siswa.all') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition-all">
                            <span><i class="fas fa-user-graduate text-emerald-500 mr-2"></i> Direktori Siswa</span>
                            <i class="fas fa-arrow-right text-[10px] text-slate-400"></i>
                        </a>
                        <a href="{{ route('guru.all') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition-all">
                            <span><i class="fas fa-chalkboard-teacher text-blue-500 mr-2"></i> Direktori Guru & PTK</span>
                            <i class="fas fa-arrow-right text-[10px] text-slate-400"></i>
                        </a>
                        <a href="{{ url('/perpustakaan') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition-all">
                            <span><i class="fas fa-book-reader text-indigo-500 mr-2"></i> Perpustakaan</span>
                            <i class="fas fa-arrow-right text-[10px] text-slate-400"></i>
                        </a>
                    </div>
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
