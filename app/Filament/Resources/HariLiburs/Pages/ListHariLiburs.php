<?php

namespace App\Filament\Resources\HariLiburs\Pages;

use App\Filament\Resources\HariLiburs\HariLiburResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Form;
use Filament\Forms\Components\Radio;
use App\Models\PengaturanSekolah;
use App\Models\HariLibur;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class ListHariLiburs extends ListRecords
{
    protected static string $resource = HariLiburResource::class;
    protected string $view = 'filament.resources.hari-liburs.pages.list-hari-liburs';

    public ?array $settingData = [];

    public function mount(): void
    {
        parent::mount();
        $settings = PengaturanSekolah::current();
        $this->getForm('settingsForm')->fill([
            'work_days_type' => $settings->work_days_type ?? '5_hari',
        ]);
    }

    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            'settingsForm' => $this->settingsForm($this->makeForm()),
        ]);
    }

    public function settingsForm(Form $form): Form
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
            ->statePath('settingData');
    }

    public function saveSettings(): void
    {
        $settings = PengaturanSekolah::current();
        if ($settings) {
            $settings->update([
                'work_days_type' => $this->settingData['work_days_type'],
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
                'end' => $holiday->end_date ? Carbon::parse($holiday->end_date)->addDay()->toDateString() : Carbon::parse($holiday->start_date)->addDay()->toDateString(),
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
