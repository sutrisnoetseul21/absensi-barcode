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
                                <i class="fab fa-youtube text-base"></i>
                            </a>
                        @endif

                        @if($setting->link_tiktok)
                            <a href="{{ $setting->link_tiktok }}" target="_blank" rel="noopener" 
                               class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-[#000000] hover:border-[#000000] hover:scale-110 transition-all shadow-sm" 
                               title="TikTok">
                                <i class="fab fa-tiktok text-base"></i>
                            </a>
                        @endif

                        @if($setting->link_ig)
                            <a href="{{ $setting->link_ig }}" target="_blank" rel="noopener" 
                               class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-gradient-to-tr hover:from-[#f09433] hover:via-[#dc2743] hover:to-[#bc1888] hover:border-transparent hover:scale-110 transition-all shadow-sm" 
                               title="Instagram">
                                <i class="fab fa-instagram text-base"></i>
                            </a>
                        @endif

                        @if($setting->link_fb)
                            <a href="{{ $setting->link_fb }}" target="_blank" rel="noopener" 
                               class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-[#1877F2] hover:border-[#1877F2] hover:scale-110 transition-all shadow-sm" 
                               title="Facebook">
                                <i class="fab fa-facebook-f text-base"></i>
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
                    <a href="{{ $setting?->link_pengaduan ?: route('pengaduan.index') }}" 
                       target="{{ $setting?->link_pengaduan && str_starts_with($setting->link_pengaduan, 'http') ? '_blank' : '_self' }}"
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
