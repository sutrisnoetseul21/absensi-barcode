<?php

namespace App\Filament\Perpustakaan\Resources\KlasifikasiDdcs\Pages;

use App\Filament\Perpustakaan\Resources\KlasifikasiDdcs\KlasifikasiDdcResource;
use App\Models\KlasifikasiDdc;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;

class ManageKlasifikasiDdcs extends ManageRecords
{
    protected static string $resource = KlasifikasiDdcResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    $csvData = "kode_ddc,kategori\n000,Karya Umum / Komputer\n100,Filsafat & Psikologi\n200,Agama\n297,Agama Islam\n300,Ilmu-ilmu Sosial\n370,Pendidikan\n400,Bahasa & Linguistik\n500,Sains & Matematika\n510,Matematika\n600,Teknologi & Ilmu Terapan\n700,Kesenian & Olahraga\n800,Kesusastraan & Sastra\n900,Sejarah & Geografi\n";
                    
                    return response()->streamDownload(function () use ($csvData) {
                        echo $csvData;
                    }, 'template_klasifikasi_ddc.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),
            Action::make('importCsv')
                ->label('Import CSV / Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file_csv')
                        ->label('Pilih File CSV / Excel')
                        ->helperText('Upload file berformat .csv dengan kolom header: kode_ddc, kategori. Anda bisa mengunduh template terlebih dahulu.')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data, Action $action) {
                    $filePath = Storage::disk('local')->path($data['file_csv']);

                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->danger()
                            ->title('File Tidak Ditemukan')
                            ->send();
                        $action->halt();
                    }

                    $inserted = 0;
                    $updated = 0;
                    $handle = fopen($filePath, 'r');

                    if ($handle !== false) {
                        $header = fgetcsv($handle, 1000, ',');
                        
                        // Normalisasi header
                        $kodeIndex = false;
                        $kategoriIndex = false;

                        if ($header) {
                            foreach ($header as $idx => $col) {
                                $cleanCol = strtolower(trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $col)));
                                if (in_array($cleanCol, ['kode_ddc', 'kode', 'kode ddc'])) {
                                    $kodeIndex = $idx;
                                } elseif (in_array($cleanCol, ['kategori', 'nama_kategori', 'subjek', 'nama'])) {
                                    $kategoriIndex = $idx;
                                }
                            }
                        }

                        // Fallback jika tidak ada header
                        if ($kodeIndex === false && $kategoriIndex === false) {
                            $kodeIndex = 0;
                            $kategoriIndex = 1;
                        }

                        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                            if (count($row) < 2) continue;
                            
                            $kodeDdc = trim($row[$kodeIndex] ?? '');
                            $kategori = trim($row[$kategoriIndex] ?? '');

                            if (!empty($kodeDdc) && !empty($kategori)) {
                                $record = KlasifikasiDdc::updateOrCreate(
                                    ['kode_ddc' => $kodeDdc],
                                    ['kategori' => $kategori]
                                );

                                if ($record->wasRecentlyCreated) {
                                    $inserted++;
                                } else {
                                    $updated++;
                                }
                            }
                        }
                        fclose($handle);
                    }

                    // Hapus file temporary import
                    @unlink($filePath);

                    Notification::make()
                        ->success()
                        ->title('Import Berhasil')
                        ->body("Berhasil memproses data DDC: {$inserted} data baru ditambahkan, {$updated} data diperbarui.")
                        ->send();
                }),
        ];
    }
}
