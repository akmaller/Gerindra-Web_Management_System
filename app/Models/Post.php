<?php

namespace App\Models;

use App\Support\ImageVariants;
use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'thumbnail',
        'status',
        'published_at',
        'is_featured',
        'is_pinned',
        'excerpt',
        'content',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    // relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_post');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getPrimaryCategoryAttribute(): ?Category
    {
        return $this->categories->first() ?? $this->category;
    }

    // scopes
    public function scopePublished(Builder $q): Builder
    {
        return $q->whereIn('status', ['published', 'scheduled'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    // hooks
    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (blank($post->user_id) && auth()->check()) {
                $post->user_id = auth()->id();
            }
            $post->slug = UniqueSlug::for(self::class, $post->slug ?: $post->title);
            if ($post->status === 'published' && blank($post->published_at)) {
                $post->published_at = now();
            }
        });

        static::updating(function (Post $post) {
            if ($post->status === 'published' && blank($post->published_at)) {
                $post->published_at = now();
            }
        });

        static::deleted(fn () => Cache::forever('popular:version', (string) Str::uuid()));

        static::saved(function (Post $post): void {
            Cache::forever('popular:version', (string) Str::uuid());
            $col = 'thumbnail'; // kamu sudah memakai kolom ini
            $path = $post->{$col} ?? null;

            if (! $path || (! $post->wasRecentlyCreated && ! $post->wasChanged('thumbnail'))) {
                return;
            }
            if (! Storage::disk('public')->exists($path)) {
                return;
            }

            try {
                // generate 3 ukuran + webp
                ImageVariants::generate($path, 'public', [
                    'thumb' => 1280,
                    'small' => 200,
                    'middle' => 400,
                ], 82);

                \Log::info("Image variants generated for: {$path}");
            } catch (\Throwable $e) {
                \Log::warning('Variants failed: '.$e->getMessage());
            }
        });
    }

    public function getPermalinkAttribute(): ?string
    {
        if (blank($this->published_at)) {
            return null;
        }

        return route('posts.show', [
            'bulan' => $this->published_at->format('m'),
            'tahun' => $this->published_at->format('Y'),
            'slug' => $this->slug,
        ]);
    }

    public function getUrlAttribute(): string
    {
        $bulan = optional($this->published_at)->format('m'); // "01".."12"
        $tahun = optional($this->published_at)->format('Y'); // "2025"

        return route('posts.show', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'slug' => $this->slug,
        ]);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function getThumbUrlAttribute(): string
    {
        // Kembalikan URL absolut ke thumbnail atau placeholder
        if ($this->thumbnail && Storage::disk('public')->exists($this->thumbnail)) {
            // Storage::url() akan menghasilkan /storage/xxx -> jadi absolutkan dengan asset()
            return asset(Storage::url($this->thumbnail));
        }

        // Pastikan file placeholder tersedia di public/images/
        return asset('images/example-middle.webp');
    }

    public function getOgImageUrlAttribute(): string
    {
        // Kalau kamu punya field lain seperti $this->image, bisa diprioritaskan di sini
        return $this->thumb_url; // pakai accessor di atas sebagai default
    }

    public function views()
    {
        return $this->hasMany(PostView::class);
    }
}
