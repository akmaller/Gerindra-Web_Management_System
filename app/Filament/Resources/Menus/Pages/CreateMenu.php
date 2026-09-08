<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;
    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);
    }
}

