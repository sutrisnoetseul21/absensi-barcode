<div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-8 pb-16">
    <!-- Hero Banner (Senada dengan Theme Settings) -->
    <div class="relative bg-gradient-to-r from-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 overflow-hidden border border-brand-primary/30">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white/10 rounded-full filter blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
                    Portal Setup &amp; Instalasi
                </span>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold mt-3 tracking-tight leading-snug">
                    Checklist Kesiapan Awal Sistem
                </h1>
                <p class="text-white/85 text-sm sm:text-base mt-1 max-w-2xl leading-relaxed">
                    Pastikan 5 fondasi utama (Identitas &amp; Jenjang Sekolah, Tahun Ajaran Aktif, Master Kelas, Master Jabatan, dan Master Mata Pelajaran) berstatus <strong>OK</strong> sebelum melakukan upload data Guru &amp; Siswa.
                </p>
            </div>

            <div class="flex flex-col items-start md:items-end gap-3 shrink-0">
                <div class="px-4 py-1.5 rounded-full bg-white/15 border border-white/25 backdrop-blur-sm text-xs font-bold">
                    Progres: {{ $completedSteps }} / {{ $totalSteps }} Langkah ({{ $progressPercent }}%)
                </div>
                <a href="{{ route('portal-setup.dasar') }}"
                   class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl text-xs sm:text-sm font-bold transition-all shadow-lg flex items-center gap-2">
                    <span>{{ $allReady ? 'Kelola Pengaturan Dasar' : 'Mulai Setup Sekarang' }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="relative z-10 mt-6 w-full bg-white/20 rounded-full h-2.5 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 {{ $allReady ? 'bg-emerald-400' : 'bg-amber-300' }}"
                 style="width: {{ $progressPercent }}%"></div>
        </div>
    </div>

    @if($allReady)
        <div class="flex items-center gap-3.5 p-4 sm:p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="text-sm">
                <span class="font-extrabold">Fondasi Sistem Siap (4/4 OK)!</span>
                <span class="text-emerald-800"> Identitas sekolah, tahun ajaran aktif, master kelas, dan master jabatan sudah lengkap untuk tahap upload data guru &amp; siswa.</span>
            </div>
        </div>
    @endif

    <!-- 5 Kartu Checklist Status -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- 1. Identitas Sekolah -->
        <div class="bg-white rounded-3xl p-6 border {{ $schoolReady ? 'border-emerald-200/80' : 'border-amber-200/80' }} shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-12 h-12 rounded-2xl {{ $schoolReady ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    @if($schoolReady)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            OK
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            Belum Lengkap
                        </span>
                    @endif
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Langkah 1</p>
                <h2 class="text-lg font-extrabold text-slate-900 mt-0.5 mb-2">Identitas &amp; Jenjang Sekolah</h2>
                @if($schoolReady)
                    <p class="text-sm font-bold text-slate-700">{{ $school->school_name }}</p>
                    <p class="text-xs text-slate-500 mt-1">Jenjang: <strong class="text-slate-700">{{ $school->school_level ?? 'SMP' }}</strong></p>
                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $school->school_address }}</p>
                @else
                    <p class="text-xs text-slate-500 leading-relaxed">Nama sekolah, alamat, dan jenjang (SD/SMP/SMA/SMK) belum dilengkapi.</p>
                @endif
            </div>
            <a href="{{ route('portal-setup.dasar', ['tab' => 'sekolah']) }}"
               class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 hover:bg-brand-primary hover:text-white text-slate-700 transition-colors">
                {{ $schoolReady ? 'Ubah Identitas Sekolah' : 'Isi Identitas Sekolah' }} &rarr;
            </a>
        </div>

        <!-- 2. Tahun Ajaran Aktif -->
        <div class="bg-white rounded-3xl p-6 border {{ $yearReady ? 'border-emerald-200/80' : 'border-amber-200/80' }} shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-12 h-12 rounded-2xl {{ $yearReady ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    @if($yearReady)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            OK
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            Belum Ada
                        </span>
                    @endif
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Langkah 2</p>
                <h2 class="text-lg font-extrabold text-slate-900 mt-0.5 mb-2">Tahun Ajaran Aktif</h2>
                @if($yearReady)
                    <p class="text-2xl font-extrabold text-indigo-700">{{ $activeYear->name }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Semester Aktif: <strong class="text-slate-700 capitalize">{{ $school?->active_semester ?? 'ganjil' }}</strong>
                    </p>
                @else
                    <p class="text-xs text-slate-500 leading-relaxed">Belum ada Tahun Ajaran berstatus aktif. Wajib diisi sebelum input kelas/siswa.</p>
                @endif
            </div>
            @if($schoolReady)
                <a href="{{ route('portal-setup.dasar', ['tab' => 'tahun-ajaran']) }}"
                   class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 hover:bg-brand-primary hover:text-white text-slate-700 transition-colors">
                    {{ $yearReady ? 'Kelola / Tambah Tahun Ajaran' : 'Atur Tahun Ajaran' }} &rarr;
                </a>
            @else
                <span class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 text-slate-400 cursor-not-allowed">
                    Terkunci (Simpan Langkah 1 dulu)
                </span>
            @endif
        </div>

        <!-- 3. Master Kelas -->
        <div class="bg-white rounded-3xl p-6 border {{ $classReady ? 'border-emerald-200/80' : 'border-amber-200/80' }} shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-12 h-12 rounded-2xl {{ $classReady ? 'bg-teal-50 text-teal-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    @if($classReady)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            OK
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            Belum Ada
                        </span>
                    @endif
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Langkah 3</p>
                <h2 class="text-lg font-extrabold text-slate-900 mt-0.5 mb-2">Master Kelas / Rombel</h2>
                @if($classReady)
                    <p class="text-2xl font-extrabold text-teal-700">{{ $totalClasses }} Kelas</p>
                    <p class="text-xs text-slate-500 mt-1">Struktur rombongan belajar siap digunakan untuk plotting siswa.</p>
                @else
                    <p class="text-xs text-slate-500 leading-relaxed">Belum ada kelas terdaftar. Anda bisa tambah satuan atau generate massal.</p>
                @endif
            </div>
            @if($schoolReady)
                <a href="{{ route('portal-setup.dasar', ['tab' => 'kelas']) }}"
                   class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 hover:bg-brand-primary hover:text-white text-slate-700 transition-colors">
                    {{ $classReady ? 'Kelola Master Kelas' : 'Buat Master Kelas' }} &rarr;
                </a>
            @else
                <span class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 text-slate-400 cursor-not-allowed">
                    Terkunci (Simpan Langkah 1 dulu)
                </span>
            @endif
        </div>

        <!-- 4. Master Jabatan -->
        <div class="bg-white rounded-3xl p-6 border {{ $jabatanReady ? 'border-emerald-200/80' : ($dasarReady ? 'border-amber-200/80' : 'border-slate-200/80 opacity-85') }} shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-12 h-12 rounded-2xl {{ $jabatanReady ? 'bg-purple-50 text-purple-600' : ($dasarReady ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    @if(!$dasarReady)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                            Terkunci
                        </span>
                    @elseif($jabatanReady)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            OK
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            Belum Lengkap
                        </span>
                    @endif
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Langkah 4</p>
                <h2 class="text-lg font-extrabold text-slate-900 mt-0.5 mb-2">Master Jabatan ({{ $schoolLevel }})</h2>
                @if(!$dasarReady)
                    <p class="text-xs text-slate-500 leading-relaxed">Selesaikan Langkah 1–3 terlebih dahulu agar daftar jabatan sesuai jenjang sekolah.</p>
                @elseif($jabatanReady)
                    <p class="text-2xl font-extrabold text-purple-700">{{ $totalJabatans }} Jabatan</p>
                    <p class="text-xs text-slate-500 mt-1">Jabatan wajib ({{ implode(', ', $wajibJabatans) }}) sudah siap.</p>
                @else
                    <p class="text-xs text-slate-500 leading-relaxed">Jabatan wajib ({{ implode(', ', $wajibJabatans) }}) belum lengkap untuk notifikasi WA &amp; SPIKAP.</p>
                @endif
            </div>
            @if($dasarReady)
                <a href="{{ route('portal-setup.jabatan') }}"
                   class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 hover:bg-brand-primary hover:text-white text-slate-700 transition-colors">
                    {{ $jabatanReady ? 'Kelola Master Jabatan' : 'Sinkron Jabatan Standar' }} &rarr;
                </a>
            @else
                <span class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 text-slate-400 cursor-not-allowed">
                    Terkunci (Lengkapi Langkah 1–3)
                </span>
            @endif
        </div>

        <!-- 5. Master Mata Pelajaran -->
        <div class="bg-white rounded-3xl p-6 border {{ $mapelReady ? 'border-emerald-200/80' : ($dasarReady ? 'border-amber-200/80' : 'border-slate-200/80 opacity-85') }} shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-12 h-12 rounded-2xl {{ $mapelReady ? 'bg-cyan-50 text-cyan-600' : ($dasarReady ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    @if(!$dasarReady)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                            Terkunci
                        </span>
                    @elseif($mapelReady)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            OK
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            Belum Ada
                        </span>
                    @endif
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Langkah 5</p>
                <h2 class="text-lg font-extrabold text-slate-900 mt-0.5 mb-2">Master Mata Pelajaran ({{ $schoolLevel }})</h2>
                @if(!$dasarReady)
                    <p class="text-xs text-slate-500 leading-relaxed">Selesaikan Langkah 1–3 terlebih dahulu agar daftar mapel standar sesuai jenjang sekolah.</p>
                @elseif($mapelReady)
                    <p class="text-2xl font-extrabold text-cyan-700">{{ $totalMapels }} Mapel</p>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $schoolLevel === 'SMK' ? 'Mapel umum siap; Anda bisa tambah mapel jurusan kapan saja.' : 'Daftar mata pelajaran siap digunakan di Pembelajaran & Ujian.' }}
                    </p>
                @else
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $schoolLevel === 'SMK' ? 'Sinkronkan mapel umum lalu tambahkan mapel kejuruan sesuai jurusan sekolah.' : 'Sinkronkan mapel standar sesuai jenjang atau tambah manual.' }}
                    </p>
                @endif
            </div>
            @if($dasarReady)
                <a href="{{ route('portal-setup.mapel') }}"
                   class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 hover:bg-brand-primary hover:text-white text-slate-700 transition-colors">
                    {{ $mapelReady ? 'Kelola Mata Pelajaran' : 'Sinkron Mapel Standar' }} &rarr;
                </a>
            @else
                <span class="mt-6 inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 text-slate-400 cursor-not-allowed">
                    Terkunci (Lengkapi Langkah 1–3)
                </span>
            @endif
        </div>
    </div>
</div>
