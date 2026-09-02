{{-- ══════════════════ PENGUMUMAN TERBARU ══════════════════ --}}
@if($pengumumans->count())
<section id="pengumuman" class="mb-16">
    <div class="text-center mb-10" data-aos="fade-up">
        <span class="text-brand-primary font-bold tracking-wider uppercase text-sm">Informasi Penting</span>
        <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mt-2">Pengumuman Terbaru</h2>
        <div class="w-24 h-1 bg-brand-primary mx-auto mt-4 rounded-full"></div>
        <p class="text-slate-500 mt-3">Pemberitahuan resmi dan agenda sekolah.</p>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-100 border-t-4 border-t-brand-primary">
        <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
            @forelse($pengumumans as $p)
                <a href="{{ route('beranda.artikel', $p->slug) }}" class="block group">
                    <div class="flex gap-4 p-4 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all duration-300">
                        {{-- Kotak Tanggal Elegan --}}
                        <div class="flex-shrink-0 text-center bg-gradient-to-br from-brand-primary to-brand-primary-dark text-white rounded-2xl p-3 w-18 h-18 sm:w-20 sm:h-20 flex flex-col justify-center shadow-sm transform group-hover:scale-105 transition-transform">
                            <span class="block text-2xl font-extrabold leading-none">{{ ($p->published_at ?? $p->created_at)?->format('d') }}</span>
                            <span class="block text-xs font-semibold uppercase mt-1 opacity-90">{{ ($p->published_at ?? $p->created_at)?->isoFormat('MMM YYYY') }}</span>
                        </div>
                        
                        {{-- Isi Pengumuman --}}
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h5 class="font-bold text-slate-800 text-base md:text-lg mb-1 leading-snug group-hover:text-brand-primary transition-colors line-clamp-2">
                                {{ $p->judul }}
                            </h5>
                            <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed">
                                {{ Str::limit(strip_tags($p->konten), 120) }}
                            </p>
                            <span class="inline-flex items-center text-xs font-semibold text-brand-primary mt-2 group-hover:gap-2 transition-all">
                                Baca Pengumuman <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                            </span>
                        </div>
                    </div>
                </a>
                
                @if(!$loop->last)
                    <hr class="border-dashed border-slate-100 my-2">
                @endif
            @empty
                <div class="text-center py-12">
                    <i class="fas fa-bullhorn text-4xl text-slate-300 mb-3"></i>
                    <p class="text-base text-slate-500">Tidak ada pengumuman saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endif
