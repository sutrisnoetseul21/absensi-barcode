<?php

namespace App\Filament\Sistem\Pages;

use App\Models\PengaduanSetting;
use App\Models\WebSetting;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use UnitEnum;

class SistemPengaturanPelayananPublikPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.pengaduan-setting-page';

    protected static ?string $navigationLabel = 'Pengaturan Layanan Publik';

    protected static ?string $slug = 'pengaturan-layanan-publik';

    protected static ?string $title = 'Pengaturan Modul Layanan Publik & Pengaduan (/pengaduan)';

    protected ?string $subheading = 'Konfigurasi identitas modul aspirasi/pengaduan, dokumen resmi maklumat pelayanan, serta tautan kanal pengawasan publik (/layanan-publik).';

    protected static string|UnitEnum|null $navigationGroup = 'Gateway & Pengaturan Modul';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem', 'admin_portal_web']);
    }

    public function mount(): void
    {
        $pengaduan = PengaduanSetting::instance();
        $web = WebSetting::instance();

        $this->form->fill([
            'module_name'                => $pengaduan->module_name,
            'banner_title'               => $pengaduan->banner_title,
            'banner_text'                => $pengaduan->banner_text,
            'gambar_visi_misi_pelayanan' => $web->gambar_visi_misi_pelayanan,
            'gambar_maklumat_pelayanan'  => $web->gambar_maklumat_pelayanan,
            'link_sp4n_lapor'            => $web->link_sp4n_lapor,
            'link_cariyanlik'            => $web->link_cariyanlik,
            'link_pengaduan_daerah'      => $web->link_pengaduan_daerah,
            'link_survei_kepuasan'       => $web->link_survei_kepuasan,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas & Tampilan Formulir Pengaduan Publik')
                    ->description('Atur nama modul, judul besar, dan teks pengantar yang tampil pada halaman formulir pengaduan publik (/pengaduan).')
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
                        Placeholder::make('info_saklar_maintenance')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-200">
                                    💡 <strong>Saklar Buka/Tutup Terpusat:</strong> Kontrol buka/tutup layanan formulir pengaduan publik (<code>/pengaduan</code>) dikelola terpusat di menu <a href="' . url('/portal-sistem/maintenance-portal') . '" class="underline font-bold">Mode Maintenance &amp; Pengumuman</a>, sedangkan template pesan WA otomatis diatur di <a href="' . url('/portal-sistem/wa-pelayanan-publik') . '" class="underline font-bold">Notifikasi WA Pelayanan Publik</a>.
                                </div>
                            '))
                            ->columnSpanFull(),
                    ]),

                Section::make('Dokumen Resmi & Kanal Terintegrasi Layanan Publik')
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
            'banner_text'  => $data['banner_text'] ?? null,
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
            ->title('Pengaturan Layanan Publik berhasil disimpan')
            ->send();
    }
}

