<?php

namespace App\Models;

use App\Support\ImageVariants;
use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'thumbnail',
        'attachment_path',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            $page->slug = UniqueSlug::for(self::class, $page->slug ?: $page->title);
        });

        static::updating(function (Page $page) {});

        static::saved(function (Page $page): void {
            $col = 'thumbnail'; // kamu sudah memakai kolom ini
            $path = $page->{$col} ?? null;

            if (! $path || (! $page->wasRecentlyCreated && ! $page->wasChanged('thumbnail'))) {
                return;
            }
            if (! Storage::disk('public')->exists($path)) {
                return;
            }

            try {
                // generate 3 ukuran + webp
                ImageVariants::generate($path, 'public', [
                    'thumb' => 1280,
                ], 82);

                \Log::info("Image variants generated for: {$path}");
            } catch (\Throwable $e) {
                \Log::warning('Variants failed: '.$e->getMessage());
            }
        });
    }
}
