<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sekolah?->school_name ?? 'Sekolah' }}</title>
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
    <!-- Alpine.js is loaded via app.js or we can rely on it if it's there. Actually Vite likely bundles it. If not, the navbar uses it so it's loaded. -->
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-primary-light selection:text-brand-primary-dark min-h-screen flex flex-col">

<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

<!-- Hero Section -->
<div class="pt-32 pb-20 relative overflow-hidden text-white border-b-4 border-amber-400 bg-gradient-to-br from-brand-primary to-blue-900">
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-blue-100/70 mb-4 font-medium flex-wrap">
            <a href="{{ route('beranda') }}" class="hover:text-amber-300 transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-white font-semibold">Layanan Publik</span>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-6">
                <i class="fas fa-bullhorn text-amber-300"></i> TRANSARANSI LAYANAN
            </div>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 leading-tight tracking-tight">
                Portal Layanan Publik
            </h1>
            <p class="text-lg text-blue-100 font-medium leading-relaxed max-w-2xl">
                Akses informasi standar pelayanan sekolah, maklumat pelayanan, serta integrasi sistem pengaduan nasional secara transparan dan akuntabel.
            </p>
        </div>
    </div>
</div>

<div class="bg-slate-50 min-h-screen py-16 w-full flex-grow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Accordions (Standar Pelayanan) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Section Header -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/60">
                    <div class="flex items-start gap-4 mb-2">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-primary flex items-center justify-center shrink-0">
                            <i class="fas fa-file-contract text-2xl"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 mb-2 tracking-wider">
                                DOKUMEN RESMI
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Standar Pelayanan Sekolah</h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed mt-4">
                        Berikut adalah dokumen resmi Standar Operasional Prosedur (SOP) dan Standar Pelayanan di lingkungan {{ $sekolah?->school_name ?? 'sekolah kami' }}. Klik pada masing-masing layanan untuk melihat detail informasi dan dokumennya.
                    </p>
                </div>

                <!-- Accordions List -->
                <div class="space-y-4" x-data="{ activeAccordion: null }">
                    @forelse($semuaLayanan as $index => $layanan)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden transition-all duration-300"
                             :class="activeAccordion === {{ $layanan->id }} ? 'ring-2 ring-brand-primary/20 border-brand-primary/30' : 'hover:border-slate-300'">
                            
                            <!-- Accordion Header -->
                            <button @click="activeAccordion = activeAccordion === {{ $layanan->id }} ? null : {{ $layanan->id }}" 
                                    class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 focus:outline-none bg-white">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                         :class="activeAccordion === {{ $layanan->id }} ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-400'">
                                        <i class="fas" :class="activeAccordion === {{ $layanan->id }} ? 'fa-folder-open' : 'fa-folder'"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold transition-colors" :class="activeAccordion === {{ $layanan->id }} ? 'text-brand-primary' : 'text-slate-800'">
                                            {{ $layanan->judul }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            @if($layanan->kategori)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wider">
                                                {{ $layanan->kategori }}
                                            </span>
                                            @endif
                                            @if($layanan->waktu_layanan)
                                            <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                                                <i class="far fa-clock"></i> {{ $layanan->waktu_layanan }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0 w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 transition-transform duration-300"
                                     :class="activeAccordion === {{ $layanan->id }} ? 'rotate-180 bg-brand-50 border-brand-200 text-brand-primary' : ''">
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </div>
                            </button>

                            <!-- Accordion Body -->
                            <div x-show="activeAccordion === {{ $layanan->id }}" x-collapse style="display: none;">
                                <div class="px-6 pb-6 pt-2 border-t border-slate-100 mt-2">
                                    
                                    @if($layanan->deskripsi_singkat)
                                    <div class="prose prose-sm prose-slate max-w-none mb-5 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                        <p class="m-0 text-slate-700 leading-relaxed">{{ $layanan->deskripsi_singkat }}</p>
                                    </div>
                                    @endif

                                    <div class="flex flex-wrap gap-3 text-sm mb-6">
                                        @if($layanan->waktu_layanan)
                                            <div class="flex items-center">
                                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 tracking-wider">
                                                    <i class="far fa-clock text-amber-500 mr-1"></i> Waktu: {{ $layanan->waktu_layanan }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($layanan->biaya)
                                            <div class="flex items-center">
                                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 tracking-wider">
                                                    <i class="fas fa-money-bill-wave text-emerald-500 mr-1"></i> Biaya: {{ $layanan->biaya }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    @if($layanan->file_pdf)
                                        <div class="bg-slate-900 rounded-xl overflow-hidden border border-slate-200 shadow-sm relative h-[600px]">
                                            <object data="{{ asset('storage/' . $layanan->file_pdf) }}" type="application/pdf" class="w-full h-full border-0 rounded-xl z-10 relative bg-white">
                                                <div class="absolute inset-0 flex flex-col items-center justify-center -z-10 bg-slate-100 text-slate-500 p-8 text-center">
                                                    <i class="fas fa-file-pdf text-4xl mb-3 text-brand-primary"></i>
                                                    <p class="text-sm font-semibold text-slate-800 mb-1">Browser Anda tidak mendukung pratinjau PDF.</p>
                                                    <p class="text-xs">Silakan unduh dokumen untuk membacanya.</p>
                                                    <a href="{{ asset('storage/' . $layanan->file_pdf) }}" download class="mt-4 px-4 py-2 rounded-lg bg-brand-primary text-white text-xs font-bold inline-block">Unduh PDF</a>
                                                </div>
                                            </object>
                                        </div>
                                        <div class="mt-4 flex justify-end">
                                            <a href="{{ asset('storage/' . $layanan->file_pdf) }}" download class="inline-flex items-center gap-2 text-sm font-bold text-brand-primary hover:text-brand-secondary transition-colors">
                                                <i class="fas fa-download"></i> Unduh File SOP
                                            </a>
                                        </div>
                                    @else
                                        <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 text-slate-500">
                                            <i class="fas fa-file-pdf text-3xl mb-3 text-slate-300"></i>
                                            <p class="text-sm font-medium">Dokumen SOP belum diunggah.</p>
                                        </div>
                                    @endif

                                    @if($layanan->konten)
                                        <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                                            <a href="{{ route('beranda.layanan', $layanan->slug) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 text-sm font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-primary transition-colors">
                                                Lihat Detail Selengkapnya <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center bg-white rounded-2xl shadow-sm border border-slate-200/60">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                                <i class="fas fa-folder-open text-2xl"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Belum ada Standar Pelayanan</h3>
                            <p class="text-sm text-slate-500 mt-2">Data informasi layanan sedang dalam proses pembaharuan.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Survei Kepuasan Banner (Mock/Optional) -->
                <div class="bg-gradient-to-r from-blue-600 to-brand-primary rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden mt-8">
                    <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-1/4 translate-y-1/4">
                        <i class="fas fa-star text-9xl"></i>
                    </div>
                    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white mb-2 tracking-wider">
                                PELAYANAN
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold mb-2">Survei Kepuasan Masyarakat</h3>
                            <p class="text-blue-100 text-sm max-w-md">Berikan penilaian Anda terhadap pelayanan kami dan lihat Indeks Kepuasan Masyarakat pada halaman khusus survei.</p>
                        </div>
                        <div class="shrink-0">
                            @if($setting?->link_survei_kepuasan)
                                <a href="{{ $setting->link_survei_kepuasan }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white text-sm font-bold text-brand-primary hover:bg-slate-50 transition-colors shadow-sm">
                                    <i class="fas fa-clipboard-list"></i> Isi Survei
                                </a>
                            @else
                                <button disabled class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white/50 text-sm font-bold text-brand-primary/50 cursor-not-allowed shadow-sm">
                                    <i class="fas fa-clipboard-list"></i> Belum Tersedia
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- FAQ Sekolah Banner Card (Sesuai Referensi) --}}
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80 mt-6 relative overflow-hidden group hover:border-brand-primary/40 hover:shadow-md transition-all">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand-primary flex items-center justify-center shrink-0 text-2xl group-hover:scale-110 transition-transform">
                                <i class="far fa-question-circle"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 uppercase tracking-wide">FAQ SEKOLAH</h3>
                                <p class="text-slate-500 text-sm leading-relaxed mt-1 max-w-xl">
                                    Temukan jawaban cepat tentang SPMB, layanan administrasi, kegiatan siswa, dan fasilitas sekolah.
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0 w-full sm:w-auto">
                            <a href="{{ route('beranda.faq') }}" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-50 text-brand-primary hover:bg-brand-primary hover:text-white text-xs font-bold uppercase tracking-wider transition-all shadow-xs">
                                BUKA FAQ <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sidebar (Visi Misi, Maklumat, Pengaduan & Lapor) -->
            <div class="lg:col-span-4 space-y-6">

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
</div>
@include('beranda.sections.footer')
@livewireScripts
</body>
</html>
