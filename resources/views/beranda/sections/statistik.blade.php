{{-- ══════════════════ PILAR KEUNGGULAN SEKOLAH ══════════════════ --}}
@if(isset($pillars) && $pillars->count() > 0)
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
        @php
            $theme = is_object($pillar) ? $pillar->theme_styles : ($pillar['theme_styles'] ?? []);
            $title = is_object($pillar) ? $pillar->title : $pillar['title'];
            $tag   = is_object($pillar) ? $pillar->tag : ($pillar['tag'] ?? 'Keunggulan');
            $desc  = is_object($pillar) ? $pillar->desc : ($pillar['desc'] ?? '');
            $icon  = is_object($pillar) ? $pillar->icon : ($pillar['icon'] ?? 'fas fa-star');
            $link  = is_object($pillar) ? $pillar->link : ($pillar['link'] ?? null);
            
            $badgeColor = $theme['badge_color'] ?? ($pillar['badge_color'] ?? 'bg-slate-50 text-slate-600 border-slate-200');
            $iconBg = $theme['icon_bg'] ?? ($pillar['icon_bg'] ?? 'bg-brand-primary text-white shadow-brand-primary/25');
            $hoverBorder = $theme['hover_border'] ?? ($pillar['hover_border'] ?? 'hover:border-slate-200');
            $gradient = $theme['gradient'] ?? ($pillar['gradient'] ?? 'from-brand-primary to-emerald-500');
        @endphp
        <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl border border-slate-100 {{ $hoverBorder }} transition-all duration-300 transform hover:-translate-y-1.5 flex flex-col justify-between relative overflow-hidden group">
            {{-- Hover gradient accent bar --}}
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $gradient }} opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center text-xl shadow-md group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                        <i class="{{ $icon }}"></i>
                    </div>
                    @if($tag)
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeColor }}">
                        {{ $tag }}
                    </span>
                    @endif
                </div>

                <h4 class="text-lg font-bold text-slate-800 group-hover:text-brand-primary transition-colors mb-2">
                    {{ $title }}
                </h4>

                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">
                    {{ $desc }}
                </p>
            </div>

            <div class="pt-4 mt-5 border-t border-slate-100/80 flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 group-hover:text-slate-600 transition-colors flex items-center gap-1.5">
                    <i class="fas fa-check-circle text-emerald-500 text-xs"></i> Program Unggulan
                </span>
                @if($link)
                    <a href="{{ $link }}" class="text-slate-300 group-hover:text-brand-primary group-hover:translate-x-1 transition-all duration-300 text-xs" title="Lihat detail">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                @else
                    <span class="text-slate-300 group-hover:text-brand-primary group-hover:translate-x-1 transition-all duration-300 text-xs">
                        <i class="fas fa-arrow-right"></i>
                    </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif
