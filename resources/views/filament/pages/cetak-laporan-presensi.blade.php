<div class="min-h-screen bg-gradient-to-br from-slate-100 to-blue-50 p-6">
    
    {{-- PAGE HEADER --}}
    <div class="mb-8">
        <h1 class="text-2xl font-black text-slate-800 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-md">
                <x-heroicon-o-printer class="w-6 h-6 text-white" />
            </div>
            Cetak Laporan Presensi
        </h1>
        <p class="text-slate-500 text-sm mt-1">Unduh laporan presensi siswa per kelas dalam format Excel atau PDF.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- PANEL FILTER --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-6 py-4">
                    <h2 class="text-white font-bold text-base flex items-center gap-2">
                        <x-heroicon-o-adjustments-horizontal class="w-5 h-5 text-blue-200" />
                        Parameter Laporan
                    </h2>
                </div>
                <div class="p-6 space-y-5">

                    {{-- Pilih Tahun Ajaran --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Tahun Ajaran
                        </label>
                        <select wire:model.live="selectedAcademicYearId" class="w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-400 shadow-sm">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Kelas --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Kelas
                        </label>
                        <select wire:model.live="selectedClassId" class="w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-400 shadow-sm">
                            @foreach($classes as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Jenis Laporan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Jenis Laporan
                        </label>
                        <div class="grid grid-cols-3 gap-1.5">
                            @foreach(['bulanan' => 'Bulanan', 'semester' => 'Semester', 'tahunan' => 'Tahunan'] as $val => $label)
                                <button
                                    wire:click="$set('jenisLaporan', '{{ $val }}')"
                                    class="py-2 px-2 rounded-xl text-xs font-bold transition-all {{ $jenisLaporan === $val ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Parameter Dinamis --}}
                    @if($jenisLaporan === 'bulanan')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
                                <select wire:model.live="bulan" class="w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 shadow-sm">
                                    @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $lbl)
                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                                <input type="number" wire:model.live="tahunBulanan" min="2020" max="2099" class="w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 shadow-sm" />
                            </div>
                        </div>

                    @elseif($jenisLaporan === 'semester')
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pilih Semester</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button wire:click="$set('semester', 'ganjil')" class="py-2.5 rounded-xl text-xs font-bold transition-all {{ $semester === 'ganjil' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    Ganjil (Jul-Des)
                                </button>
                                <button wire:click="$set('semester', 'genap')" class="py-2.5 rounded-xl text-xs font-bold transition-all {{ $semester === 'genap' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    Genap (Jan-Jun)
                                </button>
                            </div>
                        </div>

                    @elseif($jenisLaporan === 'tahunan')
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700 font-medium">
                            <x-heroicon-o-information-circle class="w-4 h-4 inline mr-1" />
                            Laporan tahunan mencakup rentang penuh Tahun Ajaran yang dipilih (Juli – Juni).
                        </div>
                    @endif

                    {{-- INFO PERIODE --}}
                    @php
                        $range = $this->getDateRange();
                    @endphp
                    @if($range['start'])
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-600">
                            <span class="font-bold text-slate-700">Periode:</span> {{ $range['label'] }}<br>
                            <span class="text-slate-400">{{ \Carbon\Carbon::parse($range['start'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($range['end'])->format('d/m/Y') }}</span>
                        </div>
                    @endif

                    {{-- TOMBOL DOWNLOAD --}}
                    <div class="flex flex-col gap-2 pt-2">
                        <button
                            wire:click="downloadExcel"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold py-3 rounded-xl shadow-lg shadow-emerald-500/20 transition-all hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-60"
                        >
                            <x-heroicon-o-table-cells class="w-5 h-5" />
                            <span wire:loading.remove wire:target="downloadExcel">Download Excel (.xlsx)</span>
                            <span wire:loading wire:target="downloadExcel">Sedang memproses...</span>
                        </button>
                        <button
                            wire:click="downloadPdf"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-400 hover:to-pink-400 text-white font-bold py-3 rounded-xl shadow-lg shadow-rose-500/20 transition-all hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-60"
                        >
                            <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                            <span wire:loading.remove wire:target="downloadPdf">Download PDF</span>
                            <span wire:loading wire:target="downloadPdf">Sedang memproses...</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- PREVIEW DATA --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h2 class="font-black text-slate-700 flex items-center gap-2">
                        <x-heroicon-o-eye class="w-5 h-5 text-blue-500" />
                        Preview Data
                    </h2>
                    @php $preview = $this->getLaporanData(); @endphp
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">{{ count($preview) }} Siswa</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-black text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4 border-b border-slate-100">No</th>
                                <th class="py-3 px-4 border-b border-slate-100">NISN</th>
                                <th class="py-3 px-4 border-b border-slate-100">Nama Siswa</th>
                                <th class="py-3 px-4 text-center border-b border-slate-100 text-emerald-600">H</th>
                                <th class="py-3 px-4 text-center border-b border-slate-100 text-amber-600">T</th>
                                <th class="py-3 px-4 text-center border-b border-slate-100 text-blue-600">I</th>
                                <th class="py-3 px-4 text-center border-b border-slate-100 text-purple-600">S</th>
                                <th class="py-3 px-4 text-center border-b border-slate-100 text-rose-600">A</th>
                                <th class="py-3 px-4 text-center border-b border-slate-100 text-slate-500">Telat (Mnt)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($preview as $row)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 text-slate-400">{{ $row['no'] }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-500 text-xs">{{ $row['nisn'] }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-800">{{ $row['name'] }}</td>
                                    <td class="py-3 px-4 text-center"><span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded font-black text-xs">{{ $row['hadir'] }}</span></td>
                                    <td class="py-3 px-4 text-center"><span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded font-black text-xs">{{ $row['telat'] }}</span></td>
                                    <td class="py-3 px-4 text-center"><span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-black text-xs">{{ $row['izin'] }}</span></td>
                                    <td class="py-3 px-4 text-center"><span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded font-black text-xs">{{ $row['sakit'] }}</span></td>
                                    <td class="py-3 px-4 text-center"><span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded font-black text-xs">{{ $row['alpa'] }}</span></td>
                                    <td class="py-3 px-4 text-center text-slate-500 font-semibold text-xs">{{ $row['late_minutes'] }} mnt</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-16 text-center text-slate-400">
                                        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                                        <p class="font-semibold">Tidak ada data untuk periode ini.</p>
                                        <p class="text-xs mt-1">Coba ubah parameter laporan di panel kiri.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
