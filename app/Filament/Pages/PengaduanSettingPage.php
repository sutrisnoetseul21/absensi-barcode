<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\PengaduanSetting;
use App\Models\WebSetting;
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
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.pengaduan-setting-page';

    public ?array $data = [];

    public function mount(): void
    {
        $pengaduan = PengaduanSetting::instance();
        $web = WebSetting::instance();

        $this->form->fill(array_merge($pengaduan->toArray(), [
            'gambar_visi_misi_pelayanan' => $web->gambar_visi_misi_pelayanan,
            'gambar_maklumat_pelayanan'  => $web->gambar_maklumat_pelayanan,
            'link_sp4n_lapor'            => $web->link_sp4n_lapor,
            'link_cariyanlik'            => $web->link_cariyanlik,
            'link_pengaduan_daerah'      => $web->link_pengaduan_daerah,
            'link_survei_kepuasan'       => $web->link_survei_kepuasan,
        ]));
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi & Tampilan Formulir Pengaduan')
                    ->description('Atur judul dan teks informasi yang tampil pada halaman formulir pengaduan publik (/pengaduan).')
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

                \Filament\Schemas\Components\Section::make('Dokumen Resmi & Kanal Terintegrasi Layanan Publik')
                    ->description('Unggah gambar Visi Misi Pelayanan, Maklumat Pelayanan, dan tautan kanal lapor resmi yang tampil di halaman portal Layanan Publik (/layanan-publik).')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('gambar_visi_misi_pelayanan')
                            ->label('Gambar Visi & Misi Pelayanan')
                            ->image()
                            ->directory('web-profil')
                            ->disk('public')
                            ->imageEditor()
                            ->helperText('Bagan komitmen Visi Misi Pelayanan sekolah.'),
                        Forms\Components\FileUpload::make('gambar_maklumat_pelayanan')
                            ->label('Gambar Maklumat Pelayanan')
                            ->image()
                            ->directory('web-profil')
                            ->disk('public')
                            ->imageEditor()
                            ->helperText('Bagan resmi Maklumat Pelayanan sekolah.'),
                        Forms\Components\TextInput::make('link_sp4n_lapor')
                            ->label('Link URL SP4N-LAPOR! (KemenPAN-RB)')
                            ->placeholder('https://www.lapor.go.id/...')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('link_cariyanlik')
                            ->label('Link URL Katalog SIPPN / Cariyanlik Pelayanan')
                            ->placeholder('https://sippn.menpan.go.id/...')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('link_pengaduan_daerah')
                            ->label('Link URL Halo Pengaduan Daerah (Pemda / Dinas)')
                            ->placeholder('https://pengaduan.daerah.go.id/...')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('link_survei_kepuasan')
                            ->label('Link URL Survei Kepuasan Masyarakat (IKM)')
                            ->placeholder('https://forms.gle/... atau link survei lainnya')
                            ->url()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        PengaduanSetting::instance()->update([
            'module_name'  => $data['module_name'],
            'banner_title' => $data['banner_title'],
            'banner_text'  => $data['banner_text'],
            'is_active'    => $data['is_active'] ?? true,
        ]);

        WebSetting::instance()->update([
            'gambar_visi_misi_pelayanan' => $data['gambar_visi_misi_pelayanan'] ?? null,
            'gambar_maklumat_pelayanan'  => $data['gambar_maklumat_pelayanan'] ?? null,
            'link_sp4n_lapor'            => $data['link_sp4n_lapor'] ?? null,
            'link_cariyanlik'            => $data['link_cariyanlik'] ?? null,
            'link_pengaduan_daerah'      => $data['link_pengaduan_daerah'] ?? null,
            'link_survei_kepuasan'       => $data['link_survei_kepuasan'] ?? null,
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan Layanan berhasil disimpan')
            ->send();
    }
}
