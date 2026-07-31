<div class="p-6 lg:p-8 space-y-6 max-w-7xl mx-auto">

    <!-- Flash Message -->
    @if(session('flash_success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 text-sm font-bold shadow-xs">
            <div class="p-1.5 rounded-xl bg-emerald-100 text-emerald-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            {{ session('flash_success') }}
        </div>
    @endif

    <!-- Header -->
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Data Peminjaman</h1>
        <p class="text-slate-500 text-sm mt-1">Pantau status pinjaman aktif, keterlambatan, dan riwayat pengembalian buku.</p>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="flex border-b border-slate-200">
            <!-- Tab: Dipinjam -->
            <button wire:click="setTab('dipinjam')"
                    class="flex-1 sm:flex-none flex items-center justify-center sm:justify-start gap-2.5 px-5 py-4 text-sm font-bold transition-all border-b-2 focus:outline-none
                           {{ $activeTab === 'dipinjam' ? 'border-indigo-600 text-indigo-700 bg-indigo-50/60' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Dipinjam (Aktif)</span>
                @if($countDipinjam > 0)
                    <span class="px-2 py-0.5 text-[11px] font-black rounded-full {{ $activeTab === 'dipinjam' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $countDipinjam }}
                    </span>
                @endif
            </button>

            <!-- Tab: Terlambat -->
            <button wire:click="setTab('terlambat')"
                    class="flex-1 sm:flex-none flex items-center justify-center sm:justify-start gap-2.5 px-5 py-4 text-sm font-bold transition-all border-b-2 focus:outline-none
                           {{ $activeTab === 'terlambat' ? 'border-rose-600 text-rose-700 bg-rose-50/60' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Terlambat</span>
                @if($countTerlambat > 0)
                    <span class="px-2 py-0.5 text-[11px] font-black rounded-full {{ $activeTab === 'terlambat' ? 'bg-rose-600 text-white' : 'bg-rose-100 text-rose-700' }}">
                        {{ $countTerlambat }}
                    </span>
                @endif
            </button>

            <!-- Tab: Riwayat -->
            <button wire:click="setTab('dikembalikan')"
                    class="flex-1 sm:flex-none flex items-center justify-center sm:justify-start gap-2.5 px-5 py-4 text-sm font-bold transition-all border-b-2 focus:outline-none
                           {{ $activeTab === 'dikembalikan' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/60' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Riwayat Pengembalian</span>
                <span class="px-2 py-0.5 text-[11px] font-black rounded-full {{ $activeTab === 'dikembalikan' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                    {{ $countDikembalikan }}
                </span>
            </button>
        </div>

        <!-- Search Bar & Tab Content -->
        <div class="p-5 space-y-5">
            <!-- Search -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-sm">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Cari nama peminjam, judul, kode..."
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all">
                </div>
                <div wire:loading class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                    <svg class="w-4 h-4 animate-spin text-brand-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Memuat...
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-extrabold uppercase tracking-wide border-b border-slate-200">
                        <tr>
                            <th class="p-3 pl-4">Peminjam</th>
                            <th class="p-3">Buku &amp; Kode Eksemplar</th>
                            <th class="p-3">Tgl Pinjam</th>
                            <th class="p-3">
                                {{ $activeTab === 'dikembalikan' ? 'Tgl Kembali' : 'Jatuh Tempo' }}
                            </th>
                            <th class="p-3">Status</th>
                            <th class="p-3 pr-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($peminjamans as $p)
                            @php
                                $isOverdue = $p->status === 'dipinjam' && $p->tanggal_jatuh_tempo < $today;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3 pl-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($p->peminjam?->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $p->peminjam?->name ?? 'Anggota' }}</p>
                                            <span class="text-[10px] text-slate-400 capitalize">{{ $p->peminjam_type }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <h4 class="font-bold text-slate-800">{{ $p->eksemplarBuku?->buku?->judul ?? 'Buku' }}</h4>
                                    <span class="font-mono text-[10px] text-slate-400">{{ $p->eksemplarBuku?->kode_eksemplar }}</span>
                                </td>
                                <td class="p-3 font-medium text-slate-600">
                                    {{ \Carbon\Carbon::parse($p->tanggal_pinjam)->format('d M Y') }}
                                </td>
                                <td class="p-3 font-medium {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                    @if($activeTab === 'dikembalikan')
                                        {{ $p->tanggal_kembali ? \Carbon\Carbon::parse($p->tanggal_kembali)->format('d M Y') : '-' }}
                                    @else
                                        {{ \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d M Y') }}
                                        @if($isOverdue)
                                            <span class="block text-[10px] font-bold text-rose-500">
                                                ({{ \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->diffForHumans() }})
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($isOverdue)
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase bg-rose-100 text-rose-700 border border-rose-200">Terlambat</span>
                                    @elseif($p->status === 'dipinjam')
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase bg-amber-100 text-amber-700 border border-amber-200">Dipinjam</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">Dikembalikan</span>
                                    @endif
                                </td>
                                <td class="p-3 pr-4 text-right">
                                    @if($p->status === 'dipinjam')
                                        <button type="button" wire:click="kembalikanBuku('{{ $p->id }}')"
                                                wire:confirm="Konfirmasi pengembalian buku '{{ $p->eksemplarBuku?->buku?->judul }}'?"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] transition-all shadow-sm inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Kembalikan
                                        </button>
                                    @else
                                        <span class="text-[11px] text-slate-400 font-medium">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <p class="text-sm font-bold text-slate-500">Tidak ada data</p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        @if($search)
                                            Tidak ditemukan hasil untuk "<strong>{{ $search }}</strong>"
                                        @else
                                            Belum ada data untuk tab ini.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($peminjamans->hasPages())
                <div class="pt-2 border-t border-slate-100">
                    {{ $peminjamans->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
