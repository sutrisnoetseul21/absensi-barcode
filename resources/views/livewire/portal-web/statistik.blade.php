<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Statistik & Info Web</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola angka statistik capaian sekolah yang tampil di beranda web publik.</p>
        </div>
        <button wire:click="openCreate" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-700 transition-colors gap-2">
            <i class="fas fa-plus"></i> Tambah Statistik
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
            <input type="text" wire:model.live.debounce.300ms="search" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-violet-500 sm:text-sm transition-colors" placeholder="Cari keterangan atau nilai...">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">Urutan</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Ikon</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai / Angka</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan Label</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($statistics as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-bold text-center">
                                {{ $item->order }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-base shadow-sm">
                                    <i class="{{ $item->icon }}"></i>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-base font-extrabold text-slate-900">{{ $item->value }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-slate-800">{{ $item->label }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $item->icon }}</div>
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
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                                    <i class="fas fa-chart-bar text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-medium text-slate-900">Belum ada data statistik</h3>
                                <p class="text-sm text-slate-500 mt-1">Tambahkan statistik seperti Akreditasi, Jumlah Lulusan, dll.</p>
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
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form wire:submit="save">
                        <div class="bg-white px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-900">{{ $editingId ? 'Edit Statistik' : 'Tambah Statistik Web' }}</h3>
                            <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Class Ikon FontAwesome <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 border border-violet-100">
                                        <i class="{{ $icon ?: 'fas fa-chart-bar' }}"></i>
                                    </div>
                                    <input type="text" wire:model.live="icon" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-violet-500 focus:ring-violet-500 shadow-sm" placeholder="fas fa-graduation-cap">
                                </div>
                                <div class="flex items-center gap-2 mt-2 flex-wrap text-xs text-slate-400">
                                    <span>Pilihan cepat:</span>
                                    <button type="button" wire:click="$set('icon', 'fas fa-graduation-cap')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-violet-50 hover:text-violet-600">Wisuda</button>
                                    <button type="button" wire:click="$set('icon', 'fas fa-award')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-violet-50 hover:text-violet-600">Akreditasi</button>
                                    <button type="button" wire:click="$set('icon', 'fas fa-trophy')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-violet-50 hover:text-violet-600">Prestasi</button>
                                    <button type="button" wire:click="$set('icon', 'fas fa-users')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-violet-50 hover:text-violet-600">Siswa</button>
                                    <button type="button" wire:click="$set('icon', 'fas fa-school')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-violet-50 hover:text-violet-600">Sekolah</button>
                                </div>
                                @error('icon') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nilai / Angka <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="value" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-violet-500 focus:ring-violet-500 shadow-sm" placeholder="Misal: A+, 100%, 50+">
                                    @error('value') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Urutan <span class="text-rose-500">*</span></label>
                                    <input type="number" wire:model="order" min="0" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-violet-500 focus:ring-violet-500 shadow-sm">
                                    @error('order') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Keterangan Label <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="label" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-violet-500 focus:ring-violet-500 shadow-sm" placeholder="Misal: Akreditasi Nasional, Tingkat Kelulusan">
                                @error('label') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
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
                                <h3 class="text-base font-bold text-slate-900">Hapus Statistik</h3>
                                <p class="text-sm text-slate-500 mt-1">Apakah Anda yakin ingin menghapus data statistik ini? Tindakan ini tidak dapat dibatalkan.</p>
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
