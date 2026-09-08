<?php

namespace App\Filament\Resources\AdSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('header_enabled')
                    ->required(),
                Textarea::make('header_html')
                    ->columnSpanFull(),
                Toggle::make('sidebar_enabled')
                    ->required(),
                Textarea::make('sidebar_html')
                    ->columnSpanFull(),
                Toggle::make('below_post_enabled')
                    ->required(),
                Textarea::make('below_post_html')
                    ->columnSpanFull(),
                Toggle::make('footer_enabled')
                    ->required(),
                Textarea::make('footer_html')
                    ->columnSpanFull(),
            ]);
    }
}
