<?php

namespace App\Filament\Sistem\Pages;

use App\Filament\Traits\HasJurnalMengajarWaNotifications;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;

class SistemWaJurnalMengajarPage extends Page implements HasForms
{
    use InteractsWithForms;
    use HasJurnalMengajarWaNotifications;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected string $view = 'filament.pages.manajemen-notifikasi-wa';

    protected static ?string $navigationLabel = 'Notifikasi WA Jurnal Mengajar';

    protected static ?string $slug = 'wa-jurnal-mengajar';

    protected static ?string $title = 'Notifikasi WhatsApp & Android Jurnal Mengajar (KBM)';

    protected ?string $subheading = 'Atur pengiriman notifikasi otomatis ke Wali Kelas, Orang Tua, dan HP Siswa saat siswa ditandai tidak mengikuti pelajaran di kelas.';

    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi WhatsApp';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem', 'admin_presensi_editor']);
    }

    public function mount(): void
    {
        $data = [];
        $this->loadJurnalMengajarData($data);
        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        $tab = $this->getJurnalMengajarWaTab();

        return $schema
            ->components($tab->getComponents(withActions: true, withHidden: true))
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $this->saveJurnalMengajarSettings($data);

            Notification::make()
                ->title('Pengaturan notifikasi Jurnal Mengajar berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}

