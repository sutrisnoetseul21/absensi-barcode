<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Inventaris Buku Induk</h1>
            <p class="text-slate-500 text-sm mt-1">Audit trail batch penerimaan & pengadaan buku sekolah.</p>
        </div>
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
        <div class="overflow-x-auto">
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
                {{ $inventaris->links() }}
            </div>
        @endif
    </div>
</div>
