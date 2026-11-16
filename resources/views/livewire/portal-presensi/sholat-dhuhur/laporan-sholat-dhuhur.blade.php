<div class="font-jakarta pb-16">
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Rekap dan Cetak Laporan Sholat Dhuhur</h1>
        <p class="text-sm sm:text-base text-slate-500 mt-1">Lihat rekapitulasi bulanan, semester, tahunan per kelas maupun rekap harian sekolah, lalu ekspor ke Excel/CSV atau PDF.</p>
    </div>

    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Parameter Laporan -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tahun Ajaran</label>
                <select wire:model.live="selectedAcademicYearId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kelas</label>
                @if($jenisLaporan === 'sekolah')
                    <div class="w-full rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-sm font-bold px-3 py-2">
                        Semua Kelas (Tingkat Sekolah)
                    </div>
                @else
                    <select wire:model.live="selectedClassId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Jenis Laporan</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 rounded-xl overflow-hidden border border-slate-300">
                    @foreach(['bulanan' => 'Bulanan', 'semester' => 'Semester', 'tahunan' => 'Tahunan', 'sekolah' => 'Sekolah'] as $val => $lbl)
                        <button type="button" wire:click="$set('jenisLaporan', '{{ $val }}')"
                            class="py-2 px-2 text-xs font-bold transition-all cursor-pointer {{ $jenisLaporan === $val ? 'bg-teal-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if($jenisLaporan === 'bulanan')
                <div class="flex gap-2">
                    <div class="flex flex-col gap-1 flex-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Bulan</label>
                        <select wire:model.live="bulan" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2">
                            @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1 w-24">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tahun</label>
                        <input type="number" wire:model.live="tahunBulanan" min="2020" max="2099" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2">
                    </div>
                </div>
            @elseif($jenisLaporan === 'semester')
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Semester</label>
                    <div class="flex rounded-xl overflow-hidden border border-slate-300">
                        <button type="button" wire:click="$set('semester', 'ganjil')" class="flex-1 py-2 text-xs font-bold cursor-pointer {{ $semester === 'ganjil' ? 'bg-teal-600 text-white' : 'bg-slate-50 text-slate-600' }}">Ganjil</button>
                        <button type="button" wire:click="$set('semester', 'genap')" class="flex-1 py-2 text-xs font-bold cursor-pointer {{ $semester === 'genap' ? 'bg-teal-600 text-white' : 'bg-slate-50 text-slate-600' }}">Genap</button>
                    </div>
                </div>
            @elseif($jenisLaporan === 'sekolah')
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tanggal Rekap Sekolah</label>
                    <x-portal-presensi.date-indo
                        model="tanggalSekolah"
                        :value="$tanggalSekolah"
                        :live="true"
                        placeholder="Pilih tanggal"
                        buttonClass="w-full flex items-center justify-between gap-2 rounded-xl border border-slate-300 bg-slate-50 hover:bg-white text-slate-900 text-sm font-bold px-3 py-2 focus:outline-none text-left cursor-pointer"
                    />
                </div>
            @else
                <div class="flex flex-col justify-end">
                    <div class="rounded-xl bg-teal-50 border border-teal-200 px-3 py-2 text-xs text-teal-800 font-medium">
                        Mencakup penuh Tahun Ajaran (Juli – Juni).
                    </div>
                </div>
            @endif
        </div>

        @php $range = $this->getDateRange(); @endphp
        <div class="mt-6 pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="text-sm text-slate-600">
                <span class="font-bold text-slate-900">Periode:</span> {{ $range['label'] }}
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="downloadExcel" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors cursor-pointer">
                    <span>Download {{ $jenisLaporan === 'sekolah' ? 'Excel' : 'CSV / Excel' }}</span>
                </button>
                <button type="button" wire:click="downloadPdf" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors cursor-pointer">
                    <span>Download PDF</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Data -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden relative min-h-[280px]">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <h2 class="text-base sm:text-lg font-bold text-slate-800">Preview Data Sholat Dhuhur</h2>
            @php
                $countLabel = $jenisLaporan === 'sekolah'
                    ? count($schoolSummary['classesSummary'] ?? []) . ' Kelas'
                    : ($jenisLaporan === 'bulanan' ? count($students ?? []) . ' Siswa' : count($semesterStudentsData ?? []) . ' Siswa');
            @endphp
            <span class="text-xs font-bold text-slate-600 bg-slate-200 px-3 py-1 rounded-full">{{ $countLabel }}</span>
        </div>

        <div class="overflow-x-auto">
            @if($jenisLaporan === 'bulanan')
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="sticky left-0 z-10 bg-slate-100 px-4 py-3 text-left font-bold text-slate-600 uppercase w-48 border-r border-slate-200">Nama Siswa</th>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <th class="px-2 py-3 text-center text-xs font-bold text-slate-500 min-w-[32px]">{{ $d }}</th>
                            @endfor
                            <th class="px-3 py-3 text-center text-xs font-bold text-emerald-600 border-l border-slate-200">H</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-sky-600">I</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-rose-600">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            @php $stat = $monthlyStats[$student->id] ?? []; @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="sticky left-0 z-10 px-4 py-2.5 bg-white border-r border-slate-200">
                                    <div class="font-bold text-slate-800 whitespace-nowrap">{{ $student->name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $student->nisn ?? '-' }}</div>
                                </td>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $code = $stat['daily'][$d] ?? '-';
                                        $cls = match($code) {
                                            'H' => 'text-emerald-600 font-bold',
                                            'I' => 'text-sky-600 font-bold',
                                            'A' => 'text-rose-600 font-bold',
                                            'L' => 'text-slate-400 bg-slate-50',
                                            'NM' => 'text-slate-400 bg-slate-100 text-[10px]',
                                            default => 'text-slate-300',
                                        };
                                    @endphp
                                    <td class="px-2 py-2.5 text-center text-xs {{ $cls }}">{{ $code }}</td>
                                @endfor
                                <td class="px-3 py-2.5 text-center font-bold text-emerald-600 border-l border-slate-200">{{ $stat['hadir'] ?? 0 }}</td>
                                <td class="px-3 py-2.5 text-center font-bold text-sky-600">{{ $stat['ijin'] ?? 0 }}</td>
                                <td class="px-3 py-2.5 text-center font-bold text-rose-600">{{ $stat['tidak_hadir'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $daysInMonth + 4 }}" class="py-12 text-center text-slate-400 font-bold">Pilih kelas untuk melihat preview.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($jenisLaporan === 'sekolah')
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-600 uppercase font-black">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Wali Kelas</th>
                            <th class="px-4 py-3 text-center">Wajib Sholat</th>
                            <th class="px-4 py-3 text-center text-emerald-700">Hadir</th>
                            <th class="px-4 py-3 text-center text-sky-700">Ijin/Haid</th>
                            <th class="px-4 py-3 text-center text-rose-700">Alpa</th>
                            <th class="px-4 py-3 text-center text-amber-700">Belum</th>
                            <th class="px-4 py-3 text-center">% Hadir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse(($schoolSummary['classesSummary'] ?? []) as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-bold text-slate-800">Kelas {{ $row['name'] }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $row['wali_kelas'] }}</td>
                                <td class="px-4 py-3 text-center font-bold">{{ $row['muslim_total'] }}</td>
                                <td class="px-4 py-3 text-center font-black text-emerald-600">{{ $row['hadir'] }}</td>
                                <td class="px-4 py-3 text-center font-bold text-sky-600">{{ $row['ijin'] }}</td>
                                <td class="px-4 py-3 text-center font-bold text-rose-600">{{ $row['tidak_hadir'] }}</td>
                                <td class="px-4 py-3 text-center font-bold text-amber-700">{{ $row['belum'] }}</td>
                                <td class="px-4 py-3 text-center font-extrabold text-teal-700">{{ $row['persen'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="py-12 text-center text-slate-400 font-bold">Belum ada data kelas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th rowspan="2" class="sticky left-0 z-10 bg-slate-100 px-4 py-3 text-left font-bold text-slate-600 border-r border-slate-200 min-w-[180px]">Nama Siswa</th>
                            @foreach($semesterMonthsList as $m)
                                <th colspan="3" class="px-2 py-2 text-center font-bold text-slate-700 border-r border-slate-200">{{ $m['name'] }}</th>
                            @endforeach
                            <th colspan="4" class="px-3 py-2 text-center font-bold text-teal-800 bg-teal-50">KUMULATIF</th>
                        </tr>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            @foreach($semesterMonthsList as $m)
                                <th class="px-2 py-1.5 text-center text-emerald-600 font-bold">H</th>
                                <th class="px-2 py-1.5 text-center text-sky-600 font-bold">I</th>
                                <th class="px-2 py-1.5 text-center text-rose-600 font-bold border-r border-slate-200">A</th>
                            @endforeach
                            <th class="px-2 py-1.5 text-center text-emerald-600 font-bold bg-teal-50/60">H</th>
                            <th class="px-2 py-1.5 text-center text-sky-600 font-bold bg-teal-50/60">I</th>
                            <th class="px-2 py-1.5 text-center text-rose-600 font-bold bg-teal-50/60">A</th>
                            <th class="px-2 py-1.5 text-center text-slate-700 font-bold bg-teal-50/60">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($semesterStudentsData as $st)
                            @php
                                $totH = $st['total']['hadir'] ?? 0;
                                $totI = $st['total']['ijin'] ?? 0;
                                $totA = $st['total']['tidak_hadir'] ?? 0;
                                $grand = $totH + $totI + $totA;
                                $pct = $grand > 0 ? round(($totH / $grand) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="sticky left-0 z-10 bg-white px-4 py-2 border-r border-slate-200">
                                    <div class="font-bold text-slate-800 whitespace-nowrap">{{ $st['name'] }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $st['nisn'] ?? '-' }}</div>
                                </td>
                                @foreach($semesterMonthsList as $m)
                                    @php $mStats = $st['months'][$m['key']] ?? []; @endphp
                                    <td class="px-2 py-2 text-center font-bold text-emerald-600">{{ $mStats['hadir'] ?? 0 }}</td>
                                    <td class="px-2 py-2 text-center font-bold text-sky-600">{{ $mStats['ijin'] ?? 0 }}</td>
                                    <td class="px-2 py-2 text-center font-bold text-rose-600 border-r border-slate-200">{{ $mStats['tidak_hadir'] ?? 0 }}</td>
                                @endforeach
                                <td class="px-2 py-2 text-center font-bold text-emerald-600 bg-teal-50/30">{{ $totH }}</td>
                                <td class="px-2 py-2 text-center font-bold text-sky-600 bg-teal-50/30">{{ $totI }}</td>
                                <td class="px-2 py-2 text-center font-bold text-rose-600 bg-teal-50/30">{{ $totA }}</td>
                                <td class="px-2 py-2 text-center font-bold text-slate-700 bg-teal-50/30">{{ $pct }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($semesterMonthsList) * 3 + 5 }}" class="py-12 text-center text-slate-400 font-bold">Pilih kelas untuk menampilkan preview.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

