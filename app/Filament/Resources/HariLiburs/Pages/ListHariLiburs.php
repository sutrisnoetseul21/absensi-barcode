<?php

namespace App\Filament\Resources\HariLiburs\Pages;

use App\Filament\Resources\HariLiburs\HariLiburResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Models\PengaturanSekolah;
use App\Models\HariLibur;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class ListHariLiburs extends ListRecords
{
    protected static string $resource = HariLiburResource::class;
    protected string $view = 'filament.resources.hari-liburs.pages.list-hari-liburs';

    // Public property untuk Livewire binding langsung
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
            CreateAction::make(),
        ];
    }
}
