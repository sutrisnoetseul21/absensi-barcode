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

    <!-- Scan Circulation Widget Card (Clean & Light Mode) -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">Terminal Transaksi Sirkulasi</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Langkah 1: Identifikasi Anggota &rarr; Langkah 2: Scan Barcode Buku</p>
                </div>
            </div>

            @if($peminjamNama)
                <button type="button" wire:click="resetPeminjam" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition-colors">
                    Reset Peminjam
                </button>
            @endif
        </div>

        <!-- Feedback Alert -->
        @if($feedbackMessage)
            <div class="p-4 rounded-2xl text-xs font-bold border flex items-center justify-between {{ $feedbackType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
                <div class="flex items-center gap-2">
                    @if($feedbackType === 'success')
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Step 1: Scan Kartu Anggota -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-brand-primary text-white text-[11px] font-extrabold flex items-center justify-center">1</span>
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Scan Kartu Anggota (NISN / NIS / NIP)</label>
                </div>
                
                @if($peminjamNama)
                    <div class="p-3.5 bg-white border border-emerald-300 rounded-xl flex items-center gap-3 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-brand-primary text-white font-extrabold flex items-center justify-center text-xs shadow-sm">
                            {{ strtoupper(substr($peminjamNama, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-900 truncate">{{ $peminjamNama }}</h4>
                            <p class="text-xs text-slate-500 truncate">{{ $peminjamSub }}</p>
                        </div>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-md uppercase">Terverifikasi</span>
                    </div>
                @else
                    <form wire:submit.prevent="processScanPeminjam" class="flex gap-2">
                        <input wire:model="barcodeAnggota" type="text" placeholder="Scan/ketik NISN, NIS, atau NIP..." class="flex-1 px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary shadow-sm font-medium">
                        <button type="submit" class="px-4 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-primary/20 transition-all">
                            Cari
                        </button>
                    </form>
                @endif
            </div>

            <!-- Step 2: Scan Barcode Buku -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full {{ $peminjamId ? 'bg-brand-primary text-white' : 'bg-slate-300 text-slate-600' }} text-[11px] font-extrabold flex items-center justify-center">2</span>
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Scan Kode Eksemplar Buku</label>
                </div>
                <form wire:submit.prevent="processScanBuku" class="flex gap-2">
                    <input wire:model="barcodeBuku" type="text" placeholder="Scan/ketik kode eksemplar (misal: UMM00001)..." class="flex-1 px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary shadow-sm font-medium {{ !$peminjamId ? 'opacity-60 bg-slate-100 cursor-not-allowed' : '' }}" {{ !$peminjamId ? 'disabled' : '' }}>
                    <button type="submit" class="px-4 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-primary/20 transition-all {{ !$peminjamId ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$peminjamId ? 'disabled' : '' }}>
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
