<?php

namespace App\Filament\Pages;

use App\Models\PengaturanSekolah;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Cache;

class ThemeSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected string $view = 'filament.pages.school-settings'; // Reusing the same view logic which typically just renders the form

    protected static ?string $navigationLabel = 'Pengaturan Tema';

    protected static ?string $title = 'Pengaturan Tema Website';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Sistem';

    public ?array $data = [];

    public ?string $activePreset = null;

    /**
     * Hanya Super Admin yang bisa mengakses halaman pengaturan.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $settings = PengaturanSekolah::current();

        if ($settings) {
            $this->form->fill($settings->toArray());
            $this->detectActivePreset($settings->theme_primary);
        } else {
            $this->form->fill();
            $this->activePreset = 'default';
        }
    }

    public function detectActivePreset(?string $primaryColor): void
    {
        $this->activePreset = match ($primaryColor) {
            '#0284c7' => 'ocean',
            '#059669' => 'emerald',
            '#e11d48' => 'rose',
            '#d97706' => 'amber',
            '#374151' => 'monochrome',
            null => 'default',
            default => null,
        };
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('Pengaturan Tema')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Preset Cepat')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('preset_info')
                                    ->hiddenLabel()
                                    ->content('Pilih salah satu preset tema siap pakai di bawah ini. Mengeklik preset akan otomatis mengisi palet manual dengan kombinasi warna yang harmonis.'),
                                    
                                \Filament\Schemas\Components\Actions::make([
                                    \Filament\Actions\Action::make('presetOcean')
                                        ->label('Ocean Blue')
                                        ->color('info')
                                        ->icon(fn ($livewire) => $livewire->activePreset === 'ocean' ? 'heroicon-m-check-circle' : 'heroicon-o-cloud')
                                        ->badge(fn ($livewire) => $livewire->activePreset === 'ocean' ? '✓' : null)
                                        ->badgeColor('success')
                                        ->action(function ($set, $livewire) {
                                            $livewire->activePreset = 'ocean';
                                            $set('theme_primary', '#0284c7'); // sky-600
                                            $set('theme_secondary', '#0369a1'); // sky-700
                                            $set('theme_accent', '#10b981');
                                            $set('theme_warning', '#f59e0b');
                                            $set('theme_danger', '#ef4444');
                                            $set('theme_info', '#0ea5e9');

                                            Notification::make()
                                                ->title('Preset "Ocean Blue" Dipilih')
                                                ->body('Kombinasi warna telah diisikan ke form. Klik tombol "Simpan Pengaturan" di bawah untuk menerapkan.')
                                                ->info()
                                                ->send();
                                        }),
                                        
                                    \Filament\Actions\Action::make('presetEmerald')
                                        ->label('Emerald Green')
                                        ->color('success')
                                        ->icon(fn ($livewire) => $livewire->activePreset === 'emerald' ? 'heroicon-m-check-circle' : 'heroicon-o-sparkles')
                                        ->badge(fn ($livewire) => $livewire->activePreset === 'emerald' ? '✓' : null)
                                        ->badgeColor('success')
                                        ->action(function ($set, $livewire) {
                                            $livewire->activePreset = 'emerald';
                                            $set('theme_primary', '#059669'); // emerald-600
                                            $set('theme_secondary', '#047857'); // emerald-700
                                            $set('theme_accent', '#14b8a6'); // teal-500
                                            $set('theme_warning', '#f59e0b');
                                            $set('theme_danger', '#ef4444');
                                            $set('theme_info', '#3b82f6');

                                            Notification::make()
                                                ->title('Preset "Emerald Green" Dipilih')
                                                ->body('Kombinasi warna telah diisikan ke form. Klik tombol "Simpan Pengaturan" di bawah untuk menerapkan.')
                                                ->info()
                                                ->send();
                                        }),
                                        
                                    \Filament\Actions\Action::make('presetRose')
                                        ->label('Rose Sunset')
                                        ->color('danger')
                                        ->icon(fn ($livewire) => $livewire->activePreset === 'rose' ? 'heroicon-m-check-circle' : 'heroicon-o-heart')
                                        ->badge(fn ($livewire) => $livewire->activePreset === 'rose' ? '✓' : null)
                                        ->badgeColor('success')
                                        ->action(function ($set, $livewire) {
                                            $livewire->activePreset = 'rose';
                                            $set('theme_primary', '#e11d48'); // rose-600
                                            $set('theme_secondary', '#be123c'); // rose-700
                                            $set('theme_accent', '#f43f5e'); // rose-500
                                            $set('theme_warning', '#f59e0b');
                                            $set('theme_danger', '#b91c1c'); // red-700
                                            $set('theme_info', '#3b82f6');

                                            Notification::make()
                                                ->title('Preset "Rose Sunset" Dipilih')
                                                ->body('Kombinasi warna telah diisikan ke form. Klik tombol "Simpan Pengaturan" di bawah untuk menerapkan.')
                                                ->info()
                                                ->send();
                                        }),
                                        
                                    \Filament\Actions\Action::make('presetAmber')
                                        ->label('Amber Gold')
                                        ->color('warning')
                                        ->icon(fn ($livewire) => $livewire->activePreset === 'amber' ? 'heroicon-m-check-circle' : 'heroicon-o-sun')
                                        ->badge(fn ($livewire) => $livewire->activePreset === 'amber' ? '✓' : null)
                                        ->badgeColor('success')
                                        ->action(function ($set, $livewire) {
                                            $livewire->activePreset = 'amber';
                                            $set('theme_primary', '#d97706'); // amber-600
                                            $set('theme_secondary', '#b45309'); // amber-700
                                            $set('theme_accent', '#84cc16'); // lime-500
                                            $set('theme_warning', '#f59e0b');
                                            $set('theme_danger', '#ef4444');
                                            $set('theme_info', '#3b82f6');

                                            Notification::make()
                                                ->title('Preset "Amber Gold" Dipilih')
                                                ->body('Kombinasi warna telah diisikan ke form. Klik tombol "Simpan Pengaturan" di bawah untuk menerapkan.')
                                                ->info()
                                                ->send();
                                        }),
                                        
                                    \Filament\Actions\Action::make('presetMonochrome')
                                        ->label('Monochrome Dark')
                                        ->color('gray')
                                        ->icon(fn ($livewire) => $livewire->activePreset === 'monochrome' ? 'heroicon-m-check-circle' : 'heroicon-o-moon')
                                        ->badge(fn ($livewire) => $livewire->activePreset === 'monochrome' ? '✓' : null)
                                        ->badgeColor('success')
                                        ->action(function ($set, $livewire) {
                                            $livewire->activePreset = 'monochrome';
                                            $set('theme_primary', '#374151'); // gray-700
                                            $set('theme_secondary', '#1f2937'); // gray-800
                                            $set('theme_accent', '#6b7280'); // gray-500
                                            $set('theme_warning', '#f59e0b');
                                            $set('theme_danger', '#ef4444');
                                            $set('theme_info', '#9ca3af'); // gray-400

                                            Notification::make()
                                                ->title('Preset "Monochrome Dark" Dipilih')
                                                ->body('Kombinasi warna telah diisikan ke form. Klik tombol "Simpan Pengaturan" di bawah untuk menerapkan.')
                                                ->info()
                                                ->send();
                                        }),
                                        
                                    \Filament\Actions\Action::make('presetDefault')
                                        ->label('Default Bawaan')
                                        ->color('primary')
                                        ->icon(fn ($livewire) => $livewire->activePreset === 'default' ? 'heroicon-m-check-circle' : 'heroicon-o-arrow-path')
                                        ->badge(fn ($livewire) => $livewire->activePreset === 'default' ? '✓' : null)
                                        ->badgeColor('success')
                                        ->action(function ($set, $livewire) {
                                            $livewire->activePreset = 'default';
                                            $set('theme_primary', null);
                                            $set('theme_secondary', null);
                                            $set('theme_accent', null);
                                            $set('theme_warning', null);
                                            $set('theme_danger', null);
                                            $set('theme_info', null);

                                            Notification::make()
                                                ->title('Reset ke Default Bawaan')
                                                ->body('Palet warna telah dikosongkan ke sistem bawaan. Klik tombol "Simpan Pengaturan" di bawah untuk menerapkan.')
                                                ->info()
                                                ->send();
                                        }),
                                ])->columns(3),
                            ]),
                            
                        \Filament\Schemas\Components\Tabs\Tab::make('Palet Manual')
                            ->icon('heroicon-o-swatch')
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('info_manual')
                                    ->hiddenLabel()
                                    ->content('Warna-warna ini akan secara dinamis diterapkan pada halaman publik. Kosongkan jika ingin menggunakan warna default bawaan sistem.'),
                                    
                                ColorPicker::make('theme_primary')
                                    ->label('Warna Utama (Primary)')
                                    ->helperText('Digunakan untuk aksen utama, hover menu, dan highlight teks.')
                                    ->nullable(),
                                    
                                ColorPicker::make('theme_secondary')
                                    ->label('Warna Sekunder (Secondary)')
                                    ->helperText('Digunakan sebagai pasangan warna utama (gradasi/hero banner).')
                                    ->nullable(),
                                    
                                ColorPicker::make('theme_accent')
                                    ->label('Warna Aksen (Accent/Hijau)')
                                    ->helperText('Biasanya digunakan untuk notifikasi positif, status "Tersedia", atau aksi sukses.')
                                    ->nullable(),
                                    
                                ColorPicker::make('theme_warning')
                                    ->label('Warna Peringatan (Warning/Kuning)')
                                    ->helperText('Digunakan untuk status "Dipinjam" atau peringatan.')
                                    ->nullable(),
                                    
                                ColorPicker::make('theme_danger')
                                    ->label('Warna Bahaya (Danger/Merah)')
                                    ->helperText('Digunakan untuk status "Rusak/Hilang" atau error.')
                                    ->nullable(),
                                    
                                ColorPicker::make('theme_info')
                                    ->label('Warna Info (Info/Biru Muda)')
                                    ->helperText('Digunakan untuk elemen informatif sekunder.')
                                    ->nullable(),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $settings = PengaturanSekolah::first();

            if ($settings) {
                $settings->update($data);
            } else {
                PengaturanSekolah::create($data);
            }

            $this->detectActivePreset($data['theme_primary'] ?? null);

            // EXACT KEY INVALIDATION AS REQUIRED
            Cache::forget('public_pengaturan_sekolah');

            Notification::make()
                ->title('Pengaturan tema berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('resetDefault')
                ->label('Reset ke Tema Bawaan')
                ->color('danger')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Reset Tema?')
                ->modalDescription('Tindakan ini akan mengosongkan semua pengaturan warna kustom dan mengembalikan website ke tema visual bawaan (Biru/Ungu). Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Reset Tema')
                ->action(function () {
                    $settings = PengaturanSekolah::first();
                    if ($settings) {
                        $settings->update([
                            'theme_primary' => null,
                            'theme_secondary' => null,
                            'theme_accent' => null,
                            'theme_warning' => null,
                            'theme_danger' => null,
                            'theme_info' => null,
                        ]);
                        
                        Cache::forget('public_pengaturan_sekolah');
                        
                        $this->form->fill($settings->toArray());
                        $this->activePreset = 'default';

                        Notification::make()
                            ->title('Tema berhasil dikembalikan ke bawaan sistem')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }
}
