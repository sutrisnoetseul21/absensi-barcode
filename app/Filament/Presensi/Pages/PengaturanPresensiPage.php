<?php

namespace App\Filament\Presensi\Pages;

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

class PengaturanPresensiPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.school-settings';

    protected static ?string $navigationLabel = 'Pengaturan Presensi';

    protected static ?string $slug = 'pengaturan-presensi';

    protected static ?string $title = 'Pengaturan Presensi';

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
                Section::make('Waktu Kehadiran')
                    ->schema([
                        TimePicker::make('checkin_time')
                            ->label('Jam Masuk (Check-in)')
                            ->required()
                            ->seconds(false)
                            ->helperText('Jam normal siswa masuk sekolah.'),

                        TextInput::make('late_threshold_minutes')
                            ->label('Batas Toleransi Terlambat (Menit)')
                            ->numeric()
                            ->default(15)
                            ->required()
                            ->helperText('Jumlah menit toleransi setelah jam masuk.'),

                        TimePicker::make('batas_scan_datang_time')
                            ->label('Batas Jam Absen Datang (Kiosk)')
                            ->required()
                            ->seconds(false)
                            ->helperText('Maksimal jam siswa bisa absen datang lewat mesin scanner. Lewat dari ini ditolak otomatis.'),

                        TimePicker::make('start_scan_out_time')
                            ->label('Jam Mulai Absen Pulang (Kiosk)')
                            ->required()
                            ->seconds(false)
                            ->helperText('Mesin scanner akan menganggap tap kartu setelah jam ini sebagai absen pulang.'),

                        Select::make('barcode_scan_mode')
                            ->label('Mode Kios Scanner Barcode')
                            ->options([
                                'nisn' => 'Gunakan NISN (Default)',
                                'nis' => 'Gunakan NIS',
                            ])
                            ->default('nisn')
                            ->required()
                            ->helperText('Menentukan jenis barcode yang akan dipindai oleh mesin presensi.'),
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

            \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');

            Notification::make()
                ->title('Pengaturan berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
