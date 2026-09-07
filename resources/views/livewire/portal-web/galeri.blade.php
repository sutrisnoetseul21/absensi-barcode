<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 sm:p-7">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-8 h-8 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center text-sm">
                        <i class="fas fa-images"></i>
                    </span>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Galeri Dokumentasi & Foto</h2>
                </div>
                <p class="text-sm text-slate-500 max-w-2xl">Kelola koleksi foto dokumentasi kegiatan, prestasi, upacara, dan sarana prasarana sekolah.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Link Pratinjau Publik --}}
                <a href="{{ route('galeri.all') }}" target="_blank" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-all shadow-xs">
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                    <span>Pratinjau Publik</span>
                </a>

                {{-- Tombol Tambah Single --}}
                <button wire:click="openCreate('single')" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-violet-200 hover:border-violet-300 bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold transition-all shadow-xs">
                    <i class="fas fa-plus text-[11px]"></i>
                    <span>Tambah 1 Foto</span>
                </button>

                {{-- Tombol Upload Banyak (Utama) --}}
                <button wire:click="openCreate('batch')" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-xs font-bold shadow-md shadow-violet-500/20 hover:shadow-lg transition-all">
                    <i class="fas fa-layer-group text-sm"></i>
                    <span>Unggah Banyak Foto Sekaligus</span>
                </button>
            </div>
        </div>

        {{-- Toolbar & Filters --}}
        <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                {{-- Search Box --}}
                <div class="relative w-full sm:max-w-xs">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Cari judul atau keterangan..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl text-xs font-medium text-slate-800 outline-none transition-all">
                </div>

                {{-- Sort Dropdown --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400">Urutkan:</span>
                    <select wire:model.live="sortBy" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:border-violet-500">
                        <option value="urutan">Berdasarkan Urutan</option>
                        <option value="latest">Terbaru Ditambahkan</option>
                        <option value="oldest">Terlama</option>
                    </select>
                </div>
            </div>

            {{-- Stat & Select All --}}
            <div class="flex items-center gap-4 text-xs">
                <label class="inline-flex items-center gap-2 font-bold text-slate-600 cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="selectAll" 
                           class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500 cursor-pointer">
                    <span>Pilih Semua Halaman Ini</span>
                </label>

                <span class="text-slate-400">|</span>

                <span class="font-bold text-slate-500">
                    Total: <strong class="text-slate-900 font-extrabold">{{ $totalGaleri }}</strong> Foto
                </span>
            </div>
        </div>
    </div>

    {{-- Floating Action Bar for Bulk Selection --}}
    @if(count($selectedIds) > 0)
        <div class="sticky top-4 z-30 bg-slate-900 text-white rounded-2xl p-4 shadow-xl flex items-center justify-between animate-in fade-in slide-in-from-top-3 duration-200">
            <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-lg bg-violet-600 text-white flex items-center justify-center font-bold text-xs">
                    {{ count($selectedIds) }}
                </span>
                <span class="text-xs sm:text-sm font-bold">Foto telah dipilih</span>
            </div>

            <div class="flex items-center gap-2">
                <button wire:click="$set('selectedIds', [])" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                    Batal Pilih
                </button>
                <button wire:click="confirmBatchDelete" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white flex items-center gap-1.5 transition-all shadow-sm">
                    <i class="fas fa-trash-alt text-[10px]"></i>
                    <span>Hapus Terpilih ({{ count($selectedIds) }})</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="px-5 py-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-bold flex items-center gap-3 animate-in fade-in duration-200">
            <i class="fas fa-check-circle text-emerald-500 text-base shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Grid Foto --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($galeris as $item)
            @php
                $isSelected = in_array($item->id, $selectedIds);
            @endphp
            <div class="bg-white rounded-2xl border transition-all duration-300 overflow-hidden group relative flex flex-col justify-between {{ $isSelected ? 'border-violet-500 ring-2 ring-violet-500/30 shadow-md' : 'border-slate-200/80 shadow-xs hover:shadow-lg hover:border-slate-300' }}">
                
                {{-- Checkbox Pemilih --}}
                <div class="absolute top-2.5 left-2.5 z-20 transition-opacity duration-200 {{ $isSelected ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }}">
                    <label class="relative flex items-center justify-center cursor-pointer">
                        <input type="checkbox" value="{{ $item->id }}" wire:model.live="selectedIds" 
                               class="w-5 h-5 rounded-lg border-2 border-white shadow-md text-violet-600 focus:ring-0 cursor-pointer">
                    </label>
                </div>

                {{-- Badge Urutan --}}
                <div class="absolute top-2.5 right-2.5 z-10">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-900/70 backdrop-blur-md text-white border border-white/20 shadow-xs">
                        #{{ $item->urutan }}
                    </span>
                </div>

                {{-- Image Container --}}
                <div class="relative aspect-4/3 w-full bg-slate-100 overflow-hidden">
                    @if($item->foto_path)
                        <img src="{{ asset('storage/' . $item->foto_path) }}" alt="{{ $item->judul }}" 
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                            <i class="fas fa-image text-3xl"></i>
                        </div>
                    @endif

                    {{-- Hover Action Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 p-2">
                        {{-- Tombol Zoom Preview --}}
                        <button wire:click="openPreview('{{ $item->id }}')" 
                                class="w-8 h-8 rounded-xl bg-white/90 hover:bg-white text-slate-800 hover:text-violet-600 flex items-center justify-center text-xs shadow-md transition-all hover:scale-110" title="Pratinjau Foto">
                            <i class="fas fa-search-plus"></i>
                        </button>

                        {{-- Tombol Edit --}}
                        <button wire:click="openEdit('{{ $item->id }}')" 
                                class="w-8 h-8 rounded-xl bg-white/90 hover:bg-white text-slate-800 hover:text-indigo-600 flex items-center justify-center text-xs shadow-md transition-all hover:scale-110" title="Edit Detail">
                            <i class="fas fa-pen"></i>
                        </button>

                        {{-- Tombol Hapus --}}
                        <button wire:click="confirmDelete('{{ $item->id }}')" 
                                class="w-8 h-8 rounded-xl bg-white/90 hover:bg-white text-rose-600 flex items-center justify-center text-xs shadow-md transition-all hover:scale-110" title="Hapus Foto">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>

                {{-- Card Info --}}
                <div class="p-3 border-t border-slate-100 bg-white">
                    <h4 class="text-xs font-bold text-slate-900 truncate" title="{{ $item->judul }}">
                        {{ $item->judul ?: 'Tanpa Judul' }}
                    </h4>
                    @if($item->keterangan)
                        <p class="text-[11px] text-slate-400 truncate mt-0.5" title="{{ $item->keterangan }}">
                            {{ $item->keterangan }}
                        </p>
                    @endif
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-200/80">
                <div class="w-16 h-16 rounded-3xl bg-violet-50 text-violet-500 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-images"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Belum Ada Foto Galeri</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mb-6">Mulai dokumentasikan kegiatan sekolah dengan mengunggah foto satuan atau langsung banyak foto sekaligus.</p>
                <div class="flex items-center justify-center gap-3">
                    <button wire:click="openCreate('batch')" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold shadow-md shadow-violet-600/20 transition-all flex items-center gap-2">
                        <i class="fas fa-layer-group"></i>
                        <span>Unggah Banyak Foto Sekaligus</span>
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($galeris->hasPages())
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4">
            {{ $galeris->links() }}
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL: FORM UPLOAD (BATCH ATAU SINGLE)                    --}}
    {{-- ======================================================== --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
                
                {{-- Modal Header with Tabs --}}
                <div class="px-6 py-4 bg-gradient-to-r from-violet-600 to-indigo-600 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <i class="fas {{ $editingId ? 'fa-pen' : ($uploadMode === 'batch' ? 'fa-layer-group' : 'fa-image') }}"></i>
                        <h3 class="text-sm sm:text-base font-extrabold">
                            {{ $editingId ? 'Edit Foto Galeri' : ($uploadMode === 'batch' ? 'Unggah Banyak Foto Sekaligus (Batch)' : 'Tambah Satu Foto Galeri') }}
                        </h3>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-white/80 hover:text-white text-lg">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Mode Selector Tabs (only when creating) --}}
                @if(!$editingId)
                    <div class="flex border-b border-slate-200 bg-slate-50 px-6 pt-3 gap-2 shrink-0">
                        <button type="button" wire:click="$set('uploadMode', 'batch')" 
                                class="px-4 py-2.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2 {{ $uploadMode === 'batch' ? 'border-violet-600 text-violet-700 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                            <i class="fas fa-layer-group"></i>
                            <span>Upload Banyak Foto</span>
                        </button>

                        <button type="button" wire:click="$set('uploadMode', 'single')" 
                                class="px-4 py-2.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2 {{ $uploadMode === 'single' ? 'border-violet-600 text-violet-700 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                            <i class="fas fa-image"></i>
                            <span>Upload 1 Foto</span>
                        </button>
                    </div>
                @endif

                {{-- Form Body --}}
                <div class="p-6 overflow-y-auto space-y-4 text-xs flex-1">
                    @if($uploadMode === 'batch' && !$editingId)
                        {{-- ── MODE BATCH UPLOAD ── --}}
                        {{-- File Input Dropzone --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                Pilih File Foto Sekaligus <span class="text-rose-500">* (Bisa pilih puluhan foto)</span>
                            </label>
                            
                            <div class="border-2 border-dashed border-violet-200 hover:border-violet-400 bg-violet-50/30 rounded-2xl p-6 text-center transition-all">
                                <i class="fas fa-cloud-upload-alt text-3xl text-violet-500 mb-2 block"></i>
                                <p class="font-bold text-slate-700 mb-1">Klik untuk memilih atau seret beberapa foto ke sini</p>
                                <p class="text-[11px] text-slate-400 mb-3">Format didukung: JPG, PNG, WEBP (Maks. 10MB per foto, otomatis dikompres)</p>
                                <input type="file" wire:model="batchFotos" multiple accept="image/*" 
                                       class="cursor-pointer file:cursor-pointer file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-violet-600 file:text-white hover:file:bg-violet-700">
                            </div>
                            @error('batchFotos') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            @error('batchFotos.*') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror

                            {{-- Loading State during file upload --}}
                            <div wire:loading wire:target="batchFotos" class="p-3 bg-violet-50 text-violet-700 rounded-xl font-bold text-xs flex items-center gap-2 mt-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Sedang memuat dan memproses foto terpilih...</span>
                            </div>
                        </div>

                        {{-- Previews of Selected Batch Photos --}}
                        @if(!empty($batchFotos))
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-700">Foto Terpilih: <strong>{{ count($batchFotos) }}</strong> foto</span>
                                    <button type="button" wire:click="$set('batchFotos', [])" class="text-rose-600 hover:underline font-bold text-[11px]">
                                        Hapus Semua Pilihan
                                    </button>
                                </div>
                                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2.5 p-3 bg-slate-50 border border-slate-200/80 rounded-2xl max-h-48 overflow-y-auto">
                                    @foreach($batchFotos as $idx => $bf)
                                        <div class="relative aspect-square rounded-xl overflow-hidden bg-slate-200 group border border-slate-300/80">
                                            <img src="{{ $bf->temporaryUrl() }}" class="w-full h-full object-cover">
                                            <button type="button" wire:click="removeBatchFoto({{ $idx }})" 
                                                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] shadow-sm hover:scale-110 transition-transform">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <span class="absolute bottom-1 left-1 px-1.5 py-0.2 rounded bg-slate-900/80 text-white text-[9px] font-bold">
                                                #{{ $idx + 1 }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Nama Kegiatan / Judul Utama --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">
                                Judul Utama / Nama Kegiatan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="batchJudul" 
                                   placeholder="Contoh: Peringatan HUT RI ke-81 / Upacara Bendera Hari Senin"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                            @error('batchJudul') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Skema Penamaan --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Pola Penamaan Judul Tiap Foto</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer {{ $batchNaming === 'numbered' ? 'border-violet-500 bg-violet-50/50 font-bold text-violet-900' : 'border-slate-200 text-slate-600' }}">
                                    <input type="radio" wire:model="batchNaming" value="numbered" class="text-violet-600 focus:ring-0">
                                    <span>[Judul] #1, #2...</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer {{ $batchNaming === 'original' ? 'border-violet-500 bg-violet-50/50 font-bold text-violet-900' : 'border-slate-200 text-slate-600' }}">
                                    <input type="radio" wire:model="batchNaming" value="original" class="text-violet-600 focus:ring-0">
                                    <span>[Judul] - [Nama File]</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer {{ $batchNaming === 'same' ? 'border-violet-500 bg-violet-50/50 font-bold text-violet-900' : 'border-slate-200 text-slate-600' }}">
                                    <input type="radio" wire:model="batchNaming" value="same" class="text-violet-600 focus:ring-0">
                                    <span>Judul Sama Persis</span>
                                </label>
                            </div>
                        </div>

                        {{-- Keterangan Bersama --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Keterangan / Deskripsi Kegiatan (Opsional)</label>
                            <textarea wire:model="batchKeterangan" rows="2" 
                                      placeholder="Penjelasan ringkas mengenai dokumentasi kegiatan ini..."
                                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none resize-none"></textarea>
                            @error('batchKeterangan') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Urutan Awal --}}
                        <div class="w-40">
                            <label class="block font-bold text-slate-700 mb-1">Nomor Urutan Awal</label>
                            <input type="number" wire:model="batchUrutanAwal" min="0" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                        </div>

                    @else
                        {{-- ── MODE SINGLE FOTO (Tambah atau Edit) ── --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">
                                Judul Foto <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="judul" placeholder="Nama kegiatan / judul foto..."
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                            @error('judul') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Keterangan / Deskripsi</label>
                            <textarea wire:model="keterangan" rows="3" placeholder="Deskripsi singkat mengenai foto dokumentasi ini..."
                                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none resize-none"></textarea>
                            @error('keterangan') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Urutan Tampil</label>
                            <input type="number" wire:model="urutan" min="0" class="w-32 px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">
                                Berkas Foto {{ $editingId ? '(Kosongkan jika tidak diganti)' : '*' }}
                            </label>
                            
                            @if($existingFoto)
                                <div class="mb-2 flex items-center gap-3 p-2 bg-slate-50 rounded-xl border border-slate-200">
                                    <img src="{{ asset('storage/' . $existingFoto) }}" class="h-16 w-24 object-cover rounded-lg border">
                                    <span class="text-[11px] text-slate-500">Foto saat ini terpasang.</span>
                                </div>
                            @endif

                            @if($foto)
                                <div class="mb-2 flex items-center gap-3 p-2 bg-violet-50 rounded-xl border border-violet-200">
                                    <img src="{{ $foto->temporaryUrl() }}" class="h-16 w-24 object-cover rounded-lg border">
                                    <span class="text-[11px] text-violet-700 font-bold">Foto baru siap diunggah.</span>
                                </div>
                            @endif

                            <input type="file" wire:model="foto" accept="image/*" 
                                   class="w-full cursor-pointer file:cursor-pointer file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                            @error('foto') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-2 shrink-0">
                    <button type="button" wire:click="$set('showModal', false)" 
                            class="px-4 py-2.5 rounded-xl text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 font-bold transition-colors">
                        Batal
                    </button>
                    
                    <button type="button" wire:click="save" 
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-bold shadow-md shadow-violet-600/20 transition-all flex items-center gap-2">
                        <span wire:loading.remove wire:target="save, batchFotos, foto">
                            @if($uploadMode === 'batch' && !$editingId)
                                Simpan {{ count($batchFotos) > 0 ? count($batchFotos) . ' Foto' : 'Batch' }}
                            @else
                                Simpan Foto
                            @endif
                        </span>
                        <span wire:loading wire:target="save, batchFotos, foto">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL: FULLSCREEN LIGHTBOX PREVIEW                        --}}
    {{-- ======================================================== --}}
    @if($showPreviewModal)
        <div class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4 sm:p-6" wire:click.self="$set('showPreviewModal', false)">
            <div class="relative max-w-4xl w-full bg-slate-900 rounded-3xl overflow-hidden border border-slate-800 shadow-2xl flex flex-col">
                <button wire:click="$set('showPreviewModal', false)" 
                        class="absolute top-3 right-3 z-20 w-10 h-10 rounded-full bg-slate-800/80 hover:bg-rose-600 text-white flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-base"></i>
                </button>

                <div class="max-h-[75vh] flex items-center justify-center bg-black/40 overflow-hidden">
                    <img src="{{ $previewFotoUrl }}" alt="{{ $previewFotoJudul }}" class="max-h-[75vh] w-auto object-contain mx-auto">
                </div>

                <div class="p-5 bg-slate-900 border-t border-slate-800 text-white">
                    <h3 class="font-bold text-base">{{ $previewFotoJudul ?: 'Tanpa Judul' }}</h3>
                    @if($previewFotoKeterangan)
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">{{ $previewFotoKeterangan }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL: CONFIRM DELETE SINGLE                              --}}
    {{-- ======================================================== --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Hapus Foto Galeri?</h3>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">Foto ini akan dihapus dari galeri dokumentasi sekolah.</p>
                <div class="flex items-center justify-center gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button wire:click="delete" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition-all">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL: CONFIRM BATCH DELETE                               --}}
    {{-- ======================================================== --}}
    @if($showBatchDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Hapus {{ count($selectedIds) }} Foto Terpilih?</h3>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">Semua foto yang telah Anda centang akan dihapus sekaligus.</p>
                <div class="flex items-center justify-center gap-3">
                    <button wire:click="$set('showBatchDeleteModal', false)" class="px-4 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button wire:click="deleteBatch" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition-all">
                        Hapus {{ count($selectedIds) }} Foto
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>