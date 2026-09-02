{{-- ══════════════════ FOOTER DINAMIS SESUAI TEMA ══════════════════ --}}
<footer id="kontak" class="text-white pt-16 pb-8 border-t-4 border-amber-400 mt-auto relative overflow-hidden"
        style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">

    {{-- Subtle pattern & glow decorations --}}
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-black/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 mb-12">
            
            {{-- Identitas & Info Sekolah (5 Kolom) --}}
            <div class="md:col-span-6 lg:col-span-5">
                <div class="flex items-center gap-3.5 mb-5">
                    @if($sekolah?->school_logo_path)
                        <div class="w-13 h-13 rounded-2xl bg-white p-2 shadow-lg flex items-center justify-center flex-shrink-0">
                            <img src="{{ asset('storage/' . $sekolah->school_logo_path) }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-2xl bg-white/20 text-white border border-white/30 flex items-center justify-center font-bold text-xl flex-shrink-0 shadow-lg">
                            <i class="fas fa-school"></i>
                        </div>
                    @endif
                    <div>
                        <h4 class="font-extrabold text-white text-lg sm:text-xl leading-tight">{{ $sekolah?->school_name ?? 'SMP Negeri 3 Kedungreja' }}</h4>
                        <p class="text-xs text-white/80 font-medium mt-0.5">Sistem Informasi & Profil Sekolah</p>
                    </div>
                </div>

                @if($sekolah?->school_address)
                    <p class="text-white/85 text-sm leading-relaxed mb-6 flex items-start gap-2.5">
                        <i class="fas fa-map-marker-alt text-amber-300 mt-1 flex-shrink-0 text-base"></i>
                        <span>{{ $sekolah->school_address }}</span>
                    </p>
                @endif
                
                {{-- Tombol Sosial Media Lengkap --}}
                <div class="pt-1">
                    <p class="text-xs font-bold text-white/75 uppercase tracking-wider mb-3">Sosial Media Resmi</p>
                    <div class="flex flex-wrap items-center gap-2.5">
                        @if($setting->link_youtube)
                            <a href="{{ $setting->link_youtube }}" target="_blank" rel="noopener" 
                               class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-[#FF0000] hover:border-[#FF0000] hover:scale-110 transition-all shadow-sm" 
                               title="YouTube">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        @endif

                        @if($setting->link_tiktok)
                            <a href="{{ $setting->link_tiktok }}" target="_blank" rel="noopener" 
                               class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-[#000000] hover:border-[#000000] hover:scale-110 transition-all shadow-sm" 
                               title="TikTok">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                        @endif

                        @if($setting->link_ig)
                            <a href="{{ $setting->link_ig }}" target="_blank" rel="noopener" 
                               class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-gradient-to-tr hover:from-[#f09433] hover:via-[#dc2743] hover:to-[#bc1888] hover:border-transparent hover:scale-110 transition-all shadow-sm" 
                               title="Instagram">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        @endif

                        @if($setting->link_fb)
                            <a href="{{ $setting->link_fb }}" target="_blank" rel="noopener" 
                               class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-[#1877F2] hover:border-[#1877F2] hover:scale-110 transition-all shadow-sm" 
                               title="Facebook">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Navigasi Cepat (3 Kolom) --}}
            <div class="md:col-span-3 lg:col-span-3 lg:col-start-7">
                <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-4 pb-2 border-b border-white/15">
                    Navigasi Cepat
                </h4>
                <ul class="space-y-2.5 text-sm text-white/80">
                    <li>
                        <a href="{{ route('beranda') }}#tentang" class="hover:text-amber-300 hover:translate-x-1.5 transition-all flex items-center gap-2">
                            <i class="fas fa-chevron-right text-[10px] text-white/40"></i> Profil Sekolah
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('beranda') }}#fasilitas" class="hover:text-amber-300 hover:translate-x-1.5 transition-all flex items-center gap-2">
                            <i class="fas fa-chevron-right text-[10px] text-white/40"></i> Fasilitas & Sarpras
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('guru.all') }}" class="hover:text-amber-300 hover:translate-x-1.5 transition-all flex items-center gap-2">
                            <i class="fas fa-chevron-right text-[10px] text-white/40"></i> Tenaga Pendidik (PTK)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('beranda') }}#berita" class="hover:text-amber-300 hover:translate-x-1.5 transition-all flex items-center gap-2">
                            <i class="fas fa-chevron-right text-[10px] text-white/40"></i> Berita & Pengumuman
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('beranda') }}#galeri" class="hover:text-amber-300 hover:translate-x-1.5 transition-all flex items-center gap-2">
                            <i class="fas fa-chevron-right text-[10px] text-white/40"></i> Galeri Dokumentasi
                        </a>
                    </li>
                </ul>
            </div>
            
            {{-- Layanan Pengaduan & Aspirasi (4 Kolom) --}}
            <div class="md:col-span-3 lg:col-span-3">
                <div class="bg-white/10 backdrop-blur-md rounded-3xl p-5 border border-white/20 shadow-lg">
                    <h4 class="text-white font-bold mb-2 flex items-center gap-2 text-sm">
                        <span class="text-lg">📣</span> Layanan Pengaduan
                    </h4>
                    <p class="text-xs text-white/80 mb-4 leading-relaxed">
                        Sampaikan masukan, saran, atau pengaduan untuk peningkatan mutu layanan sekolah.
                    </p>
                    <a href="{{ route('pengaduan.index') }}" 
                       class="block w-full py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold text-center text-xs rounded-xl shadow-md transition-all">
                        Tulis Pengaduan <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>
        
        {{-- Baris Copyright Bawah --}}
        <div class="pt-6 border-t border-white/15 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-white/70 font-medium">
            <p>&copy; {{ date('Y') }} {{ $sekolah?->school_name ?? 'Sekolah' }}. Hak Cipta Dilindungi.</p>
            <p class="text-white/60 flex items-center gap-1.5">
                <i class="fas fa-shield-alt text-amber-300"></i> Sistem Informasi Sekolah Terpadu
            </p>
        </div>
    </div>
</footer>
