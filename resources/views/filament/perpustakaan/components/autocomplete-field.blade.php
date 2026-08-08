@php
    $column = $column ?? 'penulis';
    $placeholder = $placeholder ?? 'Ketik min. 3 huruf untuk saran...';
    $items = \App\Models\Buku::whereNotNull($column)
        ->where($column, '!=', '')
        ->distinct()
        ->orderBy($column)
        ->pluck($column)
        ->values()
        ->toArray();
    $statePath = $getStatePath();
    $isPenulis = ($column === 'penulis');
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        open: false,
        state: $wire.entangle('{{ $statePath }}'),
        items: {{ json_encode($items) }},
        get filtered() {
            if (!this.state || this.state.trim().length < 3) return [];
            let q = this.state.toLowerCase().trim();
            return this.items.filter(i => i.toLowerCase().includes(q));
        },
        highlight(text) {
            if (!this.state || this.state.trim().length < 3) return text;
            let q = this.state.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            let re = new RegExp('(' + q + ')', 'gi');
            return text.replace(re, m => '<span class=\"text-indigo-600 dark:text-indigo-400 font-extrabold underline decoration-indigo-400/50 decoration-2 underline-offset-2\">' + m + '</span>');
        },
        select(val) {
            let formatted = val.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
            this.state = formatted;
            $wire.set('{{ $statePath }}', formatted);
            this.open = false;
        },
        formatOnBlur() {
            if (this.state) {
                let formatted = this.state.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                this.state = formatted;
                $wire.set('{{ $statePath }}', formatted);
            }
            setTimeout(() => { this.open = false; }, 150);
        }
    }" class="relative group">
        <div class="relative flex items-center">
            <!-- Icon Indicator -->
            <div class="absolute left-3 pointer-events-none text-gray-400 dark:text-gray-500 group-focus-within:text-indigo-500 transition-colors">
                @if($isPenulis)
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                @else
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                @endif
            </div>

            <input 
                x-model="state"
                x-on:focus="if (state && state.trim().length >= 3) open = true"
                x-on:input="open = (state && state.trim().length >= 3)"
                x-on:blur="formatOnBlur()"
                @keydown.escape="open = false"
                @click.outside="open = false"
                type="text" 
                placeholder="{{ $placeholder }}" 
                class="fi-input block w-full rounded-xl border border-gray-200/90 dark:border-gray-800 bg-gray-50/50 dark:bg-white/5 py-2.5 pl-9 pr-24 text-sm text-gray-950 shadow-sm transition-all focus:bg-white dark:focus:bg-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 font-medium"
            >

            <!-- Status Indicator / Badge -->
            <div class="absolute right-2.5 flex items-center gap-1">
                <span x-show="state && state.trim().length > 0 && state.trim().length < 3" class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50 px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    min 3 huruf
                </span>
                <span x-show="open && filtered.length > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/50 px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span x-text="filtered.length + ' saran'"></span>
                </span>
                <button 
                    type="button" 
                    x-show="state" 
                    @click="state = ''; $wire.set('{{ $statePath }}', null); open = false;" 
                    class="p-1 text-gray-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30"
                    title="Kosongkan"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Modern Glassmorphism Dropdown Menu -->
        <div 
            x-show="open && filtered.length > 0" 
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
            x-cloak 
            class="absolute left-0 right-0 top-full mt-2 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-gray-800 rounded-2xl shadow-2xl shadow-indigo-900/10 z-50 max-h-56 overflow-y-auto p-1.5 divide-y divide-gray-100 dark:divide-gray-800 text-xs"
        >
            <div class="px-3 py-2 bg-gradient-to-r from-slate-50 via-indigo-50/30 to-slate-50 dark:from-gray-900 dark:via-indigo-950/20 dark:to-gray-900 rounded-xl mb-1 flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Saran Database
                </span>
                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400" x-text="filtered.length + ' pilihan cocok'"></span>
            </div>
            <div class="pt-1 space-y-1">
                <template x-for="item in filtered" :key="item">
                    <div 
                        x-html="highlight(item)" 
                        @mousedown.prevent="select(item)"
                        class="px-3.5 py-2.5 text-xs text-gray-700 dark:text-gray-200 hover:bg-gradient-to-r hover:from-indigo-50/90 hover:to-blue-50/50 dark:hover:from-indigo-950/40 dark:hover:to-blue-950/20 hover:text-indigo-700 dark:hover:text-indigo-300 rounded-xl cursor-pointer font-semibold transition-all duration-150 flex items-center justify-between group/item"
                    >
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-dynamic-component>
