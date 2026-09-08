<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;

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
                        ignorable: fn(?User $record) => $record
                    ),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn(string $operation) => $operation === 'create')
                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state)),

                // field roles hanya muncul untuk admin
                Forms\Components\Select::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->required()
                    ->native(false)
                    ->multiple()
                    ->preload()
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->default(fn($record) => $record?->getRoleNames()->first() ?? 'penulis')
                    ->helperText('Hanya admin yang dapat mengubah peran pengguna.'),
            ]);
    }
}
