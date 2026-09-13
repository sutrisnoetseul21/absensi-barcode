<div class="max-w-6xl mx-auto space-y-6">
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
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-brand-primary/10 text-brand-primary">
                        Bimbingan Kelompok
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Permendikdasmen No. 11/2025</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                    Catat Jurnal Bimbingan Kelompok
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Kelompok: <strong>{{ $kelompok->nama_kelompok }}</strong> &bull; Input massal pendampingan untuk seluruh atau beberapa siswa sekaligus.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('portal-guru.guru-wali.jurnal.create') }}" 
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Beralih ke Jurnal Individu</span>
            </a>
        </div>
    </div>

    <!-- Error Validation Summary -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <div class="flex items-center gap-2 font-bold mb-1">
                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Terdapat kesalahan pengisian data:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- 1. PARAMETER SESI BERSAMA (CARD ATAS) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7 space-y-5">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Informasi Sesi Bimbingan Bersama</h3>
                    <p class="text-xs text-slate-500">Parameter umum yang berlaku untuk seluruh siswa yang mengikuti sesi ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Tanggal & Waktu Sesi -->
                <div>
                    <label for="tanggal_waktu" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Tanggal & Waktu Sesi <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" 
                           id="tanggal_waktu" 
                           wire:model="tanggal_waktu" 
                           class="w-full rounded-2xl border-slate-200 text-sm py-2 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs">
                    @error('tanggal_waktu')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Pendampingan -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Jenis Pendampingan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['Kelompok Kecil', 'Klasikal'] as $jenis)
                            <button type="button" 
                                    wire:click="$set('jenis_pendampingan', '{{ $jenis }}')"
                                    class="border rounded-2xl py-2 px-2.5 text-center text-xs font-bold transition-all {{ $jenis_pendampingan === $jenis ? 'border-brand-primary bg-brand-primary/10 text-brand-primary shadow-xs ring-1 ring-brand-primary' : 'border-slate-200 text-slate-600 hover:bg-slate-50 bg-white' }}">
                                {{ $jenis }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Kategori Pembahasan -->
                <div>
                    <label for="kategori_pendampingan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Kategori Pembahasan <span class="text-rose-500">*</span>
                    </label>
                    <select id="kategori_pendampingan" 
                            wire:model="kategori_pendampingan" 
                            class="w-full rounded-2xl border-slate-200 text-sm py-2 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs bg-white">
                        <option value="Akademik">📚 Akademik (Belajar & Materi)</option>
                        <option value="Karakter & Kedisiplinan">🎯 Karakter & Kedisiplinan</option>
                        <option value="Minat & Bakat / Ekskul">🎨 Minat & Bakat / Ekskul</option>
                        <option value="Sosial & Psikologis">🌱 Sosial & Psikologis</option>
                    </select>
                </div>
            </div>

            <!-- Topik / Uraian Pembahasan Utama -->
            <div>
                <label for="uraian_pembahasan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Topik / Uraian Pembahasan Bersama <span class="text-rose-500">*</span>
                </label>
                <textarea id="uraian_pembahasan" 
                          wire:model="uraian_pembahasan" 
                          rows="3" 
                          placeholder="Tuliskan materi/topik bimbingan kelompok, dinamika kelompok, serta hal-hal penting yang dibahas bersama..."
                          class="w-full rounded-2xl border-slate-200 text-sm py-2.5 px-3.5 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs"></textarea>
                @error('uraian_pembahasan')
                    <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rujukan & RTL Bersama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="rujukan_kolaborasi" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Rujukan Kolaborasi <span class="text-rose-500">*</span>
                    </label>
                    <select id="rujukan_kolaborasi" 
                            wire:model="rujukan_kolaborasi" 
                            class="w-full rounded-2xl border-slate-200 text-sm py-2 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs bg-white">
                        <option value="Mandiri">✅ Mandiri (Ditangani Sendiri Guru Wali)</option>
                        <option value="Wali Kelas">🤝 Wali Kelas (Koordinasi Akademik Kelas)</option>
                        <option value="Guru BK">🧭 Guru BK (Bimbingan Konseling Lanjutan)</option>
                        <option value="Orang Tua / Wali">👨‍👩‍👧 Orang Tua / Wali (Komunikasi ke Rumah)</option>
                    </select>
                </div>

                <div>
                    <label for="rencana_tindak_lanjut" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Rencana Tindak Lanjut Bersama (RTL) <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input type="text" 
                           id="rencana_tindak_lanjut" 
                           wire:model="rencana_tindak_lanjut" 
                           placeholder="Contoh: Evaluasi hasil tugas kelompok pada pertemuan berikutnya"
                           class="w-full rounded-2xl border-slate-200 text-sm py-2 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary shadow-xs">
                </div>
            </div>

            <!-- Toggle Publikasi Catatan -->
            <div class="pt-2 flex items-center gap-3">
                <input type="checkbox" 
                       id="is_public_note" 
                       wire:model="is_public_note" 
                       class="rounded-lg border-slate-300 text-brand-primary focus:ring-brand-primary w-4 h-4">
                <label for="is_public_note" class="text-xs font-medium text-slate-700 cursor-pointer select-none">
                    Tampilkan catatan jurnal sesi ini di <strong>Portal Siswa</strong> peserta yang mengikuti bimbingan
                </label>
            </div>
        </div>

        <!-- 2. TABEL SISWA KELOMPOK (MODEL INPUT MASAL SEPERTI ABSENSI) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <!-- Header Card Tabel & Bulk Controls -->
            <div class="p-5 sm:p-6 bg-slate-50/80 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-brand-primary text-white flex items-center justify-center font-bold text-sm shadow-xs">
                        2
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Daftar Siswa Anggota Kelompok</h3>
                        <p class="text-xs text-slate-500">
                            Pilih siswa yang hadir mengikuti sesi ini, sesuaikan status bimbingan atau tambahkan catatan khusus jika ada.
                        </p>
                    </div>
                </div>

                <!-- Bulk Actions Toolbar -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Status Counter -->
                    <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 shadow-xs">
                        <span class="text-brand-primary">{{ $this->selectedCount }}</span> dari {{ count($studentsData) }} Siswa Terpilih
                    </div>

                    <!-- Quick Set Status Massal -->
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-2.5 py-1 shadow-xs">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Set Massal:</span>
                        <select wire:change="applyBulkStatus($event.target.value)" class="border-0 bg-transparent text-xs font-bold text-brand-primary focus:ring-0 cursor-pointer py-1 pr-7 pl-1">
                            <option value="">-- Pilih Status --</option>
                            <option value="Tuntas / Selesai">Tuntas / Selesai</option>
                            <option value="Dalam Pemantauan">Dalam Pemantauan</option>
                            <option value="Bimbingan Lanjutan">Bimbingan Lanjutan</option>
                        </select>
                    </div>

                    <!-- Toggle Select All -->
                    <button type="button" 
                            wire:click="toggleSelectAll" 
                            class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-xs font-bold text-slate-700 transition-all shadow-xs">
                        {{ $selectedAll ? 'Batal Pilih Semua' : 'Pilih Semua' }}
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            @if(count($studentsData) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 text-xs uppercase font-bold tracking-wider">
                            <tr>
                                <th class="w-12 px-4 py-3.5 text-center">
                                    <input type="checkbox" 
                                           wire:model.live="selectedAll"
                                           title="Pilih / Batal Pilih Semua"
                                           class="rounded border-slate-300 text-brand-primary focus:ring-brand-primary w-4 h-4 cursor-pointer">
                                </th>
                                <th class="px-4 py-3.5 min-w-[220px]">Siswa Binaan</th>
                                <th class="px-4 py-3.5 min-w-[170px]">Status Sesi</th>
                                <th class="px-4 py-3.5 min-w-[240px]">Catatan Khusus Siswa <span class="text-[10px] font-normal lowercase text-slate-400">(opsional)</span></th>
                                <th class="px-4 py-3.5 min-w-[200px]">RTL Khusus Siswa <span class="text-[10px] font-normal lowercase text-slate-400">(opsional)</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($studentsData as $index => $sData)
                                @php
                                    $isSelected = $sData['selected'] ?? false;
                                @endphp
                                <tr class="transition-colors {{ $isSelected ? 'bg-white hover:bg-slate-50/80' : 'bg-slate-50/50 opacity-60' }}">
                                    <!-- Checkbox -->
                                    <td class="px-4 py-3.5 text-center">
                                        <input type="checkbox" 
                                               wire:model.live="studentsData.{{ $index }}.selected"
                                               class="rounded border-slate-300 text-brand-primary focus:ring-brand-primary w-4 h-4 cursor-pointer">
                                    </td>

                                    <!-- Identitas Siswa -->
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl overflow-hidden bg-brand-primary/10 flex items-center justify-center shrink-0 border border-slate-200/80 shadow-xs">
                                                @if(!empty($sData['avatar']))
                                                    <img src="{{ $sData['avatar'] }}" alt="{{ $sData['name'] }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-xs font-bold text-brand-primary">{{ strtoupper(substr($sData['name'], 0, 2)) }}</span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-900 truncate leading-tight">{{ $sData['name'] }}</p>
                                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                                                    <span>NISN: {{ $sData['nisn'] }}</span>
                                                    <span>&bull;</span>
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium">Kelas {{ $sData['kelas'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status Sesi Per Siswa -->
                                    <td class="px-4 py-3.5">
                                        <select wire:model="studentsData.{{ $index }}.status_sesi"
                                                {{ !$isSelected ? 'disabled' : '' }}
                                                class="w-full rounded-xl border-slate-200 text-xs py-1.5 px-2.5 font-semibold focus:border-brand-primary focus:ring-1 focus:ring-brand-primary bg-white shadow-2xs {{ !$isSelected ? 'bg-slate-100 cursor-not-allowed text-slate-400' : 'text-slate-700' }}">
                                            <option value="Tuntas / Selesai">✅ Tuntas / Selesai</option>
                                            <option value="Dalam Pemantauan">⏳ Dalam Pemantauan</option>
                                            <option value="Bimbingan Lanjutan">🔄 Bimbingan Lanjutan</option>
                                        </select>
                                    </td>

                                    <!-- Catatan Khusus Siswa -->
                                    <td class="px-4 py-3.5">
                                        <input type="text" 
                                               wire:model="studentsData.{{ $index }}.catatan_khusus"
                                               {{ !$isSelected ? 'disabled' : '' }}
                                               placeholder="Catatan individu anak (kosong = pakai topik bersama)..."
                                               class="w-full rounded-xl border-slate-200 text-xs py-1.5 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary bg-white shadow-2xs {{ !$isSelected ? 'bg-slate-100 cursor-not-allowed text-slate-400' : 'text-slate-700' }}">
                                    </td>

                                    <!-- RTL Khusus Siswa -->
                                    <td class="px-4 py-3.5">
                                        <input type="text" 
                                               wire:model="studentsData.{{ $index }}.rtl_khusus"
                                               {{ !$isSelected ? 'disabled' : '' }}
                                               placeholder="Tindak lanjut individu anak..."
                                               class="w-full rounded-xl border-slate-200 text-xs py-1.5 px-3 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary bg-white shadow-2xs {{ !$isSelected ? 'bg-slate-100 cursor-not-allowed text-slate-400' : 'text-slate-700' }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-slate-400 text-sm font-medium">
                    Belum ada anggota siswa aktif di kelompok ini.
                </div>
            @endif

            <!-- Table Footer -->
            <div class="p-4 bg-slate-50/50 border-t border-slate-200/80 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Menampilkan <strong>{{ count($studentsData) }}</strong> siswa binaan aktif.
                </span>
                <span class="text-xs font-bold text-brand-primary">
                    {{ $this->selectedCount }} siswa akan dicatatkan jurnalnya
                </span>
            </div>
        </div>

        <!-- 3. TOMBOL AKSI SIMPAN -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('portal-guru.guru-wali.jurnal') }}" 
               class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-100 active:scale-95 text-xs font-bold transition-all">
                Batal
            </a>

            <button type="submit" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-brand-primary hover:bg-brand-secondary text-white text-xs font-bold shadow-md shadow-brand-primary/20 active:scale-95 transition-all disabled:opacity-50">
                <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove>
                    Simpan Jurnal Kelompok ({{ $this->selectedCount }} Siswa)
                </span>
                <span wire:loading>
                    Menyimpan Jurnal Kelompok...
                </span>
            </button>
        </div>
    </form>
</div>
