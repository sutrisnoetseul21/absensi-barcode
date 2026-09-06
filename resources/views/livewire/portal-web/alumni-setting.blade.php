<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Pengaturan Tracer Alumni</h1>
        <p class="text-slate-500 text-sm mt-1">Konfigurasi tampilan banner dan akses pendaftaran tracer study pada halaman publik (/alumni).</p>
    </div>

    <!-- Flash Message -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <form wire:submit="save">
            <div class="p-6 space-y-6">
                {{-- Toggle Status Pendaftaran & Direktori --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Formulir Tracer Alumni</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Jika nonaktif, formulir pendaftaran alumni ditutup sementara.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" wire:model="is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Tampilkan Direktori Alumni</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Jika aktif, daftar kartu alumni akan tampil di halaman publik.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" wire:model="show_table" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Besar Banner <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="banner_title" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-violet-500 focus:ring-violet-500 shadow-sm" placeholder="Contoh: Tracer Study Alumni">
                        @error('banner_title') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Teks Tombol Formulir <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="button_text" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-violet-500 focus:ring-violet-500 shadow-sm" placeholder="Contoh: Daftarkan Data Saya">
                        @error('button_text') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Pengantar Banner</label>
                    <textarea wire:model="banner_text" rows="3" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-violet-500 focus:ring-violet-500 shadow-sm" placeholder="Mari terus menjalin silaturahmi dan berbagi inspirasi kesuksesan..."></textarea>
                    @error('banner_text') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 flex items-center justify-end border-t border-slate-100">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-700 transition-colors gap-2">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
