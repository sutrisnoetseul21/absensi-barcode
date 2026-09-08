<div class="space-y-4">
    {{-- Header Kompak & Responsif --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 border border-violet-100">
                <i class="fas fa-award text-sm"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold text-slate-900">Pilar Keunggulan Sekolah</h2>
                    <span class="text-[11px] font-bold text-violet-700 bg-violet-50 px-2 py-0.5 rounded-full border border-violet-100">{{ $pillars->total() }} Pilar</span>
                </div>
                <p class="text-xs text-slate-400">Kelola 4 pilar komitmen & keunggulan utama yang tampil di beranda depan</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative flex-1 sm:w-60">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari pilar / tag..." class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-900 text-xs focus:ring-2 focus:ring-violet-400 focus:bg-white outline-none">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-violet-600 text-white rounded-lg text-xs font-bold hover:bg-violet-700 transition-colors shadow-xs shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Pilar
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="px-3.5 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Grid Card Pilar Keunggulan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($pillars as $item)
        @php
            $theme = $item->theme_styles;
        @endphp
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 hover:shadow-md transition-all p-4 flex flex-col justify-between group relative overflow-hidden">
            {{-- Accent gradient bar --}}
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $theme['gradient'] }}"></div>

            <div>
                {{-- Header Card --}}
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-extrabold text-violet-600 bg-violet-50 border border-violet-100 px-1.5 py-0.5 rounded">
                            #{{ $item->urutan }}
                        </span>
                        <button wire:click="toggleStatus('{{ $item->id }}')" 
                                class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider transition-colors {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}"
                                title="Klik untuk mengubah status aktif/nonaktif">
                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </div>
                    <div class="flex items-center gap-0.5">
                        <button wire:click="openEdit('{{ $item->id }}')" class="p-1 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded transition-colors" title="Edit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button wire:click="confirmDelete('{{ $item->id }}')" class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Hapus">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Middle: Icon + Badge --}}
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl {{ $theme['icon_bg'] }} flex items-center justify-center text-lg shadow-sm">
                        <i class="{{ $item->icon }}"></i>
                    </div>
                    @if($item->tag)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $theme['badge_color'] }} truncate max-w-[150px]">
                        {{ $item->tag }}
                    </span>
                    @endif
                </div>

                {{-- Title & Desc --}}
                <h3 class="font-extrabold text-slate-800 text-sm mb-1 line-clamp-1 group-hover:text-violet-600 transition-colors" title="{{ $item->title }}">
                    {{ $item->title }}
                </h3>
                <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed mb-3" title="{{ $item->desc }}">
                    {{ $item->desc ?: 'Tidak ada deskripsi' }}
                </p>
            </div>

            {{-- Footer Card --}}
            <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center gap-1 font-medium">
                    <i class="fas fa-palette text-[10px] {{ $theme['text_color'] }}"></i> {{ ucfirst($item->color_theme) }}
                </span>
                @if($item->link)
                    <a href="{{ $item->link }}" target="_blank" class="text-violet-600 hover:underline flex items-center gap-1 font-semibold">
                        Link <i class="fas fa-external-link-alt text-[9px]"></i>
                    </a>
                @else
                    <span class="text-slate-300">Tanpa link</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-1 sm:col-span-2 lg:col-span-4 py-12 text-center text-slate-400 bg-white rounded-xl border border-slate-200">
            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-xs">Belum ada data pilar keunggulan</p>
        </div>
        @endforelse
    </div>

    @if($pillars->hasPages())
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-4 py-3">{{ $pillars->links() }}</div>
    @endif

    {{-- Modal Tambah / Edit --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-start justify-center pt-8 sm:pt-12 px-4 pb-12">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-xs">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-base">{{ $editingId ? 'Edit Pilar Keunggulan' : 'Tambah Pilar Keunggulan' }}</h3>
                </div>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="save" class="p-5 space-y-4">
                {{-- Live Preview Card --}}
                @php
                    $previewThemeObj = new \App\Models\WebPillar(['color_theme' => $color_theme]);
                    $previewTheme = $previewThemeObj->theme_styles;
                @endphp
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $previewTheme['gradient'] }}"></div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Live Preview Card:</span>
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $previewTheme['icon_bg'] }} flex items-center justify-center text-base shrink-0 shadow-sm">
                            <i class="{{ $icon ?: 'fas fa-star' }}"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-800 text-sm truncate">{{ $title ?: 'Judul Pilar Keunggulan' }}</h4>
                                <span class="px-2 py-0.2 rounded-full text-[9px] font-bold uppercase tracking-wider border {{ $previewTheme['badge_color'] }}">
                                    {{ $tag ?: 'Tag Kategori' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 line-clamp-2 mt-0.5">{{ $desc ?: 'Deskripsi singkat program keunggulan sekolah...' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Judul & Urutan --}}
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Pilar *</label>
                        <input type="text" wire:model="title" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none" placeholder="Contoh: Digital Smart School">
                        @error('title') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Urutan</label>
                        <input type="number" wire:model="urutan" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none">
                    </div>
                </div>

                {{-- Tag & Pilihan Warna Tema --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tag Kategori (Badge)</label>
                        <input type="text" wire:model="tag" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none" placeholder="Contoh: Teknologi & Inovasi">
                        @error('tag') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tema Warna *</label>
                        <select wire:model.live="color_theme" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none bg-white">
                            @foreach($availableThemes as $themeKey => $themeLabel)
                                <option value="{{ $themeKey }}">{{ $themeLabel }}</option>
                            @endforeach
                        </select>
                        @error('color_theme') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea wire:model="desc" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none" placeholder="Uraian ringkas mengenai pilar keunggulan ini..."></textarea>
                    @error('desc') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Icon Picker FontAwesome --}}
                <div x-data="{
                    open: false,
                    search: '',
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ikon FontAwesome *</label>
                    <button type="button" 
                            @click="open = !open" 
                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs focus:ring-2 focus:ring-violet-400 outline-none text-left">
                        <div class="flex items-center gap-2 truncate">
                            <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200">
                                <i :class="currentIcon || 'fas fa-star'" class="text-xs text-slate-700"></i>
                            </div>
                            <span class="truncate text-slate-800" x-text="currentLabel"></span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown Content -->
                    <div x-show="open" 
                         x-transition
                         @click.away="open = false" 
                         class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-slate-200 p-2.5 max-h-60 overflow-y-auto">
                        <input type="text" 
                               x-model="search" 
                               placeholder="Cari ikon..." 
                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs mb-2 outline-none focus:ring-2 focus:ring-violet-400">
                        <div class="grid grid-cols-2 gap-1">
                            <template x-for="item in filteredIcons" :key="item.key">
                                <button type="button" 
                                        @click="choose(item.key)" 
                                        class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-violet-50 text-left transition-colors text-xs truncate"
                                        :class="{'bg-violet-50 font-bold text-violet-700': currentIcon === item.key}">
                                    <i :class="item.key" class="w-4 text-center shrink-0"></i>
                                    <span class="truncate" x-text="item.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Link & Status Aktif --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tautan / URL (Opsional)</label>
                        <input type="url" wire:model="link" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://...">
                        @error('link') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Tampil</label>
                        <label class="inline-flex items-center gap-2 cursor-pointer mt-1">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 text-violet-600 rounded border-slate-300 focus:ring-violet-400">
                            <span class="text-xs text-slate-700 font-semibold">Tampilkan di Beranda</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-xl text-xs font-bold hover:bg-violet-700 transition-colors shadow-xs">
                        {{ $editingId ? 'Simpan Perubahan' : 'Simpan Pilar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Konfirmasi Hapus --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-5 text-center">
            <div class="w-12 h-12 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-3 text-xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="font-extrabold text-slate-900 text-base mb-1">Hapus Pilar Keunggulan?</h3>
            <p class="text-xs text-slate-500 mb-4">Pilar ini tidak akan tampil lagi di beranda sekolah.</p>
            <div class="flex justify-center gap-2">
                <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                    Batal
                </button>
                <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-xl text-xs font-bold hover:bg-red-700 transition-colors shadow-xs">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
