{{-- ══════════════════ FASILITAS / SARANA PRASARANA ══════════════════ --}}
@if($sarpras->count())
<section id="fasilitas" class="mb-16">
    <div class="text-center mb-10" data-aos="fade-up">
        <span class="text-brand-primary font-bold tracking-wider uppercase text-sm">Sarana Prasarana</span>
        <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mt-2">Fasilitas Sekolah Lengkap</h2>
        <div class="w-24 h-1 bg-brand-primary mx-auto mt-4 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($sarpras as $index => $s)
        <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 group border border-slate-100" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-slate-50 rounded-xl flex items-center justify-center text-3xl group-hover:bg-brand-primary-50 transition-colors {{ $s->color ?? 'text-slate-500' }}">
                    <i class="{{ $s->icon ?? 'fas fa-check-circle' }}"></i>
                </div>
                <i class="fas fa-check-circle text-slate-200 group-hover:text-brand-primary transition-colors text-lg"></i>
            </div>
            <h4 class="font-bold text-slate-800 text-lg mb-2">{{ $s->nama_fasilitas }}</h4>
            @if($s->deskripsi)
                <p class="text-sm text-slate-500 leading-relaxed">{{ $s->deskripsi }}</p>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif
