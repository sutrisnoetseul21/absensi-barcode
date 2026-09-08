<div class="space-y-4">
    {{-- Header Kompak & Responsif --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 border border-violet-100">
                <i class="far fa-question-circle text-sm"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold text-slate-900">FAQ Sekolah</h2>
                    <span class="text-[11px] font-bold text-violet-700 bg-violet-50 px-2 py-0.5 rounded-full border border-violet-100">{{ $faqs->total() }} Pertanyaan</span>
                </div>
                <p class="text-xs text-slate-400">Kelola tanya jawab publik yang tampil di halaman /faq dan portal layanan</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Kategori -->
            <select wire:model.live="filterKategori" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-800 text-xs focus:ring-2 focus:ring-violet-400 outline-none">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

            <!-- Search -->
            <div class="relative flex-1 sm:w-56">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari pertanyaan / jawaban..." class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-900 text-xs focus:ring-2 focus:ring-violet-400 focus:bg-white outline-none">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Tombol Tambah -->
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-violet-600 text-white rounded-lg text-xs font-bold hover:bg-violet-700 transition-colors shadow-xs shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah FAQ
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="px-3.5 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- List FAQ Items --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse($faqs as $item)
            <div class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="flex-1 space-y-1.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 uppercase tracking-wider">
                            {{ $item->kategori }}
                        </span>
                        <span class="text-[10px] font-extrabold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">
                            Urutan #{{ $item->urutan }}
                        </span>
                        <button wire:click="toggleStatus('{{ $item->id }}')" 
                                class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider transition-colors {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                            {{ $item->is_active ? 'Tampil' : 'Disembunyikan' }}
                        </button>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm leading-snug">
                        {{ $item->pertanyaan }}
                    </h3>
                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                        {{ $item->jawaban }}
                    </p>
                </div>
                <div class="flex items-center gap-1 shrink-0 self-end sm:self-center">
                    <button wire:click="openEdit('{{ $item->id }}')" class="p-1.5 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button wire:click="confirmDelete('{{ $item->id }}')" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400">
                <i class="far fa-question-circle text-3xl mb-2 text-slate-300"></i>
                <p class="text-xs">Belum ada data FAQ yang sesuai.</p>
            </div>
            @endforelse
        </div>
    </div>

    @if($faqs->hasPages())
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-4 py-3">{{ $faqs->links() }}</div>
    @endif

    {{-- Modal Tambah / Edit --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-start justify-center pt-8 sm:pt-12 px-4 pb-12">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-xs">
                        <i class="far fa-question-circle"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-base">{{ $editingId ? 'Edit FAQ' : 'Tambah FAQ Baru' }}</h3>
                </div>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="save" class="p-5 space-y-4">
                {{-- Kategori & Rekomendasi Pilihan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori FAQ *</label>
                    <input type="text" wire:model="kategori" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none" placeholder="Contoh: SPMB, Perpustakaan, UKS...">
                    @error('kategori') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror

                    <!-- Pilihan Cepat Kategori -->
                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                        <span class="text-[10px] text-slate-400 font-semibold">Pilih cepat:</span>
                        @foreach($defaultCategories as $catItem)
                            <button type="button" 
                                    wire:click="$set('kategori', '{{ $catItem }}')" 
                                    class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-600 hover:bg-violet-50 hover:text-violet-700 transition-colors">
                                {{ $catItem }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Pertanyaan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan *</label>
                    <input type="text" wire:model="pertanyaan" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none font-semibold" placeholder="Contoh: Berapa biaya pendaftaran SPMB?">
                    @error('pertanyaan') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Jawaban --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jawaban *</label>
                    <textarea wire:model="jawaban" rows="4" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none leading-relaxed" placeholder="Tuliskan jawaban yang jelas dan lengkap..."></textarea>
                    @error('jawaban') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Urutan & Status Aktif --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center pt-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Urut</label>
                        <input type="number" wire:model="urutan" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-400 outline-none">
                    </div>
                    <div class="sm:pt-5">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 text-violet-600 rounded border-slate-300 focus:ring-violet-400">
                            <span class="text-xs text-slate-700 font-semibold">Tampilkan di Publik (/faq)</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-xl text-xs font-bold hover:bg-violet-700 transition-colors shadow-xs">
                        {{ $editingId ? 'Simpan Perubahan' : 'Simpan FAQ' }}
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
            <h3 class="font-extrabold text-slate-900 text-base mb-1">Hapus FAQ ini?</h3>
            <p class="text-xs text-slate-500 mb-4">Pertanyaan ini tidak akan tampil lagi di halaman FAQ sekolah.</p>
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
