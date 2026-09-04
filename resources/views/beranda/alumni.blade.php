<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracer Study Alumni - {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    <meta name="description" content="Portal Tracer Study dan Direktori Alumni {{ $sekolah?->school_name ?? 'Sekolah' }}.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>

    @if($sekolah)
        <style>
            :root {
                @if($sekolah->theme_primary) --color-brand-primary: {{ $sekolah->theme_primary }}; @endif
                @if($sekolah->theme_secondary) --color-brand-secondary: {{ $sekolah->theme_secondary }}; @endif
                @if($sekolah->theme_accent) --color-brand-accent: {{ $sekolah->theme_accent }}; @endif
                @if($sekolah->theme_warning) --color-brand-warning: {{ $sekolah->theme_warning }}; @endif
                @if($sekolah->theme_danger) --color-brand-danger: {{ $sekolah->theme_danger }}; @endif
                @if($sekolah->theme_info) --color-brand-info: {{ $sekolah->theme_info }}; @endif
            }
        </style>
    @endif
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-primary-light selection:text-brand-primary-dark flex flex-col min-h-screen"
      x-data="{ search: '' }">

    {{-- ══════════════════ NAVBAR ══════════════════ --}}
    <x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

    {{-- ══════════════════ HERO BANNER ══════════════════ --}}
    <section class="relative pt-32 pb-20 overflow-hidden"
             style="background: linear-gradient(135deg, var(--color-brand-primary, #0f172a) 0%, var(--color-brand-secondary, #1e293b) 100%);">
        
        <!-- Subtle Glow Shapes -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-amber-400/10 rounded-full filter blur-3xl pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center text-white">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold uppercase tracking-wider text-amber-300 mb-4">
                <i class="fas fa-graduation-cap"></i> Jejak Langkah Alumni
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-4">
                {{ $alumniSetting->banner_title ?? 'Tracer Study Alumni' }}
            </h1>
            <p class="text-white/80 text-sm sm:text-base md:text-lg max-w-2xl mx-auto font-normal mb-8 leading-relaxed">
                {{ $alumniSetting->banner_text ?? 'Mari terus menjalin silaturahmi dan berbagi inspirasi. Data Anda sangat berharga bagi pengembangan kualitas pendidikan di sekolah tercinta kita.' }}
            </p>

            <!-- Flash Message (Success) -->
            @if(session('success'))
                <div class="max-w-xl mx-auto mb-6 bg-emerald-500/20 backdrop-blur-md border border-emerald-400 text-white px-5 py-3.5 rounded-2xl flex items-center gap-3 shadow-lg text-left">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Flash Message (Info Login untuk Siswa Sistem) -->
            @if(session('info_login'))
                <div class="max-w-xl mx-auto mb-6 bg-blue-500/20 backdrop-blur-md border border-blue-400 text-white px-5 py-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center shrink-0">
                            <i class="fas fa-info-circle text-white text-base"></i>
                        </div>
                        <p class="text-sm font-medium leading-relaxed">{{ session('info_login') }}</p>
                    </div>
                    <a href="{{ route('portal-siswa.login') }}" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold rounded-xl whitespace-nowrap shadow-md transition-all shrink-0">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login Portal
                    </a>
                </div>
            @endif

            <!-- Error Validation Messages -->
            @if($errors->any())
                <div class="max-w-xl mx-auto mb-6 bg-rose-500/20 backdrop-blur-md border border-rose-400 text-white px-5 py-3.5 rounded-2xl flex items-start gap-3 shadow-lg text-left">
                    <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fas fa-exclamation text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm text-rose-100 mb-1">Gagal mengirim data:</p>
                        <ul class="list-disc pl-4 text-xs text-rose-100 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($alumniSetting->is_active)
                <button @click="$dispatch('open-alumni-modal')" class="inline-flex items-center gap-2.5 px-8 py-3.5 bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold text-sm rounded-full transition-all transform hover:scale-105 shadow-xl shadow-amber-500/20 cursor-pointer">
                    <i class="fas fa-user-plus text-base"></i> {{ $alumniSetting->button_text ?? 'Daftarkan Data Saya' }}
                </button>
            @else
                <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/10 text-white text-xs font-semibold">
                    <i class="fas fa-lock"></i> Formulir Tracer Alumni Sedang Ditutup Sementara
                </div>
            @endif
        </div>
    </section>

    <!-- Stats Section -->
    @if(isset($totalAlumniCount) && $totalAlumniCount > 0)
        <section class="py-8 bg-white border-b border-slate-200/80 relative -mt-8 z-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 rounded-3xl shadow-lg">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg mx-auto mb-2">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-slate-800">{{ $totalAlumniCount }}</h3>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mt-0.5">Total Alumni Terdata</p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg mx-auto mb-2">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-slate-800">{{ $melanjutkanCount }}</h3>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mt-0.5">Melanjutkan Pendidikan</p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg mx-auto mb-2">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-slate-800">{{ $angkatanTerbaru ?? '-' }}</h3>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mt-0.5">Angkatan Terbaru</p>
                </div>

            </div>
        </section>
    @endif

    <!-- Direktori Alumni Section -->
    @if($alumniSetting->show_table)
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 flex-1 w-full" id="direktori">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Direktori Alumni</h2>
                    <p class="text-slate-500 text-sm mt-1">Daftar alumni yang telah terdata dalam tracer study sekolah.</p>
                </div>

                @if($alumnis instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="text-xs font-semibold text-slate-500 bg-slate-100 px-3.5 py-1.5 rounded-full border border-slate-200">
                        Menampilkan <span class="font-bold text-slate-800">{{ $alumnis->firstItem() ?? 0 }}-{{ $alumnis->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $alumnis->total() }}</span> alumni
                    </div>
                @endif
            </div>

            <!-- Filter Toolbar (Form Pencarian & Filter Per Angkatan/Tahun/Jenjang) -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs mb-8">
                <form action="{{ route('alumni.index') }}#direktori" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                    
                    <!-- 1. Filter Cari Nama / NISN -->
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">Cari Nama / NISN / Sekolah</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau NISN..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none">
                            <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        </div>
                    </div>

                    <!-- 2. Filter Tahun Kelulusan (Angkatan) -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tahun Lulus (Angkatan)</label>
                        <select name="tahun" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium bg-white focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none">
                            <option value="">Semua Angkatan</option>
                            @foreach($tahunLulusList as $th)
                                <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>Angkatan {{ $th }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Filter Jenjang Lanjutan -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jenjang Lanjutan</label>
                        <select name="jenjang" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium bg-white focus:ring-2 focus:ring-brand-primary focus:border-brand-primary outline-none">
                            <option value="">Semua Jenjang</option>
                            @foreach($jenjangs as $j)
                                <option value="{{ $j->id }}" {{ request('jenjang') == $j->id ? 'selected' : '' }}>{{ $j->nama_jenjang }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Tombol Aksi Filter -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                            <i class="fas fa-filter text-[10px]"></i> Filter
                        </button>
                        @if(request()->hasAny(['search', 'tahun', 'jenjang', 'status']))
                            <a href="{{ route('alumni.index') }}#direktori" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-all" title="Reset Filter">
                                <i class="fas fa-undo"></i>
                            </a>
                        @endif
                    </div>

                </form>
            </div>

            @if($alumnis->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($alumnis as $alumni)
                        <div class="bg-white rounded-3xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 border border-slate-200/80 group flex flex-col justify-between">
                            
                            <div class="h-20 bg-gradient-to-r from-slate-800 to-slate-900 relative p-3">
                                <div class="absolute top-3 right-3 px-2 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-white text-[10px] font-bold" title="{{ $alumni->jenis_kelamin == 'P' ? 'Perempuan' : 'Laki-Laki' }}">
                                    {{ $alumni->jenis_kelamin == 'P' ? 'Perempuan' : 'Laki-Laki' }}
                                </div>
                            </div>

                            <div class="px-5 pb-5 pt-0 text-center relative flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="w-20 h-20 mx-auto rounded-2xl border-4 border-white shadow-md overflow-hidden bg-slate-100 -mt-10 mb-3 relative group-hover:scale-105 transition-transform flex items-center justify-center">
                                        @if($alumni->foto)
                                            <img src="{{ asset('storage/' . $alumni->foto) }}" alt="{{ $alumni->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 text-3xl font-extrabold">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <h3 class="text-base font-bold text-slate-900 truncate mb-1" title="{{ $alumni->nama }}">{{ $alumni->nama }}</h3>
                                    
                                    <div class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-bold mb-3">
                                        Lulusan {{ $alumni->tahun_lulus }}
                                    </div>
                                </div>

                                <div class="border-t border-slate-100 pt-3 text-xs">
                                    @if($alumni->melanjutkan && $alumni->jenjang)
                                        <p class="text-emerald-600 font-bold flex items-center justify-center gap-1">
                                            <i class="fas fa-graduation-cap"></i> {{ $alumni->jenjang->nama_jenjang }}
                                        </p>
                                        @if($alumni->nama_sekolah)
                                            <p class="text-slate-500 text-[11px] mt-0.5 truncate font-medium" title="{{ $alumni->nama_sekolah }}">
                                                {{ $alumni->nama_sekolah }}
                                            </p>
                                        @endif
                                    @else
                                        <p class="text-slate-400 italic">Bekerja / Wirausaha / Lainnya</p>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                @if($alumnis instanceof \Illuminate\Pagination\LengthAwarePaginator && $alumnis->hasPages())
                    <div class="mt-10">
                        {{ $alumnis->links() }}
                    </div>
                @endif

            @else
                <div class="text-center py-16 bg-white rounded-3xl border border-slate-200/80 shadow-xs max-w-lg mx-auto p-8">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-3xl text-slate-400 mx-auto mb-3">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Tidak Ada Data Ditemukan</h3>
                    <p class="text-slate-500 text-xs mt-1">
                        @if(request()->hasAny(['search', 'tahun', 'jenjang', 'status']))
                            Tidak ada alumni yang cocok dengan kriteria filter Anda. Coba reset filter.
                        @else
                            Jadilah yang pertama mengisi data tracer study alumni sekolah kita!
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'tahun', 'jenjang', 'status']))
                        <a href="{{ route('alumni.index') }}#direktori" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
                            <i class="fas fa-undo"></i> Reset Filter
                        </a>
                    @endif
                </div>
            @endif
        </main>
    @endif

    {{-- ══════════════════ MODAL FORMULIR ALUMNI (SELF-REGISTRATION) ══════════════════ --}}
    <div x-data="{ open: false, melanjutkan: false }" 
         @open-alumni-modal.window="open = true" 
         @keydown.escape.window="open = false"
         class="relative z-[100]" 
         x-show="open" 
         style="display: none;">
         
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" 
             @click="open = false"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100">
                    
                    <div class="absolute right-4 top-4 z-20">
                        <button type="button" @click="open = false" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition-colors">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    
                    <form action="{{ route('alumni.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Honeypot -->
                        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                        
                        <div class="px-6 sm:px-8 pt-8 pb-6">
                            <div class="mb-6 border-b border-slate-100 pb-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block mb-1">Tracer Study</span>
                                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900">Daftarkan Data Alumni</h3>
                                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Khusus alumni sekolah. Jika Anda lulus dari sistem absensi ini, Anda juga dapat login langsung ke Portal Siswa.</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                
                                <!-- NISN -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">NISN <span class="text-red-500">*</span></label>
                                    <input type="text" name="nisn" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Masukkan 10 digit NISN">
                                    <p class="text-[11px] text-slate-400 mt-1">NISN digunakan sebagai identifikasi unik.</p>
                                </div>
                                
                                <!-- Nama Lengkap -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Sesuai ijazah">
                                </div>
                                
                                <!-- Tahun Lulus -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tahun Lulus <span class="text-red-500">*</span></label>
                                    <select name="tahun_lulus" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white font-semibold">
                                        <option value="">Pilih Tahun</option>
                                        @for($i = date('Y'); $i >= 1990; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                
                                <!-- Jenis Kelamin -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="jenis_kelamin" value="L" required class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                            <span class="ml-2 text-xs font-semibold text-slate-700">Laki-Laki</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="jenis_kelamin" value="P" required class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                            <span class="ml-2 text-xs font-semibold text-slate-700">Perempuan</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- No. HP / WhatsApp -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">No. HP / WhatsApp</label>
                                    <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                </div>

                                <!-- Foto -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Foto Wajah (Opsional)</label>
                                    <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition-all">
                                </div>

                                <!-- Checkbox Melanjutkan -->
                                <div class="md:col-span-2 pt-2 border-t border-slate-100">
                                    <label class="flex items-center cursor-pointer p-3 rounded-2xl bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors">
                                        <input type="checkbox" name="melanjutkan" value="1" x-model="melanjutkan" class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                        <span class="ml-3 text-xs font-bold text-slate-800">Saya melanjutkan pendidikan (SMA / SMK / Kuliah)</span>
                                    </label>
                                </div>
                                
                                <!-- Conditional Fields -->
                                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4" x-show="melanjutkan" x-transition>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Jenjang Lanjutan</label>
                                        <select name="jenjang_id" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white" :required="melanjutkan">
                                            <option value="">Pilih Jenjang</option>
                                            @foreach($jenjangs as $j)
                                                <option value="{{ $j->id }}">{{ $j->nama_jenjang }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Sekolah / Kampus Lanjutan</label>
                                        <input type="text" name="nama_sekolah" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Contoh: SMA Negeri 1 Cilacap">
                                    </div>
                                </div>
                                
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 sm:px-8 py-4 flex flex-row-reverse rounded-b-3xl border-t border-slate-100 gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                                <i class="fas fa-paper-plane"></i> Kirim Data Tracer
                            </button>
                            <button type="button" @click="open = false" class="rounded-2xl bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 shadow-xs border border-slate-200 hover:bg-slate-50 transition-all cursor-pointer">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ FOOTER ══════════════════ --}}
    <x-public-dashboard.footer :pengaturanSekolah="$sekolah" />

    @livewireScripts
</body>
</html>
