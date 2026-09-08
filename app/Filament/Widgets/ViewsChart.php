<?php

namespace App\Filament\Widgets;

use App\Models\PostView;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ViewsChart extends ChartWidget
{
    protected ?string $heading = 'Visitors (7 hari terakhir)';
    protected ?string $pollingInterval = '30s'; // opsional


    protected function getData(): array
    {
        // Range 7 hari (termasuk hari ini)
        $end = now()->endOfDay();
        $start = now()->subDays(6)->startOfDay();

        // Ambil data per hari (pengunjung unik berdasarkan session_id)
        $rows = PostView::query()
            ->selectRaw("
                DATE(COALESCE(viewed_at, created_at)) as day,
                COUNT(DISTINCT session_id) as visitors
            ")
            ->whereBetween(DB::raw('COALESCE(viewed_at, created_at)'), [$start, $end])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('visitors', 'day'); // ['2025-09-12' => 15, ...]

        // Susun lengkap 7 hari (isi 0 kalau tidak ada)
        $labels = [];
        $data = [];
        for ($d = 0; $d < 7; $d++) {
            $date = Carbon::parse($start)->addDays($d)->toDateString();
            $labels[] = Carbon::parse($date)->isoFormat('ddd, D/M'); // contoh: Rab, 17/9
            $data[] = (int) ($rows[$date] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Visitors unik',
                    'data' => $data,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
