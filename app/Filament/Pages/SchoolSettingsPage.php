<?php

namespace App\Filament\Pages;

use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class SchoolSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.school-settings';

    protected static ?string $navigationLabel = 'Pengaturan Sekolah';

    protected static ?string $title = 'Pengaturan Sekolah';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    public ?array $data = [];

    /**
     * Hanya Super Admin yang bisa mengakses halaman pengaturan.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

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
                Section::make('Identitas Sekolah')
                    ->schema([
                        TextInput::make('school_name')
                            ->label('Nama Sekolah')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('school_address')
                            ->label('Alamat Sekolah')
                            ->required()
                            ->maxLength(500),

                        TextInput::make('principal_name')
                            ->label('Nama Kepala Sekolah')
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('school_logo_path')
                            ->label('Logo Sekolah')
                            ->image()
                            ->directory('settings')
                            ->nullable(),
                    ])->columns(1),

                Section::make('Pengaturan Sistem')
                    ->schema([
                        TimePicker::make('checkin_time')
                            ->label('Jam Masuk (Check-in)')
                            ->required()
                            ->seconds(false)
                            ->helperText('Jam batas masuk siswa.'),

                        TextInput::make('late_threshold_minutes')
                            ->label('Batas Toleransi Terlambat (Menit)')
                            ->numeric()
                            ->default(15)
                            ->required()
                            ->helperText('Jumlah menit toleransi setelah jam masuk.'),

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
                    ])->columns(2),
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

            Notification::make()
                ->title('Pengaturan berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
