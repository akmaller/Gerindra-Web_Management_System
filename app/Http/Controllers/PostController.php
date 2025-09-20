<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\CompanyProfile;
use App\Services\PopularPosts;
use Illuminate\Support\Str;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;

        $posts = Post::with('category')
            ->published()
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();

        $popularPosts = PopularPosts::range('week', 6);

        $settings = SiteSetting::first();

        SEOTools::setTitle(__('Semua Berita Terbaru') . ' | ' . ($settings->site_name ?? config('app.name')));
        SEOTools::setDescription(__('Kumpulan berita terbaru dan terpopuler dari berbagai kategori.'));
        SEOTools::setCanonical(route('posts.index'));

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

        // Post
        $post = Post::published()
            ->with(['category', 'tags']) // hapus/ubah kalau relasi berbeda
            ->where('slug', $slug)
            ->firstOrFail();

        // SEO
        SEOTools::setTitle($post->title . ' | ' . $settings->site_name);
        SEOTools::setDescription(Str::limit(strip_tags($post->content), 160));
        SEOTools::opengraph()->setUrl(route('posts.show', [
            'tahun' => $post->published_at->format('Y'),
            'bulan' => $post->published_at->format('m'),
            'slug' => $post->slug,
        ]));
        SEOTools::opengraph()->addProperty('type', 'article');
        $ogImage = $post->og_image_url; // accessor dari model
        SEOTools::opengraph()->addImage($ogImage);
        SEOTools::twitter()->addImage($ogImage);
        SEOTools::twitter()->setType('summary_large_image');

        // --- Canonical guard: cocokkan {bulan}/{tahun} dengan published_at post ---
        if ($post->published_at) {
            $bulanAktual = $post->published_at->format('m'); // "01".."12"
            $tahunAktual = $post->published_at->format('Y'); // "2025"

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
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->getKey())
            ->latest('published_at')
            ->limit(6)
            ->get();


        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();

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
