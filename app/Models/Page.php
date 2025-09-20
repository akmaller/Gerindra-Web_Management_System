<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Support\ImageVariants;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'thumbnail',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            if (blank($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::updating(function (Page $page) {
            if ($page->isDirty('title')) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::saved(function (Page $page): void {
            $col = 'thumbnail'; // kamu sudah memakai kolom ini
            $path = $page->{$col} ?? null;

            if (!$path)
                return;
            if (!Storage::disk('public')->exists($path))
                return;

            try {
                // generate 3 ukuran + webp
                ImageVariants::generate($path, 'public', [
                    'thumb' => 1280,
                ], 82);

                \Log::info("Image variants generated for: {$path}");
            } catch (\Throwable $e) {
                \Log::warning("Variants failed: " . $e->getMessage());
            }
        });
    }

}
