<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Katalog & Input Buku</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola katalog koleksi buku perpustakaan & penambahan eksemplar baru.</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="$wire.openUnduhModal()" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Unduh Katalog
            </button>
            <button @click="$wire.openInputModal()" class="px-5 py-3 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-2xl font-bold text-xs shadow-lg shadow-brand-primary/30 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Buku Baru
            </button>
        </div>
    </div>

    <!-- Alert Success Flash -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center gap-4 justify-between">
        <div class="relative w-full md:w-96">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul, penulis, ISBN..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <select wire:model.live="filterKategori" class="w-full md:w-48 py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                <option value="">Semua Koleksi</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Catalog Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="p-4 pl-6">Judul Buku</th>
                        <th class="p-4">Koleksi</th>
                        <th class="p-4">Lokasi Rak</th>
                        <th class="p-4 min-w-[130px] whitespace-nowrap">Eksemplar</th>
                        <th class="p-4 text-center">Cetak</th>
                        <th class="p-4 text-right pr-6">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($bukus as $buku)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-4">
                                    @if($buku->sampul_buku)
                                        <img src="{{ asset('storage/' . $buku->sampul_buku) }}" alt="Sampul" class="w-12 h-16 shrink-0 object-cover rounded-md shadow-sm border border-slate-200">
                                    @else
                                        <div class="w-12 h-16 shrink-0 bg-slate-100 rounded-md flex items-center justify-center border border-slate-200">
                                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm">{{ $buku->judul }}</h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $buku->penulis ?? 'Tanpa Penulis' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-brand-primary/10 text-brand-primary font-bold rounded-lg border border-brand-primary/20 inline-block text-[11px]">
                                    {{ $buku->kategoriBuku?->nama_kategori ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="p-4 font-medium text-slate-600">
                                {{ $buku->lokasi_rak ?? '-' }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                @php
                                    $totalFisik = $buku->eksemplarBukus->count();
                                    $tersedia = $buku->eksemplarBukus->where('status', 'tersedia')->count();
                                    $dipinjam = $buku->eksemplarBukus->where('status', 'dipinjam')->count();
                                @endphp
                                <span class="font-bold text-slate-800">{{ $totalFisik }} Eksemplar</span>
                                <div class="flex items-center mt-1 text-[10px]">
                                    <span class="text-amber-600 font-bold">{{ $dipinjam }} Pinjam</span>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('perpustakaan.cetak-barcode', $buku->id) }}" target="_blank" class="p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl border border-indigo-200/80 transition-all shadow-2xs" title="Cetak Label Barcode Eksemplar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                    </a>
                                    <a href="{{ route('perpustakaan.cetak-label-spine', $buku->id) }}" target="_blank" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl border border-slate-200 transition-all shadow-2xs" title="Cetak Label Buku">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-7 5h7"></path></svg>
                                    </a>
                                </div>
                            </td>
                            <td class="p-4 pr-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="openDetailEksemplarModal('{{ $buku->id }}')" class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200/80 transition-all shadow-2xs cursor-pointer" title="Detail Eksemplar Buku">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button type="button" wire:click="openEditBukuModal('{{ $buku->id }}')" class="p-2 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded-xl border border-amber-200/80 transition-all shadow-2xs cursor-pointer" title="Edit Data Buku">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" wire:confirm="Apakah Anda yakin ingin menghapus buku '{{ $buku->judul }}'?" wire:click="hapusBuku('{{ $buku->id }}')" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl border border-rose-200/80 transition-all shadow-2xs cursor-pointer" title="Hapus Buku">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <p class="text-xs font-medium">Belum ada buku katalog terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bukus->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $bukus->links('vendor.livewire.custom-pagination') }}
            </div>
        @endif
    </div>

    <!-- Modal Input Buku Baru -->
    @if($showInputModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 lg:p-8 space-y-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Tambah Buku Katalog Baru</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Input buku beserta otomatisasi eksemplar & inventaris.</p>
                    </div>
                    <button type="button" @click="$wire.set('showInputModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="simpanBuku" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Buku *</label>
                        <input wire:model="judul" type="text" required placeholder="Contoh: Laskar Pelangi" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        @error('judul') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Koleksi / Kategori *</label>
                            <select wire:model.live="kategori_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value="">-- Pilih Koleksi / Kategori --</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('kategori_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        
                        @php
                            $selectedKat = $kategoriList->firstWhere('id', $kategori_id);
                            $isNonFiksi = $selectedKat && strtolower(trim($selectedKat->nama_kategori)) === 'non fiksi';
                        @endphp
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran</label>
                            <select wire:model="mapel_id" {{ $isNonFiksi ? '' : 'disabled' }} class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary disabled:opacity-50 disabled:bg-slate-100">
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($mapelList as $mapel)
                                    <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                                @endforeach
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4">

                        <div x-data="{
                            open: false,
                            selectedId: @entangle('klasifikasi_ddc_id'),
                            search: '',
                            items: {{ json_encode($ddcList->map(fn($d) => ['id' => $d->id, 'label' => $d->kode_ddc . ' - ' . $d->kategori])->values()->toArray()) }},
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
                                return text.replace(re, m => '<span class=\'text-indigo-600 font-extrabold underline decoration-indigo-400/50 decoration-2 underline-offset-2\'>' + m + '</span>');
                            },
                            select(item) {
                                this.selectedId = item.id;
                                this.search = item.label;
                                $wire.set('klasifikasi_ddc_id', item.id);
                                this.open = false;
                            },
                            clearIfInvalid() {
                                setTimeout(() => {
                                    if (!this.search || this.search.trim() === '') {
                                        this.selectedId = null;
                                        $wire.set('klasifikasi_ddc_id', null);
                                    } else {
                                        let found = this.items.find(i => i.label.toLowerCase() === this.search.toLowerCase().trim());
                                        if (found) {
                                            this.selectedId = found.id;
                                            this.search = found.label;
                                            $wire.set('klasifikasi_ddc_id', found.id);
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
                            <label class="block text-xs font-bold text-slate-700 mb-1">Klasifikasi DDC (Opsional)</label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </div>
                                <input 
                                    x-model="search"
                                    x-on:focus="if (search && search.trim().length >= 3) open = true"
                                    x-on:input="open = (search && search.trim().length >= 3); if (!search) { selectedId = null; $wire.set('klasifikasi_ddc_id', null); }"
                                    x-on:blur="clearIfInvalid()"
                                    @keydown.escape="open = false"
                                    @click.outside="open = false"
                                    type="text" 
                                    placeholder="Cari Kode / Kategori DDC (min. 3 huruf)" 
                                    class="w-full pl-9 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium transition-all"
                                >
                                <div class="absolute right-2.5 flex items-center gap-1">
                                    <span x-show="search && search.trim().length > 0 && search.trim().length < 3" class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        min 3 huruf
                                    </span>
                                    <span x-show="open && filtered.length > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span x-text="filtered.length + ' DDC'"></span>
                                    </span>
                                    <button 
                                        type="button" 
                                        x-show="selectedId" 
                                        @click="selectedId = null; search = ''; open = false; $wire.set('klasifikasi_ddc_id', null);" 
                                        class="p-1 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50"
                                        title="Hapus pilihan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div 
                                x-show="open && filtered.length > 0" 
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                x-cloak 
                                class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-2xl shadow-2xl shadow-indigo-900/10 z-50 max-h-56 overflow-y-auto p-1.5 divide-y divide-slate-100 text-xs"
                            >
                                <div class="px-3 py-2 bg-gradient-to-r from-slate-50 via-indigo-50/30 to-slate-50 rounded-xl mb-1 flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        Saran Klasifikasi DDC
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-500" x-text="filtered.length + ' DDC cocok'"></span>
                                </div>
                                <div class="pt-1 space-y-1">
                                    <template x-for="item in filtered" :key="item.id">
                                        <div 
                                            x-html="highlight(item.label)" 
                                            @mousedown.prevent="select(item)"
                                            class="px-3.5 py-2.5 text-xs text-slate-700 hover:bg-gradient-to-r hover:from-indigo-50/90 hover:to-blue-50/50 hover:text-indigo-700 rounded-xl cursor-pointer font-semibold transition-all duration-150 flex items-center justify-between"
                                        >
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang (Grade)</label>
                            <select wire:model="grade_level" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value="">-- Pilih Jenjang --</option>
                                <option value="umum">Semua Jenjang / Umum</option>
                                <option value="7">Kelas 7</option>
                                <option value="8">Kelas 8</option>
                                <option value="9">Kelas 9</option>
                            </select>
                            @error('grade_level') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div x-data="{
                            open: false,
                            query: @entangle('penulis'),
                            items: {{ json_encode($listPenulis->values()->toArray()) }},
                            get filtered() {
                                if (!this.query || this.query.trim().length < 3) return [];
                                let q = this.query.toLowerCase().trim();
                                return this.items.filter(i => i.toLowerCase().includes(q));
                            },
                            highlight(text) {
                                if (!this.query || this.query.trim().length < 3) return text;
                                let q = this.query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                                let re = new RegExp('(' + q + ')', 'gi');
                                return text.replace(re, m => '<span class=\'text-indigo-600 font-extrabold underline decoration-indigo-400/50 decoration-2 underline-offset-2\'>' + m + '</span>');
                            },
                            select(val) {
                                let formatted = val.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                                this.query = formatted;
                                $wire.set('penulis', formatted);
                                this.open = false;
                            },
                            formatOnBlur() {
                                if (this.query) {
                                    let formatted = this.query.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                                    this.query = formatted;
                                    $wire.set('penulis', formatted);
                                }
                                setTimeout(() => { this.open = false; }, 150);
                            }
                        }" class="relative group">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Penulis</label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <input 
                                    x-model="query"
                                    x-on:focus="if (query && query.trim().length >= 3) open = true"
                                    x-on:input="open = (query && query.trim().length >= 3)"
                                    x-on:blur="formatOnBlur()"
                                    @keydown.escape="open = false"
                                    @click.outside="open = false"
                                    type="text" 
                                    placeholder="Nama Penulis (min. 3 huruf)" 
                                    class="w-full pl-9 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium transition-all"
                                >
                                <div class="absolute right-2.5 flex items-center gap-1">
                                    <span x-show="query && query.trim().length > 0 && query.trim().length < 3" class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        min 3 huruf
                                    </span>
                                    <span x-show="open && filtered.length > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span x-text="filtered.length + ' saran'"></span>
                                    </span>
                                    <button 
                                        type="button" 
                                        x-show="query" 
                                        @click="query = ''; $wire.set('penulis', null); open = false;" 
                                        class="p-1 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50"
                                        title="Kosongkan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div 
                                x-show="open && filtered.length > 0" 
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                x-cloak 
                                class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-2xl shadow-2xl shadow-indigo-900/10 z-50 max-h-56 overflow-y-auto p-1.5 divide-y divide-slate-100 text-xs"
                            >
                                <div class="px-3 py-2 bg-gradient-to-r from-slate-50 via-indigo-50/30 to-slate-50 rounded-xl mb-1 flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Saran Database
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-500" x-text="filtered.length + ' pilihan cocok'"></span>
                                </div>
                                <div class="pt-1 space-y-1">
                                    <template x-for="item in filtered" :key="item">
                                        <div 
                                            x-html="highlight(item)" 
                                            @mousedown.prevent="select(item)"
                                            class="px-3.5 py-2.5 text-xs text-slate-700 hover:bg-gradient-to-r hover:from-indigo-50/90 hover:to-blue-50/50 hover:text-indigo-700 rounded-xl cursor-pointer font-semibold transition-all duration-150 flex items-center justify-between"
                                        >
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div x-data="{
                            open: false,
                            query: @entangle('penerbit'),
                            items: {{ json_encode($listPenerbit->values()->toArray()) }},
                            get filtered() {
                                if (!this.query || this.query.trim().length < 3) return [];
                                let q = this.query.toLowerCase().trim();
                                return this.items.filter(i => i.toLowerCase().includes(q));
                            },
                            highlight(text) {
                                if (!this.query || this.query.trim().length < 3) return text;
                                let q = this.query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                                let re = new RegExp('(' + q + ')', 'gi');
                                return text.replace(re, m => '<span class=\'text-indigo-600 font-extrabold underline decoration-indigo-400/50 decoration-2 underline-offset-2\'>' + m + '</span>');
                            },
                            select(val) {
                                let formatted = val.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                                this.query = formatted;
                                $wire.set('penerbit', formatted);
                                this.open = false;
                            },
                            formatOnBlur() {
                                if (this.query) {
                                    let formatted = this.query.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                                    this.query = formatted;
                                    $wire.set('penerbit', formatted);
                                }
                                setTimeout(() => { this.open = false; }, 150);
                            }
                        }" class="relative group">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Penerbit</label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <input 
                                    x-model="query"
                                    x-on:focus="if (query && query.trim().length >= 3) open = true"
                                    x-on:input="open = (query && query.trim().length >= 3)"
                                    x-on:blur="formatOnBlur()"
                                    @keydown.escape="open = false"
                                    @click.outside="open = false"
                                    type="text" 
                                    placeholder="Nama Penerbit (min. 3 huruf)" 
                                    class="w-full pl-9 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium transition-all"
                                >
                                <div class="absolute right-2.5 flex items-center gap-1">
                                    <span x-show="query && query.trim().length > 0 && query.trim().length < 3" class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        min 3 huruf
                                    </span>
                                    <span x-show="open && filtered.length > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span x-text="filtered.length + ' saran'"></span>
                                    </span>
                                    <button 
                                        type="button" 
                                        x-show="query" 
                                        @click="query = ''; $wire.set('penerbit', null); open = false;" 
                                        class="p-1 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50"
                                        title="Kosongkan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div 
                                x-show="open && filtered.length > 0" 
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                x-cloak 
                                class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-2xl shadow-2xl shadow-indigo-900/10 z-50 max-h-56 overflow-y-auto p-1.5 divide-y divide-slate-100 text-xs"
                            >
                                <div class="px-3 py-2 bg-gradient-to-r from-slate-50 via-indigo-50/30 to-slate-50 rounded-xl mb-1 flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Saran Database
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-500" x-text="filtered.length + ' pilihan cocok'"></span>
                                </div>
                                <div class="pt-1 space-y-1">
                                    <template x-for="item in filtered" :key="item">
                                        <div 
                                            x-html="highlight(item)" 
                                            @mousedown.prevent="select(item)"
                                            class="px-3.5 py-2.5 text-xs text-slate-700 hover:bg-gradient-to-r hover:from-indigo-50/90 hover:to-blue-50/50 hover:text-indigo-700 rounded-xl cursor-pointer font-semibold transition-all duration-150 flex items-center justify-between"
                                        >
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Terbit</label>
                            <input wire:model="tahun_terbit" type="number" placeholder="2026" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ISBN</label>
                            <input wire:model="isbn" type="text" placeholder="978-xxx-xxx" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Rak</label>
                            <input wire:model="lokasi_rak" type="text" placeholder="Rak A1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                    </div>

                    <div x-data="{
                        compressing: false,
                        handleFileSelect(event) {
                            const file = event.target.files[0];
                            if (!file || !file.type.startsWith('image/')) return;

                            this.compressing = true;
                            const maxWidth = 800;
                            const maxHeight = 1000;
                            const quality = 0.75;

                            const reader = new FileReader();
                            reader.onload = (e) => {
                                const img = new Image();
                                img.onload = () => {
                                    let width = img.width;
                                    let height = img.height;

                                    if (width > maxWidth || height > maxHeight) {
                                        if (width / height > maxWidth / maxHeight) {
                                            height = Math.round((height * maxWidth) / width);
                                            width = maxWidth;
                                        } else {
                                            width = Math.round((width * maxHeight) / height);
                                            height = maxHeight;
                                        }
                                    }

                                    const canvas = document.createElement('canvas');
                                    canvas.width = width;
                                    canvas.height = height;

                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(img, 0, 0, width, height);

                                    canvas.toBlob((blob) => {
                                        this.compressing = false;
                                        if (!blob) return;
                                        const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', {
                                            type: 'image/jpeg',
                                            lastModified: Date.now()
                                        });

                                        $wire.upload('sampul_buku', compressedFile, 
                                            () => {}, 
                                            () => { alert('Gagal mengunggah gambar'); }
                                        );
                                    }, 'image/jpeg', quality);
                                };
                                img.src = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    }">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sampul Buku (Opsional - Kompresi Otomatis Browser)</label>
                        <input type="file" accept="image/*" @change="handleFileSelect($event)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
                        @error('sampul_buku') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        
                        <div x-show="compressing" class="text-xs text-amber-600 mt-1 font-semibold flex items-center gap-1.5" style="display: none;">
                            <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengompres gambar di browser...
                        </div>
                        <div wire:loading wire:target="sampul_buku" class="text-xs text-brand-primary mt-1 font-semibold flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengunggah ke server...
                        </div>
                        @if ($sampul_buku)
                            <div class="mt-2 flex items-center gap-3">
                                <img src="{{ $sampul_buku->temporaryUrl() }}" class="w-20 h-28 object-cover rounded-lg border border-slate-200 shadow-sm">
                                <span class="text-xs text-emerald-600 font-semibold">✓ Gambar terkompresi & siap disimpan</span>
                            </div>
                        @endif
                    </div>

                    {{-- Upload File PDF (E-Book) --}}
                    <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl space-y-2">
                        <label class="block text-xs font-bold text-emerald-700">File PDF E-Book (Opsional / Baca Online)</label>
                        <input type="file" wire:model="file_pdf" accept="application/pdf" class="text-xs text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-100 file:text-emerald-700 hover:file:bg-emerald-200">
                        <p class="text-[10px] text-slate-500">Format PDF. Maksimal 50MB.</p>
                        @error('file_pdf') <span class="text-rose-500 text-[11px] block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <h4 class="text-sm font-bold text-slate-800 mb-1">Generate Eksemplar Awal & Inventaris</h4>
                        <p class="text-[11px] text-slate-500 mb-4">Isi data inventaris dan jumlah eksemplar yang ingin dibuat otomatis setelah buku ini disimpan.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Asal Buku *</label>
                                <select wire:model="asal_buku" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                    <option value="">-- Pilih Asal Buku --</option>
                                    <option value="Pembelian">Pembelian</option>
                                    <option value="Hibah">Hibah</option>
                                    <option value="Tukar">Tukar</option>
                                    <option value="Terbitan Sendiri">Terbitan Sendiri</option>
                                </select>
                                @error('asal_buku') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Buku (Rp)</label>
                                <input wire:model="harga_buku" type="number" placeholder="Contoh: 50000" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Eksemplar *</label>
                                <input wire:model="jumlah_eksemplar" type="number" min="1" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                @error('jumlah_eksemplar') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Prefix Kode/Singkatan Buku *</label>
                                <input wire:model="prefix_kode" type="text" required placeholder="Misal: INF, MAT" maxlength="10" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <p class="text-[10px] text-slate-500 mt-1">Contoh: INF untuk Informatika</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="$wire.set('showInputModal', false)" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-primary/20">
                            Simpan & Generate Barcode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== MODAL UNDUH KATALOG ===== --}}
    @if($showUnduhModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             x-data="{
                 nonFiksiId: '{{ $nonFiksiKategoriId }}',
                 get showMapel() {
                     return $wire.filterKategoriUnduh.includes(this.nonFiksiId);
                 }
             }">
            <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 lg:p-8 space-y-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Unduh Katalog Buku</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih koleksi dan format dokumen yang ingin diunduh.</p>
                    </div>
                    <button type="button" wire:click="$set('showUnduhModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Pilihan Koleksi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Pilih Koleksi</label>
                    <p class="text-[11px] text-slate-400 mb-3">Kosongkan semua untuk mengunduh seluruh koleksi.</p>

                    {{-- Tombol Pilih/Hapus Semua --}}
                    <div class="flex gap-2 mb-3">
                        <button type="button" wire:click="$set('filterKategoriUnduh', {{ json_encode($kategoriList->pluck('id')->toArray()) }})" class="px-3 py-1.5 text-[11px] font-bold bg-brand-primary/10 text-brand-primary rounded-lg hover:bg-brand-primary/20 transition">
                            Pilih Semua
                        </button>
                        <button type="button" wire:click="$set('filterKategoriUnduh', [])" class="px-3 py-1.5 text-[11px] font-bold bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition">
                            Hapus Pilihan
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($kategoriList as $kat)
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition
                                {{ in_array($kat->id, $filterKategoriUnduh) ? 'border-brand-primary/50 bg-brand-primary/5' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                <input type="checkbox"
                                    wire:model.live="filterKategoriUnduh"
                                    value="{{ $kat->id }}"
                                    class="rounded accent-brand-primary w-4 h-4">
                                <span class="text-xs font-semibold text-slate-700">{{ $kat->nama_kategori }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Pilihan Mata Pelajaran (hanya muncul jika Non Fiksi dipilih) --}}
                <div x-show="showMapel" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                        <label class="block text-xs font-bold text-amber-800 mb-1">
                            🎓 Filter Mata Pelajaran (Non Fiksi)
                        </label>
                        <p class="text-[11px] text-amber-600 mb-3">Kosongkan untuk mengunduh semua mapel dari Non Fiksi.</p>

                        <div class="flex gap-2 mb-3">
                            <button type="button" wire:click="$set('filterMapelUnduh', {{ json_encode($mapelList->pluck('id')->toArray()) }})" class="px-3 py-1.5 text-[11px] font-bold bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition">
                                Pilih Semua Mapel
                            </button>
                            <button type="button" wire:click="$set('filterMapelUnduh', [])" class="px-3 py-1.5 text-[11px] font-bold bg-white text-slate-600 rounded-lg hover:bg-slate-50 border border-slate-200 transition">
                                Hapus Pilihan
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                            @foreach($mapelList as $mapel)
                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition
                                    {{ in_array($mapel->id, $filterMapelUnduh) ? 'border-amber-400 bg-amber-50' : 'border-slate-200 bg-white hover:border-amber-300' }}">
                                    <input type="checkbox"
                                        wire:model.live="filterMapelUnduh"
                                        value="{{ $mapel->id }}"
                                        class="rounded accent-amber-500 w-4 h-4">
                                    <span class="text-xs font-semibold text-slate-700">{{ $mapel->nama_mapel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Format Unduhan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Format Unduhan</label>
                    <div class="flex gap-3">
                        <label class="flex-1 flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition
                            {{ $formatUnduh === 'pdf' ? 'border-rose-300 bg-rose-50' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" wire:model.live="formatUnduh" value="pdf" class="accent-rose-500 w-4 h-4">
                            <div>
                                <div class="text-xs font-bold text-slate-800">📄 PDF</div>
                                <div class="text-[10px] text-slate-500">A4 Landscape, siap cetak</div>
                            </div>
                        </label>
                        <label class="flex-1 flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition
                            {{ $formatUnduh === 'excel' ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" wire:model.live="formatUnduh" value="excel" class="accent-emerald-600 w-4 h-4">
                            <div>
                                <div class="text-xs font-bold text-slate-800">📊 Excel</div>
                                <div class="text-[10px] text-slate-500">Format .xlsx, dapat diedit</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showUnduhModal', false)" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                        Batal
                    </button>
                    <button type="button" wire:click="downloadKatalog" class="px-5 py-2.5 {{ $formatUnduh === 'excel' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' }} text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Unduh {{ strtoupper($formatUnduh) }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL DETAIL EKSEMPLAR BUKU ===== --}}
    @if($showDetailEksemplarModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full p-6 lg:p-8 space-y-6 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Detail Buku</h3>
                        @if($selectedBukuDetail)
                            <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
                                <span class="font-bold text-slate-700">{{ $selectedBukuDetail->judul }}</span> &bull; 
                                {{ $selectedBukuDetail->penulis ?? 'Tanpa Penulis' }} &bull; 
                                {{ $selectedBukuDetail->penerbit ?? 'Tanpa Penerbit' }} &bull; 
                                Rak: {{ $selectedBukuDetail->lokasi_rak ?? '-' }} &bull; 
                                ISBN: {{ $selectedBukuDetail->isbn ?? '-' }}
                            </p>
                        @endif
                    </div>
                    <button type="button" wire:click="$set('showDetailEksemplarModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Action Buttons & Search Bar --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($selectedBukuDetail)
                            <a href="{{ route('perpustakaan.cetak-barcode', $selectedBukuDetail->id) }}" target="_blank" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs inline-flex items-center gap-1.5 border border-indigo-200 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Cetak Barcode (Semua)
                            </a>
                            <a href="{{ route('perpustakaan.cetak-label-spine', $selectedBukuDetail->id) }}" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs inline-flex items-center gap-1.5 border border-slate-200 transition-all">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-7 5h7"></path></svg>
                                Cetak Label (Semua)
                            </a>
                        @endif
                    </div>

                    <div class="relative w-full sm:w-64">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input wire:model.live.debounce.300ms="searchEksemplar" type="text" placeholder="Cari kode / status / kondisi..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    </div>
                </div>

                {{-- Table Eksemplar --}}
                <div class="overflow-y-auto flex-1 rounded-2xl border border-slate-200/80">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80 sticky top-0 bg-slate-50">
                            <tr>
                                <th class="p-3 pl-4">Kode eksemplar</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Kondisi fisik</th>
                                <th class="p-3 pr-4 text-left">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($eksemplarDetailList as $eks)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-3 pl-4 font-mono font-bold text-slate-900">{{ $eks->kode_eksemplar }}</td>
                                    <td class="p-3">
                                        @php
                                            $statusColor = match($eks->status) {
                                                'tersedia' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'dipinjam' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'rusak'    => 'bg-rose-100 text-rose-700 border-rose-200',
                                                'hilang'   => 'bg-slate-200 text-slate-700 border-slate-300',
                                                default    => 'bg-slate-100 text-slate-600 border-slate-200',
                                            };
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border inline-block capitalize {{ $statusColor }}">
                                            {{ $eks->status }}
                                        </span>
                                    </td>
                                    <td class="p-3 capitalize text-slate-600">
                                        {{ str_replace('_', ' ', $eks->kondisi_fisik) }}
                                    </td>
                                    <td class="p-3 pr-4 text-left">
                                        <div class="flex items-center justify-start gap-1.5 flex-wrap">
                                            <button type="button" wire:click="openEditEksemplarModal('{{ $eks->id }}')" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded-lg text-[10px] inline-flex items-center gap-1 border border-amber-200 transition-all shadow-2xs" title="Edit Eksemplar">
                                                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit
                                            </button>
                                            <a href="{{ route('perpustakaan.cetak-barcode-eksemplar', $eks->id) }}" target="_blank" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg text-[10px] inline-flex items-center gap-1 border border-indigo-200 transition-all shadow-2xs" title="Cetak Barcode Eksemplar Ini">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                                Cetak Barcode
                                            </a>
                                            <a href="{{ route('perpustakaan.cetak-label-spine-eksemplar', $eks->id) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[10px] inline-flex items-center gap-1 border border-slate-200 transition-all shadow-2xs" title="Cetak Spine Eksemplar Ini">
                                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-7 5h7"></path></svg>
                                                Cetak Spine
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400">
                                        Belum ada eksemplar fisik terdaftar untuk buku ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer Actions --}}
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <span class="text-xs text-slate-500">
                        Total Eksemplar: <strong class="text-slate-800">{{ $eksemplarDetailList->count() }}</strong>
                    </span>
                    <button type="button" wire:click="$set('showDetailEksemplarModal', false)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL EDIT EKSEMPLAR ===== --}}
    @if($showEditEksemplarModal ?? false)
        <div class="fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 lg:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Edit Eksemplar Buku</h3>
                        <p class="text-xs font-mono font-bold text-brand-primary mt-0.5">
                            {{ $editingEksemplarKode }}
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showEditEksemplarModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="simpanEditEksemplar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status *</label>
                        <select wire:model="editingEksemplarStatus" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                            <option value="tersedia">Tersedia</option>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="rusak">Rusak</option>
                            <option value="hilang">Hilang</option>
                        </select>
                        @error('editingEksemplarStatus') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Fisik *</label>
                        <select wire:model="editingEksemplarKondisiFisik" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                        @error('editingEksemplarKondisiFisik') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="$set('showEditEksemplarModal', false)" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-primary/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== MODAL EDIT BUKU (LIVEWIRE) ===== --}}
    @if($showEditBukuModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 lg:p-8 space-y-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Edit Data Buku</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi katalog buku, sampul, dan file E-Book.</p>
                    </div>
                    <button type="button" wire:click="$set('showEditBukuModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="simpanEditBuku" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Buku *</label>
                        <input wire:model="edit_judul" type="text" required placeholder="Judul Buku" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        @error('edit_judul') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Koleksi / Kategori *</label>
                            <select wire:model.live="edit_kategori_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value="">-- Pilih Koleksi / Kategori --</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('edit_kategori_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        
                        @php
                            $selectedKatEdit = $kategoriList->firstWhere('id', $edit_kategori_id);
                            $isNonFiksiEdit = $selectedKatEdit && strtolower(trim($selectedKatEdit->nama_kategori)) === 'non fiksi';
                        @endphp
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran</label>
                            <select wire:model="edit_mapel_id" {{ $isNonFiksiEdit ? '' : 'disabled' }} class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary disabled:opacity-50 disabled:bg-slate-100">
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($mapelList as $mapel)
                                    <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                                @endforeach
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div x-data="{
                            open: false,
                            selectedId: @entangle('edit_klasifikasi_ddc_id'),
                            search: '',
                            items: {{ json_encode($ddcList->map(fn($d) => ['id' => $d->id, 'label' => $d->kode_ddc . ' - ' . $d->kategori])->values()->toArray()) }},
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
                                return text.replace(re, m => '<span class=\'text-indigo-600 font-extrabold underline decoration-indigo-400/50 decoration-2 underline-offset-2\'>' + m + '</span>');
                            },
                            select(item) {
                                this.selectedId = item.id;
                                this.search = item.label;
                                $wire.set('edit_klasifikasi_ddc_id', item.id);
                                this.open = false;
                            },
                            clearIfInvalid() {
                                setTimeout(() => {
                                    if (!this.search || this.search.trim() === '') {
                                        this.selectedId = null;
                                        $wire.set('edit_klasifikasi_ddc_id', null);
                                    } else {
                                        let found = this.items.find(i => i.label.toLowerCase() === this.search.toLowerCase().trim());
                                        if (found) {
                                            this.selectedId = found.id;
                                            this.search = found.label;
                                            $wire.set('edit_klasifikasi_ddc_id', found.id);
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
                            <label class="block text-xs font-bold text-slate-700 mb-1">Klasifikasi DDC (Opsional)</label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </div>
                                <input 
                                    x-model="search"
                                    x-on:focus="if (search && search.trim().length >= 3) open = true"
                                    x-on:input="open = (search && search.trim().length >= 3); if (!search) { selectedId = null; $wire.set('edit_klasifikasi_ddc_id', null); }"
                                    x-on:blur="clearIfInvalid()"
                                    @keydown.escape="open = false"
                                    @click.outside="open = false"
                                    type="text" 
                                    placeholder="Cari Kode / Kategori DDC (min. 3 huruf)" 
                                    class="w-full pl-9 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium transition-all"
                                >
                                <div class="absolute right-2.5 flex items-center gap-1">
                                    <span x-show="search && search.trim().length > 0 && search.trim().length < 3" class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        min 3 huruf
                                    </span>
                                    <span x-show="open && filtered.length > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span x-text="filtered.length + ' DDC'"></span>
                                    </span>
                                    <button 
                                        type="button" 
                                        x-show="selectedId" 
                                        @click="selectedId = null; search = ''; open = false; $wire.set('edit_klasifikasi_ddc_id', null);" 
                                        class="p-1 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50"
                                        title="Hapus pilihan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div 
                                x-show="open && filtered.length > 0" 
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                x-cloak 
                                class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-2xl shadow-2xl shadow-indigo-900/10 z-50 max-h-56 overflow-y-auto p-1.5 divide-y divide-slate-100 text-xs"
                            >
                                <div class="px-3 py-2 bg-gradient-to-r from-slate-50 via-indigo-50/30 to-slate-50 rounded-xl mb-1 flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        Saran Klasifikasi DDC
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-500" x-text="filtered.length + ' DDC cocok'"></span>
                                </div>
                                <div class="pt-1 space-y-1">
                                    <template x-for="item in filtered" :key="item.id">
                                        <div 
                                            x-html="highlight(item.label)" 
                                            @mousedown.prevent="select(item)"
                                            class="px-3.5 py-2.5 text-xs text-slate-700 hover:bg-gradient-to-r hover:from-indigo-50/90 hover:to-blue-50/50 hover:text-indigo-700 rounded-xl cursor-pointer font-semibold transition-all duration-150 flex items-center justify-between"
                                        >
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang (Grade)</label>
                            <select wire:model="edit_grade_level" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value="">-- Pilih Jenjang --</option>
                                <option value="umum">Semua Jenjang / Umum</option>
                                <option value="7">Kelas 7</option>
                                <option value="8">Kelas 8</option>
                                <option value="9">Kelas 9</option>
                            </select>
                            @error('edit_grade_level') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div x-data="{
                            open: false,
                            query: @entangle('edit_penulis'),
                            items: {{ json_encode($listPenulis->values()->toArray()) }},
                            get filtered() {
                                if (!this.query || this.query.trim().length < 3) return [];
                                let q = this.query.toLowerCase().trim();
                                return this.items.filter(i => i.toLowerCase().includes(q));
                            },
                            highlight(text) {
                                if (!this.query || this.query.trim().length < 3) return text;
                                let q = this.query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                                let re = new RegExp('(' + q + ')', 'gi');
                                return text.replace(re, m => '<span class=\'text-indigo-600 font-extrabold underline decoration-indigo-400/50 decoration-2 underline-offset-2\'>' + m + '</span>');
                            },
                            select(val) {
                                let formatted = val.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                                this.query = formatted;
                                $wire.set('edit_penulis', formatted);
                                this.open = false;
                            },
                            formatOnBlur() {
                                if (this.query) {
                                    let formatted = this.query.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                                    this.query = formatted;
                                    $wire.set('edit_penulis', formatted);
                                }
                                setTimeout(() => { this.open = false; }, 150);
                            }
                        }" class="relative group">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Penulis</label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <input 
                                    x-model="query"
                                    x-on:focus="if (query && query.trim().length >= 3) open = true"
                                    x-on:input="open = (query && query.trim().length >= 3)"
                                    x-on:blur="formatOnBlur()"
                                    @keydown.escape="open = false"
                                    @click.outside="open = false"
                                    type="text" 
                                    placeholder="Nama Penulis (min. 3 huruf)" 
                                    class="w-full pl-9 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium transition-all"
                                >
                                <div class="absolute right-2.5 flex items-center gap-1">
                                    <span x-show="query && query.trim().length > 0 && query.trim().length < 3" class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        min 3 huruf
                                    </span>
                                    <span x-show="open && filtered.length > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span x-text="filtered.length + ' saran'"></span>
                                    </span>
                                    <button 
                                        type="button" 
                                        x-show="query" 
                                        @click="query = ''; $wire.set('edit_penulis', null); open = false;" 
                                        class="p-1 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50"
                                        title="Kosongkan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div 
                                x-show="open && filtered.length > 0" 
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                x-cloak 
                                class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-2xl shadow-2xl shadow-indigo-900/10 z-50 max-h-56 overflow-y-auto p-1.5 divide-y divide-slate-100 text-xs"
                            >
                                <div class="px-3 py-2 bg-gradient-to-r from-slate-50 via-indigo-50/30 to-slate-50 rounded-xl mb-1 flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Saran Database
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-500" x-text="filtered.length + ' pilihan cocok'"></span>
                                </div>
                                <div class="pt-1 space-y-1">
                                    <template x-for="item in filtered" :key="item">
                                        <div 
                                            x-html="highlight(item)" 
                                            @mousedown.prevent="select(item)"
                                            class="px-3.5 py-2.5 text-xs text-slate-700 hover:bg-gradient-to-r hover:from-indigo-50/90 hover:to-blue-50/50 hover:text-indigo-700 rounded-xl cursor-pointer font-semibold transition-all duration-150 flex items-center justify-between"
                                        >
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div x-data="{
                            open: false,
                            query: @entangle('edit_penerbit'),
                            items: {{ json_encode($listPenerbit->values()->toArray()) }},
                            get filtered() {
                                if (!this.query || this.query.trim().length < 3) return [];
                                let q = this.query.toLowerCase().trim();
                                return this.items.filter(i => i.toLowerCase().includes(q));
                            },
                            highlight(text) {
                                if (!this.query || this.query.trim().length < 3) return text;
                                let q = this.query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                                let re = new RegExp('(' + q + ')', 'gi');
                                return text.replace(re, m => '<span class=\'text-indigo-600 font-extrabold underline decoration-indigo-400/50 decoration-2 underline-offset-2\'>' + m + '</span>');
                            },
                            select(val) {
                                let formatted = val.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                                this.query = formatted;
                                $wire.set('edit_penerbit', formatted);
                                this.open = false;
                            },
                            formatOnBlur() {
                                if (this.query) {
                                    let formatted = this.query.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                                    this.query = formatted;
                                    $wire.set('edit_penerbit', formatted);
                                }
                                setTimeout(() => { this.open = false; }, 150);
                            }
                        }" class="relative group">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Penerbit</label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <input 
                                    x-model="query"
                                    x-on:focus="if (query && query.trim().length >= 3) open = true"
                                    x-on:input="open = (query && query.trim().length >= 3)"
                                    x-on:blur="formatOnBlur()"
                                    @keydown.escape="open = false"
                                    @click.outside="open = false"
                                    type="text" 
                                    placeholder="Nama Penerbit (min. 3 huruf)" 
                                    class="w-full pl-9 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium transition-all"
                                >
                                <div class="absolute right-2.5 flex items-center gap-1">
                                    <span x-show="query && query.trim().length > 0 && query.trim().length < 3" class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        min 3 huruf
                                    </span>
                                    <span x-show="open && filtered.length > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span x-text="filtered.length + ' saran'"></span>
                                    </span>
                                    <button 
                                        type="button" 
                                        x-show="query" 
                                        @click="query = ''; $wire.set('edit_penerbit', null); open = false;" 
                                        class="p-1 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50"
                                        title="Kosongkan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div 
                                x-show="open && filtered.length > 0" 
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                x-cloak 
                                class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-2xl shadow-2xl shadow-indigo-900/10 z-50 max-h-56 overflow-y-auto p-1.5 divide-y divide-slate-100 text-xs"
                            >
                                <div class="px-3 py-2 bg-gradient-to-r from-slate-50 via-indigo-50/30 to-slate-50 rounded-xl mb-1 flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Saran Database
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-500" x-text="filtered.length + ' pilihan cocok'"></span>
                                </div>
                                <div class="pt-1 space-y-1">
                                    <template x-for="item in filtered" :key="item">
                                        <div 
                                            x-html="highlight(item)" 
                                            @mousedown.prevent="select(item)"
                                            class="px-3.5 py-2.5 text-xs text-slate-700 hover:bg-gradient-to-r hover:from-indigo-50/90 hover:to-blue-50/50 hover:text-indigo-700 rounded-xl cursor-pointer font-semibold transition-all duration-150 flex items-center justify-between"
                                        >
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Terbit</label>
                            <input wire:model="edit_tahun_terbit" type="number" placeholder="2026" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ISBN</label>
                            <input wire:model="edit_isbn" type="text" placeholder="ISBN" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Rak</label>
                            <input wire:model="edit_lokasi_rak" type="text" placeholder="Misal: Rak A1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                    </div>

                    {{-- Upload Sampul Buku (Kompresi Browser + Backend) --}}
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3"
                         x-data="{
                             compressing: false,
                             handleFileSelect(event) {
                                 const file = event.target.files[0];
                                 if (!file) return;
                                 if (!file.type.startsWith('image/')) return;

                                 this.compressing = true;
                                 const maxWidth = 800;
                                 const maxHeight = 1000;
                                 const quality = 0.75;

                                 const reader = new FileReader();
                                 reader.onload = (e) => {
                                     const img = new Image();
                                     img.onload = () => {
                                         let width = img.width;
                                         let height = img.height;

                                         if (width > maxWidth || height > maxHeight) {
                                             if (width / height > maxWidth / maxHeight) {
                                                 height = Math.round((height * maxWidth) / width);
                                                 width = maxWidth;
                                             } else {
                                                 width = Math.round((width * maxHeight) / height);
                                                 height = maxHeight;
                                             }
                                         }

                                         const canvas = document.createElement('canvas');
                                         canvas.width = width;
                                         canvas.height = height;

                                         const ctx = canvas.getContext('2d');
                                         ctx.drawImage(img, 0, 0, width, height);

                                         canvas.toBlob((blob) => {
                                             this.compressing = false;
                                             if (!blob) return;
                                             const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', {
                                                 type: 'image/jpeg',
                                                 lastModified: Date.now()
                                             });

                                             $wire.upload('edit_sampul_buku', compressedFile, 
                                                 () => {}, 
                                                 () => { alert('Gagal mengunggah gambar'); }
                                             );
                                         }, 'image/jpeg', quality);
                                     };
                                     img.src = e.target.result;
                                 };
                                 reader.readAsDataURL(file);
                             }
                         }">
                        <label class="block text-xs font-bold text-slate-800">Sampul Buku (Kompresi Otomatis)</label>
                        @if($edit_existing_sampul)
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/' . $edit_existing_sampul) }}" class="w-12 h-16 object-cover rounded-lg border border-slate-300">
                                <div>
                                    <span class="text-xs font-semibold text-slate-700">Sampul Saat Ini:</span>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ basename($edit_existing_sampul) }}</p>
                                </div>
                            </div>
                        @endif
                        <input type="file" accept="image/*" @change="handleFileSelect($event)" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
                        @error('edit_sampul_buku') <span class="text-rose-500 text-[11px] block">{{ $message }}</span> @enderror

                        <div x-show="compressing" class="text-xs text-amber-600 font-semibold flex items-center gap-1.5" style="display: none;">
                            <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengompres gambar di browser...
                        </div>
                        <div wire:loading wire:target="edit_sampul_buku" class="text-xs text-brand-primary font-semibold flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengunggah ke server...
                        </div>
                        @if ($edit_sampul_buku)
                            <div class="mt-2 flex items-center gap-3">
                                <img src="{{ $edit_sampul_buku->temporaryUrl() }}" class="w-16 h-20 object-cover rounded-lg border border-slate-200 shadow-sm">
                                <span class="text-xs text-emerald-600 font-semibold">✓ Gambar baru terkompresi & siap disimpan</span>
                            </div>
                        @endif
                    </div>

                    {{-- Upload File PDF E-Book --}}
                    <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl space-y-3">
                        <label class="block text-xs font-bold text-emerald-700">File PDF E-Book (Baca Online)</label>
                        @if($edit_existing_pdf)
                            <div class="flex items-center justify-between p-2.5 bg-white rounded-xl border border-emerald-200 shadow-sm">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold">Ada PDF</span>
                                    <span class="text-xs text-slate-600 font-mono">{{ basename($edit_existing_pdf) }}</span>
                                </div>
                                <a href="{{ asset('storage/' . $edit_existing_pdf) }}" target="_blank" class="text-xs text-emerald-600 font-bold hover:underline">Pratinjau →</a>
                            </div>
                        @endif
                        <input type="file" wire:model="edit_file_pdf" accept="application/pdf" class="text-xs text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-100 file:text-emerald-700 hover:file:bg-emerald-200">
                        <p class="text-[10px] text-slate-500">Format PDF. Maksimal 50MB. Kosongkan jika tidak ingin mengubah file PDF.</p>
                        @error('edit_file_pdf') <span class="text-rose-500 text-[11px] block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="$set('showEditBukuModal', false)" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-primary/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
