{{-- ══════════════════ SAMBUTAN KEPALA SEKOLAH ══════════════════ --}}
@if($setting->sambutan_kepsek)
<section id="tentang" class="mb-16">
    <div class="flex flex-col lg:flex-row gap-12 items-start">
        
        <!-- BAGIAN KIRI: FOTO KEPSEK & KUTIPAN SINGKAT -->
        <div class="lg:w-5/12 w-full">
            <div class="relative mb-8 group">
                @php
                    $fotoKepsek = $setting->foto_kepsek ? asset('storage/'.$setting->foto_kepsek) : null;
                @endphp
                @if($fotoKepsek)
                    <img src="{{ $fotoKepsek }}" alt="Kepala Sekolah" class="rounded-2xl shadow-2xl w-full object-cover h-[450px] bg-slate-100 transform transition duration-500 group-hover:scale-[1.01]">
                @else
                    <div class="rounded-2xl shadow-2xl w-full h-[450px] bg-slate-100 border border-slate-200 flex flex-col items-center justify-center text-slate-400 transform transition duration-500 group-hover:scale-[1.01]">
                        <svg class="w-20 h-20 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Belum ada foto</span>
                    </div>
                @endif

                <!-- Kotak Sambutan Singkat (Floating Card) -->
                <div class="absolute -bottom-6 -right-2 md:-right-6 bg-slate-800 text-white p-6 rounded-xl shadow-lg max-w-xs hidden md:block border-b-4 border-brand-primary">
                    <p class="font-serif italic text-sm text-slate-100">
                        "{{ $setting->kutipan_kepsek ?? 'Mewujudkan generasi berprestasi, berkarakter, dan berwawasan global melalui pendidikan berkualitas.' }}"
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-600">
                        <p class="text-xs font-bold text-brand-primary-light text-right">
                            - {{ $sekolah?->principal_name ?? 'Kepala Sekolah' }}
                        </p>
                    </div>
                </div>
            </div>
            <!-- Untuk tampilan mobile -->
            <div class="mt-6 text-center md:hidden">
                <p class="font-bold text-slate-800 text-xl">{{ $sekolah?->principal_name ?? 'Kepala Sekolah' }}</p>
                <p class="text-brand-primary text-sm font-bold uppercase tracking-wider mt-1">Kepala Sekolah</p>
            </div>
        </div>
        
        <!-- BAGIAN KANAN: TEKS SAMBUTAN LENGKAP -->
        <div class="lg:w-7/12 w-full">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 md:p-10 hover:shadow-md transition-shadow"
                 x-data="{ expanded: false }">
                
                <h3 class="text-slate-800 font-bold uppercase tracking-wider mb-2 text-sm border-l-4 border-brand-primary pl-3">Sambutan Lengkap</h3>
                <h2 class="text-3xl font-serif font-bold text-slate-900 mb-6">Kepala Sekolah</h2>
                
                <div class="relative">
                    <!-- Teks Konten -->
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed text-sm md:text-base text-justify overflow-hidden transition-all duration-700 max-h-[100px]"
                         :style="expanded ? 'max-height: 5000px' : 'max-height: 100px'">
                        {!! $setting->sambutan_kepsek !!}
                    </div>
                    
                    <!-- Gradient Overlay jika belum di-expand -->
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
        </div>
        
    </div>
</section>
<!-- CSS Khusus Scrollbar -->
<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endif
