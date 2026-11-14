@php
    $inputClass = 'w-full px-4 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 shadow-xs focus:outline-none focus:ring-2 focus:ring-brand-primary/25 focus:border-brand-primary transition-all disabled:opacity-65 disabled:cursor-not-allowed disabled:bg-slate-100';
    $inputSmClass = 'w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-primary/25 focus:border-brand-primary transition-all';
    $schoolLocked = $schoolReady && !$isUnlocked;
    $yearLocked = $yearReady && !$isUnlocked;
    $classLocked = $classReady && !$isUnlocked;
    $hasAnyConfigured = $schoolReady || $yearReady || $classReady;
@endphp

<div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6 pb-16">
    <!-- Hero Header Banner (Senada dengan Portal Guru & Perpustakaan) -->
    <div class="relative bg-gradient-to-r from-brand-primary to-brand-secondary rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-primary/20 overflow-hidden border border-brand-primary/30">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white/10 rounded-full filter blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
                    Tahap 1 &bull; Fondasi Sistem
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold mt-3 tracking-tight">
                    Setting Sekolah, Tahun Ajaran &amp; Kelas
                </h1>
                <p class="text-white/85 text-xs sm:text-sm mt-1 max-w-2xl">
                    Lengkapi ketiga langkah berikut sebelum melakukan upload data Guru dan Siswa.
                </p>
            </div>
            @if($allReady)
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold bg-emerald-500/90 text-white shadow-md self-start md:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Semua Setup Dasar OK
                </div>
            @endif
        </div>

        <!-- 3-Step Tab Switcher -->
        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6">
            <button type="button" wire:click="switchTab('sekolah')"
                    class="flex items-center justify-between p-3.5 rounded-2xl border text-left transition-all {{ $activeTab === 'sekolah' ? 'bg-white text-slate-900 border-white shadow-lg' : 'bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-xs' }}">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-extrabold {{ $activeTab === 'sekolah' ? 'bg-brand-primary text-white' : 'bg-white/20 text-white' }}">1</span>
                    <div>
                        <div class="text-sm font-bold">Identitas &amp; Jenjang</div>
                        <div class="text-[11px] {{ $activeTab === 'sekolah' ? 'text-slate-500' : 'text-white/75' }}">Jenjang: {{ $school_level }}</div>
                    </div>
                </div>
                @if($schoolReady)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">OK</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Belum</span>
                @endif
            </button>

            <button type="button" wire:click="switchTab('tahun-ajaran')" @disabled(!$schoolReady)
                    title="{{ !$schoolReady ? 'Simpan Identitas Sekolah dulu' : '' }}"
                    class="flex items-center justify-between p-3.5 rounded-2xl border text-left transition-all {{ !$schoolReady ? 'bg-white/5 text-white/45 border-white/10 cursor-not-allowed' : ($activeTab === 'tahun-ajaran' ? 'bg-white text-slate-900 border-white shadow-lg' : 'bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-xs') }}">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-extrabold {{ $activeTab === 'tahun-ajaran' ? 'bg-brand-primary text-white' : 'bg-white/20 text-white' }}">2</span>
                    <div>
                        <div class="text-sm font-bold">Tahun Ajaran</div>
                        <div class="text-[11px] {{ $activeTab === 'tahun-ajaran' ? 'text-slate-500' : 'text-white/75' }}">{{ !$schoolReady ? 'Terkunci (Isi Langkah 1)' : ($years->firstWhere('status', 'aktif')?->name ?? 'Belum ada aktif') }}</div>
                    </div>
                </div>
                @if(!$schoolReady)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-white/70">Terkunci</span>
                @elseif($yearReady)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">OK</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Belum</span>
                @endif
            </button>

            <button type="button" wire:click="switchTab('kelas')" @disabled(!$schoolReady)
                    title="{{ !$schoolReady ? 'Simpan Identitas Sekolah dulu' : '' }}"
                    class="flex items-center justify-between p-3.5 rounded-2xl border text-left transition-all {{ !$schoolReady ? 'bg-white/5 text-white/45 border-white/10 cursor-not-allowed' : ($activeTab === 'kelas' ? 'bg-white text-slate-900 border-white shadow-lg' : 'bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-xs') }}">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-extrabold {{ $activeTab === 'kelas' ? 'bg-brand-primary text-white' : 'bg-white/20 text-white' }}">3</span>
                    <div>
                        <div class="text-sm font-bold">Master Kelas</div>
                        <div class="text-[11px] {{ $activeTab === 'kelas' ? 'text-slate-500' : 'text-white/75' }}">{{ !$schoolReady ? 'Terkunci (Isi Langkah 1)' : ($classes->count() . ' Kelas terdaftar') }}</div>
                    </div>
                </div>
                @if(!$schoolReady)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-white/70">Terkunci</span>
                @elseif($classReady)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">OK</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Belum</span>
                @endif
            </button>
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

    <!-- Lock / Unlock Banner (Proteksi Perubahan Tidak Sengaja) -->
    @if($hasAnyConfigured)
        @if(!$isUnlocked)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-amber-50/90 border border-amber-200 text-amber-900 shadow-xs">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div class="text-xs sm:text-sm">
                        <span class="font-extrabold">Mode Terkunci (Aman):</span>
                        <span class="text-amber-800">Pengaturan yang sudah terisi dilindungi agar tidak berubah secara tidak sengaja.</span>
                    </div>
                </div>
                <button type="button" wire:click="openUnlockModal"
                        class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition-all shrink-0 self-start sm:self-auto">
                    Buka Kunci Edit
                </button>
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-900 shadow-xs">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-xs sm:text-sm">
                        <span class="font-extrabold">Kunci Edit Terbuka:</span>
                        <span class="text-indigo-800">Anda dapat mengubah identitas sekolah, mengganti tahun ajaran aktif, atau mengedit/menghapus kelas.</span>
                    </div>
                </div>
                <button type="button" wire:click="lockEdit"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-all shrink-0 self-start sm:self-auto">
                    Kunci Kembali
                </button>
            </div>
        @endif
    @endif

    <!-- ==================== TAB 1: IDENTITAS & JENJANG SEKOLAH ==================== -->
    @if($activeTab === 'sekolah')
        <form wire:submit="saveSchool" class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">1. Pilih Jenjang Sekolah</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pilihan jenjang menentukan opsi tingkat kelas (1–6 SD, 7–9 SMP, 10–12 SMA, atau 10–13 SMK beserta Jurusan).</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                    @foreach([
                        'SD'  => ['label' => 'SD / MI', 'desc' => 'Kelas 1 – 6'],
                        'SMP' => ['label' => 'SMP / MTs', 'desc' => 'Kelas 7 – 9'],
                        'SMA' => ['label' => 'SMA / MA', 'desc' => 'Kelas X – XII'],
                        'SMK' => ['label' => 'SMK / MAK', 'desc' => 'Kelas X – XIII + Jurusan'],
                    ] as $code => $info)
                        <label class="{{ $schoolLocked ? 'cursor-not-allowed opacity-70' : 'cursor-pointer' }} rounded-2xl border p-4 transition-all {{ $school_level === $code ? 'bg-indigo-50/70 border-brand-primary ring-2 ring-brand-primary/20' : 'bg-slate-50/50 border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" wire:model.live="school_level" value="{{ $code }}" class="sr-only" @disabled($schoolLocked)>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-extrabold {{ $school_level === $code ? 'text-brand-primary' : 'text-slate-800' }}">{{ $info['label'] }}</span>
                                @if($school_level === $code)
                                    <span class="w-3 h-3 rounded-full bg-brand-primary"></span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 mt-1">{{ $info['desc'] }}</div>
                        </label>
                    @endforeach
                </div>

                <!-- Status Sekolah: Negeri / Swasta -->
                <div class="mt-5">
                    <label class="block text-xs font-bold text-slate-700 mb-2">Status Sekolah</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach([
                            'negeri' => ['label' => 'Sekolah Negeri', 'desc' => 'Menggunakan NIP untuk Kepala Sekolah & ASN'],
                            'swasta' => ['label' => 'Sekolah Swasta / Yayasan', 'desc' => 'Menggunakan NIY / NUPTK untuk Kepala Sekolah & Guru'],
                        ] as $statusCode => $statusInfo)
                            <label class="{{ $schoolLocked ? 'cursor-not-allowed opacity-70' : 'cursor-pointer' }} rounded-2xl border p-3.5 transition-all {{ $school_status === $statusCode ? 'bg-indigo-50/70 border-brand-primary ring-2 ring-brand-primary/20' : 'bg-slate-50/50 border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" wire:model.live="school_status" value="{{ $statusCode }}" class="sr-only" @disabled($schoolLocked)>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-extrabold {{ $school_status === $statusCode ? 'text-brand-primary' : 'text-slate-800' }}">{{ $statusInfo['label'] }}</span>
                                    @if($school_status === $statusCode)
                                        <span class="w-3 h-3 rounded-full bg-brand-primary"></span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $statusInfo['desc'] }}</div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Sekolah <span class="text-rose-600">*</span></label>
                    <input type="text" wire:model="school_name" placeholder="Contoh: SMK Negeri 1 Kedungreja" class="{{ $inputClass }}" @disabled($schoolLocked)>
                    @error('school_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap Sekolah <span class="text-rose-600">*</span></label>
                    <textarea wire:model="school_address" rows="2" placeholder="Jl. Raya Pendidikan No. 1, Kecamatan, Kabupaten" class="{{ $inputClass }}" @disabled($schoolLocked)></textarea>
                    @error('school_address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kota / Kabupaten</label>
                    <input type="text" wire:model="kota" placeholder="Contoh: Cilacap" class="{{ $inputClass }}" @disabled($schoolLocked)>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Resmi Sekolah</label>
                    <input type="email" wire:model="school_email" placeholder="info@sekolah.sch.id" class="{{ $inputClass }}" @disabled($schoolLocked)>
                    @error('school_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Telepon Sekolah</label>
                    <input type="text" wire:model="school_phone" placeholder="(0282) 123456" class="{{ $inputClass }}" @disabled($schoolLocked)>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kepala Sekolah</label>
                    <input type="text" wire:model="principal_name" placeholder="Nama lengkap & gelar" class="{{ $inputClass }}" @disabled($schoolLocked)>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        {{ $school_status === 'swasta' ? 'NIY / NUPTK Kepala Sekolah' : 'NIP Kepala Sekolah' }}
                    </label>
                    <input type="text" wire:model="principal_nip"
                           placeholder="{{ $school_status === 'swasta' ? 'Contoh: NIY / NUPTK (Opsional)' : 'Contoh: 197501012000121001 (Opsional)' }}"
                           class="{{ $inputClass }}" @disabled($schoolLocked)>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Logo Sekolah (PNG/JPG, Maks. 2MB)</label>
                    <div class="flex flex-wrap items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300">
                        @if($logo_upload)
                            <img src="{{ $logo_upload->temporaryUrl() }}" class="w-16 h-16 rounded-xl object-contain border border-slate-200 p-1 bg-white">
                        @elseif($existing_logo_path)
                            <img src="{{ asset('storage/' . $existing_logo_path) }}" class="w-16 h-16 rounded-xl object-contain border border-slate-200 p-1 bg-white">
                        @endif
                        <input type="file" wire:model="logo_upload" accept="image/*" @disabled($schoolLocked)
                               class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    </div>
                    @error('logo_upload') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        {{ $school_status === 'swasta' ? 'Logo Yayasan (Opsional, PNG/JPG)' : 'Logo Kabupaten / Pemda (Opsional, PNG/JPG)' }}
                    </label>
                    <div class="flex flex-wrap items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300">
                        @if($district_logo_upload)
                            <img src="{{ $district_logo_upload->temporaryUrl() }}" class="w-16 h-16 rounded-xl object-contain border border-slate-200 p-1 bg-white">
                        @elseif($existing_district_logo_path)
                            <img src="{{ asset('storage/' . $existing_district_logo_path) }}" class="w-16 h-16 rounded-xl object-contain border border-slate-200 p-1 bg-white">
                        @endif
                        <input type="file" wire:model="district_logo_upload" accept="image/*" @disabled($schoolLocked)
                               class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Ditampilkan di sisi kiri Kartu OSIS &amp; Kop Surat.</p>
                    @error('district_logo_upload') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanda Tangan Kepala Sekolah (Opsional, PNG Transparan)</label>
                    <div class="flex flex-wrap items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300">
                        @if($signature_upload)
                            <img src="{{ $signature_upload->temporaryUrl() }}" class="w-24 h-16 rounded-xl object-contain border border-slate-200 p-1 bg-white">
                        @elseif($existing_signature_path)
                            <img src="{{ asset('storage/' . $existing_signature_path) }}" class="w-24 h-16 rounded-xl object-contain border border-slate-200 p-1 bg-white">
                        @endif
                        <input type="file" wire:model="signature_upload" accept="image/png" @disabled($schoolLocked)
                               class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Gunakan format PNG background transparan untuk hasil terbaik di Kartu OSIS &amp; Rapor.</p>
                    @error('signature_upload') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-slate-100">
                <span class="text-xs text-slate-500">Pengaturan lanjutan (tema, jam scan, WA, CBT) tetap tersedia di Panel Admin.</span>
                <div class="flex items-center gap-3">
                    @if($schoolLocked)
                        <button type="button" wire:click="openUnlockModal"
                                class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-md transition-all">
                            Buka Kunci untuk Edit
                        </button>
                    @else
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-brand-primary hover:opacity-95 text-white text-sm font-bold shadow-md transition-all">
                            <span wire:loading.remove wire:target="saveSchool">Simpan Identitas Sekolah</span>
                            <span wire:loading wire:target="saveSchool">Menyimpan...</span>
                        </button>
                    @endif
                    @if($schoolReady)
                        <button type="button" wire:click="switchTab('tahun-ajaran')"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition-all">
                            Lanjut: Tahun Ajaran &rarr;
                        </button>
                    @endif
                </div>
            </div>
        </form>
    @endif

    <!-- ==================== TAB 2: TAHUN AJARAN ==================== -->
    @if($activeTab === 'tahun-ajaran')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Tambah Tahun Ajaran -->
            <form wire:submit="createTahunAjaran" class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-5 h-fit">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Tambah Tahun Ajaran</h2>
                        <p class="text-xs text-slate-500">Contoh: 2026 / 2027</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Mulai</label>
                        <input type="number" wire:model.live="start_year" min="2000" max="2099" @disabled($yearLocked) class="{{ $inputClass }} text-center text-base font-bold">
                        @error('start_year') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Selesai</label>
                        <input type="number" wire:model="end_year" min="2000" max="2100" @disabled($yearLocked) class="{{ $inputClass }} text-center text-base font-bold">
                        @error('end_year') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">Preview Label:</span>
                    <span class="text-base font-extrabold text-brand-primary">{{ $start_year }}/{{ $end_year }}</span>
                </div>

                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 {{ $yearLocked ? 'cursor-not-allowed opacity-65' : 'cursor-pointer hover:bg-slate-100/70' }} transition-colors">
                    <input type="checkbox" wire:model="set_active" @disabled($yearLocked) class="w-4 h-4 rounded border-slate-300 text-brand-primary focus:ring-brand-primary">
                    <span class="font-semibold text-xs sm:text-sm">Langsung jadikan Tahun Ajaran Aktif</span>
                </label>

                @if($yearLocked)
                    <button type="button" wire:click="openUnlockModal"
                            class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-md transition-all">
                        Buka Kunci untuk Tambah Tahun Ajaran
                    </button>
                @else
                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-brand-primary hover:opacity-95 text-white text-sm font-bold shadow-md transition-all">
                        + Simpan Tahun Ajaran
                    </button>
                @endif
            </form>

            <!-- Daftar Tahun Ajaran -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Daftar Tahun Ajaran ({{ $years->count() }})</h2>
                        <p class="text-xs text-slate-500">Hanya 1 tahun ajaran yang aktif pada satu waktu.</p>
                    </div>
                    <button type="button" wire:click="$set('activeTab', 'kelas')"
                            class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition-colors">
                        Lanjut: Master Kelas &rarr;
                    </button>
                </div>

                @if($years->isEmpty())
                    <div class="text-center py-12 text-slate-500 text-sm bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        Belum ada Tahun Ajaran. Silakan tambah melalui form di samping.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($years as $year)
                            <div class="p-4 rounded-2xl border {{ $year->status === 'aktif' ? 'bg-emerald-50/40 border-emerald-200' : 'bg-slate-50/60 border-slate-200' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-lg font-extrabold text-slate-900">{{ $year->name }}</span>
                                        @if($year->status === 'aktif')
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Aktif</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200/80 text-slate-600">Arsip</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ $year->kelas_ajarans_count }} rombel terhubung &bull; {{ $year->enrollments_count }} enrollment siswa
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($year->status !== 'aktif')
                                        <button type="button" wire:click="activateTahunAjaran('{{ $year->id }}')"
                                                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors">
                                            Jadikan Aktif
                                        </button>
                                        <button type="button" wire:click="deleteTahunAjaran('{{ $year->id }}')"
                                                wire:confirm="Yakin hapus Tahun Ajaran {{ $year->name }}?"
                                                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                </div>
            </div>
        </div>
    @endif

    <!-- ==================== TAB 3: MASTER KELAS ==================== -->
    @if($activeTab === 'kelas')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Kolom Kiri: Generate Massal & Tambah Satuan -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Generate Massal -->
                <form wire:submit="generateBulkClasses" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-extrabold text-slate-900">Generate Massal Kelas</h2>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Jenjang {{ $school_level }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">Buat banyak rombel sekaligus (contoh: 7A–7F atau X TKJ 1–3).</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tingkat</label>
                            <select wire:model.live="bulk_grade_level" @disabled($classLocked) class="{{ $inputClass }}">
                                @foreach($gradeOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Format Tingkat</label>
                            <select wire:model.live="bulk_format" @disabled($classLocked) class="{{ $inputClass }}">
                                <option value="angka">Angka (7, 10)</option>
                                <option value="romawi">Romawi (VII, X)</option>
                            </select>
                        </div>
                    </div>

                    @if(in_array($school_level, ['SMA', 'SMK'], true))
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Jurusan / Kompetensi Keahlian {{ $school_level === 'SMK' ? '(Disarankan)' : '(Opsional)' }}
                            </label>
                            <input type="text" wire:model.live.debounce.200ms="bulk_major" @disabled($classLocked) placeholder="Contoh: TKJ, RPL, AKL, MIPA" class="{{ $inputClass }}">
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Pola Akhiran</label>
                            <select wire:model.live="bulk_pattern" @disabled($classLocked) class="{{ $inputClass }}">
                                <option value="huruf">Huruf (A, B, C...)</option>
                                <option value="angka">Angka (1, 2, 3...)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jumlah Rombel</label>
                            <input type="number" wire:model.live="bulk_count" min="1" max="15" @disabled($classLocked) class="{{ $inputClass }} text-center font-bold">
                        </div>
                    </div>

                    @if(!empty($bulkPreviewNames))
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="text-[11px] font-bold text-slate-500 mb-2">Preview Nama Kelas:</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($bulkPreviewNames as $previewName)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-700 shadow-2xs">{{ $previewName }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($classLocked)
                        <button type="button" wire:click="openUnlockModal"
                                class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-md transition-all">
                            Buka Kunci untuk Generate Kelas
                        </button>
                    @else
                        <button type="submit"
                                class="w-full py-3 rounded-xl bg-brand-primary hover:opacity-95 text-white text-sm font-bold shadow-md transition-all">
                            Generate {{ count($bulkPreviewNames) }} Kelas Sekaligus
                        </button>
                    @endif
                </form>

                <!-- Tambah Satuan -->
                <form wire:submit="createSingleClass" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <h2 class="text-base font-extrabold text-slate-900">Tambah Kelas Satuan</h2>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tingkat</label>
                        <select wire:model="class_grade_level" @disabled($classLocked) class="{{ $inputClass }}">
                            @foreach($gradeOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(in_array($school_level, ['SMA', 'SMK'], true))
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jurusan / Keahlian (Opsional)</label>
                            <input type="text" wire:model="class_major" @disabled($classLocked) placeholder="Contoh: TKJ, RPL, IPS" class="{{ $inputClass }}">
                        </div>
                    @endif
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kelas</label>
                        <input type="text" wire:model="class_name" @disabled($classLocked) placeholder="Contoh: 7A atau X TKJ 1" class="{{ $inputClass }}">
                        @error('class_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    @if($classLocked)
                        <button type="button" wire:click="openUnlockModal"
                                class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-sm transition-all">
                            Buka Kunci untuk Tambah Kelas
                        </button>
                    @else
                        <button type="submit"
                                class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold shadow-sm transition-all">
                            + Tambah Kelas
                        </button>
                    @endif
                </form>
            </div>

            <!-- Kolom Kanan: Daftar Kelas -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm h-fit">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Daftar Kelas Terdaftar ({{ $classes->count() }})</h2>
                        <p class="text-xs text-slate-500">Dikelompokkan berdasarkan tingkat kelas.</p>
                    </div>
                    <a href="{{ route('portal-setup.dashboard') }}"
                       class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition-colors">
                        Lihat Ringkasan Ceklist &rarr;
                    </a>
                </div>

                @if($classes->isEmpty())
                    <div class="text-center py-12 text-slate-500 text-sm bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        Belum ada kelas. Gunakan <strong>Generate Massal Kelas</strong> di samping untuk membuat dengan cepat.
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($groupedClasses as $grade => $items)
                            <div>
                                <div class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-2.5 flex items-center gap-2">
                                    <span>Tingkat {{ $grade }}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px]">{{ $items->count() }} kelas</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($items as $kelas)
                                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white transition-colors">
                                            @if($editingClassId === $kelas->id)
                                                <div class="space-y-2">
                                                    <input type="text" wire:model="edit_class_name" class="{{ $inputSmClass }} font-bold">
                                                    @error('edit_class_name') <p class="text-[11px] text-red-600">{{ $message }}</p> @enderror
                                                    @if(in_array($school_level, ['SMA', 'SMK'], true))
                                                        <input type="text" wire:model="edit_class_major" placeholder="Jurusan" class="{{ $inputSmClass }}">
                                                    @endif
                                                    <div class="flex items-center gap-1.5">
                                                        <button type="button" wire:click="saveEditClass" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">Simpan</button>
                                                        <button type="button" wire:click="cancelEditClass" class="px-3 py-1.5 rounded-lg bg-slate-200 text-slate-700 text-[11px] font-bold">Batal</button>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-extrabold text-slate-900 truncate">{{ $kelas->name }}</div>
                                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                                            @if($kelas->major)
                                                                <span class="font-semibold text-indigo-700">{{ $kelas->major }}</span> &bull;
                                                            @endif
                                                            {{ $kelas->enrollments_count }} siswa
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        <button type="button" wire:click="startEditClass('{{ $kelas->id }}')"
                                                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-600 hover:bg-slate-200 transition-colors">
                                                            Edit
                                                        </button>
                                                        <button type="button" wire:click="deleteClass('{{ $kelas->id }}')"
                                                                wire:confirm="Hapus kelas {{ $kelas->name }}?"
                                                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-red-600 hover:bg-red-50 transition-colors">
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

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
                            <h3 class="text-lg font-extrabold text-slate-900">Verifikasi Super Admin</h3>
                            <p class="text-xs text-slate-500">Masukkan password akun Super Admin untuk membuka kunci edit.</p>
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
                            <span wire:loading.remove wire:target="verifyUnlockPassword">Verifikasi &amp; Buka Kunci</span>
                            <span wire:loading wire:target="verifyUnlockPassword">Memeriksa...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
