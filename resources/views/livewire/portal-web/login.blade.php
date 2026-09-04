<div>
    @if($errorMessage)
        <div class="mb-5 px-4 py-3 bg-red-500/20 border border-red-400/30 rounded-xl text-red-200 text-sm">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-5">
        <!-- Email -->
        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Email</label>
            <input type="email" wire:model="email" id="portal-web-email"
                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition-all text-sm"
                placeholder="email@sekolah.sch.id" autocomplete="email">
            @error('email') <p class="text-red-300 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Password</label>
            <input type="password" wire:model="password" id="portal-web-password"
                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition-all text-sm"
                placeholder="••••••••" autocomplete="current-password">
            @error('password') <p class="text-red-300 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Remember -->
        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model="remember" id="remember-web" class="w-4 h-4 rounded border-white/30 bg-white/10 text-violet-500 focus:ring-violet-400">
            <label for="remember-web" class="text-sm text-slate-300">Ingat saya</label>
        </div>

        <!-- Submit -->
        <button type="submit" id="portal-web-login-btn"
            class="w-full py-3.5 px-6 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg hover:shadow-violet-500/25 transition-all duration-200 text-sm tracking-wide flex items-center justify-center gap-2"
            wire:loading.attr="disabled">
            <span wire:loading.remove>
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Masuk ke Portal Web
            </span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memeriksa akses...
            </span>
        </button>
    </form>

    <div class="mt-6 pt-5 border-t border-white/10 text-center">
        <a href="{{ url('/') }}" class="text-slate-400 hover:text-slate-200 text-xs transition-colors">
            &larr; Kembali ke Beranda
        </a>
    </div>
</div>
