<!-- SEKSI 3: Ringkasan Statistik -->
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl shadow-lg shadow-emerald-500/20 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-xl group relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white mb-3 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
        <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider">Hadir</p>
        <p class="text-3xl font-black text-white mt-1">{{ $monthlyStats['H'] ?? 0 }}</p>
    </div>
    
    <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-3xl shadow-lg shadow-amber-500/20 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-xl group relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white mb-3 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        <p class="text-xs font-bold text-amber-100 uppercase tracking-wider">Telat</p>
        <p class="text-3xl font-black text-white mt-1">{{ $monthlyStats['T'] ?? 0 }}</p>
    </div>

    <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-3xl shadow-lg shadow-blue-500/20 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-xl group relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white mb-3 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        <p class="text-xs font-bold text-blue-100 uppercase tracking-wider">Izin</p>
        <p class="text-3xl font-black text-white mt-1">{{ $monthlyStats['I'] ?? 0 }}</p>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-3xl shadow-lg shadow-purple-500/20 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-xl group relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white mb-3 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg></div>
        <p class="text-xs font-bold text-purple-100 uppercase tracking-wider">Sakit</p>
        <p class="text-3xl font-black text-white mt-1">{{ $monthlyStats['S'] ?? 0 }}</p>
    </div>

    <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-3xl shadow-lg shadow-rose-500/20 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-xl group relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white mb-3 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
        <p class="text-xs font-bold text-rose-100 uppercase tracking-wider">Alpa</p>
        <p class="text-3xl font-black text-white mt-1">{{ $monthlyStats['A'] ?? 0 }}</p>
    </div>

    <div class="bg-gradient-to-br from-slate-600 to-slate-700 rounded-3xl shadow-lg shadow-slate-500/20 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-xl group relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white mb-3 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        <p class="text-xs font-bold text-slate-300 uppercase tracking-wider text-center">Total Telat</p>
        <div class="flex items-baseline mt-1 text-white">
            <p class="text-3xl font-black">{{ $monthlyStats['late_minutes'] ?? 0 }}</p>
            <span class="text-sm font-semibold ml-1 text-slate-300">mnt</span>
        </div>
    </div>
</div>
