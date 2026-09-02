<div class="min-h-screen bg-slate-50 flex flex-col font-jakarta" x-data="{ showPdfModal: false, pdfTitle: '', pdfUrl: '', readerUrl: '' }">

    <!-- ====================== HEADER / NAVBAR MODERN ====================== -->
    <x-public-dashboard.navbar :pengaturanSekolah="$pengaturanSekolah" />

    <!-- ====================== HERO SECTION MODERN ====================== -->
    <div class="relative overflow-hidden min-h-[70vh] flex items-center">
        <!-- Background Photo -->
        <div class="absolute inset-0">
            @if($pengaturanSekolah && $pengaturanSekolah->hero_image_path)
                <img src="{{ asset('storage/' . $pengaturanSekolah->hero_image_path) }}"
                    alt="School Background" class="w-full h-full object-cover object-top scale-105">
            @else
                <img src="{{ asset('hero-bg-school.png') }}"
                    alt="School Background" class="w-full h-full object-cover object-top scale-105">
            @endif
            <!-- Dark gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/70 to-black/40"></div>
            <!-- Warna accent di atas overlay -->
            <div class="absolute inset-0 bg-gradient-to-tr from-brand-primary-950/60 via-transparent to-brand-secondary-950/30"></div>
        </div>
        <!-- Subtle animated color blobs -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-20 -left-20 w-96 h-96 rounded-full bg-brand-primary/20 blur-3xl animate-[moveblob1_15s_ease-in-out_infinite]"></div>
            <div class="absolute -bottom-20 right-0 w-80 h-80 rounded-full bg-brand-secondary/15 blur-3xl animate-[moveblob2_18s_ease-in-out_infinite]"></div>
        </div>

        <!-- Hero Content -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-20">
            <div class="max-w-3xl" x-data="{ 
                now: new Date(), 
                init() { setInterval(() => { this.now = new Date(); }, 1000); },
                get formattedTime() { return this.now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }); },
                get formattedDate() { return this.now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }); }
            }">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 backdrop-blur-sm text-brand-primary-light text-sm font-semibold px-4 py-2 rounded-full mb-8 shadow-lg">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    Katalog diperbarui secara real-time
                </div>

                <!-- Main Title -->
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.1] tracking-tight mb-6">
                    <span class="bg-gradient-to-r from-brand-primary-light via-brand-secondary-light to-brand-cyan bg-clip-text text-transparent">
                        Perpustakaan
                    </span>
                    <span class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-200 mt-2 block">
                        {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'SMPN 1 Majenang' }}
                    </span>
                </h1>

                <p class="text-lg text-slate-400 mb-10 max-w-xl leading-relaxed">
                    Eksplorasi koleksi buku digital dan fisik kami. Temukan referensi belajar terbaik untuk mendukung pendidikan Anda di sekolah.
                </p>

                <!-- Live Info Pills -->
                <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                    <div class="flex items-center gap-3 bg-white/5 border border-white/10 backdrop-blur-sm px-4 sm:px-5 py-3 rounded-2xl w-full sm:w-auto justify-between sm:justify-start">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-brand-primary-light flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold leading-none mb-0.5">Waktu Sekarang</p>
                                <span x-text="formattedTime" class="font-mono text-base sm:text-lg font-bold text-white tabular-nums tracking-wider leading-none"></span>
                            </div>
                        </div>
                        <div class="w-px h-8 bg-white/10 mx-1 sm:mx-2 hidden sm:block"></div>
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-brand-secondary-light flex-shrink-0 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <div class="text-right sm:text-left">
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold leading-none mb-0.5">Tanggal</p>
                                <span x-text="formattedDate" class="text-sm font-semibold text-white leading-none"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative Side Orb (Desktop Only) -->
        <div class="hidden lg:block absolute right-0 top-0 bottom-0 w-1/3 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-l from-brand-primary-950/20 to-transparent"></div>
            <div class="absolute top-1/2 right-20 -translate-y-1/2 w-72 h-72 rounded-full bg-gradient-to-br from-brand-primary/20 to-brand-secondary/20 blur-2xl"></div>
            <div class="absolute top-1/2 right-32 -translate-y-1/2 w-48 h-48 rounded-full bg-gradient-to-br from-brand-info-light/10 to-brand-cyan/10 blur-xl animate-[spin_20s_linear_infinite]"></div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">

        <!-- ====================== KARTU STATISTIK ====================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            <!-- Total Judul Buku -->
            <div class="relative bg-gradient-to-br from-brand-primary to-brand-primary-dark rounded-2xl p-5 shadow-lg shadow-brand-primary/20 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-brand-primary-100 uppercase tracking-widest mb-2">Total Judul Buku</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ number_format($stats['total_judul']) }}</span>
                    <span class="text-sm font-medium text-brand-primary-200">Judul</span>
                </div>
                <div class="mt-2 flex items-center gap-1 text-brand-primary-100 text-xs"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg> Berbagai kategori</div>
            </div>

            <!-- Total Eksemplar -->
            <div class="relative bg-gradient-to-br from-brand-secondary to-brand-secondary-dark rounded-2xl p-5 shadow-lg shadow-brand-secondary/20 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-brand-secondary-100 uppercase tracking-widest mb-2">Total Eksemplar</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ number_format($stats['total_eksemplar']) }}</span>
                    <span class="text-sm font-medium text-brand-secondary-200">Buku Fisik</span>
                </div>
                <div class="mt-2 text-brand-secondary-100 text-xs">Total inventaris perpustakaan</div>
            </div>

            <!-- Eksemplar Tersedia -->
            <div class="relative bg-gradient-to-br from-brand-accent to-brand-accent-dark rounded-2xl p-5 shadow-lg shadow-brand-accent/20 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-brand-accent-50 uppercase tracking-widest mb-2 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Tersedia
                </p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ number_format($stats['eksemplar_tersedia']) }}</span>
                    <span class="text-sm font-medium text-brand-accent-100">Buku Fisik</span>
                </div>
                <div class="mt-2 text-brand-accent-50 text-xs">Siap untuk dipinjam</div>
            </div>

            <!-- Buku Dipinjam -->
            <div class="relative bg-gradient-to-br from-brand-warning to-brand-warning-dark rounded-2xl p-5 shadow-lg shadow-brand-warning/20 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-brand-warning-50 uppercase tracking-widest mb-2">Sedang Dipinjam</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ number_format($stats['buku_dipinjam']) }}</span>
                    <span class="text-sm font-medium text-brand-warning-100">Buku Fisik</span>
                </div>
                <div class="mt-2 text-brand-warning-50 text-xs">Sirkulasi aktif</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10">
            <!-- ====================== SIDEBAR KIRI ====================== -->
            <div class="flex flex-col gap-6 lg:col-span-1">
                <!-- ====================== KATEGORI TERPOPULER ====================== -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 flex items-center justify-center bg-brand-warning-50 rounded-2xl text-brand-warning-dark flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 leading-tight">Kategori Populer</h3>
                        <p class="text-xs text-slate-400">Koleksi terbanyak</p>
                    </div>
                </div>
                <div class="flex-1 flex flex-col justify-center space-y-3">
                    @foreach($kategoriPopuler as $index => $kat)
                    @php
                        $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
                        $barColors = ['bg-brand-warning','bg-slate-400','bg-orange-400','bg-brand-primary-light','bg-brand-info-light'];
                        $textColors = ['text-brand-warning-dark','text-slate-600','text-orange-600','text-brand-primary','text-brand-info'];
                        $percentage = $stats['total_judul'] > 0 ? min(100, round(($kat->bukus_count / $stats['total_judul']) * 100)) : 0;
                    @endphp
                    <div class="group">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-lg leading-none">{{ $medals[$index] ?? '•' }}</span>
                                <span class="text-sm font-bold text-slate-700 truncate max-w-[120px]" title="{{ $kat->nama_kategori }}">{{ $kat->nama_kategori }}</span>
                            </div>
                            <span class="text-sm font-extrabold {{ $textColors[$index] ?? 'text-slate-500' }}">{{ $kat->bukus_count }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="{{ $barColors[$index] ?? 'bg-slate-300' }} h-2.5 rounded-full transition-all duration-700 ease-out"
                                style="width: {{ max(5, $percentage) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                    @if($kategoriPopuler->isEmpty())
                        <div class="text-center text-slate-400 py-8">
                            <p class="text-sm">Belum ada data kategori.</p>
                        </div>
                    @endif
                </div>
                </div>

                <!-- ====================== PENGUNJUNG HARI INI ====================== -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col">
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 flex items-center justify-center bg-brand-info-50 rounded-2xl text-brand-info-dark flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 leading-tight">Pengunjung Hari Ini</h3>
                            <p class="text-xs text-slate-400">Paling baru</p>
                        </div>
                    </div>
                    <div class="flex-1 flex flex-col justify-center space-y-4">
                        @foreach(isset($pengunjungHariIni) ? $pengunjungHariIni : [] as $kunjungan)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 border border-slate-200">
                                @if($kunjungan->pengunjung_type === 'siswa' && $kunjungan->pengunjung?->photo_path)
                                    <img src="{{ asset('storage/' . $kunjungan->pengunjung->photo_path) }}" alt="Foto" class="w-full h-full object-cover rounded-full">
                                @else
                                    <span class="text-sm font-bold text-slate-400">
                                        {{ strtoupper(substr($kunjungan->pengunjung?->name ?? '?', 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate" title="{{ $kunjungan->pengunjung?->name ?? 'Tidak diketahui' }}">
                                    {{ $kunjungan->pengunjung?->name ?? 'Tidak diketahui' }}
                                </p>
                                <p class="text-[10px] text-slate-400 font-medium truncate">
                                    Pukul {{ \Carbon\Carbon::parse($kunjungan->waktu_masuk)->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                        @if(!isset($pengunjungHariIni) || $pengunjungHariIni->isEmpty())
                            <div class="text-center text-slate-400 py-4">
                                <p class="text-sm">Belum ada kunjungan hari ini.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ====================== KELAS PENGUNJUNG TERBANYAK ====================== -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col">
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 flex items-center justify-center bg-brand-primary-50 rounded-2xl text-brand-primary-dark flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 leading-tight">Kelas Teraktif</h3>
                            <p class="text-xs text-slate-400">Pengunjung terbanyak</p>
                        </div>
                    </div>
                    <div class="flex-1 flex flex-col justify-center space-y-3">
                        @foreach(isset($kelasPopuler) ? $kelasPopuler : [] as $index => $kelas)
                        @php
                            $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
                            $barColors = ['bg-brand-warning','bg-slate-400','bg-orange-400','bg-brand-primary-light','bg-brand-info-light'];
                            $textColors = ['text-brand-warning-dark','text-slate-600','text-orange-600','text-brand-primary','text-brand-info'];
                            $maxKunjungan = isset($kelasPopuler) && count($kelasPopuler) > 0 ? $kelasPopuler[0]->total_kunjungan : 1;
                            $percentage = $maxKunjungan > 0 ? min(100, round(($kelas->total_kunjungan / $maxKunjungan) * 100)) : 0;
                        @endphp
                        <div class="group">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg leading-none">{{ $medals[$index] ?? '•' }}</span>
                                    <span class="text-sm font-bold text-slate-700 truncate max-w-[120px]" title="{{ $kelas->nama_kelas }}">{{ $kelas->nama_kelas }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-extrabold {{ $textColors[$index] ?? 'text-slate-500' }}">{{ $kelas->total_kunjungan }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium">kunjungan</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div class="{{ $barColors[$index] ?? 'bg-slate-300' }} h-2.5 rounded-full transition-all duration-700 ease-out"
                                    style="width: {{ max(5, $percentage) }}%"></div>
                            </div>
                        </div>
                        @endforeach
                        @if(!isset($kelasPopuler) || $kelasPopuler->isEmpty())
                            <div class="text-center text-slate-400 py-8">
                                <p class="text-sm">Belum ada data kunjungan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ====================== PENCARIAN & FILTER (AREA UTAMA) ====================== -->
            <div id="katalog" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 lg:col-span-3">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 flex items-center justify-center bg-brand-info-50 rounded-2xl text-brand-info-dark flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 leading-tight">Cari Buku</h3>
                        <p class="text-xs text-slate-400">Temukan buku di perpustakaan</p>
                    </div>
                </div>

                <!-- Livewire Form -->
                <div class="flex flex-col sm:flex-row gap-4 mb-8">
                    <div class="flex-1 relative">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul, penulis, penerbit, ISBN..." 
                               class="w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-brand-primary/20 focus:bg-white transition-all text-sm font-medium text-slate-700 placeholder-slate-400">
                        <svg class="absolute left-4 top-3.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div class="w-full sm:w-48 relative">
                        <select wire:model.live="kategori_id" class="w-full pl-4 pr-10 py-3 appearance-none rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-brand-primary/20 focus:bg-white transition-all text-sm font-medium text-slate-700 cursor-pointer">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div class="w-full sm:w-36 relative">
                        <select wire:model.live="grade_level" class="w-full pl-4 pr-10 py-3 appearance-none rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-brand-primary/20 focus:bg-white transition-all text-sm font-medium text-slate-700 cursor-pointer">
                            <option value="">Semua Kelas</option>
                            <option value="7">Kelas 7</option>
                            <option value="8">Kelas 8</option>
                            <option value="9">Kelas 9</option>
                            <option value="10">Kelas 10</option>
                            <option value="11">Kelas 11</option>
                            <option value="12">Kelas 12</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Grid Hasil -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4 xl:gap-5 relative min-h-[300px]">
                    <div wire:loading.delay wire:target="search, kategori_id, grade_level" class="absolute inset-0 z-10 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-2xl">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-brand-primary mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span class="text-sm font-medium text-slate-500">Mencari buku...</span>
                        </div>
                    </div>

                    @forelse($bukus as $buku)
                        <div class="group bg-white border border-slate-100 rounded-2xl hover:border-brand-primary/30 hover:shadow-xl hover:shadow-brand-primary/5 transition-all duration-300 flex flex-col h-full overflow-hidden">
                            <!-- Cover area -->
                            <div class="h-44 sm:h-52 bg-slate-50 flex items-center justify-center text-slate-300 relative overflow-hidden border-b border-slate-100">
                                @if($buku->sampul_buku)
                                    <img src="{{ asset('storage/' . $buku->sampul_buku) }}" alt="{{ $buku->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent pointer-events-none"></div>
                                @else
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                @endif
                                
                                @if($buku->kategoriBuku)
                                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-[10px] font-bold px-2 py-1 rounded-md text-slate-600 shadow-sm border border-slate-200">
                                        {{ $buku->kategoriBuku->nama_kategori }}
                                    </span>
                                @endif
                                @if($buku->grade_level)
                                    <span class="absolute top-3 right-3 bg-brand-info text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-sm">
                                        Kelas {{ $buku->grade_level }}
                                    </span>
                                @endif

                                {{-- Badge E-Book jika ada PDF --}}
                                @if($buku->file_pdf)
                                    <span class="absolute bottom-3 right-3 inline-flex items-center gap-1 bg-emerald-500 text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-sm">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        E-Book
                                    </span>
                                @endif
                            </div>
                            <div class="p-5 flex-1 flex flex-col">
                                <h4 class="font-bold text-slate-800 leading-tight mb-1 group-hover:text-brand-primary transition-colors">{{ $buku->judul }}</h4>
                                <p class="text-xs text-slate-500 mb-4 line-clamp-1">{{ $buku->penulis ?? 'Penulis tidak diketahui' }} &bull; {{ $buku->penerbit ?? 'Penerbit tidak diketahui' }}</p>
                                
                                <div class="mt-auto pt-3 border-t border-slate-50 flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            @if($buku->eksemplar_tersedia_count > 0)
                                                <span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse"></span>
                                                <span class="text-xs font-bold text-brand-accent-dark">{{ $buku->eksemplar_tersedia_count }} Tersedia</span>
                                            @else
                                                <span class="w-2 h-2 rounded-full bg-brand-danger"></span>
                                                <span class="text-xs font-bold text-brand-danger-dark">Dipinjam Semua</span>
                                            @endif
                                        </div>
                                        @if($buku->isbn)
                                            <span class="text-[10px] font-mono text-slate-400">{{ $buku->isbn }}</span>
                                        @endif
                                    </div>

                                    {{-- Tombol Baca Online (Pop-up Modal) --}}
                                    @if($buku->file_pdf)
                                        <button @click="pdfTitle = '{{ addslashes($buku->judul) }}'; pdfUrl = '{{ asset('storage/' . $buku->file_pdf) }}'; readerUrl = '{{ route('perpustakaan.baca-buku', $buku) }}'; showPdfModal = true"
                                                class="w-full flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-colors duration-200 shadow-sm hover:shadow cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            Baca Online (Pop-up)
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 mb-1">Buku tidak ditemukan</h3>
                            <p class="text-sm text-slate-500">Coba ubah kata kunci pencarian atau filter Anda.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $bukus->links('vendor.livewire.custom-pagination') }}
                </div>
            </div>
        </div>

        <!-- ====================== BUKU YANG SEDANG SAYA PINJAM (Jika Login) ====================== -->
        @if($activeLoans->isNotEmpty())
        <div class="bg-gradient-to-br from-brand-primary-950 to-brand-primary-dark rounded-3xl p-8 mb-10 shadow-xl relative overflow-hidden">
            <!-- Decorative bg -->
            <div class="absolute right-0 top-0 bottom-0 w-1/2 bg-gradient-to-l from-white/10 to-transparent pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-brand-info-light backdrop-blur-sm border border-white/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Buku Yang Sedang Saya Pinjam</h2>
                        <p class="text-brand-primary-200 text-sm">Informasi peminjaman perpustakaan Anda saat ini.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($activeLoans as $loan)
                        @php
                            $isOverdue = $loan->tanggal_jatuh_tempo < now()->startOfDay();
                        @endphp
                        <div class="bg-white/10 border border-white/20 backdrop-blur-md rounded-2xl p-5 hover:bg-white/20 transition-colors">
                            <h4 class="font-bold text-white leading-tight mb-1">{{ $loan->eksemplar->buku->judul }}</h4>
                            <p class="text-xs text-brand-primary-100 mb-4 opacity-80">Kode: {{ $loan->eksemplar->kode_eksemplar }}</p>
                            
                            <div class="flex items-center justify-between border-t border-white/10 pt-3">
                                <div>
                                    <p class="text-[10px] text-brand-primary-200 uppercase font-semibold mb-0.5">Jatuh Tempo</p>
                                    <p class="text-sm font-bold {{ $isOverdue ? 'text-brand-danger-light' : 'text-white' }}">
                                        {{ \Carbon\Carbon::parse($loan->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                                    </p>
                                </div>
                                @if($isOverdue)
                                    <span class="bg-brand-danger-light/20 text-brand-danger-100 text-xs font-bold px-2 py-1 rounded-lg border border-brand-danger-light/30">Terlambat</span>
                                @else
                                    <span class="bg-brand-accent-light/20 text-brand-accent-100 text-xs font-bold px-2 py-1 rounded-lg border border-brand-accent-light/30">Aktif</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ══════════════════ FOOTER ══════════════════ --}}
    <x-public-dashboard.footer :pengaturanSekolah="$pengaturanSekolah" />

    <!-- ====================== MODAL POPUP BACA PDF ====================== -->
    <template x-teleport="body">
        <div x-show="showPdfModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-2 sm:p-4 bg-slate-950/80 backdrop-blur-md"
             x-cloak>
            <div @click.away="showPdfModal = false"
                 class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-5 py-4 bg-slate-900 border-b border-slate-800 text-white">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-base truncate text-white" x-text="pdfTitle"></h3>
                            <p class="text-xs text-slate-400">Pratinjau E-Book / Baca Online</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="readerUrl" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Layar Penuh
                        </a>
                        <button @click="showPdfModal = false" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <!-- Modal Body (Embed PDF) -->
                <div class="flex-1 bg-slate-950 relative">
                    <template x-if="showPdfModal">
                        <iframe :src="pdfUrl" class="w-full h-full border-0"></iframe>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
