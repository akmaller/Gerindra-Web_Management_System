<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSeo;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    use InteractsWithSeo;

    public function index(Request $request)
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $q = trim($validated['q'] ?? '');
        $settings = SiteSetting::first();
        $siteName = $settings->site_name ?? config('app.name');
        $logoPath = optional($settings)->logo_url;
        $shareImage = $logoPath ? asset($logoPath) : null;

        $searchLabel = $q !== ''
            ? __('Cari: :query', ['query' => $q])
            : __('Pencarian');

        $description = $q !== ''
            ? __('Hasil pencarian untuk ":query".', ['query' => $q])
            : __('Cari berita dan halaman di :site.', ['site' => $siteName]);

        $this->setSeo(
            title: $searchLabel.' | '.$siteName,
            description: $description,
            url: url()->full(),
            images: array_filter([$shareImage]),
            options: [
                'site_name' => $siteName,
                'json_ld_type' => $q !== '' ? 'SearchResultsPage' : 'WebPage',
                'json_ld_name' => $searchLabel,
                'json_ld_values' => [
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => route('search', ['q' => '{search_term_string}']),
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ]
        );

        $this->setBreadcrumbJsonLd([
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => $searchLabel, 'url' => url()->full()],
        ]);

        $results = new LengthAwarePaginator([], 0, 12);
        // kalau kosong / terlalu pendek, langsung tampilkan form kosong
        if (mb_strlen($q) < 2) {
            return view('search.index', [
                'q' => $q,

                'results' => new LengthAwarePaginator([], 0, 12),
            ]);
        }

        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q).'%';
        $matches = function ($query) use ($pattern) {
            $query->whereRaw("title LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("content LIKE ? ESCAPE '!'", [$pattern]);
        };
        $posts = Post::published()->where($matches)
            ->selectRaw("id, title, slug, thumbnail, content, published_at as date, 'post' as type");
        $pages = Page::where('is_active', true)->where($matches)
            ->selectRaw("id, title, slug, thumbnail, content, updated_at as date, 'page' as type");

        $results = DB::query()
            ->fromSub($posts->unionAll($pages), 'search_results')
            ->orderByDesc('date')->orderBy('type')->orderByDesc('id')
            ->paginate(12)->withQueryString();
        $results->through(function ($item) {
            $date = Carbon::parse($item->date);

            return [
                'type' => $item->type,
                'title' => $item->title,
                'excerpt' => Str::limit(strip_tags($item->content ?? ''), 180),
                'date' => $date,
                'thumb' => $item->thumbnail ? asset('storage/'.$item->thumbnail) : null,
                'badge' => $item->type === 'post' ? 'Berita' : 'Halaman',
                'url' => $item->type === 'post'
                    ? route('posts.show', ['tahun' => $date->format('Y'), 'bulan' => $date->format('m'), 'slug' => $item->slug])
                    : route('pages.show', $item->slug),
            ];
        });

        return view('search.index', [
            'q' => $q,
            'results' => $results,
        ]);
    }
}
