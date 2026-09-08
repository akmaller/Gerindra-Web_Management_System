<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\HomepageSetting;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Http\Controllers\Concerns\InteractsWithSeo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    use InteractsWithSeo;

    public function index()
    {
        $settings = SiteSetting::first();
        $profile = CompanyProfile::first();
        $homepage = $this->resolveHomepageSettings();

        $heroSlides = $this->buildHeroSlides($homepage);

        if ($heroSlides->isEmpty()) {
            $heroSlides = $this->buildFallbackHeroSlides();
        }

        $customButtons = $this->buildCustomButtons($homepage);
        $managementTeam = $this->buildManagementTeam($homepage);
        $latestPosts = $this->fetchLatestPosts();
        $tabSections = $this->buildTabSections($homepage);
        $categorySections = $this->buildCategorySections($homepage);

        $this->configureHomeSeo($settings, $profile, $heroSlides);

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

    protected function resolveHomepageSettings(): HomepageSetting
    {
        if (Schema::hasTable('homepage_settings')) {
            return HomepageSetting::current();
        }

        return new HomepageSetting();
    }

    protected function buildHeroSlides(HomepageSetting $homepage): Collection
    {
        return collect($homepage->hero_slides ?? [])
            ->filter(fn ($slide) => is_array($slide) && filled($slide['image'] ?? null))
            ->map(fn (array $slide) => $this->formatHeroSlide($slide))
            ->values();
    }

    protected function buildFallbackHeroSlides(): Collection
    {
        return Post::with('categories')
            ->published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn (Post $post) => $this->formatFallbackHeroSlide($post));
    }

    protected function buildCustomButtons(HomepageSetting $homepage): Collection
    {
        return collect($homepage->custom_buttons ?? [])
            ->filter(fn ($button) => filled($button['label'] ?? null) && filled($button['url'] ?? null))
            ->map(fn ($button) => [
                'label' => $button['label'],
                'url' => $button['url'],
            ])
            ->values();
    }

    protected function buildManagementTeam(HomepageSetting $homepage): Collection
    {
        return collect($homepage->management_team ?? [])
            ->filter(fn ($member) => filled($member['name'] ?? null) && filled($member['position'] ?? null))
            ->map(fn (array $member) => $this->formatManagementMember($member))
            ->values();
    }

    protected function fetchLatestPosts(int $limit = 3)
    {
        return Post::with('categories')
            ->published()
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    protected function buildTabSections(HomepageSetting $homepage): Collection
    {
        return collect($homepage->tab_sections ?? [])
            ->filter(fn ($tab) => filled($tab['title'] ?? null) && filled($tab['content'] ?? null))
            ->map(fn ($tab) => [
                'title' => $tab['title'],
                'content' => $tab['content'],
            ])
            ->values();
    }

    protected function buildCategorySections(HomepageSetting $homepage): Collection
    {
        return collect($homepage->category_blocks ?? [])
            ->filter(fn ($block) => filled($block['category_id'] ?? null))
            ->unique(fn ($block) => $block['category_id'])
            ->take(3)
            ->map(fn (array $block) => $this->formatCategorySection($block))
            ->filter()
            ->values();
    }

    protected function configureHomeSeo(?SiteSetting $settings, ?CompanyProfile $profile, Collection $heroSlides): void
    {
        $siteName = $settings->site_name ?? config('app.name');
        $siteDesc = $settings->site_description ?? 'Portal berita terkini.';
        $homeUrl = route('home');

        $logoUrl = $this->resolveImageUrl($settings?->logo_path, 'images/example-middle.webp');
        $heroPrimaryImage = $heroSlides->first()['image_url'] ?? null;

        $this->setSeo(
            title: $siteName,
            description: $siteDesc,
            url: $homeUrl,
            images: array_filter([$logoUrl, $heroPrimaryImage]),
            options: [
                'type' => 'website',
                'site_name' => $siteName,
                'twitter_site' => $profile?->twitter,
                'json_ld_type' => 'WebSite',
                'json_ld_values' => array_filter([
                    'inLanguage' => app()->getLocale(),
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => route('search', ['q' => '{search_term_string}']),
                        'query-input' => 'required name=search_term_string',
                    ],
                ]),
            ]
        );

        $sameAs = $this->collectProfileLinks($profile);

        $organizationGraph = array_filter([
            'name' => $siteName,
            'url' => $homeUrl,
            'logo' => $logoUrl,
            'sameAs' => $sameAs,
            'description' => $siteDesc,
        ]);

        if (! empty($organizationGraph)) {
            $this->addJsonLdGraph('Organization', $organizationGraph);
        }
    }

    protected function collectProfileLinks(?CompanyProfile $profile): array
    {
        return collect([
            $profile?->facebook,
            $profile?->instagram,
            $profile?->twitter,
            $profile?->youtube,
            $profile?->tiktok,
            $profile?->wikipedia,
        ])->filter(fn (?string $url) => is_string($url) && trim($url) !== '')->values()->all();
    }

    protected function formatHeroSlide(array $slide): array
    {
        return [
            'image_url' => $this->resolveImageUrl($slide['image'] ?? null, 'images/example-wide.webp'),
            'title' => $slide['title'] ?? null,
            'subtitle' => $slide['subtitle'] ?? null,
            'link_label' => $slide['link_label'] ?? null,
            'link_url' => $slide['link_url'] ?? null,
        ];
    }

    protected function formatFallbackHeroSlide(Post $post): array
    {
        $imageUrl = null;
        $path = $this->resolveStoragePath($post->thumbnail);

        if ($path && Storage::disk('public')->exists($path)) {
            $imageUrl = Storage::url($path);
        }

        $imageUrl ??= asset('images/example-middle.webp');

        return [
            'image_url' => $imageUrl,
            'title' => $post->title,
            'subtitle' => $post->primary_category?->name,
            'link_label' => 'Baca selengkapnya',
            'link_url' => $post->permalink,
        ];
    }

    protected function formatManagementMember(array $member): array
    {
        $photoPath = $member['photo'] ?? null;
        $photoUrl = null;

        if ($photoPath) {
            if (Str::startsWith($photoPath, ['http://', 'https://'])) {
                $photoUrl = $photoPath;
            } else {
                $path = $this->resolveStoragePath($photoPath);

                if ($path && Storage::disk('public')->exists($path)) {
                    $photoUrl = Storage::url($path);
                }
            }
        }

        return [
            'name' => $member['name'],
            'position' => $member['position'],
            'photo_url' => $photoUrl,
        ];
    }

    protected function formatCategorySection(array $block): ?array
    {
        $category = Category::query()
            ->where('is_active', true)
            ->whereKey($block['category_id'])
            ->first();

        if (! $category) {
            return null;
        }

        $posts = Post::with('categories')
            ->published()
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
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

    protected function resolveImageUrl(?string $path, string $fallback): string
    {
        if (! $path) {
            return asset($fallback);
        }

        $path = trim($path);

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $storagePath = $this->resolveStoragePath($path);

        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            return Storage::url($storagePath);
        }

        return asset($fallback);
    }
}
