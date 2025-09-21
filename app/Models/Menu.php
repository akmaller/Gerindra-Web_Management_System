<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    public static function tree(string $location, bool $onlyActive = true): Collection
    {
        $menus = static::query()
            ->where('location', $location)
            ->when($onlyActive, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return static::buildTreeFromCollection($menus);
    }

    protected static function buildTreeFromCollection(Collection $menus, ?int $parentId = null): Collection
    {
        return $menus
            ->filter(fn (self $menu) => $menu->parent_id === $parentId)
            ->sortBy(fn (self $menu) => [$menu->sort_order, $menu->label])
            ->map(function (self $menu) use ($menus) {
                $parent = $menus->firstWhere('id', $menu->parent_id);
                $menu->setRelation('parent', $parent);

                $children = static::buildTreeFromCollection($menus, $menu->id);
                $menu->setRelation('children', $children);

                return $menu;
            })
            ->values();
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
