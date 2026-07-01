<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-rose-900 to-slate-900 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-rose-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="absolute top-[20%] right-[-5%] w-96 h-96 bg-red-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    <div class="absolute bottom-[-10%] left-[20%] w-96 h-96 bg-orange-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>

    <div class="w-full max-w-md px-8 py-10 backdrop-blur-xl bg-white/10 border border-white/20 shadow-2xl rounded-3xl relative z-10 transition-all duration-300 hover:shadow-rose-500/20 hover:border-white/30">
        <div class="text-center mb-10">
            <div class="mx-auto w-16 h-16 bg-rose-500/30 rounded-full flex items-center justify-center mb-4 border border-rose-300/30 shadow-inner">
                <svg class="w-8 h-8 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Wajib Ganti Password</h2>
            <p class="mt-2 text-sm text-rose-200">Untuk keamanan, ganti password bawaan Anda.</p>
        </div>

        <form wire:submit.prevent="changePassword" class="space-y-5">
            <div>
                <label for="current_password" class="block text-sm font-medium text-rose-100 mb-1">Password Saat Ini</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-rose-300 group-focus-within:text-rose-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                    </div>
                    <input wire:model="current_password" id="current_password" type="password" required class="block w-full pl-10 pr-3 py-2.5 border border-rose-300/30 rounded-xl leading-5 bg-rose-900/40 text-white placeholder-rose-300/50 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-400 focus:bg-rose-900/60 transition-all duration-200 sm:text-sm shadow-inner" placeholder="••••••••">
                </div>
                @error('current_password') <p class="mt-1 text-xs text-red-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-rose-100 mb-1">Password Baru</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-rose-300 group-focus-within:text-rose-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <input wire:model="new_password" id="new_password" type="password" required class="block w-full pl-10 pr-3 py-2.5 border border-rose-300/30 rounded-xl leading-5 bg-rose-900/40 text-white placeholder-rose-300/50 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-400 focus:bg-rose-900/60 transition-all duration-200 sm:text-sm shadow-inner" placeholder="Min. 8 karakter">
                </div>
                @error('new_password') <p class="mt-1 text-xs text-red-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-rose-100 mb-1">Konfirmasi Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-rose-300 group-focus-within:text-rose-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <input wire:model="new_password_confirmation" id="new_password_confirmation" type="password" required class="block w-full pl-10 pr-3 py-2.5 border border-rose-300/30 rounded-xl leading-5 bg-rose-900/40 text-white placeholder-rose-300/50 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-400 focus:bg-rose-900/60 transition-all duration-200 sm:text-sm shadow-inner" placeholder="Ulangi password baru">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-medium text-white bg-rose-600 hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-rose-500 transition-all duration-200 transform hover:-translate-y-0.5 relative overflow-hidden group">
                    <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></span>
                    <span class="relative">Simpan Password</span>
                </button>
            </div>
        </form>
    </div>
</div>
