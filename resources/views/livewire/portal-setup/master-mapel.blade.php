@php
    $inputClass = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/15 outline-none transition-all';
@endphp

<div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6 pb-16">
    <!-- Hero Header Banner -->
    <div class="relative bg-gradient-to-r from-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 overflow-hidden border border-brand-primary/30">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white/10 rounded-full filter blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
                    Langkah 5 &bull; Kurikulum &amp; Mata Pelajaran ({{ $school_level }})
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold mt-3 tracking-tight">Master Mata Pelajaran</h1>
                <p class="text-sm text-white/85 mt-1.5 max-w-2xl">
                    Sinkronkan mata pelajaran standar jenjang <strong>{{ $school_level }}</strong> dalam 1 klik, lalu tambah atau kurangi mapel sesuai kurikulum sekolah.
                </p>
            </div>
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('portal-setup.dashboard') }}"
                   class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold border border-white/20 transition-colors">
                    &larr; Ceklist Setup
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('setup_success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2.5">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('setup_success') }}</span>
        </div>
    @endif
    @if(session('setup_error'))
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center gap-2.5">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('setup_error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Kolom Kiri: Sinkron Standar & Tambah Manual -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Sinkronisasi Mapel Standar -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900">Sinkron Mapel Standar</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Jenjang {{ $school_level }}</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    @if($school_level === 'SMK')
                        Sinkronisasi mengisi <strong>mapel umum (normatif-adaptif)</strong> secara otomatis. Untuk <strong>mapel jurusan / kejuruan</strong>, silakan tambahkan manual melalui form di bawah.
                    @else
                        Menambahkan otomatis mata pelajaran standar jenjang <strong>{{ $school_level }}</strong> tanpa duplikasi. Setelah tersinkron, Anda tetap bebas menambah atau menghapus mapel.
                    @endif
                </p>

                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Daftar Mapel Standar ({{ count($standarList) }}):</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($standarList as $item)
                            @php $already = in_array($item['nama_mapel'], $existingNames, true); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $already ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white text-slate-600 border-slate-200' }}">
                                {{ $item['kode_mapel'] }} &bull; {{ $item['nama_mapel'] }}
                                @if($already)
                                    <span class="text-emerald-600">&check;</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>

                <button type="button" wire:click="syncStandarMapel"
                        class="w-full py-3 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white text-sm font-bold shadow-sm transition-all">
                    {{ $missingCount > 0 ? "Sinkronkan {$missingCount} Mapel Standar" : 'Sinkron Ulang Mapel Standar' }}
                </button>
            </div>

            <!-- Tambah Mapel Tambahan / Jurusan -->
            <form wire:submit="createMapel" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <h2 class="text-base font-extrabold text-slate-900">
                    {{ $school_level === 'SMK' ? 'Tambah Mapel Jurusan / Tambahan' : 'Tambah Mapel Tambahan' }}
                </h2>
                <p class="text-xs text-slate-500">
                    @if($school_level === 'SMK')
                        Masukkan mata pelajaran produktif/kejuruan (misal: Pemrograman Web, Akuntansi Keuangan) atau muatan lokal.
                    @else
                        Tambahkan mata pelajaran khusus sekolah, muatan lokal, atau peminatan tambahan.
                    @endif
                </p>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nama_mapel"
                           placeholder="{{ $school_level === 'SMK' ? 'Contoh: Konsentrasi Keahlian TKJ, Basis Data' : 'Contoh: Bahasa Daerah, Tahfidz Al-Quran' }}"
                           class="{{ $inputClass }}">
                    @error('nama_mapel') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Mapel (Opsional)</label>
                        <input type="text" wire:model="kode_mapel" placeholder="Contoh: TKJ, BD, MULOK" class="{{ $inputClass }}">
                        @error('kode_mapel') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori</label>
                        <select wire:model="kategori" class="{{ $inputClass }}">
                            <option value="Umum">Umum</option>
                            <option value="Pilihan">Pilihan</option>
                            <option value="Kejuruan">Kejuruan</option>
                            <option value="Muatan Lokal">Muatan Lokal</option>
                        </select>
                        @error('kategori') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold shadow-sm transition-all">
                    + Tambah Mata Pelajaran
                </button>
            </form>
        </div>

        <!-- Kolom Kanan: Daftar Mata Pelajaran Terdaftar -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm h-fit">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Daftar Mata Pelajaran ({{ $mapels->count() }})</h2>
                    <p class="text-xs text-slate-500">Anda bisa edit atau hapus mapel yang belum digunakan di Pengajaran/Ujian.</p>
                </div>
                <a href="{{ route('portal-setup.dashboard') }}"
                   class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition-colors shrink-0">
                    Ceklist Setup &rarr;
                </a>
            </div>

            @if($mapels->isEmpty())
                <div class="text-center py-12 text-slate-500 text-sm bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    Belum ada mata pelajaran terdaftar. Klik <strong>Sinkronkan Mapel Standar</strong> di samping untuk memulai.
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($mapels as $mapel)
                        @php
                            $isUsed = ($mapel->pengajarans_count > 0) || ($mapel->ujian_akademiks_count > 0);
                            $catBadge = match ($mapel->kategori) {
                                'Kejuruan' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'Pilihan' => 'bg-purple-50 text-purple-700 border-purple-200',
                                'Muatan Lokal' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            @if($editingId === $mapel->id)
                                <form wire:submit="saveEdit" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                    <div class="sm:col-span-5">
                                        <input type="text" wire:model="edit_nama_mapel" class="{{ $inputClass }}" placeholder="Nama Mapel">
                                        @error('edit_nama_mapel') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input type="text" wire:model="edit_kode_mapel" class="{{ $inputClass }}" placeholder="Kode">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <select wire:model="edit_kategori" class="{{ $inputClass }}">
                                            <option value="Umum">Umum</option>
                                            <option value="Pilihan">Pilihan</option>
                                            <option value="Kejuruan">Kejuruan</option>
                                            <option value="Muatan Lokal">Muatan Lokal</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2 flex items-center gap-1.5 justify-end">
                                        <button type="submit" class="px-3 py-2 rounded-xl bg-brand-primary text-white text-xs font-bold">Simpan</button>
                                        <button type="button" wire:click="cancelEdit" class="px-2.5 py-2 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                                    </div>
                                </form>
                            @else
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-slate-900 text-sm">{{ $mapel->nama_mapel }}</span>
                                        @if($mapel->kode_mapel)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200/80 text-slate-700">{{ $mapel->kode_mapel }}</span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $catBadge }}">{{ $mapel->kategori }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $mapel->pengajarans_count }} pengajaran &bull; {{ $mapel->ujian_akademiks_count }} ujian
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" wire:click="startEdit({{ $mapel->id }})"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-200/70 transition-colors">
                                        Edit
                                    </button>
                                    @if($isUsed)
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-400 bg-slate-100" title="Sudah dipakai di pengajaran/ujian">
                                            Dipakai
                                        </span>
                                    @else
                                        <button type="button" wire:click="deleteMapel({{ $mapel->id }})"
                                                wire:confirm="Yakin hapus mata pelajaran '{{ $mapel->nama_mapel }}'?"
                                                class="px-3 py-1.5 rounded-lg text-xs font-bold text-red-600 hover:bg-red-50 transition-colors">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

