<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_description',
        'logo_path',
        'favicon_path',
    ];

    /**
     * Accessor URL untuk logo (misalnya dipakai di <img src="{{ $settings->logo_url }}">).
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    /**
     * Accessor URL untuk favicon.
     */
    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon_path ? Storage::url($this->favicon_path) : null;
    }

    /**
     * Helper singleton — ambil setting pertama (id=1).
     */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'site_name' => config('app.name'),
            'site_description' => '',
        ]);
    }
}
