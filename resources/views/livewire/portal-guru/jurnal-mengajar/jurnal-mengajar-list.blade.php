@php
    $overallTotals = $analytics['totals'] ?? [
        'total_sesi' => 0, 'total_jp' => 0, 'hadir' => 0, 'izin' => 0,
        'sakit' => 0, 'izin_sakit' => 0, 'alpa' => 0, 'tidak_mengikuti' => 0, 'total_absen' => 0,
    ];
    $classesSummary = $analytics['classes_summary'] ?? [];
    $studentsSummary = $analytics['students_summary'] ?? [];

    $activeClassSummary = null;
    if (!empty($selectedClassId)) {
        foreach ($classesSummary as $cs) {
            if ((string)($cs['class_id'] ?? '') === (string)$selectedClassId) {
                $activeClassSummary = $cs;
                break;
            }
        }
    }

    $activeTotals = $activeClassSummary ?: $overallTotals;
    $activeScopeLabel = $activeClassSummary ? ('Kelas ' . ($activeClassSummary['class_name'] ?? '-')) : 'Semua Kelas Rombel';

    $scopedStudents = array_values(array_filter($studentsSummary, function ($st) use ($selectedClassId) {
        if (!empty($selectedClassId) && (string)($st['class_id'] ?? '') !== (string)$selectedClassId) {
            return false;
        }
        return true;
    }));

    $absentStudentsCount = count(array_filter($scopedStudents, fn($st) => (int)($st['total_absen'] ?? 0) > 0));
    $effectiveOnlyAbsent = $onlyAbsentStudents && $absentStudentsCount > 0;

    $filteredStudents = array_values(array_filter($scopedStudents, function ($st) use ($effectiveOnlyAbsent, $studentSearch) {
        if ($effectiveOnlyAbsent && (int)($st['total_absen'] ?? 0) <= 0) {
            return false;
        }
        if (trim($studentSearch) !== '') {
            $q = mb_strtolower(trim($studentSearch));
            $name = mb_strtolower((string)($st['student_name'] ?? ''));
            $nis = mb_strtolower((string)($st['nis'] ?? ''));
            $cname = mb_strtolower((string)($st['class_name'] ?? ''));
            return str_contains($name, $q) || str_contains($nis, $q) || str_contains($cname, $q);
        }
        return true;
    }));
@endphp

<div class="space-y-5 max-w-7xl mx-auto pb-20" x-data="{ showCetakModal: @entangle('showCetakModal').live }">
    <!-- HEADER RINGKAS & TOMBOL AKSI + TAB NAVIGASI -->
    <div class="bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary text-white rounded-3xl p-5 sm:p-6 shadow-lg border border-brand-primary/30">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-white/15 text-white text-[11px] font-bold uppercase tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        TA {{ $academicYears->firstWhere('id', $selectedAcademicYearId)?->name ?? 'Aktif' }}
                    </span>
                    @if($isSupervisor)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-amber-400/25 text-amber-100 border border-amber-300/40 text-[11px] font-bold">
                            Mode Supervisi
                        </span>
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white">
                    Jurnal Mengajar & Analisa KBM
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('portal-guru.jurnal-mengajar.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-brand-primary text-xs sm:text-sm font-extrabold shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Tulis Jurnal</span>
                </a>

                <button type="button" wire:click="openCetakModal"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white border border-white/25 text-xs sm:text-sm font-bold transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    <span>Cetak Laporan</span>
                </button>
            </div>
        </div>

        <!-- 2 TAB NAVIGASI (Seragam dengan Android) -->
        <div class="grid grid-cols-2 gap-2 mt-5 p-1.5 bg-black/15 rounded-2xl border border-white/15">
            <button type="button" wire:click="setTab('riwayat')"
                class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-extrabold transition-all cursor-pointer {{ $activeTab === 'riwayat' ? 'bg-white text-brand-primary shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Riwayat Jurnal ({{ $totalJurnal }})</span>
            </button>

            <button type="button" wire:click="setTab('analisa')"
                class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-extrabold transition-all cursor-pointer {{ $activeTab === 'analisa' ? 'bg-white text-brand-primary shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Analisa Kelas & Siswa</span>
                @if(($overallTotals['total_absen'] ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'analisa' ? 'bg-rose-100 text-rose-700' : 'bg-rose-500 text-white' }}">
                        {{ $overallTotals['total_absen'] }} Absen
                    </span>
                @endif
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/90 flex items-start gap-3 shadow-sm">
            <span class="text-sm font-bold text-emerald-800">{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/90 flex items-start gap-3 shadow-sm">
            <span class="text-sm font-bold text-rose-800">{{ session('error') }}</span>
        </div>
    @endif

    @if($activeTab === 'riwayat')
        <!-- ==================== TAB 1: RIWAYAT JURNAL ==================== -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-5 transition-all">
            <div class="grid grid-cols-1 sm:grid-cols-2 {{ $isSupervisor ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-3.5">
                @if($isSupervisor)
                    <div>
                        <label class="block text-[11px] font-extrabold text-amber-900 uppercase mb-1">Guru Pengampu</label>
                        <select wire:model.live="selectedTeacherId" class="w-full rounded-xl border border-amber-300 bg-amber-50/50 text-slate-900 text-xs sm:text-sm px-3 py-2.5 font-semibold cursor-pointer">
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase mb-1">Bulan KBM</label>
                    <select wire:model.live="selectedMonthYear" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-xs sm:text-sm px-3 py-2.5 font-bold cursor-pointer">
                        <option value="">Semua Bulan</option>
                        @foreach($availableMonths as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase mb-1">Mata Pelajaran</label>
                    <select wire:model.live="selectedMapelId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-xs sm:text-sm px-3 py-2.5 font-bold cursor-pointer">
                        <option value="">Semua Mapel</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase mb-1">Kelas Rombel</label>
                    <select wire:model.live="selectedClassId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-xs sm:text-sm px-3 py-2.5 font-bold cursor-pointer">
                        <option value="">Semua Kelas Rombel</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase mb-1">Cari Materi / Refleksi</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Ketik kata kunci materi..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-xs sm:text-sm font-medium">
                </div>
            </div>

            <!-- Strip Ringkasan Kompak -->
            <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2 text-xs font-extrabold text-slate-700">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-200/80">
                        📖 {{ number_format($totalJurnal) }} Sesi KBM
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                        ⏱️ {{ number_format($totalJp) }} JP ({{ number_format($totalJp * 40) }} Menit)
                    </span>
                    @if(($overallTotals['total_absen'] ?? 0) > 0)
                        <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200/80">
                            ⚠️ {{ $overallTotals['total_absen'] }} Catatan Ketidakhadiran
                        </span>
                    @endif
                </div>

                <button type="button" wire:click="setTab('analisa')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-brand-primary border border-indigo-200 text-xs font-extrabold transition-all cursor-pointer">
                    <span>Lihat Analisa Siswa</span>
                    <span>→</span>
                </button>
            </div>
        </div>

        <!-- JURNAL LIST CARDS -->
        <div class="space-y-4">
            @forelse($jurnals as $jurnal)
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md transition-all p-5 sm:p-6 group">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="space-y-3 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-slate-100 text-slate-800">
                                    📅 {{ hari_tanggal_indonesia($jurnal->tanggal) }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                    ⏱️ {{ $jurnal->jam_ke_label }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                    🎓 Kelas {{ $jurnal->kelas?->name }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-extrabold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                    📖 {{ $jurnal->mataPelajaran?->nama_mapel }}
                                </span>
                            </div>

                            <div>
                                <h3 class="text-base sm:text-lg font-black text-slate-900">
                                    {{ $jurnal->materi }}
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed whitespace-pre-line bg-slate-50/70 p-3 rounded-2xl border border-slate-100">
                                    {{ $jurnal->uraian_materi }}
                                </p>
                            </div>

                            @if($jurnal->refleksi)
                                <div class="p-3 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-xs text-amber-950">
                                    <span class="font-black text-amber-900">Refleksi Guru:</span>
                                    <span class="ml-1 text-slate-700">{{ $jurnal->refleksi }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between gap-3 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100 shrink-0">
                            <div class="text-left lg:text-right">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block">Kehadiran KBM</span>
                                <div class="flex items-center gap-1.5 lg:justify-end mt-0.5">
                                    <span class="text-base font-black text-emerald-700">{{ $jurnal->total_hadir }}/{{ $jurnal->total_siswa }}</span>
                                    <span class="text-xs font-bold text-slate-500">Siswa</span>
                                </div>

                                @if($jurnal->total_tidak_mengikuti > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 mt-1">
                                        {{ $jurnal->total_tidak_mengikuti }} Tidak Hadir
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 mt-1">
                                        ✓ Semua Hadir
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if(!$isSupervisor || auth()->user()->isSuperAdmin() || $jurnal->teacher_id === $currentTeacherId)
                                    <a href="{{ route('portal-guru.jurnal-mengajar.edit', $jurnal->id) }}"
                                       class="p-2.5 rounded-2xl text-slate-600 hover:text-brand-primary hover:bg-brand-primary/10 border border-slate-200 transition-all" title="Edit Jurnal">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>

                                    <button wire:click="delete('{{ $jurnal->id }}')"
                                            wire:confirm="Yakin ingin menghapus catatan jurnal mengajar tanggal {{ $jurnal->tanggal->format('d/m/Y') }} ini?"
                                            class="p-2.5 rounded-2xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition-all cursor-pointer" title="Hapus Jurnal">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-slate-200/90 p-10 text-center shadow-sm">
                    <h3 class="text-base font-extrabold text-slate-800">Belum Ada Catatan Jurnal Mengajar</h3>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto mt-1">
                        Belum ada riwayat pembelajaran yang tercatat pada filter periode atau kelas yang dipilih.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $jurnals->links() }}
        </div>
    @else
        <!-- ==================== TAB 2: ANALISA KELAS & SISWA ==================== -->
        <div class="space-y-5">
            <!-- 1. Filter Periode & Mapel -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 {{ $isSupervisor ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-3.5">
                    @if($isSupervisor)
                        <div>
                            <label class="block text-[11px] font-extrabold text-amber-900 uppercase mb-1">Guru Pengampu</label>
                            <select wire:model.live="selectedTeacherId" class="w-full rounded-xl border border-amber-300 bg-amber-50/50 text-slate-900 text-xs sm:text-sm px-3 py-2.5 font-semibold cursor-pointer">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-600 uppercase mb-1">Periode Bulan KBM</label>
                        <select wire:model.live="selectedMonthYear" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-xs sm:text-sm px-3 py-2.5 font-bold cursor-pointer">
                            <option value="">Semua Bulan (1 Tahun Ajaran)</option>
                            @foreach($availableMonths as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-600 uppercase mb-1">Mata Pelajaran</label>
                        <select wire:model.live="selectedMapelId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-xs sm:text-sm px-3 py-2.5 font-bold cursor-pointer">
                            <option value="">Semua Mata Pelajaran</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 2. Chip Pilihan Kelas Rombel -->
                @if(count($classesSummary) > 0)
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-4 mt-4 border-t border-slate-100">
                        <button type="button" wire:click="selectAnalisaClass('')"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-extrabold shrink-0 border transition-all cursor-pointer {{ empty($selectedClassId) ? 'bg-brand-primary text-white border-brand-primary shadow-sm' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' }}">
                            <span>Semua Kelas</span>
                            <span class="px-2 py-0.5 rounded-lg text-[10px] {{ empty($selectedClassId) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                                {{ $overallTotals['total_sesi'] ?? 0 }} Sesi
                            </span>
                        </button>

                        @foreach($classesSummary as $cs)
                            @php
                                $isSelectedClass = (string)$selectedClassId === (string)$cs['class_id'];
                                $absCount = (int)($cs['siswa_pernah_absen_count'] ?? 0);
                            @endphp
                            <button type="button" wire:click="selectAnalisaClass('{{ $cs['class_id'] }}')"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-extrabold shrink-0 border transition-all cursor-pointer {{ $isSelectedClass ? 'bg-brand-primary text-white border-brand-primary shadow-sm' : ($absCount > 0 ? 'bg-white text-slate-800 border-rose-200 hover:bg-rose-50/30' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50') }}">
                                <span>Kelas {{ $cs['class_name'] }}</span>
                                <span class="px-2 py-0.5 rounded-lg text-[10px] {{ $isSelectedClass ? 'bg-white/20 text-white' : ($absCount > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $cs['total_sesi'] }} Sesi{{ $absCount > 0 ? " • {$absCount} Absen" : '' }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 3. Kotak 2x2 Breakdown Kehadiran KBM (Gaya Portal Siswa) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-5">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-sm sm:text-base font-black text-slate-900">
                            Rekap Status KBM • {{ $activeScopeLabel }}
                        </h2>
                        <p class="text-xs font-semibold text-slate-500">
                            {{ $activeTotals['total_sesi'] ?? 0 }} Pertemuan Tercatat • Total {{ $activeTotals['total_jp'] ?? 0 }} JP ({{ ($activeTotals['total_jp'] ?? 0) * 40 }} Menit)
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- HADIR -->
                    <div class="rounded-2xl bg-emerald-50/90 border border-emerald-200 p-3.5">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-emerald-700">HADIR</span>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl sm:text-2xl font-black text-emerald-900">{{ $activeTotals['hadir'] ?? 0 }}</span>
                            <span class="text-xs font-bold text-emerald-700">kali</span>
                        </div>
                        <span class="block text-[11px] font-semibold text-emerald-700/80 mt-0.5">Mengikuti KBM</span>
                    </div>

                    <!-- IZIN / SAKIT -->
                    <div class="rounded-2xl bg-blue-50/90 border border-blue-200 p-3.5">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-blue-700">IZIN / SAKIT</span>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl sm:text-2xl font-black text-blue-900">{{ $activeTotals['izin_sakit'] ?? 0 }}</span>
                            <span class="text-xs font-bold text-blue-700">kali</span>
                        </div>
                        <span class="block text-[11px] font-semibold text-blue-700/80 mt-0.5">
                            Izin: {{ $activeTotals['izin'] ?? 0 }} • Sakit: {{ $activeTotals['sakit'] ?? 0 }}
                        </span>
                    </div>

                    <!-- ALPA -->
                    <div class="rounded-2xl bg-rose-50/90 border border-rose-200 p-3.5">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-rose-700">ALPA</span>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl sm:text-2xl font-black text-rose-900">{{ $activeTotals['alpa'] ?? 0 }}</span>
                            <span class="text-xs font-bold text-rose-700">kali</span>
                        </div>
                        <span class="block text-[11px] font-semibold text-rose-700/80 mt-0.5">Tanpa Keterangan</span>
                    </div>

                    <!-- TIDAK MENGIKUTI -->
                    <div class="rounded-2xl bg-orange-50/90 border border-orange-200 p-3.5">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-orange-700">TIDAK MENGIKUTI</span>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl sm:text-2xl font-black text-orange-900">{{ $activeTotals['tidak_mengikuti'] ?? 0 }}</span>
                            <span class="text-xs font-bold text-orange-700">kali</span>
                        </div>
                        <span class="block text-[11px] font-semibold text-orange-700/80 mt-0.5">Meninggalkan Kelas</span>
                    </div>
                </div>
            </div>

            <!-- 4. Perbandingan Per Kelas (Saat Semua Kelas Aktif) -->
            @if(empty($selectedClassId) && count($classesSummary) > 1)
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-5">
                    <h3 class="text-sm font-black text-slate-900 mb-1">Perbandingan Kehadiran Per Kelas</h3>
                    <p class="text-xs text-slate-500 mb-3.5">Klik salah satu kelas untuk melihat analisa mendalam siswa di kelas tersebut.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($classesSummary as $cs)
                            <div wire:click="selectAnalisaClass('{{ $cs['class_id'] }}')"
                                 class="p-3.5 rounded-2xl bg-slate-50 hover:bg-indigo-50/40 border border-slate-200 hover:border-indigo-300 transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-lg bg-brand-primary text-white text-xs font-black">
                                            Kelas {{ $cs['class_name'] }}
                                        </span>
                                        <span class="text-xs font-bold text-slate-600">
                                            {{ $cs['total_sesi'] }} Pertemuan • {{ $cs['total_jp'] }} JP
                                        </span>
                                    </div>
                                    @if(($cs['siswa_pernah_absen_count'] ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-extrabold">
                                            {{ $cs['siswa_pernah_absen_count'] }} Siswa Absen
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-4 gap-1.5 text-center text-[11px] font-extrabold">
                                    <div class="py-1 rounded-lg bg-emerald-50 text-emerald-700">Hadir: {{ $cs['hadir'] }}</div>
                                    <div class="py-1 rounded-lg bg-blue-50 text-blue-700">I/S: {{ $cs['izin_sakit'] }}</div>
                                    <div class="py-1 rounded-lg bg-rose-50 text-rose-700">Alpa: {{ $cs['alpa'] }}</div>
                                    <div class="py-1 rounded-lg bg-orange-50 text-orange-700">Tdk Ikut: {{ $cs['tidak_mengikuti'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 5. Daftar Analisa Kehadiran Siswa -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h3 class="text-sm sm:text-base font-black text-slate-900">
                        Analisa Kehadiran Siswa ({{ $activeScopeLabel }})
                    </h3>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="$set('onlyAbsentStudents', true)"
                            class="px-3 py-1.5 rounded-xl text-xs font-extrabold border transition-all cursor-pointer {{ $effectiveOnlyAbsent ? 'bg-rose-50 text-rose-700 border-rose-300' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                            ⚠️ Pernah Tidak Hadir ({{ $absentStudentsCount }})
                        </button>
                        <button type="button" wire:click="$set('onlyAbsentStudents', false)"
                            class="px-3 py-1.5 rounded-xl text-xs font-extrabold border transition-all cursor-pointer {{ !$effectiveOnlyAbsent ? 'bg-indigo-50 text-brand-primary border-indigo-300' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                            Semua Siswa ({{ count($scopedStudents) }})
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <input type="text" wire:model.live.debounce.250ms="studentSearch"
                           placeholder="Cari nama siswa atau NIS..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-xs sm:text-sm font-medium">
                </div>

                @if(count($filteredStudents) === 0)
                    <div class="p-8 rounded-2xl bg-slate-50 text-center text-xs sm:text-sm font-semibold text-slate-500">
                        Belum ada data siswa yang cocok dengan filter ketidakhadiran / pencarian ini.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($filteredStudents as $st)
                            @php
                                $hasBolosOrAlpa = ($st['tidak_mengikuti'] ?? 0) > 0 || ($st['alpa'] ?? 0) > 0;
                                $logs = $st['absence_logs'] ?? [];
                            @endphp
                            <div class="p-4 rounded-2xl border {{ $hasBolosOrAlpa ? 'bg-rose-50/20 border-rose-200' : 'bg-slate-50/60 border-slate-200' }}">
                                <div class="flex flex-wrap items-start justify-between gap-2 mb-2.5">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-900">{{ $st['student_name'] }}</h4>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-brand-primary text-[10px] font-extrabold">
                                                Kelas {{ $st['class_name'] }}
                                            </span>
                                            <span class="text-xs font-semibold text-slate-500">
                                                NIS: {{ $st['nis'] }} • {{ $st['total_pertemuan'] }} Pertemuan
                                            </span>
                                        </div>
                                    </div>

                                    @if(($st['total_absen'] ?? 0) > 0)
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-black {{ $hasBolosOrAlpa ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $st['total_absen'] }} Kali Tidak Hadir
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black">
                                            100% Hadir
                                        </span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-4 gap-2 text-center text-xs font-extrabold">
                                    <div class="py-1.5 rounded-lg bg-emerald-50 text-emerald-700">Hadir: {{ $st['hadir'] }}</div>
                                    <div class="py-1.5 rounded-lg bg-blue-50 text-blue-700">Izin/Sakit: {{ $st['izin_sakit'] }}</div>
                                    <div class="py-1.5 rounded-lg bg-rose-50 text-rose-700">Alpa: {{ $st['alpa'] }}</div>
                                    <div class="py-1.5 rounded-lg bg-orange-50 text-orange-700">Tdk Ikut: {{ $st['tidak_mengikuti'] }}</div>
                                </div>

                                @if(count($logs) > 0)
                                    <div class="mt-3 p-3 rounded-xl bg-white border border-slate-200 space-y-1.5">
                                        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">
                                            Riwayat Ketidakhadiran KBM:
                                        </span>
                                        @foreach($logs as $log)
                                            <div class="flex items-start gap-2 text-xs">
                                                <span class="px-2 py-0.5 rounded font-black text-[10px] uppercase shrink-0 {{ ($log['status'] ?? '') === 'alpa' ? 'bg-rose-100 text-rose-700' : (in_array($log['status'] ?? '', ['izin', 'sakit']) ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                                                    {{ $log['status_label'] ?? 'Tidak Mengikuti' }}
                                                </span>
                                                <div>
                                                    <span class="font-extrabold text-slate-800">{{ $log['tanggal_formatted'] }} • {{ $log['jam_ke_label'] }}</span>
                                                    <span class="text-slate-500 block">
                                                        Materi: {{ $log['materi'] }}{{ !empty($log['keterangan']) ? ' (' . $log['keterangan'] . ')' : '' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Modal Cetak Laporan (Model Akademik) -->
    @include('livewire.portal-guru.jurnal-mengajar.modal-cetak')
</div>
