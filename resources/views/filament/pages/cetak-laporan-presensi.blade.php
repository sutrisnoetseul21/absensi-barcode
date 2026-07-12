<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== PARAMETER LAPORAN ===== --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-adjustments-horizontal class="w-5 h-5 text-primary-500" />
                    Parameter Laporan
                </div>
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Pilih Tahun Ajaran --}}
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                        Tahun Ajaran
                    </label>
                    <select wire:model.live="selectedAcademicYearId"
                        class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none cursor-pointer">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Kelas --}}
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                        Kelas
                    </label>
                    <select wire:model.live="selectedClassId"
                        class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none cursor-pointer">
                        @foreach($classes as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Jenis Laporan --}}
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                        Jenis Laporan
                    </label>
                    <div class="flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-700">
                        @foreach(['bulanan' => 'Bulanan', 'semester' => 'Semester', 'tahunan' => 'Tahunan'] as $val => $lbl)
                            <button
                                wire:click="$set('jenisLaporan', '{{ $val }}')"
                                class="flex-1 py-2 text-xs font-bold transition-all
                                    {{ $jenisLaporan === $val
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Parameter Dinamis --}}
                @if($jenisLaporan === 'bulanan')
                    <div class="flex gap-2">
                        <div class="flex flex-col gap-1 flex-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Bulan</label>
                            <select wire:model.live="bulan"
                                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer">
                                @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1" style="width: 90px;">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Tahun</label>
                            <input type="number" wire:model.live="tahunBulanan" min="2020" max="2099"
                                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none" />
                        </div>
                    </div>

                @elseif($jenisLaporan === 'semester')
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Pilih Semester</label>
                        <div class="flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-700">
                            <button wire:click="$set('semester', 'ganjil')"
                                class="flex-1 py-2 text-xs font-bold transition-all
                                    {{ $semester === 'ganjil'
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                Ganjil (Jul–Des)
                            </button>
                            <button wire:click="$set('semester', 'genap')"
                                class="flex-1 py-2 text-xs font-bold transition-all
                                    {{ $semester === 'genap'
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                Genap (Jan–Jun)
                            </button>
                        </div>
                    </div>

                @elseif($jenisLaporan === 'tahunan')
                    <div class="flex flex-col justify-end">
                        <div class="rounded-lg bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 px-3 py-2 text-xs text-info-700 dark:text-info-400 font-medium flex items-start gap-2">
                            <x-heroicon-o-information-circle class="w-4 h-4 mt-0.5 flex-shrink-0" />
                            Laporan mencakup rentang penuh Tahun Ajaran yang dipilih (Juli – Juni).
                        </div>
                    </div>
                @endif

            </div>

            {{-- INFO PERIODE & TOMBOL DOWNLOAD --}}
            @php $range = $this->getDateRange(); @endphp
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">

                {{-- Info Periode --}}
                @if($range['start'])
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-semibold text-gray-900 dark:text-gray-100">Periode:</span>
                        {{ $range['label'] }}
                        <span class="text-gray-400 dark:text-gray-600 text-xs ml-1">
                            ({{ \Carbon\Carbon::parse($range['start'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($range['end'])->format('d/m/Y') }})
                        </span>
                    </div>
                @else
                    <div></div>
                @endif

                {{-- Tombol Download --}}
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        wire:click="downloadExcel"
                        wire:loading.attr="disabled"
                        icon="heroicon-o-table-cells"
                        color="success">
                        <span wire:loading.remove wire:target="downloadExcel">Download Excel (.xlsx)</span>
                        <span wire:loading wire:target="downloadExcel">Memproses...</span>
                    </x-filament::button>

                    <x-filament::button
                        wire:click="downloadPdf"
                        wire:loading.attr="disabled"
                        icon="heroicon-o-document-arrow-down"
                        color="danger">
                        <span wire:loading.remove wire:target="downloadPdf">Download PDF</span>
                        <span wire:loading wire:target="downloadPdf">Memproses...</span>
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- ===== PREVIEW DATA ===== --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-eye class="w-5 h-5 text-primary-500" />
                        Preview Data
                    </div>
                    @php
                        $isBulanan = $jenisLaporan === 'bulanan';
                        if ($isBulanan) {
                            $previewCount = count($students ?? []);
                        } else {
                            $previewCount = count($semesterStudentsData ?? []);
                        }
                    @endphp
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">
                        {{ $previewCount }} Siswa
                    </span>
                </div>
            </x-slot>

            <div class="overflow-x-auto" wire:loading.class="opacity-50">
                @if($isBulanan)
                {{-- MATRIX TABLE UNTUK BULANAN --}}
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48 border-r border-gray-200 dark:border-gray-700">
                                Nama Siswa
                            </th>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php $isToday = ($todayDate === date('Y') . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT)); @endphp
                                <th class="px-2 py-3 text-center text-xs font-semibold {{ $isToday ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-gray-500 dark:text-gray-400' }} uppercase tracking-wider min-w-[36px]">
                                    {{ $d }}
                                </th>
                            @endfor
                            <th class="px-3 py-3 text-center text-xs font-semibold text-success-600 dark:text-success-400 uppercase tracking-wider border-l border-gray-200 dark:border-gray-700">H</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-warning-600 dark:text-warning-400 uppercase tracking-wider">T</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">I</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">S</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-danger-600 dark:text-danger-400 uppercase tracking-wider">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($students as $student)
                            @php
                                $isAlpaWarning = in_array($student->id, $alerts['alpa'] ?? []);
                                $isTelatWarning = in_array($student->id, $alerts['telat'] ?? []);
                                $stat = $monthlyStats[$student->id] ?? [];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ ($isAlpaWarning || $isTelatWarning) ? 'bg-danger-50/30 dark:bg-danger-900/10' : '' }}">
                                <td class="sticky left-0 z-10 px-4 py-3 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $student->name }}</p>
                                        <p class="text-[10px] text-gray-500 font-mono mt-0.5">{{ $student->nisn }}</p>
                                        @if($isAlpaWarning)
                                            <span class="inline-flex items-center gap-1 text-xs text-danger-600 dark:text-danger-400 font-medium mt-0.5">
                                                <x-heroicon-s-exclamation-triangle class="w-3 h-3"/> ≥ 3 Alpa
                                            </span><br>
                                        @endif
                                        @if($isTelatWarning)
                                            <span class="inline-flex items-center gap-1 text-xs text-warning-600 dark:text-warning-400 font-medium mt-0.5">
                                                <x-heroicon-s-exclamation-triangle class="w-3 h-3"/> ≥ 100mnt telat
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $code = $stat['daily'][$d] ?? '-';
                                        $cellClass = match($code) {
                                            'H' => 'text-success-600 dark:text-success-400 font-bold',
                                            'T' => 'text-warning-600 dark:text-warning-400 font-bold',
                                            'I' => 'text-blue-600 dark:text-blue-400 font-bold',
                                            'S' => 'text-indigo-600 dark:text-indigo-400 font-bold',
                                            'A' => 'text-danger-600 dark:text-danger-400 font-bold',
                                            'L' => 'text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 font-bold',
                                            default => 'text-gray-300 dark:text-gray-600',
                                        };
                                    @endphp
                                    <td class="px-2 py-3 text-center {{ $cellClass }}">{{ $code }}</td>
                                @endfor
                                <td class="px-3 py-3 text-center font-bold text-success-600 dark:text-success-400 border-l border-gray-200 dark:border-gray-700">{{ $stat['hadir'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-warning-600 dark:text-warning-400">{{ $stat['telat'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-blue-600 dark:text-blue-400">{{ $stat['izin'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-indigo-600 dark:text-indigo-400">{{ $stat['sakit'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-danger-600 dark:text-danger-400">{{ $stat['alpa'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 6 }}" class="py-16 text-center text-gray-400 dark:text-gray-600">
                                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-700" />
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Tidak ada data untuk periode ini.</p>
                                    <p class="text-xs mt-1 text-gray-400 dark:text-gray-600">Coba ubah parameter laporan di panel atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @else
                {{-- SUMMARY TABLE UNTUK SEMESTER/TAHUNAN (MATRIX BULAN) --}}
                <table class="min-w-full text-xs">
                    <thead>
                        {{-- Row 1: Nama bulan --}}
                        <tr class="border-b-2 border-gray-300 dark:border-gray-600">
                            <th rowspan="2" class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-10 border-r border-gray-200 dark:border-gray-700">No</th>
                            <th rowspan="2" class="sticky left-[40px] z-10 bg-white dark:bg-gray-900 px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r-2 border-gray-300 dark:border-gray-600 min-w-[200px]">Nama Siswa</th>
                            
                            @foreach($this->semesterMonthsList as $m)
                                <th colspan="4" class="px-2 py-2 text-center text-gray-700 dark:text-gray-200 tracking-wider border-r-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/60">
                                    <div class="font-bold text-xs uppercase">{{ $m['label'] }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 font-normal mt-0.5">Hari Efektif: {{ $m['effective_days'] }} Hari</div>
                                </th>
                            @endforeach
                            
                            <th colspan="6" class="px-2 py-2 text-center text-gray-900 dark:text-gray-100 tracking-wider bg-gray-100 dark:bg-gray-700/50 border-l border-gray-300 dark:border-gray-600">
                                <div class="font-bold text-xs uppercase">TOTAL {{ strtoupper($this->jenisLaporan) }}</div>
                            </th>
                        </tr>
                        {{-- Row 2: Sub-kolom H S I A --}}
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            @foreach($this->semesterMonthsList as $m)
                                <th class="px-2 py-2 text-center text-xs font-bold text-success-600 dark:text-success-400 w-8">H</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-indigo-600 dark:text-indigo-400 w-8">S</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-blue-600 dark:text-blue-400 w-8">I</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-danger-600 dark:text-danger-400 w-8 border-r-2 border-gray-300 dark:border-gray-600">A</th>
                            @endforeach
                            
                            {{-- Kolom Total --}}
                            <th class="px-2 py-2 text-center text-xs font-bold text-success-600 dark:text-success-400 bg-gray-100 dark:bg-gray-700/50 border-l border-gray-300 dark:border-gray-600">H</th>
                            <th class="px-2 py-2 text-center text-xs font-bold text-warning-600 dark:text-warning-400 bg-gray-100 dark:bg-gray-700/50">T</th>
                            <th class="px-2 py-2 text-center text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-gray-100 dark:bg-gray-700/50">S</th>
                            <th class="px-2 py-2 text-center text-xs font-bold text-blue-600 dark:text-blue-400 bg-gray-100 dark:bg-gray-700/50">I</th>
                            <th class="px-2 py-2 text-center text-xs font-bold text-danger-600 dark:text-danger-400 bg-gray-100 dark:bg-gray-700/50">A</th>
                            <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50">Telat(m)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->semesterStudentsData as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-3 py-2.5 text-center text-gray-400 dark:text-gray-600 font-medium border-r border-gray-200 dark:border-gray-700">
                                    {{ $row['no'] }}
                                </td>
                                <td class="sticky left-[40px] z-10 bg-white dark:bg-gray-900 px-4 py-2.5 border-r-2 border-gray-300 dark:border-gray-600">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $row['name'] }}</div>
                                    <div class="text-[10px] text-gray-500 font-mono mt-0.5">{{ $row['nisn'] }}</div>
                                </td>
                                
                                @foreach($this->semesterMonthsList as $m)
                                    @php
                                        $key   = "{$m['year']}-{$m['month']}";
                                        $stats = $row['months'][$key] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                                    @endphp
                                    <td class="px-2 py-2.5 text-center font-bold text-success-600 dark:text-success-400">{{ $stats['hadir'] ?: '-' }}</td>
                                    <td class="px-2 py-2.5 text-center font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['sakit'] ?: '-' }}</td>
                                    <td class="px-2 py-2.5 text-center font-bold text-blue-600 dark:text-blue-400">{{ $stats['izin'] ?: '-' }}</td>
                                    <td class="px-2 py-2.5 text-center font-bold text-danger-600 dark:text-danger-400 border-r-2 border-gray-300 dark:border-gray-600">{{ $stats['alpa'] ?: '-' }}</td>
                                @endforeach
                                
                                {{-- Subtotal Cols --}}
                                <td class="px-2 py-2.5 text-center font-bold text-success-600 dark:text-success-400 bg-gray-50 dark:bg-gray-800/50 border-l border-gray-300 dark:border-gray-600">{{ $row['total']['hadir'] ?: '-' }}</td>
                                <td class="px-2 py-2.5 text-center font-bold text-warning-600 dark:text-warning-400 bg-gray-50 dark:bg-gray-800/50">{{ $row['total']['telat'] ?: '-' }}</td>
                                <td class="px-2 py-2.5 text-center font-bold text-indigo-600 dark:text-indigo-400 bg-gray-50 dark:bg-gray-800/50">{{ $row['total']['sakit'] ?: '-' }}</td>
                                <td class="px-2 py-2.5 text-center font-bold text-blue-600 dark:text-blue-400 bg-gray-50 dark:bg-gray-800/50">{{ $row['total']['izin'] ?: '-' }}</td>
                                <td class="px-2 py-2.5 text-center font-bold text-danger-600 dark:text-danger-400 bg-gray-50 dark:bg-gray-800/50">{{ $row['total']['alpa'] ?: '-' }}</td>
                                <td class="px-2 py-2.5 text-center font-bold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 text-[10px]">{{ $row['total']['late_minutes'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + (count($this->semesterMonthsList) * 4) + 6 }}" class="py-16 text-center text-gray-400 dark:text-gray-600">
                                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-700" />
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Tidak ada data untuk periode ini.</p>
                                    <p class="text-xs mt-1 text-gray-400 dark:text-gray-600">Coba ubah parameter laporan di panel atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Loading indicator --}}
            <div wire:loading class="flex justify-center py-4">
                <x-filament::loading-indicator class="h-6 w-6 text-primary-500" />
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
