{{-- ══════════════════ PROFIL, VISI & MISI ══════════════════ --}}
@if($setting->profil_singkat || $setting->visi || $setting->misi)
<section class="mb-16">
    <div class="flex flex-col lg:flex-row gap-12 items-start">
        
        <!-- BAGIAN KIRI: PROFIL SINGKAT -->
        <div class="lg:w-5/12 w-full" data-aos="fade-right" x-data="{ expanded: false }">
            <h3 class="text-brand-primary font-bold uppercase tracking-wider mb-2 text-sm border-l-4 border-brand-secondary pl-3">Profil Singkat</h3>
            <h2 class="text-3xl font-serif font-bold text-slate-900 mb-4">
                {{ $sekolah?->school_name ?? 'Sekolah' }}
            </h2>
            
            <div class="relative">
                <div class="text-slate-600 leading-relaxed text-justify space-y-3 overflow-hidden transition-all duration-700 max-h-[180px]"
                     :style="expanded ? 'max-height: 5000px' : 'max-height: 180px'">
                    @if($setting->profil_singkat)
                        {!! nl2br(e($setting->profil_singkat)) !!}
                    @else
                        <p>Belum ada profil singkat yang diisi.</p>
                    @endif
                </div>
                
                <!-- Gradient overlay -->
                <div x-show="!expanded" x-cloak
                     class="absolute bottom-0 left-0 w-full h-12 bg-gradient-to-t from-white to-transparent pointer-events-none">
                </div>
            </div>
            
            <!-- Tombol Read More -->
            <button @click="expanded = !expanded"
                    class="mt-3 flex items-center gap-2 text-brand-primary font-bold text-sm hover:text-brand-secondary transition-colors group">
                <span x-text="expanded ? 'Tampilkan Lebih Sedikit' : 'Baca Selengkapnya'">Baca Selengkapnya</span>
                <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-y-0.5"
                     :class="expanded ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <!-- BAGIAN KANAN: VISI & MISI -->
        <div class="lg:w-7/12 w-full space-y-8" data-aos="fade-left">
            
            <!-- Visi Card -->
            <div class="bg-brand-primary-50 border-l-8 border-brand-primary p-8 rounded-r-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-brand-primary text-2xl shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-brand-primary-900">Visi Sekolah</h3>
                </div>
                <p class="text-lg font-serif text-slate-800 italic leading-relaxed">
                    "{{ $setting->visi ?? 'Belum ada visi yang diisi.' }}"
                </p>
            </div>

            <!-- Misi -->
            <div>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-brand-secondary-50 rounded-full flex items-center justify-center text-brand-secondary text-2xl shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900">Misi Sekolah</h3>
                </div>
                
                <div class="grid md:grid-cols-1 gap-4">
                    @if($setting->misi)
                        @foreach(explode("\n", $setting->misi) as $misi)
                            @if(trim($misi) != '')
                            <div class="flex items-start gap-3 p-4 bg-white rounded-xl border border-slate-100 shadow-sm hover:border-brand-primary-200 transition-colors">
                                <svg class="w-5 h-5 text-brand-secondary mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-slate-700 leading-relaxed">{{ trim($misi) }}</span>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <div class="flex items-start gap-3 p-4 bg-white rounded-xl border border-slate-100 shadow-sm">
                            <span class="text-slate-500">Belum ada misi yang diisi.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
    </div>
</section>
@endif
