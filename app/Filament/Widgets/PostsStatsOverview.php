<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostsStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '30s'; // opsional

    public function getColumns(): int|array
    {
        return [
            'xs' => 1,
            'sm' => 3,
            'md' => 3,
            'lg' => 3,
        ];
    }

    protected int|string|array $columnSpan = [
        'md' => 1, // di layar medium ke atas, ambil 1 kolom (dari total 2)
    ];

    protected function getStats(): array
    {
        $query = PostResource::getEloquentQuery();
        $total = (clone $query)->count();
        $published = (clone $query)->published()->count();
        $draft = (clone $query)->where('status', 'draft')->count();

        return [
            Stat::make('Total berita', number_format($total)),
            Stat::make('Berita tayang', number_format($published)),
            Stat::make('Draf', number_format($draft)),
        ];
    }
}
