<div class="space-y-4">
    @if($diff['total_changes'] === 0)
        <div class="text-gray-500 italic">
            Semua data jadwal sudah mutakhir. Tidak ada data baru atau perubahan yang terdeteksi dari ZenCBT.
        </div>
    @else
        <div class="text-sm">
            Ditemukan <strong>{{ $diff['total_changes'] }}</strong> perubahan ({{ count($diff['new']) }} Jadwal Baru, {{ count($diff['updated']) }} Diperbarui).
            Lanjutkan sinkronisasi untuk menyimpan data ini ke database.
        </div>

        @if(count($diff['new']) > 0)
            <div>
                <h4 class="font-bold text-success-600 mb-1 flex items-center gap-1">
                    <x-heroicon-o-plus-circle class="w-5 h-5"/>
                    Jadwal Baru
                </h4>
                <ul class="list-disc list-inside text-sm text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    @foreach($diff['new'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(count($diff['updated']) > 0)
            <div>
                <h4 class="font-bold text-warning-600 mb-1 flex items-center gap-1">
                    <x-heroicon-o-pencil-square class="w-5 h-5"/>
                    Jadwal Diperbarui
                </h4>
                <ul class="list-disc list-inside text-sm text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    @foreach($diff['updated'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</div>
