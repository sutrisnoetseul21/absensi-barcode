<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pilihan Jenjang Alumni</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar jenjang lanjutan bagi alumni (contoh: SMA, SMK, Perguruan Tinggi, Bekerja).</p>
        </div>
        <button wire:click="openCreate" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-700 transition-colors gap-2">
            <i class="fas fa-plus"></i> Tambah Jenjang
        </button>
    </div>

    <!-- Flash Message -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        </div>
    @endif

    <!-- Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 mb-6 flex items-center justify-between">
        <div class="relative w-full sm:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-violet-500 sm:text-sm transition-colors" placeholder="Cari nama jenjang...">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Jenjang Lanjutan</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Jumlah Alumni</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($jenjangs as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-900">{{ $item->nama_jenjang }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-50 text-violet-700">
                                    {{ $item->alumnis_count }} Alumni
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEdit({{ $item->id }})" class="p-2 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                                    <i class="fas fa-tags text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-medium text-slate-900">Belum ada pilihan jenjang</h3>
                                <p class="text-sm text-slate-500 mt-1">Tambahkan jenjang lanjutan seperti SMA, SMK, Kuliah, dll.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form (Create/Edit) -->
    @if($showModal)
    <div class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <form wire:submit="save">
                        <div class="bg-white px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-900">{{ $editingId ? 'Edit Jenjang' : 'Tambah Jenjang Baru' }}</h3>
                            <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Jenjang Lanjutan <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="nama_jenjang" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-violet-500 focus:ring-violet-500 shadow-sm" placeholder="Contoh: SMA, SMK, Perguruan Tinggi">
                                @error('nama_jenjang') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-violet-600 rounded-xl hover:bg-violet-700 transition-colors shadow-sm">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Hapus Jenjang</h3>
                                <p class="text-sm text-slate-500 mt-1">Apakah Anda yakin ingin menghapus pilihan jenjang ini?</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="delete" class="px-5 py-2 text-sm font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition-colors shadow-sm">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
