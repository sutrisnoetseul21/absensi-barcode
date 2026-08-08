<?php

namespace App\Filament\Perpustakaan\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use App\Models\EksemplarBuku;
use App\Models\PengaturanSekolah;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function mount(): void
    {
        $settings = PengaturanSekolah::current();
        
        // Show only if setup not completed
        if ($settings && !$settings->is_barcode_setup_completed) {
            $user = auth()->user();
            
            // Check if user is Super Admin or Admin Perpus
            // Only admins should see this wizard, not just Petugas
            $isAdmin = $user->isSuperAdmin() || $user->hasRole('super_admin') || 
                       $user->roles->contains(fn($r) => str_starts_with($r->name, 'admin_'));
            
            \Illuminate\Support\Facades\Log::info('Dashboard mount check:', [
                'user' => $user->name,
                'isAdmin' => $isAdmin,
                'setup_completed' => $settings->is_barcode_setup_completed
            ]);

            if ($isAdmin) {
                $this->mountAction('setupBarcode');
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('setupBarcode')
                ->label('Setup Awal Perpustakaan')
                ->icon('heroicon-o-cog')
                ->hidden(function() {
                    $settings = PengaturanSekolah::current();
                    return !$settings || $settings->is_barcode_setup_completed;
                })
                ->modalHeading('Selamat Datang di Sistem Perpustakaan!')
                ->modalDescription('Sebelum mulai menginput buku, silakan atur sistem penomoran barcode eksemplar Anda.')
                ->modalSubmitActionLabel('Simpan Pengaturan & Mulai')
                ->modalCancelAction(false) // No cancel button, force setup
                ->closeModalByClickingAway(false)
                ->closeModalByEscaping(false)
                ->form([
                    Radio::make('setup_choice')
                        ->label('Metode Penomoran Barcode')
                        ->options([
                            'baru' => 'Perpustakaan Baru (Mulai dari Nomor 1)',
                            'migrasi' => 'Migrasi Data Lama dari SLiMS (Import Excel)',
                            'manual' => 'Lanjutkan Manual (Mulai dari Nomor Tertentu)',
                        ])
                        ->required()
                        ->live(),
                        
                    TextInput::make('manual_start_number')
                        ->label('Lanjutkan dari Nomor')
                        ->numeric()
                        ->required()
                        ->hidden(fn (Get $get) => $get('setup_choice') !== 'manual')
                        ->helperText('Masukkan nomor barcode tertinggi yang sudah ada secara fisik. Buku baru akan otomatis melanjutkan urutan setelah nomor ini.')
                ])
                ->action(function (array $data) {
                    $settings = PengaturanSekolah::current();
                    
                    if ($data['setup_choice'] === 'baru') {
                        $settings->update([
                            'last_barcode_number' => 0,
                            'is_barcode_setup_completed' => true
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Setup Selesai')
                            ->success()
                            ->send();
                    } elseif ($data['setup_choice'] === 'migrasi') {
                        // Redirect to Import SLiMS without completing setup
                        // Wizard will appear again if they don't finish the import
                        return redirect()->to(\App\Filament\Perpustakaan\Pages\ImportSlims::getUrl());
                    } elseif ($data['setup_choice'] === 'manual') {
                        $settings->update([
                            'last_barcode_number' => $data['manual_start_number'],
                            'is_barcode_setup_completed' => true
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Setup Manual Selesai')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }
}
