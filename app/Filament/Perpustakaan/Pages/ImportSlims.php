<?php

namespace App\Filament\Perpustakaan\Pages;

use App\Exports\SlimsBukuExport;
use App\Exports\SlimsDdcExport;
use App\Exports\SlimsEksemplarExport;
use App\Services\SlimsConnectionService;
use App\Services\SlimsMigrationService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Filament\Traits\HasSimplePageRoleAccess;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ImportSlims extends Page implements HasForms
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'perpustakaan';
    }

    protected static function requiresEditorRole(): bool
    {
        return true;
    }

    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-on-square-stack';
    protected static ?string $navigationLabel  = 'Import dari SLiMS';
    protected static ?string $title            = 'Sinkronisasi Data SLiMS → ERP';
    protected static string|\UnitEnum|null $navigationGroup = 'Perpustakaan';
    protected static ?int $navigationSort      = 11;

    protected string $view = 'filament.perpustakaan.pages.import-slims';

    public ?array $data = [];
    public bool $slimsConnected = false;
    public array $slimsStats    = [];
    public ?array $lastProgress = null;

    public function mount(): void
    {
        $conn = app(SlimsConnectionService::class);

        if ($conn->isConnected()) {
            $this->slimsConnected = true;
            $this->slimsStats     = $conn->getStats();
        }

        $this->lastProgress = Cache::get('slims_import_progress');

        $this->form->fill([
            'host'     => '127.0.0.1',
            'port'     => '3306',
            'database' => '',
            'username' => '',
            'password' => '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Koneksi Database SLiMS')
                    ->schema([
                        TextInput::make('host')
                            ->label('Host')
                            ->required()
                            ->placeholder('127.0.0.1'),
                        TextInput::make('port')
                            ->label('Port')
                            ->required()
                            ->placeholder('3306'),
                        TextInput::make('database')
                            ->label('Nama Database SLiMS')
                            ->required()
                            ->placeholder('perpus_db_perpus'),
                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->placeholder('perpus_user'),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->placeholder('••••••••'),
                    ])
                    ->columns(2),
            ]);
    }

    public function testKoneksi(): void
    {
        $data = $this->form->getState();
        $conn = app(SlimsConnectionService::class);
        $hasil = $conn->testConnection($data);

        if ($hasil === true) {
            $this->slimsConnected = true;
            $this->slimsStats     = $conn->getStats();
            Notification::make()
                ->title('✅ Terhubung ke database: ' . $data['database'])
                ->success()
                ->send();
        } else {
            $this->slimsConnected = false;
            Notification::make()
                ->title('❌ Gagal terhubung')
                ->body($hasil)
                ->danger()
                ->send();
        }
    }

    public function putusKoneksi(): void
    {
        $conn = app(SlimsConnectionService::class);
        $conn->forgetConnection();
        $this->slimsConnected = false;
        $this->slimsStats     = [];
    }

    public function downloadDdcXls(): mixed
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        return Excel::download(new SlimsDdcExport(app(SlimsConnectionService::class)), 'ddc-slims.xlsx');
    }

    public function downloadBukuXls(): mixed
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        return Excel::download(new SlimsBukuExport(app(SlimsConnectionService::class)), 'katalog-buku-slims.xlsx');
    }

    protected function getHeaderActions(): array
    {
        $settings = \App\Models\PengaturanSekolah::current();
        if ($settings && $settings->is_barcode_setup_completed) {
            return [];
        }

        return [
            \Filament\Actions\Action::make('skipSetup')
                ->label('Batal Import, Mulai dari Nomor Awal')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Batal Import SLiMS?')
                ->modalDescription('Anda akan membatalkan proses import SLiMS dan sistem akan mengatur penomoran barcode dimulai dari awal (Nomor 1). Anda yakin ingin melanjutkan?')
                ->action(function () {
                    $settings = \App\Models\PengaturanSekolah::current();
                    if ($settings) {
                        $settings->update([
                            'last_barcode_number' => 0,
                            'is_barcode_setup_completed' => true
                        ]);
                        Notification::make()
                            ->title('Setup Selesai. Penomoran dimulai dari 1.')
                            ->success()
                            ->send();
                        return redirect()->to(\App\Filament\Perpustakaan\Pages\Dashboard::getUrl());
                    }
                })
        ];
    }

}

