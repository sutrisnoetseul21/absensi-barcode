<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Inventaris Buku Induk</h1>
            <p class="text-slate-500 text-sm mt-1">Audit trail batch penerimaan &amp; pengadaan buku sekolah.</p>
        </div>

        <button @click="$wire.openUnduhModal()" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Unduh Inventaris
        </button>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center gap-4 justify-between">
        <div class="relative w-full md:w-96">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari No. Inventaris, judul, ISBN..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <select wire:model.live="filterStatus" class="w-full md:w-48 py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="dibatalkan">Dibatalkan</option>
            </select>
        </div>
    </div>

    <!-- Inventaris Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="p-4 pl-6 text-center w-12">No</th>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">No Inventaris</th>
                        <th class="p-4">Judul</th>
                        <th class="p-4">Pengarang</th>
                        <th class="p-4">Penerbit</th>
                        <th class="p-4 text-center">Tahun Terbit</th>
                        <th class="p-4">Asal</th>
                        <th class="p-4">No Klasifikasi</th>
                        <th class="p-4 text-right">Harga</th>
                        <th class="p-4 pr-6 text-center">Jumlah Eksemplar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($inventaris as $inv)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4 pl-6 text-center font-medium">
                                {{ ($inventaris->currentPage() - 1) * $inventaris->perPage() + $loop->iteration }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($inv->tanggal_masuk)->format('d/m/Y') }}
                            </td>
                            <td class="p-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $inv->no_inventaris }}
                            </td>
                            <td class="p-4 font-bold text-slate-800 text-[11px] min-w-[200px]">
                                {{ $inv->buku?->judul ?? '-' }}
                            </td>
                            <td class="p-4 text-[11px] text-slate-600">
                                {{ $inv->buku?->penulis ?? '-' }}
                            </td>
                            <td class="p-4 text-[11px] text-slate-600">
                                {{ $inv->buku?->penerbit ?? '-' }}
                            </td>
                            <td class="p-4 text-center text-slate-600">
                                {{ $inv->buku?->tahun_terbit ?? '-' }}
                            </td>
                            <td class="p-4 capitalize text-[11px] text-slate-600">
                                {{ str_replace('_', ' ', $inv->asal) }}
                            </td>
                            <td class="p-4 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                {{ $inv->buku?->klasifikasiDdc?->kode_ddc ?? '-' }}
                            </td>
                            <td class="p-4 text-slate-600 whitespace-nowrap text-right text-[11px]">
                                {{ $inv->harga > 0 ? 'Rp ' . number_format($inv->harga, 0, ',', '.') : '-' }}
                            </td>
                            <td class="p-4 pr-6 text-center font-bold text-slate-800">
                                {{ number_format($inv->jumlah_eksemplar) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <p class="text-xs font-medium">Belum ada data inventaris pengadaan buku.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inventaris->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $inventaris->links('vendor.livewire.custom-pagination') }}
            </div>
        @endif
    </div>

    {{-- ===== MODAL UNDUH INVENTARIS ===== --}}
    @if($showUnduhModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 lg:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Unduh Inventaris Buku</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih filter status dan format dokumen.</p>
                    </div>
                    <button type="button" wire:click="$set('showUnduhModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Filter Status --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Filter Status</label>
                    <p class="text-[11px] text-slate-400 mb-3">Kosongkan untuk mengunduh semua status.</p>

                    <div class="flex gap-2 mb-3">
                        <button type="button" wire:click="$set('filterStatusUnduh', ['aktif', 'dibatalkan'])" class="px-3 py-1.5 text-[11px] font-bold bg-brand-primary/10 text-brand-primary rounded-lg hover:bg-brand-primary/20 transition">
                            Pilih Semua
                        </button>
                        <button type="button" wire:click="$set('filterStatusUnduh', [])" class="px-3 py-1.5 text-[11px] font-bold bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition">
                            Hapus Pilihan
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition
                            {{ in_array('aktif', $filterStatusUnduh) ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="checkbox"
                                wire:model.live="filterStatusUnduh"
                                value="aktif"
                                class="rounded accent-emerald-600 w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">✅ Aktif</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition
                            {{ in_array('dibatalkan', $filterStatusUnduh) ? 'border-rose-400 bg-rose-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="checkbox"
                                wire:model.live="filterStatusUnduh"
                                value="dibatalkan"
                                class="rounded accent-rose-600 w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">❌ Dibatalkan</span>
                        </label>
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
                    <button type="button" wire:click="downloadInventaris" class="px-5 py-2.5 {{ $formatUnduh === 'excel' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' }} text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Unduh {{ strtoupper($formatUnduh) }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
