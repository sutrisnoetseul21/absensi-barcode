<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Standar Kepatuhan Permendikdasmen No. 11/2025</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pemantauan Bulanan Binaan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelompok: <strong>{{ $kelompok->nama_kelompok }}</strong> &bull; Target wajib pendampingan minimal 1x per bulan untuk setiap peserta didik.
            </p>
        </div>

        <!-- Month Filter Selector -->
        <div class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-600 pl-2">Periode:</span>
            <input type="month" 
                   wire:model.live="selectedMonth" 
                   class="rounded-xl border-slate-200 text-xs py-1.5 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
        </div>
    </div>

    <!-- Compliance Progress Hero Card -->
    <div class="bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 relative overflow-hidden border border-brand-primary/30">
        <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-xs text-white/80 font-semibold tracking-wider uppercase">Capaian Pendampingan {{ $labelBulan }}</span>
                    <h2 class="text-xl sm:text-2xl font-black mt-1">
                        {{ $totalSudah }} dari {{ $totalSiswa }} Siswa Sudah Didampingi
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-3xl sm:text-4xl font-black {{ $persentase >= 100 ? 'text-emerald-300' : ($persentase >= 50 ? 'text-amber-300' : 'text-rose-300') }}">
                        {{ $persentase }}%
                    </span>
                    <span class="text-xs text-white/80 font-medium">Tercapai</span>
                </div>
            </div>

            <!-- Progress Bar Barometer -->
            <div class="space-y-1.5">
                <div class="w-full bg-black/20 rounded-full h-3.5 p-0.5 backdrop-blur-xs overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 ease-out {{ $persentase >= 100 ? 'bg-emerald-400' : ($persentase >= 50 ? 'bg-amber-400' : 'bg-rose-400') }}"
                         style="width: {{ $persentase }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] text-white/80">
                    <span>0 Siswa</span>
                    <span>Target: {{ $totalSiswa }} Siswa (100%)</span>
                </div>
            </div>

            <!-- Stat Breakdown -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-white/15">
                <div class="bg-white/10 rounded-2xl p-3 border border-white/10 backdrop-blur-xs">
                    <span class="text-[11px] text-white/80 block">Total Siswa Binaan</span>
                    <span class="text-lg font-black mt-0.5 block">{{ $totalSiswa }} <span class="text-xs font-normal opacity-80">anak</span></span>
                </div>
                <div class="bg-white/10 rounded-2xl p-3 border border-white/10 backdrop-blur-xs">
                    <span class="text-[11px] text-white/80 block">Sudah Memenuhi</span>
                    <span class="text-lg font-black text-emerald-300 mt-0.5 block">{{ $totalSudah }} <span class="text-xs font-normal opacity-80">anak</span></span>
                </div>
                <div class="bg-white/10 rounded-2xl p-3 border border-white/10 backdrop-blur-xs">
                    <span class="text-[11px] text-white/80 block">Belum Didampingi</span>
                    <span class="text-lg font-black {{ $totalBelum > 0 ? 'text-rose-300' : 'text-slate-200' }} mt-0.5 block">{{ $totalBelum }} <span class="text-xs font-normal opacity-80">anak</span></span>
                </div>
                <div class="bg-white/10 rounded-2xl p-3 border border-white/10 backdrop-blur-xs">
                    <span class="text-[11px] text-white/80 block">Total Sesi Bimbingan</span>
                    <span class="text-lg font-black text-white mt-0.5 block">{{ $totalSesi }} <span class="text-xs font-normal opacity-80">kali</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribusi Kategori Bimbingan Bulan Ini -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Distribusi Topik Pendampingan ({{ $labelBulan }})</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-blue-900 block">Akademik</span>
                    <span class="text-[11px] text-blue-600">Hasil & Kesulitan Belajar</span>
                </div>
                <span class="text-xl font-black text-blue-700">{{ $kategoriCounts['Akademik'] }}</span>
            </div>

            <div class="p-3.5 rounded-2xl bg-purple-50/70 border border-purple-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-purple-900 block">Karakter & Disiplin</span>
                    <span class="text-[11px] text-purple-600">Etika & Kehadiran</span>
                </div>
                <span class="text-xl font-black text-purple-700">{{ $kategoriCounts['Karakter & Kedisiplinan'] }}</span>
            </div>

            <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-900 block">Minat & Bakat</span>
                    <span class="text-[11px] text-amber-600">Potensi & Ekskul</span>
                </div>
                <span class="text-xl font-black text-amber-700">{{ $kategoriCounts['Minat & Bakat / Ekskul'] }}</span>
            </div>

            <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-900 block">Sosial & Emosional</span>
                    <span class="text-[11px] text-emerald-600">Hubungan & Kesejahteraan</span>
                </div>
                <span class="text-xl font-black text-emerald-700">{{ $kategoriCounts['Sosial & Psikologis'] }}</span>
            </div>
        </div>
    </div>

    <!-- DAFTAR SISWA: BELUM vs SUDAH -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- SECTION 1: BELUM DIDAMPINGI (PRIORITAS) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <h3 class="text-sm font-black text-slate-900">Perlu Dijadwalkan (Belum Ada Catatan Bulan Ini)</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    {{ $totalBelum }} Siswa
                </span>
            </div>

            @if($siswaBelum->count() > 0)
                <div class="space-y-3">
                    @foreach($siswaBelum as $item)
                        <div class="bg-white rounded-2xl border border-rose-200/80 p-4 shadow-xs hover:shadow-md transition-all flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 font-bold flex items-center justify-center shrink-0 text-xs">
                                    {{ strtoupper(substr($item->siswa?->name ?? 'S', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-900 truncate">{{ $item->siswa?->name }}</h4>
                                    <p class="text-[11px] text-slate-500">
                                        Kelas {{ $item->siswa?->enrollmentAktif?->kelas?->name ?? '-' }} &bull; NISN: {{ $item->siswa?->nisn ?? '-' }}
                                    </p>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">
                                        @if($item->last_ever_date)
                                            Sesi sebelumnya: {{ \Carbon\Carbon::parse($item->last_ever_date)->translatedFormat('d M Y') }}
                                        @else
                                            Belum pernah ada sesi bimbingan
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="{{ route('portal-guru.guru-wali.jurnal.create', ['student_id' => $item->student_id]) }}" 
                               class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-[11px] shadow-xs active:scale-95 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                                </svg>
                                <span>Bimbing Sekarang</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800">Luar Biasa! Semua Siswa Sudah Didampingi</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Target kepatuhan Permendikdasmen No. 11/2025 untuk periode {{ $labelBulan }} telah terpenuhi 100%.
                    </p>
                </div>
            @endif
        </div>

        <!-- SECTION 2: SUDAH DIDAMPINGI -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm font-black text-slate-900">Sudah Didampingi Bulan Ini</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $totalSudah }} Siswa
                </span>
            </div>

            @if($siswaSudah->count() > 0)
                <div class="space-y-3">
                    @foreach($siswaSudah as $item)
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md transition-all flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 text-xs">
                                    {{ strtoupper(substr($item->siswa?->name ?? 'S', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-slate-900 truncate">{{ $item->siswa?->name }}</h4>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            {{ $item->sesi_count }}x Sesi
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500">
                                        Kelas {{ $item->siswa?->enrollmentAktif?->kelas?->name ?? '-' }}
                                    </p>
                                    @if($item->last_sesi)
                                        <span class="text-[10px] text-emerald-700 font-medium block mt-0.5">
                                            Terakhir: {{ \Carbon\Carbon::parse($item->last_sesi->tanggal_waktu)->translatedFormat('d M') }} &bull; {{ $item->last_sesi->kategori_pendampingan }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Action -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('portal-guru.guru-wali.jurnal.create', ['student_id' => $item->student_id]) }}" 
                                   title="Tambah Sesi Bimbingan Lagi"
                                   class="p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-brand-primary/10 hover:text-brand-primary transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800">Belum ada sesi di periode ini</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Mulai catat bimbingan pertama untuk peserta didik binaan pada bulan ini.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
