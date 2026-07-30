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
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari No. Inventaris, judul, ISBN..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600">
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <select wire:model.live="filterStatus" class="w-full md:w-48 py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600">
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
                        <th class="p-4 pl-6">No. Inventaris</th>
                        <th class="p-4">Buku & Judul</th>
                        <th class="p-4">Tanggal Masuk</th>
                        <th class="p-4">Asal Pengadaan</th>
                        <th class="p-4">Jumlah Fisik</th>
                        <th class="p-4 pr-6 text-right">Status Batch</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($inventaris as $inv)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4 pl-6 font-mono font-bold text-slate-900">
                                {{ $inv->no_inventaris }}
                            </td>
                            <td class="p-4">
                                <h4 class="font-bold text-slate-800 text-xs">{{ $inv->buku?->judul ?? 'Buku' }}</h4>
                                <span class="text-[10px] text-slate-400">{{ $inv->buku?->kategoriBuku?->nama_kategori }} &bull; ISBN: {{ $inv->buku?->isbn ?? '-' }}</span>
                            </td>
                            <td class="p-4 font-medium text-slate-600">
                                {{ \Carbon\Carbon::parse($inv->tanggal_masuk)->format('d M Y') }}
                            </td>
                            <td class="p-4 font-medium text-slate-600 capitalize">
                                {{ str_replace('_', ' ', $inv->asal) }}
                            </td>
                            <td class="p-4 font-bold text-slate-800">
                                {{ number_format($inv->jumlah_eksemplar) }} Fisik
                            </td>
                            <td class="p-4 pr-6 text-right">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider {{ $inv->status === 'aktif' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-rose-100 text-rose-700 border border-rose-200' }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
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
