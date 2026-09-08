<div class="space-y-4">
    {{-- Header Kompak & Responsif --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <i class="fas fa-chart-line text-sm"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold text-slate-900">Statistik & Capaian</h2>
                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">{{ $statistics->total() }} Data</span>
                </div>
                <p class="text-xs text-slate-400">Kelola angka capaian sekolah yang tampil di widget statistik beranda</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative flex-1 sm:w-60">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nilai / label..." class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-400 focus:bg-white outline-none">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition-colors shadow-xs shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Statistik
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="px-3.5 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Grid Card Kompak (5 kotak per baris di layar desktop) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
        @forelse($statistics as $item)
        @php
            $iconClass = $item->icon ?: 'fas fa-chart-bar';
            if (!str_starts_with($iconClass, 'fa')) {
                $iconClass = 'fas fa-' . $iconClass;
            }
        @endphp
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 hover:border-emerald-300 hover:shadow-md transition-all p-3.5 flex flex-col justify-between group relative">
            {{-- Header Card: Urutan & Action Buttons --}}
            <div class="flex items-center justify-between gap-1 mb-1">
                <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded">
                    #{{ $item->order }}
                </span>
                <div class="flex items-center gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                    <button wire:click="openEdit({{ $item->id }})" class="p-1 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded transition-colors" title="Edit">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button wire:click="confirmDelete({{ $item->id }})" class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Hapus">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>

            {{-- Center: Widget Style Card --}}
            <div class="my-2 p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center group-hover:bg-emerald-50/50 group-hover:border-emerald-100 transition-all">
                <div class="w-9 h-9 mx-auto rounded-lg bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-emerald-600 mb-1.5 group-hover:scale-110 transition-transform">
                    <i class="{{ $iconClass }} text-sm"></i>
                </div>
                <div class="text-lg sm:text-xl font-extrabold text-slate-900 group-hover:text-emerald-700 transition-colors truncate" title="{{ $item->value }}">
                    {{ $item->value }}
                </div>
            </div>

            {{-- Info: Label & Icon class --}}
            <div class="text-center mt-1">
                <h3 class="font-bold text-slate-800 text-xs line-clamp-2 leading-tight group-hover:text-emerald-600 transition-colors" title="{{ $item->label }}">
                    {{ $item->label }}
                </h3>
                <p class="text-[9px] text-slate-400 font-mono mt-0.5 truncate" title="{{ $item->icon }}">
                    {{ $item->icon }}
                </p>
            </div>
        </div>
        @empty
        <div class="col-span-2 sm:col-span-3 md:col-span-4 lg:col-span-5 py-12 text-center text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="text-xs">Belum ada data statistik</p>
        </div>
        @endforelse
    </div>

    @if($statistics->hasPages())
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-4 py-3">{{ $statistics->links() }}</div>
    @endif

    {{-- Modal Tambah / Edit --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-start justify-center pt-12 px-4 pb-10">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="font-extrabold text-slate-900 text-lg">{{ $editingId ? 'Edit Statistik' : 'Tambah Statistik Web' }}</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                {{-- Live Preview Card (Persis widget beranda) --}}
                <div class="rounded-2xl p-4 text-white shadow-sm relative overflow-hidden" 
                     style="background: linear-gradient(135deg, #059669, #047857)">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-200">Preview Tampilan di Widget Beranda</span>
                    <div class="mt-2 bg-white/10 backdrop-blur-sm rounded-xl p-3.5 border border-white/20 flex items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <i class="{{ $icon ?: 'fas fa-chart-bar' }} text-white/80 text-lg"></i>
                                <span class="text-white font-extrabold text-2xl tracking-tight">{{ $value ?: 'A+' }}</span>
                            </div>
                            <p class="text-white/80 text-xs font-semibold mt-1">{{ $label ?: 'Keterangan Capaian Statistik' }}</p>
                        </div>
                        <span class="text-[10px] font-bold bg-white/20 px-2 py-0.5 rounded text-white/90">Urutan: #{{ $order }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nilai / Angka Capaian *</label>
                        <input type="text" wire:model.live="value" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none" placeholder="Contoh: A+, 100%, 50+, 1.250">
                        @error('value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Urutan *</label>
                        <input type="number" wire:model.live="order" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                        @error('order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Keterangan Label *</label>
                    <input type="text" wire:model.live="label" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none" placeholder="Contoh: Akreditasi Sekolah, Tingkat Kelulusan, Prestasi Juara">
                    @error('label') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Searchable FontAwesome Icon Picker --}}
                <div x-data="{
                    open: false,
                    search: '',
                    viewMode: 'list',
                    icons: {{ Js::from(collect($availableIcons)->map(fn($label, $key) => ['key' => $key, 'label' => $label])->values()) }},
                    get filteredIcons() {
                        if (!this.search.trim()) return this.icons;
                        const q = this.search.toLowerCase();
                        return this.icons.filter(i => i.label.toLowerCase().includes(q) || i.key.toLowerCase().includes(q));
                    },
                    get currentIcon() {
                        return $wire.icon;
                    },
                    get currentLabel() {
                        const item = this.icons.find(i => i.key === $wire.icon);
                        return item ? item.label : ($wire.icon ? $wire.icon : 'Pilih Ikon');
                    },
                    choose(key) {
                        $wire.set('icon', key);
                        this.open = false;
                        this.search = '';
                    }
                }" 
                @keydown.escape.window="open = false"
                class="relative">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Ikon FontAwesome *</label>
                    
                    <!-- Trigger Button -->
                    <button type="button" 
                            @click="open = !open" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-emerald-400 outline-none transition-all hover:border-slate-400 text-left">
                        <div class="flex items-center gap-2.5 truncate">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200">
                                <template x-if="currentIcon">
                                    <i :class="currentIcon" class="text-xs text-slate-700"></i>
                                </template>
                                <template x-if="!currentIcon">
                                    <i class="fas fa-chart-bar text-xs text-slate-400"></i>
                                </template>
                            </div>
                            <span x-text="currentLabel" :class="currentIcon ? 'text-slate-800 font-medium' : 'text-slate-400'" class="truncate text-xs"></span>
                        </div>
                        <div class="flex items-center gap-1 text-slate-400 shrink-0 ml-2">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         @click.outside="open = false" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 right-0 sm:right-auto sm:w-[360px] z-50 mt-1.5 bg-white rounded-2xl shadow-2xl border border-slate-200 p-3">

                        <!-- Search & View Mode Switcher -->
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-slate-100">
                            <div class="relative flex-1">
                                <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" x-model="search" placeholder="Cari icon (grafik, piala, siswa, lulus...)" class="w-full pl-8 pr-2.5 py-1.5 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
                            </div>
                            <div class="flex bg-slate-100 p-0.5 rounded-lg shrink-0">
                                <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white shadow-xs text-emerald-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-2 py-1 text-[11px] rounded-md transition-all flex items-center gap-1" title="Daftar Ikon + Teks">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                    <span>Teks</span>
                                </button>
                                <button type="button" @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white shadow-xs text-emerald-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-2 py-1 text-[11px] rounded-md transition-all flex items-center gap-1" title="Full Logo / Ikon Saja">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    <span>Full Logo</span>
                                </button>
                            </div>
                        </div>

                        <!-- Options Container -->
                        <div class="max-h-56 overflow-y-auto custom-scrollbar pr-0.5">
                            <!-- MODE 1: LIST -->
                            <div x-show="viewMode === 'list'" class="space-y-0.5">
                                <template x-for="item in filteredIcons" :key="item.key">
                                    <button type="button" 
                                            @click="choose(item.key)"
                                            :class="currentIcon === item.key ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                            class="w-full flex items-center gap-3 px-2.5 py-1.5 rounded-xl text-left text-xs transition-colors group">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" :class="currentIcon === item.key ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'">
                                            <i :class="item.key" class="text-xs"></i>
                                        </div>
                                        <span x-text="item.label" class="flex-1 truncate"></span>
                                        <template x-if="currentIcon === item.key">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                    </button>
                                </template>
                            </div>

                            <!-- MODE 2: GRID -->
                            <div x-show="viewMode === 'grid'" class="grid grid-cols-6 gap-1.5 p-1">
                                <template x-for="item in filteredIcons" :key="item.key">
                                    <button type="button" 
                                            @click="choose(item.key)"
                                            :title="item.label"
                                            :class="currentIcon === item.key ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-300' : 'bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200/60'"
                                            class="h-10 rounded-xl flex items-center justify-center transition-all group">
                                        <i :class="item.key" class="text-base group-hover:scale-110 transition-transform"></i>
                                    </button>
                                </template>
                            </div>

                            <div x-show="filteredIcons.length === 0" class="py-6 text-center text-xs text-slate-400">
                                Tidak ada ikon yang cocok dengan kata kunci "<span x-text="search"></span>"
                            </div>
                        </div>
                    </div>
                    @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-xs">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="font-extrabold text-slate-900 mb-1">Hapus Statistik?</h3>
            <p class="text-sm text-slate-500 mb-5">Data statistik ini akan dihapus dari widget beranda.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</button>
                <button wire:click="delete" class="flex-1 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors shadow-xs">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
