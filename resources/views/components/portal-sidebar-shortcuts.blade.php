@if($hasMultiplePortals)
    <div class="pt-4 mt-4 border-t border-slate-100">
        <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 truncate" x-show="!isCollapsed" x-transition.opacity>Pintasan Portal Terkait</p>

        <a href="{{ url('/pilih-portal') }}" :title="isCollapsed ? 'Pilih Portal ERP' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 text-xs font-semibold transition-all">
            <div class="p-1 rounded-lg bg-slate-100 text-slate-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></div>
            <span x-show="!isCollapsed" x-transition.opacity>Pilih Portal ERP</span>
        </a>

        @if(!$isWebRoute && $canAccessWeb)
            <a href="{{ route('portal-web.dashboard') }}" :title="isCollapsed ? 'Portal Web' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-violet-50 hover:text-violet-700 text-xs font-semibold transition-all">
                <div class="p-1 rounded-lg bg-violet-100 text-violet-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg></div>
                <span x-show="!isCollapsed" x-transition.opacity>Portal Web</span>
            </a>
        @endif

        @if(!$isPresensiRoute && $canAccessPresensi)
            <a href="{{ route('portal-presensi.dashboard') }}" :title="isCollapsed ? 'Portal Presensi' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-amber-50 hover:text-amber-700 text-xs font-semibold transition-all">
                <div class="p-1 rounded-lg bg-amber-100 text-amber-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                <span x-show="!isCollapsed" x-transition.opacity>Portal Presensi</span>
            </a>
        @endif

        @if(!$isPerpusRoute && $canAccessPerpus)
            <a href="{{ route('portal-perpustakaan.dashboard') }}" :title="isCollapsed ? 'Portal Perpustakaan' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 text-xs font-semibold transition-all">
                <div class="p-1 rounded-lg bg-cyan-100 text-cyan-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg></div>
                <span x-show="!isCollapsed" x-transition.opacity>Portal Perpustakaan</span>
            </a>
        @endif

        @if(!request()->is('portal-guru*') && $canAccessGuru)
            <a href="{{ route('portal-guru.dashboard') }}" :title="isCollapsed ? 'Portal Guru' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 text-xs font-semibold transition-all">
                <div class="p-1 rounded-lg bg-indigo-100 text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div>
                <span x-show="!isCollapsed" x-transition.opacity>Portal Guru</span>
            </a>
        @endif

        @if($isSuper)
            <a href="{{ url('/admin') }}" :title="isCollapsed ? 'Panel Admin' : ''" class="flex items-center gap-3 py-2 px-3 rounded-xl text-slate-600 hover:bg-purple-50 hover:text-purple-700 text-xs font-semibold transition-all">
                <div class="p-1 rounded-lg bg-purple-100 text-purple-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg></div>
                <span x-show="!isCollapsed" x-transition.opacity>Panel Admin</span>
            </a>
        @endif
    </div>
@endif

<div class="mt-4 pt-4 border-t border-slate-100">
    <a href="{{ url('/') }}" target="_blank" :title="isCollapsed ? 'Lihat Web Publik' : ''"
       class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm text-slate-500 hover:text-violet-600 hover:bg-violet-50 font-medium transition-all">
        <svg class="w-5 h-5 min-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        <span x-show="!isCollapsed" x-transition.opacity>Lihat Web Publik</span>
    </a>
</div>
