<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== FILTER BAR ===== --}}
        <x-filament::section>
            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 dark:border-gray-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Filter Analisa Presensi KBM & Deteksi Siswa Bolos Jam</h3>
                        <p class="text-xs text-gray-500">Pilih Periode Waktu, Kelas, Mata Pelajaran, dan Fokus Tampilan secara berurutan, lalu klik <strong>Proses Analisa</strong>.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                        <x-filament::icon icon="heroicon-o-calendar" class="w-3.5 h-3.5" />
                        Tahun Ajaran Aktif: {{ $activeYearName }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">1. Periode Waktu <span class="text-rose-500">*</span></label>
                        <select wire:model.live="selectedPeriod"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100">
                            <option value="">— Pilih Periode Waktu —</option>
                            @foreach($periodOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">2. Pilih Kelas <span class="text-rose-500">*</span></label>
                        <select wire:model.live="selectedClassId"
                            @disabled(empty($selectedPeriod))
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">— Pilih Kelas —</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">3. Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select wire:model.live="selectedMapelId"
                            @disabled(empty($selectedPeriod) || empty($selectedClassId))
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($availableMapels as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">4. Fokus Tampilan <span class="text-rose-500">*</span></label>
                        <select wire:model.live="focusFilter"
                            @disabled(empty($selectedPeriod) || empty($selectedClassId) || empty($selectedMapelId))
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">— Pilih Fokus Tampilan —</option>
                            <option value="all">Seluruh Siswa Kelas Ini</option>
                            <option value="bolos_only">Pernah Tidak Ikut / Bolos Jam</option>
                            <option value="absent_only">Semua Pernah Absen (S/I/A/Bolos)</option>
                        </select>
                    </div>

                    {{-- Searchable Dropdown Siswa / NISN --}}
                    @php
                        $canSearchStudent = !empty($selectedPeriod) && !empty($selectedClassId) && !empty($selectedMapelId) && !empty($focusFilter);
                    @endphp
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">5. Cari Siswa / NISN (Opsional)</label>
                        <div class="relative">
                            <input type="text"
                                wire:model.live.debounce.250ms="search"
                                @disabled(!$canSearchStudent)
                                @focus="if ({{ $canSearchStudent ? 'true' : 'false' }}) open = true"
                                @click="if ({{ $canSearchStudent ? 'true' : 'false' }}) open = true"
                                placeholder="Klik atau ketik Nama / NISN..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm pl-3 pr-8 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" />
                            @if(!empty($search) || !empty($selectedStudentId))
                                <button type="button"
                                    wire:click="clearStudentSelection"
                                    @click="open = true"
                                    title="Reset pencarian siswa"
                                    class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4" />
                                </button>
                            @endif
                        </div>

                        <div x-show="open"
                             x-transition
                             style="display: none;"
                             class="absolute z-50 mt-1 w-full min-w-[260px] max-h-64 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg divide-y divide-gray-100 dark:divide-gray-800">
                            <button type="button"
                                wire:click="clearStudentSelection"
                                @click="open = false"
                                class="w-full text-left px-3 py-2 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40">
                                — Semua Siswa {{ !empty($selectedClassId) ? 'di Kelas Ini' : '' }} —
                            </button>

                            @forelse($studentOptions as $opt)
                                <button type="button"
                                    wire:click="selectStudent('{{ $opt['id'] }}', '{{ addslashes($opt['name']) }} ({{ $opt['nisn'] }})', '{{ $opt['class_id'] }}')"
                                    @click="open = false"
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center justify-between gap-2 {{ $selectedStudentId === $opt['id'] ? 'bg-primary-50/70 dark:bg-primary-950/30 font-bold' : '' }}">
                                    <div class="min-w-0">
                                        <div class="text-gray-900 dark:text-white font-semibold truncate">{{ $opt['name'] }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono">NISN: {{ $opt['nisn'] }}</div>
                                    </div>
                                    <span class="shrink-0 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ $opt['class_name'] }}
                                    </span>
                                </button>
                            @empty
                                <div class="px-3 py-3 text-xs text-gray-500 text-center">
                                    Siswa tidak ditemukan.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <x-filament::button
                            wire:click="prosesAnalisa"
                            :disabled="!$canSearchStudent"
                            icon="heroicon-o-play"
                            color="primary"
                            class="w-full justify-center">
                            Proses Analisa
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>

        @if(!$isProcessed || empty($selectedPeriod) || empty($selectedClassId) || empty($selectedMapelId) || empty($focusFilter))
            <x-filament::section>
                <div class="py-12 text-center space-y-2">
                    <div class="mx-auto w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-950/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <x-filament::icon icon="heroicon-o-shield-exclamation" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Lengkapi Filter &amp; Klik "Proses Analisa"</h3>
                    <p class="text-sm text-gray-500 max-w-md mx-auto">
                        Pilih <strong>Periode Waktu</strong>, <strong>Kelas</strong>, <strong>Mata Pelajaran</strong>, dan <strong>Fokus Tampilan</strong> secara berurutan, lalu klik tombol <strong>Proses Analisa</strong> untuk menampilkan hasil deteksi kehadiran KBM siswa.
                    </p>
                </div>
            </x-filament::section>
        @else
            {{-- ===== TABEL ANALISA PRESENSI KBM SISWA ===== --}}
            <x-filament::section>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50">
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Siswa & NISN</th>
                                <th class="py-3 px-4 text-center">Kelas</th>
                                <th class="py-3 px-4 text-center">Pertemuan</th>
                                <th class="py-3 px-4 text-center">Hadir</th>
                                <th class="py-3 px-4 text-center">Sakit</th>
                                <th class="py-3 px-4 text-center">Izin</th>
                                <th class="py-3 px-4 text-center">Alpa</th>
                                <th class="py-3 px-4 text-center">Tidak Ikut (Bolos)</th>
                                <th class="py-3 px-4 text-center">% KBM</th>
                                <th class="py-3 px-4">Rincian Sesi Tidak Mengikuti / Absen KBM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($studentRows as $idx => $row)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40 align-top">
                                    <td class="py-3 px-4 text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $row['student_name'] }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $row['nisn'] }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ $row['class_name'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-semibold">{{ $row['total_pertemuan'] }}</td>
                                    <td class="py-3 px-4 text-center text-emerald-600 dark:text-emerald-400 font-bold">{{ $row['hadir'] }}</td>
                                    <td class="py-3 px-4 text-center">{{ $row['sakit'] }}</td>
                                    <td class="py-3 px-4 text-center">{{ $row['izin'] }}</td>
                                    <td class="py-3 px-4 text-center">{{ $row['alpa'] }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row['tidak_mengikuti'] > 0)
                                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                                {{ $row['tidak_mengikuti'] }}x Bolos
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row['total_pertemuan'] > 0)
                                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $row['persentase_hadir'] >= 90 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : ($row['persentase_hadir'] >= 75 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300') }}">
                                                {{ $row['persentase_hadir'] }}%
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if(count($row['incidents']) > 0)
                                            <div class="space-y-1.5 max-w-md">
                                                @foreach($row['incidents'] as $inc)
                                                    <div class="text-xs p-2 rounded-lg border {{ $inc['norm'] === 'tidak_mengikuti' ? 'bg-rose-50/70 border-rose-200 dark:bg-rose-950/20 dark:border-rose-900/40' : 'bg-gray-50 border-gray-200 dark:bg-gray-800/50 dark:border-gray-700' }}">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span class="font-bold text-gray-900 dark:text-white">{{ $inc['tanggal'] }} • {{ $inc['jam'] }}</span>
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $inc['norm'] === 'tidak_mengikuti' ? 'bg-rose-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                                                {{ $inc['label'] }}
                                                            </span>
                                                        </div>
                                                        <div class="text-gray-600 dark:text-gray-300 mt-0.5">
                                                            <strong>{{ $inc['mapel'] }}</strong> ({{ $inc['guru'] }}) — Gerbang: <span class="font-semibold">{{ $inc['gerbang'] }}</span>
                                                        </div>
                                                        @if(!empty($inc['keterangan']))
                                                            <div class="text-gray-500 italic mt-0.5">"{{ $inc['keterangan'] }}"</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($row['total_pertemuan'] === 0)
                                            <span class="text-xs text-gray-400 italic">Belum ada sesi KBM</span>
                                        @else
                                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">100% Mengikuti KBM</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-10 text-center text-gray-500">
                                        Tidak ditemukan data siswa yang sesuai dengan filter ini di Kelas {{ $selectedKelas?->name }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
