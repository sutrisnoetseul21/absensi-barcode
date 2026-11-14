<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== TAB SWITCHER & FILTER BAR ===== --}}
        <x-filament::section>
            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="setTab('guru')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'guru' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            <x-filament::icon icon="heroicon-o-user-group" class="w-4 h-4" />
                            Monitoring Guru & Realisasi JP
                        </button>
                        <button type="button" wire:click="setTab('kelas')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'kelas' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            <x-filament::icon icon="heroicon-o-academic-cap" class="w-4 h-4" />
                            Rekap KBM per Kelas (Tampilan Mapel)
                        </button>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                        <x-filament::icon icon="heroicon-o-calendar" class="w-3.5 h-3.5" />
                        Tahun Ajaran Aktif: {{ $activeYearName }}
                    </span>
                </div>

                {{-- Filter Controls --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 {{ $activeTab === 'guru' ? 'lg:grid-cols-6' : 'lg:grid-cols-3' }} gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">1. Periode Waktu <span class="text-rose-500">*</span></label>
                        <select wire:model.live="selectedPeriod"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100">
                            @foreach($periodOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">2. Pilih Kelas <span class="text-rose-500">*</span></label>
                        <select wire:model.live="selectedClassId"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100">
                            <option value="">— Pilih Kelas —</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($activeTab === 'guru')
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">3. Status Pengisian</label>
                            <select wire:model.live="statusFilter"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100">
                                <option value="all">Semua Penugasan</option>
                                <option value="filled">Sudah Mengisi Jurnal</option>
                                <option value="unfilled">Belum Pernah Mengisi</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">4. Guru (Opsional)</label>
                            <select wire:model.live="selectedTeacherId"
                                @disabled(empty($selectedClassId))
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50">
                                <option value="">Semua Guru Kelas Ini</option>
                                @foreach($availableTeachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">5. Mapel (Opsional)</label>
                            <select wire:model.live="selectedMapelId"
                                @disabled(empty($selectedClassId))
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50">
                                <option value="">Semua Mata Pelajaran</option>
                                @foreach($availableMapels as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <x-filament::button wire:click="prosesData" icon="heroicon-o-magnifying-glass" color="primary" class="w-full justify-center">
                            Tampilkan Data
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>

        @if(!$isProcessed || empty($selectedClassId))
            <x-filament::section>
                <div class="py-12 text-center space-y-2">
                    <div class="mx-auto w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-950/50 flex items-center justify-center text-primary-600 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-funnel" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Pilih Filter & Klik "Tampilkan Data"</h3>
                    <p class="text-sm text-gray-500 max-w-md mx-auto">
                        Silakan pilih <strong>Periode Waktu</strong> (Semester / Bulan) dan <strong>Kelas</strong> terlebih dahulu, lalu klik tombol <strong>Tampilkan Data</strong> untuk memuat hasil monitoring secara instan.
                    </p>
                </div>
            </x-filament::section>
        @else
            {{-- ===== KPI SUMMARY CARDS ===== --}}
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xs">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Mapel Kelas {{ $selectedClassObj?->name }}</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">{{ $kpiTotalPenugasan }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Penugasan Guru – Mapel</div>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50 shadow-xs">
                    <div class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Sudah Isi Jurnal</div>
                    <div class="mt-1 text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ $kpiSudahIsi }}</div>
                    <div class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">Mapel aktif tercatat</div>
                </div>

                <div class="p-4 rounded-xl bg-rose-50/70 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 shadow-xs">
                    <div class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">Belum Isi Jurnal</div>
                    <div class="mt-1 text-2xl font-extrabold text-rose-700 dark:text-rose-300">{{ $kpiBelumIsi }}</div>
                    <div class="text-xs text-rose-600/80 dark:text-rose-400/80 mt-0.5">0 sesi pada periode ini</div>
                </div>

                <div class="p-4 rounded-xl bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/50 shadow-xs">
                    <div class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-400">Total Pertemuan</div>
                    <div class="mt-1 text-2xl font-extrabold text-blue-700 dark:text-blue-300">{{ $kpiTotalSesi }}</div>
                    <div class="text-xs text-blue-600/80 dark:text-blue-400/80 mt-0.5">Sesi tatap muka kelas ini</div>
                </div>

                <div class="p-4 rounded-xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 shadow-xs">
                    <div class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Total Jam Pelajaran</div>
                    <div class="mt-1 text-2xl font-extrabold text-amber-700 dark:text-amber-300">{{ $kpiTotalJp }} <span class="text-sm font-semibold">JP</span></div>
                    <div class="text-xs text-amber-600/80 dark:text-amber-400/80 mt-0.5">Akumulasi durasi mengajar</div>
                </div>
            </div>

            @if($activeTab === 'guru')
                {{-- ===== TAB 1: KEAKTIFAN GURU & REALISASI JP ===== --}}
                <x-filament::section>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50">
                                    <th class="py-3 px-4">No</th>
                                    <th class="py-3 px-4">Guru Pengampu</th>
                                    <th class="py-3 px-4">Mata Pelajaran</th>
                                    <th class="py-3 px-4">Kelas</th>
                                    <th class="py-3 px-4 text-center">Pertemuan</th>
                                    <th class="py-3 px-4 text-center">Total JP</th>
                                    <th class="py-3 px-4 text-center">Rata-rata Hadir</th>
                                    <th class="py-3 px-4">Input Terakhir & Materi</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Cetak Rekap</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @forelse($filteredGuruRows as $idx => $row)
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                        <td class="py-3 px-4 text-gray-500">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ $row['teacher_name'] }}</td>
                                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">{{ $row['mapel_name'] }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ $row['class_name'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold">{{ $row['total_sesi'] }}x</td>
                                        <td class="py-3 px-4 text-center font-bold text-primary-600 dark:text-primary-400">{{ $row['total_jp'] }} JP</td>
                                        <td class="py-3 px-4 text-center">
                                            @if($row['total_sesi'] > 0)
                                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $row['avg_kehadiran'] >= 90 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                                    {{ $row['avg_kehadiran'] }}%
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($row['last_date'])
                                                <div class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $row['last_date'] }}</div>
                                                <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $row['last_materi'] }}">{{ $row['last_materi'] }}</div>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Belum ada jurnal</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($row['total_sesi'] > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                    Sudah Mengisi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                    Belum Mengisi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($row['total_sesi'] > 0)
                                                <div class="inline-flex items-center gap-1">
                                                    <a href="{{ route('portal-guru.jurnal-mengajar.cetak', array_filter(['academic_year_id' => $selectedAcademicYearId, 'teacher_id' => $row['teacher_id'], 'class_id' => $row['class_id'], 'mata_pelajaran_id' => $row['mata_pelajaran_id'], 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                                                       target="_blank"
                                                       class="px-2 py-1 rounded text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-500">
                                                        PDF
                                                    </a>
                                                    <a href="{{ route('portal-guru.jurnal-mengajar.excel', array_filter(['academic_year_id' => $selectedAcademicYearId, 'teacher_id' => $row['teacher_id'], 'class_id' => $row['class_id'], 'mata_pelajaran_id' => $row['mata_pelajaran_id'], 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                                                       target="_blank"
                                                       class="px-2 py-1 rounded text-xs font-semibold bg-blue-600 text-white hover:bg-blue-500">
                                                        Excel
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-10 text-center text-gray-500">
                                            Belum ada data penugasan mengajar atau jurnal untuk filter ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @else
                {{-- ===== TAB 2: REKAP KBM PER KELAS (ALA PORTAL SISWA) ===== --}}
                <x-filament::section>
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4 pb-3 border-b border-gray-200 dark:border-gray-800">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Rekapitulasi Mata Pelajaran — Kelas {{ $selectedClassObj?->name ?? '-' }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Wali Kelas: <strong>{{ $waliKelasName }}</strong> • Menampilkan realisasi KBM setiap mata pelajaran pada periode terpilih
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($kelasSubjectRows as $sub)
                            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex flex-col justify-between gap-3 shadow-xs">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ $sub['mapel_name'] }}</h4>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $sub['teacher_name'] }}</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $sub['total_sesi'] > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                            {{ $sub['total_sesi'] }} Pertemuan ({{ $sub['total_jp'] }} JP)
                                        </span>
                                    </div>

                                    <div class="mt-3 p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/60 text-xs space-y-1">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Rata-rata Kehadiran Kelas:</span>
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $sub['total_sesi'] > 0 ? $sub['avg_kehadiran'] . '%' : '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Total Kejadian Tidak Ikut / Bolos:</span>
                                            <span class="font-bold {{ $sub['sum_tidak_mengikuti'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-600 dark:text-gray-400' }}">
                                                {{ $sub['sum_tidak_mengikuti'] }} Kejadian
                                            </span>
                                        </div>
                                        <div class="pt-1 border-t border-gray-200/70 dark:border-gray-700/70">
                                            <span class="text-gray-500 block">Materi Terakhir ({{ $sub['last_date'] ?? 'Belum ada' }}):</span>
                                            <span class="font-medium text-gray-800 dark:text-gray-200 line-clamp-1">{{ $sub['last_materi'] ?? 'Belum ada jurnal diinput' }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($sub['total_sesi'] > 0)
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                        <a href="{{ route('portal-guru.jurnal-mengajar.cetak', array_filter(['academic_year_id' => $selectedAcademicYearId, 'teacher_id' => $sub['teacher_id'], 'class_id' => $sub['class_id'], 'mata_pelajaran_id' => $sub['mata_pelajaran_id'], 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                                           target="_blank"
                                           class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-500">
                                            Cetak PDF
                                        </a>
                                        <a href="{{ route('portal-guru.jurnal-mengajar.excel', array_filter(['academic_year_id' => $selectedAcademicYearId, 'teacher_id' => $sub['teacher_id'], 'class_id' => $sub['class_id'], 'mata_pelajaran_id' => $sub['mata_pelajaran_id'], 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                                           target="_blank"
                                           class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 text-white hover:bg-blue-500">
                                            Unduh Excel
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-full py-10 text-center text-gray-500">
                                Belum ada mata pelajaran / jurnal untuk kelas ini.
                            </div>
                        @endforelse
                    </div>
                </x-filament::section>
            @endif
        @endif

    </div>
</x-filament-panels::page>
