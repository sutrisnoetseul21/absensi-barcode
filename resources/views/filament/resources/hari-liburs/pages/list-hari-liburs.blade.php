<x-filament-panels::page>
    {{-- Pengaturan Hari Kerja menggunakan Filament card + Livewire binding --}}
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
            {{-- Script dimuat terlebih dahulu, lalu kalender diinisialisasi setelah script selesai --}}
            <div id="calendar-wrapper" wire:ignore>
                <div id="calendar" style="min-height: 400px;"></div>
            </div>
        </x-filament::card>
    </div>

    {{-- Tabel Data Hari Libur --}}
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">Data Hari Libur</h2>
        {{ $this->table }}
    </div>
</x-filament-panels::page>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var events = @json($this->getEvents());
        var createUrl = '{{ \App\Filament\Resources\HariLiburs\HariLiburResource::getUrl('create') }}';

        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ FullCalendar.dayGridPlugin, FullCalendar.interactionPlugin ],
            initialView: 'dayGridMonth',
            locale: 'id',
            events: events,
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            dateClick: function(info) {
                window.location.href = createUrl + '?date=' + info.dateStr;
            }
        });
        calendar.render();
    });
</script>
@endpush
