<div class="max-w-5xl mx-auto space-y-6 pb-12">
    <!-- Top Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('portal-siswa.ijin') }}" 
               class="group flex items-center justify-center w-11 h-11 rounded-2xl bg-white border border-slate-200/80 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50/50 shadow-sm transition-all duration-200"
               title="Kembali ke Daftar Pengajuan">
                <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ $recordId ? 'Edit Pengajuan Ijin / Sakit' : 'Buat Pengajuan Baru' }}
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $recordId ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-indigo-50 text-indigo-700 border border-indigo-200/60' }}">
                        {{ $recordId ? 'Mode Edit' : 'Formulir Digital' }}
                    </span>
                </div>
                <p class="text-slate-500 text-sm mt-0.5">Isi detail pengajuan ijin atau sakit dengan benar.</p>
            </div>
        </div>

        @if($student)
            <div class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-white border border-slate-200/80 shadow-sm self-start sm:self-auto">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 font-bold text-xs flex items-center justify-center border border-indigo-100 uppercase">
                    {{ substr($student->name ?? 'S', 0, 2) }}
                </div>
                <div class="text-left text-xs">
                    <div class="font-bold text-slate-800 line-clamp-1">{{ $student->name }}</div>
                    <div class="text-slate-400 font-medium">
                        NISN: {{ $student->nisn }}
                        @if($student->enrollmentAktif?->kelas)
                            • Kelas {{ $student->enrollmentAktif->kelas->name }}
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Form Main -->
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Kolom Kiri: Tipe, Waktu & Durasi (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-5">
                    
                    <!-- Tipe Pengajuan (Segmented Switch Simpel & Modern) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">
                            Tipe Pengajuan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/60">
                            <!-- Tab Ijin -->
                            <button type="button" 
                                    wire:click="$set('type', 'ijin')"
                                    class="flex items-center justify-center gap-2.5 py-2.5 px-4 rounded-xl text-sm font-bold transition-all duration-200 cursor-pointer {{ $type === 'ijin' ? 'bg-white text-indigo-600 shadow-sm border border-slate-200/60 ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}">
                                <svg class="w-4 h-4 {{ $type === 'ijin' ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Ijin</span>
                                @if($type === 'ijin')
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                @endif
                            </button>

                            <!-- Tab Sakit -->
                            <button type="button" 
                                    wire:click="$set('type', 'sakit')"
                                    class="flex items-center justify-center gap-2.5 py-2.5 px-4 rounded-xl text-sm font-bold transition-all duration-200 cursor-pointer {{ $type === 'sakit' ? 'bg-white text-rose-600 shadow-sm border border-slate-200/60 ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}">
                                <svg class="w-4 h-4 {{ $type === 'sakit' ? 'text-rose-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                                <span>Sakit</span>
                                @if($type === 'sakit')
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                @endif
                            </button>
                        </div>
                        @error('type') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Tanggal Mulai & Durasi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                                Tanggal Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" wire:model.live="start_date" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/10 shadow-sm transition-all">
                            @error('start_date') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Durasi (Hari) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                                Durasi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <input type="number" wire:model.live.debounce.500ms="duration_days" min="1" max="30"
                                       class="w-full pl-3.5 pr-14 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/10 shadow-sm transition-all">
                                <span class="absolute right-3.5 text-xs font-medium text-slate-400 pointer-events-none">Hari</span>
                            </div>
                            @error('duration_days') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Quick Preset Pills -->
                    <div>
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Cepat Durasi:</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach([1, 2, 3, 5, 7] as $dayOption)
                                <button type="button" 
                                        wire:click="setDuration({{ $dayOption }})"
                                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer {{ (int)$duration_days === $dayOption ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20 ring-2 ring-indigo-600/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                                    {{ $dayOption }} Hari
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Visual Timeline Summary Card -->
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-50 to-indigo-50/40 border border-slate-200/80">
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <div class="flex-1">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Mulai</span>
                                <span class="font-bold text-slate-800 text-sm">
                                    {{ $start_date ? \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y') : '-' }}
                                </span>
                                <span class="text-slate-500 block text-[11px]">
                                    {{ $start_date ? \Carbon\Carbon::parse($start_date)->translatedFormat('l') : '' }}
                                </span>
                            </div>

                            <div class="flex flex-col items-center px-3 py-1 bg-white rounded-xl border border-indigo-100 shadow-xs">
                                <span class="text-[10px] font-extrabold text-indigo-600 uppercase">{{ $duration_days ?? 1 }} HARI</span>
                                <svg class="w-4 h-4 text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </div>

                            <div class="flex-1 text-right">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Selesai (Otomatis)</span>
                                <span class="font-bold text-slate-800 text-sm">
                                    {{ $end_date ? \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y') : '-' }}
                                </span>
                                <span class="text-slate-500 block text-[11px]">
                                    {{ $end_date ? \Carbon\Carbon::parse($end_date)->translatedFormat('l') : '' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Holiday Notice Warning -->
                    @if(count($holidayMessages) > 0)
                        <div class="p-4 bg-amber-50/80 border border-amber-200/70 rounded-2xl">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wide">Pemberitahuan Hari Libur</h4>
                                    <ul class="mt-1 space-y-1">
                                        @foreach($holidayMessages as $msg)
                                            <li class="text-xs text-amber-800 leading-relaxed">{{ $msg }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Kolom Kanan: Alasan & Upload Lampiran (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Card Alasan Lengkap -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-3">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Alasan Pengajuan <span class="text-rose-500">*</span>
                    </label>
                    
                    <div class="relative">
                        <textarea wire:model="reason" rows="4" 
                                  class="w-full p-3.5 rounded-xl border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/10 shadow-sm transition-all" 
                                  placeholder="Tuliskan keterangan detail alasan tidak dapat hadir..."></textarea>
                    </div>
                    @error('reason') <p class="text-rose-500 text-xs font-medium">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-slate-400">Contoh: Menghadiri acara keluarga / Mengalami demam dan dianjurkan istirahat dokter.</p>
                </div>

                <!-- Card Lampiran Bukti / Surat Keterangan -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            Lampiran Bukti
                        </label>
                        <span class="text-[11px] text-slate-400 font-medium">Opsional</span>
                    </div>

                    <!-- Drag & Drop / File Input Box -->
                    <div class="relative group">
                        <input type="file" wire:model="attachments" id="file_upload_input" accept=".pdf,image/jpeg,image/png,image/webp" multiple class="sr-only">
                        
                        <label for="file_upload_input" 
                               class="flex flex-col items-center justify-center p-5 border-2 border-dashed border-slate-200 rounded-2xl hover:border-indigo-400 hover:bg-indigo-50/30 cursor-pointer transition-all duration-200 text-center group">
                            
                            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-2.5 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-200 shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>

                            <span class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">
                                Unggah Surat / Foto Bukti
                            </span>
                            <span class="text-xs text-slate-400 mt-0.5">
                                Klik untuk memilih file dari galeri / perangkat
                            </span>
                            
                            <div class="flex items-center gap-1.5 mt-3">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[10px]">JPG</span>
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[10px]">PNG</span>
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[10px]">PDF</span>
                                <span class="text-[10px] text-slate-400 ml-1">Maks. 2MB</span>
                            </div>
                        </label>
                    </div>

                    <!-- Upload Loading State -->
                    <div wire:loading wire:target="attachments" class="w-full p-3 bg-indigo-50/70 border border-indigo-100 rounded-xl text-center">
                        <div class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-700">
                            <svg class="animate-spin h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sedang memproses & mengunggah file...
                        </div>
                    </div>

                    <!-- Uploaded File Preview -->
                    @if($attachments && count($attachments) > 0)
                        <div class="space-y-2">
                        @foreach($attachments as $index => $file)
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="truncate text-xs">
                                    <span class="font-bold text-emerald-900 block truncate">{{ $file->getClientOriginalName() }}</span>
                                    <span class="text-emerald-700 text-[10px]">File baru siap dikirim</span>
                                </div>
                            </div>
                            <button type="button" wire:click="removeAttachment({{ $index }})" class="text-slate-400 hover:text-rose-600 p-1.5 transition-colors cursor-pointer" title="Hapus file ini">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        @endforeach
                        </div>
                    @endif

                    <!-- Existing Files If Editing -->
                    @if($existing_file_paths && count($existing_file_paths) > 0)
                        <div class="space-y-2">
                        @foreach($existing_file_paths as $path)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                </div>
                                <div class="truncate text-xs">
                                    <span class="font-bold text-slate-800 block truncate">Lampiran Tersimpan</span>
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank" class="text-indigo-600 hover:underline text-[11px] font-semibold inline-flex items-center gap-1">
                                        Lihat / Unduh Dokumen
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        </div>
                    @endif

                    @error('attachments') <p class="text-rose-500 text-xs font-medium">{{ $message }}</p> @enderror
                    @error('attachments.*') <p class="text-rose-500 text-xs font-medium">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        <!-- Action Footer -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Pengajuan akan diverifikasi oleh Wali Kelas / Admin Presensi.</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('portal-siswa.ijin') }}" 
                   class="w-1/2 sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 transition-colors text-center">
                    Batal
                </a>

                <button type="submit" 
                        class="w-1/2 sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold shadow-md shadow-indigo-600/25 hover:shadow-indigo-600/35 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer" 
                        wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $recordId ? 'Perbarui Pengajuan' : 'Kirim Pengajuan' }}
                    </span>
                    <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
