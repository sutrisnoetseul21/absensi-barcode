@php
    $items = \App\Models\KlasifikasiDdc::orderBy('kode_ddc')
        ->get()
        ->map(fn($d) => ['id' => $d->id, 'label' => $d->kode_ddc . ' - ' . $d->kategori])
        ->values()
        ->toArray();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        open: false,
        selectedId: $wire.entangle('{{ $statePath }}'),
        search: '',
        items: {{ json_encode($items) }},
        init() {
            if (this.selectedId) {
                let found = this.items.find(i => i.id === this.selectedId);
                if (found) this.search = found.label;
            }
            $watch('selectedId', (val) => {
                if (!val) {
                    this.search = '';
                } else {
                    let found = this.items.find(i => i.id === val);
                    if (found) this.search = found.label;
                }
            });
        },
        get filtered() {
            if (!this.search || this.search.trim().length < 3) return [];
            let q = this.search.toLowerCase().trim();
            return this.items.filter(i => i.label.toLowerCase().includes(q));
        },
        highlight(text) {
            if (!this.search || this.search.trim().length < 3) return text;
            let q = this.search.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            let re = new RegExp('(' + q + ')', 'gi');
            return text.replace(re, m => '<span class=\"text-indigo-600 dark:text-indigo-400 font-extrabold underline decoration-indigo-400/50 decoration-2 underline-offset-2\">' + m + '</span>');
        },
        select(item) {
            this.selectedId = item.id;
            this.search = item.label;
            $wire.set('{{ $statePath }}', item.id);
            this.open = false;
        },
        clearIfInvalid() {
            setTimeout(() => {
                if (!this.search || this.search.trim() === '') {
                    this.selectedId = null;
                    $wire.set('{{ $statePath }}', null);
                } else {
                    let found = this.items.find(i => i.label.toLowerCase() === this.search.toLowerCase().trim());
                    if (found) {
                        this.selectedId = found.id;
                        this.search = found.label;
                        $wire.set('{{ $statePath }}', found.id);
                    } else if (this.selectedId) {
                        let current = this.items.find(i => i.id === this.selectedId);
                        if (current) this.search = current.label;
                    } else {
                        this.search = '';
                    }
                }
                this.open = false;
            }, 150);
        }
    }" class="relative group">
        <div class="relative flex items-center">
            <!-- Icon Tag / Bookmark -->
            <div class="absolute left-3 pointer-events-none text-gray-400 dark:text-gray-500 group-focus-within:text-indigo-500 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>

            <input 
                x-model="search"
                x-on:focus="if (search && search.trim().length >= 3) open = true"
                x-on:input="open = (search && search.trim().length >= 3); if (!search) { selectedId = null; $wire.set('{{ $statePath }}', null); }"
                x-on:blur="clearIfInvalid()"
                @keydown.escape="open = false"
                @click.outside="open = false"
                type="text" 
                placeholder="Cari Kode / Kategori DDC (min. 3 huruf)" 
                class="fi-input block w-full rounded-xl border border-gray-200/90 dark:border-gray-800 bg-gray-50/50 dark:bg-white/5 py-2.5 pl-9 pr-24 text-sm text-gray-950 shadow-sm transition-all focus:bg-white dark:focus:bg-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 font-medium"
            >

            <!-- Status Indicator / Badge -->
            <div class="absolute right-2.5 flex items-center gap-1">
                <span x-show="search && search.trim().length > 0 && search.trim().length < 3" class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50 px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    min 3 huruf
                </span>
                <span x-show="open && filtered.length > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/50 px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span x-text="filtered.length + ' DDC'"></span>
                </span>
                <button 
                    type="button" 
                    x-show="selectedId" 
                    @click="selectedId = null; search = ''; open = false; $wire.set('{{ $statePath }}', null);" 
                    class="p-1 text-gray-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30"
                    title="Hapus pilihan"
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
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Saran Klasifikasi DDC
                </span>
                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400" x-text="filtered.length + ' DDC cocok'"></span>
            </div>
            <div class="pt-1 space-y-1">
                <template x-for="item in filtered" :key="item.id">
                    <div 
                        x-html="highlight(item.label)" 
                        @mousedown.prevent="select(item)"
                        class="px-3.5 py-2.5 text-xs text-gray-700 dark:text-gray-200 hover:bg-gradient-to-r hover:from-indigo-50/90 hover:to-blue-50/50 dark:hover:from-indigo-950/40 dark:hover:to-blue-950/20 hover:text-indigo-700 dark:hover:text-indigo-300 rounded-xl cursor-pointer font-semibold transition-all duration-150 flex items-center justify-between group/item"
                    >
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-dynamic-component>
