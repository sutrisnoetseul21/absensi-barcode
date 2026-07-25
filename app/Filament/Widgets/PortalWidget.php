<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PortalWidget extends Widget
{
    protected string $view = 'filament.widgets.portal-widget';

    protected int | string | array $columnSpan = 'full';

    // Urutan widget di dashboard. Taruh paling atas.
    protected static ?int $sort = -1;
}
