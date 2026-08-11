<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SchedulerHeartbeatCommand extends Command
{
    protected $signature = 'scheduler:heartbeat';
    protected $description = 'Menulis timestamp heartbeat untuk membuktikan scheduler aktif berjalan';

    public function handle(): void
    {
        $path = storage_path('framework/schedule-heartbeat');
        file_put_contents($path, time());
        $this->info('Heartbeat ditulis: ' . now()->toDateTimeString());
    }
}
