<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSeo;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageController extends Controller
{
    use InteractsWithSeo;

    public function show(string $slug)
    {
        $settings = SiteSetting::first();
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $siteName = $settings->site_name ?? config('app.name');
        $pageUrl = route('pages.show', $page->slug);
        $description = Str::limit(strip_tags($page->content), 160);

        $thumbnailUrl = $this->resolveThumbnailUrl($page->thumbnail);

        $this->setSeo(
            title: $page->title . ' | ' . $siteName,
            description: $description,
            url: $pageUrl,
            images: array_filter([$thumbnailUrl]),
            options: [
                'site_name' => $siteName,
                'json_ld_type' => 'WebPage',
                'json_ld_values' => array_filter([
                    'mainEntityOfPage' => $pageUrl,
                    'isPartOf' => [
                        '@type' => 'WebSite',
                        'name' => $siteName,
                        'url' => route('home'),
                    ],
                    'dateModified' => optional($page->updated_at)->toIso8601String(),
                ]),
            ]
        );

        $latestPosts = Post::published()
            ->latest('published_at')
            ->take(4)
            ->select(['id', 'title', 'slug', 'thumbnail', 'published_at'])
            ->get();

        return view('pages.show', compact('page', 'latestPosts'));
    }

    protected function resolveThumbnailUrl(?string $thumbnail): ?string
    {
        if (! $thumbnail) {
            return null;
        }

        $thumbnail = trim($thumbnail);

        if (Str::startsWith($thumbnail, ['http://', 'https://'])) {
            return $thumbnail;
        }

        $thumbnail = ltrim($thumbnail, '/');

        if (Str::startsWith($thumbnail, 'storage/')) {
            $thumbnail = Str::after($thumbnail, 'storage/');
        }

        if (Storage::disk('public')->exists($thumbnail)) {
            return Storage::url($thumbnail);
        }

        return null;
    }
}
