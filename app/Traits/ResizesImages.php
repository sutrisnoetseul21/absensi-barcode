<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

trait ResizesImages
{
    /**
     * Resize image in storage to maximum width while keeping aspect ratio.
     * Optionally compress to JPEG.
     */
    protected function resizeImage(string $disk, string $column, int $maxWidth, bool $forceJpeg = false, int $quality = 80): void
    {
        $path = $this->getAttribute($column);

        if (!$path || !Storage::disk($disk)->exists($path)) {
            return;
        }

        $fullPath = Storage::disk($disk)->path($path);
        
        try {
            $manager = new ImageManager(Driver::class);
            $image = $manager->decodePath($fullPath);

            $isModified = false;

            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
                $isModified = true;
            }

            if ($forceJpeg) {
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (!in_array($extension, ['jpg', 'jpeg'])) {
                    // File bukan JPEG, maka konversi dan ubah nama file
                    $newPath = preg_replace('/\.[^.]+$/', '.jpg', $path);
                    $newFullPath = Storage::disk($disk)->path($newPath);
                    
                    // Simpan sebagai JPEG baru
                    $image->save($newFullPath, quality: $quality);
                    
                    // Hapus file lama (PNG/WebP/dll)
                    if ($newPath !== $path && file_exists($newFullPath)) {
                        Storage::disk($disk)->delete($path);
                    }

                    // Update kolom di DB tanpa memicu event saved() lagi
                    $this->updateQuietly([$column => $newPath]);
                    return;
                } else {
                    // Sudah JPEG, cukup kompres & simpan ulang
                    $image->save($fullPath, quality: $quality);
                    return;
                }
            }

            // Jika tidak force JPEG, simpan perubahan hanya jika ada modifikasi (resize)
            if ($isModified) {
                $image->save($fullPath, quality: $quality);
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal resize image [{$column}]: " . $e->getMessage());
        }
    }
}
