<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // pindahkan role_name keluar agar tidak dianggap kolom
        $this->tempRole = $data['role_name'] ?? 'penulis';
        unset($data['role_name']);

        // password sudah auto-hashed oleh cast 'hashed' di model User kamu
        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->tempRole)) {
            $this->record->syncRoles([$this->tempRole]);
        }
    }
}
