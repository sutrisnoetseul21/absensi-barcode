<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8" x-data="{ showCardModal: false }">
    
    <!-- Top Hero Banner & Profile Header -->
    <div class="relative bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/80 overflow-hidden">
        <!-- Sleek Banner Cover -->
        <div class="h-36 sm:h-40 bg-gradient-to-r from-brand-primary via-indigo-600 to-purple-600 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10 backdrop-blur-[1px]"></div>
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <!-- Left Header Pill Tag -->
            <div class="absolute top-4 left-6 flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-xs font-semibold shadow-sm">
                <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Portal Siswa &bull; Profil Akun</span>
            </div>

            <!-- Right Header Class Badge -->
            <div class="absolute top-4 right-6">
                @if($enrollment && $enrollment->kelas && $enrollment->kelas->name)
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-extrabold bg-white/20 text-white backdrop-blur-md shadow-md border border-white/30 tracking-wide">
                        <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Kelas {{ $enrollment->kelas->name }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-md shadow-md border border-white/30">
                        Kelas -
                    </span>
                @endif
            </div>
        </div>

        <!-- Profile Details Body -->
        <div class="px-6 sm:px-8 pb-6 pt-0 relative">
            <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-5 -mt-14 sm:-mt-16">
                
                <!-- Left: Avatar + Name + Badges -->
                <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left w-full">
                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white p-1.5 shadow-2xl ring-4 ring-white overflow-hidden bg-slate-100 flex items-center justify-center">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" alt="Preview Foto" class="w-full h-full object-cover rounded-2xl">
                            @elseif ($photo_path)
                                <img src="{{ asset('storage/' . $photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover rounded-2xl">
                            @else
                                <div class="w-full h-full rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white font-black text-4xl shadow-inner">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Name & Information -->
                    <div class="space-y-2 sm:-mt-4 w-full">
                        <div class="flex items-center justify-center sm:justify-start gap-3 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none">{{ $student->name }}</h1>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ ucfirst($student->status ?? 'Aktif') }}
                            </span>
                        </div>

                        <!-- Info Badges Row -->
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-2.5 sm:pt-3">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80 shadow-xs">
                                <span class="text-slate-400">NISN:</span> <strong class="font-bold text-slate-900">{{ $student->nisn }}</strong>
                            </span>

                            @if($student->nis)
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80 shadow-xs">
                                    <span class="text-slate-400">NIS:</span> <strong class="font-bold text-slate-900">{{ $student->nis }}</strong>
                                </span>
                            @endif

                            @if($enrollment && $enrollment->tahunAjaran && $enrollment->tahunAjaran->name)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/80 shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    TA {{ $enrollment->tahunAjaran->name }}
                                </span>
                            @endif

                            <a href="#kartu-digital-siswa" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-extrabold bg-amber-500 hover:bg-amber-600 text-white shadow-md transition-all">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Lihat Kartu Siswa
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Column 1: Read-Only Academic & Personal Info Card -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Quick Actions Card (Foto Profil Upload) -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 rounded-xl text-brand-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">Ubah Foto Profil</h2>
                    </div>
                </div>

                <!-- Session Flash Message for Photo -->
                @if (session()->has('success_photo'))
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs font-semibold">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success_photo') }}</span>
                    </div>
                @endif

                <form wire:submit.prevent="updatePhoto" class="space-y-4">
                    <div x-data="{
                        compressing: false,
                        handleFileSelect(event) {
                            const file = event.target.files[0];
                            if (!file || !file.type.startsWith('image/')) return;

                            this.compressing = true;
                            const maxWidth = 400;
                            const maxHeight = 400;
                            const quality = 0.75;

                            const reader = new FileReader();
                            reader.onload = (e) => {
                                const img = new Image();
                                img.onload = () => {
                                    let width = img.width;
                                    let height = img.height;

                                    if (width > maxWidth || height > maxHeight) {
                                        if (width / height > maxWidth / maxHeight) {
                                            height = Math.round((height * maxWidth) / width);
                                            width = maxWidth;
                                        } else {
                                            width = Math.round((width * maxHeight) / height);
                                            height = maxHeight;
                                        }
                                    }

                                    const canvas = document.createElement('canvas');
                                    canvas.width = width;
                                    canvas.height = height;

                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(img, 0, 0, width, height);

                                    canvas.toBlob((blob) => {
                                        this.compressing = false;
                                        if (!blob) return;
                                        const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', {
                                            type: 'image/jpeg',
                                            lastModified: Date.now()
                                        });
                                        $wire.upload('photo', compressedFile,
                                            () => {},
                                            () => { alert('Gagal mengunggah foto, coba lagi.'); }
                                        );
                                    }, 'image/jpeg', quality);
                                };
                                img.src = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    }">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih File Foto</label>
                        <input type="file" accept="image/png, image/jpeg, image/jpg, image/webp"
                            @change="handleFileSelect($event)"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-brand-primary hover:file:bg-indigo-100 transition-all cursor-pointer border border-slate-200 rounded-xl p-1 bg-slate-50">
                        @error('photo') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror

                        {{-- Status compress di browser --}}
                        <div x-show="compressing" style="display:none;" class="text-xs text-amber-600 mt-1.5 font-semibold flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengompres foto di browser...
                        </div>
                        {{-- Status upload ke server --}}
                        <div wire:loading wire:target="photo" class="text-xs text-brand-primary mt-1.5 font-semibold flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengunggah ke server...
                        </div>

                        <p class="text-[11px] text-slate-400 mt-1.5">Format: JPG, PNG, WEBP — Otomatis dikompres di browser sebelum diunggah</p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-primary hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-brand-primary/25 transition-all disabled:opacity-50">
                            <span wire:loading.remove wire:target="updatePhoto">Simpan Foto</span>
                            <span wire:loading wire:target="updatePhoto" class="flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Memproses...
                            </span>
                        </button>

                        @if ($photo_path)
                            <button type="button" wire:click="removePhoto" wire:confirm="Yakin ingin menghapus foto profil?"
                                class="px-3 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold rounded-xl transition-all" title="Hapus Foto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Read-only Info List Card -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-amber-50 rounded-xl text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">Data Siswa (Read-Only)</h2>
                    </div>
                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 bg-slate-100 text-slate-500 rounded-md">Terkunci</span>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <span class="text-slate-400 font-semibold block text-[11px] uppercase tracking-wider">Nama Lengkap</span>
                        <p class="font-bold text-slate-900 text-sm">{{ $student->name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                            <span class="text-slate-400 font-semibold block text-[11px] uppercase tracking-wider">NISN</span>
                            <p class="font-bold text-slate-900">{{ $student->nisn }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                            <span class="text-slate-400 font-semibold block text-[11px] uppercase tracking-wider">NIS</span>
                            <p class="font-bold text-slate-900">{{ $student->nis ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <span class="text-slate-400 font-semibold block text-[11px] uppercase tracking-wider">Tempat & Tanggal Lahir</span>
                        <p class="font-bold text-slate-900">
                            {{ $student->birth_place ?? '-' }}, {{ $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '-' }}
                        </p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <span class="text-slate-400 font-semibold block text-[11px] uppercase tracking-wider">Kelas & Tahun Ajaran</span>
                        <p class="font-bold text-slate-900">
                            @if($enrollment && $enrollment->kelas)
                                Kelas {{ $enrollment->kelas->name }}
                            @else
                                -
                            @endif
                            @if($enrollment && $enrollment->tahunAjaran)
                                ({{ $enrollment->tahunAjaran->name }})
                            @endif
                        </p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                        <span class="text-slate-400 font-semibold block text-[11px] uppercase tracking-wider">Kode Barcode Presensi</span>
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-mono font-bold text-brand-primary text-sm tracking-wider">
                                {{ $student->barcode_code ?? $student->nisn }}
                            </p>
                            <a href="#kartu-digital-siswa" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-primary hover:bg-indigo-700 text-white shadow-sm transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Lihat Kartu
                            </a>
                        </div>
                    </div>
                </div>

                <div class="pt-2 text-[11px] text-slate-400 italic">
                    * Data bertanda terkunci hanya dapat diubah melalui Tata Usaha / Admin Sekolah.
                </div>
            </div>
        </div>

        <!-- Column 2 & 3: Kartu Digital, Form Edit Alamat & Form Ganti Password -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Card 0: Kartu Presensi Siswa Digital & Cetak Mandiri -->
            <div id="kartu-digital-siswa" class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 sm:p-8 space-y-6 scroll-mt-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-amber-50 rounded-2xl text-amber-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">Kartu Presensi Siswa Digital</h2>
                            <p class="text-xs font-medium text-slate-500">Pratinjau fisik kartu presensi siswa dan opsi cetak/simpan PDF mandiri.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Ukuran ID Card (54x86mm)
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                    <!-- Left: Real Physical Card Preview -->
                    <div class="md:col-span-5 flex flex-col items-center justify-center p-6 bg-slate-900/95 rounded-3xl border border-slate-800 shadow-xl">
                        <x-kartu-siswa-card :student="$student" />
                    </div>

                    <!-- Right: Details & Action Controls -->
                    <div class="md:col-span-7 space-y-5">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2.5 text-xs">
                            <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                                <span class="text-slate-400 font-medium">Nama Siswa</span>
                                <strong class="text-slate-900 font-bold text-sm">{{ $student->name }}</strong>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                                <span class="text-slate-400 font-medium">Kode Barcode / NISN</span>
                                <strong class="font-mono font-bold text-brand-primary text-sm">{{ $student->barcode_code ?? $student->nisn }}</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400 font-medium">Status Kartu</span>
                                <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-brand-primary hover:bg-indigo-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-brand-primary/25 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Cetak Kartu Siswa
                            </button>

                            <a href="{{ route('portal-siswa.cetak-kartu', ['download' => 1]) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-emerald-600/25 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Simpan Berkas PDF
                            </a>
                        </div>

                        <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-2xl text-indigo-900 text-xs space-y-1.5">
                            <h5 class="font-bold flex items-center gap-1.5 text-indigo-900">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Petunjuk Cetak / Simpan PDF:
                            </h5>
                            <p class="text-[11px] text-indigo-700 leading-relaxed">
                                Klik <strong>Simpan Berkas PDF</strong> lalu pada dialog printer ubah <em>Destination / Tujuan</em> menjadi <strong>"Save as PDF"</strong>. Ukuran kartu otomatis disetel 54mm x 86mm.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 1: Form Edit Alamat -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-emerald-50 rounded-2xl text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">Alamat Tempat Tinggal</h2>
                            <p class="text-xs font-medium text-slate-500">Perbarui informasi domisili tempat tinggal Anda yang aktif.</p>
                        </div>
                    </div>
                </div>

                <!-- Session Flash Message for Address -->
                @if (session()->has('success_address'))
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold animate-fade-in">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success_address') }}</span>
                    </div>
                @endif

                <form wire:submit.prevent="updateAddress" class="space-y-4">
                    <div>
                        <label for="address" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Alamat Lengkap</label>
                        <textarea id="address" wire:model="address" rows="3"
                            placeholder="Tuliskan nama jalan, RT/RW, kelurahan, kecamatan, kota/kabupaten..."
                            class="w-full px-4 py-3 text-sm rounded-2xl border border-slate-200 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10 transition-all placeholder:text-slate-400 bg-slate-50/50 focus:bg-white resize-none"></textarea>
                        @error('address') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-emerald-600/25 transition-all disabled:opacity-50">
                            <span wire:loading.remove wire:target="updateAddress" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Perubahan Alamat
                            </span>
                            <span wire:loading wire:target="updateAddress" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Form Ganti Password -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 sm:p-8 space-y-6" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-indigo-50 rounded-2xl text-brand-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">Keamanan & Ganti Sandi</h2>
                            <p class="text-xs font-medium text-slate-500">Gunakan kata sandi kombinasi aman untuk mengamankan akun Anda.</p>
                        </div>
                    </div>
                </div>

                <!-- Session Flash Message for Password -->
                @if (session()->has('success_password'))
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold animate-fade-in">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success_password') }}</span>
                    </div>
                @endif

                <form wire:submit.prevent="updatePassword" class="space-y-4">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Password Saat Ini</label>
                        <div class="relative">
                            <input :type="showCurrent ? 'text' : 'password'" id="current_password" wire:model="current_password"
                                placeholder="Masukkan password lama Anda"
                                class="w-full px-4 py-3 pr-11 text-sm rounded-2xl border border-slate-200 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10 transition-all placeholder:text-slate-400 bg-slate-50/50 focus:bg-white">
                            <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <svg x-show="!showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a9.957 9.957 0 013.785-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @error('current_password') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- New Password & Confirmation Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Password Baru</label>
                            <div class="relative">
                                <input :type="showNew ? 'text' : 'password'" id="new_password" wire:model="new_password"
                                    placeholder="Minimal 8 karakter"
                                    class="w-full px-4 py-3 pr-11 text-sm rounded-2xl border border-slate-200 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10 transition-all placeholder:text-slate-400 bg-slate-50/50 focus:bg-white">
                                <button type="button" @click="showNew = !showNew" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg x-show="!showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 013.785-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/></svg>
                                </button>
                            </div>
                            @error('new_password') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Ulangi Password Baru</label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" id="new_password_confirmation" wire:model="new_password_confirmation"
                                    placeholder="Ulangi password baru"
                                    class="w-full px-4 py-3 pr-11 text-sm rounded-2xl border border-slate-200 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10 transition-all placeholder:text-slate-400 bg-slate-50/50 focus:bg-white">
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 013.785-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-brand-primary hover:bg-indigo-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-brand-primary/25 transition-all disabled:opacity-50">
                            <span wire:loading.remove wire:target="updatePassword" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Perbarui Kata Sandi
                            </span>
                            <span wire:loading wire:target="updatePassword" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
