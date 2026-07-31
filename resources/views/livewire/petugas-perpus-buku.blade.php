<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Katalog & Input Buku</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola katalog koleksi buku perpustakaan & penambahan eksemplar baru.</p>
        </div>

        <button @click="$wire.openInputModal()" class="px-5 py-3 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-2xl font-bold text-xs shadow-lg shadow-brand-primary/30 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Buku Baru
        </button>
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
                        <th class="p-4 pr-6 text-right">Opsi</th>
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
                                    <span class="block text-[10px] text-slate-400 mt-1 font-mono">DDC: {{ $buku->klasifikasiDdc->kode_ddc }} - {{ $buku->klasifikasiDdc->nama_klasifikasi }}</span>
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
                            <td class="p-4 pr-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('perpustakaan.cetak-barcode', $buku->id) }}" target="_blank" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-[11px] inline-flex items-center gap-1 border border-indigo-200/80 transition-all shadow-2xs" title="Cetak Label Barcode Eksemplar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                        Barcode
                                    </a>
                                    <a href="{{ route('perpustakaan.cetak-label-spine', $buku->id) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-[11px] inline-flex items-center gap-1 border border-slate-200 transition-all shadow-2xs" title="Cetak Label Spine / Punggung Buku">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-7 5h7"></path></svg>
                                        Label Spine
                                    </a>
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
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
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
                                    <option value="{{ $ddc->id }}">{{ $ddc->kode_ddc }} - {{ $ddc->nama_klasifikasi }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang (Grade)</label>
                            <select wire:model="grade_level" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value="umum">Semua Jenjang / Umum</option>
                                <option value="7">Kelas 7</option>
                                <option value="8">Kelas 8</option>
                                <option value="9">Kelas 9</option>
                            </select>
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
                                    <option value="Pembelian">Pembelian</option>
                                    <option value="Hibah">Hibah</option>
                                    <option value="Tukar">Tukar</option>
                                    <option value="Terbitan Sendiri">Terbitan Sendiri</option>
                                </select>
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
</div>
