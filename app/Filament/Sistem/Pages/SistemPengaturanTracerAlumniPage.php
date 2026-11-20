<?php

namespace App\Filament\Sistem\Pages;

use App\Models\AlumniSetting;
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

class SistemPengaturanTracerAlumniPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected string $view = 'filament.pages.alumni-setting-page';

    protected static ?string $navigationLabel = 'Pengaturan Tracer Alumni';

    protected static ?string $slug = 'pengaturan-tracer-alumni';

    protected static ?string $title = 'Pengaturan Modul Tracer Study Alumni (/alumni)';

    protected ?string $subheading = 'Atur judul banner, deskripsi pengantar, dan teks tombol formulir pendaftaran pada halaman Tracer Study Alumni (/alumni).';

    protected static string|UnitEnum|null $navigationGroup = 'Gateway & Pengaturan Modul';

    protected static ?int $navigationSort = 4;

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
        $setting = AlumniSetting::instance();

        $this->form->fill([
            'banner_title' => $setting->banner_title ?? 'Tracer Study Alumni',
            'button_text'  => $setting->button_text ?? 'Daftarkan Data Saya',
            'banner_text'  => $setting->banner_text ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas & Tampilan Halaman Tracer Alumni')
                    ->description('Atur judul besar banner, deskripsi pengantar, dan teks tombol pendaftaran data alumni pada halaman publik (/alumni).')
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
                        Placeholder::make('info_saklar_alumni')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-200">
                                    💡 <strong>Saklar Buka/Tutup Terpusat:</strong> Kontrol buka/tutup pengisian formulir Tracer Alumni serta visibilitas tabel Direktori Alumni Publik (<code>/alumni</code>) dikelola terpusat di menu <a href="' . url('/portal-sistem/maintenance-portal') . '" class="underline font-bold">Mode Maintenance &amp; Pengumuman</a>.
                                </div>
                            '))
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AlumniSetting::instance()->update([
            'banner_title' => $data['banner_title'],
            'button_text'  => $data['button_text'],
            'banner_text'  => $data['banner_text'] ?? null,
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan Tracer Alumni berhasil disimpan')
            ->send();
    }
}

