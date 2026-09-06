<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900">Sarana & Prasarana</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola data fasilitas sekolah</p>
            </div>
            <button wire:click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Sarpras
            </button>
        </div>
        <div class="mt-4">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari fasilitas..." class="w-full sm:max-w-xs px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:ring-2 focus:ring-violet-400 outline-none">
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($sarpras as $item)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow">
            <div class="w-full h-36 bg-slate-50 border-b border-slate-100 flex items-center justify-center">
                @if($item->icon)
                    <i class="{{ $item->icon }} {{ $item->color ?? 'text-slate-400' }} text-6xl"></i>
                @else
                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                @endif
            </div>
            <div class="p-4">
                <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded-lg uppercase">Urutan: {{ $item->urutan }}</span>
                <h3 class="font-bold text-slate-800 mt-2 text-sm line-clamp-2">{{ $item->nama_fasilitas }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ $item->created_at?->format('d/m/Y') }}</p>
                <div class="flex justify-end gap-2 mt-3 pt-3 border-t border-slate-100">
                    <button wire:click="openEdit('{{ $item->id }}')" class="p-1.5 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button wire:click="confirmDelete('{{ $item->id }}')" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-1 sm:col-span-2 lg:col-span-3 py-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-sm">Belum ada data sarana prasarana</p>
        </div>
        @endforelse
    </div>

    @if($sarpras->hasPages())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-5 py-4">{{ $sarpras->links() }}</div>
    @endif

    {{-- Modal Tambah/Edit --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-start justify-center pt-12 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="font-extrabold text-slate-900 text-lg">{{ $editingId ? 'Edit Sarpras' : 'Tambah Sarpras' }}</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Fasilitas *</label>
                        <input type="text" wire:model="nama_fasilitas" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 focus:border-violet-400 outline-none" placeholder="Ruang Laboratorium...">
                        @error('nama_fasilitas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Urutan</label>
                        <input type="number" wire:model="urutan" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <input type="text" wire:model="deskripsi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="Deskripsi singkat...">
                    @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <!-- Live Preview Card -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-center shrink-0">
                        <i class="{{ $icon ? $icon : 'fas fa-building' }} {{ $color }} text-2xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Preview Tampilan Fasilitas</span>
                        <h4 class="font-extrabold text-slate-800 text-sm truncate">{{ $nama_fasilitas ?: 'Nama Fasilitas' }}</h4>
                        <p class="text-xs text-slate-500 truncate">{{ $deskripsi ?: 'Deskripsi sarana dan prasarana sekolah' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Icon Picker Field -->
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
                            return item ? item.label : ($wire.icon ? $wire.icon : 'Select an option');
                        },
                        choose(key) {
                            $wire.set('icon', key);
                            this.open = false;
                            this.search = '';
                        }
                    }" 
                    @keydown.escape.window="open = false"
                    class="relative">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Ikon *</label>
                        
                        <!-- Trigger Button (Icon di sebelah teks langsung) -->
                        <button type="button" 
                                @click="open = !open" 
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-violet-400 outline-none transition-all hover:border-slate-400 text-left">
                            <div class="flex items-center gap-2.5 truncate">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200">
                                    <template x-if="currentIcon">
                                        <i :class="currentIcon" class="text-xs text-slate-700"></i>
                                    </template>
                                    <template x-if="!currentIcon">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
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
                                    <input type="text" x-model="search" placeholder="Cari icon (lab, buku, olahraga...)" class="w-full pl-8 pr-2.5 py-1.5 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-violet-400 outline-none">
                                </div>
                                <!-- Switcher: Teks (Icon + Teks) vs Full Logo -->
                                <div class="flex bg-slate-100 p-0.5 rounded-lg shrink-0">
                                    <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white shadow-xs text-violet-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-2 py-1 text-[11px] rounded-md transition-all flex items-center gap-1" title="Daftar Ikon + Teks">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                        <span>Teks</span>
                                    </button>
                                    <button type="button" @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white shadow-xs text-violet-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-2 py-1 text-[11px] rounded-md transition-all flex items-center gap-1" title="Full Logo / Ikon Saja">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                        <span>Full Logo</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Options Container -->
                            <div class="max-h-56 overflow-y-auto custom-scrollbar pr-0.5">
                                <!-- MODE 1: LIST (Icon sebelah langsung teks) -->
                                <div x-show="viewMode === 'list'" class="space-y-0.5">
                                    <template x-for="item in filteredIcons" :key="item.key">
                                        <button type="button" 
                                                @click="choose(item.key)"
                                                :class="currentIcon === item.key ? 'bg-violet-50 text-violet-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                                class="w-full flex items-center gap-3 px-2.5 py-1.5 rounded-xl text-left text-xs transition-colors group">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" :class="currentIcon === item.key ? 'bg-violet-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'">
                                                <i :class="item.key" class="text-xs"></i>
                                            </div>
                                            <span x-text="item.label" class="flex-1 truncate"></span>
                                            <template x-if="currentIcon === item.key">
                                                <svg class="w-4 h-4 text-violet-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                        </button>
                                    </template>
                                </div>

                                <!-- MODE 2: GRID (Full Logo / Ikon Saja) -->
                                <div x-show="viewMode === 'grid'" class="grid grid-cols-6 gap-1.5 p-1">
                                    <template x-for="item in filteredIcons" :key="item.key">
                                        <button type="button" 
                                                @click="choose(item.key)"
                                                :title="item.label"
                                                :class="currentIcon === item.key ? 'bg-violet-600 text-white shadow-sm ring-2 ring-violet-300' : 'bg-slate-50 hover:bg-violet-50 text-slate-700 hover:text-violet-700 border border-slate-200/60'"
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

                        <p class="text-[11px] text-slate-400 mt-1">Ketik untuk mencari icon. Pilih dari daftar icon FontAwesome yang tersedia.</p>
                        @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Color Picker Field -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Warna Ikon *</label>
                        <select wire:model.live="color" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none">
                            <option value="text-red-500">🔴 Merah</option>
                            <option value="text-orange-500">🟠 Oranye</option>
                            <option value="text-yellow-500">🟡 Kuning</option>
                            <option value="text-emerald-500">🟢 Hijau Zamrud</option>
                            <option value="text-nature-500">🍃 Hijau Daun</option>
                            <option value="text-teal-500">🩵 Tosca</option>
                            <option value="text-cyan-500">🔷 Cyan</option>
                            <option value="text-blue-500">🔵 Biru</option>
                            <option value="text-indigo-500">🟣 Nila</option>
                            <option value="text-purple-500">🪻 Ungu</option>
                            <option value="text-pink-500">🌸 Merah Muda</option>
                            <option value="text-gray-500">⚪ Abu-abu</option>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Pilih warna aksen untuk tampilan ikon fasilitas.</p>
                        @error('color') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-violet-600 hover:bg-violet-700 rounded-xl">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
            <h3 class="font-extrabold text-slate-900 mb-1">Hapus Sarpras?</h3>
            <p class="text-sm text-slate-500 mb-5">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl">Batal</button>
                <button wire:click="delete" class="flex-1 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
