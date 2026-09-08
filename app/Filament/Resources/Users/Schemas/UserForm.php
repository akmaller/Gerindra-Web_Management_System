<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(
                        ignorable: fn (?User $record) => $record
                    ),

                Forms\Components\TextInput::make('current_password')
                    ->label('Password saat ini')->password()
                    ->visible(fn (?User $record) => $record?->id === auth()->id())
                    ->required(fn (callable $get, ?User $record) => $record?->id === auth()->id() && filled($get('password')))
                    ->rules(['nullable', 'current_password:web'])->dehydrated(false),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->minLength(12)
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),

                // field roles hanya muncul untuk admin
                Forms\Components\Select::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->required()
                    ->native(false)
                    ->multiple()
                    ->preload()
                    ->visible(fn () => auth()->user()?->hasRole('admin'))
                    ->default(fn () => Role::where('name', 'penulis')->where('guard_name', 'web')->pluck('id')->all())
                    ->helperText('Hanya admin yang dapat mengubah peran pengguna.'),
            ]);
    }
}
