<x-filament-panels::page>
    <x-filament::card>
        <form wire:submit="saveSettings">
            {{ $this->getForm('settingsForm') }}

            <div class="mt-4">
                <x-filament::button type="submit">
                    Simpan Pengaturan Hari Kerja
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>

    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">Kalender Hari Libur</h2>
        <x-filament::card>
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
            <div 
                x-data="{
                    events: {{ \Illuminate\Support\Js::from($this->getEvents()) }},
                    init() {
                        if (!window.FullCalendar) {
                            console.error('FullCalendar not loaded in window!');
                            return;
                        }
                        const calendarEl = document.getElementById('calendar');
                        const calendar = new window.FullCalendar.Calendar(calendarEl, {
                            plugins: [ window.FullCalendar.dayGridPlugin, window.FullCalendar.interactionPlugin ],
                            initialView: 'dayGridMonth',
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
                id="calendar-container" 
                wire:ignore
            >
                <div id="calendar"></div>
            </div>
        </x-filament::card>
    </div>

    <!-- Tampilkan Tabel Data Hari Libur (Filament Default) -->
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">Data Hari Libur</h2>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
