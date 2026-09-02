@props(['pengaturanSekolah' => null])

@php
    $setting = \App\Models\WebSetting::instance();
    $sekolah = $pengaturanSekolah ?? \App\Models\PengaturanSekolah::current();
@endphp

<footer id="kontak" class="text-white pt-16 pb-8 border-t-4 border-amber-400 mt-auto relative overflow-hidden"
        style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">
    
    {{-- Background Glow --}}
    <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-black/20 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-12">
            
            {{-- Identitas Sekolah & Sosmed (5 Kolom) --}}
            <div class="md:col-span-6 lg:col-span-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3.5 mb-4">
                        @if($sekolah?->school_logo_path)
                            <div class="bg-white p-2 rounded-2xl shadow-md flex items-center justify-center shrink-0">
                                <img src="{{ asset('storage/' . $sekolah->school_logo_path) }}" alt="Logo" class="h-10 w-auto object-contain">
                            </div>
                        @else
                            <div class="w-12 h-12 bg-white text-slate-900 rounded-2xl flex items-center justify-center font-bold text-xl shadow-md shrink-0">
                                <i class="fas fa-school text-brand-primary"></i>
                            </div>
                        @endif
                        <div>
                            <h3 class="font-extrabold text-white text-lg sm:text-xl tracking-tight leading-tight">
                                {{ $sekolah?->school_name ?? 'Sekolah' }}
                            </h3>
                            <p class="text-xs text-white/80 font-medium mt-0.5">Sistem Informasi & Profil Sekolah</p>
                        </div>
                    </div>
                    
                    @if($sekolah?->school_address)
                        <p class="text-xs text-white/80 leading-relaxed mb-6 flex items-start gap-2 max-w-sm">
                            <i class="fas fa-map-marker-alt text-amber-300 mt-0.5 shrink-0"></i>
                            <span>{{ $sekolah->school_address }}</span>
                        </p>
                    @endif
                </div>

                {{-- Sosial Media Resmi Sekolah --}}
                <div class="mt-4">
                    <p class="text-xs font-bold text-white/90 uppercase tracking-wider mb-3">Sosial Media Resmi</p>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        @if($setting?->link_youtube)
                            <a href="{{ $setting->link_youtube }}" target="_blank" rel="noopener" 
                               class="w-9 h-9 rounded-xl bg-white/10 hover:bg-red-600 text-white flex items-center justify-center transition-all border border-white/20 shadow-sm hover:scale-110" 
                               title="YouTube">
                                <i class="fab fa-youtube text-sm"></i>
                            </a>
                        @endif

                        @if($setting?->link_tiktok)
                            <a href="{{ $setting->link_tiktok }}" target="_blank" rel="noopener" 
                               class="w-9 h-9 rounded-xl bg-white/10 hover:bg-black text-white flex items-center justify-center transition-all border border-white/20 shadow-sm hover:scale-110" 
                               title="TikTok">
                                <i class="fab fa-tiktok text-sm"></i>
                            </a>
                        @endif

                        @if($setting?->link_ig)
                            <a href="{{ $setting->link_ig }}" target="_blank" rel="noopener" 
                               class="w-9 h-9 rounded-xl bg-white/10 hover:bg-pink-600 text-white flex items-center justify-center transition-all border border-white/20 shadow-sm hover:scale-110" 
                               title="Instagram">
                                <i class="fab fa-instagram text-sm"></i>
                            </a>
                        @endif

                        @if($setting?->link_fb)
                            <a href="{{ $setting->link_fb }}" target="_blank" rel="noopener" 
                               class="w-9 h-9 rounded-xl bg-white/10 hover:bg-blue-600 text-white flex items-center justify-center transition-all border border-white/20 shadow-sm hover:scale-110" 
                               title="Facebook">
                                <i class="fab fa-facebook-f text-sm"></i>
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
            <div class="flex items-center gap-2">
                <i class="fas fa-shield-alt text-amber-300"></i>
                <span>Sistem Informasi Sekolah Terpadu</span>
            </div>
        </div>
    </div>
</footer>
