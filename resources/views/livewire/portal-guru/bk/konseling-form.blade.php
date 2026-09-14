<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header & Navigation -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('portal-guru.bk.konseling') }}" 
               class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    {{ $isEdit ? 'Edit Catatan Konseling' : 'Catat Sesi Konseling Baru' }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Dokumentasi layanan bimbingan & konseling peserta didik secara komprehensif.
                </p>
            </div>
        </div>
    </div>

    <!-- Referral Info Banner if linked -->
    @if($referralJurnal)
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-50/90 border border-amber-200 text-amber-900 space-y-2 shadow-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-amber-500 text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="text-xs sm:text-sm font-bold text-amber-950">
                        Sesi ini ditindaklanjuti dari Rujukan Guru Wali: {{ $referralJurnal->guru?->name ?? 'Guru Wali' }}
                    </span>
                </div>
                <span class="text-[11px] font-medium text-amber-700">
                    {{ $referralJurnal->tanggal_waktu->translatedFormat('d M Y') }}
                </span>
            </div>
            <div class="text-xs text-amber-800/90 bg-white/70 p-3 rounded-xl border border-amber-200/60 leading-relaxed whitespace-pre-line">
                <strong>Catatan Kasus dari Guru Wali:</strong><br>
                {{ $referralJurnal->uraian_pembahasan }}
            </div>
        </div>
    @endif

    <!-- Main Form Card -->
    <form wire:submit.prevent="save" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-7 space-y-6">
        
        <!-- SECTION 1: IDENTITAS SISWA & WAKTU -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-black">1</span>
                <span>Sasaran & Waktu Pelaksanaan</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Siswa Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Pilih Siswa <span class="text-rose-500">*</span>
                    </label>
                    @if($isStudentLocked && $selectedStudent)
                        <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $selectedStudent->avatar_url }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $selectedStudent->name }}</div>
                                    <div class="text-[10px] text-slate-500">NISN: {{ $selectedStudent->nisn }}</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">Terkunci</span>
                        </div>
                    @else
                        <select wire:model.live="student_id" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 border @error('student_id') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all">
                            <option value="">— Pilih Siswa Binaan —</option>
                            @foreach($studentsList as $st)
                                <option value="{{ $st['id'] }}">{{ $st['name'] }} (Kelas {{ $st['kelas_name'] }} - NISN: {{ $st['nisn'] }})</option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <!-- Tanggal & Waktu -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tanggal & Waktu Konseling <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" 
                           wire:model="tanggal_waktu" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border @error('tanggal_waktu') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all">
                    @error('tanggal_waktu')
                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Student Snapshot if selected -->
            @if($selectedStudent)
                @php
                    $stdKelas = $selectedStudent->resolveKelasModel($activeYear?->id);
                @endphp
                <div class="p-3.5 rounded-2xl bg-indigo-50/40 border border-indigo-100/80 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <img src="{{ $selectedStudent->avatar_url }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-indigo-200">
                        <div>
                            <div class="font-bold text-indigo-950">{{ $selectedStudent->name }}</div>
                            <div class="text-[11px] text-slate-500">
                                Kelas: <strong>{{ $stdKelas?->name ?? '—' }}</strong> &bull; Kelamin: <strong>{{ $selectedStudent->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong>
                            </div>
                        </div>
                    </div>
                    @if($selectedStudent->no_hp_orang_tua)
                        <div class="text-right text-[11px] text-slate-500">
                            <div>Kontak Orang Tua:</div>
                            <strong class="text-slate-700">{{ $selectedStudent->no_hp_orang_tua }}</strong>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <hr class="border-slate-100">

        <!-- SECTION 2: BIDANG & FORMAT LAYANAN -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-black">2</span>
                <span>Bidang Bimbingan & Format Layanan</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Bidang Bimbingan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Bidang Bimbingan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['Pribadi', 'Sosial', 'Belajar', 'Karir'] as $bidang)
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all {{ $bidang_bimbingan === $bidang ? 'bg-indigo-50/80 border-indigo-400 text-indigo-900 font-bold' : 'bg-slate-50/60 border-slate-200 text-slate-700 hover:bg-slate-100' }}">
                                <input type="radio" wire:model.live="bidang_bimbingan" value="{{ $bidang }}" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs">{{ $bidang }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('bidang_bimbingan')
                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Layanan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Format / Jenis Layanan <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="jenis_layanan" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border @error('jenis_layanan') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all">
                        <option value="Konseling Individu">Konseling Individu</option>
                        <option value="Konseling Kelompok">Konseling Kelompok</option>
                        <option value="Mediasi">Mediasi</option>
                        <option value="Bimbingan Klasikal">Bimbingan Klasikal</option>
                        <option value="Home Visit / Kunjungan Rumah">Home Visit / Kunjungan Rumah</option>
                        <option value="Konferensi Kasus">Konferensi Kasus</option>
                    </select>
                    @error('jenis_layanan')
                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- SECTION 3: URAIAN KASUS & PENANGANAN -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-black">3</span>
                <span>Proses & Hasil Konseling</span>
            </div>

            <!-- Topik Masalah -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Topik Masalah / Pokok Pembahasan <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       wire:model="topik_masalah" 
                       placeholder="Contoh: Kesulitan konsentrasi belajar, penyesuaian sosial, motivasi..."
                       class="w-full px-3.5 py-2.5 bg-slate-50 border @error('topik_masalah') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all">
                @error('topik_masalah')
                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Uraian Kasus -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Uraian Kasus & Dinamika Masalah <span class="text-rose-500">*</span>
                    <span class="text-[11px] font-normal text-slate-400">(Catatan mendalam, dilindungi kode etik kerahasiaan BK)</span>
                </label>
                <textarea wire:model="uraian_kasus" 
                          rows="4" 
                          placeholder="Jelaskan latar belakang permasalahan, faktor pemicu, serta dinamika psikologis / perilaku yang diamati..."
                          class="w-full px-3.5 py-2.5 bg-slate-50 border @error('uraian_kasus') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all"></textarea>
                @error('uraian_kasus')
                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pendekatan & Teknik -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Pendekatan / Teknik Konseling yang Digunakan
                </label>
                <input type="text" 
                       wire:model="pendekatan_teknik" 
                       placeholder="Contoh: Cognitive Behavioral, SFBT, Kontrak Perilaku..."
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all mb-2">
                
                <!-- Quick Chips -->
                <div class="flex flex-wrap gap-1.5 items-center">
                    <span class="text-[10px] text-slate-400 font-semibold">Pilihan Cepat:</span>
                    @foreach([
                        'Cognitive Behavioral (CBT)',
                        'Solution-Focused (SFBT)',
                        'Client-Centered',
                        'Kontrak Perilaku (Behavioral)',
                        'REBT',
                        'Wawancara Motivasi'
                    ] as $chip)
                        <button type="button" 
                                wire:click="selectTeknik('{{ $chip }}')"
                                class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-600 text-[10px] font-medium transition-all">
                            + {{ $chip }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Hasil Konseling & Kesepakatan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Hasil Konseling & Kesepakatan Bersama Siswa
                </label>
                <textarea wire:model="hasil_konseling" 
                          rows="3" 
                          placeholder="Jelaskan komitmen atau kesadaran baru yang dicapai siswa selama sesi..."
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all"></textarea>
            </div>

            <!-- Rencana Tindak Lanjut -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Rencana Tindak Lanjut (RTL)
                </label>
                <textarea wire:model="rencana_tindak_lanjut" 
                          rows="2" 
                          placeholder="Jadwal sesi berikutnya, pemantauan tugas, atau koordinasi pihak lain..."
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all"></textarea>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- SECTION 4: STATUS & REKOMENDASI GURU WALI -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-black">4</span>
                <span>Status & Kolaborasi Guru Wali</span>
            </div>

            <!-- Status Kasus -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Status Kasus Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach([
                        'Dalam Penanganan',
                        'Bimbingan Lanjutan',
                        'Dirujuk ke Ahli Luar',
                        'Tuntas / Selesai'
                    ] as $stOption)
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all {{ $status_kasus === $stOption ? 'bg-indigo-50/80 border-indigo-400 text-indigo-900 font-bold' : 'bg-slate-50/60 border-slate-200 text-slate-700 hover:bg-slate-100' }}">
                            <input type="radio" wire:model.live="status_kasus" value="{{ $stOption }}" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs">{{ $stOption }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Rekomendasi untuk Guru Wali -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Umpan Balik / Rekomendasi untuk Guru Wali
                    <span class="text-[11px] font-normal text-indigo-600">(Dapat dibaca oleh Guru Wali untuk pendampingan berkala)</span>
                </label>
                <textarea wire:model="rekomendasi_untuk_guru_wali" 
                          rows="3" 
                          placeholder="Sampaikan saran umum dan hal-hal yang perlu dipantau Guru Wali di kelas (tanpa membuka privasi rahasia konseling)..."
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all"></textarea>
            </div>

            <!-- Checkbox Kerahasiaan -->
            <div class="p-3.5 rounded-2xl bg-purple-50/70 border border-purple-100 flex items-start gap-3">
                <input type="checkbox" 
                       id="is_rahasia" 
                       wire:model="is_rahasia" 
                       class="mt-1 rounded text-purple-600 focus:ring-purple-500">
                <label for="is_rahasia" class="text-xs text-purple-900 cursor-pointer">
                    <strong class="block font-bold">Tandai sebagai Catatan Rahasia (Asas Kerahasiaan BK)</strong>
                    <span class="text-[11px] text-purple-700">Rincian uraian dinamika konseling hanya dapat diakses oleh konselor/Guru BK. Hanya status dan rekomendasi umum yang dibagikan ke Guru Wali.</span>
                </label>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('portal-guru.bk.konseling') }}" 
               class="px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-bold transition-all">
                Batal
            </a>

            <button type="submit" 
                    class="inline-flex items-center gap-2 px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/25 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Sesi Konseling' }}</span>
            </button>
        </div>
    </form>
</div>
