<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $fillable = [
        'hero_slides',
        'custom_buttons',
        'management_team',
        'category_blocks',
        'tab_sections',
    ];

    protected $casts = [
        'hero_slides' => 'array',
        'custom_buttons' => 'array',
        'management_team' => 'array',
        'category_blocks' => 'array',
        'tab_sections' => 'array',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
