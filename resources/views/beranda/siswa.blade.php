<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori Siswa - {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    @php
        $favicon = $sekolah?->school_logo_path ? asset('storage/' . $sekolah->school_logo_path) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ $favicon }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @if($sekolah)
        <style>
            :root {
                @if($sekolah->theme_primary) --color-brand-primary: {{ $sekolah->theme_primary }}; @endif
                @if($sekolah->theme_secondary) --color-brand-secondary: {{ $sekolah->theme_secondary }}; @endif
            }
        </style>
    @endif
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

<!-- Header Section (Dynamic Theme Color matching PTK / Guru) -->
<div class="pt-32 pb-20 relative overflow-hidden text-white border-b-4 border-amber-400"
     style="background: linear-gradient(135deg, color-mix(in srgb, var(--color-brand-primary, #059669) 85%, black 15%) 0%, color-mix(in srgb, var(--color-brand-secondary, #047857) 75%, black 35%) 100%);">
    
    {{-- Decorative Background Glow --}}
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <!-- Breadcrumb -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-wider mb-4 shadow-sm">
            <a href="{{ route('beranda') }}" class="text-white hover:text-amber-300 transition-colors">Beranda</a>
            <span class="text-white/40">/</span>
            <span>Warga Sekolah</span>
            <span class="text-white/40">/</span>
            <span>Direktori Siswa</span>
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-white mb-3 tracking-tight">
            Direktori Siswa
        </h1>
        <div class="w-24 h-1.5 bg-amber-400 mx-auto rounded-full mb-4 shadow-sm"></div>
        
        <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto leading-relaxed font-medium">
            Daftar peserta didik aktif {{ $sekolah?->school_name ?? 'Sekolah' }} yang terdaftar pada rombongan belajar tahun ajaran {{ $activeYear?->name ?? 'aktif' }}.
        </p>

        @if($activeYear)
            <div class="mt-4 inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-300/30 text-xs font-semibold backdrop-blur-sm">
                <i class="fas fa-calendar-check text-[11px]"></i>
                Tahun Ajaran: <span class="font-bold text-white">{{ $activeYear->name }}</span>
            </div>
        @endif
    </div>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 py-12 max-w-7xl" x-data="{ 
    modalOpen: false,
    activeStudent: null,
    openModal(student) {
        this.activeStudent = student;
        this.modalOpen = true;
        document.body.style.overflow = 'hidden';
    },
    closeModal() {
        this.modalOpen = false;
        this.activeStudent = null;
        document.body.style.overflow = 'auto';
    }
}">
    
    <!-- Filter & Search Bar Section -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 mb-10">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Filter Pills Kelas -->
            <div class="flex-1 overflow-x-auto pb-2 lg:pb-0 scrollbar-none">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 shrink-0 flex items-center gap-1.5">
                        <i class="fas fa-filter text-brand-primary"></i> Kelas:
                    </span>
                    
                    {{-- Pill Semua Kelas --}}
                    <a href="{{ route('siswa.all', array_filter(['search' => $search])) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ empty($selectedKelas) ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20 scale-105' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        Semua Kelas
                    </a>

                    {{-- Pills Tiap Kelas --}}
                    @foreach($kelasList as $kelasItem)
                        @php
                            $isClassActive = ($selectedKelas === $kelasItem->name || $selectedKelas == $kelasItem->id);
                        @endphp
                        <a href="{{ route('siswa.all', array_filter(['kelas' => $kelasItem->name, 'search' => $search])) }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $isClassActive ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/20 scale-105' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                            {{ $kelasItem->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Search Box -->
            <div class="w-full lg:w-80 shrink-0">
                <form action="{{ route('siswa.all') }}" method="GET" class="relative flex items-center">
                    @if(!empty($selectedKelas))
                        <input type="hidden" name="kelas" value="{{ $selectedKelas }}">
                    @endif
                    <div class="relative w-full">
                        <input type="text" name="search" value="{{ $search }}" 
                               placeholder="Cari nama / NIS..." 
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-slate-800 placeholder-slate-400">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        @if(!empty($search))
                            <a href="{{ route('siswa.all', array_filter(['kelas' => $selectedKelas])) }}" 
                               class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 text-xs p-1"
                               title="Hapus pencarian">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="sr-only">Cari</button>
                </form>
            </div>

        </div>

        <!-- Info Hasil Filter -->
        <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
            <div>
                Menampilkan <strong class="text-slate-800 font-bold">{{ $totalSiswaCount }}</strong> siswa aktif 
                @if(!empty($selectedKelas))
                    pada <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold bg-emerald-50 text-brand-primary border border-emerald-100">Kelas {{ $selectedKelas }}</span>
                @else
                    dari seluruh kelas
                @endif
                @if(!empty($search))
                    dengan kata kunci <strong class="text-slate-800">"{{ $search }}"</strong>
                @endif
            </div>

            @if(!empty($selectedKelas) || !empty($search))
                <a href="{{ route('siswa.all') }}" class="text-brand-primary font-bold hover:underline flex items-center gap-1">
                    <i class="fas fa-redo text-[10px]"></i> Reset Semua Filter
                </a>
            @endif
        </div>
    </div>

    <!-- Grid Siswa -->
    @if($students->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-10">
            @foreach($students as $siswa)
                @php
                    $namaKelas = $siswa->enrollments->first()?->kelas?->name ?? 'Kelas -';
                    $genderLabel = match($siswa->gender) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => '-'
                    };
                @endphp
                <!-- CARD SISWA -->
                <div class="group relative w-full cursor-pointer h-full flex flex-col bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 overflow-hidden text-center" 
                     @click="openModal({
                         name: '{{ addslashes($siswa->name) }}',
                         kelas: '{{ addslashes($namaKelas) }}',
                         nis: '{{ addslashes($siswa->nis ?? '-') }}',
                         gender: '{{ addslashes($genderLabel) }}',
                         status: '{{ addslashes(ucfirst($siswa->status)) }}',
                         tahunAjaran: '{{ addslashes($activeYear?->name ?? '-') }}',
                         img: '{{ $siswa->avatar_url }}'
                     })">
                    
                    <!-- Foto Wrapper -->
                    <div class="relative w-full aspect-[4/5] bg-slate-100 overflow-hidden">
                        <img src="{{ $siswa->avatar_url }}" alt="{{ $siswa->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        
                        <!-- Floating Class Badge -->
                        <div class="absolute top-3 left-3 z-10">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-900/80 backdrop-blur-md text-white font-bold text-[11px] shadow-sm border border-white/20">
                                <i class="fas fa-graduation-cap text-amber-300 text-[10px]"></i> {{ $namaKelas }}
                            </span>
                        </div>

                        <!-- Floating Gender Badge -->
                        <div class="absolute top-3 right-3 z-10">
                            <span class="w-7 h-7 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center text-xs shadow-sm border border-slate-200/50 {{ $siswa->gender === 'P' ? 'text-pink-500' : 'text-blue-600' }}" title="{{ $genderLabel }}">
                                <i class="fas {{ $siswa->gender === 'P' ? 'fa-venus' : 'fa-mars' }}"></i>
                            </span>
                        </div>

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <span class="w-12 h-12 bg-white/20 backdrop-blur-md border border-white/50 rounded-full flex items-center justify-center text-white shadow-lg transform scale-50 group-hover:scale-100 transition-transform duration-300">
                                <i class="fas fa-eye text-sm"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="p-4 flex-grow flex flex-col justify-center">
                        <h3 class="text-base font-bold text-slate-800 leading-snug mb-1 line-clamp-1 group-hover:text-brand-primary transition-colors">
                            {{ $siswa->name }}
                        </h3>
                        <div class="flex items-center justify-center text-slate-500 text-xs font-semibold">
                            <span>NIS: <span class="text-slate-700 font-mono font-bold">{{ $siswa->nis ?? '-' }}</span></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
            <div class="mt-14 flex justify-center">
                {{ $students->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm max-w-md mx-auto">
            <div class="w-16 h-16 rounded-3xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak Ada Data Siswa</h3>
            <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                Tidak ditemukan data siswa aktif yang sesuai dengan kriteria filter atau pencarian Anda.
            </p>
            <a href="{{ route('siswa.all') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-primary text-white font-bold text-xs hover:opacity-90 transition-all shadow-md shadow-brand-primary/20">
                <i class="fas fa-redo text-[10px]"></i> Tampilkan Semua Siswa
            </a>
        </div>
    @endif

    <!-- MODAL DETAIL SISWA -->
    <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         @keydown.escape.window="closeModal()">
        
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="closeModal()"></div>
        
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col sm:flex-row" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-10 scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <button @click="closeModal()" class="absolute top-4 right-4 z-20 bg-slate-100 hover:bg-rose-50 hover:text-rose-500 text-slate-500 p-2 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            
            <!-- Foto Siswa -->
            <div class="sm:w-5/12 h-64 sm:h-auto relative bg-slate-100 flex items-center justify-center overflow-hidden">
                <template x-if="activeStudent?.img">
                    <img :src="activeStudent.img" :alt="activeStudent.name" class="w-full h-full object-cover">
                </template>
            </div>

            <!-- Detail Info -->
            <div class="sm:w-7/12 p-6 sm:p-8 bg-white relative flex flex-col justify-center">
                <div class="mb-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-brand-primary/10 text-brand-primary rounded-full text-xs font-extrabold uppercase tracking-wider border border-brand-primary/20" x-text="'Kelas ' + (activeStudent?.kelas || '-')"></span>
                        <span class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-md text-[10px] font-bold border border-emerald-200">
                            Siswa Aktif
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-1" x-text="activeStudent?.name"></h3>
                    <div class="h-1.5 w-14 bg-brand-primary rounded-full mt-2"></div>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- NIS -->
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-8 h-8 bg-white rounded-xl flex items-center justify-center text-slate-600 shadow-sm border border-slate-100">
                            <i class="fas fa-id-card text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">NIS (Nomor Induk Siswa)</p>
                            <p class="font-mono font-bold text-slate-800 text-sm" x-text="activeStudent?.nis || '-'"></p>
                        </div>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-8 h-8 bg-white rounded-xl flex items-center justify-center text-slate-600 shadow-sm border border-slate-100">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Jenis Kelamin</p>
                            <p class="font-bold text-slate-800" x-text="activeStudent?.gender || '-'"></p>
                        </div>
                    </div>

                    <!-- Tahun Ajaran -->
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-8 h-8 bg-white rounded-xl flex items-center justify-center text-slate-600 shadow-sm border border-slate-100">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Tahun Ajaran</p>
                            <p class="font-bold text-slate-800" x-text="activeStudent?.tahunAjaran || '-'"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                    <button type="button" @click="closeModal()" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
@include('beranda.sections.footer')

@livewireScripts
</body>
</html>
