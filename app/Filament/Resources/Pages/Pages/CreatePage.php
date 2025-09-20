<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);
    }
}
