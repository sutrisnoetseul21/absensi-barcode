<div x-data="{ activeTab: 'beranda' }" class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900">Pengaturan Web Profil</h2>
            <p class="text-sm text-slate-500 mt-0.5">Kelola konten utama dan identitas halaman profil sekolah</p>
        </div>

        {{-- Tab Switcher --}}
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl flex-wrap">
            <button type="button" @click="activeTab = 'beranda'"
                    :class="activeTab === 'beranda' ? 'bg-white text-violet-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3.5 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <i class="fas fa-home"></i> Hero & Beranda
            </button>
            <button type="button" @click="activeTab = 'profil'"
                    :class="activeTab === 'profil' ? 'bg-white text-violet-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3.5 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <i class="fas fa-graduation-cap"></i> Profil, Visi & Misi
            </button>
            <button type="button" @click="activeTab = 'sosmed'"
                    :class="activeTab === 'sosmed' ? 'bg-white text-violet-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3.5 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <i class="fas fa-share-alt"></i> Sosial Media & Tendik
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">
        
        {{-- TAB 1: HERO & BERANDA --}}
        <div x-show="activeTab === 'beranda'" x-transition:enter.opacity class="space-y-5">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i class="fas fa-home text-violet-600"></i> Hero Banner & Kepala Sekolah
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pengaturan tampilan visual utama pada beranda website.</p>
                </div>

                {{-- Hero Banner --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Hero Image (Banner Utama)</label>
                    @if($existingHeroImage)
                        <img src="{{ asset('storage/' . $existingHeroImage) }}" class="h-28 w-auto rounded-xl object-cover mb-2 border border-slate-200 shadow-sm">
                        <p class="text-xs text-slate-400 mb-2">Gambar banner saat ini. Pilih berkas baru di bawah jika ingin mengganti.</p>
                    @endif
                    <input type="file" wire:model="hero_image" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                    <div wire:loading wire:target="hero_image" class="text-xs text-violet-600 mt-1"><i class="fas fa-spinner fa-spin"></i> Mengunggah hero image...</div>
                    @error('hero_image') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Running Text --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Running Text Pengumuman</label>
                    <textarea wire:model="running_text" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Teks pengumuman berjalan di beranda..."></textarea>
                    @error('running_text') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Foto & Kutipan Kepsek --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Foto Kepala Sekolah</label>
                        @if($existingFotoKepsek)
                            <img src="{{ asset('storage/' . $existingFotoKepsek) }}" class="h-24 w-24 rounded-2xl object-cover mb-2 border-2 border-slate-200 shadow-sm">
                        @endif
                        <input type="file" wire:model="foto_kepsek" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-50 file:text-slate-600 hover:file:bg-slate-100">
                        <div wire:loading wire:target="foto_kepsek" class="text-xs text-violet-600 mt-1"><i class="fas fa-spinner fa-spin"></i> Mengunggah foto kepsek...</div>
                        @error('foto_kepsek') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Kutipan Singkat Kepala Sekolah</label>
                        <textarea wire:model="kutipan_kepsek" rows="4" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Kutipan singkat yang tampil di samping foto kepala sekolah..."></textarea>
                        @error('kutipan_kepsek') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Sambutan Kepsek --}}
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Sambutan Lengkap Kepala Sekolah</label>
                    <div wire:ignore wire:key="portal-sambutan-editor-wrapper" x-data="portalWebTinyMCE(@entangle('sambutan_kepsek'), 'portal-sambutan-editor', 350)" class="rounded-xl overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-violet-400 focus-within:border-violet-400 transition-all bg-white shadow-inner">
                        <textarea x-ref="editor" id="portal-sambutan-editor" class="w-full"></textarea>
                    </div>
                    @error('sambutan_kepsek') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Bagan Struktur Organisasi --}}
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Bagan Struktur Organisasi Sekolah</label>
                    <p class="text-xs text-slate-400 mb-2">Unggah gambar bagan struktur organisasi yang akan tampil pada halaman Profil Sekolah.</p>
                    @if($existingStrukturOrganisasi)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $existingStrukturOrganisasi) }}" class="max-h-40 rounded-xl object-contain border border-slate-200 shadow-sm p-1 bg-slate-50">
                            <span class="text-xs text-slate-400 mt-1 inline-block">Gambar struktur organisasi saat ini.</span>
                        </div>
                    @endif
                    <input type="file" wire:model="struktur_organisasi" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                    <div wire:loading wire:target="struktur_organisasi" class="text-xs text-violet-600 mt-1"><i class="fas fa-spinner fa-spin"></i> Mengunggah bagan struktur organisasi...</div>
                    @error('struktur_organisasi') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- TAB 2: PROFIL, VISI & MISI --}}
        <div x-show="activeTab === 'profil'" x-transition:enter.opacity class="space-y-5" style="display: none;">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i class="fas fa-graduation-cap text-violet-600"></i> Profil, Visi & Misi Sekolah
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Uraian ringkas profil, arah tujuan, dan misi sekolah.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Profil Singkat & Sejarah Sekolah</label>
                    <textarea wire:model="profil_singkat" rows="5" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none leading-relaxed" placeholder="Tuliskan profil singkat atau sejarah sekolah..."></textarea>
                    @error('profil_singkat') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Visi Sekolah</label>
                    <textarea wire:model="visi" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none leading-relaxed" placeholder="Visi sekolah..."></textarea>
                    @error('visi') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Misi Sekolah</label>
                    <textarea wire:model="misi" rows="6" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none leading-relaxed" placeholder="Tuliskan misi sekolah (satu baris per poin misi)..."></textarea>
                    @error('misi') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- TAB 3: SOSIAL MEDIA & STATISTIK --}}
        <div x-show="activeTab === 'sosmed'" x-transition:enter.opacity class="space-y-5" style="display: none;">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i class="fas fa-share-alt text-violet-600"></i> Media Sosial & Statistik Pendukung
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tautan sosial media resmi sekolah dan angka tenaga kependidikan.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link YouTube</label>
                        <input type="url" wire:model="link_youtube" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://youtube.com/@sekolah">
                        @error('link_youtube') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link TikTok</label>
                        <input type="url" wire:model="link_tiktok" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://tiktok.com/@sekolah">
                        @error('link_tiktok') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link Instagram</label>
                        <input type="url" wire:model="link_ig" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://instagram.com/sekolah">
                        @error('link_ig') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Link Facebook</label>
                        <input type="url" wire:model="link_fb" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none" placeholder="https://facebook.com/sekolah">
                        @error('link_fb') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Jumlah Tenaga Kependidikan (Tendik)</label>
                    <input type="number" wire:model="stat_tenaga_kependidikan" min="0" class="w-36 px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none">
                    <p class="text-xs text-slate-400 mt-1">Jumlah staf / tata usaha yang ditampilkan di widget statistik beranda.</p>
                    @error('stat_tenaga_kependidikan') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Save Button Bar --}}
        <div class="flex justify-end pt-2">
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