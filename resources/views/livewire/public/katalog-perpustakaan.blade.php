<div class="min-h-screen bg-slate-50 flex flex-col font-jakarta">

    <!-- ====================== HEADER / NAVBAR MODERN ====================== -->
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500" id="main-navbar"
        x-data="{ scrolled: false, mobileMenuOpen: false }"
        @scroll.window="scrolled = window.scrollY > 30"
        :class="scrolled ? 'bg-white/80 backdrop-blur-xl shadow-lg shadow-brand-primary/10 border-b border-white/60' : (mobileMenuOpen ? 'bg-slate-950/70 backdrop-blur-2xl border-b border-white/10' : 'bg-transparent')">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Header Kiri: Logo & Nama Sekolah -->
                <div class="flex items-center gap-3 group">
                    @if($pengaturanSekolah && $pengaturanSekolah->school_logo_path)
                        <div class="relative">
                            <div class="absolute inset-0 bg-brand-primary-light/30 rounded-xl blur-md group-hover:blur-lg transition-all duration-300"></div>
                            <img src="{{ asset('storage/' . $pengaturanSekolah->school_logo_path) }}" alt="Logo"
                                class="relative h-10 sm:h-12 w-auto object-contain drop-shadow-md">
                        </div>
                    @endif
                    <div>
                        <h1 class="text-lg sm:text-xl font-extrabold tracking-tight leading-tight transition-colors duration-300"
                            :class="scrolled ? 'text-slate-800' : 'text-white drop-shadow-md'">
                            {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'SMPN 1 Majenang' }}
                        </h1>
                        <p class="text-xs font-medium transition-colors duration-300" :class="scrolled ? 'text-brand-primary-dark' : 'text-brand-primary-100'">
                            Perpustakaan Digital
                        </p>
                    </div>
                </div>

                <!-- Header Kanan: Menu Navigasi Desktop -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <a href="{{ url('/') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-brand-primary hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Home
                        </span>
                    </a>
                    <a href="{{ url('/siswa') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-brand-primary hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Portal Siswa
                        </span>
                    </a>
                    <a href="{{ url('/wali-kelas') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-brand-primary hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Portal Guru
                        </span>
                    </a>
                    <a href="{{ url('/admin') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-brand-primary hover:bg-brand-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Admin
                        </span>
                    </a>
                    <a href="#katalog"
                        class="ml-4 flex items-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary hover:from-brand-primary-dark hover:to-brand-secondary-dark text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-brand-primary/30 transition-all duration-300 transform hover:scale-105 hover:shadow-brand-primary/50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Cari Buku
                        <span class="w-2 h-2 rounded-full bg-brand-accent-light animate-pulse ml-1"></span>
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-xl transition-all duration-200"
                    :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'">
                    <svg x-show="!mobileMenuOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenuOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden absolute top-full left-0 w-full backdrop-blur-xl border-b shadow-xl transition-colors duration-300"
             :class="scrolled ? 'bg-white/95 border-slate-200' : 'bg-slate-950/80 border-white/10'"
             style="display: none;">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="{{ url('/') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-brand-primary-50 hover:text-brand-primary' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Home
                </a>
                <a href="{{ url('/siswa') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-brand-primary-50 hover:text-brand-primary' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Portal Siswa
                </a>
                <a href="{{ url('/wali-kelas') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-brand-primary-50 hover:text-brand-primary' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Portal Guru
                </a>
                <a href="{{ url('/admin') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-brand-primary-50 hover:text-brand-primary' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Admin
                </a>
                <a href="#katalog" class="mt-4 flex items-center justify-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary text-white px-4 py-3 rounded-xl font-bold shadow-md shadow-brand-primary/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari Buku
                </a>
            </div>
        </div>
    </header>

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
                    Katalog
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
            <!-- ====================== KATEGORI TERPOPULER (DATA SEKUNDER) ====================== -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col h-full lg:col-span-1">
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
                            <!-- Jika SMA, ubah ke 10,11,12 -->
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Grid Hasil -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative min-h-[300px]">
                    <div wire:loading.delay wire:target="search, kategori_id, grade_level" class="absolute inset-0 z-10 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-2xl">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-brand-primary mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span class="text-sm font-medium text-slate-500">Mencari buku...</span>
                        </div>
                    </div>

                    @forelse($bukus as $buku)
                        <div class="group bg-white border border-slate-100 rounded-2xl hover:border-brand-primary/30 hover:shadow-xl hover:shadow-brand-primary/5 transition-all duration-300 flex flex-col h-full overflow-hidden">
                            <!-- Cover area (placeholder) -->
                            <div class="h-32 bg-slate-50 flex items-center justify-center text-slate-300 relative">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                
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
                            </div>
                            <div class="p-5 flex-1 flex flex-col">
                                <h4 class="font-bold text-slate-800 leading-tight mb-1 group-hover:text-brand-primary transition-colors">{{ $buku->judul }}</h4>
                                <p class="text-xs text-slate-500 mb-4 line-clamp-1">{{ $buku->penulis ?? 'Penulis tidak diketahui' }} &bull; {{ $buku->penerbit ?? 'Penerbit tidak diketahui' }}</p>
                                
                                <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
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
                    {{ $bukus->links('pagination::tailwind') }}
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

    <footer class="bg-slate-900 text-slate-400 py-8 mt-auto border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'SMPN 1 Majenang' }}. All rights reserved.</p>
            <p class="mt-2 text-slate-500 text-xs">Sistem Informasi Perpustakaan Terpadu</p>
        </div>
    </footer>
</div>
