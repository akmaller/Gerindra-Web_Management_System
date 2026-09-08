<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // gunakan getter untuk icon & group agar tidak bentrok tipe parent
    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-users'; // atau gunakan Heroicon enum jika mau
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Akun';
    }

    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $slug = 'users';

    // judul record (mis. untuk breadcrumbs)
    protected static ?string $recordTitleAttribute = 'name';

    /** Form (Schemas API v4) */
    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    /** Table */
    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    /** Tampilkan menu hanya untuk admin */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }
}
