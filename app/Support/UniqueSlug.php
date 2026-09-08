<?php

namespace App\Support;

use Illuminate\Support\Str;

class UniqueSlug
{
    public static function for(string $model, ?string $title): string
    {
        $base = Str::slug($title ?? '') ?: 'konten';
        $base = Str::limit($base, 220, '');
        $slug = $base;
        for ($suffix = 2; $model::where('slug', $slug)->exists(); $suffix++) {
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }
}
