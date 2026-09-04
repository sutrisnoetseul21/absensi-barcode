<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900">Data Alumni</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola data alumni sekolah</p>
            </div>
            <button wire:click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Alumni
            </button>
        </div>
        <div class="flex flex-wrap gap-3 mt-4">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama / NISN..." class="flex-1 min-w-[200px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm focus:ring-2 focus:ring-violet-400 outline-none">
            <select wire:model.live="filterTahun" class="px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm focus:ring-2 focus:ring-violet-400 outline-none cursor-pointer">
                <option value="">Semua Angkatan</option>
                @foreach($tahunList as $tahun)
                    <option value="{{ $tahun }}">Angkatan {{ $tahun }}</option>
                @endforeach
            </select>
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
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama</th>
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">NISN</th>
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Angkatan</th>
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Melanjutkan</th>
                        <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Sekolah Lanjutan</th>
                        <th class="text-right px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alumnis as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                @if($item->foto)
                                    <img src="{{ asset('storage/' . $item->foto) }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        {{ substr($item->nama, 0, 1) }}
                                    </div>
                                @endif
                                <span class="font-semibold text-slate-800">{{ $item->nama }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $item->nisn ?? '-' }}</td>
                        <td class="px-5 py-3.5"><span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold">{{ $item->tahun_lulus }}</span></td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $item->melanjutkan ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $item->melanjutkan ? 'Ya' : 'Tidak' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $item->nama_sekolah ?? '-' }}</td>
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
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 text-sm">Belum ada data alumni</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alumnis->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">{{ $alumnis->links() }}</div>
        @endif
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-start justify-center pt-8 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl mb-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="font-extrabold text-slate-900 text-lg">{{ $editingId ? 'Edit Alumni' : 'Tambah Alumni' }}</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                        <input type="text" wire:model="nama" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="Nama alumni...">
                        @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">NISN</label>
                        <input type="text" wire:model="nisn" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="NISN...">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Jenis Kelamin *</label>
                        <select wire:model="jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none cursor-pointer">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Tahun Lulus *</label>
                        <input type="number" wire:model="tahun_lulus" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="{{ date('Y') }}">
                        @error('tahun_lulus') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">No. HP</label>
                        <input type="text" wire:model="no_hp" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="08xx...">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model.live="melanjutkan" id="cb-melanjutkan" class="w-4 h-4 rounded border-slate-300 text-violet-500 focus:ring-violet-400">
                    <label for="cb-melanjutkan" class="text-sm font-semibold text-slate-700">Melanjutkan ke Jenjang Lebih Tinggi</label>
                </div>
                @if($melanjutkan)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Jenjang</label>
                        <select wire:model="jenjang_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none cursor-pointer">
                            <option value="">-- Pilih Jenjang --</option>
                            @foreach($jenjangs as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jenjang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Sekolah</label>
                        <input type="text" wire:model="nama_sekolah" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="Nama sekolah lanjutan...">
                    </div>
                </div>
                @endif
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Foto</label>
                    @if($existingFoto)
                        <img src="{{ asset('storage/' . $existingFoto) }}" class="h-16 w-16 rounded-full object-cover mb-2 border-2 border-slate-200">
                    @endif
                    <input type="file" wire:model="foto" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700">
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
            <h3 class="font-extrabold text-slate-900 mb-1">Hapus Alumni?</h3>
            <p class="text-sm text-slate-500 mb-5">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl">Batal</button>
                <button wire:click="delete" class="flex-1 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>