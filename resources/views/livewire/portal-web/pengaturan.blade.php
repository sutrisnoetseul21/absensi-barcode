<div x-data="{ activeTab: 'beranda' }" class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900">Pengaturan Web Profil</h2>
            <p class="text-sm text-slate-500 mt-0.5">Kelola konten utama dan identitas halaman profil sekolah</p>
        </div>

        {{-- Tab Switcher --}}
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl flex-wrap">
            <button type="button" @click="activeTab = 'beranda'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                    :class="activeTab === 'beranda' ? 'bg-white text-violet-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3.5 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <i class="fas fa-home"></i> Hero & Beranda
            </button>
            <button type="button" @click="activeTab = 'profil'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                    :class="activeTab === 'profil' ? 'bg-white text-violet-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3.5 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <i class="fas fa-graduation-cap"></i> Profil, Visi & Misi
            </button>
            <button type="button" @click="activeTab = 'sosmed'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
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
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-4 border-t border-slate-100">
                    <div class="lg:col-span-7" x-data="{
                        posisi: @entangle('posisi_foto_kepsek').live,
                        get cssPosisi() {
                            if (this.posisi === 'top') return '0%';
                            if (this.posisi === 'center') return '50%';
                            if (this.posisi === 'bottom') return '100%';
                            return this.posisi || '0%';
                        },
                        get sliderVal() {
                            if (this.posisi === 'top') return 0;
                            if (this.posisi === 'center') return 50;
                            if (this.posisi === 'bottom') return 100;
                            let val = parseInt(this.posisi);
                            return isNaN(val) ? 0 : val;
                        },
                        setPreset(val) {
                            this.posisi = val;
                        },
                        setSlider(e) {
                            this.posisi = e.target.value + '%';
                        }
                    }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Foto Kepala Sekolah
                            </label>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                <i class="fas fa-portrait"></i> Format Potret 3:4 (Pasfoto Resmi)
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">
                            Proporsional untuk foto rasio 3x4 atau 2x3. Sesuaikan posisi fokus vertikal agar jilbab & kepala tampil utuh tanpa terpotong.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-5 items-start bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
                            <!-- Live Interactive 3:4 Preview Card -->
                            <div class="w-40 sm:w-44 aspect-[3/4] rounded-2xl overflow-hidden shadow-md border-2 border-slate-300 relative bg-slate-200 shrink-0 select-none">
                                @if($foto_kepsek)
                                    <img src="{{ $foto_kepsek->temporaryUrl() }}" alt="Preview Baru" class="w-full h-full object-cover transition-all duration-150" :style="'object-position: center ' + cssPosisi">
                                    <span class="absolute top-2 left-2 bg-violet-600/90 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded shadow">
                                        Foto Baru
                                    </span>
                                @elseif($existingFotoKepsek)
                                    <img src="{{ asset('storage/' . $existingFotoKepsek) }}" alt="Preview Saat Ini" class="w-full h-full object-cover transition-all duration-150" :style="'object-position: center ' + cssPosisi">
                                    <span class="absolute top-2 left-2 bg-slate-900/70 backdrop-blur-sm text-white text-[10px] font-semibold px-2 py-0.5 rounded shadow">
                                        3:4 Potret
                                    </span>
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 p-4 text-center">
                                        <i class="fas fa-user-tie text-4xl mb-2 text-slate-300"></i>
                                        <span class="text-xs font-medium">Belum ada foto</span>
                                    </div>
                                @endif
                                <span class="absolute bottom-2 inset-x-2 bg-slate-900/80 backdrop-blur-sm text-white text-[10px] text-center py-0.5 rounded font-mono shadow" x-text="'Fokus: ' + cssPosisi"></span>
                            </div>

                            <!-- Controls & Sliders -->
                            <div class="flex-1 w-full space-y-3.5">
                                <div>
                                    <span class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                        Pilihan Fokus Cepat (Preset)
                                    </span>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <button type="button" @click="setPreset('top')"
                                            class="px-2.5 py-1.5 text-xs rounded-lg border transition-all text-center flex flex-col items-center justify-center gap-0.5"
                                            :class="cssPosisi === '0%' ? 'bg-violet-600 text-white border-violet-600 font-bold shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'">
                                            <i class="fas fa-arrow-up text-[10px]"></i>
                                            <span>Atas (Kepala)</span>
                                        </button>
                                        <button type="button" @click="setPreset('center')"
                                            class="px-2.5 py-1.5 text-xs rounded-lg border transition-all text-center flex flex-col items-center justify-center gap-0.5"
                                            :class="cssPosisi === '50%' ? 'bg-violet-600 text-white border-violet-600 font-bold shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'">
                                            <i class="fas fa-arrows-alt-v text-[10px]"></i>
                                            <span>Tengah</span>
                                        </button>
                                        <button type="button" @click="setPreset('bottom')"
                                            class="px-2.5 py-1.5 text-xs rounded-lg border transition-all text-center flex flex-col items-center justify-center gap-0.5"
                                            :class="cssPosisi === '100%' ? 'bg-violet-600 text-white border-violet-600 font-bold shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'">
                                            <i class="fas fa-arrow-down text-[10px]"></i>
                                            <span>Bawah</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Slider fine-tuning -->
                                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-slate-700">Geser Presisi Vertikal:</span>
                                        <span class="font-mono font-bold text-violet-700 bg-violet-50 px-1.5 py-0.5 rounded border border-violet-200" x-text="cssPosisi"></span>
                                    </div>
                                    <input type="range" min="0" max="100" step="1" :value="sliderVal" @input="setSlider"
                                        class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-violet-600">
                                    <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                                        <span>0% (Kepala / Jilbab)</span>
                                        <span>50% (Tengah)</span>
                                        <span>100% (Bawah)</span>
                                    </div>
                                </div>

                                <!-- Upload File Input -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Ganti Foto Baru</label>
                                    <input type="file" wire:model="foto_kepsek" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-violet-100 file:text-violet-700 hover:file:bg-violet-200">
                                    <div wire:loading wire:target="foto_kepsek" class="text-xs text-violet-600 mt-1"><i class="fas fa-spinner fa-spin"></i> Mengunggah foto kepsek...</div>
                                    @error('foto_kepsek') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Kutipan Singkat Kepala Sekolah</label>
                            <textarea wire:model="kutipan_kepsek" rows="5" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none resize-none" placeholder="Kutipan singkat yang tampil di samping foto kepala sekolah..."></textarea>
                            @error('kutipan_kepsek') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-4 p-3.5 rounded-xl bg-violet-50/60 border border-violet-100 text-xs text-slate-600 flex items-start gap-2.5">
                            <i class="fas fa-info-circle text-violet-500 text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <span class="font-bold text-slate-800">Tampilan di Beranda:</span>
                                Foto akan tampil dengan rasio potret 3:4 dengan kutipan singkat melayang di pojok foto pada website sekolah.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sambutan Kepsek --}}
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Sambutan Lengkap Kepala Sekolah</label>
                    <div wire:ignore wire:key="portal-sambutan-editor-wrapper" x-data="portalWebTinyMCE(@entangle('sambutan_kepsek'), 'portal-sambutan-editor', 350)" class="rounded-xl overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-violet-400 focus-within:border-violet-400 transition-all bg-white shadow-inner">
                        <textarea x-ref="editor" id="portal-sambutan-editor" class="w-full">{!! $sambutan_kepsek !!}</textarea>
                    </div>
                    @error('sambutan_kepsek') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Video YouTube Beranda (Profil / Kegiatan) --}}
                <div class="pt-4 border-t border-slate-100" x-data="{
                    ytUrl: @entangle('link_youtube').live,
                    getYtId() {
                        if (!this.ytUrl) return null;
                        const match = this.ytUrl.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^&?/\s]{11})/i);
                        return match ? match[1] : null;
                    }
                }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Video YouTube Beranda (Profil / Kegiatan Sekolah)
                        </label>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded-md border border-red-200">
                            <i class="fab fa-youtube"></i> Widget Video Beranda
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mb-3">
                        Tautan video YouTube yang di-embed pada widget Beranda (di sebelah kanan informasi & statistik). Masukkan URL video lengkap atau link share.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
                        <div class="md:col-span-7 space-y-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">URL Video YouTube</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-red-500">
                                        <i class="fab fa-youtube text-lg"></i>
                                    </div>
                                    <input type="url" wire:model.live="link_youtube"
                                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-400 outline-none bg-white"
                                           placeholder="https://www.youtube.com/watch?v=xxxxxxxxx atau https://youtu.be/xxxxxxxxx">
                                </div>
                                @error('link_youtube') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="p-3 bg-white rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-lightbulb text-amber-500"></i> Contoh Format Tautan yang Didukung:
                                </div>
                                <ul class="list-disc list-inside text-[11px] text-slate-500 space-y-0.5">
                                    <li><code>https://www.youtube.com/watch?v=R7uGUQPaqf0</code></li>
                                    <li><code>https://youtu.be/R7uGUQPaqf0</code></li>
                                    <li><code>https://www.youtube.com/embed/R7uGUQPaqf0</code></li>
                                </ul>
                                <p class="text-[11px] text-violet-600 font-medium pt-1">
                                    <i class="fas fa-info-circle"></i> Tautan ini juga otomatis disinkronkan ke ikon YouTube di footer dan menu media sosial.
                                </p>
                            </div>
                        </div>

                        <!-- Live Preview Video Embed -->
                        <div class="md:col-span-5">
                            <span class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Preview Video di Beranda</span>
                            <template x-if="getYtId()">
                                <div class="rounded-xl overflow-hidden shadow-md border-2 border-slate-300 aspect-video bg-black relative">
                                    <iframe class="w-full h-full"
                                            :src="'https://www.youtube.com/embed/' + getYtId()"
                                            title="Preview Video YouTube"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen>
                                    </iframe>
                                </div>
                            </template>
                            <template x-if="!getYtId()">
                                <div class="rounded-xl border-2 border-dashed border-slate-300 p-6 text-center text-slate-400 flex flex-col items-center justify-center aspect-video bg-white shadow-inner">
                                    <i class="fab fa-youtube text-4xl mb-2 text-slate-300"></i>
                                    <span class="text-xs font-medium">Video belum disetel</span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Masukkan link video YouTube di samping</span>
                                </div>
                            </template>
                        </div>
                    </div>
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

                {{-- Profil Singkat --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Profil Singkat & Sejarah Sekolah</label>
                    <p class="text-xs text-slate-400 mb-2">Ringkasan profil atau sejarah sekolah yang tampil di beranda dan halaman profil.</p>
                    <div wire:ignore wire:key="portal-profil-editor-wrapper" x-data="portalWebTinyMCE(@entangle('profil_singkat'), 'portal-profil-editor', 280)" class="rounded-xl overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-violet-400 focus-within:border-violet-400 transition-all bg-white shadow-inner">
                        <textarea x-ref="editor" id="portal-profil-editor" class="w-full">{!! $profil_singkat !!}</textarea>
                    </div>
                    @error('profil_singkat') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Visi Sekolah --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Visi Sekolah</label>
                    <p class="text-xs text-slate-400 mb-2">Visi sekolah dapat diformat tebal, miring, atau paragraf.</p>
                    <div wire:ignore wire:key="portal-visi-editor-wrapper" x-data="portalWebTinyMCE(@entangle('visi'), 'portal-visi-editor', 220)" class="rounded-xl overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-violet-400 focus-within:border-violet-400 transition-all bg-white shadow-inner">
                        <textarea x-ref="editor" id="portal-visi-editor" class="w-full">{!! $visi !!}</textarea>
                    </div>
                    @error('visi') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Misi Sekolah --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Misi Sekolah</label>
                    <p class="text-xs text-slate-400 mb-2">Dapat berupa daftar poin (bullet / numbered list) atau paragraf.</p>
                    <div wire:ignore wire:key="portal-misi-editor-wrapper" x-data="portalWebTinyMCE(@entangle('misi'), 'portal-misi-editor', 300)" class="rounded-xl overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-violet-400 focus-within:border-violet-400 transition-all bg-white shadow-inner">
                        <textarea x-ref="editor" id="portal-misi-editor" class="w-full">{!! $misi !!}</textarea>
                    </div>
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