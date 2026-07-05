<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-emerald-900 to-slate-900 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="absolute top-[20%] right-[-5%] w-96 h-96 bg-teal-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    <div class="absolute bottom-[-10%] left-[20%] w-96 h-96 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>

    <div class="w-full max-w-md px-8 py-10 backdrop-blur-xl bg-white/10 border border-white/20 shadow-2xl rounded-3xl relative z-10 transition-all duration-300 hover:shadow-emerald-500/20 hover:border-white/30">
        <div class="text-center mb-10">
            <div class="mx-auto w-16 h-16 bg-emerald-500/30 rounded-2xl flex items-center justify-center mb-4 border border-emerald-300/30 shadow-inner">
                <svg class="w-8 h-8 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7"></path></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Portal Siswa</h2>
            <p class="mt-2 text-sm text-emerald-200">Sistem Absensi Barcode Terpadu</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-6">
            <div>
                <label for="nisn" class="block text-sm font-medium text-emerald-100 mb-1">NISN Siswa</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-emerald-300 group-focus-within:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <input wire:model="nisn" id="nisn" type="text" required class="block w-full pl-10 pr-3 py-3 border border-emerald-300/30 rounded-xl leading-5 bg-emerald-900/40 text-white placeholder-emerald-300/50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:bg-emerald-900/60 transition-all duration-200 sm:text-sm shadow-inner" placeholder="Masukkan NISN Anda">
                </div>
                @error('nisn') <p class="mt-2 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-emerald-100 mb-1">Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-emerald-300 group-focus-within:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <input wire:model="password" id="password" type="password" required class="block w-full pl-10 pr-3 py-3 border border-emerald-300/30 rounded-xl leading-5 bg-emerald-900/40 text-white placeholder-emerald-300/50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:bg-emerald-900/60 transition-all duration-200 sm:text-sm shadow-inner" placeholder="••••••••">
                </div>
                @error('password') <p class="mt-2 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center">
                    <input wire:model="remember" id="remember" type="checkbox" class="h-4 w-4 text-emerald-500 focus:ring-emerald-400 border-emerald-300/30 rounded bg-emerald-900/40">
                    <label for="remember" class="ml-2 block text-sm text-emerald-200 cursor-pointer">
                        Ingat saya
                    </label>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-emerald-500 transition-all duration-200 transform hover:-translate-y-0.5 relative overflow-hidden group">
                    <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></span>
                    <span class="relative">Masuk ke Dashboard</span>
                </button>
            </div>
        </form>
    </div>
</div>
