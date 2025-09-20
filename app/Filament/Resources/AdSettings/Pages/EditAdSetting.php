<?php

namespace App\Filament\Resources\AdSettings\Pages;

use App\Filament\Resources\AdSettings\AdSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdSetting extends EditRecord
{
    protected static string $resource = AdSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pengaturan iklan disimpan.';
    }
}
