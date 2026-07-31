@php
    $settings = \Illuminate\Support\Facades\Cache::remember('public_pengaturan_sekolah', 3600, function () {
        return \App\Models\PengaturanSekolah::first();
    });
@endphp

@if($settings && $settings->global_announcement_active && !empty(trim(strip_tags($settings->global_announcement))))
    <div class="w-full bg-blue-600 text-white shadow-md relative z-50" id="global-announcement-banner">
        <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center">
                    <span class="flex p-2 rounded-lg bg-blue-800 shrink-0">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                        </svg>
                    </span>
                    <div class="ml-3 font-medium text-white max-w-full overflow-hidden prose prose-sm prose-invert prose-p:my-0 prose-a:text-blue-200 hover:prose-a:text-white">
                        {!! $settings->global_announcement !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
