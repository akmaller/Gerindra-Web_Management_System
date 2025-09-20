<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\SiteSetting;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

class ArchiveController extends Controller
{
    public function category(string $slug, Request $request)
    {
        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();

        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $posts = Post::with(['category', 'tags'])
            ->where('category_id', $category->id)
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $this->seoForArchive(
            title: "{$category->name} — " . ($settings->site_name ?? config('app.name')),
            description: $category->description ?? ($settings->site_description ?? ''),
            url: route('category.show', $category->slug),
            breadcrumb: [
                ['@id' => route('home'), 'name' => 'Beranda'],
                ['@id' => route('category.show', $category->slug), 'name' => $category->name],
            ],
        );

        return view('archive.category', compact('settings', 'profile', 'category', 'posts'));
    }

    public function tag(string $slug, Request $request)
    {
        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();

        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = Post::with(['category', 'tags'])
            ->whereHas('tags', fn($q) => $q->where('tags.id', $tag->id))
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $this->seoForArchive(
            title: "Tag: {$tag->name} — " . ($settings->site_name ?? config('app.name')),
            description: "Artikel dengan tag {$tag->name}.",
            url: route('tag.show', $tag->slug),
            breadcrumb: [
                ['@id' => route('home'), 'name' => 'Beranda'],
                ['@id' => route('tag.show', $tag->slug), 'name' => "Tag: {$tag->name}"],
            ],
        );

        return view('archive.tag', compact('settings', 'profile', 'tag', 'posts'));
    }

    /** SEO helper */
    private function seoForArchive(string $title, string $description, string $url, array $breadcrumb): void
    {
        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical($url);

        OpenGraph::setTitle($title)->setDescription($description)->setUrl($url)->setType('website');
        TwitterCard::setTitle($title)->setDescription($description);

        // Breadcrumb JSON-LD sederhana
        JsonLd::setType('BreadcrumbList');
        JsonLd::addValue('itemListElement', collect($breadcrumb)->values()->map(function ($item, $i) {
            return [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['@id'],
            ];
        })->all());
    }
}
