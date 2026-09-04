<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h2 class="text-xl font-extrabold text-slate-900">Pengaturan Web Profil</h2>
        <p class="text-sm text-slate-500 mt-0.5">Kelola konten utama halaman web sekolah</p>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">
        {{-- Hero & Sambutan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-extrabold text-slate-900 mb-4 text-base">Hero & Sambutan</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Hero Image (Banner)</label>
                    @if($existingHeroImage)
                        <img src="{{ asset('storage/' . $existingHeroImage) }}" class="h-24 w-auto rounded-xl object-cover mb-2 border border-slate-200">
                        <p class="text-xs text-slate-400 mb-2">Gambar saat ini. Upload baru untuk mengganti.</p>
                    @endif
                    <input type="file" wire:model="hero_image" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Running Text</label>
                    <textarea wire:model="running_text" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Teks berjalan di beranda..."></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Foto Kepala Sekolah</label>
                        @if($existingFotoKepsek)
                            <img src="{{ asset('storage/' . $existingFotoKepsek) }}" class="h-20 w-20 rounded-full object-cover mb-2 border-2 border-slate-200">
                        @endif
                        <input type="file" wire:model="foto_kepsek" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-50 file:text-slate-600 hover:file:bg-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Kutipan Kepala Sekolah</label>
                        <textarea wire:model="kutipan_kepsek" rows="4" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Kutipan singkat kepala sekolah..."></textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Profil Singkat Sekolah</label>
                    <textarea wire:model="profil_singkat" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Profil singkat sekolah..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Visi Sekolah</label>
                    <textarea wire:model="visi" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Visi sekolah..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Misi Sekolah</label>
                    <textarea wire:model="misi" rows="5" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Misi sekolah (satu per baris)..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Sambutan Kepala Sekolah</label>
                    <textarea wire:model="sambutan_kepsek" rows="6" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Teks sambutan lengkap..."></textarea>
                </div>
            </div>
        </div>

        {{-- Sosial Media --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-extrabold text-slate-900 mb-4 text-base">Sosial Media & Kontak</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link YouTube</label>
                    <input type="url" wire:model="link_youtube" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://youtube.com/...">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link TikTok</label>
                    <input type="url" wire:model="link_tiktok" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://tiktok.com/...">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link Instagram</label>
                    <input type="url" wire:model="link_ig" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://instagram.com/...">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link Facebook</label>
                    <input type="url" wire:model="link_fb" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://facebook.com/...">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link Layanan Pengaduan</label>
                    <input type="url" wire:model="link_pengaduan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://...">
                </div>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-extrabold text-slate-900 mb-4 text-base">Statistik</h3>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Jumlah Tenaga Kependidikan (Tendik)</label>
                <input type="number" wire:model="stat_tenaga_kependidikan" min="0" class="w-32 px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl text-sm transition-colors shadow-sm flex items-center gap-2" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Pengaturan
                </span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>