<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900">Prestasi Sekolah</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola data prestasi siswa dan sekolah</p>
            </div>
            <button wire:click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Prestasi
            </button>
        </div>
        <div class="mt-4">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari prestasi..." class="w-full sm:max-w-xs px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:ring-2 focus:ring-violet-400 outline-none">
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($prestasis as $item)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow">
            @if($item->thumbnail)
                <img src="{{ asset('storage/' . $item->thumbnail) }}" class="w-full h-36 object-cover">
            @else
                <div class="w-full h-36 bg-gradient-to-br from-yellow-50 to-amber-100 flex items-center justify-center">
                    <svg class="w-12 h-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            @endif
            <div class="p-4">
                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg uppercase">{{ $item->is_published ? 'Aktif' : 'Draft' }}</span>
                <h3 class="font-bold text-slate-800 mt-2 text-sm line-clamp-2">{{ $item->judul }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ $item->created_at?->format('d/m/Y') }}</p>
                <div class="flex justify-end gap-2 mt-3 pt-3 border-t border-slate-100">
                    <button wire:click="openEdit('{{ $item->id }}')" class="p-1.5 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button wire:click="confirmDelete('{{ $item->id }}')" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm">Belum ada data prestasi</p>
        </div>
        @endforelse
    </div>

    @if($prestasis->hasPages())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-5 py-4">{{ $prestasis->links() }}</div>
    @endif

    {{-- Modal Tambah/Edit --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-start justify-center pt-8 pb-12 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden my-auto" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-base shadow-sm">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-lg leading-tight">{{ $editingId ? 'Edit Prestasi' : 'Tambah Prestasi' }}</h3>
                        <p class="text-xs text-slate-500">Kelola riwayat capaian prestasi siswa atau sekolah</p>
                    </div>
                </div>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Judul Prestasi <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="judul" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 focus:border-violet-400 outline-none transition-all placeholder:text-slate-400" placeholder="Contoh: Juara 1 Olimpiade Sains Nasional...">
                    @error('judul') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status Publikasi</label>
                    <select wire:model="is_published" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none cursor-pointer bg-white transition-all">
                        <option value="1">Published (Tampilkan di Web)</option>
                        <option value="0">Draft (Sembunyikan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Deskripsi Prestasi <span class="text-rose-500">*</span></label>
                    <div wire:ignore wire:key="portal-prestasi-editor-wrapper-{{ $editingId ?? 'create' }}" x-data="portalWebTinyMCE(@entangle('konten'), 'portal-prestasi-editor-{{ $editingId ?? 'create' }}', 350)" class="rounded-xl overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-violet-400 focus-within:border-violet-400 transition-all bg-white shadow-inner">
                        <textarea x-ref="editor" id="portal-prestasi-editor-{{ $editingId ?? 'create' }}" class="w-full"></textarea>
                    </div>
                    @error('konten') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Foto Prestasi / Sertifikat</label>
                    @if($existingFoto)
                        <div class="mb-3 flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <img src="{{ asset('storage/' . $existingFoto) }}" class="h-16 w-24 rounded-lg object-cover border border-slate-200 shadow-sm">
                            <div class="text-xs text-slate-500">
                                <span class="font-semibold text-slate-700 block">Foto Saat Ini</span>
                                Unggah file baru di bawah jika ingin mengganti.
                            </div>
                        </div>
                    @endif
                    <input type="file" wire:model="foto" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 transition-all">
                    <div wire:loading wire:target="foto" class="text-xs text-amber-600 mt-1 font-semibold"><i class="fas fa-spinner fa-spin"></i> Mengunggah file foto...</div>
                    @error('foto') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-violet-600 hover:bg-violet-700 rounded-xl transition-colors shadow-md shadow-violet-500/20 flex items-center gap-2" wire:loading.attr="disabled">
                        <span wire:loading.remove><i class="fas fa-check mr-1"></i> Simpan Prestasi</span>
                        <span wire:loading class="flex items-center gap-1.5"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
            <h3 class="font-extrabold text-slate-900 mb-1">Hapus Prestasi?</h3>
            <p class="text-sm text-slate-500 mb-5">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl">Batal</button>
                <button wire:click="delete" class="flex-1 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
