<?php

namespace App\Filament\Perpustakaan\Pages;

use App\Models\PengaturanSekolah;
use App\Services\BarcodeService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Closure;

class PengaturanPerpustakaan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Default';
    protected static ?string $title = 'Pengaturan Perpustakaan';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.perpustakaan.pages.pengaturan-perpustakaan';

    public ?array $data = [];
    public bool $isUnlocked = false;

    public function mount(): void
    {
        $settings = PengaturanSekolah::current();
        if ($settings) {
            $realMax = BarcodeService::getRealMaxBarcodeNumber();
            $lastBarcode = $settings->last_barcode_number ?? 0;
            
            // Jika counter 0 dan database kosong, asumsikan perpustakaan baru (Opsi 1: Mulai dari awal)
            $tipePenomoran = ($lastBarcode == 0 && $realMax == 0) ? 'auto' : 'manual';
            
            $this->form->fill([
                'lama_pinjam_buku_hari' => $settings->lama_pinjam_buku_hari,
                'barcode_scan_mode' => $settings->barcode_scan_mode ?? 'nisn',
                'tipe_penomoran' => $tipePenomoran,
                'last_barcode_number' => $lastBarcode,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Konfigurasi Sirkulasi')
                    ->description('Atur pengaturan dasar untuk operasional perpustakaan.')
                    ->schema([
                        TextInput::make('lama_pinjam_buku_hari')
                            ->label('Batas Lama Pinjam Buku (Hari)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(365)
                            ->helperText('Berapa hari maksimal anggota dapat meminjam buku sebelum ditandai terlambat.'),
                        
                        Select::make('barcode_scan_mode')
                            ->label('Mode Kios Scanner Barcode (Siswa)')
                            ->options([
                                'nisn' => 'Gunakan NISN',
                                'nis' => 'Gunakan NIS',
                            ])
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Pengaturan mode ini diatur melalui Pengaturan Admin Sekolah Utama.'),
                    ]),
                
                Section::make('Konfigurasi Barcode Eksemplar')
                    ->description('Pengaturan standar penomoran barcode (Counter Global). Nomor urut akan dilanjutkan lintas prefix secara otomatis.')
                    ->schema([
                        Radio::make('tipe_penomoran')
                            ->label('Skenario Penomoran')
                            ->options([
                                'auto' => 'Mulai dari Awal (Perpustakaan Baru)',
                                'manual' => 'Lanjutkan dari Nomor Tertentu (Migrasi Data Lama)',
                            ])
                            ->disabled(function () {
                                $settings = PengaturanSekolah::current();
                                return $settings && $settings->is_barcode_setup_completed && !$this->isUnlocked;
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state === 'auto') {
                                    $set('last_barcode_number', BarcodeService::getRealMaxBarcodeNumber());
                                }
                            }),
                            
                        Placeholder::make('info_penomoran')
                            ->label('Petunjuk')
                            ->content(fn (Get $get) => new HtmlString(
                                $get('tipe_penomoran') === 'auto' 
                                ? '<div class="text-sm text-gray-500">Jika perpustakaan Anda baru mulai menggunakan sistem ini dari nol, <strong>TIDAK PERLU mengubah apa pun di sini</strong>. Biarkan nilai default, nomor barcode akan otomatis dimulai dari 1 saat buku pertama diinput.</div>'
                                : '<div class="text-sm text-gray-500">Jika perpustakaan Anda sudah memiliki koleksi fisik dengan nomor barcode manual sebelumnya (misal sudah sampai nomor 2000), isi angka tersebut di bawah ini agar buku baru melanjutkan nomor urut tersebut, bukan mulai dari 1 lagi.</div>'
                            )),

                        TextInput::make('last_barcode_number')
                            ->label('Nomor Urut Terakhir')
                            ->numeric()
                            ->required()
                            ->disabled(function () {
                                $settings = PengaturanSekolah::current();
                                return $settings && $settings->is_barcode_setup_completed && !$this->isUnlocked;
                            })
                            ->hidden(fn (Get $get) => $get('tipe_penomoran') === 'auto')
                            ->helperText(fn () => 'Nomor tertinggi di seluruh database saat ini: ' . BarcodeService::getRealMaxBarcodeNumber())
                            ->rules([
                                fn () => function (string $attribute, $value, Closure $fail) {
                                    $realMax = BarcodeService::getRealMaxBarcodeNumber();
                                    if ((int) $value < $realMax) {
                                        $fail("Tidak bisa diset ke {$value} karena sudah ada buku dengan nomor sampai {$realMax}. Nomor terendah yang aman adalah {$realMax}.");
                                    }
                                },
                            ]),
                    ])->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = PengaturanSekolah::current();

        if ($settings) {
            $settings->update([
                'lama_pinjam_buku_hari' => $data['lama_pinjam_buku_hari'],
                'last_barcode_number' => $data['last_barcode_number'] ?? 0,
            ]);

            \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');
            
            // Re-lock the form after successful save
            $this->isUnlocked = false;

            Notification::make()
                ->title('Pengaturan berhasil disimpan')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Pengaturan sekolah belum diinisialisasi')
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        $settings = PengaturanSekolah::current();
        
        return [
            Action::make('unlockSettings')
                ->label('Buka Kunci Pengaturan')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->hidden(fn () => !$settings || !$settings->is_barcode_setup_completed || $this->isUnlocked)
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Keamanan')
                ->modalDescription('Form penomoran barcode saat ini terkunci demi keamanan. Silakan masukkan password akun Anda untuk membukanya.')
                ->form([
                    TextInput::make('password')
                        ->label('Password Akun Anda')
                        ->password()
                        ->required()
                        ->revealable()
                ])
                ->action(function (array $data) {
                    $key = 'unlock-settings:' . auth()->id();
                    
                    if (RateLimiter::tooManyAttempts($key, 5)) {
                        $seconds = RateLimiter::availableIn($key);
                        Notification::make()
                            ->title('Terlalu Banyak Percobaan')
                            ->body("Silakan coba lagi dalam {$seconds} detik.")
                            ->danger()
                            ->send();
                        return;
                    }

                    if (Hash::check($data['password'], auth()->user()->password)) {
                        RateLimiter::clear($key);
                        $this->isUnlocked = true;
                        
                        Notification::make()
                            ->title('Kunci Berhasil Dibuka')
                            ->body('Pengaturan kini dapat diedit untuk sesi ini.')
                            ->success()
                            ->send();
                    } else {
                        RateLimiter::hit($key, 300); // 5 menit
                        Notification::make()
                            ->title('Password Salah')
                            ->body('Autentikasi gagal. Silakan coba lagi.')
                            ->danger()
                            ->send();
                    }
                }),
                
            Action::make('autoSync')
                ->label('Auto-Sync Barcode')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->tooltip('Gunakan setelah import data massal atau jika ada perubahan kode barcode manual, untuk menyesuaikan nomor urut dengan data terbaru.')
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Nomor Barcode')
                ->modalDescription('Tindakan ini akan memindai database untuk mencari nomor barcode fisik terbesar sesuai prefix saat ini dan menyimpannya sebagai urutan terbaru. Gunakan ini setelah import data massal.')
                ->modalSubmitActionLabel('Ya, Sinkronkan Sekarang')
                ->action(function () {
                    BarcodeService::autoSyncBarcodeNumber();
                    $this->mount(); // Refresh the form data
                    
                    Notification::make()
                        ->title('Auto-Sync Berhasil')
                        ->body('Nomor barcode terakhir telah disinkronkan dengan data fisik aktual.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
