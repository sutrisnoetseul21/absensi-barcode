{{-- ══════════════════ BERITA & PENGUMUMAN (2 KOLOM SEJAJAR) ══════════════════ --}}
<section id="berita" class="mb-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
        
        {{-- Kolom Kiri: Berita Terbaru (Mini Berita) --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 border-t-4 border-t-brand-primary p-6 md:p-8 flex flex-col h-full">
            {{-- Header Berita --}}
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 text-brand-primary rounded-xl flex items-center justify-center text-lg">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-lg leading-tight">Berita Terbaru</h3>
                </div>
            </div>
            
            {{-- Konten Daftar Berita --}}
            <div class="flex-grow space-y-4 overflow-y-auto max-h-[420px] pr-2 custom-scrollbar">
                @forelse($artikels as $a)
                    <a href="{{ route('beranda.artikel', $a->slug) }}" class="block group">
                        <div class="flex gap-4 p-3.5 rounded-2xl border border-slate-100 hover:border-brand-primary-100 hover:bg-slate-50 transition-all">
                            {{-- Thumbnail Berita --}}
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden flex-shrink-0 bg-slate-100 relative">
                                @if($a->thumbnail)
                                    <img src="{{ asset('storage/' . $a->thumbnail) }}" 
                                         alt="{{ $a->judul }}" 
                                         onerror="this.parentElement.innerHTML='<div class=\'w-full h-full bg-slate-100 flex items-center justify-center text-slate-400\'><i class=\'fas fa-newspaper text-2xl\'></i></div>'"
                                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                        <i class="fas fa-newspaper text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Teks Berita --}}
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider mb-1">
                                    <i class="far fa-calendar-alt mr-1"></i> {{ ($a->published_at ?? $a->created_at)?->isoFormat('D MMM YYYY') }}
                                </span>
                                <h4 class="font-bold text-slate-800 text-sm leading-snug group-hover:text-brand-primary transition-colors line-clamp-2 mb-1">
                                    {{ $a->judul }}
                                </h4>
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ Str::limit(strip_tags($a->konten), 80) }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                        <i class="fas fa-newspaper text-4xl mb-2 opacity-40"></i>
                        <span class="text-sm">Belum ada berita</span>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Kolom Kanan: Pengumuman Terbaru --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 border-t-4 border-t-brand-primary p-6 md:p-8 flex flex-col h-full">
            {{-- Header Pengumuman --}}
            <div class="text-center mb-6 pb-2">
                <span class="text-brand-primary font-bold tracking-wider uppercase text-xs">Informasi Penting</span>
                <h3 class="text-xl font-bold text-slate-800 mt-1">Pengumuman Terbaru</h3>
                <div class="w-16 h-1 bg-brand-primary mx-auto mt-2 rounded-full"></div>
            </div>

            {{-- Konten Daftar Pengumuman --}}
            <div class="flex-grow space-y-3 overflow-y-auto max-h-[380px] pr-2 custom-scrollbar">
                @forelse($pengumumans as $p)
                    <a href="{{ route('beranda.artikel', $p->slug) }}" class="block group">
                        <div class="flex gap-4 p-3.5 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all">
                            {{-- Kotak Tanggal Elegan --}}
                            <div class="flex-shrink-0 text-center bg-gradient-to-br from-brand-primary to-emerald-700 text-white rounded-2xl p-2 w-16 h-16 sm:w-18 sm:h-18 flex flex-col justify-center shadow-sm transform group-hover:scale-105 transition-transform">
                                <span class="block text-xl font-extrabold leading-none">{{ ($p->published_at ?? $p->created_at)?->format('d') }}</span>
                                <span class="block text-[10px] font-semibold uppercase mt-1 opacity-90">{{ ($p->published_at ?? $p->created_at)?->isoFormat('MMM YYYY') }}</span>
                            </div>
                            
                            {{-- Isi Pengumuman --}}
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <h5 class="font-bold text-slate-800 text-sm leading-snug group-hover:text-brand-primary transition-colors line-clamp-2 mb-1">
                                    {{ $p->judul }}
                                </h5>
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ Str::limit(strip_tags($p->konten), 80) }}
                                </p>
                            </div>
                        </div>
                    </a>
                    @if(!$loop->last)
                        <hr class="border-dashed border-slate-100 my-1">
                    @endif
                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                        <i class="fas fa-bullhorn text-4xl mb-2 opacity-40"></i>
                        <span class="text-sm">Tidak ada pengumuman saat ini.</span>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>
