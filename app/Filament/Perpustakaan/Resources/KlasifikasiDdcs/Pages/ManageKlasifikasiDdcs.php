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
            CreateAction::make()
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')),
            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor'))
                ->action(function () {
                    $csvData = "kode_ddc,kategori\n000,Karya Umum / Komputer\n100,Filsafat & Psikologi\n200,Agama\n297,Agama Islam\n300,Ilmu-ilmu Sosial\n370,Pendidikan\n400,Bahasa & Linguistik\n500,Sains & Matematika\n510,Matematika\n600,Teknologi & Ilmu Terapan\n700,Kesenian & Olahraga\n800,Kesusastraan & Sastra\n900,Sejarah & Geografi\n";
                    
                    return response()->streamDownload(function () use ($csvData) {
                        echo $csvData;
                    }, 'template_klasifikasi_ddc.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),
            Action::make('importXls')
                ->label('Import DDC dari XLS')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor'))
                ->form([
                    FileUpload::make('file_xls')
                        ->label('Pilih File Excel DDC dari SLiMS')
                        ->helperText('Gunakan file .xlsx yang didownload dari menu Import SLiMS.')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data, Action $action) {
                    $filePath = Storage::disk('local')->path($data['file_xls']);

                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->danger()
                            ->title('File Tidak Ditemukan')
                            ->send();
                        $action->halt();
                    }

                    $import = new \App\Imports\SlimsDdcImport();
                    \Maatwebsite\Excel\Facades\Excel::import($import, $filePath);

                    // Hapus file temporary import
                    @unlink($filePath);

                    $msg = "✅ Baru: {$import->baru} | 🔄 Update: {$import->update} | ⏭️ Skipped: {$import->skipped} | ❌ Error: {$import->errors}";

                    $notif = Notification::make()
                        ->success()
                        ->title('Import DDC Selesai')
                        ->body($msg);
                        
                    // Tambahkan keterangan baris yang diskip (maksimal 10 baris agar tidak kepanjangan)
                    if ($import->skipped > 0 && !empty($import->skippedRows)) {
                        $skippedDetail = implode('<br>', array_slice($import->skippedRows, 0, 10));
                        if (count($import->skippedRows) > 10) {
                            $skippedDetail .= '<br>...dan lainnya';
                        }
                        $notif->body($msg . '<br><br><b>Detail baris di-skip:</b><br>' . $skippedDetail);
                    }
                    
                    $notif->send();
                }),
        ];
    }
}
