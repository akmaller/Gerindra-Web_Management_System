<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RckInfoWidget extends Widget
{
    protected string $view = 'filament.widgets.rck-info-widget';
    protected int|string|array $columnSpan = [
        'md' => 1, // di layar medium ke atas, ambil 1 kolom (dari total 2)
    ];
}
