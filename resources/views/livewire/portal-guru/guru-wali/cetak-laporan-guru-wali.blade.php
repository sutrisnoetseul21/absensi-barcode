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
        </button>

        <button wire:click="setTab('kelompok')"
                type="button"
                class="flex items-center gap-2.5 py-3 px-4 font-bold text-sm border-b-2 transition-all whitespace-nowrap {{ $activeTab === 'kelompok' ? 'border-brand-primary text-brand-primary bg-brand-primary/5 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
            <svg class="w-4 h-4 {{ $activeTab === 'kelompok' ? 'text-brand-primary' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>2. Laporan Kinerja Kelompok (Supervisi 2 JP)</span>
            <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700">Resmi</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: LAPORAN INDIVIDUAL MURID                                           -->
    <!-- ========================================================================= -->
    @if($activeTab === 'individual')
    <div class="space-y-6 animate-fadeIn">
        <!-- Control Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Parameter Cetak Dokumen Individual</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih peserta didik dan rentang semester yang ingin dicetak.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                        Format: Kertas A4 Portrait
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Dropdown Siswa -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Peserta Didik Binaan <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="selectedStudentId" 
                            class="w-full rounded-2xl border-slate-200 text-xs sm:text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                        @forelse($anggotaAktif as $anggota)
                            <option value="{{ $anggota->student_id }}">
                                {{ $anggota->siswa?->name }} ({{ $anggota->siswa?->enrollmentAktif?->kelas?->name ?? 'Tanpa Kelas' }}) - NISN: {{ $anggota->siswa?->nisn ?? '—' }}
                            </option>
                        @empty
                            <option value="">Tidak ada anggota aktif</option>
                        @endforelse
                    </select>
                </div>

                <!-- Dropdown Periode -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Rentang Periode Semester <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="selectedPeriode" 
                            class="w-full rounded-2xl border-slate-200 text-xs sm:text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                        <option value="tahunan">1 Tahun Penuh (Semester 1 & 2)</option>
                        <option value="semester_1">Semester 1 (Ganjil - Juli s/d Des)</option>
                        <option value="semester_2">Semester 2 (Genap - Jan s/d Juni)</option>
                    </select>
                </div>

                <!-- Dropdown Tahun Ajaran -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tahun Ajaran <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="selectedAcademicYearId" 
                            class="w-full rounded-2xl border-slate-200 text-xs sm:text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                        @foreach($academicYears as $th)
                            <option value="{{ $th->id }}">
                                {{ $th->name }} {{ $th->status === 'aktif' ? '(Tahun Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            @if($selectedSiswa)
                @php
                    $printUrl = route('portal-guru.guru-wali.cetak.individual.print', [
                        'student_id'       => $selectedSiswa->id,
                        'periode'          => $selectedPeriode,
                        'academic_year_id' => $selectedAcademicYearId,
                        'autoprint'        => '1'
                    ]);
                    $pdfUrl = route('portal-guru.guru-wali.cetak.individual.pdf', [
                        'student_id'       => $selectedSiswa->id,
                        'periode'          => $selectedPeriode,
                        'academic_year_id' => $selectedAcademicYearId,
                    ]);
                @endphp

                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <div class="text-xs text-slate-500">
                        Siap mencetak laporan untuk <strong>{{ $selectedSiswa->name }}</strong> ({{ $individualMetrics['label_periode'] ?? '' }}).
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ $printUrl }}" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-brand-primary text-brand-primary hover:bg-brand-primary/5 text-xs font-bold shadow-xs transition-all hover:scale-102">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Browser (A4)</span>
                        </a>

                        <a href="{{ $pdfUrl }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-brand-primary text-white hover:opacity-90 text-xs font-bold shadow-md shadow-brand-primary/30 transition-all hover:scale-102">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Dokumen PDF</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Live Preview / Quick Info Card -->
        @if($selectedSiswa && $individualMetrics)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 1 Col: Student Profile Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center gap-3.5 border-b border-slate-100 pb-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-black text-lg border border-brand-primary/20">
                        {{ strtoupper(substr($selectedSiswa->name, 0, 2)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $selectedSiswa->name }}</h4>
                        <span class="text-xs text-slate-500">Kelas: {{ $selectedSiswa->enrollmentAktif?->kelas?->name ?? '—' }}</span>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">NIS / NISN:</span>
                        <span class="font-semibold text-slate-800">{{ $selectedSiswa->nis ?? '—' }} / {{ $selectedSiswa->nisn ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Guru Wali:</span>
                        <span class="font-semibold text-slate-800">{{ $teacher->name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Tahun Ajaran:</span>
                        <span class="font-semibold text-slate-800">{{ $selectedTahun?->name ?? '2026/2027' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Total Sesi Terlaksana:</span>
                        <span class="font-black text-brand-primary text-sm">{{ $individualMetrics['total_sesi'] }} Sesi</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Konsultasi Mandiri Siswa:</span>
                        <span class="font-bold text-slate-800">{{ $individualMetrics['total_konsultasi'] }} Pengajuan</span>
                    </div>
                </div>

                <!-- 4 Pillars Breakdown -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <h5 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Distribusi 4 Pilar</h5>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-2 text-center">
                            <span class="text-[10px] text-blue-600 block font-semibold">Akademik</span>
                            <span class="text-sm font-bold text-blue-900">{{ $individualMetrics['pilar_akademik'] }}</span>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-2 text-center">
                            <span class="text-[10px] text-emerald-600 block font-semibold">Karakter</span>
                            <span class="text-sm font-bold text-emerald-900">{{ $individualMetrics['pilar_karakter'] }}</span>
                        </div>
                        <div class="bg-amber-50 border border-amber-100 rounded-xl p-2 text-center">
                            <span class="text-[10px] text-amber-600 block font-semibold">Minat/Bakat</span>
                            <span class="text-sm font-bold text-amber-900">{{ $individualMetrics['pilar_minat'] }}</span>
                        </div>
                        <div class="bg-purple-50 border border-purple-100 rounded-xl p-2 text-center">
                            <span class="text-[10px] text-purple-600 block font-semibold">Sosial/Psikologis</span>
                            <span class="text-sm font-bold text-purple-900">{{ $individualMetrics['pilar_sosial'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 2 Cols: Session Preview Table -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">Pratinjau Rekam Jurnal Pendampingan</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Sesi terbaru dalam rentang periode terpilih.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-primary/10 text-brand-primary">
                        {{ $recentJurnals->count() }} Sesi Ditampilkan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="py-2.5 px-3 rounded-l-xl">Tanggal</th>
                                <th class="py-2.5 px-3">Bentuk & Pilar</th>
                                <th class="py-2.5 px-3">Topik Pembahasan</th>
                                <th class="py-2.5 px-3 rounded-r-xl">Status & Tindak Lanjut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentJurnals as $jurnal)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->format('H:i') }} WIB</div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 mb-1">
                                        {{ $jurnal->jenis_pendampingan }}
                                    </span>
                                    <div class="font-medium text-slate-800 text-[11px]">{{ $jurnal->kategori_pendampingan }}</div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <p class="line-clamp-2 text-slate-700 text-[11px]">{{ $jurnal->uraian_pembahasan }}</p>
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $jurnal->status_sesi === 'Tuntas / Selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $jurnal->status_sesi }}
                                    </span>
                                    @if($jurnal->rujukan_kolaborasi && $jurnal->rujukan_kolaborasi !== 'Mandiri')
                                        <div class="text-[10px] text-brand-primary font-medium mt-0.5">Rujukan: {{ $jurnal->rujukan_kolaborasi }}</div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">
                                    Belum ada catatan jurnal pendampingan untuk peserta didik ini dalam periode yang dipilih.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($recentJurnals->isNotEmpty())
                    <p class="text-[11px] text-slate-400 italic text-right pt-2">
                        * Cetak dokumen resmi untuk melihat seluruh riwayat lengkap beserta lembar pengesahan tanda tangan.
                    </p>
                @endif
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs mb-1">
                        <span>Ekuivalensi 2 JP/Minggu</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Laporan Kinerja Periodik / Tahunan Kelompok Guru Wali</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Dokumen resmi bukti fisik pelaksanaan tugas Guru Wali untuk diserahkan kepada Kepala Sekolah.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                        Format: Kertas A4 Portrait
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Dropdown Periode Kelompok -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Periode Laporan <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="selectedKelompokPeriode" 
                            class="w-full rounded-2xl border-slate-200 text-xs sm:text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                        <option value="tahunan">1 Tahun Penuh (Tahunan)</option>
                        <option value="semester_1">Semester 1 (Ganjil)</option>
                        <option value="semester_2">Semester 2 (Genap)</option>
                    </select>
                </div>

                <!-- Dropdown Tahun Ajaran Kelompok -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tahun Ajaran <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="selectedKelompokAcademicYearId" 
                            class="w-full rounded-2xl border-slate-200 text-xs sm:text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                        @foreach($academicYears as $th)
                            <option value="{{ $th->id }}">
                                {{ $th->name }} {{ $th->status === 'aktif' ? '(Tahun Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Optional Catatan Refleksi -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Catatan Refleksi & Evaluasi Guru Wali <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea wire:model.defer="catatanRefleksi" 
                          rows="2" 
                          placeholder="Tuliskan ringkasan refleksi, kendala yang dihadapi, atau rekomendasi tindak lanjut yang akan dicetak pada lembar evaluasi..."
                          class="w-full rounded-2xl border-slate-200 text-xs sm:text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary"></textarea>
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
