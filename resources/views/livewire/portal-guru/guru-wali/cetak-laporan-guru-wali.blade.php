<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Dokumen Resmi &bull; Permendikdasmen No. 11/2025</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pusat Cetak Laporan Guru Wali</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelompok: <strong>{{ $kelompok->nama_kelompok }}</strong> &bull; Cetak laporan berkala individual murid dan laporan kinerja kelompok ekuivalensi 2 JP/Minggu.
            </p>
        </div>

        <!-- Quick Back Button to Kelompok -->
        <div class="flex items-center gap-2">
            <a href="{{ route('portal-guru.guru-wali.kelompok') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kelompok Binaan</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2 sm:gap-4 overflow-x-auto pb-px">
        <button wire:click="setTab('individual')"
                type="button"
                class="flex items-center gap-2.5 py-3 px-4 font-bold text-sm border-b-2 transition-all whitespace-nowrap {{ $activeTab === 'individual' ? 'border-brand-primary text-brand-primary bg-brand-primary/5 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
            <svg class="w-4 h-4 {{ $activeTab === 'individual' ? 'text-brand-primary' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>1. Laporan Pendampingan Individual Murid</span>
            <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-brand-primary/10 text-brand-primary">Tabel Siswa</span>
        </button>

        <button wire:click="setTab('kelompok')"
                type="button"
                class="flex items-center gap-2.5 py-3 px-4 font-bold text-sm border-b-2 transition-all whitespace-nowrap {{ $activeTab === 'kelompok' ? 'border-brand-primary text-brand-primary bg-brand-primary/5 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
            <svg class="w-4 h-4 {{ $activeTab === 'kelompok' ? 'text-brand-primary' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>2. Laporan Kinerja Kelompok (Supervisi 2 JP)</span>
            <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700">Resmi 2 JP</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: TABEL LAPORAN INDIVIDUAL MURID                                      -->
    <!-- ========================================================================= -->
    @if($activeTab === 'individual')
    <div class="space-y-6 animate-fadeIn">
        
        <!-- Modern Filter Bar Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pilih Periode Laporan Individual</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tentukan semester atau tahunan. Data dan tombol cetak pada setiap baris siswa akan otomatis menyesuaikan periode terpilih.
                    </p>
                </div>
                
                <!-- Pill Tahun Ajaran -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500">Tahun Ajaran:</span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach($academicYears as $th)
                            <button type="button" 
                                    wire:click="$set('selectedAcademicYearId', '{{ $th->id }}')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $selectedAcademicYearId === $th->id ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                <span>{{ $th->name }}</span>
                                @if($th->status === 'aktif')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Segmented Controls: Periode Semester -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">
                    Pilihan Periode Semester
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- 1 Tahun Penuh -->
                    <button type="button" 
                            wire:click="$set('selectedPeriode', 'tahunan')"
                            class="relative flex items-center gap-3.5 p-3.5 rounded-2xl border-2 text-left transition-all {{ $selectedPeriode === 'tahunan' ? 'border-brand-primary bg-brand-primary/5 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $selectedPeriode === 'tahunan' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-slate-100 text-slate-600' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold {{ $selectedPeriode === 'tahunan' ? 'text-brand-primary font-black' : 'text-slate-800' }}">
                                1 Tahun Penuh (Tahunan)
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">Semester 1 & 2 Lengkap</div>
                        </div>
                        @if($selectedPeriode === 'tahunan')
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary shrink-0"></span>
                        @endif
                    </button>

                    <!-- Semester 1 (Ganjil) -->
                    <button type="button" 
                            wire:click="$set('selectedPeriode', 'semester_1')"
                            class="relative flex items-center gap-3.5 p-3.5 rounded-2xl border-2 text-left transition-all {{ $selectedPeriode === 'semester_1' ? 'border-brand-primary bg-brand-primary/5 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $selectedPeriode === 'semester_1' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-slate-100 text-slate-600' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold {{ $selectedPeriode === 'semester_1' ? 'text-brand-primary font-black' : 'text-slate-800' }}">
                                Semester 1 (Ganjil)
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">Juli s/d Desember</div>
                        </div>
                        @if($selectedPeriode === 'semester_1')
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary shrink-0"></span>
                        @endif
                    </button>

                    <!-- Semester 2 (Genap) -->
                    <button type="button" 
                            wire:click="$set('selectedPeriode', 'semester_2')"
                            class="relative flex items-center gap-3.5 p-3.5 rounded-2xl border-2 text-left transition-all {{ $selectedPeriode === 'semester_2' ? 'border-brand-primary bg-brand-primary/5 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $selectedPeriode === 'semester_2' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-slate-100 text-slate-600' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold {{ $selectedPeriode === 'semester_2' ? 'text-brand-primary font-black' : 'text-slate-800' }}">
                                Semester 2 (Genap)
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">Januari s/d Juni</div>
                        </div>
                        @if($selectedPeriode === 'semester_2')
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary shrink-0"></span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Search Filter Bar -->
            <div class="pt-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           wire:model.live.debounce.300ms="studentSearch"
                           placeholder="Cari berdasarkan nama murid, NISN, atau kelas..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-2xs">
                    @if(!empty($studentSearch))
                        <button type="button" wire:click="$set('studentSearch', '')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 text-xs font-bold">
                            ✕
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabel Siswa Binaan -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden space-y-0">
            <!-- Table Header Bar -->
            <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-brand-primary"></div>
                    <h3 class="text-sm font-bold text-slate-900">
                        Daftar Peserta Didik Binaan ({{ $tabelSiswa->count() }} Murid)
                    </h3>
                    <span class="text-xs text-slate-400">&bull; Periode: <strong class="text-slate-700">{{ $indLabelPeriode }}</strong></span>
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    Tahun Ajaran: <strong class="text-slate-800">{{ $selectedTahun?->name ?? '2026/2027' }}</strong>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Peserta Didik</th>
                            <th class="py-3 px-4 text-center">Total Sesi</th>
                            <th class="py-3 px-4 text-center">Distribusi 4 Pilar</th>
                            <th class="py-3 px-4 text-center">Konsultasi Mandiri</th>
                            <th class="py-3 px-4">Sesi Terakhir</th>
                            <th class="py-3 px-4 text-right">Aksi Cetak Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tabelSiswa as $index => $item)
                        <tr class="hover:bg-slate-50/70 transition-colors group">
                            <!-- No -->
                            <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>

                            <!-- Profil Murid -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $item->avatar_url }}" 
                                         alt="{{ $item->nama }}" 
                                         class="w-10 h-10 rounded-2xl object-cover bg-slate-100 border border-slate-200/80 shrink-0 shadow-2xs">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-sm truncate group-hover:text-brand-primary transition-colors">
                                            {{ $item->nama }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                                            <span class="font-mono">{{ $item->nisn }}</span>
                                            <span>&bull;</span>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-md bg-slate-100 text-slate-700 font-semibold text-[10px]">
                                                Kelas {{ $item->kelas }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Total Sesi -->
                            <td class="py-3.5 px-4 text-center">
                                @if($item->total_sesi > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                        {{ $item->total_sesi }} Sesi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-400">
                                        0 Sesi
                                    </span>
                                @endif
                            </td>

                            <!-- Distribusi 4 Pilar -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1 font-mono text-[10px]">
                                    <span class="px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100" title="Akademik: {{ $item->pilar_akademik }}">
                                        A:{{ $item->pilar_akademik }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100" title="Karakter: {{ $item->pilar_karakter }}">
                                        K:{{ $item->pilar_karakter }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-100" title="Minat/Bakat: {{ $item->pilar_minat }}">
                                        M:{{ $item->pilar_minat }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-100" title="Sosial/Psikologis: {{ $item->pilar_sosial }}">
                                        S:{{ $item->pilar_sosial }}
                                    </span>
                                </div>
                            </td>

                            <!-- Konsultasi Mandiri Siswa -->
                            <td class="py-3.5 px-4 text-center">
                                @if($item->total_konsultasi > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        <span>{{ $item->total_konsultasi }}x</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>

                            <!-- Sesi Terakhir -->
                            <td class="py-3.5 px-4">
                                <div class="text-[11px] font-semibold text-slate-800">{{ $item->last_sesi_date }}</div>
                                <div class="text-[10px] {{ $item->last_status === 'Tuntas / Selesai' ? 'text-emerald-600 font-bold' : ($item->last_status === 'Belum ada sesi' ? 'text-slate-400' : 'text-amber-600 font-medium') }}">
                                    {{ $item->last_status }}
                                </div>
                            </td>

                            <!-- Aksi Cetak -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Tombol Detail / Preview -->
                                    <button type="button" 
                                            wire:click="showDetail('{{ $item->id }}')"
                                            class="p-2 rounded-xl text-slate-500 hover:text-brand-primary hover:bg-brand-primary/10 transition-colors"
                                            title="Lihat Pratinjau Rekam Jurnal">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Cetak Browser A4 -->
                                    <a href="{{ $item->print_url }}" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-brand-primary text-brand-primary hover:bg-brand-primary/5 text-xs font-bold shadow-2xs transition-all hover:scale-102"
                                       title="Buka Cetak Dokumen A4">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        <span>Cetak A4</span>
                                    </a>

                                    <!-- Tombol Unduh PDF -->
                                    <a href="{{ $item->pdf_url }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-primary text-white hover:opacity-90 text-xs font-bold shadow-xs transition-all hover:scale-102"
                                       title="Unduh Berkas PDF">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span>PDF</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-sm font-bold text-slate-600">Tidak ada peserta didik yang sesuai dengan pencarian.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Silakan bersihkan kata kunci pencarian.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Summary -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500">
                <div>
                    Menampilkan <strong>{{ $tabelSiswa->count() }}</strong> peserta didik aktif kelompok <strong>{{ $kelompok->nama_kelompok }}</strong>.
                </div>
                <div class="text-[11px] text-slate-400 italic">
                    * Klik "Cetak A4" untuk membuka format cetak browser atau "PDF" untuk unduh dokumen resmi.
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL DETAIL / PRATINJAU SISWA                                           -->
        <!-- ========================================================================= -->
        @if($previewStudentId && $previewSiswa)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-fadeIn">
            <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200">
                <!-- Modal Header -->
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <div class="flex items-center gap-3">
                        <img src="{{ $previewSiswa->avatar_url }}" 
                             alt="{{ $previewSiswa->name }}" 
                             class="w-12 h-12 rounded-2xl object-cover bg-white border border-slate-200 shrink-0">
                        <div>
                            <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $previewSiswa->name }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Kelas: {{ $previewSiswa->enrollmentAktif?->kelas?->name ?? '—' }} &bull; NISN: {{ $previewSiswa->nisn ?? '—' }} &bull; Periode: <strong>{{ $indLabelPeriode }}</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            wire:click="closeDetail" 
                            class="w-8 h-8 rounded-full bg-slate-200/80 hover:bg-slate-300 text-slate-600 flex items-center justify-center text-sm font-bold transition-colors">
                        ✕
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto space-y-5">
                    <!-- Session Logs -->
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                Catatan Sesi Pendampingan ({{ $previewJurnals->count() }} Sesi)
                            </h4>
                            <a href="{{ route('portal-guru.guru-wali.jurnal.create', ['student_id' => $previewSiswa->id]) }}" 
                               class="text-xs font-bold text-brand-primary hover:underline">
                                + Tambah Jurnal
                            </a>
                        </div>
                        <div class="space-y-2">
                            @forelse($previewJurnals as $pj)
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($pj->tanggal_waktu)->translatedFormat('d F Y, H:i') }} WIB</span>
                                            <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 text-[10px] font-semibold">{{ $pj->jenis_pendampingan }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pj->status_sesi === 'Tuntas / Selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $pj->status_sesi }}
                                        </span>
                                    </div>
                                    <div class="font-semibold text-brand-primary text-[11px]">{{ $pj->kategori_pendampingan }}</div>
                                    <p class="text-slate-600">{{ $pj->uraian_pembahasan }}</p>
                                    @if($pj->rencana_tindak_lanjut)
                                        <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-200/60">
                                            <strong>Tindak Lanjut:</strong> {{ $pj->rencana_tindak_lanjut }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="p-6 text-center text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-xs">
                                    Belum ada catatan jurnal pada rentang periode ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Consultation Logs -->
                    @if($previewKonsultasis->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2.5">
                            Riwayat Konsultasi Mandiri Siswa ({{ $previewKonsultasis->count() }})
                        </h4>
                        <div class="space-y-2">
                            @foreach($previewKonsultasis as $pk)
                                <div class="p-3 rounded-2xl bg-indigo-50/50 border border-indigo-100 text-xs space-y-1">
                                    <div class="flex justify-between">
                                        <span class="font-bold text-indigo-900">{{ \Carbon\Carbon::parse($pk->created_at)->translatedFormat('d M Y') }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">{{ $pk->status_pengajuan }}</span>
                                    </div>
                                    <p class="text-slate-700">{{ $pk->isi_konsultasi ?? $pk->topik }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                @php
                    $modalPrintUrl = route('portal-guru.guru-wali.cetak.individual.print', [
                        'student_id'       => $previewSiswa->id,
                        'periode'          => $selectedPeriode,
                        'academic_year_id' => $selectedTahun?->id,
                        'autoprint'        => '1',
                    ]);
                    $modalPdfUrl = route('portal-guru.guru-wali.cetak.individual.pdf', [
                        'student_id'       => $previewSiswa->id,
                        'periode'          => $selectedPeriode,
                        'academic_year_id' => $selectedTahun?->id,
                    ]);
                @endphp
                <div class="p-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <button type="button" 
                            wire:click="closeDetail" 
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold">
                        Tutup
                    </button>
                    <div class="flex items-center gap-2">
                        <a href="{{ $modalPrintUrl }}" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-brand-primary text-brand-primary hover:bg-brand-primary/5 text-xs font-bold shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Browser (A4)</span>
                        </a>
                        <a href="{{ $modalPdfUrl }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-primary text-white hover:opacity-90 text-xs font-bold shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- TAB 2: LAPORAN KINERJA KELOMPOK (SUPERVISI 2 JP)                          -->
    <!-- ========================================================================= -->
    @if($activeTab === 'kelompok')
    <div class="space-y-6 animate-fadeIn">
        <!-- Control Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs mb-1">
                        <span>Ekuivalensi 2 JP/Minggu (Kepmendikdasmen No. 221/P/2025)</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Laporan Kinerja Periodik / Tahunan Kelompok Guru Wali</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Dokumen resmi bukti fisik pelaksanaan tugas Guru Wali untuk diserahkan kepada Kepala Sekolah.</p>
                </div>
                
                <!-- Pill Tahun Ajaran Kelompok -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500">Tahun Ajaran:</span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach($academicYears as $th)
                            <button type="button" 
                                    wire:click="$set('selectedKelompokAcademicYearId', '{{ $th->id }}')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $selectedKelompokAcademicYearId === $th->id ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                <span>{{ $th->name }}</span>
                                @if($th->status === 'aktif')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Segmented Controls Periode Kelompok -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">
                    Pilihan Periode Laporan
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button type="button" 
                            wire:click="$set('selectedKelompokPeriode', 'tahunan')"
                            class="relative flex items-center gap-3.5 p-3.5 rounded-2xl border-2 text-left transition-all {{ $selectedKelompokPeriode === 'tahunan' ? 'border-brand-primary bg-brand-primary/5 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $selectedKelompokPeriode === 'tahunan' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-slate-100 text-slate-600' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold {{ $selectedKelompokPeriode === 'tahunan' ? 'text-brand-primary font-black' : 'text-slate-800' }}">
                                1 Tahun Penuh (Tahunan)
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">Laporan Akhir 1 Tahun</div>
                        </div>
                        @if($selectedKelompokPeriode === 'tahunan')
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary shrink-0"></span>
                        @endif
                    </button>

                    <button type="button" 
                            wire:click="$set('selectedKelompokPeriode', 'semester_1')"
                            class="relative flex items-center gap-3.5 p-3.5 rounded-2xl border-2 text-left transition-all {{ $selectedKelompokPeriode === 'semester_1' ? 'border-brand-primary bg-brand-primary/5 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $selectedKelompokPeriode === 'semester_1' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-slate-100 text-slate-600' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold {{ $selectedKelompokPeriode === 'semester_1' ? 'text-brand-primary font-black' : 'text-slate-800' }}">
                                Semester 1 (Ganjil)
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">Juli s/d Desember</div>
                        </div>
                        @if($selectedKelompokPeriode === 'semester_1')
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary shrink-0"></span>
                        @endif
                    </button>

                    <button type="button" 
                            wire:click="$set('selectedKelompokPeriode', 'semester_2')"
                            class="relative flex items-center gap-3.5 p-3.5 rounded-2xl border-2 text-left transition-all {{ $selectedKelompokPeriode === 'semester_2' ? 'border-brand-primary bg-brand-primary/5 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $selectedKelompokPeriode === 'semester_2' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-slate-100 text-slate-600' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold {{ $selectedKelompokPeriode === 'semester_2' ? 'text-brand-primary font-black' : 'text-slate-800' }}">
                                Semester 2 (Genap)
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">Januari s/d Juni</div>
                        </div>
                        @if($selectedKelompokPeriode === 'semester_2')
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary shrink-0"></span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Optional Catatan Refleksi -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Catatan Refleksi & Evaluasi Guru Wali <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea wire:model.defer="catatanRefleksi" 
                          rows="2" 
                          placeholder="Tuliskan catatan refleksi, kendala yang dihadapi, atau rekomendasi perbaikan untuk dicetak pada lembar pengesahan supervisi..."
                          class="w-full rounded-2xl border border-slate-200 text-xs sm:text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary"></textarea>
            </div>

            <!-- Action Buttons -->
            @php
                $printKelompokUrl = route('portal-guru.guru-wali.cetak.kelompok.print', [
                    'periode'          => $selectedKelompokPeriode,
                    'academic_year_id' => $selectedKelompokAcademicYearId,
                    'catatan_refleksi' => $catatanRefleksi,
                    'autoprint'        => '1'
                ]);
                $pdfKelompokUrl = route('portal-guru.guru-wali.cetak.kelompok.pdf', [
                    'periode'          => $selectedKelompokPeriode,
                    'academic_year_id' => $selectedKelompokAcademicYearId,
                    'catatan_refleksi' => $catatanRefleksi,
                ]);
            @endphp

            <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-100">
                <div class="text-xs text-slate-500">
                    Siap mencetak laporan kinerja kelompok <strong>{{ $kelompok->nama_kelompok }}</strong> ({{ $kelompokMetrics['label_periode'] ?? '' }}).
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ $printKelompokUrl }}" 
                       target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-brand-primary text-brand-primary hover:bg-brand-primary/5 text-xs font-bold shadow-xs transition-all hover:scale-102">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Browser (A4)</span>
                    </a>

                    <a href="{{ $pdfKelompokUrl }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-brand-primary text-white hover:opacity-90 text-xs font-bold shadow-md shadow-brand-primary/30 transition-all hover:scale-102">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Dokumen PDF</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Group Performance Metrics Card -->
        <div class="bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 relative overflow-hidden border border-brand-primary/30">
            <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-xs text-white/80 font-semibold tracking-wider uppercase">Kinerja Pendampingan {{ $kelompokMetrics['label_periode'] }}</span>
                        <h2 class="text-xl sm:text-2xl font-black mt-1">
                            {{ $kelompokMetrics['siswa_didampingi'] }} dari {{ $kelompokMetrics['total_siswa'] }} Siswa Telah Didampingi
                        </h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-3xl sm:text-4xl font-black {{ $kelompokMetrics['persentase_kepatuhan'] >= 100 ? 'text-emerald-300' : ($kelompokMetrics['persentase_kepatuhan'] >= 50 ? 'text-amber-300' : 'text-rose-300') }}">
                            {{ $kelompokMetrics['persentase_kepatuhan'] }}%
                        </span>
                        <span class="text-xs text-white/80 font-medium">Kepatuhan</span>
                    </div>
                </div>

                <!-- Barometer Bar -->
                <div class="space-y-1.5">
                    <div class="w-full bg-black/20 rounded-full h-3.5 p-0.5 backdrop-blur-xs overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700 ease-out {{ $kelompokMetrics['persentase_kepatuhan'] >= 100 ? 'bg-emerald-400' : ($kelompokMetrics['persentase_kepatuhan'] >= 50 ? 'bg-amber-400' : 'bg-rose-400') }}"
                             style="width: {{ $kelompokMetrics['persentase_kepatuhan'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] text-white/80">
                        <span>Total Sesi: {{ $kelompokMetrics['total_sesi'] }}</span>
                        <span>Target: {{ $kelompokMetrics['total_siswa'] }} Siswa</span>
                    </div>
                </div>

                <!-- Stat Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-white/15">
                    <div class="bg-white/10 rounded-2xl p-3 border border-white/10 backdrop-blur-xs">
                        <span class="text-[11px] text-white/80 block">Sesi Individu</span>
                        <span class="text-lg font-black mt-0.5 block">{{ $kelompokMetrics['sesi_individu'] }} <span class="text-xs font-normal opacity-80">sesi</span></span>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-3 border border-white/10 backdrop-blur-xs">
                        <span class="text-[11px] text-white/80 block">Sesi Kelompok/Klasikal</span>
                        <span class="text-lg font-black mt-0.5 block">{{ $kelompokMetrics['sesi_kelompok'] }} <span class="text-xs font-normal opacity-80">sesi</span></span>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-3 border border-white/10 backdrop-blur-xs">
                        <span class="text-[11px] text-white/80 block">Rujukan Guru BK</span>
                        <span class="text-lg font-black text-amber-300 mt-0.5 block">{{ $kelompokMetrics['rujukan_bk'] }} <span class="text-xs font-normal opacity-80">kasus</span></span>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-3 border border-white/10 backdrop-blur-xs">
                        <span class="text-[11px] text-white/80 block">Koordinasi Wali Kelas</span>
                        <span class="text-lg font-black text-blue-200 mt-0.5 block">{{ $kelompokMetrics['rujukan_wali_kelas'] }} <span class="text-xs font-normal opacity-80">kasus</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
