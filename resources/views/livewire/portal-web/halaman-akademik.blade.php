<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $isForm ? ($editingId ? 'Edit Halaman Akademik' : 'Buat Halaman Akademik') : 'Halaman Akademik Sekolah' }}</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $isForm ? 'Lengkapi detail dokumen dan informasi akademik pada form di bawah.' : 'Kelola dokumen kurikulum, kalender pendidikan, jadwal, dan panduan akademik publik.' }}</p>
        </div>
        @if(!$isForm)
        <button wire:click="openCreate" class="inline-flex items-center justify-center rounded-xl bg-brand-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-secondary transition-colors gap-2">
            <i class="fas fa-plus"></i> Tambah Halaman Akademik
        </button>
        @else
        <button wire:click="$set('isForm', false)" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-200 transition-colors gap-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </button>
        @endif
    </div>

    @if(!$isForm)

    <!-- Toolbar & Pencarian -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-primary focus:border-brand-primary sm:text-sm transition-colors" placeholder="Cari judul, kategori, atau deskripsi...">
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('beranda.akademik.index') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-brand-primary bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                <i class="fas fa-external-link-alt"></i> Pratinjau Portal Publik
            </a>
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
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">Urutan</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Judul & Halaman</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Berkas PDF</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Aksi</th>
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
                                <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                    <span>/akademik/{{ $item->slug }}</span>
                                    <a href="{{ route('beranda.akademik', $item->slug) }}" target="_blank" class="text-brand-primary hover:underline inline-flex items-center gap-1" title="Lihat Halaman">
                                        <i class="fas fa-external-link-alt text-[10px]"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($item->kategori)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $item->kategori }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($item->file_pdf)
                                    <a href="{{ asset('storage/' . $item->file_pdf) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-lg border border-rose-200 transition-colors" title="Lihat PDF">
                                        <i class="fas fa-file-pdf"></i> Ada PDF
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tidak ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @if($item->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                                    <i class="fas fa-graduation-cap text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900">Belum ada Halaman Akademik</h3>
                                <p class="text-sm text-slate-500 mt-1">Tambahkan dokumen kurikulum atau kalender pendidikan pertama Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @else
    <!-- Form Create / Edit Full Page -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <form wire:submit="save">
            <div class="px-6 py-6 border-b border-slate-100">
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Judul Halaman Akademik <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model.live.debounce.300ms="judul" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Misal: Kurikulum Merdeka 2026/2027">
                            @error('judul') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Slug (URL) <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="slug" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm bg-slate-50 focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="kurikulum-merdeka-2026-2027">
                            <span class="text-[11px] text-slate-400 mt-1 block">Alamat halaman: {{ url('/akademik') }}/{{ $slug ?: '...' }}</span>
                            @error('slug') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori Dokumen</label>
                            <input type="text" list="kategoriList" wire:model="kategori" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Pilih atau ketik kategori (misal: Kurikulum, Kalender Pendidikan, Jadwal)">
                            <datalist id="kategoriList">
                                <option value="Kurikulum">
                                <option value="Kalender Pendidikan">
                                <option value="Jadwal Pelajaran">
                                <option value="Dokumen & Regulasi">
                                <option value="Panduan Akademik">
                            </datalist>
                            @error('kategori') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">File Dokumen / PDF (Opsional)</label>
                            <input type="file" wire:model="file_pdf" accept="application/pdf" class="block w-full rounded-xl border-slate-300 py-2.5 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm bg-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-primary hover:file:bg-brand-100">
                            @error('file_pdf') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="file_pdf" class="text-sm text-brand-primary mt-1"><i class="fas fa-spinner fa-spin"></i> Mengunggah berkas PDF...</div>
                            @if($editingId && is_string($file_pdf))
                                <div class="mt-2 text-xs text-slate-500">Berkas saat ini: <a href="{{ Storage::url($file_pdf) }}" target="_blank" class="text-brand-primary font-bold hover:underline inline-flex items-center gap-1"><i class="fas fa-file-pdf"></i> Lihat Berkas PDF</a></div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi Singkat</label>
                        <textarea wire:model="deskripsi_singkat" rows="3" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Tuliskan intisari atau ringkasan dokumen akademik ini..."></textarea>
                        @error('deskripsi_singkat') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Urutan Tampil <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="urutan" min="0" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm">
                            <span class="text-[11px] text-slate-400 mt-1 block">Angka lebih kecil tampil lebih awal pada menu dan daftar.</span>
                            @error('urutan') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status Publikasi</label>
                            <select wire:model="is_active" class="block w-full rounded-xl border-slate-300 py-3 px-4 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm">
                                <option value="1">Aktif (Tampilkan di Web)</option>
                                <option value="0">Nonaktif (Sembunyikan)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Konten Lengkap Halaman</label>
                        <div wire:ignore wire:key="portal-akademik-editor-wrapper-{{ $editingId ?? 'create' }}" x-data="portalWebTinyMCE(@entangle('konten'), 'portal-akademik-editor-{{ $editingId ?? 'create' }}', 450)" class="rounded-xl overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-violet-400 focus-within:border-violet-400 transition-all bg-white shadow-inner">
                            <textarea x-ref="editor" id="portal-akademik-editor-{{ $editingId ?? 'create' }}" class="w-full"></textarea>
                        </div>
                        @error('konten') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-5 flex items-center gap-3 justify-end border-t border-slate-100">
                <button type="button" wire:click="$set('isForm', false)" class="inline-flex justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex justify-center rounded-xl bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-secondary transition-colors">
                    Simpan Halaman Akademik
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
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Hapus Halaman Akademik</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500">Apakah Anda yakin ingin menghapus halaman akademik ini? Berkas PDF dan konten yang terkait akan dihapus secara permanen.</p>
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
