<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Pengaturan Layanan Publik</h1>
        <p class="text-slate-500 text-sm mt-1">Konfigurasi formulir pengaduan serta dokumen resmi dan kanal pengawasan publik.</p>
    </div>

    <!-- Flash Message -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- SECTION 1: FORMULIR ASPIRASI & PENGADUAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <i class="fas fa-comment-dots text-brand-primary"></i> Formulir Aspirasi & Pengaduan
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Pengaturan tampilan banner dan modul pada halaman /pengaduan.</p>
            </div>

            <div class="p-6 space-y-6">
                <!-- Toggle Aktif/Nonaktif -->
                <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Status Layanan Pengaduan</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Aktifkan untuk menerima laporan dari masyarakat. Jika dinonaktifkan, form pengaduan akan ditutup sementara.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Modul / Label <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="module_name" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Misal: Pengaduan, Kotak Saran, Aspirasi">
                        @error('module_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Besar Banner <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="banner_title" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Misal: Layanan Aspirasi & Pengaduan">
                        @error('banner_title') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Teks Pengantar Banner</label>
                    <textarea wire:model="banner_text" rows="3" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="Sampaikan laporan Anda dengan mudah, cepat, dan aman..."></textarea>
                    @error('banner_text') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- SECTION 2: DOKUMEN RESMI & KANAL TERINTEGRASI LAYANAN PUBLIK --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <i class="fas fa-file-contract text-brand-primary"></i> Dokumen Resmi & Kanal Terintegrasi Layanan Publik
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Unggah bagan resmi dan tautan pengawasan yang tampil pada portal publik (/layanan-publik).</p>
            </div>

            <div class="p-6 space-y-6">
                {{-- Gambar Dokumen Visi Misi & Maklumat Pelayanan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Gambar Visi & Misi Pelayanan</label>
                        @if($existingGambarVisiMisi)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $existingGambarVisiMisi) }}" class="h-32 w-auto rounded-xl object-contain border border-slate-200 shadow-sm p-1 bg-slate-50">
                                <span class="text-xs text-slate-400 mt-0.5 block">Gambar saat ini. Unggah baru untuk mengganti.</span>
                            </div>
                        @endif
                        <input type="file" wire:model="gambar_visi_misi_pelayanan" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <div wire:loading wire:target="gambar_visi_misi_pelayanan" class="text-xs text-brand-primary mt-1"><i class="fas fa-spinner fa-spin"></i> Mengunggah gambar...</div>
                        @error('gambar_visi_misi_pelayanan') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Gambar Maklumat Pelayanan</label>
                        @if($existingGambarMaklumat)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $existingGambarMaklumat) }}" class="h-32 w-auto rounded-xl object-contain border border-slate-200 shadow-sm p-1 bg-slate-50">
                                <span class="text-xs text-slate-400 mt-0.5 block">Gambar saat ini. Unggah baru untuk mengganti.</span>
                            </div>
                        @endif
                        <input type="file" wire:model="gambar_maklumat_pelayanan" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <div wire:loading wire:target="gambar_maklumat_pelayanan" class="text-xs text-brand-primary mt-1"><i class="fas fa-spinner fa-spin"></i> Mengunggah gambar...</div>
                        @error('gambar_maklumat_pelayanan') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Tautan Kanal Lapor & Pengawasan --}}
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tautan Kanal Pengawasan & Survei</h4>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Link SP4N-LAPOR! (KemenPAN-RB)</label>
                        <input type="url" wire:model="link_sp4n_lapor" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="https://www.lapor.go.id/...">
                        @error('link_sp4n_lapor') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Link Katalog SIPPN / Cariyanlik Pelayanan</label>
                        <input type="url" wire:model="link_cariyanlik" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="https://sippn.menpan.go.id/...">
                        @error('link_cariyanlik') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Link Halo Pengaduan Daerah (Pemda / Dinas)</label>
                        <input type="url" wire:model="link_pengaduan_daerah" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="https://pengaduan.daerah.go.id/...">
                        @error('link_pengaduan_daerah') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Link Survei Kepuasan Masyarakat (IKM)</label>
                        <input type="url" wire:model="link_survei_kepuasan" class="block w-full rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm" placeholder="https://forms.gle/... atau link survei lainnya">
                        @error('link_survei_kepuasan') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 flex items-center justify-end border-t border-slate-100">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-secondary transition-colors gap-2">
                    <i class="fas fa-save"></i> Simpan Pengaturan Layanan
                </button>
            </div>
        </div>
    </form>
</div>
