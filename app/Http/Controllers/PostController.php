<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSeo;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\CompanyProfile;
use App\Services\PopularPosts;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use InteractsWithSeo;

    public function index(Request $request)
    {
        $perPage = 12;

        $posts = Post::with('categories')
            ->published()
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();

        $popularPosts = PopularPosts::range('week', 6);

        $settings = SiteSetting::first();

        $siteName = $settings->site_name ?? config('app.name');
        $pageTitle = __('Semua Berita Terbaru') . ' | ' . $siteName;
        $description = __('Kumpulan berita terbaru dan terpopuler dari berbagai kategori.');
        $shareImage = optional($settings)->logo_url ?: asset('images/example-middle.webp');

        $this->setSeo(
            title: $pageTitle,
            description: $description,
            url: route('posts.index'),
            images: array_filter([$shareImage]),
            options: [
                'site_name' => $siteName,
                'json_ld_type' => 'CollectionPage',
                'json_ld_values' => array_filter([
                    'inLanguage' => app()->getLocale(),
                ]),
            ]
        );

        $this->setBreadcrumbJsonLd([
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => __('Semua Berita Terbaru'), 'url' => route('posts.index')],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'items' => view('post.partials.cards', ['posts' => $posts->items()])->render(),
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }

        return view('post.index', [
            'posts' => $posts,
            'popularPosts' => $popularPosts,
        ]);
    }

    /**
     * /post/{bulan}/{tahun}/{slug}
     * {bulan} bisa "09", "9", "september", "sep" (indo/en)
     */
    public function show(string $bulan, string $tahun, string $slug)
    {
        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();

        // Post
        $post = Post::published()
            ->with(['categories', 'tags', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        // --- Canonical guard: cocokkan {bulan}/{tahun} dengan published_at post ---
        $publishedAt = $post->published_at;

        if ($publishedAt) {
            $bulanAktual = $publishedAt->format('m'); // "01".."12"
            $tahunAktual = $publishedAt->format('Y'); // "2025"

            $bulanReq = $this->normalizeMonth($bulan);       // "01".."12"
            $tahunReq = $this->normalizeYear($tahun);        // "2025"

            if ($bulanReq !== $bulanAktual || $tahunReq !== $tahunAktual) {
                return redirect()->route('posts.show', [
                    'bulan' => $bulanAktual,
                    'tahun' => $tahunAktual,
                    'slug' => $post->slug,
                ], 301);
            }
        }

        $postUrl = $publishedAt
            ? route('posts.show', [
                'tahun' => $publishedAt->format('Y'),
                'bulan' => $publishedAt->format('m'),
                'slug' => $post->slug,
            ])
            : url()->current();

        $siteName = $settings->site_name ?? config('app.name');
        $description = Str::limit(strip_tags($post->content), 160);
        $ogImage = $post->og_image_url;
        $keywords = $post->tags->pluck('name')->implode(', ');
        $wordCount = str_word_count(strip_tags((string) $post->content));
        $wordCount = $wordCount > 0 ? $wordCount : null;
        $authorName = optional($post->author)->name;

        $jsonLdValues = array_filter([
            'headline' => $post->title,
            'articleSection' => $post->primary_category?->name,
            'datePublished' => optional($publishedAt)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'keywords' => $keywords,
            'wordCount' => $wordCount,
            'mainEntityOfPage' => $postUrl,
            'author' => $authorName ? [
                '@type' => 'Person',
                'name' => $authorName,
            ] : null,
        ], function ($value) {
            if (is_array($value)) {
                return ! empty($value);
            }

            if (is_numeric($value)) {
                return $value > 0;
            }

            return $value !== null && $value !== '';
        });

        $this->setSeo(
            title: $post->title . ' | ' . $siteName,
            description: $description,
            url: $postUrl,
            images: array_filter([$ogImage]),
            options: [
                'site_name' => $siteName,
                'twitter_site' => $profile?->twitter,
                'json_ld_type' => 'Article',
                'json_ld_values' => $jsonLdValues,
            ]
        );

        $breadcrumb = [
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => __('Semua Berita Terbaru'), 'url' => route('posts.index')],
        ];

        if ($post->primary_category) {
            $breadcrumb[] = [
                'name' => $post->primary_category->name,
                'url' => route('category.show', $post->primary_category->slug),
            ];
        }

        $breadcrumb[] = [
            'name' => $post->title,
            'url' => $postUrl,
        ];

        $this->setBreadcrumbJsonLd($breadcrumb);

        // Rekam view (dedup 30 menit)
        PopularPosts::record($post, 30);

        // Populer (silakan pilih mana yang mau dipakai di sidebar)
        $popularToday = PopularPosts::range('today', 5);
        $popularWeek = PopularPosts::range('week', 5);
        $popularPosts = $popularWeek;

        // Prev/Next berdasarkan published_at
        $prev = Post::published()
            ->where('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->first();

        $next = Post::published()
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->first();

        // Terbaru (opsional)
        $latest = Post::published()
            ->latest('published_at')
            ->limit(5)
            ->get();

        $latestPosts = Post::published()->latest('published_at')->limit(4)->get();
        $related = Post::published()
            ->whereKeyNot($post->getKey())
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $post->categories->pluck('id')))
            ->latest('published_at')
            ->limit(6)
            ->get();

        return view('post.show', compact(
            'post',
            'prev',
            'next',
            'latest',
            'popularPosts',
            'popularToday',
            'popularWeek',
            'settings',
            'profile',
            'latestPosts',
            'related'
        ));
    }

    /**
     * Normalisasi bulan jadi "01".."12"
     * Terima angka "9"/"09" atau nama "september"/"sep" (indo/en).
     */
    private function normalizeMonth(string $bulan): string
    {
        $s = mb_strtolower(trim($bulan));

        // jika numeric
        if (preg_match('/^\d{1,2}$/', $s)) {
            $n = max(1, min(12, (int) $s));
            return str_pad((string) $n, 2, '0', STR_PAD_LEFT);
        }

        // mapping nama bulan (indo + english)
        $map = [
            'januari' => 1,
            'jan' => 1,
            'january' => 1,
            'februari' => 2,
            'feb' => 2,
            'february' => 2,
            'maret' => 3,
            'mar' => 3,
            'march' => 3,
            'april' => 4,
            'apr' => 4,
            'mei' => 5,
            'may' => 5,
            'juni' => 6,
            'jun' => 6,
            'june' => 6,
            'juli' => 7,
            'jul' => 7,
            'july' => 7,
            'agustus' => 8,
            'agu' => 8,
            'ags' => 8,
            'aug' => 8,
            'august' => 8,
            'september' => 9,
            'sep' => 9,
            'sept' => 9,
            'oktober' => 10,
            'okt' => 10,
            'oct' => 10,
            'october' => 10,
            'november' => 11,
            'nov' => 11,
            'desember' => 12,
            'des' => 12,
            'dec' => 12,
            'december' => 12,
        ];

        if (isset($map[$s])) {
            return str_pad((string) $map[$s], 2, '0', STR_PAD_LEFT);
        }

        // fallback aman
        return '01';
    }

    /** Normalisasi tahun 2/4 digit ke 4 digit */
    private function normalizeYear(string $tahun): string
    {
        $t = trim($tahun);
        if (preg_match('/^\d{4}$/', $t)) {
            return $t;
        }
        if (preg_match('/^\d{2}$/', $t)) {
            // asumsikan 20xx
            return '20' . $t;
        }
        // fallback
        return now()->format('Y');
    }
}
