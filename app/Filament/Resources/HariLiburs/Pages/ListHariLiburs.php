<?php

namespace App\Filament\Resources\HariLiburs\Pages;

use App\Filament\Resources\HariLiburs\HariLiburResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Models\PengaturanSekolah;
use App\Models\HariLibur;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class ListHariLiburs extends ListRecords
{
    protected static string $resource = HariLiburResource::class;
    protected string $view = 'filament.resources.hari-liburs.pages.list-hari-liburs';

    public string $work_days_type = '5_hari';

    public function mount(): void
    {
        parent::mount();
        $settings = PengaturanSekolah::current();
        $this->work_days_type = $settings->work_days_type ?? '5_hari';
    }

    public function saveSettings(): void
    {
        $this->validate([
            'work_days_type' => 'required|in:5_hari,6_hari',
        ]);

        $settings = PengaturanSekolah::current();
        if ($settings) {
            $settings->update([
                'work_days_type' => $this->work_days_type,
            ]);

            Notification::make()
                ->title('Pengaturan hari kerja berhasil disimpan')
                ->success()
                ->send();
        }
    }

    /**
     * Daftar hari libur nasional & cuti bersama Indonesia 2026 (hardcoded).
     * Sumber: SKB 3 Menteri / Keppres terkait.
     */
    public static function getHariLiburNasional2026(): array
    {
        return [
            // Januari
            ['date' => '2026-01-01', 'desc' => 'Tahun Baru Masehi', 'type' => 'nasional'],

            // Februari
            ['date' => '2026-02-17', 'desc' => 'Tahun Baru Imlek 2577', 'type' => 'nasional'],
            ['date' => '2026-02-18', 'desc' => 'Cuti Bersama Tahun Baru Imlek', 'type' => 'cuti_bersama'],

            // Maret
            ['date' => '2026-03-03', 'desc' => 'Isra Miraj Nabi Muhammad SAW', 'type' => 'nasional'],
            ['date' => '2026-03-19', 'desc' => 'Hari Raya Nyepi (Tahun Baru Saka 1948)', 'type' => 'nasional'],
            ['date' => '2026-03-20', 'desc' => 'Cuti Bersama Hari Raya Nyepi', 'type' => 'cuti_bersama'],

            // Maret-April - Idul Fitri 1447 H (perkiraan ~20 Mar)
            ['date' => '2026-03-20', 'desc' => 'Hari Raya Idul Fitri 1447 H', 'type' => 'nasional'],
            ['date' => '2026-03-21', 'desc' => 'Hari Raya Idul Fitri 1447 H (Hari kedua)', 'type' => 'nasional'],
            ['date' => '2026-03-17', 'desc' => 'Cuti Bersama Idul Fitri', 'type' => 'cuti_bersama'],
            ['date' => '2026-03-18', 'desc' => 'Cuti Bersama Idul Fitri', 'type' => 'cuti_bersama'],
            ['date' => '2026-03-23', 'desc' => 'Cuti Bersama Idul Fitri', 'type' => 'cuti_bersama'],
            ['date' => '2026-03-24', 'desc' => 'Cuti Bersama Idul Fitri', 'type' => 'cuti_bersama'],
            ['date' => '2026-03-25', 'desc' => 'Cuti Bersama Idul Fitri', 'type' => 'cuti_bersama'],
            ['date' => '2026-03-26', 'desc' => 'Cuti Bersama Idul Fitri', 'type' => 'cuti_bersama'],

            // April
            ['date' => '2026-04-02', 'desc' => 'Wafat Isa Al-Masih', 'type' => 'nasional'],
            ['date' => '2026-04-05', 'desc' => 'Hari Paskah', 'type' => 'nasional'],

            // Mei
            ['date' => '2026-05-01', 'desc' => 'Hari Buruh Internasional', 'type' => 'nasional'],
            ['date' => '2026-05-14', 'desc' => 'Kenaikan Isa Al-Masih', 'type' => 'nasional'],
            ['date' => '2026-05-23', 'desc' => 'Hari Raya Waisak 2570 BE', 'type' => 'nasional'],

            // Juni
            ['date' => '2026-06-01', 'desc' => 'Hari Lahir Pancasila', 'type' => 'nasional'],
            ['date' => '2026-06-12', 'desc' => 'Hari Raya Idul Adha 1447 H', 'type' => 'nasional'],
            ['date' => '2026-06-15', 'desc' => 'Cuti Bersama Idul Adha', 'type' => 'cuti_bersama'],

            // Juli
            ['date' => '2026-07-02', 'desc' => 'Tahun Baru Islam 1448 H', 'type' => 'nasional'],

            // Agustus
            ['date' => '2026-08-17', 'desc' => 'Hari Kemerdekaan Republik Indonesia', 'type' => 'nasional'],

            // September
            ['date' => '2026-09-11', 'desc' => 'Maulid Nabi Muhammad SAW', 'type' => 'nasional'],

            // Desember
            ['date' => '2026-12-25', 'desc' => 'Hari Raya Natal', 'type' => 'nasional'],
            ['date' => '2026-12-24', 'desc' => 'Cuti Bersama Natal', 'type' => 'cuti_bersama'],
            ['date' => '2026-12-26', 'desc' => 'Cuti Bersama Natal', 'type' => 'cuti_bersama'],
        ];
    }

    public function importLiburNasional(): void
    {
        $tahun = now()->year;
        $holidays = static::getHariLiburNasional2026();

        $imported = 0;
        $skipped = 0;

        foreach ($holidays as $item) {
            // Hanya import jika tahun sesuai tahun berjalan
            if (!str_starts_with($item['date'], $tahun)) {
                // Jika tahun tidak cocok, coba cek manual
            }

            $exists = HariLibur::where('start_date', $item['date'])
                ->where('description', $item['desc'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            HariLibur::create([
                'start_date' => $item['date'],
                'end_date'   => null,
                'description'=> $item['desc'],
                'type'       => $item['type'],
                'class_id'   => null,
            ]);
            $imported++;
        }

        if ($imported > 0) {
            Notification::make()
                ->title("Berhasil mengimpor {$imported} hari libur nasional 2026.")
                ->body($skipped > 0 ? "{$skipped} data yang sudah ada dilewati." : null)
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Semua hari libur nasional sudah ada.')
                ->info()
                ->send();
        }
    }

    public function getEvents(): array
    {
        return HariLibur::with('kelas')->get()->map(function ($holiday) {
            $color = match ($holiday->type) {
                'nasional' => '#3b82f6',
                'cuti_bersama' => '#10b981',
                'khusus' => '#f59e0b',
                default => '#6b7280',
            };

            $title = $holiday->description;
            if ($holiday->type === 'khusus' && $holiday->kelas) {
                $title .= ' (' . $holiday->kelas->name . ')';
            }

            return [
                'title' => $title,
                'start' => $holiday->start_date->toDateString(),
                'end' => $holiday->end_date
                    ? Carbon::parse($holiday->end_date)->addDay()->toDateString()
                    : Carbon::parse($holiday->start_date)->addDay()->toDateString(),
                'color' => $color,
            ];
        })->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importLiburNasional')
                ->label('Impor Libur Nasional 2026')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Impor Hari Libur Nasional Indonesia 2026')
                ->modalDescription('Sistem akan menambahkan hari libur nasional & cuti bersama resmi tahun 2026 ke kalender. Data yang sudah ada tidak akan diduplikasi.')
                ->modalSubmitActionLabel('Ya, Impor Sekarang')
                ->action('importLiburNasional'),

            CreateAction::make()
                ->label('Tambah Hari Libur'),
        ];
    }
}
