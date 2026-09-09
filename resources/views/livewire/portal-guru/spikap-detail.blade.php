<div class="space-y-6 pb-12 max-w-7xl mx-auto">

    {{-- ── Navigasi Kembali ────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('portal-guru.spikap') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Inbox Laporan
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">ID Laporan:</span>
            <span class="font-mono text-xs font-bold bg-slate-100 text-slate-700 px-2 py-1 rounded-lg">#{{ $laporan->id }}</span>
        </div>
    </div>

    {{-- ── Banner Laporan Darurat ───────────────────────────────────────── --}}
    @if($laporan->sifat_laporan === 'darurat')
        <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-4 sm:p-5 flex items-start gap-4 text-rose-900 shadow-sm animate-pulse">
            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-rose-800">PERHATIAN: LAPORAN BERSIFAT DARURAT</h3>
                <p class="text-xs sm:text-sm text-rose-700 mt-0.5">
                    Laporan ini dikategorikan darurat oleh pelapor dan memerlukan atensi prioritas tinggi serta koordinasi segera antara Wali Kelas, Guru BK, dan Kepala Sekolah.
                </p>
            </div>
        </div>
    @endif

    {{-- ── Layout 2 Kolom ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── KOLOM UTAMA (Kiri - 2/3) ─────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Kartu Identitas Siswa Pelapor --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Identitas Siswa Pelapor
                </h2>

                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                        <img src="{{ $laporan->siswa->avatar_url }}" alt="{{ $laporan->siswa->name }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0 space-y-2">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 leading-snug">{{ $laporan->siswa->name }}</h3>
                            <p class="text-xs text-slate-500">
                                NISN: <span class="font-mono font-medium text-slate-700">{{ $laporan->siswa->nisn }}</span>
                                @if($laporan->siswa->enrollmentAktif?->kelas)
                                    • Kelas: <span class="font-semibold text-slate-800">{{ $laporan->siswa->enrollmentAktif->kelas->name }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 block">No. HP Siswa:</span>
                                @if($laporan->siswa->no_hp)
                                    <a href="https://wa.me/{{ $laporan->siswa->no_hp }}" target="_blank"
                                       class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:text-emerald-700 mt-0.5">
                                        <span>+{{ $laporan->siswa->no_hp }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                @else
                                    <span class="text-slate-500 italic mt-0.5 block">Tidak tersedia</span>
                                @endif
                            </div>

                            <div>
                                <span class="text-slate-400 block">No. HP Orang Tua:</span>
                                @php
                                    $hportu = $laporan->siswa->no_hp_orang_tua;
                                @endphp
                                @if($hportu)
                                    <a href="https://wa.me/{{ $hportu }}" target="_blank"
                                       class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:text-emerald-700 mt-0.5">
                                        <span>+{{ $hportu }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                @else
                                    <span class="text-slate-500 italic mt-0.5 block">Tidak tersedia</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Detail Kejadian --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Kronologi & Uraian Kejadian
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3.5 bg-slate-50/70 rounded-xl text-xs">
                    <div>
                        <span class="text-slate-400 block">Sifat Laporan:</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ $laporan->label_sifat }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jenis Perundungan:</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ $laporan->label_jenis }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Tujuan Awal:</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">
                            @if($laporan->tujuan_penerima === 'guru_bk')
                                Guru BK
                            @elseif($laporan->tujuan_penerima === 'wali_kelas')
                                Wali Kelas
                            @else
                                Wali Kelas & KS (Darurat)
                            @endif
                        </span>
                    </div>
                    @if($laporan->lokasi_kejadian)
                        <div>
                            <span class="text-slate-400 block">Lokasi:</span>
                            <span class="font-semibold text-slate-700 mt-0.5 block">{{ $laporan->lokasi_kejadian }}</span>
                        </div>
                    @endif
                    @if($laporan->waktu_kejadian)
                        <div class="col-span-2">
                            <span class="text-slate-400 block">Waktu Kejadian:</span>
                            <span class="font-semibold text-slate-700 mt-0.5 block">{{ $laporan->waktu_kejadian->translatedFormat('l, d F Y - H:i') }} WIB</span>
                        </div>
                    @endif
                </div>

                <div>
                    <h4 class="text-xs font-semibold text-slate-500 mb-1.5 uppercase">Uraian Kejadian:</h4>
                    <div class="p-4 bg-slate-50 rounded-xl text-slate-800 text-sm leading-relaxed whitespace-pre-line border border-slate-100">
                        {{ $laporan->uraian_kejadian }}
                    </div>
                </div>
            </div>

            {{-- 3. Lampiran Bukti --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        Lampiran Bukti ({{ $laporan->lampiran->count() }})
                    </h2>
                    <span class="text-[11px] text-slate-400">File tersimpan aman di disk lokal sekolah</span>
                </div>

                @if($laporan->lampiran->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($laporan->lampiran as $lampiran)
                            <div class="flex items-center justify-between p-3.5 bg-slate-50 border border-slate-200/70 rounded-xl gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-lg {{ $lampiran->tipe_file === 'foto' ? 'bg-indigo-100 text-indigo-600' : 'bg-purple-100 text-purple-600' }} flex items-center justify-center shrink-0">
                                        @if($lampiran->tipe_file === 'foto')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-800 truncate" title="{{ $lampiran->nama_asli }}">{{ $lampiran->nama_asli }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ strtoupper($lampiran->tipe_file) }} • {{ $lampiran->ukuran_readable }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="{{ route('spikap.lampiran.show', $lampiran->id) }}" target="_blank"
                                       class="px-2.5 py-1.5 bg-white border border-slate-200 text-slate-700 hover:text-rose-600 hover:border-rose-300 text-xs font-medium rounded-lg transition-colors inline-flex items-center gap-1 shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        Lihat
                                    </a>
                                    <a href="{{ route('spikap.lampiran.download', $lampiran->id) }}"
                                       class="p-1.5 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 text-xs rounded-lg transition-colors shadow-2xs"
                                       title="Download file">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tidak ada lampiran bukti foto atau video yang diunggah.</p>
                @endif
            </div>

            {{-- 4. Riwayat & Alur Penanganan (Audit Trail) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Penanganan & Audit Trail
                </h2>

                <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse($laporan->logStatus->sortByDesc('created_at') as $log)
                        <div class="relative">
                            {{-- Dot indicator --}}
                            <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-white border-2 border-slate-300 flex items-center justify-center">
                                <div class="w-2 h-2 rounded-full {{ $log->status_baru === 'selesai' ? 'bg-emerald-500' : ($log->status_baru === 'dalam_investigasi' ? 'bg-blue-500' : 'bg-amber-500') }}"></div>
                            </div>

                            <div class="bg-slate-50/70 border border-slate-200/60 rounded-xl p-3.5 space-y-1.5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        @if($log->status_lama)
                                            <span class="text-xs text-slate-500 line-through">{{ ucfirst(str_replace('_', ' ', $log->status_lama)) }}</span>
                                            <span class="text-xs text-slate-400">→</span>
                                        @endif
                                        <span class="text-xs font-bold text-slate-800 uppercase px-2 py-0.5 rounded-md bg-white border border-slate-200">
                                            {{ ucfirst(str_replace('_', ' ', $log->status_baru)) }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-400">
                                        {{ $log->created_at->translatedFormat('d M Y, H:i') }} ({{ $log->created_at->diffForHumans() }})
                                    </span>
                                </div>

                                <div class="text-xs text-slate-500">
                                    Dicatat oleh: <span class="font-semibold text-slate-700">{{ $log->changedBy?->name ?? 'Sistem Otomatis' }}</span>
                                </div>

                                @if($log->catatan)
                                    <div class="mt-2 text-xs text-slate-700 bg-white p-2.5 rounded-lg border border-slate-200/60 leading-relaxed whitespace-pre-line">
                                        {{ $log->catatan }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada riwayat penanganan tercatat.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ── KOLOM SAMPING (Kanan - 1/3) ──────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Status Saat Ini --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Laporan Saat Ini</h3>
                @php
                    $statusVisual = match($laporan->status) {
                        'diterima'          => ['bg-amber-100 text-amber-800 border-amber-300', '⏳ Diterima'],
                        'dalam_investigasi' => ['bg-blue-100 text-blue-800 border-blue-300',    '🔍 Dalam Investigasi'],
                        'selesai'           => ['bg-emerald-100 text-emerald-800 border-emerald-300', '✅ Selesai'],
                        default             => ['bg-slate-100 text-slate-700 border-slate-200', $laporan->status],
                    };
                @endphp
                <div class="px-4 py-3 rounded-xl border text-center font-bold text-sm {{ $statusVisual[0] }}">
                    {{ $statusVisual[1] }}
                </div>

                <div class="text-xs text-slate-500 space-y-1.5 pt-2 border-t border-slate-100">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Tanggal Masuk:</span>
                        <span class="font-medium text-slate-700">{{ $laporan->created_at->translatedFormat('d M Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Penangan Terakhir:</span>
                        <span class="font-medium text-slate-700">{{ $laporan->lastHandledBy?->name ?? 'Belum ada' }}</span>
                    </div>
                </div>
            </div>

            {{-- Form Tindak Lanjut / Update Status --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Tindak Lanjut & Status
                </h3>

                {{-- Alert Messages --}}
                @if (session()->has('success'))
                    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session()->has('info'))
                    <div class="p-3 bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-xl flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if($canUpdate)
                    <form wire:submit="updateStatus" class="space-y-4">
                        {{-- Select Status Baru --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Perbarui Status <span class="text-rose-600">*</span>
                            </label>
                            <select wire:model="statusBaru"
                                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-rose-500 focus:ring-rose-500">
                                <option value="diterima">Diterima (Menunggu Tindak Lanjut)</option>
                                <option value="dalam_investigasi">Dalam Investigasi / Mediasi</option>
                                <option value="selesai">Selesai (Kasus Ditangani)</option>
                            </select>
                            @error('statusBaru')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Catatan Tindak Lanjut --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Catatan Tindak Lanjut
                            </label>
                            <textarea wire:model="catatan"
                                      rows="4"
                                      placeholder="Tuliskan tindakan yang diambil, hasil wawancara, pemanggilan siswa, atau mediasi..."
                                      class="w-full p-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-rose-500 focus:ring-rose-500 transition-colors"></textarea>
                            @error('catatan')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-[10px] text-slate-400 mt-1">Catatan akan tercatat secara permanen di riwayat penanganan laporan.</p>
                        </div>

                        {{-- Tombol Simpan --}}
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="w-full py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-md shadow-rose-500/20 transition-all flex items-center justify-center gap-2">
                            <span wire:loading.remove>Simpan Perubahan</span>
                            <span wire:loading class="inline-flex items-center gap-1.5">
                                <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Menyimpan...
                            </span>
                        </button>
                    </form>
                @else
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 space-y-1">
                        <p class="font-semibold text-slate-700">Akses Hanya Baca (Read-Only)</p>
                        <p class="text-[11px] leading-relaxed">
                            Anda memiliki izin memantau laporan ini, namun kewenangan memperbarui status hanya dimiliki oleh guru penerima resmi (Wali Kelas / Guru BK terkait).
                        </p>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
