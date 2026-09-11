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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Peminjaman Buku Paket</h1>
                <span class="px-2.5 py-1 text-[11px] font-black uppercase tracking-wider rounded-lg bg-indigo-100 text-indigo-700 border border-indigo-200">
                    Durasi 1 Tahun
                </span>
            </div>
            <p class="text-slate-500 text-sm mt-1">Kelola pinjaman buku paket pelajaran tahunan siswa selama di tingkat kelas 7, 8, atau 9.</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('portal-perpustakaan.peminjaman-paket.tambah') }}" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Pinjaman Paket
            </a>
            <button @click="$wire.openUnduhModal()" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Unduh Laporan
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="flex border-b border-slate-200">
            <!-- Tab: Dipinjam -->
            <button wire:click="setTab('dipinjam')"
                    class="flex-1 sm:flex-none flex items-center justify-center sm:justify-start gap-2.5 px-5 py-4 text-sm font-bold transition-all border-b-2 focus:outline-none
                           {{ $activeTab === 'dipinjam' ? 'border-indigo-600 text-indigo-700 bg-indigo-50/60' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Paket Aktif (1 Tahun)</span>
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
                <span>Terlambat / Lewat Tahun</span>
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
            <!-- Search & Filter -->
            <div class="flex flex-col lg:flex-row items-center gap-3 justify-between">
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto flex-1">
                    <div class="relative w-full max-w-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Cari siswa, kelas (7A), buku, kode..."
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all">
                    </div>

                    <!-- Filter Tingkat Kelas -->
                    <div class="w-full sm:w-auto">
                        <select wire:model.live="filterGradeLevel" class="w-full sm:w-auto bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-brand-primary/20 focus:border-brand-primary py-2.5 pl-3 pr-8 font-medium">
                            <option value="">Semua Tingkat Kelas</option>
                            <option value="7">Buku Paket Kelas 7</option>
                            <option value="8">Buku Paket Kelas 8</option>
                            <option value="9">Buku Paket Kelas 9</option>
                        </select>
                    </div>

                    <div wire:loading class="text-xs text-slate-400 font-medium hidden sm:flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4 animate-spin text-brand-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Memuat...
                    </div>
                </div>
                
                <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
                    <span class="text-xs text-slate-500 font-medium">Tampilkan:</span>
                    <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-brand-primary/20 focus:border-brand-primary py-2 pl-3 pr-8">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-extrabold uppercase tracking-wide border-b border-slate-200">
                        <tr>
                            <th class="p-3 pl-4">Peminjam</th>
                            <th class="p-3">Buku Paket &amp; Eksemplar</th>
                            <th class="p-3">Tgl Pinjam</th>
                            <th class="p-3">
                                {{ $activeTab === 'dikembalikan' ? 'Tgl Kembali' : 'Jatuh Tempo (1 Thn)' }}
                            </th>
                            <th class="p-3">Status</th>
                            <th class="p-3 pr-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($peminjamans as $p)
                            @php
                                $isOverdue = $p->status === 'dipinjam' && $p->tanggal_jatuh_tempo < $today;
                                $kelasName = $p->peminjam_type === 'siswa' && $p->peminjam?->enrollmentAktif?->kelas?->name ? $p->peminjam->enrollmentAktif->kelas->name : null;
                                $gradeLevel = $p->eksemplarBuku?->buku?->grade_level;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3 pl-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($p->peminjam?->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <p class="font-bold text-slate-800">{{ $p->peminjam?->name ?? 'Anggota' }}</p>
                                                @if($kelasName)
                                                    <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-black text-[10px]">
                                                        Kelas {{ $kelasName }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] text-slate-400">
                                                @if($p->peminjam_type === 'siswa')
                                                    NISN: {{ $p->peminjam?->nisn ?? '-' }}
                                                @elseif($p->peminjam_type === 'guru')
                                                    Guru / Staff (NIP: {{ $p->peminjam?->nip ?? '-' }})
                                                @else
                                                    {{ ucfirst($p->peminjam_type) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-bold text-slate-800">{{ $p->eksemplarBuku?->buku?->judul ?? 'Buku Paket' }}</h4>
                                        @if($gradeLevel)
                                            <span class="px-1.5 py-0.2 text-[9px] font-bold rounded bg-amber-50 text-amber-700 border border-amber-200">
                                                Tingkat {{ $gradeLevel }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="font-mono text-[10px] text-slate-400 font-semibold">{{ $p->eksemplarBuku?->kode_eksemplar }}</span>
                                        @if($p->eksemplarBuku?->buku?->mataPelajaran?->nama_mapel)
                                            <span class="text-[10px] text-slate-400">• {{ $p->eksemplarBuku->buku->mataPelajaran->nama_mapel }}</span>
                                        @endif
                                    </div>
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
                                        @else
                                            <span class="block text-[10px] text-indigo-500 font-semibold">
                                                {{ \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->diffForHumans(['parts' => 1]) }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($isOverdue)
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase bg-rose-100 text-rose-700 border border-rose-200">Terlambat</span>
                                    @elseif($p->status === 'dipinjam')
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase bg-indigo-100 text-indigo-700 border border-indigo-200">Dipinjam (1 Thn)</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">Dikembalikan</span>
                                    @endif
                                </td>
                                <td class="p-3 pr-4 text-right">
                                    @if($p->status === 'dipinjam')
                                        <button type="button" wire:click="kembalikanBuku('{{ $p->id }}')"
                                                wire:confirm="Konfirmasi pengembalian buku paket '{{ $p->eksemplarBuku?->buku?->judul }}'?"
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
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <p class="text-sm font-bold text-slate-500">Tidak ada data peminjaman buku paket</p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        @if($search)
                                            Tidak ditemukan hasil untuk "<strong>{{ $search }}</strong>"
                                        @else
                                            Belum ada peminjaman buku paket pada tab ini.
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
                    {{ $peminjamans->links('vendor.livewire.custom-pagination') }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===== MODAL UNDUH PEMINJAMAN PAKET ===== --}}
    @if($showUnduhModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 lg:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Unduh Peminjaman Buku Paket</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih filter status dan format dokumen.</p>
                    </div>
                    <button type="button" wire:click="$set('showUnduhModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Filter Status --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Filter Status</label>
                    <p class="text-[11px] text-slate-400 mb-3">Kosongkan untuk mengunduh semua status.</p>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition
                            {{ in_array('dipinjam', $filterStatusUnduh) ? 'border-amber-400 bg-amber-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="checkbox"
                                wire:model.live="filterStatusUnduh"
                                value="dipinjam"
                                class="rounded accent-amber-500 w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">Dipinjam</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition
                            {{ in_array('dikembalikan', $filterStatusUnduh) ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="checkbox"
                                wire:model.live="filterStatusUnduh"
                                value="dikembalikan"
                                class="rounded accent-emerald-500 w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">Dikembalikan</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition col-span-2
                            {{ in_array('hilang', $filterStatusUnduh) ? 'border-rose-400 bg-rose-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="checkbox"
                                wire:model.live="filterStatusUnduh"
                                value="hilang"
                                class="rounded accent-rose-500 w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">Hilang</span>
                        </label>
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
                                <div class="text-[10px] text-slate-500">A4 Landscape</div>
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
                    <button type="button" wire:click="downloadPeminjaman" class="px-5 py-2.5 {{ $formatUnduh === 'excel' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' }} text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Unduh {{ strtoupper($formatUnduh) }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL TAMBAH PEMINJAMAN BUKU PAKET ===== --}}
    @if($showTambahModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 lg:p-8 space-y-5 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xl font-bold text-slate-800">Pinjam Buku Paket</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-indigo-100 text-indigo-700">1 Tahun</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Catat pinjaman buku paket untuk siswa/guru selama 1 tahun ajaran.</p>
                    </div>
                    <button type="button" wire:click="$set('showTambahModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="overflow-y-auto space-y-4 pr-1 flex-grow">
                    {{-- Pilih Peminjam --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">1. Pilih Peminjam (Siswa / Guru)</label>
                        <div x-data="{ open: false }" class="relative">
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.400ms="searchMemberModal"
                                       wire:keydown.enter="scanMember"
                                       @focus="open = true"
                                       @keydown.enter="open = false"
                                       @click.away="open = false"
                                       placeholder="🔍 Ketik nama, NISN, atau kelas (misal: 7A)..."
                                       class="w-full pl-3 pr-8 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <button type="button" @click="open = !open" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>

                            <div x-show="open" 
                                 x-transition
                                 class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100">
                                @if(strlen(trim($searchMemberModal)) < 2)
                                    <div class="px-4 py-3 text-xs text-slate-500 text-center">
                                        Ketik minimal 2 huruf untuk mencari siswa / guru...
                                    </div>
                                @elseif($availableMembers->isEmpty())
                                    <div class="p-3 text-xs text-slate-400 italic text-center">
                                        Tidak ada anggota ditemukan.
                                    </div>
                                @else
                                    @foreach($availableMembers as $m)
                                        @php
                                            $extra = isset($m->nisn) && $m->nisn ? '(NISN: '.$m->nisn.')' : (isset($m->nip) && $m->nip ? '(NIP: '.$m->nip.')' : '');
                                            $isSelected = $form_peminjam_id === $m->id;
                                            $displayName = $m->name . ($m->kelas_name !== '-' ? ' - Kelas ' . $m->kelas_name : '');
                                        @endphp
                                        <button type="button"
                                                wire:click="selectMember('{{ $m->id }}', '{{ $m->model_type }}', '{{ addslashes($displayName) }}')"
                                                @click="open = false"
                                                class="w-full text-left px-3 py-2 text-xs flex items-center justify-between hover:bg-indigo-50 transition-colors {{ $isSelected ? 'bg-indigo-50 font-bold text-indigo-700' : 'text-slate-700' }}">
                                            <div>
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-semibold">{{ $m->name }}</span>
                                                    @if($m->kelas_name && $m->kelas_name !== '-')
                                                        <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 text-[10px] font-bold">
                                                            Kelas {{ $m->kelas_name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-slate-400 mt-0.5">
                                                    {{ $extra }}
                                                    <span class="uppercase font-bold tracking-wider ml-1 {{ $m->model_type === 'guru' ? 'text-amber-600' : 'text-blue-600' }}">
                                                        [{{ $m->model_type }}]
                                                    </span>
                                                </div>
                                            </div>
                                            @if($isSelected)
                                                <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @endif
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @error('form_peminjam_id') <span class="text-[11px] font-bold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        @error('form_peminjam_type') <span class="text-[11px] font-bold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Pilih Buku Paket & Eksemplar --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">2. Buku Paket &amp; Kode Eksemplar (Tersedia)</label>
                        <div x-data="{ open: false }" class="relative">
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.400ms="searchEksemplarModal"
                                       wire:keydown.enter="scanEksemplar"
                                       @focus="open = true"
                                       @keydown.enter="open = false"
                                       @click.away="open = false"
                                       placeholder="🔍 Ketik judul buku paket, mapel, atau barcode..."
                                       class="w-full pl-3 pr-8 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <button type="button" @click="open = !open" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>

                            <div x-show="open" 
                                 x-transition
                                 class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100">
                                @if(strlen(trim($searchEksemplarModal)) < 2)
                                    <div class="px-4 py-3 text-xs text-slate-500 text-center">
                                        Ketik minimal 2 huruf untuk mencari buku paket...
                                    </div>
                                @elseif($availableEksemplars->isEmpty())
                                    <div class="p-3 text-xs text-slate-400 italic text-center">
                                        Tidak ada buku paket ditemukan atau stok tidak tersedia.
                                    </div>
                                @else
                                    @foreach($availableEksemplars as $e)
                                        @php
                                            $judul = $e->buku?->judul ?? 'Buku Tanpa Judul';
                                            $grade = $e->buku?->grade_level ? '[Kelas ' . $e->buku->grade_level . '] ' : '';
                                            $mapel = $e->buku?->mataPelajaran?->nama_mapel ? ' • ' . $e->buku->mataPelajaran->nama_mapel : '';
                                            $displayFull = $grade . $judul . ' - [Kode: ' . $e->kode_eksemplar . ']';
                                            $isSelected = $form_eksemplar_id === $e->id;
                                        @endphp
                                        <button type="button"
                                                wire:click="selectEksemplar('{{ $e->id }}', '{{ addslashes($displayFull) }}')"
                                                @click="open = false"
                                                class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-emerald-50 transition-colors {{ $isSelected ? 'bg-emerald-50 font-bold text-emerald-700' : 'text-slate-700' }}">
                                            <div>
                                                <div class="text-[11px] font-semibold leading-tight flex items-center gap-1.5 flex-wrap">
                                                    @if($e->buku?->grade_level)
                                                        <span class="px-1 py-0.2 rounded bg-amber-100 text-amber-800 text-[9px] font-bold">
                                                            Kelas {{ $e->buku->grade_level }}
                                                        </span>
                                                    @endif
                                                    <span>{{ $judul }}</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                                    Kode: {{ $e->kode_eksemplar }} {{ $mapel }}
                                                </div>
                                            </div>
                                            @if($isSelected)
                                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @endif
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @error('form_eksemplar_id') <span class="text-[11px] font-bold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Tanggal Pinjam & Jatuh Tempo (Otomatis 1 Tahun) --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Pinjam</label>
                            <input type="date" wire:model.live="form_tanggal_pinjam" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                            @error('form_tanggal_pinjam') <span class="text-[11px] font-bold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700">Tanggal Jatuh Tempo</label>
                                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.2 rounded">1 Tahun</span>
                            </div>
                            <input type="date" wire:model.live="form_tanggal_jatuh_tempo" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                            @error('form_tanggal_jatuh_tempo') <span class="text-[11px] font-bold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-indigo-50/70 border border-indigo-100 flex items-start gap-2">
                        <svg class="w-4 h-4 text-indigo-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[11px] text-indigo-900 leading-snug">
                            Jatuh tempo otomatis diset <strong>1 tahun (365 hari)</strong> sejak tanggal pinjam untuk masa pinjam buku paket tahunan. Petugas tetap dapat menyesuaikan tanggal bila diperlukan.
                        </p>
                    </div>

                    {{-- Catatan Opsional --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan / Keterangan (Opsional)</label>
                        <input type="text" wire:model.live="form_catatan" placeholder="Contoh: Buku Paket Kelas 7 Semester Ganjil &amp; Genap" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 flex-shrink-0">
                    <button type="button" wire:click="$set('showTambahModal', false)" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                        Batal
                    </button>
                    <button type="button" wire:click="simpanPeminjaman" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Pinjaman Paket
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
