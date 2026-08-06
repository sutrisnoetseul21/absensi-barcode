<?php

namespace App\Filament\Perpustakaan\Pages;

use App\Exports\SlimsBukuExport;
use App\Exports\SlimsDdcExport;
use App\Exports\SlimsEksemplarExport;
use App\Services\SlimsConnectionService;
use App\Services\SlimsMigrationService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;

class ImportSlims extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-down-on-square-stack';
    protected static ?string $navigationLabel  = 'Import dari SLiMS';
    protected static ?string $title            = 'Sinkronisasi Data SLiMS → ERP';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort      = 90;

    protected string $view = 'filament.perpustakaan.pages.import-slims';

    // Form fields untuk koneksi
    public ?array $data = [];

    // State
    public bool $slimsConnected = false;
    public array $slimsStats    = [];
    public ?array $lastReport   = null;

    public function mount(): void
    {
        $conn = app(SlimsConnectionService::class);

        if ($conn->isConnected()) {
            $this->slimsConnected = true;
            $this->slimsStats     = $conn->getStats();
        }

        // Muat laporan terakhir dari cache (berguna jika sebelumnya terkena 504 Timeout)
        $this->lastReport = \Illuminate\Support\Facades\Cache::get('slims_last_report');

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
            ->schema([
                Section::make('Konfigurasi Koneksi Database SLiMS')
                    ->description('Masukkan kredensial database SLiMS. Data ini hanya disimpan sementara di session dan tidak disimpan ke file .env.')
                    ->icon('heroicon-o-circle-stack')
                    ->schema([
                        TextInput::make('host')
                            ->label('Host / IP Database')
                            ->placeholder('127.0.0.1')
                            ->required()
                            ->default('127.0.0.1'),

                        TextInput::make('port')
                            ->label('Port')
                            ->placeholder('3306')
                            ->required()
                            ->default('3306')
                            ->numeric(),

                        TextInput::make('database')
                            ->label('Nama Database SLiMS')
                            ->placeholder('perpus_db_perpus')
                            ->required()
                            ->helperText('Nama database MySQL yang dipakai SLiMS'),

                        TextInput::make('username')
                            ->label('Username Database')
                            ->placeholder('perpus_user')
                            ->required(),

                        TextInput::make('password')
                            ->label('Password Database')
                            ->password()
                            ->revealable()
                            ->placeholder('••••••••')
                            ->extraAttributes(['autocomplete' => 'new-password']),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HEADER ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        $conn = app(SlimsConnectionService::class);

        if (!$this->slimsConnected) {
            return [
                Action::make('testKoneksi')
                    ->label('Tes Koneksi')
                    ->icon('heroicon-o-signal')
                    ->color('primary')
                    ->action('testKoneksi'),
            ];
        }

        return [
            // ── Download XLS ──
            Action::make('downloadDdcXls')
                ->label('Export DDC (.xlsx)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action('downloadDdcXls'),

            Action::make('downloadBukuXls')
                ->label('Export Buku (.xlsx)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action('downloadBukuXls'),

            Action::make('downloadEksemplarXls')
                ->label('Export Eksemplar (.xlsx)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action('downloadEksemplarXls'),

            // ── Putus koneksi ──
            Action::make('putusKoneksi')
                ->label('Putus Koneksi')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Putus Koneksi SLiMS?')
                ->modalDescription('Session koneksi ke database SLiMS akan dihapus. Anda perlu mengisi form koneksi lagi untuk melanjutkan.')
                ->action('putusKoneksi'),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTION METHODS
    // ─────────────────────────────────────────────────────────────────────────

    public function testKoneksi(): void
    {
        $data   = $this->form->getState();
        $conn   = app(SlimsConnectionService::class);
        $result = $conn->testConnection($data);

        if ($result === true) {
            $this->slimsConnected = true;
            $this->slimsStats     = $conn->getStats();

            Notification::make()
                ->title('Koneksi berhasil!')
                ->body("Terhubung ke database \"{$data['database']}\". Ditemukan {$this->slimsStats['biblio']} judul buku dan {$this->slimsStats['item']} eksemplar.")
                ->success()
                ->duration(6000)
                ->send();
        } else {
            Notification::make()
                ->title('Koneksi gagal')
                ->body($result)
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function putusKoneksi(): void
    {
        app(SlimsConnectionService::class)->forgetConnection();
        $this->slimsConnected = false;
        $this->slimsStats     = [];
        $this->lastReport     = null;

        Notification::make()
            ->title('Koneksi diputus')
            ->body('Session koneksi SLiMS telah dihapus.')
            ->info()
            ->send();
    }

    // ── Import Actions ────────────────────────────────────────────────────────

    protected function getImportActions(): array
    {
        return [
            Action::make('importDdc')
                ->label('Import DDC')
                ->icon('heroicon-o-tag')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('⚠️ Import Klasifikasi DDC')
                ->modalDescription('Data klasifikasi DDC di ERP akan di-OVERWRITE dengan data dari SLiMS. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Import DDC')
                ->action('jalanImportDdc'),

            Action::make('importBuku')
                ->label('Import Buku')
                ->icon('heroicon-o-book-open')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('⚠️ Import Katalog Buku')
                ->modalDescription('Data buku di ERP akan di-OVERWRITE dengan data dari SLiMS (berdasarkan ISBN / judul+penerbit). Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Import Buku')
                ->action('jalanImportBuku'),

            Action::make('importEksemplar')
                ->label('Import Eksemplar')
                ->icon('heroicon-o-cube')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('⚠️ Import Eksemplar Buku')
                ->modalDescription('Data eksemplar di ERP akan di-OVERWRITE. Inventaris otomatis akan dibuat per judul buku. WAJIB jalankan "Import Buku" terlebih dahulu. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Import Eksemplar')
                ->action('jalanImportEksemplar'),

            Action::make('importSemua')
                ->label('Import SEMUA (DDC → Buku → Eksemplar)')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('⚠️ Import Semua Data SLiMS')
                ->modalDescription('SEMUA data di ERP (DDC, Buku, Eksemplar, Inventaris) akan di-OVERWRITE dengan data dari SLiMS. Proses ini membutuhkan beberapa menit. Pastikan sudah backup database ERP terlebih dahulu!')
                ->modalSubmitActionLabel('Saya Siap, Import Semua')
                ->action('jalanImportSemua'),
        ];
    }

    public function jalanImportDdc(): void
    {
        set_time_limit(300); // 5 menit
        try {
            $svc    = new SlimsMigrationService(app(SlimsConnectionService::class));
            $result = $svc->importDdc();
            $this->lastReport = ['jenis' => 'DDC', 'hasil' => $result];

            Notification::make()
                ->title('Import DDC selesai')
                ->body("Baru: {$result['baru']} | Diupdate: {$result['diupdate']} | Error: {$result['error']}")
                ->success()
                ->duration(8000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()->title('Import gagal')->body($e->getMessage())->danger()->persistent()->send();
        }
    }

    public function jalanImportBuku(): void
    {
        set_time_limit(600); // 10 menit
        try {
            $svc    = new SlimsMigrationService(app(SlimsConnectionService::class));
            $result = $svc->importBuku();
            $this->lastReport = ['jenis' => 'Buku', 'hasil' => $result];

            Notification::make()
                ->title('Import Buku selesai')
                ->body("Baru: {$result['baru']} | Diupdate: {$result['diupdate']} | Error: {$result['error']}")
                ->success()
                ->duration(8000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()->title('Import gagal')->body($e->getMessage())->danger()->persistent()->send();
        }
    }

    public function jalanImportEksemplar(): void
    {
        set_time_limit(1800); // 30 menit untuk 33k eksemplar
        try {
            $svc    = new SlimsMigrationService(app(SlimsConnectionService::class));
            $result = $svc->importEksemplar();
            $this->lastReport = ['jenis' => 'Eksemplar', 'hasil' => $result];

            Notification::make()
                ->title('Import Eksemplar selesai')
                ->body("Baru: {$result['baru']} | Diupdate: {$result['diupdate']} | Dilewati: {$result['dilewati']} | Inventaris dibuat: {$result['inventaris_dibuat']} | Error: {$result['error']}")
                ->success()
                ->duration(8000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()->title('Import gagal')->body($e->getMessage())->danger()->persistent()->send();
        }
    }

    public function jalanImportSemua(): void
    {
        set_time_limit(3600); // 1 jam
        try {
            $svc    = new SlimsMigrationService(app(SlimsConnectionService::class));
            $result = $svc->importSemua();
            $this->lastReport = \Illuminate\Support\Facades\Cache::get('slims_last_report');

            $ddcMsg  = "DDC — Baru: {$result['ddc']['baru']} | Update: {$result['ddc']['diupdate']}";
            $bukuMsg = "Buku — Baru: {$result['buku']['baru']} | Update: {$result['buku']['diupdate']}";
            $ekMsg   = "Eksemplar — Baru: {$result['eksemplar']['baru']} | Inventaris: {$result['eksemplar']['inventaris_dibuat']}";

            Notification::make()
                ->title('Import Semua selesai!')
                ->body("{$ddcMsg}\n{$bukuMsg}\n{$ekMsg}")
                ->success()
                ->duration(10000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()->title('Import gagal')->body($e->getMessage())->danger()->persistent()->send();
        }
    }

    public function refreshLaporan(): void
    {
        $this->lastReport = \Illuminate\Support\Facades\Cache::get('slims_last_report');
        if ($this->lastReport) {
            Notification::make()->title('Laporan diperbarui')->success()->send();
        } else {
            Notification::make()->title('Belum ada laporan terbaru')->info()->send();
        }
    }

    // ── Download XLS ──────────────────────────────────────────────────────────

    public function downloadDdcXls(): mixed
    {
        try {
            $conn = app(SlimsConnectionService::class)->getConnection();
            $data = $conn->table('mst_topic')
                ->whereNotNull('topic')
                ->where('topic', '!=', '')
                ->orderBy('topic_id')
                ->get();

            $filename = 'export-ddc-slims-' . now()->format('Ymd-His') . '.xlsx';
            return Excel::download(new SlimsDdcExport($data), $filename);
        } catch (\Exception $e) {
            Notification::make()->title('Export gagal')->body($e->getMessage())->danger()->send();
        }
        return null;
    }

    public function downloadBukuXls(): mixed
    {
        try {
            $conn = app(SlimsConnectionService::class)->getConnection();
            $data = $conn->table('biblio')
                ->leftJoin('mst_publisher', 'biblio.publisher_id', '=', 'mst_publisher.publisher_id')
                ->leftJoin('biblio_author', 'biblio.biblio_id', '=', 'biblio_author.biblio_id')
                ->leftJoin('mst_author', 'biblio_author.author_id', '=', 'mst_author.author_id')
                ->select(
                    'biblio.biblio_id',
                    'biblio.title',
                    'biblio.isbn_issn',
                    'biblio.publish_year',
                    'biblio.classification',
                    'mst_publisher.publisher_name',
                )
                ->selectRaw('GROUP_CONCAT(mst_author.author_name ORDER BY biblio_author.level SEPARATOR ", ") as penulis')
                ->selectRaw('(SELECT i2.coll_type_id FROM item i2 WHERE i2.biblio_id = biblio.biblio_id LIMIT 1) as coll_type_id')
                ->groupBy('biblio.biblio_id', 'biblio.title', 'biblio.isbn_issn', 'biblio.publish_year', 'biblio.classification', 'mst_publisher.publisher_name')
                ->orderBy('biblio.biblio_id')
                ->get();

            $filename = 'export-buku-slims-' . now()->format('Ymd-His') . '.xlsx';
            return Excel::download(new SlimsBukuExport($data), $filename);
        } catch (\Exception $e) {
            Notification::make()->title('Export gagal')->body($e->getMessage())->danger()->send();
        }
        return null;
    }

    public function downloadEksemplarXls(): mixed
    {
        try {
            $conn = app(SlimsConnectionService::class)->getConnection();
            $data = $conn->table('item')
                ->leftJoin('biblio', 'item.biblio_id', '=', 'biblio.biblio_id')
                ->whereNotNull('item.item_code')
                ->where('item.item_code', '!=', '')
                ->select(
                    'item.item_id',
                    'item.item_code',
                    'item.inventory_code',
                    'item.item_status_id',
                    'item.received_date',
                    'item.price',
                    'biblio.title',
                )
                ->orderBy('item.biblio_id')
                ->orderBy('item.item_id')
                ->get();

            $filename = 'export-eksemplar-slims-' . now()->format('Ymd-His') . '.xlsx';
            return Excel::download(new SlimsEksemplarExport($data), $filename);
        } catch (\Exception $e) {
            Notification::make()->title('Export gagal')->body($e->getMessage())->danger()->send();
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PASS DATA TO VIEW
    // ─────────────────────────────────────────────────────────────────────────

    protected function getViewData(): array
    {
        return [
            'importActions' => $this->getImportActions(),
        ];
    }
}
