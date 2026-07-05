<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Radio;
use App\Models\PengaturanSekolah;
use Filament\Notifications\Notification;
use App\Models\HariLibur;
use Illuminate\Support\Carbon;

class SettingLibur extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static string|\BackedEnum|null $navigationGroup = 'Pengaturan Sistem';
    protected static ?string $navigationLabel = 'Setting Libur';
    protected static ?string $title = 'Kalender Libur & Hari Kerja';

    protected string $view = 'filament.pages.setting-libur';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = PengaturanSekolah::current();
        $this->form->fill([
            'work_days_type' => $settings->work_days_type ?? '5_hari',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Radio::make('work_days_type')
                    ->label('Tipe Hari Kerja')
                    ->options([
                        '5_hari' => '5 Hari Sekolah (Senin - Jumat)',
                        '6_hari' => '6 Hari Sekolah (Senin - Sabtu)',
                    ])
                    ->descriptions([
                        '5_hari' => 'Sabtu & Minggu otomatis dihitung sebagai hari libur.',
                        '6_hari' => 'Hanya Minggu yang dihitung sebagai hari libur rutin.',
                    ])
                    ->inline()
                    ->required()
            ])
            ->statePath('data');
    }

    public function saveSettings(): void
    {
        $settings = PengaturanSekolah::current();
        if ($settings) {
            $settings->update([
                'work_days_type' => $this->data['work_days_type'],
            ]);

            Notification::make()
                ->title('Pengaturan hari kerja berhasil disimpan')
                ->success()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        $events = HariLibur::with('kelas')->get()->map(function ($holiday) {
            $color = match ($holiday->type) {
                'nasional' => '#3b82f6', // blue
                'cuti_bersama' => '#10b981', // green
                'khusus' => '#f59e0b', // yellow
                default => '#6b7280',
            };

            $title = $holiday->description;
            if ($holiday->type === 'khusus' && $holiday->kelas) {
                $title .= ' (' . $holiday->kelas->name . ')';
            }

            return [
                'title' => $title,
                'start' => $holiday->start_date->toDateString(),
                'end' => $holiday->end_date ? $holiday->end_date->addDay()->toDateString() : $holiday->start_date->addDay()->toDateString(),
                'color' => $color,
            ];
        })->toArray();

        return [
            'events' => $events,
        ];
    }
}
