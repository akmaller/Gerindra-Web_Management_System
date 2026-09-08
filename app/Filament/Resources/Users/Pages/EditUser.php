<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('admin') || $user->id === $this->record->id),
            403
        );
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->tempRole = $data['role_name'] ?? null;
        unset($data['role_name']);
        return $data;
    }

    protected function afterSave(): void
    {
        if (auth()->user()->hasRole('admin') && !empty($this->tempRole)) {
            $this->record->syncRoles([$this->tempRole]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
