{{-- ══════════════════ PILAR KEUNGGULAN SEKOLAH (HYBRID) ══════════════════ --}}
@php
    $rawPillars = null;
    if (!empty($setting?->pilar_keunggulan)) {
        $rawPillars = is_array($setting->pilar_keunggulan) 
            ? $setting->pilar_keunggulan 
            : json_decode($setting->pilar_keunggulan, true);
    }

    $pillars = (!empty($rawPillars) && is_array($rawPillars)) ? $rawPillars : [
        [
            'title' => 'Digital Smart School',
            'tag' => 'Teknologi & Inovasi',
            'desc' => 'Presensi barcode digital terpadu, e-library modern, dan digitalisasi layanan administrasi sekolah.',
            'icon' => 'fas fa-laptop-code',
            'badge_color' => 'bg-blue-50 text-blue-700 border-blue-200/60',
            'icon_bg' => 'bg-blue-600 text-white shadow-blue-500/25',
            'hover_border' => 'hover:border-blue-200',
            'gradient' => 'from-blue-500 to-indigo-500',
        ],
        [
            'title' => 'Penguatan Karakter',
            'tag' => 'Budi Pekerti Luhur',
            'desc' => 'Pembiasaan akhlak mulia, literasi pagi, kedisiplinan, dan keteladanan religius berkesinambungan.',
            'icon' => 'fas fa-hands-praying',
            'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
            'icon_bg' => 'bg-emerald-600 text-white shadow-emerald-500/25',
            'hover_border' => 'hover:border-emerald-200',
            'gradient' => 'from-emerald-500 to-teal-500',
        ],
        [
            'title' => 'Pengembangan Potensi',
            'tag' => 'Prestasi & Bakat',
            'desc' => 'Wadah optimalisasi bakat akademik, sains, seni budaya, serta olahraga untuk mencetak generasi juara.',
            'icon' => 'fas fa-trophy',
            'badge_color' => 'bg-amber-50 text-amber-700 border-amber-200/60',
            'icon_bg' => 'bg-amber-500 text-white shadow-amber-500/25',
            'hover_border' => 'hover:border-amber-200',
            'gradient' => 'from-amber-500 to-orange-500',
        ],
        [
            'title' => 'Sekolah Ramah & Asri',
            'tag' => 'Lingkungan Harmonis',
            'desc' => 'Ekosistem belajar yang inklusif, aman dari perundungan, ramah anak, dan berwawasan adiwiyata.',
            'icon' => 'fas fa-seedling',
            'badge_color' => 'bg-teal-50 text-teal-700 border-teal-200/60',
            'icon_bg' => 'bg-teal-600 text-white shadow-teal-500/25',
            'hover_border' => 'hover:border-teal-200',
            'gradient' => 'from-teal-500 to-emerald-500',
        ],
    ];
@endphp

<section class="mb-16">
    {{-- Header Section --}}
    <div class="text-center mb-8">
        <span class="text-brand-primary font-bold tracking-wider uppercase text-xs sm:text-sm">Komitmen & Keunggulan</span>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1">Pilar Keunggulan Sekolah</h2>
        <div class="w-16 h-1 bg-brand-primary mx-auto mt-3 rounded-full"></div>
    </div>

    {{-- 4 Pilar Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($pillars as $pillar)
        <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl border border-slate-100 {{ $pillar['hover_border'] ?? 'hover:border-slate-200' }} transition-all duration-300 transform hover:-translate-y-1.5 flex flex-col justify-between relative overflow-hidden group">
            {{-- Hover gradient accent bar --}}
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $pillar['gradient'] ?? 'from-brand-primary to-emerald-500' }} opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl {{ $pillar['icon_bg'] ?? 'bg-brand-primary text-white shadow-brand-primary/25' }} flex items-center justify-center text-xl shadow-md group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                        <i class="{{ $pillar['icon'] ?? 'fas fa-star' }}"></i>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $pillar['badge_color'] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                        {{ $pillar['tag'] ?? 'Keunggulan' }}
                    </span>
                </div>

                <h4 class="text-lg font-bold text-slate-800 group-hover:text-brand-primary transition-colors mb-2">
                    {{ $pillar['title'] }}
                </h4>

                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">
                    {{ $pillar['desc'] }}
                </p>
            </div>

            <div class="pt-4 mt-5 border-t border-slate-100/80 flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 group-hover:text-slate-600 transition-colors flex items-center gap-1.5">
                    <i class="fas fa-check-circle text-emerald-500 text-xs"></i> Program Unggulan
                </span>
                <span class="text-slate-300 group-hover:text-brand-primary group-hover:translate-x-1 transition-all duration-300 text-xs">
                    <i class="fas fa-arrow-right"></i>
                </span>
            </div>
        </div>
        @endforeach
    </div>
</section>
