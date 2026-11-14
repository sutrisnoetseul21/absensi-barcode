@php
    $inputClass = 'w-full px-4 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 shadow-xs focus:outline-none focus:ring-2 focus:ring-brand-primary/25 focus:border-brand-primary transition-all disabled:opacity-65 disabled:cursor-not-allowed disabled:bg-slate-100';
    $inputSmClass = 'w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/25 focus:border-brand-primary transition-all';
@endphp

<div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6 pb-16">
    <!-- Hero Header Banner -->
    <div class="relative bg-gradient-to-r from-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 overflow-hidden border border-brand-primary/30">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white/10 rounded-full filter blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
                    Tahap 2 &bull; Struktur Jabatan ({{ $school_level }})
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold mt-3 tracking-tight">
                    Master Jabatan Sekolah
                </h1>
                <p class="text-white/85 text-xs sm:text-sm mt-1 max-w-2xl">
                    Jabatan inti seperti <strong>Kepala Sekolah</strong> dan <strong>Guru BK</strong> digunakan otomatis oleh notifikasi WhatsApp, SPIKAP, dan hak akses portal.
                </p>
            </div>
            <div class="shrink-0">
                @if($isJabatanReady)
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold bg-emerald-500/90 text-white shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Jabatan Wajib {{ $school_level }} OK
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold bg-amber-400 text-slate-900 shadow-md">
                        Belum Lengkap ({{ count($missingWajib) }} wajib belum ada)
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if(session('setup_success'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('setup_success') }}</span>
        </div>
    @endif

    @if(session('setup_error'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold shadow-xs">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('setup_error') }}</span>
        </div>
    @endif

    <!-- Gembok Banner -->
    @if($isJabatanReady)
        @if($isLocked)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-amber-50/90 border border-amber-200 text-amber-900 shadow-xs">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div class="text-xs sm:text-sm">
                        <span class="font-extrabold">Gembok Jabatan Aktif:</span>
                        <span class="text-amber-800">Jabatan wajib sudah tersedia. Buka gembok dengan password Super Admin jika ingin menambah, mengubah, atau menghapus jabatan.</span>
                    </div>
                </div>
                <button type="button" wire:click="openUnlockModal"
                        class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition-all shrink-0 self-start sm:self-auto">
                    Buka Gembok Edit
                </button>
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-900 shadow-xs">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-xs sm:text-sm">
                        <span class="font-extrabold">Gembok Terbuka:</span>
                        <span class="text-indigo-800">Anda dapat menyinkronkan, menambah, atau mengedit jabatan tambahan.</span>
                    </div>
                </div>
                <button type="button" wire:click="lockEdit"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-all shrink-0 self-start sm:self-auto">
                    Kunci Gembok Kembali
                </button>
            </div>
        @endif
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Kolom Kiri: Sinkron Seeder & Tambah Custom -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Sinkron Jabatan Standar -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900">Sinkron Jabatan Standar</h2>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Jenjang {{ $school_level }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Menambahkan otomatis jabatan baku dari seeder sesuai jenjang <strong>{{ $school_level }}</strong> tanpa duplikasi.
                </p>

                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <div class="text-[11px] font-bold text-slate-500">Daftar Jabatan Standar ({{ count($standarNames) }}):</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($standarNames as $stdName)
                            @php $already = !in_array($stdName, $missingStandar, true); @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $already ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-white border-amber-300 text-amber-800' }}">
                                {{ $stdName }} {{ $already ? '✓' : '(Belum)' }}
                            </span>
                        @endforeach
                    </div>
                </div>

                @if($isLocked)
                    <button type="button" wire:click="openUnlockModal"
                            class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-md transition-all">
                        Buka Gembok untuk Sinkronisasi
                    </button>
                @else
                    <button type="button" wire:click="syncStandarJabatan"
                            class="w-full py-3 rounded-xl bg-brand-primary hover:opacity-95 text-white text-sm font-bold shadow-md transition-all">
                        Sinkronkan Jabatan Standar {{ $school_level }}
                    </button>
                @endif
            </div>

            <!-- Tambah Jabatan Custom -->
            <form wire:submit="createJabatan" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <h2 class="text-base font-extrabold text-slate-900">Tambah Jabatan Tambahan</h2>
                <p class="text-xs text-slate-500">Jabatan tambahan akan otomatis tersedia di pengaturan penerima WA &amp; profil guru.</p>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Jabatan</label>
                    <input type="text" wire:model="nama_jabatan" placeholder="Contoh: Bendahara BOS, Kaprog TKJ" class="{{ $inputClass }}">
                    @error('nama_jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold shadow-sm transition-all">
                    + Tambah Jabatan
                </button>
            </form>
        </div>

        <!-- Kolom Kanan: Daftar Jabatan Terdaftar -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm h-fit">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Daftar Jabatan Terdaftar ({{ $jabatans->count() }})</h2>
                    <p class="text-xs text-slate-500">Jabatan berlabel <strong>Inti Sistem</strong> diproteksi agar integrasi WA &amp; SPIKAP tetap stabil.</p>
                </div>
                <a href="{{ route('portal-setup.dashboard') }}"
                   class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition-colors">
                    Ceklist Setup &rarr;
                </a>
            </div>

            @if($jabatans->isEmpty())
                <div class="text-center py-12 text-slate-500 text-sm bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    Belum ada jabatan terdaftar. Klik <strong>Sinkronkan Jabatan Standar {{ $school_level }}</strong> di samping.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($jabatans as $jab)
                        @php
                            $isCore = in_array($jab->nama_jabatan, \App\Models\Jabatan::SYSTEM_CORE_NAMES, true);
                            $isWajib = in_array($jab->nama_jabatan, $wajibNames, true);
                        @endphp
                        <div class="p-4 rounded-2xl border {{ $isWajib ? 'border-indigo-200 bg-indigo-50/30' : 'border-slate-200 bg-slate-50/60' }}">
                            @if($editingId === $jab->id)
                                <div class="space-y-2">
                                    <input type="text" wire:model="edit_nama_jabatan" class="{{ $inputSmClass }} font-bold">
                                    @error('edit_nama_jabatan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="saveEdit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold">Simpan</button>
                                        <button type="button" wire:click="cancelEdit" class="px-3 py-1.5 rounded-lg bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-extrabold text-slate-900">{{ $jab->nama_jabatan }}</span>
                                            @if($isWajib)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">Wajib {{ $school_level }}</span>
                                            @endif
                                            @if($isCore)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">Inti Sistem</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            {{ $jab->gurus_count }} guru menjabat
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @if($isCore)
                                            <span class="text-[11px] font-semibold text-slate-400 px-2 py-1">Terproteksi</span>
                                        @else
                                            <button type="button" wire:click="startEdit({{ $jab->id }})"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-200 transition-colors">
                                                Edit
                                            </button>
                                            <button type="button" wire:click="deleteJabatan({{ $jab->id }})"
                                                    wire:confirm="Hapus jabatan {{ $jab->nama_jabatan }}?"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-red-600 hover:bg-red-50 transition-colors">
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Verifikasi Password Super Admin (Opsi B) -->
    @if($showUnlockModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-md p-6 sm:p-7 space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">Buka Gembok Jabatan</h3>
                            <p class="text-xs text-slate-500">Masukkan password akun Super Admin untuk mengubah Master Jabatan.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeUnlockModal" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="verifyUnlockPassword" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Password Super Admin</label>
                        <input type="password" wire:model="unlockPassword" autofocus placeholder="Masukkan password Super Admin..." class="{{ $inputClass }}">
                        @error('unlockPassword') <p class="text-xs text-red-600 mt-1.5 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" wire:click="closeUnlockModal"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-brand-primary hover:opacity-95 text-white text-xs font-bold shadow-md transition-all">
                            <span wire:loading.remove wire:target="verifyUnlockPassword">Verifikasi &amp; Buka Gembok</span>
                            <span wire:loading wire:target="verifyUnlockPassword">Memeriksa...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

