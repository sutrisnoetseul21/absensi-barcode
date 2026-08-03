<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Katalog & Input Buku</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola katalog koleksi buku perpustakaan & penambahan eksemplar baru.</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="$wire.openUnduhModal()" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Unduh Katalog
            </button>
            <button @click="$wire.openInputModal()" class="px-5 py-3 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-2xl font-bold text-xs shadow-lg shadow-brand-primary/30 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Buku Baru
            </button>
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

    <!-- Catalog Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="p-4 pl-6">Judul & Detail Buku</th>
                        <th class="p-4">Koleksi & DDC</th>
                        <th class="p-4">Lokasi Rak</th>
                        <th class="p-4">Fisik Eksemplar</th>
                        <th class="p-4 text-center">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($bukus as $buku)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4 pl-6">
                                <h4 class="font-bold text-slate-900 text-sm">{{ $buku->judul }}</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $buku->penulis ?? 'Tanpa Penulis' }} &bull; {{ $buku->penerbit ?? '-' }} ({{ $buku->tahun_terbit ?? '-' }})
                                </p>
                                @if($buku->isbn)
                                    <span class="inline-block mt-1 font-mono text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded border">ISBN: {{ $buku->isbn }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-brand-primary/10 text-brand-primary font-bold rounded-lg border border-brand-primary/20 inline-block text-[11px]">
                                    {{ $buku->kategoriBuku?->nama_kategori ?? 'Umum' }}
                                </span>
                                @if($buku->klasifikasiDdc)
                                    <span class="block text-[10px] text-slate-400 mt-1 font-mono">DDC: {{ $buku->klasifikasiDdc->kode_ddc }} - {{ $buku->klasifikasiDdc->kategori }}</span>
                                @endif
                            </td>
                            <td class="p-4 font-medium text-slate-600">
                                {{ $buku->lokasi_rak ?? '-' }}
                            </td>
                            <td class="p-4">
                                @php
                                    $totalFisik = $buku->eksemplarBukus->count();
                                    $tersedia = $buku->eksemplarBukus->where('status', 'tersedia')->count();
                                    $dipinjam = $buku->eksemplarBukus->where('status', 'dipinjam')->count();
                                @endphp
                                <span class="font-bold text-slate-800">{{ $totalFisik }} Fisik</span>
                                <div class="flex items-center gap-1.5 mt-1 text-[10px]">
                                    <span class="text-emerald-600 font-bold">{{ $tersedia }} Ada</span>
                                    <span>&bull;</span>
                                    <span class="text-amber-600 font-bold">{{ $dipinjam }} Pinjam</span>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="openDetailEksemplarModal('{{ $buku->id }}')" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl text-[11px] inline-flex items-center gap-1 border border-blue-200/80 transition-all shadow-2xs" title="Detail Eksemplar Buku">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Detail
                                    </button>
                                    <a href="{{ route('perpustakaan.cetak-barcode', $buku->id) }}" target="_blank" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-[11px] inline-flex items-center gap-1 border border-indigo-200/80 transition-all shadow-2xs" title="Cetak Label Barcode Eksemplar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                        Barcode
                                    </a>
                                    <button type="button" wire:click="openLabelSpineModal('{{ $buku->id }}')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-[11px] inline-flex items-center gap-1 border border-slate-200 transition-all shadow-2xs" title="Cetak Label Spine / Punggung Buku">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-7 5h7"></path></svg>
                                        Label Spine
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <p class="text-xs font-medium">Belum ada buku katalog terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bukus->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $bukus->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Input Buku Baru -->
    @if($showInputModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 lg:p-8 space-y-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Tambah Buku Katalog Baru</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Input buku beserta otomatisasi eksemplar & inventaris.</p>
                    </div>
                    <button type="button" @click="$wire.set('showInputModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="simpanBuku" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Buku *</label>
                        <input wire:model="judul" type="text" required placeholder="Contoh: Laskar Pelangi" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        @error('judul') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Koleksi / Kategori *</label>
                            <select wire:model.live="kategori_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value="">-- Pilih Koleksi / Kategori --</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('kategori_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        
                        @php
                            $selectedKat = $kategoriList->firstWhere('id', $kategori_id);
                            $isNonFiksi = $selectedKat && strtolower(trim($selectedKat->nama_kategori)) === 'non fiksi';
                        @endphp
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran</label>
                            <select wire:model="mapel_id" {{ $isNonFiksi ? '' : 'disabled' }} class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary disabled:opacity-50 disabled:bg-slate-100">
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($mapelList as $mapel)
                                    <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                                @endforeach
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Klasifikasi DDC (Opsional)</label>
                            <select wire:model="klasifikasi_ddc_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value="">-- Pilih DDC --</option>
                                @foreach($ddcList as $ddc)
                                    <option value="{{ $ddc->id }}">{{ $ddc->kode_ddc }} - {{ $ddc->kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang (Grade)</label>
                            <select wire:model="grade_level" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value="">-- Pilih Jenjang --</option>
                                <option value="umum">Semua Jenjang / Umum</option>
                                <option value="7">Kelas 7</option>
                                <option value="8">Kelas 8</option>
                                <option value="9">Kelas 9</option>
                            </select>
                            @error('grade_level') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Penulis</label>
                            <input wire:model="penulis" type="text" placeholder="Nama Penulis" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Penerbit</label>
                            <input wire:model="penerbit" type="text" placeholder="Nama Penerbit" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Terbit</label>
                            <input wire:model="tahun_terbit" type="number" placeholder="2026" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ISBN</label>
                            <input wire:model="isbn" type="text" placeholder="978-xxx-xxx" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Rak</label>
                            <input wire:model="lokasi_rak" type="text" placeholder="Rak A1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <h4 class="text-sm font-bold text-slate-800 mb-1">Generate Eksemplar Awal & Inventaris</h4>
                        <p class="text-[11px] text-slate-500 mb-4">Isi data inventaris dan jumlah eksemplar yang ingin dibuat otomatis setelah buku ini disimpan.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Asal Buku *</label>
                                <select wire:model="asal_buku" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                    <option value="">-- Pilih Asal Buku --</option>
                                    <option value="Pembelian">Pembelian</option>
                                    <option value="Hibah">Hibah</option>
                                    <option value="Tukar">Tukar</option>
                                    <option value="Terbitan Sendiri">Terbitan Sendiri</option>
                                </select>
                                @error('asal_buku') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Buku (Rp)</label>
                                <input wire:model="harga_buku" type="number" placeholder="Contoh: 50000" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Eksemplar *</label>
                                <input wire:model="jumlah_eksemplar" type="number" min="1" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                @error('jumlah_eksemplar') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Prefix Kode/Singkatan Buku *</label>
                                <input wire:model="prefix_kode" type="text" required placeholder="Misal: INF, MAT" maxlength="10" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <p class="text-[10px] text-slate-500 mt-1">Contoh: INF untuk Informatika</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="$wire.set('showInputModal', false)" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-primary/20">
                            Simpan & Generate Barcode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== MODAL UNDUH KATALOG ===== --}}
    @if($showUnduhModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             x-data="{
                 nonFiksiId: '{{ $nonFiksiKategoriId }}',
                 get showMapel() {
                     return $wire.filterKategoriUnduh.includes(this.nonFiksiId);
                 }
             }">
            <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 lg:p-8 space-y-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Unduh Katalog Buku</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih koleksi dan format dokumen yang ingin diunduh.</p>
                    </div>
                    <button type="button" wire:click="$set('showUnduhModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Pilihan Koleksi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Pilih Koleksi</label>
                    <p class="text-[11px] text-slate-400 mb-3">Kosongkan semua untuk mengunduh seluruh koleksi.</p>

                    {{-- Tombol Pilih/Hapus Semua --}}
                    <div class="flex gap-2 mb-3">
                        <button type="button" wire:click="$set('filterKategoriUnduh', {{ json_encode($kategoriList->pluck('id')->toArray()) }})" class="px-3 py-1.5 text-[11px] font-bold bg-brand-primary/10 text-brand-primary rounded-lg hover:bg-brand-primary/20 transition">
                            Pilih Semua
                        </button>
                        <button type="button" wire:click="$set('filterKategoriUnduh', [])" class="px-3 py-1.5 text-[11px] font-bold bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition">
                            Hapus Pilihan
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($kategoriList as $kat)
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition
                                {{ in_array($kat->id, $filterKategoriUnduh) ? 'border-brand-primary/50 bg-brand-primary/5' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                <input type="checkbox"
                                    wire:model.live="filterKategoriUnduh"
                                    value="{{ $kat->id }}"
                                    class="rounded accent-brand-primary w-4 h-4">
                                <span class="text-xs font-semibold text-slate-700">{{ $kat->nama_kategori }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Pilihan Mata Pelajaran (hanya muncul jika Non Fiksi dipilih) --}}
                <div x-show="showMapel" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                        <label class="block text-xs font-bold text-amber-800 mb-1">
                            🎓 Filter Mata Pelajaran (Non Fiksi)
                        </label>
                        <p class="text-[11px] text-amber-600 mb-3">Kosongkan untuk mengunduh semua mapel dari Non Fiksi.</p>

                        <div class="flex gap-2 mb-3">
                            <button type="button" wire:click="$set('filterMapelUnduh', {{ json_encode($mapelList->pluck('id')->toArray()) }})" class="px-3 py-1.5 text-[11px] font-bold bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition">
                                Pilih Semua Mapel
                            </button>
                            <button type="button" wire:click="$set('filterMapelUnduh', [])" class="px-3 py-1.5 text-[11px] font-bold bg-white text-slate-600 rounded-lg hover:bg-slate-50 border border-slate-200 transition">
                                Hapus Pilihan
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                            @foreach($mapelList as $mapel)
                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition
                                    {{ in_array($mapel->id, $filterMapelUnduh) ? 'border-amber-400 bg-amber-50' : 'border-slate-200 bg-white hover:border-amber-300' }}">
                                    <input type="checkbox"
                                        wire:model.live="filterMapelUnduh"
                                        value="{{ $mapel->id }}"
                                        class="rounded accent-amber-500 w-4 h-4">
                                    <span class="text-xs font-semibold text-slate-700">{{ $mapel->nama_mapel }}</span>
                                </label>
                            @endforeach
                        </div>
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
                    <button type="button" wire:click="downloadKatalog" class="px-5 py-2.5 {{ $formatUnduh === 'excel' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' }} text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Unduh {{ strtoupper($formatUnduh) }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL CETAK LABEL SPINE ===== --}}
    @if($showLabelSpineModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 lg:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Cetak Label Spine</h3>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium line-clamp-1" title="{{ $selectedBukuJudulForSpine }}">
                            {{ $selectedBukuJudulForSpine }}
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showLabelSpineModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Jumlah Label yang Dicetak *
                        </label>
                        <input type="number" 
                               wire:model="jumlahCetakSpine" 
                               min="1" 
                               max="{{ $maxEksemplarSpine }}" 
                               required 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                        <p class="text-[11px] text-slate-500 mt-1">
                            Jumlah eksemplar tersedia: <span class="font-bold text-slate-700">{{ $maxEksemplarSpine }}</span> label.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showLabelSpineModal', false)" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                        Batal
                    </button>
                    <a href="{{ route('perpustakaan.cetak-label-spine', ['buku' => $selectedBukuIdForSpine, 'jumlah' => max(1, (int)$jumlahCetakSpine)]) }}" 
                       target="_blank" 
                       wire:click="$set('showLabelSpineModal', false)"
                       class="px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-primary/20 inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak PDF
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL DETAIL EKSEMPLAR BUKU ===== --}}
    @if($showDetailEksemplarModal ?? false)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full p-6 lg:p-8 space-y-6 max-h-[90vh] flex flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Eksemplar Buku</h3>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium line-clamp-1">
                            {{ $selectedBukuDetail?->judul ?? '' }}
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showDetailEksemplarModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Action Buttons & Search Bar --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        @if($selectedBukuDetail)
                            <a href="{{ route('perpustakaan.cetak-barcode', $selectedBukuDetail->id) }}" target="_blank" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs inline-flex items-center gap-1.5 border border-indigo-200 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Cetak Barcode (Semua)
                            </a>
                        @endif
                    </div>

                    <div class="relative w-full sm:w-64">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input wire:model.live.debounce.300ms="searchEksemplar" type="text" placeholder="Cari kode / status / kondisi..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    </div>
                </div>

                {{-- Table Eksemplar --}}
                <div class="overflow-y-auto flex-1 rounded-2xl border border-slate-200/80">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80 sticky top-0 bg-slate-50">
                            <tr>
                                <th class="p-3 pl-4">Kode eksemplar</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Kondisi fisik</th>
                                <th class="p-3 pr-4 text-right">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($eksemplarDetailList as $eks)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-3 pl-4 font-mono font-bold text-slate-900">{{ $eks->kode_eksemplar }}</td>
                                    <td class="p-3">
                                        @php
                                            $statusColor = match($eks->status) {
                                                'tersedia' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'dipinjam' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'rusak'    => 'bg-rose-100 text-rose-700 border-rose-200',
                                                'hilang'   => 'bg-slate-200 text-slate-700 border-slate-300',
                                                default    => 'bg-slate-100 text-slate-600 border-slate-200',
                                            };
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border inline-block capitalize {{ $statusColor }}">
                                            {{ $eks->status }}
                                        </span>
                                    </td>
                                    <td class="p-3 capitalize text-slate-600">
                                        {{ str_replace('_', ' ', $eks->kondisi_fisik) }}
                                    </td>
                                    <td class="p-3 pr-4 text-right">
                                        <a href="{{ route('perpustakaan.cetak-barcode-eksemplar', $eks->id) }}" target="_blank" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg text-[10px] inline-flex items-center gap-1 border border-indigo-200 transition-all shadow-2xs" title="Cetak Barcode Eksemplar Ini">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                            Cetak Barcode
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400">
                                        Belum ada eksemplar fisik terdaftar untuk buku ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer Actions --}}
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <span class="text-xs text-slate-500">
                        Total Eksemplar: <strong class="text-slate-800">{{ $eksemplarDetailList->count() }}</strong>
                    </span>
                    <button type="button" wire:click="$set('showDetailEksemplarModal', false)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
