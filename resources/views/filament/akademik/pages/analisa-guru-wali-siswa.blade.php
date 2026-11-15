<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== FILTER CASCADING ===== --}}
        <x-filament::section>
            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 dark:border-gray-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Filter Analisa Bimbingan Guru Wali &amp; Konsultasi Siswa</h3>
                        <p class="text-xs text-gray-500">Pilih Periode Waktu, Lingkup (Kelas atau Kelompok), Target, dan Fokus Tampilan secara berurutan, lalu klik <strong>Proses Analisa</strong>.</p>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">1. Periode Waktu <span class="text-rose-500">*</span></label>
                        <select wire:model.live="selectedPeriod"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100">
                            <option value="">— Pilih Periode —</option>
                            @foreach($periodOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">2. Lingkup Data <span class="text-rose-500">*</span></label>
                        <select wire:model.live="scopeType"
                            @disabled(empty($selectedPeriod))
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">— Pilih Lingkup —</option>
                            <option value="kelas">Per Kelas</option>
                            <option value="kelompok">Per Kelompok Guru Wali</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                            3. {{ $scopeType === 'kelompok' ? 'Pilih Kelompok' : 'Pilih Kelas' }} <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.live="scopeTargetId"
                            @disabled(empty($selectedPeriod) || empty($scopeType))
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">— {{ $scopeType === 'kelompok' ? 'Pilih Kelompok' : 'Pilih Kelas' }} —</option>
                            @if($scopeType === 'kelas')
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                                @endforeach
                            @elseif($scopeType === 'kelompok')
                                @foreach($kelompokList as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelompok }} — {{ $k->guru?->name ?? '-' }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">4. Fokus Tampilan <span class="text-rose-500">*</span></label>
                        <select wire:model.live="focusFilter"
                            @disabled(empty($selectedPeriod) || empty($scopeType) || empty($scopeTargetId))
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm px-3 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">— Pilih Fokus —</option>
                            <option value="all">Seluruh Siswa</option>
                            <option value="unserved">Belum Terlayani (0 Sesi)</option>
                            <option value="attention">Butuh Perhatian / Pemantauan / Rujukan</option>
                            <option value="consult_active">Pernah Ajukan Konsultasi Portal</option>
                        </select>
                    </div>

                    @php
                        $canSearchStudent = !empty($selectedPeriod) && !empty($scopeType) && !empty($scopeTargetId) && !empty($focusFilter);
                    @endphp
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">5. Cari Siswa (Opsional)</label>
                        <div class="relative">
                            <input type="text"
                                wire:model.live.debounce.250ms="search"
                                @disabled(!$canSearchStudent)
                                @focus="if ({{ $canSearchStudent ? 'true' : 'false' }}) open = true"
                                @click="if ({{ $canSearchStudent ? 'true' : 'false' }}) open = true"
                                placeholder="Ketik Nama / NISN..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm pl-3 pr-8 py-2 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" />
                            @if(!empty($search) || !empty($selectedStudentId))
                                <button type="button"
                                    wire:click="clearStudentSelection"
                                    @click="open = true"
                                    class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600">
                                    <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4" />
                                </button>
                            @endif
                        </div>

                        <div x-show="open" x-transition style="display: none;"
                            class="absolute z-50 mt-1 w-full min-w-[260px] max-h-60 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg divide-y divide-gray-100 dark:divide-gray-800">
                            <button type="button"
                                wire:click="selectStudent(null, '')"
                                @click="open = false"
                                class="w-full text-left px-3 py-2 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-gray-50 dark:hover:bg-gray-800">
                                — Semua Siswa pada Lingkup Ini —
                            </button>
                            @foreach($studentDropdownOptions as $st)
                                <button type="button"
                                    wire:click="selectStudent('{{ $st['id'] }}', '{{ addslashes($st['name']) }}')"
                                    @click="open = false"
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $st['name'] }}</div>
                                    <div class="text-gray-500">NISN: {{ $st['nisn'] }}</div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <x-filament::button wire:click="prosesAnalisa" icon="heroicon-o-sparkles" color="primary" class="w-full justify-center">
                            Proses Analisa
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>

        @if(!$isProcessed)
            <x-filament::section>
                <div class="py-12 text-center space-y-2">
                    <div class="mx-auto w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-950/50 flex items-center justify-center text-primary-600 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-chart-pie" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Siap Memproses Analisa Bimbingan Guru Wali</h3>
                    <p class="text-sm text-gray-500 max-w-lg mx-auto">
                        Lengkapi filter <strong>Periode Waktu</strong>, <strong>Lingkup &amp; Target</strong>, serta <strong>Fokus Tampilan</strong> di atas, lalu klik <strong>Proses Analisa</strong>.
                    </p>
                </div>
            </x-filament::section>
        @else
            {{-- ===== RINGKASAN 4 PILAR, SENTIMEN & KONSULTASI ===== --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <x-filament::section>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Peta Isu 4 Pilar ({{ $periodLabel }})</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        @foreach($summary['pilar'] as $pilarName => $pilarCount)
                            <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200/70 dark:border-gray-700/60">
                                <div class="text-gray-500 truncate" title="{{ $pilarName }}">{{ $pilarName }}</div>
                                <div class="text-base font-bold text-gray-900 dark:text-white mt-0.5">{{ $pilarCount }} sesi</div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Sentimen Catatan Jurnal</h4>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/50 text-center">
                            <div class="text-emerald-700 dark:text-emerald-300 font-semibold">Positif</div>
                            <div class="text-lg font-bold text-emerald-800 dark:text-emerald-200 mt-0.5">{{ $summary['sentimen']['Positif'] }}</div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200/70 dark:border-gray-700/60 text-center">
                            <div class="text-gray-600 dark:text-gray-300 font-semibold">Netral</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ $summary['sentimen']['Netral'] }}</div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-900/50 text-center">
                            <div class="text-rose-700 dark:text-rose-300 font-semibold">Negatif</div>
                            <div class="text-lg font-bold text-rose-800 dark:text-rose-200 mt-0.5">{{ $summary['sentimen']['Negatif'] }}</div>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Konsultasi Portal Siswa &amp; Refleksi</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Total Tiket / Selesai</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $summary['konsultasi']['total'] }} tiket ({{ $summary['konsultasi']['selesai'] }} selesai, {{ $summary['konsultasi']['aktif'] }} aktif)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Rata-rata Rating Siswa</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ $summary['konsultasi']['avg_rating'] }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 font-semibold">Lega: {{ $summary['emoji']['Lega'] }}</span>
                            <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 font-semibold">Biasa: {{ $summary['emoji']['Biasa'] }}</span>
                            <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 font-semibold">Masih Bingung: {{ $summary['emoji']['Masih Bingung'] }}</span>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            {{-- ===== TABEL ANALISA SISWA ===== --}}
            <x-filament::section>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4 pb-3 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Hasil Analisa Pendampingan Siswa</h3>
                        <p class="text-xs text-gray-500">Menampilkan {{ $rows->count() }} siswa pada periode {{ $periodLabel }}.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50">
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Siswa &amp; Kelas</th>
                                <th class="py-3 px-4">Guru Wali &amp; Kelompok</th>
                                <th class="py-3 px-4 text-center">Jurnal</th>
                                <th class="py-3 px-4">Status &amp; Sentimen Terakhir</th>
                                <th class="py-3 px-4">Konsultasi &amp; Refleksi</th>
                                <th class="py-3 px-4">Rujukan &amp; Badge</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($rows as $idx => $r)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                    <td class="py-3 px-4 text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $r['name'] }}</div>
                                        <div class="text-xs text-gray-500">NISN: {{ $r['nisn'] }} · Kelas {{ $r['kelas_name'] }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $r['guru_wali'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $r['kelompok'] }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($r['total_jurnal'] > 0)
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                {{ $r['total_jurnal'] }}x
                                            </span>
                                            <div class="text-[11px] text-gray-500 mt-0.5">{{ $r['last_tanggal'] }}</div>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                0x
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs">
                                        <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $r['last_status'] }}</div>
                                        <div class="text-gray-500">
                                            {{ $r['last_pilar'] }}
                                            @if($r['last_sentimen'])
                                                · <span class="font-semibold {{ $r['last_sentimen'] === 'Negatif' ? 'text-rose-600' : ($r['last_sentimen'] === 'Positif' ? 'text-emerald-600' : 'text-gray-500') }}">{{ $r['last_sentimen'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-xs">
                                        @if($r['total_konsultasi'] > 0)
                                            <div class="font-semibold text-blue-700 dark:text-blue-300">{{ $r['total_konsultasi'] }} tiket ({{ $r['last_konsultasi_status'] }})</div>
                                            @if($r['last_rating'] || $r['last_emoji'])
                                                <div class="text-gray-500">
                                                    {{ $r['last_rating'] ? "Rating {$r['last_rating']}/5" : '' }}
                                                    {{ $r['last_emoji'] ? "· {$r['last_emoji']}" : '' }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">Belum ada pengajuan</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs">
                                        <div class="flex flex-wrap gap-1">
                                            @if($r['last_rujukan'] !== 'Mandiri')
                                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 font-semibold">{{ $r['last_rujukan'] }}</span>
                                            @endif
                                            @if($r['has_bk'])
                                                <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 font-semibold">Proses BK</span>
                                            @endif
                                            @if($r['total_badge'] > 0)
                                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-semibold">{{ $r['total_badge'] }} Badge</span>
                                            @endif
                                            @if($r['last_rujukan'] === 'Mandiri' && !$r['has_bk'] && $r['total_badge'] === 0)
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" wire:click="openTimeline('{{ $r['student_id'] }}')"
                                                class="px-2.5 py-1 rounded text-xs font-semibold bg-primary-600 text-white hover:bg-primary-500">
                                                Linimasa
                                            </button>
                                            @if($r['kelompok_id'])
                                                <a href="{{ route('akademik.guru-wali.cetak.individual.pdf', ['academic_year_id' => $activeYearId, 'periode' => $selectedPeriod, 'kelompok_id' => $r['kelompok_id'], 'student_id' => $r['student_id']]) }}"
                                                   target="_blank"
                                                   class="px-2.5 py-1 rounded text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-500">
                                                    PDF
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-gray-500">
                                        Tidak ada data siswa yang sesuai dengan kriteria analisa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        {{-- ===== MODAL LINIMASA PENDAMPINGAN SISWA ===== --}}
        @if($timelineModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4" wire:click.self="closeTimeline">
                <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                        <div>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                Linimasa Pendampingan — {{ $timelineModal['name'] }}
                            </h4>
                            <p class="text-xs text-gray-500">
                                Kelas {{ $timelineModal['kelas'] }} · Guru Wali: {{ $timelineModal['guru_wali'] }} ({{ $timelineModal['kelompok'] }}) · {{ $timelineModal['period_label'] }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeTimeline" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto p-5 space-y-5 text-sm">
                        @if(count($timelineModal['badges']) > 0)
                            <div>
                                <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Badge Karakter Diterima</h5>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($timelineModal['badges'] as $b)
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            ★ {{ $b['nama'] }} ({{ $b['tanggal'] }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Catatan Jurnal Guru Wali ({{ count($timelineModal['jurnals']) }})</h5>
                            <div class="space-y-3">
                                @forelse($timelineModal['jurnals'] as $j)
                                    <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 space-y-1.5">
                                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $j['tanggal'] }} · {{ $j['jenis'] }} ({{ $j['pilar'] }})</span>
                                            <span class="px-2 py-0.5 rounded bg-primary-100 text-primary-800 dark:bg-primary-900/40 dark:text-primary-300 font-semibold">{{ $j['status'] }}</span>
                                        </div>
                                        <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $j['uraian'] }}</p>
                                        <div class="text-xs text-amber-700 dark:text-amber-300">
                                            <strong>Tindak Lanjut:</strong> {{ $j['rtl'] ?: '-' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500">
                                            Sentimen: {{ $j['sentimen'] }} · Kolaborasi: {{ $j['rujukan'] }}
                                            @if($j['bk']) · Status BK: {{ $j['bk'] }} @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500 italic">Belum ada catatan jurnal Guru Wali pada periode ini.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Pengajuan Konsultasi Portal Siswa ({{ count($timelineModal['konsultasis']) }})</h5>
                            <div class="space-y-3">
                                @forelse($timelineModal['konsultasis'] as $k)
                                    <div class="p-3.5 rounded-xl border border-blue-200/70 dark:border-blue-900/50 bg-blue-50/40 dark:bg-blue-950/20 space-y-1.5">
                                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $k['tanggal'] }} · {{ $k['topik'] }}</span>
                                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-semibold">{{ $k['status'] }}</span>
                                        </div>
                                        <p class="text-xs text-gray-700 dark:text-gray-300">{{ $k['detail'] ?: '-' }}</p>
                                        @if($k['tanggapan'])
                                            <div class="text-xs text-emerald-700 dark:text-emerald-300"><strong>Tanggapan Guru:</strong> {{ $k['tanggapan'] }}</div>
                                        @endif
                                        @if($k['rating'] || $k['emoji'])
                                            <div class="text-[11px] text-gray-500">
                                                Refleksi Siswa: {{ $k['rating'] ? "Rating {$k['rating']}/5" : '' }} {{ $k['emoji'] ? "({$k['emoji']})" : '' }}
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500 italic">Belum ada pengajuan konsultasi dari Portal Siswa pada periode ini.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>

