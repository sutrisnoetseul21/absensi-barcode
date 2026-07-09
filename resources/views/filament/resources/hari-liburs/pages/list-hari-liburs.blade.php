<x-filament-panels::page>
    {{-- Pengaturan Hari Kerja --}}
    <x-filament::card>
        <h3 class="text-base font-semibold mb-4">Pengaturan Hari Kerja</h3>
        <form wire:submit.prevent="saveSettings">
            <div class="space-y-3">
                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border hover:bg-gray-50 dark:hover:bg-gray-800 transition
                    {{ $work_days_type === '5_hari' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                    <input type="radio" wire:model.live="work_days_type" value="5_hari" class="mt-1">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">5 Hari Sekolah (Senin - Jumat)</div>
                        <div class="text-sm text-gray-500">Sabtu & Minggu otomatis dihitung sebagai hari libur.</div>
                    </div>
                </label>

                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border hover:bg-gray-50 dark:hover:bg-gray-800 transition
                    {{ $work_days_type === '6_hari' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                    <input type="radio" wire:model.live="work_days_type" value="6_hari" class="mt-1">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">6 Hari Sekolah (Senin - Sabtu)</div>
                        <div class="text-sm text-gray-500">Hanya Minggu yang dihitung sebagai hari libur rutin.</div>
                    </div>
                </label>
            </div>

            {{-- Tanggal Mulai Berlaku --}}
            <div class="mt-4 max-w-xs">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tanggal Mulai Berlaku
                </label>
                <input type="date" wire:model="effective_date" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                <p class="text-xs text-gray-500 mt-1">
                    Perubahan hanya akan berlaku mulai dari tanggal yang dipilih. Periode sebelum tanggal ini akan tetap menggunakan tipe hari kerja sebelumnya.
                </p>
            </div>

            <div class="mt-4">
                <x-filament::button type="submit" color="primary">
                    Simpan Pengaturan Hari Kerja
                </x-filament::button>
            </div>
        </form>

        {{-- Tampilan Riwayat --}}
        @if(count($work_days_history) > 0)
            <div class="mt-6 border-t pt-4 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Riwayat Pengaturan Hari Kerja</h4>
                <div class="space-y-1">
                    @foreach($work_days_history as $hist)
                        <div class="text-xs text-gray-600 dark:text-gray-400 flex justify-between max-w-md">
                            <span>
                                📅 {{ \Carbon\Carbon::parse($hist['start_date'])->translatedFormat('d M Y') }} 
                                s/d 
                                {{ $hist['end_date'] ? \Carbon\Carbon::parse($hist['end_date'])->translatedFormat('d M Y') : 'Sekarang' }}
                            </span>
                            <span class="font-semibold text-primary-600 dark:text-primary-400">
                                {{ $hist['work_days_type'] === '5_hari' ? '5 Hari Sekolah' : '6 Hari Sekolah' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-filament::card>

    {{-- Tabel Data Hari Libur --}}
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">Data Hari Libur</h2>
        {{ $this->table }}
    </div>

    {{-- Kalender Hari Libur --}}
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">Kalender Hari Libur</h2>
        <div class="text-sm text-gray-500 mb-3">
            Klik tanggal pada kalender untuk menambah hari libur baru.
            <span class="inline-flex items-center gap-1 ml-2"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Nasional</span>
            <span class="inline-flex items-center gap-1 ml-2"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Cuti Bersama</span>
            <span class="inline-flex items-center gap-1 ml-2"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> Khusus Kelas</span>
        </div>
        <x-filament::card>
            <div id="filament-calendar-wrapper" wire:ignore style="min-height: 420px;">
                <div id="filament-calendar"></div>
            </div>
        </x-filament::card>
    </div>

    {{-- FullCalendar: load script secara inline, tidak menggunakan @push --}}
    <div id="fullcalendar-init" wire:ignore>
        <script>
            (function() {
                var EVENTS = @json($this->getEvents());
                var CREATE_URL = '{{ \App\Filament\Resources\HariLiburs\HariLiburResource::getUrl('create') }}';

                function initCalendar() {
                    if (typeof FullCalendar === 'undefined') return;
                    var el = document.getElementById('filament-calendar');
                    if (!el || el.dataset.initialized) return;
                    el.dataset.initialized = '1';

                    var calendar = new FullCalendar.Calendar(el, {
                        initialView: 'dayGridMonth',
                        locale: 'id',
                        events: EVENTS,
                        height: 'auto',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,dayGridWeek'
                        },
                        dateClick: function(info) {
                            window.location.href = CREATE_URL + '?date=' + info.dateStr;
                        }
                    });
                    calendar.render();
                }

                // Jika FullCalendar sudah ada, langsung init
                if (typeof FullCalendar !== 'undefined') {
                    initCalendar();
                } else {
                    // Muat script FullCalendar secara dinamis
                    var script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js';
                    script.onload = initCalendar;
                    document.head.appendChild(script);
                }
            })();
        </script>
    </div>

    {{-- Log Aktivitas Hari Libur & Hari Kerja --}}
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">Log Aktivitas & Riwayat Penghapusan</h2>
        <x-filament::card>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm text-gray-500 dark:text-gray-400">
                    <thead>
                        <tr class="border-b dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold bg-gray-50 dark:bg-gray-800/50">
                            <th class="py-3 px-4">Tanggal & Waktu</th>
                            <th class="py-3 px-4">Aktor</th>
                            <th class="py-3 px-4">Aktivitas</th>
                            <th class="py-3 px-4">Detail Perubahan / Data Libur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->getHolidayLogs() as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                                <td class="py-3 px-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">
                                    {{ $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }}
                                </td>
                                <td class="py-3 px-4">
                                    {{ $log->causer?->name ?? 'System' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                        {{ $log->description === 'Menghapus hari libur' || $log->description === 'Menghapus hari libur (Bulk)' ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' }}">
                                        {{ $log->description }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs font-mono">
                                    @if($log->description === 'Menghapus hari libur' || $log->description === 'Menghapus hari libur (Bulk)')
                                        <div>Keterangan: {{ $log->properties['description'] ?? '-' }}</div>
                                        <div>Mulai: {{ $log->properties['start_date'] ?? '-' }}</div>
                                        @if(!empty($log->properties['end_date']))
                                            <div>Selesai: {{ $log->properties['end_date'] }}</div>
                                        @endif
                                        <div>Tipe: {{ ucfirst($log->properties['type'] ?? '-') }}</div>
                                    @elseif($log->description === 'Mengubah pengaturan hari kerja sekolah')
                                        <div>Tipe Baru: {{ $log->properties['new_work_days_type'] === '5_hari' ? '5 Hari Sekolah' : '6 Hari Sekolah' }}</div>
                                        <div>Mulai Berlaku: {{ $log->properties['effective_date'] ?? '-' }}</div>
                                    @else
                                        {{ json_encode($log->properties) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-400">
                                    Belum ada log aktivitas yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
