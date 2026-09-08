<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\WidgetConfiguration;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return array_values(array_filter(
            parent::getWidgets(),
            static function ($widget): bool {
                if ($widget === AccountWidget::class) {
                    return false;
                }

                if ($widget instanceof WidgetConfiguration && $widget->widget === AccountWidget::class) {
                    return false;
                }

                return true;
            },
        ));
    }
}
