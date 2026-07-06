<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\TahunAjaran;
use App\Services\PresensiRekapService;

class ProblematicStudentsTable extends Widget
{
    protected string $view = 'filament.widgets.problematic-students-table';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    
    public $alerts = [];

    public function mount()
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            return;
        }

        $now = now('Asia/Jakarta');
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $service = app(PresensiRekapService::class);
        $this->alerts = $service->getGlobalAlerts($activeYear->id, $startOfMonth, $endOfMonth)->toArray();
    }
}
