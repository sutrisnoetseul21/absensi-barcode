<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="Informasi dan panduan alur {{ $halaman->judul }} di {{ $sekolah?->school_name ?? 'Sekolah' }}.">
    
    <!-- Favicon -->
    @php
        $favicon = $sekolah?->school_logo_path ? asset('storage/' . $sekolah->school_logo_path) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ $favicon }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .prose-layanan img {
            max-width: 100%;
            height: auto;
            border-radius: 1rem;
            margin: 1.5rem 0;
            box-shadow: 0 4px 15px -3px rgb(0 0 0 / 0.08);
        }
        .prose-layanan p {
            line-height: 1.8;
            margin-bottom: 1.25rem;
            color: #334155;
            font-size: 1rem;
        }
        .prose-layanan h1, .prose-layanan h2, .prose-layanan h3, .prose-layanan h4 {
            font-weight: 700;
            color: #0f172a;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
        }
        .prose-layanan h2 { font-size: 1.5rem; }
        .prose-layanan h3 { font-size: 1.25rem; }
        .prose-layanan ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-bottom: 1.25rem;
            color: #334155;
        }
        .prose-layanan ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
            margin-bottom: 1.25rem;
            color: #334155;
        }
        .prose-layanan li {
            margin-bottom: 0.5rem;
            line-height: 1.7;
        }
        .prose-layanan a {
            color: var(--color-brand-primary, #059669);
            text-decoration: underline;
            font-weight: 600;
        }
        .prose-layanan table {
            width: 100%;
            margin: 1.5rem 0;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .prose-layanan th, .prose-layanan td {
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            text-align: left;
        }
        .prose-layanan th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #1e293b;
        }
        .prose-layanan blockquote {
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
<div class="pt-32 pb-20 relative overflow-hidden text-white border-b-4 border-amber-400"
     style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">
    
    {{-- Background Geometric Elements --}}
    <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-white/5 blur-2xl pointer-events-none"></div>
    <div class="absolute -left-20 -top-20 w-80 h-80 rounded-full bg-amber-400/10 blur-2xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-emerald-100/70 mb-4 font-medium flex-wrap">
            <a href="{{ route('beranda') }}" class="hover:text-amber-300 transition-colors">Beranda</a>
            <span>/</span>
            <span>Layanan Publik</span>
            <span>/</span>
            <span class="text-white font-semibold truncate max-w-xs">{{ $halaman->judul }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-3">
                    <i class="fas fa-file-alt"></i> Alur & Informasi Layanan
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                    {{ $halaman->judul }}
                </h1>
                <p class="text-emerald-100/90 text-sm mt-2 flex items-center gap-2">
                    <i class="far fa-calendar-alt text-amber-300"></i>
                    Diperbarui: {{ $halaman->updated_at->translatedFormat('d F Y') }}
                </p>
            </div>
            
            <div class="shrink-0">
                <a href="{{ route('pengaduan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold text-xs uppercase tracking-wider transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-headset"></i> Layanan Pengaduan
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full flex-grow">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Konten Utama (8 Kolom) --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-100">
                {{-- Metadata Layanan --}}
                <div class="mb-8 bg-slate-50 rounded-2xl p-5 border border-slate-100 flex flex-col gap-4">
                    @if($halaman->deskripsi_singkat)
                        <p class="text-slate-700 text-sm leading-relaxed m-0">{{ $halaman->deskripsi_singkat }}</p>
                    @endif
                    <div class="flex flex-wrap gap-3 text-sm">
                        @if($halaman->kategori)
                            <div class="flex items-center">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 uppercase tracking-wider">
                                    <i class="fas fa-tag text-brand-primary mr-1"></i> {{ $halaman->kategori }}
                                </span>
                            </div>
                        @endif
                        @if($halaman->waktu_layanan)
                            <div class="flex items-center">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 tracking-wider">
                                    <i class="far fa-clock text-amber-500 mr-1"></i> Waktu: {{ $halaman->waktu_layanan }}
                                </span>
                            </div>
                        @endif
                        @if($halaman->biaya)
                            <div class="flex items-center">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 tracking-wider">
                                    <i class="fas fa-money-bill-wave text-emerald-500 mr-1"></i> Biaya: {{ $halaman->biaya }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                @if(!empty(trim(strip_tags($halaman->konten, '<img><iframe>'))))
                    <div class="prose-layanan">
                        {!! $halaman->konten !!}
                    </div>
                @else
                    <div class="text-center py-16">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mb-1">Konten Belum Diisi</h3>
                        <p class="text-sm text-slate-500 max-w-md mx-auto">
                            Panduan dan alur pelayanan untuk halaman ini sedang dalam tahap penyusunan oleh pihak sekolah.
                        </p>
                    </div>
                @endif

                {{-- PDF Viewer --}}
                @if($halaman->file_pdf)
                    <div class="mt-10 pt-8 border-t border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-file-pdf text-rose-500"></i> Dokumen SOP
                        </h3>
                        <div class="bg-slate-900 rounded-xl overflow-hidden border border-slate-200 shadow-sm relative h-[600px]">
                            <object data="{{ asset('storage/' . $halaman->file_pdf) }}" type="application/pdf" class="w-full h-full border-0 rounded-xl z-10 relative bg-white">
                                <div class="absolute inset-0 flex flex-col items-center justify-center -z-10 bg-slate-100 text-slate-500 p-8 text-center">
                                    <i class="fas fa-file-pdf text-4xl mb-3 text-brand-primary"></i>
                                    <p class="text-sm font-semibold text-slate-800 mb-1">Browser Anda tidak mendukung pratinjau PDF.</p>
                                    <p class="text-xs">Silakan unduh dokumen untuk membacanya.</p>
                                    <a href="{{ asset('storage/' . $halaman->file_pdf) }}" download class="mt-4 px-4 py-2 rounded-lg bg-brand-primary text-white text-xs font-bold inline-block">Unduh PDF</a>
                                </div>
                            </object>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <a href="{{ asset('storage/' . $halaman->file_pdf) }}" download class="inline-flex items-center gap-2 text-sm font-bold text-brand-primary hover:text-brand-secondary transition-colors">
                                <i class="fas fa-download"></i> Unduh File SOP
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Bottom Action Bar --}}
                <div class="mt-10 pt-8 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        <p class="font-semibold text-slate-700">Ada kendala atau pertanyaan seputar layanan ini?</p>
                        <p class="text-slate-400 mt-0.5">Sampaikan pesan langsung melalui saluran resmi aspirasi & pengaduan sekolah.</p>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        <a href="{{ route('pengaduan.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white text-xs font-bold transition-colors shadow-sm">
                            <i class="fas fa-paper-plane"></i> Kirim Aspirasi
                        </a>
                        <button onclick="window.history.back()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- Sidebar (4 Kolom) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Widget: Layanan Publik Lainnya --}}
            @if($semuaLayanan->isNotEmpty())
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <i class="fas fa-list-ul text-brand-primary"></i> Layanan Publik Lainnya
                    </h3>
                    <div class="space-y-2">
                        @foreach($semuaLayanan as $other)
                            <a href="{{ route('beranda.layanan', $other->slug) }}" class="flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-brand-primary flex items-center justify-center shrink-0 text-xs group-hover:scale-105 transition-transform">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-primary transition-colors line-clamp-1">
                                        {{ $other->judul }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400">Lihat panduan & alur &rarr;</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 1. Visi & Misi Pelayanan -->
            @if($setting?->gambar_visi_misi_pelayanan)
            <div class="bg-white rounded-2xl p-1 shadow-sm border border-slate-200/60 overflow-hidden group">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-bullseye text-brand-primary"></i> VISI & MISI PELAYANAN
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-brand-primary tracking-wider">KOMITMEN</span>
                </div>
                <a href="{{ asset('storage/' . $setting->gambar_visi_misi_pelayanan) }}" target="_blank" class="block relative overflow-hidden bg-slate-100">
                    <img src="{{ asset('storage/' . $setting->gambar_visi_misi_pelayanan) }}" alt="Visi Misi Pelayanan" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm">
                        <span class="text-white font-bold text-sm bg-white/20 px-4 py-2 rounded-full backdrop-blur-md border border-white/30">
                            <i class="fas fa-search-plus mr-1"></i> Perbesar Gambar
                        </span>
                    </div>
                </a>
            </div>
            @endif

            <!-- 2. Maklumat Pelayanan -->
            @if($setting?->gambar_maklumat_pelayanan)
            <div class="bg-white rounded-2xl p-1 shadow-sm border border-slate-200/60 overflow-hidden group">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-certificate text-brand-primary"></i> MAKLUMAT PELAYANAN
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 tracking-wider">RESMI</span>
                </div>
                <a href="{{ asset('storage/' . $setting->gambar_maklumat_pelayanan) }}" target="_blank" class="block relative overflow-hidden bg-slate-100">
                    <img src="{{ asset('storage/' . $setting->gambar_maklumat_pelayanan) }}" alt="Maklumat Pelayanan" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm">
                        <span class="text-white font-bold text-sm bg-white/20 px-4 py-2 rounded-full backdrop-blur-md border border-white/30">
                            <i class="fas fa-search-plus mr-1"></i> Perbesar Gambar
                        </span>
                    </div>
                </a>
            </div>
            @endif

            <!-- 3. Pusat Aspirasi, Pengaduan & Kanal Lapor -->
            <div class="space-y-4">
                {{-- Widget Utama: Layanan Aspirasi & Pengaduan Internal Sekolah --}}
                <div class="bg-gradient-to-br from-brand-primary to-brand-secondary rounded-3xl p-6 text-white shadow-lg shadow-brand-primary/20 relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center mb-4 text-white text-base">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4 class="text-base font-extrabold mb-1">Layanan Aspirasi & Pengaduan</h4>
                    <p class="text-emerald-100/90 text-xs mb-5 leading-relaxed">
                        Sampaikan masukan, pengaduan, atau aspirasi Anda secara transparan dan aman melalui sistem layanan sekolah kami.
                    </p>
                    <a href="{{ route('pengaduan.index') }}" class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-white text-slate-900 hover:bg-amber-300 font-bold text-xs uppercase tracking-wider transition-colors shadow-sm">
                        <i class="fas fa-comment-dots text-brand-primary"></i> Buat Laporan Pengaduan
                    </a>
                </div>

                {{-- Kanal Pengaduan & Lapor Eksternal Terintegrasi --}}
                @if($setting?->link_sp4n_lapor || $setting?->link_pengaduan_daerah || $setting?->link_cariyanlik)
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/60">
                    <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100">
                        <i class="fas fa-network-wired text-slate-400 text-xs"></i>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kanal Lapor & Pengawasan Resmi</span>
                    </div>

                    <div class="space-y-2.5">
                        @if($setting?->link_sp4n_lapor)
                        <a href="{{ $setting->link_sp4n_lapor }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-rose-50 border border-slate-200/70 hover:border-rose-200 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-bullhorn text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nasional (KemenPAN-RB)</div>
                                    <h5 class="text-xs font-bold text-slate-800 group-hover:text-rose-600 transition-colors">SP4N-LAPOR!</h5>
                                </div>
                            </div>
                            <i class="fas fa-external-link-alt text-slate-400 group-hover:text-rose-600 text-xs transition-colors"></i>
                        </a>
                        @endif

                        @if($setting?->link_pengaduan_daerah)
                        <a href="{{ $setting->link_pengaduan_daerah }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200/70 hover:border-amber-200 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-comments text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pemerintah Daerah</div>
                                    <h5 class="text-xs font-bold text-slate-800 group-hover:text-amber-600 transition-colors">Halo Pengaduan Daerah</h5>
                                </div>
                            </div>
                            <i class="fas fa-external-link-alt text-slate-400 group-hover:text-amber-600 text-xs transition-colors"></i>
                        </a>
                        @endif

                        @if($setting?->link_cariyanlik)
                        <a href="{{ $setting->link_cariyanlik }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200/70 hover:border-blue-200 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-globe-asia text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Katalog SIPPN</div>
                                    <h5 class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Cariyanlik Pelayanan</h5>
                                </div>
                            </div>
                            <i class="fas fa-external-link-alt text-slate-400 group-hover:text-blue-600 text-xs transition-colors"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>

        </div>

    </div>
</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

@livewireScripts
</body>
</html>
