<?php

namespace App\Filament\Akademik\Pages;

use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Filament\Traits\HasSimplePageRoleAccess;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class AkademikSettingsPage extends Page implements HasForms
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static function requiresEditorRole(): bool
    {
        return true;
    }

    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.school-settings';

    protected static ?string $navigationLabel = 'Pengaturan Akademik';

    protected static ?string $slug = 'pengaturan-akademik';

    protected static ?string $title = 'Pengaturan Akademik';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 8;

    public ?array $data = [];

    public function mount(): void
    {
        // Load data singleton
        $settings = PengaturanSekolah::current();

        if ($settings) {
            $this->form->fill($settings->toArray());
        } else {
            $this->form->fill();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Akademik')
                    ->schema([
                        Select::make('academic_year_id_active')
                            ->label('Tahun Ajaran Aktif Saat Ini')
                            ->options(TahunAjaran::where('status', 'aktif')->pluck('name', 'id'))
                            ->nullable()
                            ->helperText('Otomatis diset ketika Tahun Ajaran diubah menjadi "Aktif" di Data Master. Form ini hanya read-only/display.')
                            ->disabled() // Di-disable karena diset dari TahunAjaranResource
                            ->dehydrated(false), // Jangan disimpan dari form ini

                        Toggle::make('enable_promotion_features')
                            ->label('Aktifkan Tombol Kenaikan & Kelulusan Kelas')
                            ->helperText('Jika diaktifkan, tombol Luluskan dan Naik Kelas akan muncul di tabel Siswa.')
                            ->default(false),
                    ])->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Karena data singleton, kita pake id 1 jika belum ada
            $settings = PengaturanSekolah::first();

            if ($settings) {
                $settings->update($data);
            } else {
                PengaturanSekolah::create($data);
            }

            \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');

            Notification::make()
                ->title('Pengaturan Akademik berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
