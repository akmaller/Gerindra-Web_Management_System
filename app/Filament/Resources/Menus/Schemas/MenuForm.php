<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Models\Menu;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section; // container v4
use Filament\Forms;                      // fields
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;

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
                            ->options(fn (Get $get, ?Menu $record) => static::parentOptions($get('location') ?? $record?->location, $record))
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
                            ->required()
                            ->live(),
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

    protected static function parentOptions(?string $location, ?Menu $current = null): array
    {
        if (! $location) {
            $location = 'header';
        }

        $menus = Menu::query()
            ->where('location', $location)
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $excludeIds = [];

        if ($current) {
            $excludeIds = array_merge([$current->id], static::descendantIds($current));
        }

        $options = [];
        static::buildOptions($menus, null, '', $options, $excludeIds);

        return $options;
    }

    protected static function buildOptions(Collection $menus, ?int $parentId, string $prefix, array &$options, array $excludeIds): void
    {
        $menus
            ->filter(fn (Menu $menu) => $menu->parent_id === $parentId)
            ->sortBy(fn (Menu $menu) => [$menu->sort_order, $menu->label])
            ->each(function (Menu $menu) use ($menus, $prefix, &$options, $excludeIds): void {
                if (in_array($menu->id, $excludeIds, true)) {
                    return;
                }

                $options[$menu->id] = $prefix . $menu->label;

                static::buildOptions($menus, $menu->id, $prefix . '— ', $options, $excludeIds);
            });
    }

    protected static function descendantIds(Menu $menu): array
    {
        return Menu::query()
            ->where('parent_id', $menu->id)
            ->get()
            ->flatMap(fn (Menu $child) => array_merge([$child->id], static::descendantIds($child)))
            ->all();
    }
}
