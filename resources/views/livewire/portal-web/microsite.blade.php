<div>
    {{-- Header Section --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Kelola Microsite Sekolah & Guru</h1>
            <p class="text-slate-500 text-sm mt-1">Pusat kelola tautan website mikro (Canva, Google Sites, Linktree, S.id) untuk sekolah dan dewan guru.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('beranda.microsite') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-violet-700 bg-violet-50 hover:bg-violet-100 border border-violet-200 transition-colors shadow-xs">
                <i class="fas fa-external-link-alt"></i> Pratinjau Halaman Publik
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-violet-100 text-violet-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-globe"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Microsite Sekolah</p>
                <h3 class="text-2xl font-extrabold text-slate-800">{{ $totalSekolah }} <span class="text-xs font-semibold text-slate-400">Tautan</span></h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Guru Ber-Microsite</p>
                <h3 class="text-2xl font-extrabold text-slate-800">{{ $totalGuruAdaLink }} <span class="text-xs font-semibold text-slate-400">/ {{ $totalGuru }} Guru</span></h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-percentage"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Persentase Guru</p>
                <h3 class="text-2xl font-extrabold text-slate-800">
                    {{ $totalGuru > 0 ? round(($totalGuruAdaLink / $totalGuru) * 100) : 0 }}%
                    <span class="text-xs font-semibold text-slate-400">Tersambung</span>
                </h3>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="flex items-center gap-2 border-b border-slate-200 mb-6">
        <button wire:click="setTab('sekolah')" 
                class="px-5 py-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2 {{ $activeTab === 'sekolah' ? 'border-violet-600 text-violet-700 bg-violet-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i class="fas fa-school"></i>
            <span>Microsite Resmi Sekolah</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-extrabold {{ $activeTab === 'sekolah' ? 'bg-violet-200/80 text-violet-800' : 'bg-slate-100 text-slate-600' }}">{{ $totalSekolah }}</span>
        </button>

        <button wire:click="setTab('guru')" 
                class="px-5 py-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2 {{ $activeTab === 'guru' ? 'border-violet-600 text-violet-700 bg-violet-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i class="fas fa-user-graduate"></i>
            <span>Direktori Microsite Guru</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-extrabold {{ $activeTab === 'guru' ? 'bg-violet-200/80 text-violet-800' : 'bg-slate-100 text-slate-600' }}">{{ $totalGuruAdaLink }} / {{ $totalGuru }}</span>
        </button>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 1: MICROSITE SEKOLAH                                  --}}
    {{-- ======================================================== --}}
    @if($activeTab === 'sekolah')
        @if (session()->has('success_sekolah'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-base"></i>
                <span>{{ session('success_sekolah') }}</span>
            </div>
        @endif

        {{-- Toolbar --}}
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" wire:model.live.debounce.300ms="searchSekolah" 
                       placeholder="Cari judul, kategori, atau deskripsi..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:bg-white focus:border-violet-500 transition-colors">
            </div>

            <button wire:click="openCreateSekolah" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-md shadow-violet-600/20 transition-all">
                <i class="fas fa-plus"></i>
                <span>Tambah Microsite Sekolah</span>
            </button>
        </div>

        {{-- Table Sekolah --}}
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5 text-center w-16">Urutan</th>
                            <th class="px-5 py-3.5">Microsite Sekolah</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Tautan URL</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($micrositesSekolah as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-4 text-center font-bold text-slate-500">
                                    {{ $item->urutan }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center shrink-0 text-sm mt-0.5">
                                            <i class="{{ $item->icon ?: 'fas fa-globe' }}"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 text-sm">{{ $item->judul }}</h4>
                                            @if($item->deskripsi)
                                                <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $item->deskripsi }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $item->kategori ?: 'Utama' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer" 
                                       class="inline-flex items-center gap-1.5 text-violet-600 hover:text-violet-800 font-bold hover:underline max-w-xs truncate">
                                        <span class="truncate">{{ $item->url }}</span>
                                        <i class="fas fa-external-link-alt text-[10px]"></i>
                                    </a>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <button wire:click="toggleActiveSekolah({{ $item->id }})" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold cursor-pointer transition-colors {{ $item->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $item->is_active ? 'Aktif' : 'Draft' }}
                                    </button>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="openEditSekolah({{ $item->id }})" 
                                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-violet-100 text-slate-600 hover:text-violet-700 flex items-center justify-center transition-colors" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDeleteSekolah({{ $item->id }})" 
                                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-600 flex items-center justify-center transition-colors" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fas fa-globe text-3xl mb-2 text-slate-300 block"></i>
                                    <p class="font-bold text-slate-600 text-sm">Belum ada microsite sekolah yang ditambahkan.</p>
                                    <p class="text-xs text-slate-400 mt-1">Klik tombol "Tambah Microsite Sekolah" untuk menambahkan tautan Canva, Google Sites, dll.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($micrositesSekolah->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $micrositesSekolah->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- TAB 2: MICROSITE GURU                                     --}}
    {{-- ======================================================== --}}
    @if($activeTab === 'guru')
        @if (session()->has('success_guru'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-base"></i>
                <span>{{ session('success_guru') }}</span>
            </div>
        @endif

        {{-- Toolbar Guru --}}
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-80">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="searchGuru" 
                           placeholder="Cari nama guru atau NIP..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:bg-white focus:border-violet-500 transition-colors">
                </div>

                <div class="flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-slate-200">
                    <button wire:click="$set('filterStatusGuru', 'semua')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $filterStatusGuru === 'semua' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        Semua Guru
                    </button>
                    <button wire:click="$set('filterStatusGuru', 'ada_link')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $filterStatusGuru === 'ada_link' ? 'bg-emerald-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        Ada Microsite
                    </button>
                    <button wire:click="$set('filterStatusGuru', 'belum_ada')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $filterStatusGuru === 'belum_ada' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        Belum Ada
                    </button>
                </div>
            </div>

            <span class="text-xs text-slate-500 font-medium">
                Menampilkan <strong>{{ $gurus->total() }}</strong> dewan guru
            </span>
        </div>

        {{-- Table Guru --}}
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Guru & NIP</th>
                            <th class="px-5 py-3.5">Penugasan / Mapel</th>
                            <th class="px-5 py-3.5">Tautan Microsite Guru</th>
                            <th class="px-5 py-3.5 text-center w-28">Status</th>
                            <th class="px-5 py-3.5 text-center w-36">Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($gurus as $guru)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 border border-slate-200 shrink-0">
                                            @if($guru->photo_path)
                                                <img src="{{ asset('storage/' . $guru->photo_path) }}" alt="{{ $guru->name }}" class="w-full h-full object-cover">
                                            @else
                                                <img src="{{ $guru->jenis_kelamin === 'P' ? asset('images/avatar-f.svg') : asset('images/avatar-m.svg') }}" alt="{{ $guru->name }}" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 text-sm">{{ $guru->name }}</h4>
                                            <span class="text-[11px] text-slate-400">NIP: {{ $guru->nip ?: '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $mapel = $guru->mapel_aktif;
                                    @endphp
                                    @if(!empty($mapel))
                                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ implode(', ', array_slice($mapel, 0, 2)) }}{{ count($mapel) > 2 ? '...' : '' }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum ada mapel</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($guru->microsite_url)
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $guru->microsite_url }}" target="_blank" rel="noopener noreferrer" 
                                               class="inline-flex items-center gap-1.5 text-violet-600 hover:text-violet-800 font-bold hover:underline max-w-xs truncate">
                                                <i class="fas fa-link text-[10px]"></i>
                                                <span class="truncate">{{ $guru->microsite_url }}</span>
                                                <i class="fas fa-external-link-alt text-[9px]"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum diisi oleh guru</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($guru->microsite_url)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            <i class="fas fa-check text-[10px]"></i> Tersedia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500">
                                            Kosong
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button wire:click="openEditGuru('{{ $guru->id }}')" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-violet-50 hover:bg-violet-100 text-violet-700 font-bold text-xs transition-colors">
                                            <i class="fas fa-pen text-[10px]"></i>
                                            <span>{{ $guru->microsite_url ? 'Edit' : 'Input' }}</span>
                                        </button>
                                        @if($guru->microsite_url)
                                            <button wire:click="clearGuruMicrosite('{{ $guru->id }}')" 
                                                    wire:confirm="Yakin ingin mengosongkan tautan microsite untuk guru ini?"
                                                    class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 transition-colors" title="Kosongkan Tautan">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fas fa-users-slash text-3xl mb-2 text-slate-300 block"></i>
                                    <p class="font-bold text-slate-600 text-sm">Tidak ada data guru yang cocok dengan pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($gurus->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $gurus->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL: FORM MICROSITE SEKOLAH                             --}}
    {{-- ======================================================== --}}
    @if($showModalSekolah)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold flex items-center gap-2">
                        <i class="fas fa-globe"></i>
                        <span>{{ $editingSekolahId ? 'Edit Microsite Sekolah' : 'Tambah Microsite Sekolah' }}</span>
                    </h3>
                    <button wire:click="$set('showModalSekolah', false)" class="text-white/80 hover:text-white text-lg">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="saveSekolah" class="p-6 space-y-4 text-xs">
                    {{-- Judul --}}
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Judul Microsite <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="judul" placeholder="Contoh: Modul Digital Kurikulum Merdeka / Canva Profil Sekolah"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                        @error('judul') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- URL --}}
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tautan URL <span class="text-rose-500">*</span></label>
                        <input type="url" wire:model="url" placeholder="https://sites.google.com/view/... atau https://canva.com/..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                        @error('url') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                        <textarea wire:model="deskripsi" rows="3" placeholder="Jelaskan isi atau peruntukan microsite ini..."
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none"></textarea>
                        @error('deskripsi') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        {{-- Kategori --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kategori</label>
                            <input type="text" wire:model="kategori" placeholder="Utama / Pembelajaran / PPDB"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                        </div>

                        {{-- Ikon FontAwesome --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Ikon (FontAwesome)</label>
                            <div class="relative">
                                <input type="text" wire:model="icon" placeholder="fas fa-globe"
                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                                <i class="{{ $icon ?: 'fas fa-globe' }} absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        {{-- Button Text --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Teks Tombol</label>
                            <input type="text" wire:model="button_text" placeholder="Kunjungi Microsite"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                        </div>

                        {{-- Urutan --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Urutan Tampil</label>
                            <input type="number" wire:model="urutan" min="0"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                        </div>
                    </div>

                    {{-- Toggle Aktif --}}
                    <div class="pt-2 flex items-center gap-3">
                        <input type="checkbox" id="sekolah_is_active" wire:model="is_active" 
                               class="w-4 h-4 text-violet-600 rounded border-slate-300 focus:ring-violet-500 cursor-pointer">
                        <label for="sekolah_is_active" class="font-bold text-slate-700 cursor-pointer">
                            Tampilkan di Halaman Publik (Aktif)
                        </label>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" wire:click="$set('showModalSekolah', false)" 
                                class="px-4 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold shadow-md shadow-violet-600/20 transition-all flex items-center gap-2">
                            <span wire:loading.remove wire:target="saveSekolah">Simpan Microsite</span>
                            <span wire:loading wire:target="saveSekolah">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL: CONFIRM DELETE SEKOLAH                             --}}
    {{-- ======================================================== --}}
    @if($showDeleteModalSekolah)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Hapus Microsite Sekolah?</h3>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">Tautan microsite ini akan dihapus permanen dan tidak akan ditampilkan lagi di website utama.</p>
                <div class="flex items-center justify-center gap-3">
                    <button wire:click="$set('showDeleteModalSekolah', false)" 
                            class="px-4 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button wire:click="deleteSekolah" 
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition-all">
                        Ya, Hapus Sekarang
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL: QUICK EDIT MICROSITE GURU                          --}}
    {{-- ======================================================== --}}
    @if($showModalGuru)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold flex items-center gap-2">
                        <i class="fas fa-user-edit"></i>
                        <span>Input / Edit Microsite Guru</span>
                    </h3>
                    <button wire:click="$set('showModalGuru', false)" class="text-white/80 hover:text-white text-lg">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="saveGuruMicrosite" class="p-6 space-y-4 text-xs">
                    {{-- Guru Info Card --}}
                    <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Guru Terpilih</span>
                            <h4 class="text-sm font-bold text-slate-800">{{ $editingGuruNama }}</h4>
                            <p class="text-[11px] text-slate-500">NIP: {{ $editingGuruNip }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-violet-100 text-violet-700 border border-violet-200">
                            Pendidik
                        </span>
                    </div>

                    {{-- URL Field --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-bold text-slate-700">Tautan Microsite Guru <span class="text-rose-500">*</span></label>
                            @if($editingGuruMicrositeUrl)
                                <a href="{{ $editingGuruMicrositeUrl }}" target="_blank" rel="noopener noreferrer" 
                                   class="text-[11px] font-bold text-violet-600 hover:underline flex items-center gap-1">
                                    <span>Uji Coba Tautan</span>
                                    <i class="fas fa-external-link-alt text-[9px]"></i>
                                </a>
                            @endif
                        </div>
                        <input type="url" wire:model.live="editingGuruMicrositeUrl" 
                               placeholder="https://sites.google.com/view/... atau https://canva.com/..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-violet-500 rounded-xl font-medium outline-none">
                        @error('editingGuruMicrositeUrl') <span class="text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        <p class="text-[10px] text-slate-400 mt-1.5 leading-relaxed">
                            Bisa berupa Google Sites materi ajar, presentasi Canva, Linktree, portofolio Notion, atau S.id. Kosongkan jika ingin menghapus tautan.
                        </p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" wire:click="$set('showModalGuru', false)" 
                                class="px-4 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold shadow-md shadow-violet-600/20 transition-all flex items-center gap-2">
                            <span wire:loading.remove wire:target="saveGuruMicrosite">Simpan Tautan Guru</span>
                            <span wire:loading wire:target="saveGuruMicrosite">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
