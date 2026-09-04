<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Login Portal Web Sekolah' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center font-jakarta relative overflow-hidden">

    @php $sekolah = \App\Models\PengaturanSekolah::current(); @endphp

    <!-- Background -->
    <div class="absolute inset-0 z-0">
        @if($sekolah?->login_background_path)
            <img src="{{ asset('storage/' . $sekolah->login_background_path) }}" class="w-full h-full object-cover opacity-40">
        @else
            <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover opacity-40">
        @endif
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950/80 via-slate-900/70 to-violet-950/60"></div>
        <div class="absolute top-1/4 -left-24 w-96 h-96 bg-violet-500 rounded-full mix-blend-screen filter blur-[120px] opacity-30 animate-blob"></div>
        <div class="absolute bottom-1/4 -right-24 w-96 h-96 bg-indigo-500 rounded-full mix-blend-screen filter blur-[120px] opacity-30 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md mx-4">
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl ring-1 ring-white/10">

            <!-- Logo & Judul -->
            <div class="text-center mb-8">
                @if($sekolah?->school_logo_path)
                    <img src="{{ asset('storage/' . $sekolah->school_logo_path) }}" alt="Logo Sekolah" class="w-16 h-16 object-contain mx-auto mb-4 drop-shadow-lg">
                @else
                    <div class="w-16 h-16 bg-violet-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                        </svg>
                    </div>
                @endif
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Portal Web Sekolah</h1>
                <p class="text-sm text-slate-300 mt-1">{{ $sekolah?->school_name ?? 'Sistem Informasi Sekolah' }}</p>
            </div>

            {{ $slot }}
        </div>

        <p class="text-center text-slate-400 text-xs mt-6">
            &copy; {{ date('Y') }} {{ $sekolah?->school_name ?? 'Sekolah' }} &middot; Portal Web Sekolah
        </p>
    </div>

    @livewireScripts
</body>
</html>
