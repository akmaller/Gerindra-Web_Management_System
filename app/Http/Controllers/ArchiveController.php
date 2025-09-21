<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSeo;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\SiteSetting;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArchiveController extends Controller
{
    use InteractsWithSeo;

    public function category(string $slug, Request $request)
    {
        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();

        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $siteName = $settings->site_name ?? config('app.name');
        $rawDescription = $category->description ?: ($settings->site_description ?? '');
        $description = Str::limit(strip_tags((string) $rawDescription), 160);
        $logoPath = optional($settings)->logo_url;
        $shareImage = $logoPath ? asset($logoPath) : null;

        $posts = Post::with(['categories', 'tags'])
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $this->seoForArchive(
            title: "{$category->name} — {$siteName}",
            description: $description,
            url: route('category.show', $category->slug),
            breadcrumb: [
                ['name' => 'Beranda', 'url' => route('home')],
                ['name' => $category->name, 'url' => route('category.show', $category->slug)],
            ],
            options: [
                'images' => array_filter([$shareImage]),
                'site_name' => $siteName,
                'twitter_site' => $profile?->twitter,
                'json_ld_values' => array_filter([
                    'about' => strip_tags((string) $category->description),
                ], fn ($value) => $value !== null && $value !== ''),
            ],
        );

        return view('archive.category', compact('settings', 'profile', 'category', 'posts'));
    }

    public function tag(string $slug, Request $request)
    {
        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();

        $tag = Tag::where('slug', $slug)->firstOrFail();

        $siteName = $settings->site_name ?? config('app.name');
        $description = Str::limit("Artikel dengan tag {$tag->name}.", 160);
        $logoPath = optional($settings)->logo_url;
        $shareImage = $logoPath ? asset($logoPath) : null;

        $posts = Post::with(['categories', 'tags'])
            ->whereHas('tags', fn($q) => $q->where('tags.id', $tag->id))
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $this->seoForArchive(
            title: "Tag: {$tag->name} — {$siteName}",
            description: $description,
            url: route('tag.show', $tag->slug),
            breadcrumb: [
                ['name' => 'Beranda', 'url' => route('home')],
                ['name' => "Tag: {$tag->name}", 'url' => route('tag.show', $tag->slug)],
            ],
            options: [
                'images' => array_filter([$shareImage]),
                'site_name' => $siteName,
                'twitter_site' => $profile?->twitter,
                'json_ld_values' => [
                    'about' => "Artikel dengan tag {$tag->name}",
                ],
            ],
        );

        return view('archive.tag', compact('settings', 'profile', 'tag', 'posts'));
    }

    /**
     * Pengaturan SEO umum untuk halaman arsip.
     */
    private function seoForArchive(
        string $title,
        string $description,
        string $url,
        array $breadcrumb = [],
        array $options = []
    ): void {
        $images = array_values(array_filter($options['images'] ?? [], function ($image) {
            if (is_string($image)) {
                return trim($image) !== '';
            }

            return ! empty($image);
        }));

        $siteName = $options['site_name'] ?? config('app.name');
        $twitterSite = $options['twitter_site'] ?? null;
        $jsonLdValues = $options['json_ld_values'] ?? [];

        $jsonLdValues = array_merge([
            'description' => $description,
            'inLanguage' => app()->getLocale(),
        ], $jsonLdValues);

        $jsonLdValues = array_filter($jsonLdValues, function ($value) {
            if (is_array($value)) {
                return ! empty($value);
            }

            return $value !== null && $value !== '';
        });

        $this->setSeo(
            title: $title,
            description: $description,
            url: $url,
            images: $images,
            options: [
                'type' => 'website',
                'site_name' => $siteName,
                'twitter_site' => $twitterSite,
                'json_ld_type' => 'CollectionPage',
                'json_ld_values' => $jsonLdValues,
            ]
        );

        $this->setBreadcrumbJsonLd($breadcrumb);
    }
}
