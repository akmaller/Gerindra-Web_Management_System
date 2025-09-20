<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostsStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '30s'; // opsional
    public function getColumns(): int|array
    {
        return [
            'xs' => 3,
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
        $total = Post::query()->count();
        $published = Post::query()
            ->where('status', 'published')
            ->count();
        $draft = Post::query()
            ->where('status', 'draft')
            ->count();

        return [
            Stat::make('Total Posts', number_format($total)),
            Stat::make('Post Tayang', number_format($published)),
            Stat::make('Post Draft', number_format($draft)),
        ];
    }
}
