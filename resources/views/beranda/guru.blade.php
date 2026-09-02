<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori Guru & Staff - {{ $sekolah?->school_name ?? 'Beranda Sekolah' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
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

<!-- Header Section (Dynamic Theme Color matching Footer) -->
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
            <span>Direktori PTK</span>
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-white mb-3 tracking-tight">
            Pendidik & Tenaga Kependidikan
        </h1>
        <div class="w-24 h-1.5 bg-amber-400 mx-auto rounded-full mb-4 shadow-sm"></div>
        
        <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto leading-relaxed font-medium">
            Mengenal lebih dekat sosok-sosok inspiratif yang membimbing siswa-siswi {{ $sekolah?->school_name ?? 'Sekolah' }} dengan hati dan dedikasi.
        </p>
    </div>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 py-16 max-w-7xl" x-data="{ 
    modalOpen: false,
    activeTeacher: null,
    openModal(teacher) {
        this.activeTeacher = teacher;
        this.modalOpen = true;
        document.body.style.overflow = 'hidden';
    },
    closeModal() {
        this.modalOpen = false;
        this.activeTeacher = null;
        document.body.style.overflow = 'auto';
    }
}">
    
    <!-- Grid Guru -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-8 gap-y-12 mt-8">
        @foreach($teachers as $guru)
            <!-- CARD GURU -->
            <div class="group relative w-full cursor-pointer h-full flex flex-col bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 overflow-hidden text-center" 
                 @click="openModal({
                     name: '{{ addslashes($guru->name) }}',
                     position: '{{ addslashes($guru->semua_jabatan[0] ?? 'Guru') }}',
                     subject: '{{ addslashes(implode(', ', $guru->mapel_aktif)) ?: '-' }}',
                     nip: '{{ addslashes($guru->nip ?? '-') }}',
                     img: '{{ $guru->avatar_url }}',
                     email: '{{ addslashes($guru->user->email ?? '') }}',
                     facebook: '{{ addslashes($guru->facebook_url ?? '') }}',
                     instagram: '{{ addslashes($guru->instagram_url ?? '') }}'
                 })">
                
                <!-- Foto Wrapper -->
                <div class="relative w-full aspect-[4/5] bg-slate-200 overflow-hidden">
                    <img src="{{ $guru->avatar_url }}" alt="{{ $guru->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-300 scale-0 group-hover:scale-100">
                        <span class="w-14 h-14 bg-white/20 backdrop-blur-md border border-white/50 rounded-full flex items-center justify-center text-white shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                        </span>
                    </div>

                    <!-- Social Links (Hover) -->
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2 translate-y-10 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                        @if($guru->facebook_url)
                        <span class="w-8 h-8 bg-white/90 backdrop-blur-sm text-brand-primary rounded-full flex items-center justify-center hover:bg-brand-primary hover:text-white transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </span>
                        @endif
                        @if($guru->instagram_url)
                        <span class="w-8 h-8 bg-white/90 backdrop-blur-sm text-brand-primary rounded-full flex items-center justify-center hover:bg-brand-primary hover:text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Info Box -->
                <div class="p-5 flex-grow flex flex-col justify-center">
                    <h3 class="text-lg font-bold text-slate-800 leading-tight mb-1 line-clamp-1">{{ $guru->name }}</h3>
                    <p class="text-brand-primary text-xs font-bold uppercase tracking-wide mt-1">{{ $guru->semua_jabatan[0] ?? 'Guru' }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-16 flex justify-center">
        {{ $teachers->links() }}
    </div>

    <!-- MODAL DETAIL -->
    <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.escape.window="closeModal()">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col md:flex-row" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            <button @click="closeModal()" class="absolute top-4 right-4 z-20 bg-white/20 text-white p-2 rounded-full md:hidden backdrop-blur-sm hover:bg-white/30 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            <div class="md:w-5/12 h-80 md:h-auto relative bg-slate-100 flex items-center justify-center overflow-hidden">
                <template x-if="activeTeacher?.img">
                    <img :src="activeTeacher.img" :alt="activeTeacher.name" class="w-full h-full object-cover">
                </template>
            </div>
            <div class="md:w-7/12 p-8 md:p-10 bg-white relative flex flex-col justify-center">
                <button @click="closeModal()" class="hidden md:block absolute top-6 right-6 text-slate-400 hover:text-rose-500 transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                <div class="mb-6">
                    <span class="inline-block px-3 py-1 bg-brand-primary-50 text-brand-primary rounded-full text-xs font-bold uppercase tracking-wider mb-2 border border-brand-primary-100" x-text="activeTeacher?.position"></span>
                    <h3 class="text-3xl font-serif font-bold text-slate-800 mb-1" x-text="activeTeacher?.name"></h3>
                    <div class="h-1.5 w-16 bg-brand-primary rounded-full mt-3"></div>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-600 shadow-sm border border-slate-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg></div>
                        <div><p class="text-xs text-slate-500 font-bold uppercase mb-0.5">NIP / NUPTK</p><p class="font-bold text-slate-800 text-sm" x-text="activeTeacher?.nip || '-'"></p></div>
                    </div>
                    <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-600 shadow-sm border border-slate-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></div>
                        <div><p class="text-xs text-slate-500 font-bold uppercase mb-0.5">Mata Pelajaran</p><p class="font-bold text-slate-800 text-sm" x-text="activeTeacher?.subject || '-'"></p></div>
                    </div>
                    <div class="pt-5 mt-5 border-t border-slate-100">
                        <p class="text-xs text-slate-400 font-bold uppercase mb-3">Kontak & Media Sosial</p>
                        <div class="flex gap-3">
                            <a x-show="activeTeacher?.email" :href="'mailto:'+activeTeacher?.email" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600 hover:bg-brand-primary hover:text-white transition-colors border border-slate-200 shadow-sm" title="Email"><i class="fas fa-envelope"></i></a>
                            <a x-show="activeTeacher?.facebook" :href="activeTeacher?.facebook" target="_blank" class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white transition-colors border border-blue-100 shadow-sm" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a x-show="activeTeacher?.instagram" :href="activeTeacher?.instagram" target="_blank" class="w-10 h-10 rounded-full bg-pink-50 flex items-center justify-center text-pink-600 hover:bg-pink-600 hover:text-white transition-colors border border-pink-100 shadow-sm" title="Instagram"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
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
