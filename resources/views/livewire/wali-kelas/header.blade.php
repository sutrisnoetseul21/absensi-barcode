<!-- Header Banner -->
<div class="relative bg-blue-900 overflow-hidden rounded-b-[2.5rem] shadow-xl pb-16">
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('hero-bg-school.png') }}" class="w-full h-full object-cover object-center opacity-60 mix-blend-overlay">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/90 via-blue-900/70 to-blue-800/40"></div>
        <!-- Decorative Blobs -->
        <div class="absolute top-0 -left-20 w-72 h-72 bg-blue-400 rounded-full mix-blend-screen filter blur-[80px] opacity-30 animate-blob"></div>
        <div class="absolute bottom-0 right-10 w-72 h-72 bg-cyan-400 rounded-full mix-blend-screen filter blur-[80px] opacity-30 animate-blob animation-delay-2000"></div>
    </div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-3xl font-extrabold leading-9 text-white sm:text-4xl sm:truncate flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl backdrop-blur-md border border-white/30 flex items-center justify-center text-white shadow-lg">
                        <svg class="w-7 h-7 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    Dashboard Wali Kelas
                </h2>
                <p class="mt-4 text-base text-indigo-200 max-w-2xl">
                    Kelola data absensi, pantau keterlambatan harian, dan lakukan presensi manual untuk siswa di kelas binaan Anda.
                </p>
            </div>
            <div class="mt-6 flex xl:mt-0 xl:ml-4 gap-4 items-center">
                @if(count($classes) > 0 && $selectedClassId)
                    <button wire:click="openInputModal" class="inline-flex items-center px-5 py-2.5 border border-transparent rounded-xl shadow-lg shadow-blue-500/30 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:-translate-y-0.5 group">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-blue-100 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Input Manual
                    </button>
                @endif
                
                @if(Auth::guard('wali_kelas')->check())
                    <form action="{{ route('wali-kelas.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2.5 border border-white/20 rounded-xl shadow-sm text-sm font-bold text-white bg-white/10 hover:bg-white/20 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-indigo-500 transition-all duration-300">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
