<!-- Filters Card -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-4 sm:p-6 mb-8 flex flex-col md:flex-row gap-5 items-end relative z-20">
    <div class="w-full md:w-1/3">
        <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Tahun Ajaran
        </label>
        <div class="relative">
            <select wire:model.live="selectedAcademicYearId" class="block w-full pl-4 pr-10 py-3 text-slate-800 border-slate-200/60 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 sm:text-sm rounded-xl bg-white/50 backdrop-blur-sm font-semibold cursor-pointer appearance-none transition-all duration-200 hover:bg-white shadow-sm">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
    
    @if(count($classes) > 0)
    <div class="w-full md:w-1/3">
        <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Pilih Kelas
        </label>
        <div class="relative">
            <select wire:model.live="selectedClassId" class="block w-full pl-4 pr-10 py-3 text-slate-800 border-slate-200/60 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 sm:text-sm rounded-xl bg-white/50 backdrop-blur-sm font-semibold cursor-pointer appearance-none transition-all duration-200 hover:bg-white shadow-sm">
                @foreach($classes as $kelas)
                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
    @endif
    
    <div class="w-full md:w-1/3">
        <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Bulan Kehadiran
        </label>
        <div class="relative">
            <select wire:model.live="selectedMonthYear" class="block w-full pl-4 pr-10 py-3 text-slate-800 border-slate-200/60 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 sm:text-sm rounded-xl bg-white/50 backdrop-blur-sm font-semibold cursor-pointer appearance-none transition-all duration-200 hover:bg-white shadow-sm">
                @foreach($availableMonths as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
</div>
