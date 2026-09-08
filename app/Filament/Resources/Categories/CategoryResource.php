<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms;
use Filament\Tables\Table;
use Filament\Tables;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    // tampil di grup "Post"
    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Post';
    }
    public static function getNavigationSort(): int
    {
        return '2';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-tag';
    }

    protected static ?string $navigationLabel = 'Kategori';
    protected static ?string $slug = 'categories';
    protected static ?string $recordTitleAttribute = 'name';

    // (opsional) hanya admin yang melihat menu ini
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['admin', 'editor']) ?? false;
    }

    /** FORM (Schemas API) */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', \Str::slug($state)))
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->disabled()
                    ->dehydrated() // tetap kirim ke server meski disabled
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif?')
                    ->default(true),

                // Forms\Components\TextInput::make('sort_order')
                //     ->label('Urutan')
                //     ->numeric()
                //     ->default(0)
                //     ->helperText('Angka lebih kecil tampil lebih awal.'),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    /** TABLE (klik baris untuk edit; tanpa actions bawaan agar aman) */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->copyable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                // Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->since(),
            ])
            // ->defaultSort('sort_order')
            ->recordUrl(fn($record) => static::getUrl('edit', ['record' => $record]))
            ->bulkActions([]); // kosong dulu sesuai stack-mu
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
