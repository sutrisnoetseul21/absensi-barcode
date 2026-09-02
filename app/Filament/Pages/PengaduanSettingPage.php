<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\PengaduanSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class PengaduanSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Publik';
    protected static ?string $title = 'Pengaturan Layanan';
    protected static ?string $slug = 'pengaduan/settings';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.pengaduan-setting-page';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = PengaduanSetting::instance();
        $this->form->fill($setting->toArray());
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi & Tampilan Banner Layanan')
                    ->description('Atur judul dan teks informasi yang tampil pada halaman formulir pengaduan publik.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('module_name')
                            ->label('Nama Modul / Label Formulir')
                            ->placeholder('Contoh: Pengaduan, Kotak Saran, Aspirasi')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('banner_title')
                            ->label('Judul Besar Banner')
                            ->placeholder('Contoh: Layanan Aspirasi & Pengaduan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('banner_text')
                            ->label('Deskripsi / Teks Pengantar Banner')
                            ->placeholder('Punya saran, kritik, atau laporan? Sampaikan kepada kami dengan mudah, cepat, dan aman.')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Formulir Pengaduan Publik?')
                            ->helperText('Jika dinonaktifkan, halaman pengaduan akan menampilkan informasi bahwa layanan sedang ditutup sementara.')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = PengaduanSetting::instance();
        $setting->update($data);

        Notification::make()
            ->success()
            ->title('Pengaturan Layanan Pengaduan berhasil disimpan')
            ->send();
    }
}
