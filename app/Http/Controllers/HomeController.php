<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\HomepageSetting;
use App\Models\Post;
use App\Models\SiteSetting;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();
        $homepage = Schema::hasTable('homepage_settings')
            ? HomepageSetting::current()
            : new HomepageSetting();

        $heroSlides = collect($homepage->hero_slides ?? [])
            ->filter(fn ($slide) => filled($slide['image'] ?? null))
            ->map(function (array $slide) {
                $path = $this->resolveStoragePath($slide['image'] ?? null);
                $imageUrl = null;

                if ($path) {
                    if (Str::startsWith($path, ['http://', 'https://'])) {
                        $imageUrl = $path;
                    } elseif (Storage::disk('public')->exists($path)) {
                        $imageUrl = Storage::disk('public')->url($path);
                    }
                }

                $imageUrl ??= asset('images/example-wide.webp');

                return [
                    'image_url' => $imageUrl,
                    'title' => $slide['title'] ?? null,
                    'subtitle' => $slide['subtitle'] ?? null,
                    'link_label' => $slide['link_label'] ?? null,
                    'link_url' => $slide['link_url'] ?? null,
                ];
            })
            ->values();

        if ($heroSlides->isEmpty()) {
            $heroSlides = Post::with('category')
                ->published()
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
                ->map(function (Post $post) {
                    $imageUrl = null;

                    $path = $this->resolveStoragePath($post->thumbnail);

                    if ($path && Storage::disk('public')->exists($path)) {
                        $imageUrl = Storage::disk('public')->url($path);
                    }

                    $imageUrl ??= asset('images/example-middle.webp');

                    return [
                        'image_url' => $imageUrl,
                        'title' => $post->title,
                        'subtitle' => $post->category?->name,
                        'link_label' => 'Baca selengkapnya',
                        'link_url' => $post->permalink,
                    ];
                });
        }

        $customButtons = collect($homepage->custom_buttons ?? [])
            ->filter(fn ($button) => filled($button['label'] ?? null) && filled($button['url'] ?? null))
            ->map(fn ($button) => [
                'label' => $button['label'],
                'url' => $button['url'],
            ])
            ->values();

        $managementTeam = collect($homepage->management_team ?? [])
            ->filter(fn ($member) => filled($member['name'] ?? null) && filled($member['position'] ?? null))
            ->map(function (array $member) {
                $photoPath = $member['photo'] ?? null;
                $photoUrl = null;

                if ($photoPath) {
                    if (Str::startsWith($photoPath, ['http://', 'https://'])) {
                        $photoUrl = $photoPath;
                    } else {
                        $path = $this->resolveStoragePath($photoPath);

                        if ($path && Storage::disk('public')->exists($path)) {
                            $photoUrl = Storage::disk('public')->url($path);
                        }
                    }
                }

                return [
                    'name' => $member['name'],
                    'position' => $member['position'],
                    'photo_url' => $photoUrl,
                ];
            })
            ->values();

        $latestPosts = Post::with('category')
            ->published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $tabSections = collect($homepage->tab_sections ?? [])
            ->filter(fn ($tab) => filled($tab['title'] ?? null) && filled($tab['content'] ?? null))
            ->map(fn ($tab) => [
                'title' => $tab['title'],
                'content' => $tab['content'],
            ])
            ->values();

        $categorySections = collect($homepage->category_blocks ?? [])
            ->filter(fn ($block) => filled($block['category_id'] ?? null))
            ->unique(fn ($block) => $block['category_id'])
            ->take(3)
            ->map(function (array $block) {
                $category = Category::query()
                    ->where('is_active', true)
                    ->whereKey($block['category_id'])
                    ->first();

                if (! $category) {
                    return null;
                }

                $posts = Post::with('category')
                    ->published()
                    ->where('category_id', $category->id)
                    ->orderByDesc('published_at')
                    ->limit(3)
                    ->get();

                if ($posts->isEmpty()) {
                    return null;
                }

                return [
                    'title' => $block['title'] ?? $category->name,
                    'category' => $category,
                    'posts' => $posts,
                    'more_url' => route('category.show', $category->slug),
                ];
            })
            ->filter()
            ->values();

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

        $logoUrl = null;
        if ($settings?->logo_path) {
            if (Str::startsWith($settings->logo_path, ['http://', 'https://'])) {
                $logoUrl = $settings->logo_path;
            } else {
                $logoPath = $this->resolveStoragePath($settings->logo_path);

                if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                    $logoUrl = Storage::disk('public')->url($logoPath);
                }
            }

            if ($logoUrl) {
                OpenGraph::addImage($logoUrl);
            }
        }

        TwitterCard::setTitle($siteName)->setSite($profile?->twitter);

        JsonLd::setType('WebSite')
            ->setTitle($siteName)
            ->setDescription($siteDesc)
            ->setUrl(route('home'))
            ->addImage($logoUrl ?? asset('images/example-middle.webp'));
        JsonLd::addValue('name', $siteName);

        JsonLd::setType('Organization')
            ->setUrl(route('home'))
            ->addImage($logoUrl ?? asset('images/example-middle.webp'));

        JsonLd::addValue('name', $siteName);
        JsonLd::addValue('logo', $logoUrl ?? asset('images/example-middle.webp'));
        JsonLd::addValue('description', $siteDesc);

        return view('home', compact(
            'settings',
            'profile',
            'heroSlides',
            'customButtons',
            'latestPosts',
            'tabSections',
            'managementTeam',
            'categorySections'
        ));
    }

    protected function resolveStoragePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        return $path;
    }
}
