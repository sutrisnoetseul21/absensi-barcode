<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-rose-700 tracking-tight">Riwayat Hapus Buku</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar buku yang telah dihapus dan dapat dipulihkan kembali.</p>
        </div>
    </div>

    <!-- Alert Success Flash -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center gap-4 justify-between">
        <div class="relative w-full md:w-96">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul, penulis, ISBN..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <select wire:model.live="filterKategori" class="w-full md:w-48 py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                <option value="">Semua Koleksi</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="p-4 pl-6">Judul Buku</th>
                        <th class="p-4">Koleksi</th>
                        <th class="p-4">Lokasi Rak</th>
                        <th class="p-4 min-w-[120px] whitespace-nowrap">Eksemplar</th>
                        <th class="p-4 text-right pr-6">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($bukus as $buku)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-4">
                                    @if($buku->sampul_buku)
                                        <img src="{{ asset('storage/' . $buku->sampul_buku) }}" alt="Sampul" class="w-12 h-16 shrink-0 object-cover rounded-md shadow-sm border border-slate-200 opacity-70 grayscale">
                                    @else
                                        <div class="w-12 h-16 shrink-0 bg-slate-100 rounded-md flex items-center justify-center border border-slate-200 opacity-70">
                                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm">{{ $buku->judul }}</h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $buku->penulis ?? 'Tanpa Penulis' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-brand-primary/10 text-brand-primary font-bold rounded-lg border border-brand-primary/20 inline-block text-[11px]">
                                    {{ $buku->kategoriBuku?->nama_kategori ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="p-4 font-medium text-slate-600">
                                {{ $buku->lokasi_rak ?? '-' }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                @php
                                    $totalFisik = $buku->eksemplarBukus->count();
                                    $dipinjam = $buku->eksemplarBukus->where('status', 'dipinjam')->count();
                                @endphp
                                <span class="font-bold text-slate-800">{{ $totalFisik }} Eksemplar</span>
                                <div class="flex items-center mt-1 text-[10px]">
                                    <span class="text-amber-600 font-bold">{{ $dipinjam }} Pinjam</span>
                                </div>
                            </td>
                            <td class="p-4 pr-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="openDetailModal('{{ $buku->id }}')" class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200/80 transition-all shadow-2xs cursor-pointer" title="Detail Eksemplar Buku">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button type="button" wire:confirm="Apakah Anda yakin ingin memulihkan buku '{{ $buku->judul }}'?" wire:click="restoreBuku('{{ $buku->id }}')" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-xl border border-emerald-200/80 transition-all shadow-2xs cursor-pointer" title="Pulihkan Buku">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                    <button type="button" wire:confirm="PERINGATAN: Buku '{{ $buku->judul }}' akan dihapus secara permanen dari database. Lanjutkan?" wire:click="forceDeleteBuku('{{ $buku->id }}')" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl border border-rose-200/80 transition-all shadow-2xs cursor-pointer" title="Hapus Permanen">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <p class="text-xs font-medium">Tong sampah kosong. Tidak ada riwayat buku yang dihapus.</p>
                            </td>
                        </tr>
                    @endempty
                </tbody>
            </table>
        </div>
        
        @if($bukus->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $bukus->links('vendor.livewire.custom-pagination') }}
            </div>
        @endif
    </div>

    {{-- ===== MODAL DETAIL EKSEMPLAR BUKU ===== --}}
    @if($showDetailModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full p-6 lg:p-8 space-y-6 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Detail Buku (Terhapus)</h3>
                        @if($selectedBukuDetail)
                            <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
                                <span class="font-bold text-slate-700">{{ $selectedBukuDetail->judul }}</span> &bull; 
                                {{ $selectedBukuDetail->penulis ?? 'Tanpa Penulis' }} &bull; 
                                {{ $selectedBukuDetail->penerbit ?? 'Tanpa Penerbit' }} &bull; 
                                Rak: {{ $selectedBukuDetail->lokasi_rak ?? '-' }} &bull; 
                                ISBN: {{ $selectedBukuDetail->isbn ?? '-' }}
                            </p>
                        @endif
                    </div>
                    <button type="button" wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="overflow-y-auto flex-1 rounded-2xl border border-slate-200/80">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80 sticky top-0">
                            <tr>
                                <th class="p-3 pl-4">Kode eksemplar</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Kondisi fisik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($eksemplarDetailList as $eks)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-3 pl-4 font-mono font-bold text-slate-900">{{ $eks->kode_eksemplar }}</td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border inline-block capitalize bg-slate-100 text-slate-600 border-slate-200">
                                            {{ $eks->status }}
                                        </span>
                                    </td>
                                    <td class="p-3 capitalize text-slate-600">
                                        {{ str_replace('_', ' ', $eks->kondisi_fisik) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-8 text-center text-slate-400 font-medium">Tidak ada data fisik eksemplar terdaftar.</td>
                                </tr>
                            @endempty
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showDetailModal', false)" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-slate-800/20">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
