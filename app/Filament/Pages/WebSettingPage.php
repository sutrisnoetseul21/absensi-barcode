<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\WebSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class WebSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $title = 'Pengaturan Web Profil';
    protected static ?string $slug = 'web-setting';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.web-setting-page';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = WebSetting::instance();
        $this->form->fill($setting->toArray());
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Hero & Sambutan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('hero_image')
                            ->label('Hero Image (Banner)')
                            ->image()
                            ->directory('web-profil')
                            ->disk('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('running_text')
                            ->label('Running Text')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('foto_kepsek')
                            ->label('Foto Kepala Sekolah')
                            ->image()
                            ->directory('web-profil')
                            ->disk('public')
                            ->imageEditor(),
                        Forms\Components\Textarea::make('kutipan_kepsek')
                            ->label('Kutipan Singkat Kepala Sekolah')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('profil_singkat')
                            ->label('Profil Singkat Sekolah')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('visi')
                            ->label('Visi Sekolah')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('misi')
                            ->label('Misi Sekolah')
                            ->helperText('Gunakan tombol Enter untuk memisahkan setiap poin misi.')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('sambutan_kepsek')
                            ->label('Sambutan Kepala Sekolah')
                            ->columnSpanFull(),
                    ]),
                \Filament\Schemas\Components\Section::make('Sosial Media & Pengaduan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('link_youtube')
                            ->label('Link YouTube')
                            ->url(),
                        Forms\Components\TextInput::make('link_tiktok')
                            ->label('Link TikTok')
                            ->url(),
                        Forms\Components\TextInput::make('link_ig')
                            ->label('Link Instagram')
                            ->url(),
                        Forms\Components\TextInput::make('link_fb')
                            ->label('Link Facebook')
                            ->url(),
                        Forms\Components\TextInput::make('link_pengaduan')
                            ->label('Link Layanan Pengaduan')
                            ->url()
                            ->columnSpanFull(),
                    ]),
                \Filament\Schemas\Components\Section::make('Statistik Tambahan')
                    ->schema([
                        Forms\Components\TextInput::make('stat_tenaga_kependidikan')
                            ->label('Jumlah Tenaga Kependidikan (Tendik)')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = WebSetting::instance();
        $setting->update($data);

        Notification::make()
            ->success()
            ->title('Pengaturan berhasil disimpan')
            ->send();
    }
}
