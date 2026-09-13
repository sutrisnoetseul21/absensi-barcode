<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Page -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('portal-guru.guru-wali.jurnal') }}" 
               class="p-2.5 rounded-2xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 active:scale-95 transition-all shadow-xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-brand-primary/10 text-brand-primary text-[10px] font-bold mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Mode Personal / Individu</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ $jurnalId ? 'Edit Jurnal Siswa' : 'Catat Jurnal Individu' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Kelompok: <strong>{{ $kelompok->nama_kelompok }}</strong> &bull; Pendampingan personal 1 peserta didik binaan.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('portal-guru.guru-wali.kelompok') }}" 
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Anggota Kelompok</span>
            </a>
        </div>
    </div>

    <!-- Alert jika dari konsultasi -->
    @if($konsultasi_id)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3">
            <div class="p-2 rounded-xl bg-amber-100 text-amber-700 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-amber-900">Konversi Permintaan Konsultasi Siswa</h4>
                <p class="text-xs text-amber-700 mt-0.5">
                    Menyimpan jurnal ini akan otomatis menandai permintaan konsultasi terkait sebagai <strong>"Dikonversi ke Jurnal"</strong> dan mengaitkannya ke riwayat pendampingan ini.
                </p>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 sm:p-8 space-y-6">

            <!-- 1. IDENTITAS SISWA & WAKTU -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Siswa Binaan -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Siswa Binaan <span class="text-rose-500">*</span>
                    </label>

                    @if($isStudentLocked && $selectedStudent)
                        <!-- Tampilan Terkunci Khusus Siswa Terpilih (Non-Dropdown) -->
                        <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/90 flex items-center justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $selectedStudent->avatar_url }}" 
                                     alt="{{ $selectedStudent->name }}" 
                                     class="w-11 h-11 rounded-2xl object-cover bg-white border border-slate-200 shrink-0 shadow-2xs">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-900 truncate">{{ $selectedStudent->name }}</h3>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20 shrink-0">
                                            Siswa Terpilih
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs font-mono text-slate-500">{{ $selectedStudent->nisn ?? 'NISN —' }}</span>
                                        @if($selectedStudent->enrollmentAktif?->kelas)
                                            <span class="text-xs text-slate-600 font-semibold">
                                                &bull; Kelas {{ $selectedStudent->enrollmentAktif->kelas->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(!$jurnalId && !$konsultasi_id)
                                <button type="button" 
                                        wire:click="$set('isStudentLocked', false)"
                                        class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-white text-slate-600 font-bold text-xs transition-all shrink-0 hover:border-slate-300"
                                        title="Pilih siswa lain dari daftar kelompok">
                                    Pilih Lain
                                </button>
                            @endif
                        </div>
                    @else
                        <!-- Dropdown Pilihan Siswa -->
                        <select id="student_id" 
                                wire:model.live="student_id"
                                class="w-full rounded-2xl border-slate-200 text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs bg-slate-50/50">
                            <option value="">-- Pilih Siswa Anggota Kelompok --</option>
                            @foreach($anggotaList as $anggota)
                                <option value="{{ $anggota->student_id }}">
                                    {{ $anggota->siswa?->name }} ({{ $anggota->siswa?->nisn ?? 'NISN -' }}) - Kelas {{ $anggota->siswa?->enrollmentAktif?->kelas?->name ?? 'Belum ada kelas' }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                        @enderror

                        <!-- Preview Siswa Terpilih jika dipilih via dropdown -->
                        @if($selectedStudent)
                            <div class="mt-2 p-3 rounded-xl bg-brand-primary/5 border border-brand-primary/20 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-brand-primary/10 text-brand-primary font-bold flex items-center justify-center shrink-0 text-xs">
                                    {{ strtoupper(substr($selectedStudent->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $selectedStudent->name }}</p>
                                    <p class="text-[11px] text-slate-500">
                                        NISN: {{ $selectedStudent->nisn ?? '-' }} &bull; Kelas: {{ $selectedStudent->enrollmentAktif?->kelas?->name ?? 'Belum ada kelas' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Waktu Pelaksanaan -->
                <div>
                    <label for="tanggal_waktu" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Tanggal & Waktu Sesi <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" 
                           id="tanggal_waktu" 
                           wire:model="tanggal_waktu" 
                           class="w-full rounded-2xl border-slate-200 text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs">
                    @error('tanggal_waktu')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-slate-400 mt-1">Waktu nyata saat sesi pendampingan berlangsung.</p>
                </div>
            </div>

            <!-- 2. BENTUK & KATEGORI PENDAMPINGAN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-4 border-t border-slate-100">
                <!-- Bentuk Pendampingan: Terkunci Khusus Individu -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Bentuk Pendampingan
                    </label>
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-brand-primary/5 border border-brand-primary/20 text-brand-primary">
                        <div class="w-8 h-8 rounded-xl bg-brand-primary text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold">Jurnal Individu (1 Siswa)</p>
                            <p class="text-[11px] text-slate-500">Khusus pencatatan personal/kasus personal siswa</p>
                        </div>
                    </div>
                </div>

                <!-- Kategori Pendampingan (4 Pilar Permendikdasmen 11/2025) -->
                <div>
                    <label for="kategori_pendampingan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Kategori Pembahasan <span class="text-rose-500">*</span>
                    </label>
                    <select id="kategori_pendampingan" 
                            wire:model="kategori_pendampingan" 
                            class="w-full rounded-2xl border-slate-200 text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs bg-white">
                        <option value="Akademik">📚 Akademik (Hasil Belajar, Kesulitan Materi)</option>
                        <option value="Karakter & Kedisiplinan">🎯 Karakter & Kedisiplinan (Ketertiban, Etika, Kehadiran)</option>
                        <option value="Minat & Bakat / Ekskul">🎨 Minat & Bakat / Ekskul (Potensi, Pengembangan Diri)</option>
                        <option value="Sosial & Psikologis">🌱 Sosial & Psikologis (Hubungan Teman, Kesejahteraan Emosional)</option>
                    </select>
                    @error('kategori_pendampingan')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 3. URAIAN PEMBAHASAN -->
            <div class="pt-4 border-t border-slate-100">
                <label for="uraian_pembahasan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                    Uraian Pembahasan & Temuan Bimbingan <span class="text-rose-500">*</span>
                </label>
                <textarea id="uraian_pembahasan" 
                          wire:model="uraian_pembahasan" 
                          rows="4" 
                          placeholder="Deskripsikan kondisi siswa, hal yang dikonsultasikan, observasi guru, dan solusi/arahan yang diberikan..." 
                          class="w-full rounded-2xl border-slate-200 text-sm p-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs leading-relaxed"></textarea>
                @error('uraian_pembahasan')
                    <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 4. STATUS SESI & RUJUKAN KOLABORASI -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-4 border-t border-slate-100">
                <!-- Status Sesi -->
                <div>
                    <label for="status_sesi" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Status Sesi Pendampingan <span class="text-rose-500">*</span>
                    </label>
                    <select id="status_sesi" 
                            wire:model="status_sesi" 
                            class="w-full rounded-2xl border-slate-200 text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs">
                        <option value="Tuntas / Selesai">✅ Tuntas / Selesai (Masalah/diskusi teratasi)</option>
                        <option value="Dalam Pemantauan">👀 Dalam Pemantauan (Perlu diobservasi dalam waktu dekat)</option>
                        <option value="Bimbingan Lanjutan">🔄 Bimbingan Lanjutan (Dijadwalkan sesi berikutnya)</option>
                    </select>
                    @error('status_sesi')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rujukan Kolaborasi (Wajib Kolaboratif sesuai Permen 11/2025) -->
                <div>
                    <label for="rujukan_kolaborasi" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Kolaborasi / Rujukan <span class="text-rose-500">*</span>
                    </label>
                    <select id="rujukan_kolaborasi" 
                            wire:model="rujukan_kolaborasi" 
                            class="w-full rounded-2xl border-slate-200 text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs">
                        <option value="Mandiri">🟢 Mandiri (Cukup ditangani Guru Wali)</option>
                        <option value="Wali Kelas">🤝 Wali Kelas (Koordinasi capaian & absensi kelas)</option>
                        <option value="Guru BK">🚨 Guru BK (Masalah psikologis mendalam / kasus khusus)</option>
                        <option value="Orang Tua / Wali">👨‍👩‍👧 Orang Tua / Wali (Perlu pendampingan di rumah)</option>
                    </select>
                    @error('rujukan_kolaborasi')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 5. RENCANA TINDAK LANJUT -->
            <div class="pt-4 border-t border-slate-100">
                <label for="rencana_tindak_lanjut" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                    Rencana Tindak Lanjut (Opsional)
                </label>
                <textarea id="rencana_tindak_lanjut" 
                          wire:model="rencana_tindak_lanjut" 
                          rows="2" 
                          placeholder="Langkah tindak lanjut berikutnya oleh guru wali, wali kelas, guru BK, atau siswa sendiri..." 
                          class="w-full rounded-2xl border-slate-200 text-sm p-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs"></textarea>
                @error('rencana_tindak_lanjut')
                    <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 6. PENGATURAN PRIVASI CATATAN -->
            <div class="pt-4 border-t border-slate-100">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4">
                    <div>
                        <span class="text-sm font-bold text-slate-800 block">Bagikan Catatan ke Siswa (Publik di Portal Siswa)</span>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Jika aktif, saran dan rangkuman arahan ini dapat dibaca oleh siswa terkait di portal siswa. Jika nonaktif, catatan hanya terlihat oleh Guru Wali (privat).
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" wire:model="is_public_note" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                    </label>
                </div>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('portal-guru.guru-wali.jurnal') }}" 
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 active:scale-95 transition-all">
                    Batal
                </a>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-secondary text-white font-bold text-xs shadow-md shadow-brand-primary/20 active:scale-95 transition-all inline-flex items-center gap-2">
                    <span wire:loading.remove>{{ $jurnalId ? 'Simpan Perubahan' : 'Simpan Jurnal Pendampingan' }}</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
