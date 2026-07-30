<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Sirkulasi Peminjaman & Pengembalian</h1>
            <p class="text-slate-500 text-sm mt-1">Transaksi pinjam-kembali buku perpustakaan untuk Siswa & Guru.</p>
        </div>

        <a href="{{ route('perpustakaan.sirkulasi') }}" target="_blank" class="px-5 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-2xl font-bold text-xs shadow-sm transition-all flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            Buka Kiosk Full Screen
        </a>
    </div>

    <!-- Scan Circulation Widget Card (Vibrant Brand Gradient Theme) -->
    <div class="relative bg-gradient-to-r from-brand-primary via-indigo-900 to-brand-secondary border border-brand-primary/40 rounded-3xl p-6 lg:p-8 text-white shadow-2xl overflow-hidden space-y-6">
        <!-- Decorative Glow Blobs -->
        <div class="absolute -top-12 -right-12 w-80 h-80 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-80 h-80 bg-brand-secondary/20 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/20">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center font-bold shadow-lg">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl lg:text-2xl font-extrabold text-white tracking-tight">Terminal Transaksi Sirkulasi</h3>
                    <p class="text-xs text-indigo-100/90 font-medium mt-0.5">Langkah 1: Identifikasi Anggota &rarr; Langkah 2: Scan Barcode Buku</p>
                </div>
            </div>

            @if($peminjamNama)
                <button type="button" wire:click="resetPeminjam" class="px-4 py-2 bg-rose-500/25 hover:bg-rose-500/40 text-white border border-rose-300/40 rounded-xl text-xs font-extrabold backdrop-blur-md transition-all shadow">
                    Reset Peminjam
                </button>
            @endif
        </div>

        <!-- Feedback Alert -->
        @if($feedbackMessage)
            <div class="relative z-10 p-4 rounded-2xl text-xs font-bold border backdrop-blur-md flex items-center justify-between shadow-md {{ $feedbackType === 'success' ? 'bg-emerald-500/20 border-emerald-300/40 text-emerald-100' : 'bg-rose-500/20 border-rose-300/40 text-rose-100' }}">
                <div class="flex items-center gap-2.5">
                    @if($feedbackType === 'success')
                        <div class="w-6 h-6 rounded-full bg-emerald-400 text-slate-950 font-bold flex items-center justify-center">✓</div>
                    @else
                        <div class="w-6 h-6 rounded-full bg-rose-400 text-slate-950 font-bold flex items-center justify-center">!</div>
                    @endif
                    <span class="text-sm">{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" class="text-white/80 hover:text-white font-bold text-lg">&times;</button>
            </div>
        @endif

        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Step 1: Scan Kartu Anggota -->
            <div class="bg-white/15 backdrop-blur-md rounded-2xl p-5 border border-white/25 space-y-3.5 shadow-lg">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-full bg-white text-slate-900 text-xs font-extrabold flex items-center justify-center shadow-md">1</span>
                    <label class="block text-xs font-extrabold text-white uppercase tracking-wider">Scan Kartu Anggota (NISN / NIS / NIP)</label>
                </div>
                
                @if($peminjamNama)
                    <div class="p-4 bg-white/20 border border-white/35 rounded-xl flex items-center gap-3.5 shadow-inner backdrop-blur-md">
                        <div class="w-11 h-11 rounded-xl bg-white text-slate-900 font-extrabold flex items-center justify-center text-sm shadow">
                            {{ strtoupper(substr($peminjamNama, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-extrabold text-white truncate">{{ $peminjamNama }}</h4>
                            <p class="text-xs text-indigo-100 font-medium truncate">{{ $peminjamSub }}</p>
                        </div>
                        <span class="px-2.5 py-1 bg-emerald-400 text-slate-950 text-[10px] font-extrabold rounded-lg uppercase shadow-sm">Terverifikasi</span>
                    </div>
                @else
                    <form wire:submit.prevent="processScanPeminjam" class="flex gap-2">
                        <input wire:model="barcodeAnggota" type="text" placeholder="Scan/ketik NISN, NIS, atau NIP..." class="flex-1 px-4 py-3 bg-white text-slate-900 font-semibold placeholder-slate-400 border-2 border-white/60 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-white/40 focus:border-white focus:bg-white shadow-md transition-all">
                        <button type="submit" class="px-5 py-3 bg-white hover:bg-slate-100 text-slate-900 rounded-xl text-xs font-extrabold shadow-lg transition-transform hover:scale-[1.02]">
                            Cari
                        </button>
                    </form>
                @endif
            </div>

            <!-- Step 2: Scan Barcode Buku -->
            <div class="bg-white/15 backdrop-blur-md rounded-2xl p-5 border border-white/25 space-y-3.5 shadow-lg">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-full {{ $peminjamId ? 'bg-white text-slate-900' : 'bg-white/30 text-white/70' }} text-xs font-extrabold flex items-center justify-center shadow-md">2</span>
                    <label class="block text-xs font-extrabold text-white uppercase tracking-wider">Scan Kode Eksemplar Buku</label>
                </div>
                <form wire:submit.prevent="processScanBuku" class="flex gap-2">
                    <input wire:model="barcodeBuku" type="text" placeholder="Scan/ketik kode eksemplar (misal: UMM00001)..." class="flex-1 px-4 py-3 bg-white text-slate-900 font-semibold placeholder-slate-400 border-2 border-white/60 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-white/40 focus:border-white focus:bg-white shadow-md transition-all {{ !$peminjamId ? 'opacity-60 bg-white/70 cursor-not-allowed' : '' }}" {{ !$peminjamId ? 'disabled' : '' }}>
                    <button type="submit" class="px-5 py-3 bg-white hover:bg-slate-100 text-slate-900 rounded-xl text-xs font-extrabold shadow-lg transition-transform hover:scale-[1.02] {{ !$peminjamId ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$peminjamId ? 'disabled' : '' }}>
                        Proses
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Active Loans Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden space-y-4 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Daftar Transaksi Peminjaman</h3>
                <p class="text-xs text-slate-500">Pantau status pinjaman aktif, terlambat, dan histori pengembalian.</p>
            </div>

            <div class="flex items-center gap-3">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul/kode..." class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                <select wire:model.live="filterStatus" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    <option value="dipinjam">Dipinjam (Aktif)</option>
                    <option value="terlambat">Terlambat Jatuh Tempo</option>
                    <option value="dikembalikan">Dikembalikan</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4">Peminjam</th>
                        <th class="p-3">Buku & Kode Eksemplar</th>
                        <th class="p-3">Tgl Pinjam</th>
                        <th class="p-3">Jatuh Tempo</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 pr-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($peminjamans as $p)
                        @php
                            $isOverdue = $p->status === 'dipinjam' && $p->tanggal_jatuh_tempo < $today;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3 pl-4 font-bold text-slate-800">
                                {{ $p->peminjam?->name ?? 'Anggota' }}
                                <span class="block text-[10px] text-slate-400 font-normal capitalize">{{ $p->peminjam_type }}</span>
                            </td>
                            <td class="p-3">
                                <h4 class="font-bold text-slate-800 text-xs">{{ $p->eksemplarBuku?->buku?->judul ?? 'Buku' }}</h4>
                                <span class="font-mono text-[10px] text-slate-400">Kode: {{ $p->eksemplarBuku?->kode_eksemplar }}</span>
                            </td>
                            <td class="p-3 font-medium text-slate-600">
                                {{ \Carbon\Carbon::parse($p->tanggal_pinjam)->format('d M Y') }}
                            </td>
                            <td class="p-3 font-medium text-slate-600">
                                {{ \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d M Y') }}
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider {{ $isOverdue ? 'bg-rose-100 text-rose-700 border border-rose-200' : ($p->status === 'dipinjam' ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200') }}">
                                    {{ $isOverdue ? 'Terlambat' : $p->status }}
                                </span>
                            </td>
                            <td class="p-3 pr-4 text-right">
                                @if($p->status === 'dipinjam')
                                    <button type="button" wire:click="kembalikanBukuDirect('{{ $p->id }}')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] transition-all shadow-sm">
                                        Kembalikan Buku
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                <p class="text-xs font-medium">Tidak ada data transaksi peminjaman.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($peminjamans->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</div>
