<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\AlumniSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class AlumniSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|\UnitEnum|null $navigationGroup = 'Data Alumni';
    protected static ?string $title = 'Pengaturan Tracer Alumni';
    protected static ?string $slug = 'alumni/settings';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.alumni-setting-page';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = AlumniSetting::instance();
        $this->form->fill($setting->toArray());
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi & Tampilan Halaman Tracer Alumni')
                    ->description('Atur judul, deskripsi banner, dan tombol pendaftaran data alumni.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('banner_title')
                            ->label('Judul Besar Banner')
                            ->placeholder('Contoh: Tracer Study Alumni')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('button_text')
                            ->label('Teks Tombol Formulir')
                            ->placeholder('Contoh: Daftarkan Data Saya')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Textarea::make('banner_text')
                            ->label('Deskripsi / Teks Pengantar Banner')
                            ->placeholder('Mari terus menjalin silaturahmi dan berbagi inspirasi.')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Halaman Tracer Alumni Publik?')
                            ->helperText('Jika dinonaktifkan, pengisian data alumni ditutup sementara.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_table')
                            ->label('Tampilkan Direktori Alumni Publik?')
                            ->helperText('Jika diaktifkan, kartu dan data alumni akan tampil di halaman /alumni.')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = AlumniSetting::instance();
        $setting->update($data);

        Notification::make()
            ->success()
            ->title('Pengaturan Tracer Alumni berhasil disimpan')
            ->send();
    }
}
