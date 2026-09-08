<?php

namespace App\Filament\Resources\AdSettings\Pages;

use App\Filament\Resources\AdSettings\AdSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdSetting extends CreateRecord
{
    protected static string $resource = AdSettingResource::class;
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengaturan iklan dibuat.';
    }
}
