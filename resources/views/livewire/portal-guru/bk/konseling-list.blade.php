<div class="space-y-6">
    <!-- Notifications -->
    @if(session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Header Page & Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Buku Catatan Logbook Guru BK</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Catatan Konseling Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Dokumentasi sesi konseling individual, bimbingan kelompok, mediasi, dan konferensi kasus.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('portal-guru.bk.rujukan') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition-all">
                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Lihat Rujukan Guru Wali</span>
            </a>

            <a href="{{ route('portal-guru.bk.konseling.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                </svg>
                <span>Catat Sesi Baru</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards Metric -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Sesi -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-slate-800">{{ $totalSesi }}</div>
                <div class="text-[11px] font-medium text-slate-400">Total Sesi Konseling</div>
            </div>
        </div>

        <!-- Sesi Bulan Ini -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-blue-600">{{ $totalBulanIni }}</div>
                <div class="text-[11px] font-medium text-slate-400">Konseling Bulan Ini</div>
            </div>
        </div>

        <!-- Dalam Penanganan -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-amber-600">{{ $totalDalamPenanganan }}</div>
                <div class="text-[11px] font-medium text-slate-400">Dalam Penanganan</div>
            </div>
        </div>

        <!-- Dari Rujukan Guru Wali -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-emerald-600">{{ $totalRujukanWali }}</div>
                <div class="text-[11px] font-medium text-slate-400">Dari Rujukan Wali</div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="w-full md:w-72 relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Cari siswa, topik masalah..."
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

            <!-- Filter Bidang -->
            <select wire:model.live="filterBidang" 
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium">
                <option value="">Semua Bidang</option>
                <option value="Pribadi">Pribadi</option>
                <option value="Sosial">Sosial</option>
                <option value="Belajar">Belajar</option>
                <option value="Karir">Karir</option>
            </select>

            <!-- Filter Layanan -->
            <select wire:model.live="filterLayanan" 
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium">
                <option value="">Semua Layanan</option>
                <option value="Konseling Individu">Konseling Individu</option>
                <option value="Konseling Kelompok">Konseling Kelompok</option>
                <option value="Mediasi">Mediasi</option>
                <option value="Bimbingan Klasikal">Bimbingan Klasikal</option>
                <option value="Home Visit / Kunjungan Rumah">Home Visit</option>
                <option value="Konferensi Kasus">Konferensi Kasus</option>
            </select>

            <!-- Filter Status -->
            <select wire:model.live="filterStatus" 
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium">
                <option value="">Semua Status</option>
                <option value="Dalam Penanganan">Dalam Penanganan</option>
                <option value="Bimbingan Lanjutan">Bimbingan Lanjutan</option>
                <option value="Dirujuk ke Ahli Luar">Dirujuk ke Ahli Luar</option>
                <option value="Tuntas / Selesai">Tuntas / Selesai</option>
            </select>

            <!-- Filter Bulan -->
            <input type="month" 
                   wire:model.live="bulan" 
                   class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium">
        </div>
    </div>

    <!-- Table Content Area -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($konselings->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Belum ada catatan konseling</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Mulai catat sesi bimbingan atau konseling pertama Anda untuk mendokumentasikan penanganan siswa binaan.
                </p>
                <div class="mt-4">
                    <a href="{{ route('portal-guru.bk.konseling.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-xs hover:bg-indigo-700 transition-all">
                        <span>Catat Konseling Baru</span>
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Siswa & Kelas</th>
                            <th class="py-3.5 px-4">Layanan & Bidang</th>
                            <th class="py-3.5 px-4">Topik & Kasus</th>
                            <th class="py-3.5 px-4">Status & Guru BK</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($konselings as $item)
                            @php
                                $siswa = $item->siswa;
                                $kelasModel = $siswa?->resolveKelasModel($activeYear?->id);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Siswa -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $siswa?->avatar_url }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover border border-slate-200 shrink-0">
                                        <div>
                                            <div class="font-bold text-slate-900 leading-tight">{{ $siswa?->name ?? '—' }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">
                                                NISN: {{ $siswa?->nisn ?? '—' }} &bull; <span class="font-semibold text-indigo-600">Kelas {{ $kelasModel?->name ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Layanan & Tanggal -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 leading-tight">{{ $item->jenis_layanan }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 font-semibold text-[10px]">{{ $item->bidang_bimbingan }}</span>
                                        <span>&bull; {{ $item->tanggal_waktu->translatedFormat('d M Y, H:i') }}</span>
                                    </div>
                                </td>

                                <!-- Topik & Masalah -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                        @if($item->jurnal_guru_wali_id)
                                            <span class="px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 border border-amber-200 font-bold text-[10px]">
                                                Rujukan Guru Wali
                                            </span>
                                        @endif
                                        @if($item->is_rahasia)
                                            <span class="px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200 font-bold text-[10px] flex items-center gap-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                Rahasia
                                            </span>
                                        @endif
                                    </div>
                                    <div class="font-bold text-slate-800 text-xs truncate">{{ $item->topik_masalah }}</div>
                                    <p class="text-xs text-slate-500 truncate mt-0.5">
                                        {{ $item->hasil_konseling ?: $item->uraian_kasus }}
                                    </p>
                                </td>

                                <!-- Status & Guru -->
                                <td class="py-3.5 px-4">
                                    <div>
                                        @if(in_array($item->status_kasus, ['Dalam Penanganan', 'Bimbingan Lanjutan']))
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                {{ $item->status_kasus }}
                                            </span>
                                        @elseif($item->status_kasus === 'Tuntas / Selesai')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Tuntas / Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $item->status_kasus }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        {{ $item->guru?->name ?? 'Guru BK' }}
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                wire:click="showDetail('{{ $item->id }}')" 
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition-all">
                                            Detail
                                        </button>

                                        <a href="{{ route('portal-guru.bk.konseling.edit', $item->id) }}" 
                                           class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition-all">
                                            Edit
                                        </a>

                                        <button type="button" 
                                                wire:click="confirmDelete('{{ $item->id }}')" 
                                                class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-all">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $konselings->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Detail Konseling -->
    @if($showDetailModal && $selectedKonseling)
        @php
            $detSiswa = $selectedKonseling->siswa;
            $detKelas = $detSiswa?->resolveKelasModel($activeYear?->id);
            $detRujukan = $selectedKonseling->jurnalGuruWali;
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-200 transform transition-all"
                 @click.away="$wire.closeDetailModal()">
                
                <!-- Modal Header -->
                <div class="p-5 bg-gradient-to-r from-indigo-800 to-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold leading-tight">Detail Sesi Konseling Siswa</h3>
                                @if($selectedKonseling->is_rahasia)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-500 text-white">DOKUMEN RAHASIA</span>
                                @endif
                            </div>
                            <p class="text-xs text-indigo-200 mt-0.5">{{ $selectedKonseling->jenis_layanan }} &bull; {{ $selectedKonseling->tanggal_waktu->translatedFormat('d F Y, H:i') }} WIB</p>
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
                        </div>
                    </div>

                    <!-- Meta Data Konseling -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Bidang Bimbingan</span>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm mt-0.5 block">{{ $selectedKonseling->bidang_bimbingan }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Status Kasus</span>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm mt-0.5 block">{{ $selectedKonseling->status_kasus }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 col-span-2 sm:col-span-1">
                            <span class="text-slate-400 block text-[11px]">Konselor (Guru BK)</span>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm mt-0.5 block">{{ $selectedKonseling->guru?->name ?? '—' }}</span>
                        </div>
                    </div>

                    <!-- Topik Masalah -->
                    <div>
                        <span class="text-xs font-semibold text-slate-500 block mb-1">Topik Permasalahan:</span>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-bold text-slate-800">
                            {{ $selectedKonseling->topik_masalah }}
                        </div>
                    </div>

                    <!-- Uraian Kasus & Dinamika Masalah -->
                    @if($selectedKonseling->uraian_kasus)
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block mb-1">Uraian Kasus & Gejala Masalah:</span>
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                                {{ $selectedKonseling->uraian_kasus }}
                            </div>
                        </div>
                    @endif

                    <!-- Pendekatan & Teknik -->
                    @if($selectedKonseling->pendekatan_teknik)
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block mb-1">Pendekatan / Teknik Konseling:</span>
                            <div class="p-3 rounded-xl bg-indigo-50/50 border border-indigo-100 text-xs text-indigo-900 font-medium">
                                {{ $selectedKonseling->pendekatan_teknik }}
                            </div>
                        </div>
                    @endif

                    <!-- Hasil Konseling & Kesepakatan -->
                    @if($selectedKonseling->hasil_konseling)
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block mb-1">Hasil Konseling & Kesepakatan Siswa:</span>
                            <div class="p-3.5 rounded-xl bg-emerald-50/50 border border-emerald-200 text-xs text-emerald-950 leading-relaxed whitespace-pre-line">
                                {{ $selectedKonseling->hasil_konseling }}
                            </div>
                        </div>
                    @endif

                    <!-- Rencana Tindak Lanjut -->
                    @if($selectedKonseling->rencana_tindak_lanjut)
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block mb-1">Rencana Tindak Lanjut (RTL):</span>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 whitespace-pre-line">
                                {{ $selectedKonseling->rencana_tindak_lanjut }}
                            </div>
                        </div>
                    @endif

                    <!-- Rekomendasi untuk Guru Wali -->
                    @if($selectedKonseling->rekomendasi_untuk_guru_wali)
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block mb-1">Umpan Balik & Rekomendasi untuk Guru Wali:</span>
                            <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 text-xs text-amber-950 leading-relaxed whitespace-pre-line">
                                {{ $selectedKonseling->rekomendasi_untuk_guru_wali }}
                            </div>
                        </div>
                    @endif

                    <!-- Info Rujukan Guru Wali jika ada -->
                    @if($detRujukan)
                        <div class="p-3.5 rounded-2xl bg-slate-100 text-xs text-slate-600 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-slate-800">Berasal dari Rujukan Guru Wali:</span> {{ $detRujukan->guru?->name ?? 'Guru Wali' }} ({{ $detRujukan->tanggal_waktu->translatedFormat('d M Y') }})
                            </div>
                            <a href="{{ route('portal-guru.bk.rujukan') }}" class="font-bold text-indigo-600 hover:underline">Lihat Rujukan</a>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" wire:click="closeDetailModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition-all">
                        Tutup
                    </button>

                    <a href="{{ route('portal-guru.bk.konseling.edit', $selectedKonseling->id) }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all">
                        <span>Edit Sesi Ini</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus -->
    @if($showDeleteModal && $konselingToDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-200 transform transition-all"
                 @click.away="$wire.cancelDelete()">
                <div class="p-6 text-center space-y-4">
                    <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-slate-900">Hapus Sesi Konseling?</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Anda akan menghapus sesi konseling untuk siswa <strong>{{ $konselingToDelete->siswa?->name }}</strong> (Topik: {{ $konselingToDelete->topik_masalah }}). Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div class="flex items-center justify-center gap-3 pt-2">
                        <button type="button" 
                                wire:click="cancelDelete" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-all">
                            Batal
                        </button>

                        <button type="button" 
                                wire:click="deleteKonseling" 
                                class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition-all">
                            Ya, Hapus Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
