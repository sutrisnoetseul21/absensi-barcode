<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('webprofil:fix-disk', function () {
    $files = Storage::disk('local')->files('web-profil');
    $this->info('Files found: ' . count($files));
    foreach ($files as $file) {
        $contents = Storage::disk('local')->get($file);
        Storage::disk('public')->put($file, $contents);
        // Set world-readable permission on the new file
        $newPath = Storage::disk('public')->path($file);
        chmod($newPath, 0644);
        $this->info('Copied: ' . $file);
    }
    // Also fix the directory permission
    $dir = Storage::disk('public')->path('web-profil');
    if (is_dir($dir)) {
        chmod($dir, 0755);
    }
    $this->info('Done!');
})->purpose('Copy web-profil files from private to public disk');
