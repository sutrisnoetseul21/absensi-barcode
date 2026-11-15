<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== TAB SWITCHER & FILTER BAR ===== --}}
        <x-filament::section>
            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="setTab('guru_wali')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'guru_wali' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            <x-filament::icon icon="heroicon-o-user-group" class="w-4 h-4" />
                            Monitoring Kepatuhan Guru Wali
                        </button>
                        <button type="button" wire:click="setTab('kelompok')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'kelompok' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            <x-filament::icon icon="heroicon-o-users" class="w-4 h-4" />
                            Monitoring Anggota per Kelompok
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Tahun Ajaran:</label>
                        <select wire:model.live="selectedAcademicYearId"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs font-semibold px-3 py-1.5 text-gray-900 dark:text-gray-100">
                            @foreach($academicYearOptions as $ayId => $ayLabel)
                                <option value="{{ $ayId }}">{{ $ayLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 {{ $activeTab === 'guru_wali' ? 'lg:grid-cols-3' : 'lg:grid-cols-4' }} gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">1. Periode Waktu <span class="text-rose-500">*</span></label>
                        <select wire:model.live="selectedPeriod"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100">
                            <option value="">— Pilih Bulan / Semester / Tahun —</option>
                            @foreach($periodOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($activeTab === 'guru_wali')
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">2. Status Kepatuhan (Min. 1x/Bulan) <span class="text-rose-500">*</span></label>
                            <select wire:model.live="complianceFilter"
                                @disabled(empty($selectedPeriod))
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <option value="">— Pilih Status Kepatuhan —</option>
                                <option value="all">Semua Kelompok Aktif</option>
                                <option value="complete">Lengkap 100% Siswa Dibimbing</option>
                                <option value="incomplete">Belum Lengkap (&lt; 100%)</option>
                                <option value="empty">Belum Ada Sesi Sama Sekali (0)</option>
                            </select>
                        </div>

                        <div>
                            <x-filament::button wire:click="prosesData" icon="heroicon-o-magnifying-glass" color="primary" class="w-full justify-center">
                                Tampilkan Data
                            </x-filament::button>
                        </div>
                    @else
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">2. Pilih Kelompok / Guru Wali <span class="text-rose-500">*</span></label>
                            <select wire:model.live="selectedKelompokId"
                                @disabled(empty($selectedPeriod))
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <option value="">— Pilih Kelompok Guru Wali —</option>
                                @foreach($allKelompok as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama_kelompok }} — {{ $k->guru?->name ?? '-' }} ({{ $k->anggota_aktif_count }} siswa)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-filament::button wire:click="prosesData" icon="heroicon-o-magnifying-glass" color="primary" class="w-full justify-center">
                                Tampilkan Data
                            </x-filament::button>
                        </div>
                    @endif
                </div>
            </div>
        </x-filament::section>

        @if($activeTab === 'guru_wali')
            @if(!$isProcessed || empty($selectedPeriod) || empty($complianceFilter))
                <x-filament::section>
                    <div class="py-12 text-center space-y-2">
                        <div class="mx-auto w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-950/50 flex items-center justify-center text-primary-600 dark:text-primary-400">
                            <x-filament::icon icon="heroicon-o-funnel" class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Pilih Filter &amp; Klik "Tampilkan Data"</h3>
                        <p class="text-sm text-gray-500 max-w-md mx-auto">
                            Silakan pilih <strong>Periode Waktu</strong> (Bulan / Semester / Tahun) dan <strong>Status Kepatuhan</strong> terlebih dahulu, lalu klik <strong>Tampilkan Data</strong> untuk memuat monitoring secara hemat resource.
                        </p>
                    </div>
                </x-filament::section>
            @else
                <x-filament::section>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4 pb-3 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            Rekap Kepatuhan Guru Wali — {{ $periodLabel }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Mengacu indikator pendampingan minimal 1x per bulan untuk setiap siswa binaan. Klik angka siswa belum dibimbing untuk melihat daftar nama.
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-gray-500">
                        Total ditampilkan: {{ $guruRows->count() }} kelompok
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50">
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Guru Wali &amp; Kelompok</th>
                                <th class="py-3 px-4 text-center">Siswa Binaan</th>
                                <th class="py-3 px-4 text-center">Sudah Dibimbing</th>
                                <th class="py-3 px-4 text-center">Belum Dibimbing</th>
                                <th class="py-3 px-4 text-center">Cakupan</th>
                                <th class="py-3 px-4 text-center">Total Sesi</th>
                                <th class="py-3 px-4 text-center">Konsultasi Siswa</th>
                                <th class="py-3 px-4 text-center">Rujukan BK</th>
                                <th class="py-3 px-4">Input Terakhir</th>
                                <th class="py-3 px-4 text-right">Aksi &amp; Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($guruRows as $idx => $row)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                    <td class="py-3 px-4 text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $row['guru_name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $row['nama_kelompok'] }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold">{{ $row['total_siswa'] }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            {{ $row['siswa_dibimbing'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row['siswa_belum'] > 0)
                                            <button type="button" wire:click="showUnservedModal('{{ $row['kelompok_id'] }}')"
                                                class="px-2.5 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-700 hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-300 transition underline decoration-dotted">
                                                {{ $row['siswa_belum'] }} siswa
                                            </button>
                                        @else
                                            <span class="text-xs font-semibold text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @php
                                            $covClass = $row['coverage'] >= 100
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                                                : ($row['coverage'] >= 50
                                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'
                                                    : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300');
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $covClass }}">
                                            {{ $row['coverage'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $row['total_sesi'] }}x</div>
                                        @if($row['total_sesi'] > 0)
                                            <div class="text-[11px] text-gray-500">Ind: {{ $row['sesi_individu'] }} · Kel: {{ $row['sesi_kelompok'] }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row['total_konsultasi'] > 0)
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ $row['konsultasi_selesai'] }}/{{ $row['total_konsultasi'] }} selesai
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row['rujukan_bk'] > 0)
                                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                                                {{ $row['rujukan_bk'] }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-600 dark:text-gray-300">
                                        {{ $row['last_date'] ?? 'Belum ada jurnal' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" wire:click="inspectKelompok('{{ $row['kelompok_id'] }}')"
                                                class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                                                Detail
                                            </button>
                                            <a href="{{ route('akademik.guru-wali.cetak.kelompok.pdf', ['academic_year_id' => $activeYearId, 'periode' => $selectedPeriod, 'kelompok_id' => $row['kelompok_id']]) }}"
                                               target="_blank"
                                               class="px-2 py-1 rounded text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-500">
                                                PDF
                                            </a>
                                            <a href="{{ route('akademik.guru-wali.export.excel', ['academic_year_id' => $activeYearId, 'periode' => $selectedPeriod, 'kelompok_id' => $row['kelompok_id']]) }}"
                                               target="_blank"
                                               class="px-2 py-1 rounded text-xs font-semibold bg-blue-600 text-white hover:bg-blue-500">
                                                Excel
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-10 text-center text-gray-500">
                                        Tidak ada data kelompok Guru Wali yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
            @endif
        @else
            @if(!$isProcessed || empty($selectedPeriod) || empty($selectedKelompokId))
                <x-filament::section>
                    <div class="py-12 text-center space-y-2">
                        <div class="mx-auto w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-950/50 flex items-center justify-center text-primary-600 dark:text-primary-400">
                            <x-filament::icon icon="heroicon-o-funnel" class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Pilih Kelompok &amp; Klik "Tampilkan Data"</h3>
                        <p class="text-sm text-gray-500 max-w-md mx-auto">
                            Silakan pilih <strong>Periode Waktu</strong> dan <strong>Kelompok Guru Wali</strong>, lalu klik <strong>Tampilkan Data</strong> untuk memantau status pendampingan setiap anggota siswa.
                        </p>
                    </div>
                </x-filament::section>
            @else
                <x-filament::section>
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4 pb-3 border-b border-gray-200 dark:border-gray-800">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                {{ $selectedKelompokObj?->nama_kelompok ?? '-' }} — {{ $periodLabel }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Guru Wali: <strong>{{ $selectedKelompokObj?->guru?->name ?? '-' }}</strong> · Total Anggota Aktif: <strong>{{ $kelompokMembers->count() }} siswa</strong>
                            </p>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('akademik.guru-wali.cetak.kelompok.pdf', ['academic_year_id' => $activeYearId, 'periode' => $selectedPeriod, 'kelompok_id' => $selectedKelompokId]) }}"
                               target="_blank"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-500">
                                PDF Laporan Kelompok
                            </a>
                            <a href="{{ route('akademik.guru-wali.cetak.individual.massal.pdf', ['academic_year_id' => $activeYearId, 'periode' => $selectedPeriod, 'kelompok_id' => $selectedKelompokId]) }}"
                               target="_blank"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-600 text-white hover:bg-amber-500">
                                PDF Individual Massal
                            </a>
                            <a href="{{ route('akademik.guru-wali.export.excel', ['academic_year_id' => $activeYearId, 'periode' => $selectedPeriod, 'kelompok_id' => $selectedKelompokId]) }}"
                               target="_blank"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 text-white hover:bg-blue-500">
                                Excel Rekap
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50">
                                    <th class="py-3 px-4">No</th>
                                    <th class="py-3 px-4">Nama Siswa &amp; NISN</th>
                                    <th class="py-3 px-4 text-center">Kelas</th>
                                    <th class="py-3 px-4 text-center">Sesi Periode Ini</th>
                                    <th class="py-3 px-4">Pilar Dominan</th>
                                    <th class="py-3 px-4">Status Sesi Terakhir</th>
                                    <th class="py-3 px-4">Pendampingan Terakhir</th>
                                    <th class="py-3 px-4 text-center">Konsultasi</th>
                                    <th class="py-3 px-4 text-right">Cetak</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @forelse($kelompokMembers as $idx => $m)
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                        <td class="py-3 px-4 text-gray-500">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $m['name'] }}</div>
                                            <div class="text-xs text-gray-500">NISN: {{ $m['nisn'] }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                {{ $m['kelas'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($m['is_served'])
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                    {{ $m['total_sesi'] }}x
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                    Belum (0x)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-xs text-gray-700 dark:text-gray-300">{{ $m['pilar_dominan'] }}</td>
                                        <td class="py-3 px-4 text-xs">
                                            <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $m['last_status'] }}</div>
                                            <div class="text-gray-500">{{ $m['last_date'] }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-gray-400">{{ $m['last_ever_date'] }}</td>
                                        <td class="py-3 px-4 text-center text-xs font-semibold">{{ $m['total_konsultasi'] }}x</td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('akademik.guru-wali.cetak.individual.pdf', ['academic_year_id' => $activeYearId, 'periode' => $selectedPeriod, 'kelompok_id' => $selectedKelompokId, 'student_id' => $m['student_id']]) }}"
                                               target="_blank"
                                               class="px-2.5 py-1 rounded text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-500">
                                                PDF Siswa
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-10 text-center text-gray-500">
                                            Belum ada anggota siswa aktif di kelompok ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @endif
        @endif

        {{-- ===== MODAL DAFTAR SISWA BELUM DIBIMBING ===== --}}
        @if($unservedModalData)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4" wire:click.self="closeUnservedModal">
                <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                        <div>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                Siswa Belum Dibimbing — {{ $unservedModalData['nama_kelompok'] }}
                            </h4>
                            <p class="text-xs text-gray-500">
                                Guru Wali: {{ $unservedModalData['guru_name'] }} · Periode: {{ $unservedModalData['period_label'] }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeUnservedModal" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="max-h-96 overflow-y-auto p-5">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500">
                                    <th class="py-2 px-3">No</th>
                                    <th class="py-2 px-3">Nama Siswa</th>
                                    <th class="py-2 px-3">Kelas</th>
                                    <th class="py-2 px-3">Pendampingan Terakhir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($unservedModalData['students'] as $i => $st)
                                    <tr>
                                        <td class="py-2 px-3 text-gray-500">{{ $i + 1 }}</td>
                                        <td class="py-2 px-3">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $st['name'] }}</div>
                                            <div class="text-xs text-gray-500">NISN: {{ $st['nisn'] }}</div>
                                        </td>
                                        <td class="py-2 px-3 text-xs font-semibold">{{ $st['kelas'] }}</td>
                                        <td class="py-2 px-3 text-xs text-gray-500">{{ $st['last_ever'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-emerald-600 font-semibold">
                                            Semua siswa pada kelompok ini sudah didampingi pada periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>

