<?php

namespace App\Filament\Resources\AdSettings\Pages;

use App\Filament\Resources\AdSettings\AdSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdSettings extends ListRecords
{
    protected static string $resource = AdSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
