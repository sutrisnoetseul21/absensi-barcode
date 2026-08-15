@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-3 px-4 bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-sm transition-all duration-300">
        
        <!-- Information Summary Pill -->
        <div class="text-xs font-medium text-slate-600 flex items-center gap-1.5 bg-slate-100/80 px-3.5 py-1.5 rounded-full border border-slate-200/50">
            <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span>Menampilkan</span>
            <span class="font-bold text-slate-900">{{ number_format($paginator->firstItem() ?? 0) }}</span>
            <span>-</span>
            <span class="font-bold text-slate-900">{{ number_format($paginator->lastItem() ?? 0) }}</span>
            <span>dari</span>
            <span class="font-bold text-indigo-600">{{ number_format($paginator->total()) }}</span>
            <span>buku</span>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center gap-1.5 flex-wrap justify-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center min-w-[36px] h-9 px-2.5 rounded-xl text-slate-300 bg-slate-50 cursor-not-allowed text-xs font-semibold select-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <button type="button" 
                        wire:click="previousPage('{{ $paginator->getPageName() }}')" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center min-w-[36px] h-9 px-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 border border-slate-200/60 text-xs font-semibold transition-all duration-200 active:scale-95 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-9 h-9 text-slate-400 text-xs font-medium select-none">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold text-xs shadow-md shadow-indigo-500/25 select-none transform transition-transform scale-105">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" 
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-xl text-slate-600 bg-slate-100/70 hover:bg-indigo-50 hover:text-indigo-600 border border-slate-200/50 text-xs font-medium transition-all duration-200 active:scale-95">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button" 
                        wire:click="nextPage('{{ $paginator->getPageName() }}')" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center min-w-[36px] h-9 px-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 border border-slate-200/60 text-xs font-semibold transition-all duration-200 active:scale-95 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <span class="inline-flex items-center justify-center min-w-[36px] h-9 px-2.5 rounded-xl text-slate-300 bg-slate-50 cursor-not-allowed text-xs font-semibold select-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
