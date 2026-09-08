<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSeo;
use App\Models\Post;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    use InteractsWithSeo;

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
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
            title: $searchLabel . ' | ' . $siteName,
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

        // --- Posts (published) ---
        $postQuery = Post::query()
            ->published()                             // asumsi sudah ada scope published()
            ->where('published_at', '<=', now())
            ->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            })
            ->with(['categories:id,name,slug'])
            ->select(['id', 'title', 'slug', 'thumbnail', 'category_id', 'published_at', 'content']);

        // --- Pages (published/active) ---
        $pageQuery = Page::query()
            ->where('is_active', true)
            ->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            })
            ->select(['id', 'title', 'slug', 'content', 'updated_at']);

        // eksekusi dan ubah ke bentuk unified
        $posts = $postQuery->get()->map(function ($p) {
            return [
                'type' => 'post',
                'title' => $p->title,
                'excerpt' => Str::limit(strip_tags($p->content), 180),
                'date' => $p->published_at,
                'thumb' => $p->thumbnail ? asset('storage/' . $p->thumbnail) : null,
                'badge' => $p->primary_category?->name ?? 'Berita',
                'url' => route('posts.show', [
                    'tahun' => $p->published_at?->format('Y'),
                    'bulan' => $p->published_at?->format('m'),
                    'slug' => $p->slug,
                ]),
            ];
        });

        $pages = $pageQuery->get()->map(function ($p) {
            return [
                'type' => 'page',
                'title' => $p->title,
                'excerpt' => Str::limit(strip_tags($p->content), 180),
                'date' => $p->updated_at,
                'thumb' => null,
                'badge' => 'Halaman',
                'url' => route('pages.show', $p->slug),
            ];
        });

        // gabung + sort desc by date
        $collection = $posts->concat($pages)
            ->sortByDesc('date')
            ->values();

        // manual pagination untuk mixed collection
        $perPage = 12;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $slice = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $results = new LengthAwarePaginator(
            $slice,
            $collection->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => ['q' => $q]]
        );

        return view('search.index', [
            'q' => $q,
            'results' => $results,
        ]);
    }
}
