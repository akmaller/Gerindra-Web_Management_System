<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Menu;
use App\Models\SiteSetting;
use App\Models\CompanyProfile;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

class HomeController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();
        $slugs = ['ekonomi', 'opini', 'olahraga'];

        $featuredPosts = Post::with('category')
            ->published()
            ->where('is_featured', 1)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $categoryBlocks = collect($slugs)->map(function ($slug) {
            $cat = Category::where('slug', $slug)->where('is_active', true)->first();

            $posts = Post::with('category')
                ->published()
                ->when($cat, fn($q) => $q->where('category_id', $cat->id))
                ->orderByDesc('published_at')
                ->limit(4)
                ->get();

            return [
                'slug' => $slug,
                'title' => $cat->name ?? ucfirst($slug),
                'category' => $cat,
                'posts' => $posts,
                'more_url' => $cat ? route('category.show', $cat->slug) : '#',
            ];
        })->values();

        // hero: 3 featured terbaru
        $featured = Post::with('category')
            ->published()
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        // grid: 3 terbaru
        $latest = Post::with('category')
            ->published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $latestList = Post::with('category')
            ->published()
            ->orderByDesc('published_at')
            ->skip(3)
            ->limit(9)
            ->get();

        // 3 kategori utama (ubah slug sesuai data kamu)
        $categories = Category::whereIn('slug', ['ekonomi', 'opini', 'olahraga'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->keyBy('slug');

        // menu header dengan anak
        $menus = Menu::with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->where('location', 'header')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        // ====== SEO ======
        $siteName = $settings->site_name ?? config('app.name');
        $siteDesc = $settings->site_description ?? 'Portal berita terkini.';

        SEOMeta::setTitle($siteName);
        SEOMeta::setDescription($siteDesc);
        SEOMeta::setCanonical(route('home'));

        OpenGraph::setTitle($siteName)
            ->setDescription($siteDesc)
            ->setType('website')
            ->setUrl(route('home'))
            ->addProperty('locale', app()->getLocale());

        if ($settings?->logo_path) {
            OpenGraph::addImage(asset('storage/' . $settings->logo_path));
        }

        TwitterCard::setTitle($siteName)->setSite($profile?->twitter);

        JsonLd::setType('WebSite')
            ->setTitle($siteName)
            ->setDescription($siteDesc)
            ->setUrl(route('home'))
            ->addImage(('storage/' . $settings->logo_path));
        JsonLd::addValue('name', $siteName);

        // Organization (optional JSON-LD)
        JsonLd::setType('Organization')
            ->setUrl(route('home'))
            ->addImage(('storage/' . $settings->logo_path));

        JsonLd::addValue('name', $siteName);
        JsonLd::addValue('logo', ('storage/' . $settings->logo_path));
        JsonLd::addValue('description', $siteDesc);


        return view('home', compact('settings', 'profile', 'menus', 'featured', 'latest', 'latestList', 'categories', 'categoryBlocks', 'featuredPosts'));
    }
}
