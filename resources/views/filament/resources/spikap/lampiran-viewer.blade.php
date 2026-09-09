@php
    $lampirans = $record?->lampiran ?? collect();
@endphp

@if($lampirans->isEmpty())
    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center">
        <svg class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
        </svg>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada berkas bukti foto maupun video yang dilampirkan oleh pelapor.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($lampirans as $lampiran)
            @php
                $isFoto = $lampiran->tipe_file === 'foto';
                $sizeFormatted = $lampiran->ukuran_bytes >= 1048576 
                    ? number_format($lampiran->ukuran_bytes / 1048576, 2) . ' MB'
                    : number_format($lampiran->ukuran_bytes / 1024, 1) . ' KB';
            @endphp
            <div class="flex flex-col justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-lg shrink-0 {{ $isFoto ? 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'bg-purple-50 text-purple-600 dark:bg-purple-950 dark:text-purple-400' }}">
                        @if($isFoto)
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @else
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" title="{{ $lampiran->nama_asli }}">
                            {{ $lampiran->nama_asli }}
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $isFoto ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' }}">
                                {{ ucfirst($lampiran->tipe_file) }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $sizeFormatted }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('spikap.lampiran.show', $lampiran->id) }}" 
                       target="_blank" 
                       class="flex-1 inline-flex justify-center items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950 dark:hover:bg-primary-900/70 dark:text-primary-300 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Pratinjau
                    </a>
                    <a href="{{ route('spikap.lampiran.download', $lampiran->id) }}" 
                       class="inline-flex items-center justify-center p-1.5 text-xs font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition"
                       title="Unduh Berkas">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
