<!-- SEKSI 3: Ringkasan Statistik -->
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
        <div class="w-10 h-10 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 mb-2 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
        <p class="text-xs font-semibold text-slate-500 uppercase">Hadir</p>
        <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['H'] ?? 0 }}</p>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm border border-yellow-100 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
        <div class="w-10 h-10 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-500 mb-2 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        <p class="text-xs font-semibold text-slate-500 uppercase">Telat</p>
        <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['T'] ?? 0 }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
        <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 mb-2 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        <p class="text-xs font-semibold text-slate-500 uppercase">Izin</p>
        <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['I'] ?? 0 }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
        <div class="w-10 h-10 bg-purple-50 rounded-full flex items-center justify-center text-purple-500 mb-2 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg></div>
        <p class="text-xs font-semibold text-slate-500 uppercase">Sakit</p>
        <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['S'] ?? 0 }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
        <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center text-red-500 mb-2 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
        <p class="text-xs font-semibold text-slate-500 uppercase">Alpa</p>
        <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['A'] ?? 0 }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
        <div class="w-10 h-10 bg-orange-50 rounded-full flex items-center justify-center text-orange-500 mb-2 group-hover:scale-110 transition-transform"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        <p class="text-xs font-semibold text-slate-500 uppercase">Total Telat</p>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-extrabold text-slate-800">{{ $monthlyStats['late_minutes'] ?? 0 }}</p>
            <span class="text-sm font-semibold text-slate-500 ml-1">mnt</span>
        </div>
    </div>
</div>
