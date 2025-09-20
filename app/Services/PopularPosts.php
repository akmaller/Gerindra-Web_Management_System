<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostView;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class PopularPosts
{
    /**
     * Rekam satu view (didedup per session per post per X menit).
     */
    public static function record(Post $post, int $dedupMinutes = 30): void
    {
        $sessionId = session()->getId();
        $ip = Request::ip();
        $ua = (string) Request::header('User-Agent');

        // Dedup sederhana: 1 hit / post / session / N menit
        $key = sprintf('pv:%s:%s', $post->id, $sessionId);
        if (Cache::has($key)) {
            return;
        }

        PostView::create([
            'post_id' => $post->id,
            'user_id' => optional(auth()->user())->id,
            'session_id' => $sessionId,
            'ip' => $ip,
            'user_agent' => mb_substr($ua, 0, 255),
            'viewed_at' => now(),
        ]);

        // Set dedup TTL
        Cache::put($key, 1, now()->addMinutes($dedupMinutes));

        // (opsional) invalidasi cache popular untuk periode aktif
        Cache::forget('popular:today:5');
        Cache::forget('popular:week:5');
        Cache::forget('popular:month:5');
    }

    /**
     * Ambil popular berdasarkan range.
     * $period: today|week|month|all
     */
    public static function range(string $period = 'week', int $limit = 5)
    {
        [$start, $end] = self::periodBounds($period);

        $cacheKey = "popular:{$period}:{$limit}";
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($start, $end, $limit, $period) {
            $query = PostView::query()
                ->when($period !== 'all', fn($q) => $q->whereBetween('viewed_at', [$start, $end]))
                ->selectRaw('post_id, COUNT(*) as views')
                ->groupBy('post_id')
                ->orderByDesc('views')
                ->limit($limit)
                ->get();

            // Ambil post lengkap + urutkan sesuai agregat
            $posts = Post::with(['category'])
                ->whereIn('id', $query->pluck('post_id'))
                ->get()
                ->keyBy('id');

            // Kembalikan dalam urutan populer
            return $query->map(fn($row) => tap($posts[$row->post_id] ?? null, function ($p) use ($row) {
                if ($p)
                    $p->aggregated_views = (int) $row->views;
            }))->filter()->values();
        });
    }

    protected static function periodBounds(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'week' => [$now->startOfWeek(), $now->endOfWeek()],
            'month' => [$now->startOfMonth(), $now->endOfMonth()],
            default => [CarbonImmutable::minValue(), CarbonImmutable::maxValue()], // all time
        };
    }
}
