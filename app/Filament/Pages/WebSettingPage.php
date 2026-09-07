<?php

namespace App\Filament\Pages;

use App\Filament\Components\TinyEditor;
use App\Models\WebSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pengaturan Web Profil')
                    ->tabs([
                        Tab::make('Hero & Beranda')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Forms\Components\FileUpload::make('hero_image')
                                    ->label('Hero Image (Banner Utama Beranda)')
                                    ->image()
                                    ->directory('web-profil')
                                    ->disk('public')
                                    ->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('running_text')
                                    ->label('Running Text Pengumuman')
                                    ->placeholder('Teks berjalan yang tampil di bawah navbar beranda...')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('foto_kepsek')
                                    ->label('Foto Kepala Sekolah')
                                    ->image()
                                    ->directory('web-profil')
                                    ->disk('public')
                                    ->imageEditor()
                                    ->helperText('Gunakan foto portrait resmi (rasio 3x4 / 2x3).'),
                                Forms\Components\Select::make('posisi_foto_kepsek')
                                    ->label('Posisi Fokus Vertikal Foto')
                                    ->options([
                                        'top'    => 'Fokus Atas (Kepala / Jilbab)',
                                        'center' => 'Fokus Tengah (Badan)',
                                        'bottom' => 'Fokus Bawah',
                                    ])
                                    ->default('top')
                                    ->helperText('Pilih posisi fokus agar foto proporsional portrait 3:4 dan kepala/jilbab tidak terpotong.'),
                                Forms\Components\Textarea::make('kutipan_kepsek')
                                    ->label('Kutipan Singkat Kepala Sekolah')
                                    ->placeholder('Kutipan singkat yang tampil di samping foto kepala sekolah...')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                                TinyEditor::make('sambutan_kepsek')
                                    ->label('Sambutan Kepala Sekolah')
                                    ->helperText('Amanat atau sambutan lengkap dari Kepala Sekolah.')
                                    ->height(400)
                                    ->uploadDirectory('web-profil/editor')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('link_youtube')
                                    ->label('Video YouTube Beranda (Profil / Kegiatan Sekolah)')
                                    ->placeholder('https://www.youtube.com/watch?v=xxxxxxxxx')
                                    ->helperText('Tautan video YouTube yang di-embed langsung pada widget beranda dan tautan footer.')
                                    ->url()
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('struktur_organisasi')
                                    ->label('Bagan Struktur Organisasi Sekolah')
                                    ->image()
                                    ->directory('web-profil')
                                    ->disk('public')
                                    ->imageEditor()
                                    ->helperText('Unggah bagan struktur organisasi yang tampil di halaman Profil Sekolah.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Profil, Visi & Misi')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                TinyEditor::make('profil_singkat')
                                    ->label('Profil Singkat & Sejarah Sekolah')
                                    ->helperText('Ringkasan profil atau sejarah sekolah yang tampil di beranda dan halaman profil.')
                                    ->height(280)
                                    ->uploadDirectory('web-profil/editor')
                                    ->columnSpanFull(),
                                TinyEditor::make('visi')
                                    ->label('Visi Sekolah')
                                    ->helperText('Visi sekolah dapat diformat tebal, miring, atau paragraf.')
                                    ->height(220)
                                    ->uploadDirectory('web-profil/editor')
                                    ->columnSpanFull(),
                                TinyEditor::make('misi')
                                    ->label('Misi Sekolah')
                                    ->helperText('Dapat berupa daftar poin (bullet/numbered list) atau paragraf.')
                                    ->height(300)
                                    ->uploadDirectory('web-profil/editor')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Sosial Media & Statistik')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Section::make('Tautan Media Sosial Resmi')
                                    ->description('Tautan media sosial resmi sekolah yang tampil pada header dan footer website.')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('link_youtube')
                                            ->label('Link YouTube')
                                            ->placeholder('https://youtube.com/@sekolah')
                                            ->url(),
                                        Forms\Components\TextInput::make('link_tiktok')
                                            ->label('Link TikTok')
                                            ->placeholder('https://tiktok.com/@sekolah')
                                            ->url(),
                                        Forms\Components\TextInput::make('link_ig')
                                            ->label('Link Instagram')
                                            ->placeholder('https://instagram.com/sekolah')
                                            ->url(),
                                        Forms\Components\TextInput::make('link_fb')
                                            ->label('Link Facebook')
                                            ->placeholder('https://facebook.com/sekolah')
                                            ->url(),
                                    ]),
                                Section::make('Statistik Tambahan')
                                    ->description('Statistik pendukung pada profil beranda sekolah.')
                                    ->schema([
                                        Forms\Components\TextInput::make('stat_tenaga_kependidikan')
                                            ->label('Jumlah Tenaga Kependidikan (Tendik)')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
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
            ->title('Pengaturan Web Profil berhasil disimpan')
            ->send();
    }
}
