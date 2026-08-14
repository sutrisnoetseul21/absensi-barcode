<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Riwayat Presensi Kunjungan</h1>
            <p class="text-slate-500 text-sm mt-1">Log kunjungan harian siswa & guru ke perpustakaan.</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="$wire.openUnduhModal()" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Unduh Riwayat
            </button>
            <a href="{{ route('perpustakaan.kunjungan') }}" target="_blank" class="px-5 py-3 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-2xl font-bold text-xs shadow-lg shadow-brand-primary/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                Buka Presensi
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center gap-4 justify-between">
        <div class="relative w-full md:w-64 shrink-0">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama pengunjung..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="flex items-center gap-2">
                <input wire:model.live="filterTanggalAwal" type="date" title="Dari Tanggal" class="py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                <span class="text-slate-400 text-xs font-medium">s.d</span>
                <input wire:model.live="filterTanggalAkhir" type="date" title="Sampai Tanggal" class="py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
            </div>
            
            <select wire:model.live="filterType" class="py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                <option value="">Semua Anggota</option>
                <option value="siswa">Siswa</option>
                <option value="guru">Guru / Staff</option>
            </select>

            <select wire:model.live="perPage" class="py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                <option value="12">12</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <!-- Kunjungan Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="p-4 pl-6">Tanggal & Waktu</th>
                        <th class="p-4">Pengunjung / Anggota</th>
                        <th class="p-4">Tipe Anggota</th>
                        <th class="p-4 pr-6 text-right">Tujuan Kunjungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($kunjungans as $k)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4 pl-6">
                                <span class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($k->tanggal)->format('d M Y') }}</span>
                                <span class="block text-[11px] font-mono text-brand-primary font-semibold mt-0.5">{{ \Carbon\Carbon::parse($k->waktu_masuk)->format('H:i:s') }}</span>
                            </td>
                            <td class="p-4">
                                <h4 class="font-bold text-slate-800 text-xs">{{ $k->pengunjung?->name ?? 'Pengunjung' }}</h4>
                                <span class="text-[10px] text-slate-400">
                                    @if($k->pengunjung_type === 'siswa' && $k->pengunjung?->enrollmentAktif)
                                        Kelas {{ $k->pengunjung->enrollmentAktif->kelas->name }}
                                    @elseif($k->pengunjung_type === 'guru')
                                        Guru / Staff
                                    @else
                                        Anggota Perpustakaan
                                    @endif
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider {{ $k->pengunjung_type === 'siswa' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                    {{ $k->pengunjung_type }}
                                </span>
                            </td>
                            <td class="p-4 pr-6 text-right font-medium text-slate-600">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md text-[11px] font-medium border border-slate-200 inline-block">
                                    {{ $k->tujuan_kunjungan }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="text-xs font-medium">Belum ada data kunjungan pada filter ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kunjungans->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $kunjungans->links('vendor.livewire.custom-pagination') }}
            </div>
        @endif
    </div>

    {{-- ===== MODAL UNDUH KUNJUNGAN ===== --}}
    @if($showUnduhModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 lg:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Unduh Riwayat Kunjungan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih periode dan format dokumen.</p>
                    </div>
                    <button type="button" wire:click="$set('showUnduhModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Filter Periode (Tanggal) --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Periode Tanggal</label>
                    <p class="text-[11px] text-slate-400 mb-3">Kosongkan untuk mengunduh semua waktu.</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Mulai Tanggal</label>
                            <input type="date" wire:model.live="filterMulaiUnduh" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Sampai Tanggal</label>
                            <input type="date" wire:model.live="filterAkhirUnduh" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                    </div>
                </div>

                {{-- Filter Tipe Anggota --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Filter Tipe Anggota</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition
                            {{ in_array('siswa', $filterTipeAnggotaUnduh) ? 'border-brand-primary bg-brand-primary/10' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="checkbox"
                                wire:model.live="filterTipeAnggotaUnduh"
                                value="siswa"
                                class="rounded accent-brand-primary w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">Siswa</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition
                            {{ in_array('guru', $filterTipeAnggotaUnduh) ? 'border-brand-primary bg-brand-primary/10' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="checkbox"
                                wire:model.live="filterTipeAnggotaUnduh"
                                value="guru"
                                class="rounded accent-brand-primary w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">Guru / Staff</span>
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
                                <div class="text-[10px] text-slate-500">A4 Portrait</div>
                            </div>
                        </label>
                        <label class="flex-1 flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition
                            {{ $formatUnduh === 'excel' ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" wire:model.live="formatUnduh" value="excel" class="accent-emerald-600 w-4 h-4">
                            <div>
                                <div class="text-xs font-bold text-slate-800">📊 Excel</div>
                                <div class="text-[10px] text-slate-500">Format .xlsx</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showUnduhModal', false)" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                        Batal
                    </button>
                    <button type="button" wire:click="downloadKunjungan" class="px-5 py-2.5 {{ $formatUnduh === 'excel' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' }} text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Unduh {{ strtoupper($formatUnduh) }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
