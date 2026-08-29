<div class="space-y-6 max-w-5xl mx-auto">
    {{-- Header --}}
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Notifikasi WA</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola pengaturan otomatis pengiriman pesan WhatsApp untuk presensi siswa dan laporan wali kelas.</p>
    </div>

    {{-- Scheduler Status Banner --}}
    @if($schedulerActive)
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 flex items-start gap-4">
        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center border border-emerald-200">
            <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="flex-1">
            <p class="font-black text-emerald-900 text-lg">Scheduler Aktif</p>
            <p class="text-sm text-emerald-700 mt-0.5">
                Terakhir berjalan: <strong class="font-bold">{{ $schedulerLastRun }}</strong> ({{ $schedulerAgeLabel }})
            </p>
            <p class="text-sm text-emerald-600 mt-1.5 flex items-center gap-1.5 font-medium">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Laporan otomatis akan dikirim sesuai jadwal.
            </p>
        </div>
        <button wire:click="testSchedulerRun" class="flex-shrink-0 px-3 py-1.5 bg-white border border-emerald-200 text-emerald-700 rounded-lg text-xs font-bold hover:bg-emerald-50 transition-colors shadow-sm">
            Refresh Status
        </button>
    </div>
    @else
    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 space-y-4">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center border border-rose-200">
                <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <div class="flex-1">
                <p class="font-black text-rose-900 text-lg">Scheduler Tidak Terdeteksi</p>
                <p class="text-sm text-rose-700 mt-0.5">
                    @if($schedulerLastRun)
                        Terakhir terdeteksi: <strong>{{ $schedulerLastRun }}</strong> ({{ $schedulerAgeLabel }})
                    @else
                        Belum pernah terdeteksi sejak instalasi.
                    @endif
                </p>
                <p class="text-sm text-rose-800 mt-1.5 font-bold">⚠️ Laporan otomatis TIDAK akan berjalan sampai scheduler diaktifkan.</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-rose-100 p-4 space-y-2">
            <p class="text-xs font-black text-slate-800 uppercase tracking-wider">📋 Panduan Mengaktifkan Scheduler (HestiaCP)</p>
            <ol class="text-sm text-slate-600 space-y-1.5 list-decimal list-inside ml-2">
                <li>Login ke <strong>HestiaCP</strong> → menu <strong>Cron Jobs</strong></li>
                <li>Klik tombol <strong>Add Cron Job</strong></li>
                <li>Set semua kolom waktu ke <strong>*</strong> (setiap menit)</li>
                <li>Isi kolom <strong>Command</strong> dengan perintah di bawah ini:</li>
            </ol>
            <div class="bg-slate-900 text-emerald-400 text-xs font-mono rounded-lg p-3 break-all select-all shadow-inner mt-2">
                /usr/bin/php8.4 {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1
            </div>
        </div>
        <div>
            <button wire:click="testSchedulerRun" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition-colors shadow-md disabled:opacity-50">
                <span wire:loading.remove wire:target="testSchedulerRun" class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Test Jalankan Sekarang
                </span>
                <span wire:loading wire:target="testSchedulerRun" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Menjalankan...
                </span>
            </button>
        </div>
    </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button wire:click="setActiveTab('siswa')" class="{{ $activeTab === 'siswa' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Notifikasi Siswa
            </button>
            <button wire:click="setActiveTab('harian')" class="{{ $activeTab === 'harian' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>
                Laporan Harian Kelas
            </button>
            <button wire:click="setActiveTab('sekolah')" class="{{ $activeTab === 'sekolah' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                Rekap Seluruh Sekolah
            </button>
        </nav>
    </div>

    {{-- Form Content --}}
    <form wire:submit.prevent="save" class="space-y-6">
        
        {{-- TAB 1: SISWA --}}
        <div class="{{ $activeTab === 'siswa' ? 'block' : 'hidden' }} space-y-6">
            {{-- Terlambat --}}
            <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Aturan Notifikasi: Terlambat</h3>
                    <p class="text-sm text-slate-500 mt-1">Sistem akan otomatis mendeteksi ketika siswa berstatus Telat dan mengirimkan pesan WA sesuai template di bawah ini.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="telat_notif_is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                            <span class="ml-3 text-sm font-bold text-slate-700">Aktifkan Notifikasi Terlambat</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kirim Ke (Penerima)</label>
                        <div x-data="{
                                options: {{ json_encode($recipientOptions) }},
                                selected: @entangle('telat_notif_recipients'),
                                open: false,
                                toggle(key) {
                                    if (this.selected.includes(key)) {
                                        this.selected = this.selected.filter(i => i !== key);
                                    } else {
                                        this.selected.push(key);
                                    }
                                }
                            }" class="relative">
                            <div @click="open = !open" class="min-h-[42px] w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-3 py-2 cursor-pointer flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-brand-primary focus-within:border-brand-primary transition-colors">
                                <template x-if="selected.length === 0">
                                    <span class="text-slate-500">Pilih satu atau beberapa penerima...</span>
                                </template>
                                <template x-for="item in selected" :key="item">
                                    <span class="inline-flex items-center gap-1 bg-brand-primary/10 text-brand-primary px-2.5 py-1 rounded-md text-xs font-bold">
                                        <span x-text="options[item] || item"></span>
                                        <svg @click.stop="toggle(item)" class="w-3 h-3 cursor-pointer hover:text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </span>
                                </template>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto p-1">
                                <template x-for="[key, label] in Object.entries(options)" :key="key">
                                    <div @click="toggle(key)" class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-colors" :class="selected.includes(key) ? 'text-brand-primary font-bold bg-brand-primary/5' : 'text-slate-700'">
                                        <span x-text="label"></span>
                                        <svg x-show="selected.includes(key)" class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Template Pesan Terlambat</label>
                        <textarea wire:model="telat_notif_template_pesan" rows="5" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-3 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors"></textarea>
                        <p class="text-xs text-slate-500 mt-2 font-medium">Placeholder: {nama_siswa}, {kelas}, {tanggal}, {jam}, {nama_wali_kelas}, {nama_sekolah}</p>
                    </div>
                </div>
            </div>

            {{-- Sakit/Izin/Alpa --}}
            <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Aturan Notifikasi: Sakit / Izin / Alpa</h3>
                    <p class="text-sm text-slate-500 mt-1">Sistem akan otomatis mendeteksi ketika siswa berstatus Sakit, Izin, atau Alpa dan mengirimkan pesan WA sesuai template di bawah ini.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="student_notif_is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                            <span class="ml-3 text-sm font-bold text-slate-700">Aktifkan Notifikasi (Sakit / Izin / Alpa)</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kirim Ke (Penerima)</label>
                        <div x-data="{
                                options: {{ json_encode($recipientOptions) }},
                                selected: @entangle('student_notif_recipients'),
                                open: false,
                                toggle(key) {
                                    if (this.selected.includes(key)) {
                                        this.selected = this.selected.filter(i => i !== key);
                                    } else {
                                        this.selected.push(key);
                                    }
                                }
                            }" class="relative">
                            <div @click="open = !open" class="min-h-[42px] w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-3 py-2 cursor-pointer flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-brand-primary focus-within:border-brand-primary transition-colors">
                                <template x-if="selected.length === 0">
                                    <span class="text-slate-500">Pilih satu atau beberapa penerima...</span>
                                </template>
                                <template x-for="item in selected" :key="item">
                                    <span class="inline-flex items-center gap-1 bg-brand-primary/10 text-brand-primary px-2.5 py-1 rounded-md text-xs font-bold">
                                        <span x-text="options[item] || item"></span>
                                        <svg @click.stop="toggle(item)" class="w-3 h-3 cursor-pointer hover:text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </span>
                                </template>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto p-1">
                                <template x-for="[key, label] in Object.entries(options)" :key="key">
                                    <div @click="toggle(key)" class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-colors" :class="selected.includes(key) ? 'text-brand-primary font-bold bg-brand-primary/5' : 'text-slate-700'">
                                        <span x-text="label"></span>
                                        <svg x-show="selected.includes(key)" class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Template Pesan Sakit/Izin/Alpa</label>
                        <textarea wire:model="student_notif_template_pesan" rows="5" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-3 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors"></textarea>
                        <p class="text-xs text-slate-500 mt-2 font-medium">Placeholder {status_kehadiran} akan terisi dinamis (Sakit/Izin/Alpa). Placeholder lain: {nama_siswa}, {kelas}, {tanggal}, {jam}, {nama_wali_kelas}, {nama_sekolah}</p>
                    </div>
                </div>
            {{-- Pengajuan Ijin --}}
            <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Aturan Notifikasi: Pengajuan Ijin (Oleh Siswa)</h3>
                    <p class="text-sm text-slate-500 mt-1">Sistem akan mengirim pesan WA saat siswa membuat pengajuan Ijin/Sakit melalui Portal Siswa.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="leave_request_notif_is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                            <span class="ml-3 text-sm font-bold text-slate-700">Aktifkan Notifikasi Pengajuan Ijin</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kirim Ke (Penerima)</label>
                        <div x-data="{
                                options: {{ json_encode($recipientOptions) }},
                                selected: @entangle('leave_request_notif_recipients'),
                                open: false,
                                toggle(key) {
                                    if (this.selected.includes(key)) {
                                        this.selected = this.selected.filter(i => i !== key);
                                    } else {
                                        this.selected.push(key);
                                    }
                                }
                            }" class="relative">
                            <div @click="open = !open" class="min-h-[42px] w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-3 py-2 cursor-pointer flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-brand-primary focus-within:border-brand-primary transition-colors">
                                <template x-if="selected.length === 0">
                                    <span class="text-slate-500">Pilih satu atau beberapa penerima...</span>
                                </template>
                                <template x-for="item in selected" :key="item">
                                    <span class="inline-flex items-center gap-1 bg-brand-primary/10 text-brand-primary px-2.5 py-1 rounded-md text-xs font-bold">
                                        <span x-text="options[item] || item"></span>
                                        <svg @click.stop="toggle(item)" class="w-3 h-3 cursor-pointer hover:text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </span>
                                </template>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto p-1">
                                <template x-for="[key, label] in Object.entries(options)" :key="key">
                                    <div @click="toggle(key)" class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-colors" :class="selected.includes(key) ? 'text-brand-primary font-bold bg-brand-primary/5' : 'text-slate-700'">
                                        <span x-text="label"></span>
                                        <svg x-show="selected.includes(key)" class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Template Pesan Pengajuan Ijin</label>
                        <textarea wire:model="leave_request_notif_template_pesan" rows="5" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-3 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors"></textarea>
                        <p class="text-xs text-slate-500 mt-2 font-medium">Placeholder: {nama_siswa}, {kelas}, {jenis_ijin}, {tanggal_mulai}, {tanggal_selesai}, {alasan}, {nama_wali_kelas}, {link_detail}</p>
                    </div>
                </div>
            </div>

            {{-- Persetujuan Ijin --}}
            <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Aturan Notifikasi: Persetujuan Ijin (Oleh Guru)</h3>
                    <p class="text-sm text-slate-500 mt-1">Sistem akan mengirim pesan WA saat guru (Wali Kelas) memberikan persetujuan atau menolak pengajuan ijin.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="leave_approval_notif_is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                            <span class="ml-3 text-sm font-bold text-slate-700">Aktifkan Notifikasi Persetujuan Ijin</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kirim Ke (Penerima)</label>
                        <div x-data="{
                                options: {{ json_encode($recipientOptions) }},
                                selected: @entangle('leave_approval_notif_recipients'),
                                open: false,
                                toggle(key) {
                                    if (this.selected.includes(key)) {
                                        this.selected = this.selected.filter(i => i !== key);
                                    } else {
                                        this.selected.push(key);
                                    }
                                }
                            }" class="relative">
                            <div @click="open = !open" class="min-h-[42px] w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-3 py-2 cursor-pointer flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-brand-primary focus-within:border-brand-primary transition-colors">
                                <template x-if="selected.length === 0">
                                    <span class="text-slate-500">Pilih satu atau beberapa penerima...</span>
                                </template>
                                <template x-for="item in selected" :key="item">
                                    <span class="inline-flex items-center gap-1 bg-brand-primary/10 text-brand-primary px-2.5 py-1 rounded-md text-xs font-bold">
                                        <span x-text="options[item] || item"></span>
                                        <svg @click.stop="toggle(item)" class="w-3 h-3 cursor-pointer hover:text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </span>
                                </template>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto p-1">
                                <template x-for="[key, label] in Object.entries(options)" :key="key">
                                    <div @click="toggle(key)" class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-colors" :class="selected.includes(key) ? 'text-brand-primary font-bold bg-brand-primary/5' : 'text-slate-700'">
                                        <span x-text="label"></span>
                                        <svg x-show="selected.includes(key)" class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Template Pesan Persetujuan Ijin</label>
                        <textarea wire:model="leave_approval_notif_template_pesan" rows="5" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-3 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors"></textarea>
                        <p class="text-xs text-slate-500 mt-2 font-medium">Placeholder: {nama_siswa}, {jenis_ijin}, {tanggal_mulai}, {tanggal_selesai}, {status_persetujuan}, {nama_guru}, {alasan_penolakan}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- TAB 2: HARIAN KELAS --}}
        <div class="{{ $activeTab === 'harian' ? 'block' : 'hidden' }} space-y-6">
            <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Pengaturan Laporan Harian Wali Kelas</h3>
                    <p class="text-sm text-slate-500 mt-1">Laporan harian dikirim otomatis oleh sistem sesuai jam cutoff. Toleransi pengiriman: 1 jam setelah jam cutoff.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="daily_is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                            <span class="ml-3 text-sm font-bold text-slate-700">Aktifkan Laporan Harian</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jam Pengiriman (Cut-off Time)</label>
                        <input type="time" wire:model="daily_cutoff_time" class="w-48 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-2 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kirim Ke (Penerima)</label>
                        <div x-data="{
                                options: {{ json_encode(array_merge(['wali_kelas' => 'Wali Kelas'], $schoolRecipientOptions)) }},
                                selected: @entangle('daily_recipients'),
                                open: false,
                                toggle(key) {
                                    if (this.selected.includes(key)) {
                                        this.selected = this.selected.filter(i => i !== key);
                                    } else {
                                        this.selected.push(key);
                                    }
                                }
                            }" class="relative">
                            <div @click="open = !open" class="min-h-[42px] w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-3 py-2 cursor-pointer flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-brand-primary focus-within:border-brand-primary transition-colors">
                                <template x-if="selected.length === 0">
                                    <span class="text-slate-500">Pilih satu atau beberapa penerima...</span>
                                </template>
                                <template x-for="item in selected" :key="item">
                                    <span class="inline-flex items-center gap-1 bg-brand-primary/10 text-brand-primary px-2.5 py-1 rounded-md text-xs font-bold">
                                        <span x-text="options[item] || item"></span>
                                        <svg @click.stop="toggle(item)" class="w-3 h-3 cursor-pointer hover:text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </span>
                                </template>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto p-1">
                                <template x-for="[key, label] in Object.entries(options)" :key="key">
                                    <div @click="toggle(key)" class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-colors" :class="selected.includes(key) ? 'text-brand-primary font-bold bg-brand-primary/5' : 'text-slate-700'">
                                        <span x-text="label"></span>
                                        <svg x-show="selected.includes(key)" class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Template Pesan</label>
                        <textarea wire:model="daily_template_pesan" rows="6" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-3 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors"></textarea>
                        <p class="text-xs text-slate-500 mt-2 font-medium">Placeholder: {nama_kelas}, {tanggal}, {total_siswa}, {jumlah_hadir}, {jumlah_terlambat}, {jumlah_alpa}, {jumlah_sakit}, {jumlah_izin}, {daftar_belum_presensi}</p>
                    </div>
                </div>
            </div>

            {{-- Kirim Manual --}}
            <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="p-2 bg-brand-primary/10 rounded-lg">
                        <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Kirim Manual Laporan Harian</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Kirim laporan harian hari ini secara manual. Maksimal 1x per hari.</p>
                    </div>
                </div>
                <div class="p-6">
                    @if($canSendDailyManual)
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Tanggal hari ini: <strong class="font-bold text-slate-900">{{ date('d M Y') }}</strong></span>
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700">Belum dikirim</span>
                        </div>
                        <button type="button" wire:click="confirmSendDailyManual" wire:confirm="Yakin ingin kirim laporan harian presensi ke semua wali kelas sekarang?" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-primary hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-brand-primary/20">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                            Kirim Laporan Harian Sekarang
                        </button>
                    </div>
                    @else
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider bg-amber-100 text-amber-800">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Sudah dikirim manual hari ini
                        </span>
                        <p class="text-sm text-slate-500 font-medium">Pengiriman manual sudah dilakukan hari ini. Tersedia kembali besok.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- TAB 3: REKAP SEKOLAH --}}
        <div class="{{ $activeTab === 'sekolah' ? 'block' : 'hidden' }} space-y-6">
            <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Pengaturan Rekap Seluruh Sekolah</h3>
                    <p class="text-sm text-slate-500 mt-1">Rekap presensi seluruh kelas sekaligus (Helicopter View) untuk Manajemen Sekolah.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="school_is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                            <span class="ml-3 text-sm font-bold text-slate-700">Aktifkan Laporan Rekap Sekolah</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jam Pengiriman (Cut-off Time)</label>
                        <input type="time" wire:model="school_cutoff_time" class="w-48 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-2 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kirim Ke (Penerima)</label>
                        <div x-data="{
                                options: {{ json_encode($schoolRecipientOptions) }},
                                selected: @entangle('school_recipients'),
                                open: false,
                                toggle(key) {
                                    if (this.selected.includes(key)) {
                                        this.selected = this.selected.filter(i => i !== key);
                                    } else {
                                        this.selected.push(key);
                                    }
                                }
                            }" class="relative">
                            <div @click="open = !open" class="min-h-[42px] w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-3 py-2 cursor-pointer flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-brand-primary focus-within:border-brand-primary transition-colors">
                                <template x-if="selected.length === 0">
                                    <span class="text-slate-500">Pilih satu atau beberapa penerima...</span>
                                </template>
                                <template x-for="item in selected" :key="item">
                                    <span class="inline-flex items-center gap-1 bg-brand-primary/10 text-brand-primary px-2.5 py-1 rounded-md text-xs font-bold">
                                        <span x-text="options[item] || item"></span>
                                        <svg @click.stop="toggle(item)" class="w-3 h-3 cursor-pointer hover:text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </span>
                                </template>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto p-1">
                                <template x-for="[key, label] in Object.entries(options)" :key="key">
                                    <div @click="toggle(key)" class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-colors" :class="selected.includes(key) ? 'text-brand-primary font-bold bg-brand-primary/5' : 'text-slate-700'">
                                        <span x-text="label"></span>
                                        <svg x-show="selected.includes(key)" class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Template Header</label>
                        <textarea wire:model="school_template_header" rows="3" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-3 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors"></textarea>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Placeholder: {nama_sekolah}, {hari}, {tanggal}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Template Baris per Kelas</label>
                        <textarea wire:model="school_template_row" rows="3" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-3 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors"></textarea>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Diulang untuk setiap kelas. Placeholder: {nama_kelas}, {jumlah_hadir}, {jumlah_terlambat}, {nama_terlambat}, {jumlah_sakit}, {nama_sakit}, {jumlah_izin}, {nama_izin}, {jumlah_alpa}, {nama_alpa}, {jumlah_belum_presensi}, {nama_belum_presensi}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Template Footer</label>
                        <textarea wire:model="school_template_footer" rows="3" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-4 py-3 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none transition-colors"></textarea>
                    </div>
                </div>
            </div>

            {{-- Kirim Manual --}}
            <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="p-2 bg-brand-primary/10 rounded-lg">
                        <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Kirim Manual Rekap Sekolah</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Kirim rekap seluruh sekolah hari ini secara manual. Maksimal 1x per hari.</p>
                    </div>
                </div>
                <div class="p-6">
                    @if($canSendSchoolManual)
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Tanggal hari ini: <strong class="font-bold text-slate-900">{{ date('d M Y') }}</strong></span>
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700">Belum dikirim</span>
                        </div>
                        <button type="button" wire:click="confirmSendSchoolManual" wire:confirm="Yakin ingin kirim rekap presensi seluruh sekolah sekarang?" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-primary hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-brand-primary/20">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                            Kirim Rekap Sekolah Sekarang
                        </button>
                    </div>
                    @else
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider bg-amber-100 text-amber-800">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Sudah dikirim manual hari ini
                        </span>
                        <p class="text-sm text-slate-500 font-medium">Pengiriman manual sudah dilakukan hari ini. Tersedia kembali besok.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sticky Footer Save Button --}}
        <div class="sticky bottom-6 z-30 pt-6">
            <div class="bg-white/80 backdrop-blur-md shadow-lg border border-slate-200 rounded-2xl p-4 flex items-center justify-between">
                <p class="text-xs text-slate-500 font-medium px-2">Pastikan pengaturan sudah benar sebelum menyimpan.</p>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold transition-colors shadow-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
