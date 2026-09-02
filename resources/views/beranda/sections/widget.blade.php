{{-- ══════════════════ WIDGET: STATISTIK, AKSES CEPAT & VIDEO YOUTUBE ══════════════════ --}}
<section class="mb-16">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-stretch">
        
        {{-- KOLOM KIRI: STATISTIK & AKSES CEPAT --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            {{-- STATISTIK & INFO --}}
            <div class="rounded-2xl shadow-lg p-6 flex flex-col justify-center relative overflow-hidden group flex-1"
                 style="background: linear-gradient(135deg, var(--color-brand-primary, #059669), color-mix(in srgb, var(--color-brand-primary, #059669) 60%, #000))">
                {{-- Dekorasi latar --}}
                <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-white/10 rounded-full filter blur-2xl opacity-20 group-hover:opacity-40 transition-opacity duration-500"></div>
                <div class="absolute -left-10 -top-10 w-32 h-32 bg-white/10 rounded-full filter blur-2xl opacity-10"></div>

                <div class="relative z-10">
                    <h3 class="text-white/70 text-xs font-bold uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-line"></i> Statistik & Info
                    </h3>

                    @if($webStats->count() > 0)
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($webStats as $stat)
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:bg-white/20 transition-colors">
                            <div class="flex items-center gap-3 mb-1">
                                <i class="{{ $stat->icon }} text-white/70 text-lg"></i>
                                <span class="text-white font-bold text-2xl">{{ $stat->value }}</span>
                            </div>
                            <p class="text-white/60 text-xs font-medium">{{ $stat->label }}</p>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-white/50 text-sm italic">Statistik sedang diperbarui...</p>
                    @endif
                </div>
            </div>

            {{-- AKSES CEPAT --}}
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 flex flex-col justify-center flex-1">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-link"></i> Akses Cepat
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    @forelse($quickLinks as $link)
                    <a href="{{ $link->url }}" target="_blank"
                       class="group flex flex-col items-center justify-center p-3 bg-slate-50 rounded-xl border border-transparent hover:border-slate-200 hover:bg-slate-100 transition-all">
                        <div class="w-10 h-10 rounded-full shadow-sm flex items-center justify-center {{ $link->color_class }} text-white mb-2 group-hover:scale-110 transition-transform">
                            <i class="{{ $link->icon }}"></i>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">{{ $link->title }}</span>
                    </a>
                    @empty
                    <div class="col-span-2 text-center text-xs text-slate-400 py-4 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        Belum ada akses cepat
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: EMBED YOUTUBE --}}
        <div class="lg:col-span-3 h-full">
            @php
                $ytId = null;
                if (!empty($setting->link_youtube)) {
                    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $setting->link_youtube, $ytMatch);
                    $ytId = $ytMatch[1] ?? null;
                }
            @endphp
            @if($ytId)
            <div class="rounded-2xl overflow-hidden shadow-lg h-full min-h-[300px] lg:min-h-full">
                <iframe class="w-full h-full min-h-[300px] lg:min-h-full"
                        src="https://www.youtube.com/embed/{{ $ytId }}"
                        title="Video Profil Sekolah"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                </iframe>
            </div>
            @else
            <div class="bg-slate-100 rounded-2xl shadow-lg border border-dashed border-slate-200 h-full min-h-[300px] flex flex-col items-center justify-center text-slate-400">
                <i class="fab fa-youtube text-5xl mb-3 text-red-300"></i>
                <p class="text-sm font-medium">Video belum diatur</p>
                <p class="text-xs mt-1">Isi link YouTube di <strong>Pengaturan Web</strong></p>
            </div>
            @endif
        </div>

    </div>
</section>
