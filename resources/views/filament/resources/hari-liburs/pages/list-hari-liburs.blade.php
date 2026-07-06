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

            <div class="mt-4">
                <x-filament::button type="submit" color="primary">
                    Simpan Pengaturan Hari Kerja
                </x-filament::button>
            </div>
        </form>
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
</x-filament-panels::page>
