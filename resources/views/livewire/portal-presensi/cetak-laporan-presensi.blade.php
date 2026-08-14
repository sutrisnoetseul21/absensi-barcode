<div>
    <div class="mb-6 lg:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Cetak Laporan Presensi</h1>
            <p class="text-sm sm:text-base text-slate-500 mt-1">Hasilkan laporan rekapitulasi presensi siswa dalam berbagai format.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h3 class="text-sm font-bold text-rose-800">Gagal</h3>
                <p class="text-xs text-rose-600 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
            Parameter Laporan
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tahun Ajaran -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tahun Ajaran</label>
                <select wire:model.live="selectedAcademicYearId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Kelas -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kelas</label>
                <select wire:model.live="selectedClassId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Jenis Laporan -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Jenis Laporan</label>
                <div class="flex rounded-xl overflow-hidden border border-slate-300">
                    @foreach(['bulanan' => 'Bulanan', 'semester' => 'Semester', 'tahunan' => 'Tahunan'] as $val => $lbl)
                        <button wire:click="$set('jenisLaporan', '{{ $val }}')" class="flex-1 py-2 text-xs font-bold transition-all {{ $jenisLaporan === $val ? 'bg-brand-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Parameter Dinamis -->
            @if($jenisLaporan === 'bulanan')
                <div class="flex gap-2">
                    <div class="flex flex-col gap-1 flex-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Bulan</label>
                        <select wire:model.live="bulan" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                            @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1 w-24">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tahun</label>
                        <input type="number" wire:model.live="tahunBulanan" min="2020" max="2099" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all" />
                    </div>
                </div>
            @elseif($jenisLaporan === 'semester')
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih Semester</label>
                    <div class="flex rounded-xl overflow-hidden border border-slate-300">
                        <button wire:click="$set('semester', 'ganjil')" class="flex-1 py-2 text-xs font-bold transition-all {{ $semester === 'ganjil' ? 'bg-brand-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                            Ganjil (Jul–Des)
                        </button>
                        <button wire:click="$set('semester', 'genap')" class="flex-1 py-2 text-xs font-bold transition-all {{ $semester === 'genap' ? 'bg-brand-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                            Genap (Jan–Jun)
                        </button>
                    </div>
                </div>
            @elseif($jenisLaporan === 'tahunan')
                <div class="flex flex-col justify-end">
                    <div class="rounded-xl bg-sky-50 border border-sky-200 px-3 py-2 text-xs text-sky-700 font-medium flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Laporan mencakup rentang penuh (Juli – Juni).
                    </div>
                </div>
            @endif
        </div>

        @php $range = $this->getDateRange(); @endphp
        <div class="mt-6 pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            @if($range['start'])
                <div class="text-sm text-slate-600">
                    <span class="font-bold text-slate-900">Periode:</span> {{ $range['label'] }}
                    <span class="text-slate-400 text-xs ml-1">({{ \Carbon\Carbon::parse($range['start'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($range['end'])->format('d/m/Y') }})</span>
                </div>
            @else
                <div></div>
            @endif

            <div class="flex flex-wrap gap-2">
                <button wire:click="downloadExcel" wire:loading.attr="disabled" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Download Excel
                </button>
                <button wire:click="downloadPdf" wire:loading.attr="disabled" class="flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Download PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Data -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden relative min-h-[300px]">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                Preview Data
            </h2>
            @php
                $isBulanan = $jenisLaporan === 'bulanan';
                $previewCount = $isBulanan ? count($students ?? []) : count($semesterStudentsData ?? []);
            @endphp
            <span class="text-xs font-bold text-slate-500 bg-slate-200 px-3 py-1 rounded-full">
                {{ $previewCount }} Siswa
            </span>
        </div>

        <div class="overflow-x-auto" wire:loading.class="opacity-50">
            @if($isBulanan)
                <!-- Matrix Bulanan -->
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="sticky left-0 z-10 bg-slate-100 px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider w-48 border-r border-slate-200">Nama Siswa</th>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php $isToday = ($todayDate === date('Y') . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT)); @endphp
                                <th class="px-2 py-3 text-center text-xs font-bold {{ $isToday ? 'text-brand-primary bg-brand-primary/10' : 'text-slate-500' }} uppercase tracking-wider min-w-[36px]">{{ $d }}</th>
                            @endfor
                            <th class="px-3 py-3 text-center text-xs font-bold text-emerald-600 uppercase tracking-wider border-l border-slate-200">H</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-amber-600 uppercase tracking-wider">T</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-sky-600 uppercase tracking-wider">I</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">S</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-rose-600 uppercase tracking-wider">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            @php
                                $isAlpaWarning = in_array($student->id, $alerts['alpa'] ?? []);
                                $isTelatWarning = in_array($student->id, $alerts['telat'] ?? []);
                                $stat = $monthlyStats[$student->id] ?? [];
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors {{ ($isAlpaWarning || $isTelatWarning) ? 'bg-rose-50/50' : '' }}">
                                <td class="sticky left-0 z-10 px-4 py-3 bg-white border-r border-slate-200">
                                    <div class="font-bold text-slate-800 whitespace-nowrap">{{ $student->name }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $student->nisn }}</div>
                                </td>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $code = $stat['daily'][$d] ?? '-';
                                        $cellClass = match($code) {
                                            'H' => 'text-emerald-600 font-bold',
                                            'T' => 'text-amber-600 font-bold',
                                            'I' => 'text-sky-600 font-bold',
                                            'S' => 'text-indigo-600 font-bold',
                                            'A' => 'text-rose-600 font-bold',
                                            'L' => 'text-slate-400 bg-slate-50 font-bold',
                                            default => 'text-slate-300',
                                        };
                                    @endphp
                                    <td class="px-2 py-3 text-center text-xs {{ $cellClass }}">{{ $code }}</td>
                                @endfor
                                <td class="px-3 py-3 text-center font-bold text-emerald-600 border-l border-slate-200 bg-slate-50/50">{{ $stat['hadir'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-amber-600 bg-slate-50/50">{{ $stat['telat'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-sky-600 bg-slate-50/50">{{ $stat['izin'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-indigo-600 bg-slate-50/50">{{ $stat['sakit'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-rose-600 bg-slate-50/50">{{ $stat['alpa'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 6 }}" class="py-16 text-center text-slate-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <p class="font-bold text-slate-500">Tidak ada data untuk periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <!-- Matrix Semester/Tahunan -->
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b-2 border-slate-300">
                            <th rowspan="2" class="sticky left-0 z-10 bg-slate-100 px-3 py-3 text-center font-bold text-slate-600 uppercase tracking-wider w-10 border-r border-slate-200">No</th>
                            <th rowspan="2" class="sticky left-[40px] z-10 bg-slate-100 px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider border-r-2 border-slate-300 min-w-[200px]">Nama Siswa</th>
                            @foreach($this->semesterMonthsList as $m)
                                <th colspan="4" class="px-2 py-2 text-center text-slate-700 tracking-wider border-r-2 border-slate-300">
                                    <div class="font-bold uppercase">{{ $m['label'] }}</div>
                                    <div class="text-[10px] text-slate-500 font-normal mt-0.5">{{ $m['effective_days'] }} Hari</div>
                                </th>
                            @endforeach
                            <th colspan="6" class="px-2 py-2 text-center text-slate-800 tracking-wider bg-slate-200 border-l border-slate-300">
                                <div class="font-bold uppercase">TOTAL {{ strtoupper($this->jenisLaporan) }}</div>
                            </th>
                        </tr>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            @foreach($this->semesterMonthsList as $m)
                                <th class="px-2 py-2 text-center font-bold text-emerald-600 w-8">H</th>
                                <th class="px-2 py-2 text-center font-bold text-indigo-600 w-8">S</th>
                                <th class="px-2 py-2 text-center font-bold text-sky-600 w-8">I</th>
                                <th class="px-2 py-2 text-center font-bold text-rose-600 w-8 border-r-2 border-slate-300">A</th>
                            @endforeach
                            <th class="px-2 py-2 text-center font-bold text-emerald-600 bg-slate-100 border-l border-slate-300">H</th>
                            <th class="px-2 py-2 text-center font-bold text-amber-600 bg-slate-100">T</th>
                            <th class="px-2 py-2 text-center font-bold text-indigo-600 bg-slate-100">S</th>
                            <th class="px-2 py-2 text-center font-bold text-sky-600 bg-slate-100">I</th>
                            <th class="px-2 py-2 text-center font-bold text-rose-600 bg-slate-100">A</th>
                            <th class="px-2 py-2 text-center font-bold text-slate-500 bg-slate-100">Telat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($this->semesterStudentsData as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="sticky left-0 z-10 bg-white px-3 py-2 text-center text-slate-500 font-bold border-r border-slate-200">{{ $row['no'] }}</td>
                                <td class="sticky left-[40px] z-10 bg-white px-4 py-2 border-r-2 border-slate-300">
                                    <div class="font-bold text-slate-800 whitespace-nowrap">{{ $row['name'] }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $row['nisn'] }}</div>
                                </td>
                                @foreach($this->semesterMonthsList as $m)
                                    @php
                                        $key = "{$m['year']}-{$m['month']}";
                                        $stats = $row['months'][$key] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                                    @endphp
                                    <td class="px-2 py-2 text-center font-bold text-emerald-600">{{ $stats['hadir'] ?: '-' }}</td>
                                    <td class="px-2 py-2 text-center font-bold text-indigo-600">{{ $stats['sakit'] ?: '-' }}</td>
                                    <td class="px-2 py-2 text-center font-bold text-sky-600">{{ $stats['izin'] ?: '-' }}</td>
                                    <td class="px-2 py-2 text-center font-bold text-rose-600 border-r-2 border-slate-300">{{ $stats['alpa'] ?: '-' }}</td>
                                @endforeach
                                <td class="px-2 py-2 text-center font-bold text-emerald-600 bg-slate-50 border-l border-slate-300">{{ $row['total']['hadir'] ?: '-' }}</td>
                                <td class="px-2 py-2 text-center font-bold text-amber-600 bg-slate-50">{{ $row['total']['telat'] ?: '-' }}</td>
                                <td class="px-2 py-2 text-center font-bold text-indigo-600 bg-slate-50">{{ $row['total']['sakit'] ?: '-' }}</td>
                                <td class="px-2 py-2 text-center font-bold text-sky-600 bg-slate-50">{{ $row['total']['izin'] ?: '-' }}</td>
                                <td class="px-2 py-2 text-center font-bold text-rose-600 bg-slate-50">{{ $row['total']['alpa'] ?: '-' }}</td>
                                <td class="px-2 py-2 text-center font-bold text-slate-500 bg-slate-50 text-[10px]">{{ $row['total']['late_minutes'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + (count($this->semesterMonthsList) * 4) + 6 }}" class="py-16 text-center text-slate-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <p class="font-bold text-slate-500">Tidak ada data untuk periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <div wire:loading class="absolute inset-0 bg-white/50 backdrop-blur-sm flex items-center justify-center z-50">
            <svg class="animate-spin w-8 h-8 text-brand-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>
    </div>
</div>
