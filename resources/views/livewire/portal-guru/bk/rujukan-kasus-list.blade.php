<div class="space-y-6">
    <!-- Header Page & Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Layanan Bimbingan & Konseling (BK)</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Rujukan Kasus Guru Wali</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Daftar kasus siswa yang dilimpahkan oleh Guru Wali untuk intervensi dan konseling lebih lanjut.
                @if(!$canAccessAll && count($accessibleClasses) > 0)
                    &bull; Kelas Binaan: <strong>{{ $accessibleClasses->pluck('name')->join(', ') }}</strong>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('portal-guru.bk.konseling.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                </svg>
                <span>Catat Konseling Baru</span>
            </a>
        </div>
    </div>

    @if(!$canAccessAll && empty($accessibleClassIds))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <strong>Belum ada kelas binaan yang ditugaskan.</strong>
                <p class="mt-0.5 text-amber-700">Hubungi Administrator untuk mengatur akses kelas pantau BK Anda pada menu Manajemen Akses Portal.</p>
            </div>
        </div>
    @endif

    <!-- Stat Cards Metric -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Rujukan -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-slate-800">{{ $totalRujukan }}</div>
                <div class="text-[11px] font-medium text-slate-400">Total Rujukan Masuk</div>
            </div>
        </div>

        <!-- Menunggu Tindak Lanjut -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-rose-600">{{ $totalMenunggu }}</div>
                <div class="text-[11px] font-medium text-slate-400">Perlu Tindak Lanjut</div>
            </div>
        </div>

        <!-- Sedang Ditangani -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-amber-600">{{ $totalProses }}</div>
                <div class="text-[11px] font-medium text-slate-400">Dalam Penanganan</div>
            </div>
        </div>

        <!-- Tuntas -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-emerald-600">{{ $totalSelesai }}</div>
                <div class="text-[11px] font-medium text-slate-400">Selesai / Tuntas</div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="w-full md:w-80 relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Cari nama atau NISN siswa..."
                   class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all">
        </div>

        <div class="w-full md:w-auto flex flex-wrap items-center gap-2">
            <!-- Filter Kelas -->
            @if(count($accessibleClasses) > 1 || $canAccessAll)
                <select wire:model.live="filterClass" 
                        class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium">
                    <option value="">Semua Kelas</option>
                    @foreach($accessibleClasses as $cls)
                        <option value="{{ $cls->id }}">Kelas {{ $cls->name }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Filter Status -->
            <select wire:model.live="filterStatus" 
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium">
                <option value="all">Semua Status</option>
                <option value="menunggu">Perlu Tindak Lanjut (Belum Ada BK)</option>
                <option value="proses">Dalam Penanganan</option>
                <option value="selesai">Tuntas / Selesai</option>
            </select>
        </div>
    </div>

    <!-- Table / Content Area -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($rujukans->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Tidak ada rujukan kasus</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Belum ada jurnal Guru Wali yang dirujuk ke Guru BK untuk kelas binaan Anda atau sesuai filter yang dipilih.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Siswa & Kelas</th>
                            <th class="py-3.5 px-4">Guru Wali & Tanggal</th>
                            <th class="py-3.5 px-4">Fokus Permasalahan</th>
                            <th class="py-3.5 px-4">Status Penanganan BK</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rujukans as $rujukan)
                            @php
                                $siswa = $rujukan->siswa;
                                $kelasModel = $siswa?->resolveKelasModel($activeYear?->id);
                                $hasKonseling = $rujukan->teacher_id !== null;
                                $statusBk = $hasKonseling ? $rujukan->status_kasus->value : 'Menunggu Tindak Lanjut';
                                $guruWaliName = $rujukan->konsultasiGuruWali?->guru?->name ?? $rujukan->jurnalGuruWali?->guru?->name ?? 'Sistem';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Siswa -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center border border-indigo-200 shrink-0">
                                            {{ substr($siswa?->name ?? 'S', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 leading-tight">{{ $siswa?->name ?? '—' }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">
                                                NISN: {{ $siswa?->nisn ?? '—' }} &bull; <span class="font-semibold text-indigo-600">Kelas {{ $kelasModel?->name ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Guru Wali -->
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800 leading-tight">{{ $guruWaliName }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $rujukan->created_at->translatedFormat('d M Y, H:i') }} WIB
                                    </div>
                                </td>

                                <!-- Fokus & Masalah -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-100 text-orange-700 mb-1">
                                        Alasan Rujukan
                                    </div>
                                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                        {{ $rujukan->alasan_rujukan }}
                                    </p>
                                </td>

                                <!-- Status Penanganan -->
                                <td class="py-3.5 px-4">
                                    @if(!$hasKonseling)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            Perlu Tindak Lanjut
                                        </span>
                                    @elseif(in_array($statusBk, ['Dalam Proses']))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $statusBk }}
                                        </span>
                                    @elseif(in_array($statusBk, ['Selesai', 'Dikonversi ke Jurnal']))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $statusBk }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                wire:click="showDetail('{{ $rujukan->id }}')" 
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition-all">
                                            Detail
                                        </button>

                                        @if(!$hasKonseling)
                                            <button wire:click="terimaRujukan('{{ $rujukan->id }}')" 
                                               class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                                                Tangani Kasus
                                            </button>
                                        @else
                                            <a href="{{ route('portal-guru.bk.konseling.edit', $rujukan->id) }}" 
                                               class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all">
                                                Lembar Konseling
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $rujukans->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Detail Rujukan -->
    @if($showDetailModal && $selectedRujukan)
        @php
            $detSiswa = $selectedRujukan->siswa;
            $detKelas = $detSiswa?->resolveKelasModel($activeYear?->id);
            $guruWaliName = $selectedRujukan->konsultasiGuruWali?->guru?->name ?? $selectedRujukan->jurnalGuruWali?->guru?->name ?? 'Sistem';
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-200 transform transition-all"
                 @click.away="$wire.closeDetailModal()">
                
                <!-- Modal Header -->
                <div class="p-5 bg-gradient-to-r from-indigo-700 to-indigo-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold leading-tight">Detail Rujukan Kasus Guru Wali</h3>
                            <p class="text-xs text-indigo-200 mt-0.5">ID Rujukan: {{ substr($selectedRujukan->id, 0, 8) }} &bull; {{ $selectedRujukan->created_at->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="p-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                    <!-- Student Card Snapshot -->
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <img src="{{ $detSiswa?->avatar_url }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover border border-slate-200">
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-slate-900 text-sm sm:text-base">{{ $detSiswa?->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap gap-x-3 gap-y-1">
                                <span>Kelas: <strong class="text-indigo-600">{{ $detKelas?->name ?? '—' }}</strong></span>
                                <span>NISN: <strong>{{ $detSiswa?->nisn ?? '—' }}</strong></span>
                                <span>Kelamin: <strong>{{ $detSiswa?->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong></span>
                            </div>
                            @if($detSiswa?->no_hp_orang_tua)
                                <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span>Kontak Wali: {{ $detSiswa->nama_ayah ?? $detSiswa->nama_ibu ?? 'Orang Tua' }} ({{ $detSiswa->no_hp_orang_tua }})</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informasi Dari Guru Wali -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <span>Informasi dari Guru Wali</span>
                            <span class="h-px flex-1 bg-slate-200"></span>
                        </h4>

                        <div class="grid grid-cols-1 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 block text-[11px]">Guru Wali Perujuk</span>
                                <span class="font-bold text-slate-800 text-xs sm:text-sm mt-0.5 block">{{ $guruWaliName }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="text-xs font-semibold text-slate-600 block mb-1">Alasan Merujuk Kasus:</span>
                            <div class="p-3.5 rounded-xl bg-orange-50 border border-orange-200 text-xs sm:text-sm text-orange-900 italic leading-relaxed whitespace-pre-line">
                                "{{ $selectedRujukan->alasan_rujukan }}"
                            </div>
                        </div>
                    </div>

                    <!-- Status / Catatan Sesi BK -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <span>Hasil Tindak Lanjut Guru BK</span>
                            <span class="h-px flex-1 bg-slate-200"></span>
                        </h4>

                        @if($selectedRujukan->teacher_id)
                            <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-indigo-900">Sesi Konseling: {{ $selectedRujukan->tanggal_waktu ? $selectedRujukan->tanggal_waktu->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum dijadwalkan' }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">
                                        {{ $selectedRujukan->status_kasus->value }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-700">
                                    <strong>Layanan:</strong> {{ $selectedRujukan->jenis_layanan ?? '-' }} ({{ $selectedRujukan->bidang_bimbingan ?? '-' }})
                                </div>
                                @if($selectedRujukan->rekomendasi_untuk_guru_wali)
                                    <div>
                                        <span class="text-[11px] font-semibold text-indigo-900 block mb-0.5">Umpan Balik / Rekomendasi untuk Guru Wali:</span>
                                        <div class="p-2.5 rounded-xl bg-white border border-indigo-200/80 text-xs text-slate-800 leading-relaxed whitespace-pre-line">
                                            {{ $selectedRujukan->rekomendasi_untuk_guru_wali }}
                                        </div>
                                    </div>
                                @endif
                                <div class="text-[11px] text-slate-500">
                                    Ditangani oleh: <strong>{{ $selectedRujukan->guru?->name ?? 'Guru BK' }}</strong>
                                </div>
                            </div>
                        @else
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-3">
                                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Kasus ini belum ditangani resmi oleh Guru BK. Klik "Tangani Kasus" untuk mulai menangani kasus ini.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" wire:click="closeDetailModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition-all">
                        Tutup
                    </button>

                    @if(!$selectedRujukan->teacher_id)
                        <button wire:click="terimaRujukan('{{ $selectedRujukan->id }}')" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 active:scale-95 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                            </svg>
                            <span>Tangani Kasus</span>
                        </button>
                    @else
                        <a href="{{ route('portal-guru.bk.konseling.edit', $selectedRujukan->id) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-xs transition-all">
                            <span>Buka Lembar Konseling</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
