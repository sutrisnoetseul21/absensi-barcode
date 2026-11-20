<div class="space-y-6" x-data="{ quickMenu: null }" @keydown.escape.window="quickMenu = null">
    {{-- Header / Hero Card --}}
    <div class="relative bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-7 text-white shadow-lg shadow-brand-primary/20 overflow-hidden">
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/10 rounded-full filter blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-[11px] font-bold uppercase tracking-wider">
                    Portal Web Sekolah
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2.5">Dashboard Portal Web</h2>
                <p class="text-sm text-white/85 mt-1">{{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }} &bull; Pilih menu kotak akses cepat di bawah untuk mengelola konten dan layanan website.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('portal-web.artikel', ['action' => 'create']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-brand-primary rounded-2xl text-xs sm:text-sm font-extrabold hover:bg-white/90 transition-colors shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Tulis Artikel Baru
                </a>
                <a href="{{ url('/') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white border border-white/25 rounded-2xl text-xs sm:text-sm font-bold transition-colors backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Lihat Web Publik
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Menu Akses Cepat (Kotak-Kotak) --}}
    <div class="space-y-6">
        {{-- Kelompok 1: Upload & Publikasi (Operasional Harian) --}}
        <div>
            <div class="flex items-center justify-between mb-3 px-0.5">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                        Upload &amp; Publikasi Harian
                    </h3>
                    <p class="text-xs text-slate-500">Menu yang paling sering digunakan untuk memperbarui berita, prestasi, galeri, dan microsite.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                <button type="button" @click="quickMenu = 'artikel'"
                   class="text-left w-full bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md hover:border-blue-300 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-100">
                                {{ $totalBerita + $totalPengumuman }} Terbit
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-blue-700 transition-colors">Artikel &amp; Pengumuman</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $totalBerita }} Berita &bull; {{ $totalPengumuman }} Pengumuman aktif</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-blue-600 w-full">
                        <span>Pilih Upload / Lihat</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </button>

                <button type="button" @click="quickMenu = 'prestasi'"
                   class="text-left w-full bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md hover:border-amber-300 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-100">
                                {{ $totalPrestasi }} Prestasi
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-amber-700 transition-colors">Prestasi Sekolah</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Publikasi kejuaraan, lomba, dan penghargaan siswa maupun guru.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-600 w-full">
                        <span>Pilih Upload / Lihat</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </button>

                <button type="button" @click="quickMenu = 'galeri'"
                   class="text-left w-full bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md hover:border-violet-300 hover:-translate-y-0.5 transition-all group flex flex-col justify-between cursor-pointer">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-violet-50 text-violet-600 border border-violet-100 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-violet-50 text-violet-700 border border-violet-100">
                                {{ $totalGaleri }} Foto
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-violet-700 transition-colors">Galeri Foto</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Unggah dokumentasi foto acara dan kegiatan sekolah.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-violet-600 w-full">
                        <span>Pilih Upload / Lihat</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </button>

                <a href="{{ route('portal-web.microsite') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md hover:border-cyan-300 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-cyan-50 text-cyan-600 border border-cyan-100 flex items-center justify-center group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-cyan-50 text-cyan-700 border border-cyan-100">
                                {{ $totalMicrosite }} Aktif
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-cyan-700 transition-colors">Microsite Sekolah &amp; Guru</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Kelola daftar tautan microsite resmi sekolah maupun guru.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-cyan-600">
                        <span>Kelola Tautan</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>
            </div>
        </div>

        {{-- Kelompok 2: Pelayanan Publik & Data Alumni --}}
        <div>
            <div class="mb-3 px-0.5">
                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Pelayanan Publik &amp; Data Alumni
                </h3>
                <p class="text-xs text-slate-500">Tindak lanjut pengaduan masyarakat, halaman standar pelayanan, FAQ, serta database tracer alumni.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                <a href="{{ route('portal-web.pelayanan.data') }}"
                   class="bg-white rounded-2xl border {{ $pengaduanMenunggu > 0 ? 'border-rose-200 bg-rose-50/20' : 'border-slate-200/90' }} p-5 shadow-xs hover:shadow-md hover:border-rose-300 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $pengaduanMenunggu > 0 ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $pengaduanMenunggu > 0 ? $pengaduanMenunggu . ' Menunggu' : $totalPengaduan . ' Total' }}
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-rose-700 transition-colors">Data Pengaduan</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Periksa dan perbarui status aspirasi/pengaduan masuk.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600">
                        <span>Tindak Lanjut</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>

                <a href="{{ route('portal-web.pelayanan.halaman') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md hover:border-emerald-300 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                {{ $totalHalamanLayanan }} Halaman
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors">Halaman Layanan</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Kelola halaman standar pelayanan publik &amp; PPID sekolah.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-600">
                        <span>Kelola Halaman</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>

                <a href="{{ route('portal-web.pelayanan.faq') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md hover:border-teal-300 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-teal-50 text-teal-700 border border-teal-100">
                                {{ $totalFaq }} Item
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-teal-700 transition-colors">FAQ Sekolah</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Atur daftar pertanyaan yang sering diajukan pengunjung.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-teal-600">
                        <span>Kelola FAQ</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>

                <a href="{{ route('portal-web.alumni') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md hover:border-indigo-300 hover:-translate-y-0.5 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $totalAlumni }} Alumni
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-indigo-700 transition-colors">Data Tracer Alumni</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Lihat dan kelola data kelulusan serta jenjang lanjutan alumni.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600">
                        <span>Lihat Data Alumni</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </a>
            </div>
        </div>

        {{-- Kelompok 3: Setup Beranda & Profil (Statis) --}}
        <div>
            <div class="mb-3 px-0.5">
                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    Setup Beranda &amp; Profil Sekolah
                </h3>
                <p class="text-xs text-slate-500">Pengaturan konten statis halaman depan &amp; profil sekolah yang umumnya diatur di awal atau berkala.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                <a href="{{ route('portal-web.pengaturan') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs hover:shadow-md hover:border-violet-300 transition-all group flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-slate-900 text-sm group-hover:text-violet-700 truncate">Profil &amp; Beranda Utama</h4>
                        <p class="text-xs text-slate-500 truncate">Hero banner, kepsek, visi-misi &amp; sosmed</p>
                    </div>
                    <span class="text-slate-400 group-hover:text-violet-600 text-sm font-bold">&rarr;</span>
                </a>

                <a href="{{ route('portal-web.akademik') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs hover:shadow-md hover:border-teal-300 transition-all group flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-teal-700 truncate">Halaman Akademik</h4>
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold">{{ $totalAkademik }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate">Halaman informasi kurikulum &amp; program</p>
                    </div>
                    <span class="text-slate-400 group-hover:text-teal-600 text-sm font-bold">&rarr;</span>
                </a>

                <a href="{{ route('portal-web.sarpras') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs hover:shadow-md hover:border-emerald-300 transition-all group flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 truncate">Sarana &amp; Prasarana</h4>
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold">{{ $totalSarpras }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate">Kartu daftar fasilitas sekolah di beranda</p>
                    </div>
                    <span class="text-slate-400 group-hover:text-emerald-600 text-sm font-bold">&rarr;</span>
                </a>

                <a href="{{ route('portal-web.pilar-keunggulan') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs hover:shadow-md hover:border-amber-300 transition-all group flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-amber-700 truncate">Pilar Keunggulan</h4>
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold">{{ $totalPilar }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate">Kartu keunggulan sekolah di halaman depan</p>
                    </div>
                    <span class="text-slate-400 group-hover:text-amber-600 text-sm font-bold">&rarr;</span>
                </a>

                <a href="{{ route('portal-web.statistik') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs hover:shadow-md hover:border-sky-300 transition-all group flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-sky-700 truncate">Statistik &amp; Info</h4>
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold">{{ $totalStatistik }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate">Counter angka &amp; info akreditasi beranda</p>
                    </div>
                    <span class="text-slate-400 group-hover:text-sky-600 text-sm font-bold">&rarr;</span>
                </a>

                <a href="{{ route('portal-web.akses-cepat') }}"
                   class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs hover:shadow-md hover:border-purple-300 transition-all group flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-purple-700 truncate">Akses Cepat</h4>
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold">{{ $totalAksesCepat }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate">Tautan pintas aplikasi di beranda publik</p>
                    </div>
                    <span class="text-slate-400 group-hover:text-purple-600 text-sm font-bold">&rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Artikel Terbaru --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-extrabold text-slate-900">Artikel Terbaru</h3>
                <a href="{{ route('portal-web.artikel') }}" class="text-xs text-violet-600 hover:text-violet-700 font-bold">Lihat Semua &rarr;</a>
            </div>
            @forelse($artikelTerbaru as $item)
            <div class="flex items-start gap-3 py-3 border-b border-slate-100 last:border-0">
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wide mt-0.5 flex-shrink-0
                    {{ $item->tipe === 'berita' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $item->tipe }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $item->judul }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $item->created_at?->diffForHumans() }}</p>
                </div>
                <a href="{{ route('beranda.artikel', $item->slug) }}" target="_blank" class="text-slate-400 hover:text-violet-600 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
            @empty
            <p class="text-sm text-slate-400 py-4 text-center">Belum ada artikel yang dipublikasikan.</p>
            @endforelse
        </div>

        {{-- Presensi Hari Ini --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-extrabold text-slate-900 mb-4">Presensi Hari Ini</h3>
            @if(!empty($presensiHariIni))
                @php
                    $presensiItems = [
                        ['label' => 'Hadir', 'count' => $presensiHariIni['hadir'], 'dot' => 'bg-emerald-500'],
                        ['label' => 'Terlambat', 'count' => $presensiHariIni['telat'], 'dot' => 'bg-amber-500'],
                        ['label' => 'Sakit', 'count' => $presensiHariIni['sakit'], 'dot' => 'bg-sky-500'],
                        ['label' => 'Izin', 'count' => $presensiHariIni['izin'], 'dot' => 'bg-indigo-500'],
                        ['label' => 'Alpa', 'count' => $presensiHariIni['alpa'], 'dot' => 'bg-red-500'],
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach($presensiItems as $p)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $p['dot'] }}"></span>
                            <span class="text-sm text-slate-600">{{ $p['label'] }}</span>
                        </div>
                        <span class="font-extrabold text-slate-900 text-sm">{{ $p['count'] }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 text-center py-4">Tidak ada data presensi.</p>
            @endif
        </div>
    </div>

    {{-- Pop-up Pilihan Cepat (Upload atau Lihat) --}}
    <div x-show="quickMenu !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="quickMenu = null">

        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">

            {{-- Pop-up 1: Artikel & Pengumuman --}}
            <template x-if="quickMenu === 'artikel'">
                <div>
                    <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">Artikel &amp; Pengumuman</h3>
                                <p class="text-xs text-white/80">Pilih aksi yang ingin dilakukan</p>
                            </div>
                        </div>
                        <button type="button" @click="quickMenu = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-5 space-y-2.5">
                        <a href="{{ route('portal-web.artikel', ['action' => 'create', 'tipe' => 'berita']) }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100/80 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Upload / Tulis Berita Baru</div>
                                <div class="text-xs text-slate-500">Langsung buka form upload berita sekolah</div>
                            </div>
                            <span class="text-blue-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-web.artikel', ['action' => 'create', 'tipe' => 'pengumuman']) }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100/80 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Upload / Tulis Pengumuman</div>
                                <div class="text-xs text-slate-500">Langsung buka form pengumuman resmi</div>
                            </div>
                            <span class="text-amber-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-web.artikel') }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Lihat &amp; Kelola Semua</div>
                                <div class="text-xs text-slate-500">Edit, cari, atau hapus artikel ({{ $totalBerita + $totalPengumuman }} terbit)</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('berita.all') }}" target="_blank"
                               class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Web Berita
                            </a>
                            <a href="{{ route('pengumuman.all') }}" target="_blank"
                               class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Web Pengumuman
                            </a>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Pop-up 2: Prestasi Sekolah --}}
            <template x-if="quickMenu === 'prestasi'">
                <div>
                    <div class="px-6 py-5 bg-gradient-to-r from-amber-500 to-orange-500 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">Prestasi Sekolah</h3>
                                <p class="text-xs text-white/85">Pilih aksi yang ingin dilakukan</p>
                            </div>
                        </div>
                        <button type="button" @click="quickMenu = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-5 space-y-2.5">
                        <a href="{{ route('portal-web.prestasi', ['action' => 'create']) }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100/80 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Upload / Tambah Prestasi Baru</div>
                                <div class="text-xs text-slate-500">Langsung buka form upload prestasi &amp; foto piala</div>
                            </div>
                            <span class="text-amber-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-web.prestasi') }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Lihat &amp; Kelola Daftar Prestasi</div>
                                <div class="text-xs text-slate-500">Total {{ $totalPrestasi }} prestasi terpublikasi</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('prestasi.all') }}" target="_blank"
                           class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Lihat Halaman Prestasi di Web Publik
                        </a>
                    </div>
                </div>
            </template>

            {{-- Pop-up 3: Galeri Foto --}}
            <template x-if="quickMenu === 'galeri'">
                <div>
                    <div class="px-6 py-5 bg-gradient-to-r from-violet-600 to-purple-600 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">Galeri Foto</h3>
                                <p class="text-xs text-white/80">Pilih mode upload atau lihat galeri</p>
                            </div>
                        </div>
                        <button type="button" @click="quickMenu = null" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-5 space-y-2.5">
                        <a href="{{ route('portal-web.galeri', ['action' => 'create', 'mode' => 'batch']) }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-violet-200 bg-violet-50/70 hover:bg-violet-100/80 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-violet-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Upload Banyak Foto (Batch)</div>
                                <div class="text-xs text-slate-500">Unggah banyak foto kegiatan sekaligus</div>
                            </div>
                            <span class="text-violet-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-web.galeri', ['action' => 'create', 'mode' => 'single']) }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-indigo-200 bg-indigo-50/60 hover:bg-indigo-100/70 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Upload 1 Foto</div>
                                <div class="text-xs text-slate-500">Unggah foto tunggal dengan judul khusus</div>
                            </div>
                            <span class="text-indigo-600 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('portal-web.galeri') }}"
                           class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-sm text-slate-900">Lihat &amp; Kelola Galeri</div>
                                <div class="text-xs text-slate-500">Atur urutan atau hapus foto ({{ $totalGaleri }} foto)</div>
                            </div>
                            <span class="text-slate-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>

                        <a href="{{ route('galeri.all') }}" target="_blank"
                           class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Lihat Halaman Galeri di Web Publik
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
