<div class="space-y-6" x-data="{ showModalPilihJurnal: false }">
    <!-- Header Page -->
    <div class="bg-gradient-to-r from-brand-primary via-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 relative overflow-hidden border border-brand-primary/30">
        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold backdrop-blur-md mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Penugasan Guru Wali — Permendikdasmen No. 11/2025</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    {{ $kelompok->nama_kelompok }}
                </h1>
                <p class="text-white/90 text-sm mt-1">
                    Pembimbing: <strong>{{ $teacher->name }}</strong> &bull; Beban Ekuivalensi: <strong>2 JP/Minggu</strong>
                </p>
            </div>

            <!-- Action Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" 
                        @click="showModalPilihJurnal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-brand-primary font-bold text-sm shadow-md hover:bg-white/90 active:scale-95 transition-all">
                    <svg class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                    </svg>
                    <span>Catat Jurnal Baru</span>
                </button>
            </div>
        </div>

        <!-- Metric Stat Cards Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 mt-6 pt-6 border-t border-white/15">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 border border-white/10">
                <span class="text-xs text-white/80 font-medium block">Siswa Aktif</span>
                <span class="text-2xl font-black mt-0.5 block">{{ $totalAktif }} <span class="text-xs font-normal opacity-80">anak</span></span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 border border-white/10">
                <span class="text-xs text-white/80 font-medium block">Didampingi Bulan Ini</span>
                <span class="text-2xl font-black mt-0.5 block text-emerald-300">{{ $didampingiCount }} <span class="text-xs font-normal opacity-80">/ {{ $totalAktif }}</span></span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 border border-white/10">
                <span class="text-xs text-white/80 font-medium block">Kepatuhan (Min 1x/Bln)</span>
                <span class="text-2xl font-black mt-0.5 block {{ $persenDidampingi >= 100 ? 'text-emerald-300' : ($persenDidampingi >= 50 ? 'text-amber-300' : 'text-rose-300') }}">{{ $persenDidampingi }}%</span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 border border-white/10">
                <span class="text-xs text-white/80 font-medium block">Riwayat Siswa Arsip</span>
                <span class="text-2xl font-black mt-0.5 block text-slate-200">{{ $totalArsip }} <span class="text-xs font-normal opacity-80">anak</span></span>
            </div>
        </div>
    </div>

    <!-- Filter & Navigation Tabs Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Tabs -->
        <div class="inline-flex p-1 rounded-2xl bg-slate-200/70 dark:bg-slate-800">
            <button type="button" 
                    wire:click="$set('activeTab', 'aktif')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'aktif' ? 'bg-white dark:bg-slate-900 text-brand-primary shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <span>Siswa Aktif</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'aktif' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-300 text-slate-700' }}">{{ $totalAktif }}</span>
            </button>
            <button type="button" 
                    wire:click="$set('activeTab', 'arsip')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'arsip' ? 'bg-white dark:bg-slate-900 text-brand-primary shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <span>Riwayat / Arsip</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'arsip' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-300 text-slate-700' }}">{{ $totalArsip }}</span>
            </button>
        </div>

        <!-- Search Input -->
        <div class="relative w-full sm:w-72">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Cari siswa atau NISN..." 
                   class="w-full bg-white rounded-2xl border-slate-200 text-xs py-2.5 pl-3.5 pr-9 shadow-sm focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
        </div>
    </div>

    <!-- TAB 1: SISWA AKTIF -->
    @if($activeTab === 'aktif')
        @if($siswaAktifData->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($siswaAktifData as $item)
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-4.5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                        <div>
                            <!-- Header Card Siswa -->
                            <div class="flex items-start justify-between gap-2.5 mb-3.5">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img src="{{ $item->siswa?->avatar_url }}" 
                                         alt="{{ $item->siswa?->name }}" 
                                         class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl object-cover bg-slate-100 border border-slate-200 shadow-xs shrink-0">
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-xs sm:text-sm text-slate-900 leading-tight truncate" title="{{ $item->siswa?->name }}">
                                            {{ $item->siswa?->name }}
                                        </h3>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[10px] sm:text-[11px] font-mono text-slate-500 truncate">{{ $item->siswa?->nisn ?? '—' }}</span>
                                            @if($item->siswa?->enrollmentAktif?->kelas)
                                                <span class="px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20 shrink-0">
                                                    {{ $item->siswa->enrollmentAktif->kelas->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Tombol Cek Presensi Siswa -->
                                <a href="{{ route('portal-guru.student-detail', ['id' => $item->siswa?->id]) }}" 
                                   title="Buka kalender dan rekap presensi {{ $item->siswa?->name }}"
                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-xl text-[11px] font-bold bg-slate-100 hover:bg-brand-primary hover:text-white text-slate-700 border border-slate-200 hover:border-brand-primary transition-all group shrink-0 shadow-2xs">
                                    <svg class="w-3 h-3 text-slate-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    <span>Presensi</span>
                                </a>
                            </div>

                            <!-- Status Pendampingan Bulan Ini -->
                            <div class="p-3 rounded-2xl mb-4 {{ $item->has_bimbingan ? 'bg-emerald-50/80 border border-emerald-200/70' : 'bg-amber-50/80 border border-amber-200/70' }}">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold flex items-center gap-1.5 {{ $item->has_bimbingan ? 'text-emerald-700' : 'text-amber-700' }}">
                                        @if($item->has_bimbingan)
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Sudah Didampingi ({{ $item->bimbingan_count }}x)
                                        @else
                                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                            </svg>
                                            Belum Didampingi
                                        @endif
                                    </span>
                                    <span class="text-[10px] text-slate-500">{{ $namaBulanIni }}</span>
                                </div>
                                @if($item->has_bimbingan && $item->last_bimbingan_at)
                                    <div class="mt-1.5 pt-1.5 border-t border-emerald-200/50 flex justify-between items-center text-[10px] text-emerald-800">
                                        <span>Terakhir: {{ \Carbon\Carbon::parse($item->last_bimbingan_at)->translatedFormat('d M Y') }}</span>
                                        <span class="font-medium bg-emerald-100/70 px-1.5 py-0.5 rounded">{{ $item->last_kategori }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                            <a href="{{ route('portal-guru.guru-wali.jurnal.create', ['student_id' => $item->student_id]) }}" 
                                class="flex-1 text-center py-2 px-2.5 rounded-xl bg-brand-primary text-white font-bold text-xs hover:bg-brand-secondary active:scale-98 transition-all flex items-center justify-center gap-1.5 min-w-0"
                                title="Catat Jurnal Individu untuk {{ $item->siswa?->name }}">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                                </svg>
                                <span class="truncate">Catat Jurnal Individu</span>
                            </a>
                            <a href="{{ route('portal-guru.guru-wali.jurnal', ['student_id' => $item->student_id]) }}" 
                               class="py-2 px-3 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 active:scale-98 transition-all shrink-0"
                               title="Lihat riwayat jurnal siswa ini">
                                <span>Riwayat</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum Ada Siswa Aktif</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    {{ $search ? 'Tidak ada siswa aktif yang sesuai dengan pencarian.' : 'Admin belum menambahkan siswa ke dalam kelompok dampingan Anda.' }}
                </p>
            </div>
        @endif
    @endif

    <!-- TAB 2: RIWAYAT / SISWA ARSIP -->
    @if($activeTab === 'arsip')
        @if($siswaArsipList->count() > 0)
            <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="p-3.5 pl-5">Nama Siswa</th>
                                <th class="p-3.5">NISN</th>
                                <th class="p-3.5 text-center">Th. Masuk</th>
                                <th class="p-3.5">Kondisi / Status Sekarang</th>
                                <th class="p-3.5 text-right pr-5">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($siswaArsipList as $item)
                                @php
                                    $s = $item->siswa;
                                    $kelompokLain = $s?->kelompokGuruWali;
                                    $pindahKelompok = ($kelompokLain && $kelompokLain->kelompok_id !== $kelompok->id);
                                    $namaKelompokBaru = $kelompokLain?->kelompok?->nama_kelompok ?? 'Kelompok Lain';
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-3.5 pl-5 font-bold text-slate-900 flex items-center gap-3">
                                        <img src="{{ $s?->avatar_url }}" class="w-8 h-8 rounded-xl object-cover bg-slate-100 border border-slate-200">
                                        <span>{{ $s?->name }}</span>
                                    </td>
                                    <td class="p-3.5 font-mono text-slate-500">{{ $s?->nisn ?? '—' }}</td>
                                    <td class="p-3.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $item->tahun_masuk }}
                                        </span>
                                    </td>
                                    <td class="p-3.5">
                                        @if($pindahKelompok)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                                Pindah ke: {{ $namaKelompokBaru }}
                                            </span>
                                        @elseif($s?->status === 'lulus')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Sudah Lulus
                                            </span>
                                        @elseif($s?->status === 'mutasi')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                Mutasi Keluar
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                Keluar dari Kelompok
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-right pr-5">
                                        <a href="{{ route('portal-guru.guru-wali.jurnal', ['search_student' => $s?->name]) }}" 
                                           class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold transition-all inline-flex items-center gap-1">
                                            <span>Lihat Riwayat Jurnal</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak Ada Siswa Arsip</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    Semua siswa yang pernah didampingi masih berstatus aktif dalam kelompok ini.
                </p>
            </div>
        @endif
    @endif

    <!-- MODAL PILIH TIPE JURNAL (INDIVIDU ATAU KELOMPOK) -->
    <div x-show="showModalPilihJurnal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-pilih-jurnal-title" 
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop with transition -->
        <div x-show="showModalPilihJurnal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showModalPilihJurnal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showModalPilihJurnal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                
                <!-- Modal Header -->
                <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-start justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-primary/10 text-brand-primary text-[11px] font-bold mb-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Pencatatan Jurnal Siswa</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900" id="modal-pilih-jurnal-title">
                            Pilih Bentuk Bimbingan
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pilih format pencatatan jurnal yang ingin dilakukan:
                        </p>
                    </div>
                    <button type="button" 
                            @click="showModalPilihJurnal = false"
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Options Body -->
                <div class="p-6 space-y-3.5">
                    <!-- Opsi 1: Jurnal Individu -->
                    <a href="{{ route('portal-guru.guru-wali.jurnal.create') }}" 
                       class="group block p-4 rounded-2xl border-2 border-slate-200 hover:border-brand-primary hover:bg-brand-primary/[0.02] transition-all relative overflow-hidden">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0 group-hover:bg-brand-primary group-hover:text-white transition-all shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-black text-slate-900 group-hover:text-brand-primary transition-colors">
                                        Jurnal Individu
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 group-hover:bg-brand-primary/10 group-hover:text-brand-primary transition-colors">
                                        1 Siswa
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Pencatatan sesi bimbingan personal, konseling mendalam, atau penanganan kasus khusus untuk 1 orang siswa secara spesifik.
                                </p>
                            </div>
                            <div class="shrink-0 self-center text-slate-300 group-hover:text-brand-primary group-hover:translate-x-0.5 transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Opsi 2: Bimbingan Kelompok (Input Massal) -->
                    <a href="{{ route('portal-guru.guru-wali.jurnal.kelompok') }}" 
                       class="group block p-4 rounded-2xl border-2 border-slate-200 hover:border-brand-primary hover:bg-brand-primary/[0.02] transition-all relative overflow-hidden">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">
                                        Bimbingan Kelompok (Input Massal)
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                        Tabel Terpadu
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Catat sesi pembinaan bersama satu kelompok sekaligus dalam tabel terpadu mirip absensi massal. Cepat & otomatis tersimpan untuk seluruh siswa yang dipilih.
                                </p>
                            </div>
                            <div class="shrink-0 self-center text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                    <button type="button" 
                            @click="showModalPilihJurnal = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
