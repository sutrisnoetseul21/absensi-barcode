<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pengaduanSetting->banner_title ?? 'Layanan Aspirasi & Pengaduan' }} — {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="{{ $pengaduanSetting->banner_text ?? 'Layanan Aspirasi dan Pengaduan Online Sekolah' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

{{-- ══════════════════ HEADER SECTION ══════════════════ --}}
<div class="pt-32 pb-20 relative overflow-hidden text-white border-b-4 border-amber-400"
     style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">
    
    {{-- Decorative Background Elements --}}
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <!-- Breadcrumb -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-4 shadow-sm">
            <a href="{{ route('beranda') }}" class="text-white hover:text-amber-300 transition-colors">Beranda</a>
            <span class="text-white/40">/</span>
            <span>Layanan Publik</span>
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-white mb-3 tracking-tight">
            {{ $pengaduanSetting->banner_title ?? 'Layanan Aspirasi & Pengaduan' }}
        </h1>
        <div class="w-24 h-1.5 bg-amber-400 mx-auto rounded-full mb-4 shadow-sm"></div>
        
        <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto leading-relaxed font-medium">
            {{ $pengaduanSetting->banner_text ?? 'Punya saran, kritik, aspirasi, atau laporan? Sampaikan kepada kami dengan mudah, cepat, dan aman.' }}
        </p>
    </div>
</div>

{{-- ══════════════════ FORM SECTION ══════════════════ --}}
<div class="container mx-auto px-4 py-12 flex-grow max-w-4xl relative -mt-8 z-20">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        
        {{-- Card Header --}}
        <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-50 to-emerald-50/40 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fas fa-bullhorn text-amber-600"></i> Suara Anda Berharga
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">
                    Formulir {{ $pengaduanSetting->module_name ?? 'Pengaduan' }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Silakan isi formulir di bawah ini secara lengkap. Data Anda akan dijaga kerahasiaannya.
                </p>
            </div>
            <div class="hidden sm:flex w-14 h-14 rounded-2xl bg-white shadow-md border border-slate-100 items-center justify-center text-2xl text-amber-500">
                <i class="fas fa-paper-plane"></i>
            </div>
        </div>

        {{-- Card Body --}}
        <div class="p-6 sm:p-10">
            
            {{-- Flash Alert Sukses --}}
            @if(session('success'))
                <div class="mb-8 p-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-start gap-4 shadow-sm animate-fade-in">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-lg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-emerald-900 text-sm">Laporan Berhasil Terkirim!</h4>
                        <p class="text-xs text-emerald-700 mt-1 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Flash Alert Error Validasi --}}
            @if($errors->any())
                <div class="mb-8 p-5 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-start gap-4 shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 text-lg">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-rose-900 text-sm">Mohon Lengkapi Formulir</h4>
                        <ul class="list-disc pl-4 text-xs text-rose-700 mt-1 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($pengaduanSetting->is_active)
                <form action="{{ route('pengaduan.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    {{-- Honeypot Anti-Bot Spam --}}
                    <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        
                        {{-- Nama Lengkap --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fas fa-user text-xs"></i>
                                </span>
                                <input type="text" name="nama" value="{{ old('nama') }}" required
                                       placeholder="Contoh: Budi Santoso / Orang Tua Siswa"
                                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/40 focus:border-brand-primary transition">
                            </div>
                        </div>

                        {{-- Alamat Email --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fas fa-envelope text-xs"></i>
                                </span>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       placeholder="nama@email.com"
                                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/40 focus:border-brand-primary transition">
                            </div>
                        </div>

                        {{-- No HP / WhatsApp --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                No. HP / WhatsApp <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fab fa-whatsapp text-xs"></i>
                                </span>
                                <input type="tel" name="no_hp" value="{{ old('no_hp') }}"
                                       placeholder="08123456789"
                                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/40 focus:border-brand-primary transition">
                            </div>
                        </div>

                        {{-- Kategori Pengaduan --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Kategori {{ $pengaduanSetting->module_name ?? 'Pengaduan' }} <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 pointer-events-none">
                                    <i class="fas fa-tag text-xs"></i>
                                </span>
                                <select name="pengaduan_kategori_id" required
                                        class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/40 focus:border-brand-primary transition appearance-none">
                                    <option value="">-- Pilih Jenis Kategori --</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('pengaduan_kategori_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 pointer-events-none">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                        </div>

                        {{-- Isi Pengaduan / Pesan --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Isi Pesan / Aspirasi / Pengaduan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="isi_pengaduan" rows="5" required
                                      placeholder="Tuliskan secara jelas dan detail apa yang ingin Anda sampaikan kepada pihak sekolah..."
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/40 focus:border-brand-primary transition">{{ old('isi_pengaduan') }}</textarea>
                        </div>

                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <i class="fas fa-shield-alt text-amber-500"></i>
                            <span>Kerahasiaan data pengirim terjamin aman.</span>
                        </div>
                        <button type="submit" 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-95 text-white font-bold text-sm rounded-2xl shadow-lg shadow-brand-primary/20 transition transform hover:-translate-y-0.5 cursor-pointer">
                            <span>Kirim Laporan</span>
                            <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </div>

                </form>
            @else
                {{-- Notice Layanan Sedang Dinonaktifkan --}}
                <div class="py-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 text-2xl border border-amber-200">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Layanan Sedang Ditutup Sementara</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto mt-1">
                        Formulir pengaduan publik saat ini sedang dalam pemeliharaan. Silakan hubungi kontak resmi sekolah untuk informasi lebih lanjut.
                    </p>
                </div>
            @endif

        </div>

    </div>
</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

@livewireScripts
</body>
</html>
