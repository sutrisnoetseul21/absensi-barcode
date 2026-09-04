<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900">Pelayanan Publik</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola link layanan publik yang tampil di web sekolah</p>
            </div>
            <button wire:click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Layanan
            </button>
        </div>
        <div class="mt-4">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari layanan..." class="w-full sm:max-w-xs px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm focus:ring-2 focus:ring-violet-400 outline-none">
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Layanan</th>
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">URL</th>
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Urutan</th>
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pelayanans as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-800">{{ $item->title }}</p>
                            @if($item->description)
                                <p class="text-xs text-slate-400 mt-0.5 truncate max-w-xs">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ $item->url }}" target="_blank" class="text-xs text-violet-600 hover:underline truncate block max-w-xs">{{ $item->url }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $item->order }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400 text-sm">Belum ada layanan publik</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pelayanans->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">{{ $pelayanans->links() }}</div>
        @endif
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-start justify-center pt-12 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="font-extrabold text-slate-900 text-lg">{{ $editingId ? 'Edit Layanan' : 'Tambah Layanan Publik' }}</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Layanan *</label>
                    <input type="text" wire:model="title" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="e.g. Pengaduan Masyarakat">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea wire:model="description" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Deskripsi singkat layanan..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">URL *</label>
                    <input type="url" wire:model="url" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://...">
                    @error('url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Icon (nama)</label>
                        <input type="text" wire:model="icon" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="link, phone, mail...">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Urutan</label>
                        <input type="number" wire:model="order" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="is_active" id="cb-active" class="w-4 h-4 rounded border-slate-300 text-violet-500 focus:ring-violet-400">
                    <label for="cb-active" class="text-sm font-semibold text-slate-700">Tampilkan di web publik</label>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-violet-600 rounded-xl">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <h3 class="font-extrabold text-slate-900 mb-1">Hapus Layanan?</h3>
            <p class="text-sm text-slate-500 mb-5">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl">Batal</button>
                <button wire:click="delete" class="flex-1 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>