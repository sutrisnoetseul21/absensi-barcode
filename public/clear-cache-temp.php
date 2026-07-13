<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
\Illuminate\Support\Facades\Artisan::call('optimize:clear');
\Illuminate\Support\Facades\Artisan::call('filament:clear-cached-components');
echo "CACHE CLEARED SUCCESSFULLY VIA WEB";
