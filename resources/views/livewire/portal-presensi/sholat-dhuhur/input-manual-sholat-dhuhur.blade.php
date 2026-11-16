<div class="font-jakarta pb-16">
    <div class="mb-6 lg:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Input Manual Sholat Dhuhur</h1>
            <p class="text-sm sm:text-base text-slate-500 mt-1">Kelola dan catat kehadiran sholat dhuhur berjamaah siswa per kelas.</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <h3 class="text-sm font-bold text-emerald-800">Berhasil</h3>
                <p class="text-xs text-emerald-600 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <h3 class="text-sm font-bold text-rose-800">Gagal</h3>
                <p class="text-xs text-rose-600 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif
    @if (session()->has('warning'))
        <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h3 class="text-sm font-bold text-amber-800">Perhatian</h3>
                <p class="text-xs text-amber-600 mt-0.5">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran</label>
                <select wire:model.change="selectedAcademicYearId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 outline-none">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">Kelas</label>
                <select wire:model.change="selectedClassId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 outline-none">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Sholat Dhuhur</label>
                <x-portal-presensi.date-indo
                    model="inputDate"
                    :value="$inputDate"
                    :live="true"
                    placeholder="Pilih tanggal"
                    buttonClass="w-full flex items-center justify-between gap-2 rounded-xl border border-slate-300 bg-slate-50 hover:bg-white text-slate-900 text-sm font-bold px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 transition-all text-left cursor-pointer"
                />
            </div>

            <div>
                <button type="button" wire:click="filterData" wire:loading.attr="disabled"
                    class="w-full md:w-auto flex items-center justify-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md shadow-teal-600/20 transition-all cursor-pointer">
                    <span>Proses Data</span>
                </button>
            </div>
        </div>
    </div>

    @if(!$hasSubmittedFilter)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-10 text-center">
            <h3 class="text-lg font-bold text-slate-800 mb-1">Daftar Siswa Belum Dimuat</h3>
            <p class="text-sm text-slate-500">Pilih Tahun Ajaran, Kelas, dan Tanggal di atas, lalu klik <strong>Proses Data</strong>.</p>
        </div>
    @elseif($isInputDateHoliday)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6 text-center">
            <h3 class="text-lg font-bold text-rose-800 mb-1">Hari Libur / Jumat (Tidak Ada Sholat Dhuhur Sekolah)</h3>
            <p class="text-sm text-rose-600">
                Tanggal {{ \Carbon\Carbon::parse($inputDate)->locale('id')->translatedFormat('l, d F Y') }} tidak dijadwalkan presensi Sholat Dhuhur{{ $inputDateHolidayDesc ? ' (' . $inputDateHolidayDesc . ')' : '' }}.
            </p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm font-extrabold text-slate-800">
                    Daftar Siswa ({{ count($inputStudents) }} Siswa)
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <button type="button" wire:click="tarikDariPresensiPagi"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 transition-colors cursor-pointer">
                        Sinkron Presensi Pagi
                    </button>

                    <select wire:change="applyBulkStatus($event.target.value)"
                        class="text-xs font-bold py-1.5 px-3 rounded-xl border border-teal-300 bg-white text-slate-800 cursor-pointer">
                        <option value="">Set Massal...</option>
                        <option value="hadir">Semua Hadir Berjamaah</option>
                        <option value="ijin">Semua Ijin</option>
                        <option value="haid">Semua Putri Haid/Halangan</option>
                        <option value="tidak_hadir">Semua Tidak Hadir (Alpa)</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Nama Siswa</th>
                            <th class="py-3.5 px-4 w-56">Status Sholat Dhuhur</th>
                            <th class="py-3.5 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($inputStudents as $index => $st)
                            @if(!empty($st['is_non_muslim']))
                                <tr class="bg-slate-50/70 text-slate-400">
                                    <td class="py-3 px-4 text-center font-bold">{{ $loop->iteration }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-500">{{ $st['name'] }}</div>
                                        <div class="text-[11px]">{{ $st['nisn'] ?? '-' }} &bull; Non-Muslim</div>
                                    </td>
                                    <td colspan="2" class="py-3 px-4 text-xs italic">{{ $st['keterangan'] }}</td>
                                </tr>
                            @else
                                <tr class="hover:bg-slate-50/60">
                                    <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $loop->iteration }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800">{{ $st['name'] }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $st['nisn'] ?? '-' }} &bull; {{ $st['gender'] === 'P' ? 'Perempuan' : 'Laki-laki' }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <select wire:model.live="inputStudents.{{ $index }}.status"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-500/30">
                                            <option value="">-- Pilih Status --</option>
                                            <option value="hadir">Hadir Berjamaah</option>
                                            <option value="ijin">Ijin / Sakit</option>
                                            @if(($st['gender'] ?? '') === 'P')
                                                <option value="haid">Halangan / Haid</option>
                                            @endif
                                            <option value="tidak_hadir">Tidak Hadir (Alpa)</option>
                                        </select>
                                    </td>
                                    <td class="py-3 px-4">
                                        <input type="text" wire:model="inputStudents.{{ $index }}.keterangan" placeholder="Catatan..."
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 focus:bg-white px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-black text-white bg-teal-600 hover:bg-teal-700 shadow-md shadow-teal-600/20 transition-all cursor-pointer">
                    <span>Simpan Presensi Sholat Dhuhur</span>
                </button>
            </div>
        </div>
    @endif
</div>

