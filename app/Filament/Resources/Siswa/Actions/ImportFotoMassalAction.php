<?php

namespace App\Filament\Resources\Siswa\Actions;

use Filament\Actions\Action;

class ImportFotoMassalAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'import_foto_massal';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Upload Foto Massal (ZIP)')
            ->icon('heroicon-o-photo')
            ->color('info')
            ->modalHeading('Upload Foto Massal (ZIP)')
            ->modalDescription('Unggah file .zip yang berisi foto-foto siswa (.jpg, .jpeg, .png). Pastikan nama file foto adalah NISN siswa (contoh: 1234567890.jpg). Sistem akan otomatis memperkecil ukuran foto dan menimpa foto lama.')
            ->form([
                \Filament\Forms\Components\FileUpload::make('zip_file')
                    ->label('Pilih File ZIP')
                    ->disk('local')
                    ->directory('imports/zip')
                    ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'application/x-zip'])
                    ->required(),
            ])
            ->action(function (array $data) {
                $zipPath = storage_path('app/private/' . $data['zip_file']);
                if (!file_exists($zipPath)) {
                    $zipPath = storage_path('app/' . $data['zip_file']);
                }

                $zip = new \ZipArchive;
                if ($zip->open($zipPath) === true) {
                    $extractPath = storage_path('app/private/tmp/zip_extract_' . time());
                    $zip->extractTo($extractPath);
                    $zip->close();

                    $countSuccess = 0;
                    $countIgnored = 0;

                    $files = \Illuminate\Support\Facades\File::allFiles($extractPath);
                    
                    $imageManager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

                    foreach ($files as $file) {
                        $ext = strtolower($file->getExtension());
                        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            $nisn = $file->getFilenameWithoutExtension();
                            
                            $siswa = \App\Models\Siswa::where('nisn', $nisn)->first();
                            if ($siswa) {
                                try {
                                    $image = $imageManager->decode($file->getPathname());
                                    
                                    // Scale down proportionally so max dimension is 500
                                    $image->scaleDown(width: 500, height: 500);

                                    $newFilename = \Illuminate\Support\Str::random(24) . '.' . $ext;
                                    $destPath = storage_path('app/public/siswa-photos/' . $newFilename);
                                    
                                    if (!file_exists(storage_path('app/public/siswa-photos'))) {
                                        mkdir(storage_path('app/public/siswa-photos'), 0755, true);
                                    }

                                    if ($ext === 'png') {
                                        $image->save($destPath);
                                    } else {
                                        $image->save($destPath, 80);
                                    }

                                    // Delete old photo if exists
                                    if ($siswa->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($siswa->photo_path)) {
                                        \Illuminate\Support\Facades\Storage::disk('public')->delete($siswa->photo_path);
                                    }

                                    $siswa->update([
                                        'photo_path' => 'siswa-photos/' . $newFilename,
                                    ]);
                                    $countSuccess++;
                                } catch (\Exception $e) {
                                    $countIgnored++;
                                }
                            } else {
                                $countIgnored++;
                            }
                        }
                    }

                    // Clean up
                    \Illuminate\Support\Facades\File::deleteDirectory($extractPath);
                    @unlink($zipPath);

                    \Filament\Notifications\Notification::make()
                        ->title('Import Foto Selesai')
                        ->body("Berhasil memperbarui **{$countSuccess}** foto. Diabaikan/Tidak Ditemukan: **{$countIgnored}**.")
                        ->success()
                        ->send();
                } else {
                    \Filament\Notifications\Notification::make()->title('Gagal membuka file ZIP')->danger()->send();
                }
            });
    }
}
