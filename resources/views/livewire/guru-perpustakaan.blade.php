<div class="space-y-6 max-w-7xl mx-auto p-4 lg:p-8">
    
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-brand-primary to-indigo-600 rounded-3xl p-8 text-white shadow-lg shadow-brand-primary/20 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row justify-between md:items-end gap-6">
            <div>
                <h1 class="text-3xl font-extrabold mb-2">Perpustakaan</h1>
                <p class="text-indigo-100 max-w-2xl text-lg">Cari buku, pantau pinjaman pribadi, dan pantau riwayat pinjaman siswa di kelas yang Anda ampu.</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/20 font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                Portal Guru
            </div>
        </div>
        
        <!-- Decorative shapes -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-10 blur-2xl"></div>
        <div class="absolute bottom-0 right-32 -mb-16 w-48 h-48 rounded-full bg-indigo-400 opacity-20 blur-xl"></div>
    </div>

    <!-- Main Content Area -->
    <div x-data="{ tab: @entangle('activeTab') }">
        
        <!-- Navigation Tabs -->
        <div class="flex flex-wrap gap-3 mb-8">
            <button wire:click="setTab('katalog')" 
                    class="px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 {{ $activeTab === 'katalog' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/30' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-brand-primary border border-slate-200' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                Katalog Buku
            </button>
            <button wire:click="setTab('peminjaman')" 
                    class="px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 {{ $activeTab === 'peminjaman' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/30' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-brand-primary border border-slate-200' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                Pinjaman Pribadi
            </button>
            <button wire:click="setTab('riwayat')" 
                    class="px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 {{ $activeTab === 'riwayat' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/30' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-brand-primary border border-slate-200' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Riwayat Pribadi
            </button>
            <button wire:click="setTab('kunjungan')" 
                    class="px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 {{ $activeTab === 'kunjungan' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/30' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-brand-primary border border-slate-200' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Riwayat Kunjungan
            </button>
            <button wire:click="setTab('siswa')" 
                    class="px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 {{ $activeTab === 'siswa' ? 'bg-teal-600 text-white shadow-lg shadow-teal-600/30' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-teal-600 border border-slate-200' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Peminjaman Siswa
            </button>
        </div>

        @if($activeTab === 'katalog')
            <!-- Panel Pencarian Buku -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8 p-6 lg:p-8">
                <!-- Search Bar and Sort Options -->
                <div class="max-w-3xl mx-auto mb-10 flex flex-col md:flex-row gap-4">
                    <div class="relative group flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-brand-primary transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-12 pr-4 py-4 bg-slate-50 border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all shadow-sm" placeholder="Cari judul buku, penulis, atau ISBN...">
                        <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <svg class="animate-spin h-5 w-5 text-brand-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-56 shrink-0 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" /></svg>
                        </div>
                        <select wire:model.live="sortBy" class="block w-full pl-11 pr-10 py-4 bg-slate-50 border-slate-200 rounded-2xl text-slate-900 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all shadow-sm font-medium appearance-none cursor-pointer">
                            <option value="terbaru">Buku Terbaru</option>
                            <option value="populer">Sering Dipinjam</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                <!-- Hasil Pencarian -->
                @if($bukus->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4 xl:gap-5">
                        @foreach($bukus as $buku)
                            <div class="flex flex-col bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg hover:border-indigo-500/30 transition-all duration-300 group">
                                <div class="h-44 sm:h-52 bg-slate-50 relative overflow-hidden flex items-center justify-center border-b border-slate-100">
                                    @if($buku->sampul_buku)
                                        <img src="{{ asset('storage/' . $buku->sampul_buku) }}" alt="{{ $buku->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                    @endif
                                </div>
                                <div class="p-4 sm:p-5 flex-1 flex flex-col">
                                    <div class="flex flex-wrap justify-between items-start gap-1.5 mb-3">
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $buku->dipinjam_oleh_saya ? 'bg-amber-100 text-amber-700' : ($buku->eksemplar_tersedia_count > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700') }}">
                                            @if($buku->dipinjam_oleh_saya)
                                                <span class="w-2 h-2 rounded-full bg-amber-500 mr-1.5"></span>
                                                Sedang Anda Pinjam
                                            @elseif($buku->eksemplar_tersedia_count > 0)
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>
                                                Tersedia ({{ $buku->eksemplar_tersedia_count }})
                                            @else
                                                <span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5"></span>
                                                Habis Dipinjam
                                            @endif
                                        </div>
                                        @if($buku->kategoriBuku)
                                            <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-md">{{ $buku->kategoriBuku->nama_kategori }}</span>
                                        @endif
                                    </div>
                                    
                                    <h3 class="text-lg font-bold text-slate-900 mb-1 line-clamp-2 group-hover:text-brand-primary transition-colors">{{ $buku->judul }}</h3>
                                    <p class="text-sm text-slate-500 mb-4">{{ $buku->penulis ?? 'Penulis Tidak Diketahui' }}</p>
                                    
                                    <div class="mt-auto pt-4 border-t border-slate-100">
                                        @if($buku->dipinjam_oleh_saya)
                                            <div class="flex items-start gap-2 text-sm text-amber-700 bg-amber-50/50 p-3 rounded-xl">
                                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <div class="flex flex-col">
                                                    <span>Buku ini sedang Anda pinjam.</span>
                                                    @if($buku->earliest_return_date)
                                                        <span class="font-medium mt-0.5">Batas Kembali: {{ $buku->earliest_return_date }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($buku->eksemplar_tersedia_count > 0)
                                            <div class="flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50/50 p-3 rounded-xl">
                                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                <span>Berada di perpustakaan, <strong>Rak: {{ $buku->lokasi_rak ?? 'Tidak ditentukan' }}</strong></span>
                                            </div>
                                        @else
                                            <div class="flex items-start gap-2 text-sm text-rose-700 bg-rose-50/50 p-3 rounded-xl">
                                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <div class="flex flex-col">
                                                    <span>Sedang dipinjam oleh pihak lain.</span>
                                                    @if($buku->earliest_return_date)
                                                        <span class="font-medium mt-0.5">Akan tersedia sekitar: {{ $buku->earliest_return_date }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-8">
                        {{ $bukus->links('vendor.livewire.custom-pagination') }}
                    </div>
                @else
                    <div class="text-center py-16 bg-slate-50/50 rounded-2xl border border-dashed border-slate-300">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-1">Buku tidak ditemukan</h3>
                        <p class="text-slate-500">Coba gunakan kata kunci pencarian yang lain.</p>
                    </div>
                @endif
            </div>
            
        @elseif($activeTab === 'peminjaman')
            <!-- Panel Sedang Dipinjam Pribadi -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800">Buku Yang Sedang Anda Pinjam</h2>
                    <p class="text-sm text-slate-500 mt-1">Daftar buku perpustakaan yang saat ini masih Anda pinjam.</p>
                </div>
                
                <div class="overflow-x-auto [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Buku & Eksemplar</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Tgl Pinjam</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Batas Kembali</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($peminjamanAktif as $pinjam)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900">{{ $pinjam->eksemplarBuku->buku->judul ?? 'Judul Tidak Diketahui' }}</span>
                                            <span class="text-xs text-slate-500 mt-1 font-mono bg-slate-100 inline-block w-fit px-2 py-0.5 rounded">
                                                {{ $pinjam->eksemplarBuku->kode_eksemplar ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            <span class="text-sm font-medium text-slate-700">{{ $pinjam->tanggal_pinjam ? $pinjam->tanggal_pinjam->format('d M Y') : '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $isTerlambat = $pinjam->tanggal_jatuh_tempo && now()->startOfDay()->gt($pinjam->tanggal_jatuh_tempo->startOfDay());
                                            @endphp
                                            <svg class="w-4 h-4 {{ $isTerlambat ? 'text-rose-500' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span class="text-sm font-medium {{ $isTerlambat ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                                {{ $pinjam->tanggal_jatuh_tempo ? $pinjam->tanggal_jatuh_tempo->format('d M Y') : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @if($isTerlambat)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                                <span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5 animate-pulse"></span>
                                                Terlambat
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 mr-1.5"></span>
                                                Sedang Dipinjam
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-500">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                        </div>
                                        <p>Anda saat ini tidak sedang meminjam buku perpustakaan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($peminjamanAktif->hasPages())
                    <div class="mt-6">
                        {{ $peminjamanAktif->links('vendor.livewire.custom-pagination') }}
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'riwayat')
            <!-- Panel Riwayat Peminjaman Pribadi -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800">Riwayat Peminjaman Pribadi Anda</h2>
                    <p class="text-sm text-slate-500 mt-1">Daftar buku yang sudah pernah Anda pinjam dan kembalikan sebelumnya.</p>
                </div>
                
                <div class="overflow-x-auto [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Buku & Eksemplar</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Tgl Pinjam</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status & Tgl Kembali</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($riwayatPeminjaman as $pinjam)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900">{{ $pinjam->eksemplarBuku->buku->judul ?? 'Judul Tidak Diketahui' }}</span>
                                            <span class="text-xs text-slate-500 mt-1 font-mono bg-slate-100 inline-block w-fit px-2 py-0.5 rounded">
                                                {{ $pinjam->eksemplarBuku->kode_eksemplar ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            <span class="text-sm font-medium text-slate-700">{{ $pinjam->tanggal_pinjam ? $pinjam->tanggal_pinjam->format('d M Y') : '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($pinjam->status === 'dikembalikan')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>
                                                Dikembalikan
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-1">
                                                Pada: {{ $pinjam->tanggal_kembali ? $pinjam->tanggal_kembali->format('d M Y') : '-' }}
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ ucfirst($pinjam->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-12 text-center text-slate-500">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <p>Anda belum memiliki riwayat peminjaman perpustakaan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($riwayatPeminjaman->hasPages())
                    <div class="mt-6">
                        {{ $riwayatPeminjaman->links('vendor.livewire.custom-pagination') }}
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'kunjungan')
            <!-- Panel Riwayat Kunjungan Pribadi -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800">Riwayat Kunjungan Pribadi Anda</h2>
                    <p class="text-sm text-slate-500 mt-1">Daftar riwayat kunjungan fisik Anda ke perpustakaan.</p>
                </div>
                
                <div class="overflow-x-auto [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal & Waktu</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Tujuan Kunjungan</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($riwayatKunjungan as $kunjungan)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900">{{ $kunjungan->tanggal ? $kunjungan->tanggal->format('d M Y') : '-' }}</span>
                                            <span class="text-sm text-slate-500 mt-1 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ $kunjungan->waktu_masuk ? substr($kunjungan->waktu_masuk, 0, 5) : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                            {{ ucwords(str_replace('_', ' ', $kunjungan->tujuan_kunjungan)) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-sm text-slate-600">{{ $kunjungan->catatan ?? '-' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-12 text-center text-slate-500">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        </div>
                                        <p>Anda belum memiliki riwayat kunjungan perpustakaan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($riwayatKunjungan->hasPages())
                    <div class="mt-6">
                        {{ $riwayatKunjungan->links('vendor.livewire.custom-pagination') }}
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'siswa')
            <!-- Panel Riwayat Siswa (Khusus Kelas Ampu) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-200 bg-teal-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-teal-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Riwayat Peminjaman Siswa
                        </h2>
                        <p class="text-sm text-teal-700/70 mt-1">Pantau peminjaman buku oleh siswa dari kelas yang Anda ampu.</p>
                    </div>

                    @if(count($kelasAmpu) > 0)
                        <div class="w-full md:w-64 relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-teal-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <select wire:model.live="selectedKelasId" class="block w-full pl-11 pr-10 py-3 bg-white border-teal-200 rounded-2xl text-teal-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm font-bold appearance-none cursor-pointer">
                                @foreach($kelasAmpu as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-teal-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    @endif
                </div>
                
                @if(count($kelasAmpu) > 0)
                    <div class="overflow-x-auto [touch-action:pan-x_pan-y] [-webkit-overflow-scrolling:touch]">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Buku</th>
                                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu Peminjaman</th>
                                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($peminjamanSiswa as $pinjam)
                                    @php
                                        $isTerlambat = $pinjam->status === 'dipinjam' && $pinjam->tanggal_jatuh_tempo && now()->startOfDay()->gt($pinjam->tanggal_jatuh_tempo->startOfDay());
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors {{ $isTerlambat ? 'bg-rose-50/30' : '' }}">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-brand-primary flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                                                    {{ substr($pinjam->peminjam->name ?? 'S', 0, 2) }}
                                                </div>
                                                <span class="font-bold text-slate-800">{{ $pinjam->peminjam->name ?? 'Siswa Tidak Diketahui' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-slate-700 line-clamp-1" title="{{ $pinjam->eksemplarBuku->buku->judul ?? '-' }}">
                                                    {{ $pinjam->eksemplarBuku->buku->judul ?? '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex flex-col text-sm">
                                                <span class="text-slate-600"><span class="font-medium">Pinjam:</span> {{ $pinjam->tanggal_pinjam ? $pinjam->tanggal_pinjam->format('d M Y') : '-' }}</span>
                                                <span class="text-slate-600"><span class="font-medium">Jatuh Tempo:</span> {{ $pinjam->tanggal_jatuh_tempo ? $pinjam->tanggal_jatuh_tempo->format('d M Y') : '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            @if($pinjam->status === 'dipinjam')
                                                @if($isTerlambat)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                                        <span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5 animate-pulse"></span>
                                                        Terlambat
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                                        <span class="w-2 h-2 rounded-full bg-amber-500 mr-1.5"></span>
                                                        Dipinjam
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-slate-500">
                                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                            </div>
                                            <p>Tidak ada riwayat peminjaman siswa untuk kelas ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($peminjamanSiswa->hasPages())
                        <div class="mt-6">
                            {{ $peminjamanSiswa->links('vendor.livewire.custom-pagination') }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center text-slate-500">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak Ada Kelas yang Diampu</h3>
                        <p>Anda saat ini belum mengampu mata pelajaran atau ditugaskan sebagai wali kelas di periode aktif ini.</p>
                    </div>
                @endif
            </div>

        @endif
    </div>
</div>
