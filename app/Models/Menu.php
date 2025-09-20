<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'label',
        'location',
        'item_type',
        'category_id',
        'page_id',
        'url',
        'open_in_new_tab',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    // Opsional: hubungkan jika modelnya ada
    public function page()
    {
        return $this->belongsTo(\App\Models\Page::class, 'page_id');
    }

    public function category()
    {
        // ganti namespace Category sesuai project kamu
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }
    public function getResolvedUrlAttribute(): string
    {
        if ($this->item_type === 'category' && $this->category) {
            return route('category.show', $this->category->slug);
        }

        if ($this->item_type === 'page' && $this->page) {
            return route('pages.show', $this->page->slug);
        }

        if ($this->item_type === 'custom' && $this->url) {
            return $this->url;
        }

        return '#';
    }
}
