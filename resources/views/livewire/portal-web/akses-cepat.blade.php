<div class="space-y-4">
    {{-- Header Kompak & Responsif --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                <i class="fas fa-bolt text-sm"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold text-slate-900">Akses Cepat</h2>
                    <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">{{ $pelayanans->total() }} Tautan</span>
                </div>
                <p class="text-xs text-slate-400">Kelola link akses cepat yang tampil di widget beranda web publik</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative flex-1 sm:w-60">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari layanan / URL..." class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-900 text-xs focus:ring-2 focus:ring-blue-400 focus:bg-white outline-none">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition-colors shadow-xs shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Akses Cepat
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
        @forelse($pelayanans as $item)
        @php
            $iconClass = $item->icon ?: 'fas fa-link';
            if (!str_starts_with($iconClass, 'fa')) {
                $iconClass = 'fas fa-' . $iconClass;
            }
        @endphp
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 hover:border-blue-300 hover:shadow-md transition-all p-3.5 flex flex-col justify-between group relative">
            {{-- Header Card: Urutan & Status & Action Buttons --}}
            <div class="flex items-center justify-between gap-1 mb-1">
                <div class="flex items-center gap-1">
                    <span class="text-[10px] font-extrabold text-blue-600 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded">
                        #{{ $item->order }}
                    </span>
                    <button wire:click="toggleActive('{{ $item->id }}')" 
                            class="text-[10px] font-bold px-1.5 py-0.5 rounded transition-colors {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}" 
                            title="Klik untuk toggle status aktif/nonaktif">
                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                    </button>
                </div>
                <div class="flex items-center gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                    <button wire:click="openEdit('{{ $item->id }}')" class="p-1 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Edit">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button wire:click="confirmDelete('{{ $item->id }}')" class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Hapus">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>

            {{-- Center: Icon Bulat Berwarna seperti di Beranda --}}
            <div class="w-12 h-12 mx-auto my-1.5 rounded-full {{ $item->color_class ?? 'bg-blue-500' }} text-white shadow-xs flex items-center justify-center group-hover:scale-110 transition-all duration-200">
                <i class="{{ $iconClass }} text-lg"></i>
            </div>

            {{-- Info: Title & URL/Deskripsi --}}
            <div class="text-center mt-1">
                <h3 class="font-bold text-slate-800 text-xs line-clamp-1 group-hover:text-blue-600 transition-colors" title="{{ $item->title }}">
                    {{ $item->title }}
                </h3>
                <p class="text-[10px] text-slate-400 line-clamp-1 mt-0.5" title="{{ $item->description ?: $item->url }}">
                    {{ $item->description ?: $item->url }}
                </p>
                <div class="mt-1 flex justify-center">
                    <a href="{{ $item->url }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-semibold text-blue-500 hover:text-blue-700 hover:underline">
                        <span>Buka Link</span>
                        <i class="fas fa-external-link-alt text-[8px]"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 sm:col-span-3 md:col-span-4 lg:col-span-5 py-12 text-center text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            <p class="text-xs">Belum ada data akses cepat</p>
        </div>
        @endforelse
    </div>

    @if($pelayanans->hasPages())
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-4 py-3">{{ $pelayanans->links() }}</div>
    @endif

    {{-- Modal Tambah / Edit --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-start justify-center pt-12 px-4 pb-10">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="font-extrabold text-slate-900 text-lg">{{ $editingId ? 'Edit Akses Cepat' : 'Tambah Akses Cepat' }}</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                {{-- Live Preview Card --}}
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full {{ $color_class ?: 'bg-blue-500' }} text-white shadow-sm flex items-center justify-center shrink-0">
                        <i class="{{ $icon ?: 'fas fa-link' }} text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Preview Tampilan Akses Cepat</span>
                        <h4 class="font-extrabold text-slate-800 text-sm truncate">{{ $title ?: 'Nama Layanan / Tautan' }}</h4>
                        <p class="text-xs text-slate-500 truncate">{{ $description ?: ($url ?: 'https://...') }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                        {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Layanan / Tautan *</label>
                    <input type="text" wire:model.live="title" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none" placeholder="Contoh: SPMB Online, Perpustakaan Digital, CBT">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">URL Target *</label>
                    <input type="url" wire:model.live="url" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none" placeholder="https://contoh-link.sch.id atau /portal-perpustakaan">
                    @error('url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Deskripsi Singkat (Opsional)</label>
                    <input type="text" wire:model.live="description" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-400 outline-none" placeholder="Keterangan singkat fungsi tautan...">
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-blue-400 outline-none transition-all hover:border-slate-400 text-left">
                            <div class="flex items-center gap-2.5 truncate">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200">
                                    <template x-if="currentIcon">
                                        <i :class="currentIcon" class="text-xs text-slate-700"></i>
                                    </template>
                                    <template x-if="!currentIcon">
                                        <i class="fas fa-link text-xs text-slate-400"></i>
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
                                    <input type="text" x-model="search" placeholder="Cari icon (link, buku, wa, cbt...)" class="w-full pl-8 pr-2.5 py-1.5 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-400 outline-none">
                                </div>
                                <div class="flex bg-slate-100 p-0.5 rounded-lg shrink-0">
                                    <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white shadow-xs text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-2 py-1 text-[11px] rounded-md transition-all flex items-center gap-1" title="Daftar Ikon + Teks">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                        <span>Teks</span>
                                    </button>
                                    <button type="button" @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white shadow-xs text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-2 py-1 text-[11px] rounded-md transition-all flex items-center gap-1" title="Full Logo / Ikon Saja">
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
                                                :class="currentIcon === item.key ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                                class="w-full flex items-center gap-3 px-2.5 py-1.5 rounded-xl text-left text-xs transition-colors group">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" :class="currentIcon === item.key ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'">
                                                <i :class="item.key" class="text-xs"></i>
                                            </div>
                                            <span x-text="item.label" class="flex-1 truncate"></span>
                                            <template x-if="currentIcon === item.key">
                                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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
                                                :class="currentIcon === item.key ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-300' : 'bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200/60'"
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

                    {{-- Color Selector --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Warna Lingkaran Ikon *</label>
                        <select wire:model.live="color_class" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                            @foreach($colorOptions as $val => $cLabel)
                                <option value="{{ $val }}">{{ $cLabel }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Warna latar ikon yang tampil di widget beranda.</p>
                        @error('color_class') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Urutan Tampilan</label>
                        <input type="number" wire:model.live="order" min="0" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        @error('order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="pt-4 sm:pt-6">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model.live="is_active" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-400">
                            <span class="text-xs font-bold text-slate-700">Tampilkan di Beranda Publik</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-xs">Simpan</button>
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
            <h3 class="font-extrabold text-slate-900 mb-1">Hapus Akses Cepat?</h3>
            <p class="text-sm text-slate-500 mb-5">Tautan ini akan dihapus dari widget beranda.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</button>
                <button wire:click="delete" class="flex-1 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors shadow-xs">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>