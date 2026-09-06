<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $isForm ? ($editingId ? 'Edit Halaman Layanan' : 'Buat Halaman Layanan') : 'Halaman Layanan Publik' }}</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $isForm ? 'Silakan lengkapi form di bawah ini.' : 'Kelola halaman panduan dan alur layanan untuk publik.' }}</p>
        </div>
        @if(!$isForm)
        <button wire:click="openCreate" class="inline-flex items-center justify-center rounded-xl bg-brand-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-secondary transition-colors gap-2">
            <i class="fas fa-plus"></i> Buat Halaman
        </button>
        @else
        <button wire:click="$set('isForm', false)" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-200 transition-colors gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
        @endif
    </div>

    @if(!$isForm)

    <!-- Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 mb-6 flex items-center justify-between">
        <div class="relative w-full sm:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-primary focus:border-brand-primary sm:text-sm transition-colors" placeholder="Cari halaman layanan...">
        </div>
    </div>

    <!-- Flash Message -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Urutan</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Judul Halaman</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($halamans as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 text-center font-bold">
                                {{ $item->urutan }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-900">{{ $item->judul }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">/layanan-publik/{{ $item->slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                @if($item->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEdit({{ $item->id }})" class="p-2 text-slate-400 hover:text-brand-primary hover:bg-brand-50 rounded-lg transition-colors" title="Edit">
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
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                                    <i class="fas fa-file-alt text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-medium text-slate-900">Belum ada halaman</h3>
                                <p class="text-sm text-slate-500 mt-1">Buat halaman informasi layanan Anda sekarang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @else
    <!-- Form Full Page -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <form wire:submit="save">
            <div class="px-6 py-6 border-b border-slate-100">
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Judul Halaman / Layanan <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model.live.debounce.300ms="judul" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Misal: Pelayanan SPMB">
                            @error('judul') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Slug (URL) <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="slug" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm bg-slate-50 focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="pelayanan-spmb">
                            @error('slug') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori (Badge)</label>
                            <input type="text" wire:model="kategori" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Misal: KESISWAAN">
                            @error('kategori') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">File SOP (PDF)</label>
                            <input type="file" wire:model="file_pdf" accept="application/pdf" class="block w-full rounded-xl border-slate-300 py-2.5 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm bg-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-primary hover:file:bg-brand-100">
                            @error('file_pdf') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="file_pdf" class="text-sm text-brand-primary mt-1"><i class="fas fa-spinner fa-spin"></i> Mengunggah...</div>
                            @if($editingId && is_string($file_pdf))
                                <div class="mt-2 text-xs text-slate-500">File saat ini: <a href="{{ Storage::url($file_pdf) }}" target="_blank" class="text-brand-primary hover:underline">Lihat PDF</a></div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Biaya Layanan</label>
                            <input type="text" wire:model="biaya" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Misal: Gratis (Rp 0)">
                            @error('biaya') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Layanan</label>
                            <input type="text" wire:model="waktu_layanan" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Misal: 15 - 30 Menit">
                            @error('waktu_layanan') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi Singkat</label>
                        <textarea wire:model="deskripsi_singkat" rows="3" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Tuliskan syarat atau prosedur singkat di sini..."></textarea>
                        @error('deskripsi_singkat') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Urutan Tampil <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="urutan" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm">
                            @error('urutan') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status Publikasi</label>
                            <select wire:model="is_active" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm">
                                <option value="1">Aktif / Tampilkan</option>
                                <option value="0">Nonaktif / Sembunyikan</option>
                            </select>
                        </div>
                    </div>

                    <div wire:ignore x-data="portalWebTinyMCE(@entangle('konten'))">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Konten Halaman</label>
                        <textarea x-ref="editor" id="portal-layanan-editor" class="w-full"></textarea>
                    </div>
                    @error('konten') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-5 flex items-center gap-3 justify-end border-t border-slate-100">
                <button type="button" wire:click="$set('isForm', false)" class="inline-flex justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex justify-center rounded-xl bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-secondary transition-colors">
                    Simpan Halaman
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="show" x-transition.opacity x-transition:enter.duration.300ms x-transition:leave.duration.200ms
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg" @click.away="show = false">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-rose-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Hapus Halaman</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500">Apakah Anda yakin ingin menghapus halaman ini? Data tidak dapat dikembalikan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="delete" class="inline-flex w-full justify-center rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 sm:ml-3 sm:w-auto transition-colors">Hapus</button>
                        <button type="button" @click="show = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
