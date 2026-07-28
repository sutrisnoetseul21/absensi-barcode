<div>
    @if($pinjaman->isEmpty())
        <div class="text-center p-4">
            <p class="text-gray-500 dark:text-gray-400">Tidak ada pinjaman aktif untuk anggota ini.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($pinjaman as $item)
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-bold text-lg text-gray-900 dark:text-white">
                                {{ $item->eksemplar->buku->judul ?? 'Buku Tidak Diketahui' }}
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Kode Eksemplar: <span class="font-mono bg-gray-200 dark:bg-gray-700 px-1 rounded">{{ $item->eksemplar->kode_eksemplar }}</span>
                            </p>
                        </div>
                        <div>
                            @if($item->status === 'terlambat' || \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->startOfDay()->isPast())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300">
                                    Terlambat
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300">
                                    Dipinjam
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                        <div>
                            <span class="block text-gray-500 dark:text-gray-400">Tanggal Pinjam</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($item->tanggal_pinjam)->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 dark:text-gray-400">Jatuh Tempo</span>
                            <span class="font-medium {{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->startOfDay()->isPast() ? 'text-danger-600 dark:text-danger-400' : '' }}">
                                {{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
