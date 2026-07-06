<x-filament-panels::page>
    {{-- Pengaturan Hari Kerja --}}
    <x-filament::card>
        <h3 class="text-base font-semibold mb-4">Pengaturan Hari Kerja</h3>
        <form wire:submit.prevent="saveSettings">
            <div class="space-y-3">
                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition {{ $work_days_type === '5_hari' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : '' }}">
                    <input type="radio" wire:model="work_days_type" value="5_hari" class="mt-1">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">5 Hari Sekolah (Senin - Jumat)</div>
                        <div class="text-sm text-gray-500">Sabtu & Minggu otomatis dihitung sebagai hari libur.</div>
                    </div>
                </label>

                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition {{ $work_days_type === '6_hari' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : '' }}">
                    <input type="radio" wire:model="work_days_type" value="6_hari" class="mt-1">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">6 Hari Sekolah (Senin - Sabtu)</div>
                        <div class="text-sm text-gray-500">Hanya Minggu yang dihitung sebagai hari libur rutin.</div>
                    </div>
                </label>
            </div>

            <div class="mt-4">
                <x-filament::button type="submit">
                    Simpan Pengaturan Hari Kerja
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>

    {{-- Kalender Hari Libur --}}
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">Kalender Hari Libur</h2>
        <x-filament::card>
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
            <div
                x-data="{
                    events: {{ \Illuminate\Support\Js::from($this->getEvents()) }},
                    init() {
                        const calendarEl = document.getElementById('calendar');
                        const calendar = new FullCalendar.Calendar(calendarEl, {
                            plugins: [ FullCalendar.dayGridPlugin, FullCalendar.interactionPlugin ],
                            initialView: 'dayGridMonth',
                            locale: 'id',
                            events: this.events,
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,dayGridWeek'
                            },
                            dateClick: function(info) {
                                window.location.href = '{{ \App\Filament\Resources\HariLiburs\HariLiburResource::getUrl('create') }}?date=' + info.dateStr;
                            }
                        });
                        calendar.render();
                    }
                }"
                wire:ignore
            >
                <div id="calendar"></div>
            </div>
        </x-filament::card>
    </div>

    {{-- Tabel Data Hari Libur --}}
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">Data Hari Libur</h2>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
