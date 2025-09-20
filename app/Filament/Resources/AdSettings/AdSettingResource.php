<?php

namespace App\Filament\Resources\AdSettings;

use App\Filament\Resources\AdSettings\Pages\CreateAdSetting;
use App\Filament\Resources\AdSettings\Pages\EditAdSetting;
use App\Filament\Resources\AdSettings\Pages\ListAdSettings;
use App\Models\AdSetting;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;

class AdSettingResource extends Resource
{
    protected static ?string $model = AdSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'AdSettingResource';
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-rectangle-group';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationSort(): int
    {
        return '5';
    }


    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Iklan: Bawah Header')->schema([
                Toggle::make('header_enabled')->label('Aktifkan'),
                Textarea::make('header_html')
                    ->label('Kode Iklan (HTML/JS)')
                    ->rows(8)
                    ->helperText('Tempelkan script/HTML dari AdSense atau jaringan iklan lain.'),
            ]),

            Section::make('Iklan: Sidebar')->schema([
                Toggle::make('sidebar_enabled')->label('Aktifkan'),
                Textarea::make('sidebar_html')->label('Kode Iklan (HTML/JS)')->rows(8),
            ]),

            Section::make('Iklan: Bawah Post')->schema([
                Toggle::make('below_post_enabled')->label('Aktifkan'),
                Textarea::make('below_post_html')->label('Kode Iklan (HTML/JS)')->rows(8),
            ]),

            Section::make('Iklan: Footer')->schema([
                Toggle::make('footer_enabled')->label('Aktifkan'),
                Textarea::make('footer_html')->label('Kode Iklan (HTML/JS)')->rows(8),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('header_enabled')->label('Hdr')->boolean(),
                IconColumn::make('sidebar_enabled')->label('Sdb')->boolean(),
                IconColumn::make('below_post_enabled')->label('Post')->boolean(),
                IconColumn::make('footer_enabled')->label('Ftr')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Diubah')->dateTime()->sortable(),
            ])
            ->recordUrl(fn($record) => static::getUrl('edit', ['record' => $record]))
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdSettings::route('/'),
            'create' => CreateAdSetting::route('/create'),
            'edit' => EditAdSetting::route('/{record}/edit'),
        ];
    }
}
