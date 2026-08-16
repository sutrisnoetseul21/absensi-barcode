<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8">
    
    <!-- Top Hero Banner & Profile Header -->
    <div class="relative bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/80 overflow-hidden">
        <!-- Sleek Banner Cover -->
        <div class="h-36 sm:h-44 bg-gradient-to-r from-indigo-700 via-indigo-800 to-purple-800 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10 backdrop-blur-[1px]"></div>
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <!-- Left Header Pill Tag -->
            <div class="absolute top-4 left-6 flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-xs font-semibold shadow-sm">
                <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Portal Guru &bull; Profil & Akun Pendidik</span>
            </div>

            <!-- Right Header Wali Kelas Badge -->
            <div class="absolute top-4 right-6">
                @if(!empty($kelasWaliList))
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-extrabold bg-emerald-500/90 text-white backdrop-blur-md shadow-md border border-emerald-400/40 tracking-wide">
                        <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Wali Kelas {{ implode(', ', $kelasWaliList) }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-md shadow-md border border-white/30">
                        Guru Pengajar
                    </span>
                @endif
            </div>
        </div>

        <!-- Profile Details Body -->
        <div class="px-6 sm:px-8 pb-6 pt-0 relative">
            <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-5 -mt-14 sm:-mt-16">
                
                <!-- Left: Avatar + Name + Badges -->
                <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left w-full">
                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white p-1.5 shadow-2xl ring-4 ring-white overflow-hidden bg-slate-100 flex items-center justify-center">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" alt="Preview Foto" class="w-full h-full object-cover rounded-2xl">
                            @elseif ($photo_path)
                                <img src="{{ asset('storage/' . $photo_path) }}" alt="{{ $nama_lengkap }}" class="w-full h-full object-cover rounded-2xl">
                            @else
                                <div class="w-full h-full rounded-2xl bg-gradient-to-br from-brand-primary to-indigo-700 flex items-center justify-center text-white font-black text-4xl shadow-inner">
                                    {{ substr($nama_lengkap, 0, 1) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Name & Information -->
                    <div class="space-y-2 sm:-mt-4 w-full">
                        <div class="flex items-center justify-center sm:justify-start gap-3 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none">{{ $nama_lengkap }}</h1>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Tenaga Pendidik Aktif
                            </span>
                        </div>

                        <!-- Info Badges Row -->
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-2.5 sm:pt-3">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80 shadow-xs">
                                <span class="text-slate-400">NIP:</span> <strong class="font-bold text-slate-900">{{ $nip }}</strong>
                            </span>

                            @if($no_hp)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                                    WA: {{ $no_hp }}
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80 shadow-xs">
                                <span class="text-slate-400">Email:</span> <strong class="font-bold text-slate-900">{{ $email }}</strong>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Penugasan Akademik & Foto Profil -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Foto Profil Upload Card -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Foto Profil Guru
                    </h3>
                </div>

                @if (session()->has('success_photo'))
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ session('success_photo') }}
                    </div>
                @endif

                <form wire:submit="updatePhoto" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Unggah Foto Baru</label>
                        <input type="file" wire:model="photo" accept="image/png, image/jpeg, image/jpg, image/webp" 
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20 file:cursor-pointer cursor-pointer border border-slate-200 rounded-2xl p-1 bg-slate-50/50">
                        @error('photo') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-2">
                        @if ($photo)
                            <button type="submit" wire:loading.attr="disabled" class="flex-1 py-2.5 px-3 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-primary/20 transition-all flex items-center justify-center gap-2">
                                <span wire:loading.remove wire:target="photo, updatePhoto">Simpan Foto</span>
                                <span wire:loading wire:target="photo, updatePhoto">Menyimpan...</span>
                            </button>
                        @endif

                        @if ($photo_path && !$photo)
                            <button type="button" wire:click="removePhoto" wire:confirm="Apakah Anda yakin ingin menghapus foto profil?" class="py-2.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition-all">
                                Hapus Foto
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Informasi Penugasan Akademik (Read Only) -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        Penugasan Akademik
                    </h3>
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        Master Data
                    </span>
                </div>

                <!-- Info Banner Notice -->
                <div class="p-3 bg-amber-50/90 border border-amber-200/80 rounded-2xl text-amber-900 text-xs leading-relaxed flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Penugasan Wali Kelas, Mata Pelajaran, dan NIP dikelola langsung oleh Administrator Kurikulum.</span>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Wali Kelas Binaan -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Penugasan Wali Kelas</span>
                        @if(!empty($kelasWaliList))
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @foreach($kelasWaliList as $kw)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Kelas {{ $kw }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-slate-500 font-medium italic">Tidak bertugas sebagai Wali Kelas</span>
                        @endif
                    </div>

                    <!-- Kelas Pantau BK (Jika Ada) -->
                    @if(!empty($kelasPantauList))
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Akses Kelas Pantau (BK)</span>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @foreach($kelasPantauList as $kp)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        Kelas {{ $kp }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Mata Pelajaran yang Diampu -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Mata Pelajaran (Tahun Ini)</span>
                        @if(!empty($mapelList))
                            <ul class="space-y-1 mt-1 text-slate-700 font-semibold">
                                @foreach($mapelList as $mp)
                                    <li class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                                        {{ $mp }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-slate-500 font-medium italic">Belum ada mata pelajaran terdaftar</span>
                        @endif
                    </div>

                    <!-- Jabatan & Tugas Tambahan -->
                    @if(!empty($jabatanList))
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Jabatan & Tugas Tambahan</span>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @foreach($jabatanList as $jb)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        {{ $jb }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right Column: Form Kontak & Keamanan Password -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Card 1: Informasi Kontak & Akun -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                            Informasi Kontak & Akun Login
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Perbarui email aktif dan nomor WhatsApp untuk notifikasi presensi.</p>
                    </div>
                </div>

                @if (session()->has('success_contact'))
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ session('success_contact') }}
                    </div>
                @endif

                <form wire:submit="updateContact" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama (Readonly) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap</label>
                            <input type="text" value="{{ $nama_lengkap }}" disabled 
                                   class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-2xl text-xs font-medium text-slate-600 cursor-not-allowed">
                            <span class="text-[10px] text-slate-400 mt-1 block">Nama resmi terdaftar di sistem.</span>
                        </div>

                        <!-- NIP (Readonly) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" value="{{ $nip }}" disabled 
                                   class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-2xl text-xs font-medium text-slate-600 cursor-not-allowed">
                            <span class="text-[10px] text-slate-400 mt-1 block">NIP resmi pendidik.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <!-- Email Login -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alamat Email Login <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" wire:model="email" 
                                   placeholder="nama@sekolah.sch.id"
                                   class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 focus:border-brand-primary focus:bg-white rounded-2xl text-xs font-medium text-slate-800 transition-all outline-none">
                            @error('email') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- No WhatsApp -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP
                            </label>
                            <input type="text" wire:model="no_hp" 
                                   placeholder="081234567890"
                                   class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 focus:border-brand-primary focus:bg-white rounded-2xl text-xs font-medium text-slate-800 transition-all outline-none">
                            @error('no_hp') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                            <span class="text-[10px] text-slate-400 mt-1 block">Digunakan untuk menerima notifikasi sistem & siswa.</span>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="py-2.5 px-6 bg-brand-primary hover:bg-brand-primary/90 text-white rounded-2xl text-xs font-bold shadow-md shadow-brand-primary/20 transition-all flex items-center gap-2">
                            <span wire:loading.remove wire:target="updateContact">Simpan Perubahan Kontak</span>
                            <span wire:loading wire:target="updateContact">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Ganti Password -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Keamanan & Kata Sandi
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pastikan menggunakan kombinasi kata sandi yang aman dan unik.</p>
                    </div>
                </div>

                @if (session()->has('success_password'))
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ session('success_password') }}
                    </div>
                @endif

                <form wire:submit="updatePassword" class="space-y-4">
                    <!-- Password Saat Ini -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" wire:model="current_password" 
                               placeholder="Masukkan kata sandi lama Anda"
                               class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 focus:border-brand-primary focus:bg-white rounded-2xl text-xs font-medium text-slate-800 transition-all outline-none">
                        @error('current_password') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <!-- Password Baru -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" wire:model="new_password" 
                                   placeholder="Minimal 8 karakter"
                                   class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 focus:border-brand-primary focus:bg-white rounded-2xl text-xs font-medium text-slate-800 transition-all outline-none">
                            @error('new_password') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Ulangi Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" wire:model="new_password_confirmation" 
                                   placeholder="Ulangi kata sandi baru"
                                   class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 focus:border-brand-primary focus:bg-white rounded-2xl text-xs font-medium text-slate-800 transition-all outline-none">
                            @error('new_password_confirmation') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="py-2.5 px-6 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-xs font-bold shadow-md transition-all flex items-center gap-2">
                            <span wire:loading.remove wire:target="updatePassword">Perbarui Kata Sandi</span>
                            <span wire:loading wire:target="updatePassword">Memperbarui...</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
