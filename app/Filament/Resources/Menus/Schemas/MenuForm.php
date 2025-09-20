<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section; // container v4
use Filament\Forms;                      // fields
use Filament\Forms\Get;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ========== Informasi dasar ==========
                Section::make('Informasi Menu')
                    ->schema([
                        // Parent (submenu) – hanya pilih top-level sebagai induk
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent')
                            ->options(fn() => \App\Models\Menu::query()
                                ->whereNull('parent_id')
                                ->orderBy('sort_order')
                                ->pluck('label', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Kosongkan untuk menu utama.'),

                        Forms\Components\TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('location')
                            ->label('Posisi')
                            ->options(['header' => 'Header', 'footer' => 'Footer'])
                            ->default('header')
                            ->required(),
                    ])
                    ->columns(3),

                // ========== Target ==========
                Section::make('Target')
                    ->schema([
                        Forms\Components\Radio::make('item_type')
                            ->label('Jenis Target')
                            ->options([
                                'category' => 'Kategori',
                                'page' => 'Halaman',
                                'url' => 'URL Kustom',
                            ])
                            ->inline()
                            ->live()
                            ->required(),

                        // Kategori (dropdown)
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => $get('item_type') === 'category')
                            ->required(fn($get) => $get('item_type') === 'category')
                            ->native(false),

                        // Halaman (dropdown)
                        Forms\Components\Select::make('page_id')
                            ->label('Halaman')
                            ->relationship('page', 'title')
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => $get('item_type') === 'page')
                            ->required(fn($get) => $get('item_type') === 'page')
                            ->native(false),

                        // URL kustom
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->default('/')
                            ->maxLength(2048)
                            ->visible(fn($get) => $get('item_type') === 'url'),
                    ]),

                // ========== Tampilan ==========
                Section::make('Tampilan')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif?')
                            ->default(true),

                        Forms\Components\Toggle::make('open_in_new_tab')
                            ->label('Buka di tab baru?')
                            ->default(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->rules(['integer', 'min:0'])
                            ->helperText('Angka lebih kecil tampil lebih awal.'),
                    ])
                    ->columns(3),
            ]);
    }
}
