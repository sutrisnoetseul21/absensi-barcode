<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Data Pengaduan & Aspirasi</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola laporan, pengaduan, dan aspirasi masyarakat.</p>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="relative w-full sm:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-primary focus:border-brand-primary sm:text-sm transition-colors" placeholder="Cari nama, email, atau isi laporan...">
        </div>
        
        <div class="w-full sm:w-auto">
            <select wire:model.live="filterStatus" class="block w-full pl-3 pr-10 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-primary focus:border-brand-primary sm:text-sm transition-colors">
                <option value="">Semua Status</option>
                <option value="menunggu">Menunggu</option>
                <option value="diproses">Diproses</option>
                <option value="selesai">Selesai</option>
            </select>
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
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pelapor</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($pengaduans as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $item->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-900">{{ $item->nama }}</div>
                                <div class="text-xs text-slate-500">{{ $item->email }}</div>
                                @if($item->no_hp)
                                <div class="text-xs text-slate-500">{{ $item->no_hp }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ $item->kategori->nama_kategori ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->status === 'menunggu')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-clock mr-1.5"></i> Menunggu
                                    </span>
                                @elseif($item->status === 'diproses')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-spinner fa-spin mr-1.5"></i> Diproses
                                    </span>
                                @elseif($item->status === 'selesai')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fas fa-check mr-1.5"></i> Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="viewDetails({{ $item->id }})" class="p-2 text-slate-400 hover:text-brand-primary hover:bg-brand-50 rounded-lg transition-colors" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
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
                                    <i class="fas fa-inbox text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-medium text-slate-900">Tidak ada pengaduan</h3>
                                <p class="text-sm text-slate-500 mt-1">Belum ada laporan atau aspirasi masuk.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pengaduans->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $pengaduans->links() }}
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div x-data="{ show: @entangle('showModal') }" x-show="show" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="show" x-transition.opacity x-transition:enter.duration.300ms x-transition:leave.duration.200ms
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" @click.away="show = false">
                    
                    @if($selectedPengaduan)
                    <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-slate-100">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg font-bold text-slate-800" id="modal-title">Detail Pengaduan</h3>
                            <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl">
                                <div>
                                    <span class="block text-xs font-semibold tracking-wider uppercase text-slate-500 mb-1">Pelapor</span>
                                    <span class="block font-bold text-slate-800">{{ $selectedPengaduan->nama }}</span>
                                    <span class="block text-sm text-slate-600">{{ $selectedPengaduan->email }}</span>
                                    @if($selectedPengaduan->no_hp)
                                    <span class="block text-sm text-slate-600">{{ $selectedPengaduan->no_hp }}</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="block text-xs font-semibold tracking-wider uppercase text-slate-500 mb-1">Informasi</span>
                                    <span class="block text-sm text-slate-600 mb-1">
                                        <i class="far fa-calendar-alt w-4"></i> {{ $selectedPengaduan->created_at->format('d M Y, H:i') }}
                                    </span>
                                    <span class="block text-sm text-slate-600">
                                        <i class="fas fa-tag w-4"></i> {{ $selectedPengaduan->kategori->nama_kategori ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <span class="block text-xs font-semibold tracking-wider uppercase text-slate-500 mb-2">Isi Laporan</span>
                                <div class="bg-white border border-slate-200 rounded-xl p-4 text-slate-700 text-sm whitespace-pre-wrap leading-relaxed">{{ $selectedPengaduan->isi_pengaduan }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold tracking-wider uppercase text-slate-500 mb-2">Update Status Penanganan</label>
                                <select wire:model="updateStatus" class="mt-1 block w-full rounded-xl border-slate-300 py-2 pl-3 pr-10 text-base focus:border-brand-primary focus:outline-none focus:ring-brand-primary sm:text-sm shadow-sm transition-shadow">
                                    <option value="menunggu">Menunggu</option>
                                    <option value="diproses">Diproses</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6 gap-2 border-t border-slate-100">
                        <button type="button" wire:click="saveStatus" class="inline-flex w-full justify-center rounded-xl bg-brand-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-secondary sm:w-auto transition-colors">
                            Simpan Perubahan
                        </button>
                        <button type="button" @click="show = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                            Tutup
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

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
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Hapus Pengaduan</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500">Apakah Anda yakin ingin menghapus data pengaduan ini? Data yang sudah dihapus tidak dapat dikembalikan.</p>
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
