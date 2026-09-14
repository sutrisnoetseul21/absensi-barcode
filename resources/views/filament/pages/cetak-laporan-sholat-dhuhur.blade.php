<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== PARAMETER LAPORAN ===== --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-adjustments-horizontal class="w-5 h-5 text-primary-500" />
                    Parameter Laporan Sholat Dhuhur
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
                        <option value="">-- Pilih Kelas --</option>
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
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                            Jumat & Libur Otomatis (L)
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
                        <span wire:loading.remove wire:target="downloadExcel">Download Excel / CSV</span>
                        <span wire:loading wire:target="downloadExcel">Memproses...</span>
                    </x-filament::button>

                    <x-filament::button
                        wire:click="downloadPdf"
                        wire:loading.attr="disabled"
                        icon="heroicon-o-document-arrow-down"
                        color="danger">
                        <span wire:loading.remove wire:target="downloadPdf">Download PDF Landscape</span>
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
                        Preview Matriks Sholat Dhuhur
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
                        {{ $previewCount }} Siswa Terdaftar
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
                                <th class="px-2 py-3 text-center text-xs font-semibold {{ $isToday ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-gray-500 dark:text-gray-400' }} uppercase tracking-wider min-w-[34px]">
                                    {{ $d }}
                                </th>
                            @endfor
                            <th class="px-3 py-3 text-center text-xs font-semibold text-success-600 dark:text-success-400 uppercase tracking-wider border-l border-gray-200 dark:border-gray-700">H</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">I</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-danger-600 dark:text-danger-400 uppercase tracking-wider">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($students as $student)
                            @php
                                $stat = $monthlyStats[$student->id] ?? [];
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-2.5 whitespace-nowrap border-r border-gray-200 dark:border-gray-700">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-600">{{ $student->nisn ?? '-' }} &bull; {{ $student->gender ?? '-' }}</div>
                                </td>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $code = $stat['daily'][$d] ?? '-';
                                        $badgeClass = match($code) {
                                            'H' => 'text-success-700 dark:text-success-400 bg-success-50 dark:bg-success-900/30 font-bold',
                                            'I' => 'text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 font-bold',
                                            'A' => 'text-danger-700 dark:text-danger-400 bg-danger-50 dark:bg-danger-900/30 font-bold',
                                            'L' => 'text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800/50',
                                            default => 'text-gray-300 dark:text-gray-600',
                                        };
                                    @endphp
                                    <td class="px-1 py-2 text-center text-xs">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded text-xs {{ $badgeClass }}">
                                            {{ $code }}
                                        </span>
                                    </td>
                                @endfor
                                <td class="px-3 py-2.5 text-center text-xs font-bold text-success-600 dark:text-success-400 border-l border-gray-200 dark:border-gray-700">
                                    {{ $stat['hadir'] ?? 0 }}
                                </td>
                                <td class="px-3 py-2.5 text-center text-xs font-bold text-blue-600 dark:text-blue-400">
                                    {{ $stat['ijin'] ?? 0 }}
                                </td>
                                <td class="px-3 py-2.5 text-center text-xs font-bold text-danger-600 dark:text-danger-400">
                                    {{ $stat['tidak_hadir'] ?? 0 }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 4 }}" class="text-center py-12 text-gray-400 dark:text-gray-600">
                                    Pilih kelas untuk menampilkan preview matriks sholat dhuhur.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @else
                {{-- TABLE REKAPITULASI SEMESTER / TAHUNAN --}}
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th rowspan="2" class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48 border-r border-gray-200 dark:border-gray-700">
                                Nama Siswa
                            </th>
                            @foreach($semesterMonthsList as $m)
                                <th colspan="3" class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    {{ $m['name'] }}
                                </th>
                            @endforeach
                            <th colspan="4" class="px-4 py-2 text-center text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider bg-primary-50/50 dark:bg-primary-900/10 border-l border-gray-200 dark:border-gray-700">
                                Kumulatif
                            </th>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            @foreach($semesterMonthsList as $m)
                                <th class="px-2 py-1.5 text-center text-xs text-success-600">H</th>
                                <th class="px-2 py-1.5 text-center text-xs text-blue-600">I</th>
                                <th class="px-2 py-1.5 text-center text-xs text-danger-600">A</th>
                            @endforeach
                            <th class="px-3 py-1.5 text-center text-xs font-bold text-success-600 bg-primary-50/50 dark:bg-primary-900/10 border-l border-gray-200 dark:border-gray-700">H</th>
                            <th class="px-3 py-1.5 text-center text-xs font-bold text-blue-600 bg-primary-50/50 dark:bg-primary-900/10">I</th>
                            <th class="px-3 py-1.5 text-center text-xs font-bold text-danger-600 bg-primary-50/50 dark:bg-primary-900/10">A</th>
                            <th class="px-3 py-1.5 text-center text-xs font-bold text-gray-700 dark:text-gray-300 bg-primary-50/50 dark:bg-primary-900/10">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($semesterStudentsData as $st)
                            @php
                                $totH = $st['total']['hadir'] ?? 0;
                                $totI = $st['total']['ijin'] ?? 0;
                                $totA = $st['total']['tidak_hadir'] ?? 0;
                                $grand = $totH + $totI + $totA;
                                $pct = $grand > 0 ? round(($totH / $grand) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-2.5 whitespace-nowrap border-r border-gray-200 dark:border-gray-700">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $st['name'] }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-600">{{ $st['nisn'] ?? '-' }}</div>
                                </td>
                                @foreach($semesterMonthsList as $m)
                                    @php $mStats = $st['months'][$m['key']] ?? []; @endphp
                                    <td class="px-2 py-2 text-center text-xs font-bold text-success-600">{{ $mStats['hadir'] ?? 0 }}</td>
                                    <td class="px-2 py-2 text-center text-xs font-bold text-blue-600">{{ $mStats['ijin'] ?? 0 }}</td>
                                    <td class="px-2 py-2 text-center text-xs font-bold text-danger-600">{{ $mStats['tidak_hadir'] ?? 0 }}</td>
                                @endforeach
                                <td class="px-3 py-2 text-center text-xs font-bold text-success-600 bg-primary-50/30 dark:bg-primary-900/10 border-l border-gray-200 dark:border-gray-700">{{ $totH }}</td>
                                <td class="px-3 py-2 text-center text-xs font-bold text-blue-600 bg-primary-50/30 dark:bg-primary-900/10">{{ $totI }}</td>
                                <td class="px-3 py-2 text-center text-xs font-bold text-danger-600 bg-primary-50/30 dark:bg-primary-900/10">{{ $totA }}</td>
                                <td class="px-3 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-300 bg-primary-50/30 dark:bg-primary-900/10">{{ $pct }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($semesterMonthsList) * 3 + 5 }}" class="text-center py-12 text-gray-400 dark:text-gray-600">
                                    Pilih kelas untuk menampilkan preview rekapitulasi semester/tahunan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
