<?php

namespace App\Filament\Perpustakaan\Pages;

use App\Models\PengaturanSekolah;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

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

    public function mount(): void
    {
        $settings = PengaturanSekolah::current();
        if ($settings) {
            $this->form->fill([
                'lama_pinjam_buku_hari' => $settings->lama_pinjam_buku_hari,
                'barcode_scan_mode' => $settings->barcode_scan_mode ?? 'nisn',
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
                    ])
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
            ]);

            \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');

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
