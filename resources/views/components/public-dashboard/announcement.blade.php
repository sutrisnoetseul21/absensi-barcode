@props(['pengumuman'])

@if($pengumuman->count() > 0)
<div class="bg-amber-50 border-b border-amber-200 shadow-sm relative overflow-hidden z-20">
    <div class="max-w-7xl mx-auto flex items-center">
        <div class="bg-amber-500 text-white px-4 py-3 font-bold flex items-center z-10 shadow-[4px_0_10px_rgba(0,0,0,0.1)] whitespace-nowrap">
            <svg class="w-5 h-5 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            PENGUMUMAN
        </div>
        <div class="overflow-hidden flex-1 py-3 px-4">
            <div class="whitespace-nowrap inline-block animate-[shimmer_25s_linear_infinite]">
                @foreach($pengumuman as $p)
                    <span class="text-amber-800 font-medium mx-4">
                        @if($p->tipe === 'peringatan') 🔴
                        @elseif($p->tipe === 'penting') 🟡
                        @else 🔵
                        @endif
                        {{ $p->judul }} &mdash; {{ $p->isi }}
                    </span>
                    @if(!$loop->last) <span class="text-amber-300 mx-2">|</span> @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
