<!-- Filters Card -->
<div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-4 sm:p-6 mb-8 flex flex-col md:flex-row gap-4 items-end">
    <div class="w-full md:w-1/3">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tahun Ajaran</label>
        <div class="relative">
            <select wire:model.live="selectedAcademicYearId" class="block w-full pl-4 pr-10 py-2.5 text-slate-800 border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm rounded-xl bg-slate-50 font-semibold cursor-pointer appearance-none transition-all">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
    
    @if(count($classes) > 0)
    <div class="w-full md:w-1/3">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Kelas</label>
        <div class="relative">
            <select wire:model.live="selectedClassId" class="block w-full pl-4 pr-10 py-2.5 text-slate-800 border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm rounded-xl bg-slate-50 font-semibold cursor-pointer appearance-none transition-all">
                @foreach($classes as $kelas)
                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
    @endif
    
    <div class="w-full md:w-1/3">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Bulan Kehadiran</label>
        <div class="relative">
            <select wire:model.live="selectedMonthYear" class="block w-full pl-4 pr-10 py-2.5 text-slate-800 border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm rounded-xl bg-slate-50 font-semibold cursor-pointer appearance-none transition-all">
                @foreach($availableMonths as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
</div>
