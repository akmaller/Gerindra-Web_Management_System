<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use Artesaos\SEOTools\Facades\SEOTools;
use App\Services\PopularPosts;
use App\Models\SiteSetting;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $settings = SiteSetting::first();
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // SEO meta (pakai artesaos/seotools yang sudah ada di project)
        SEOTools::setTitle($page->title . ' | ' . $settings->site_name);
        SEOTools::setDescription(\Str::limit(strip_tags($page->content), 160));
        if ($page->thumbnail) {
            SEOTools::opengraph()->addImage(asset('storage/' . $page->thumbnail));
        }
        $latestPosts = Post::published()
            ->latest('published_at')
            ->take(4)
            ->select(['id', 'title', 'slug', 'thumbnail', 'published_at'])
            ->get();

        return view('pages.show', compact('page', 'latestPosts'));
        // sementara return data mentah, nanti bisa diarahkan ke view frontend
        // return response()->json([
        //     'title' => $page->title,
        //     'slug' => $page->slug,
        //     'content' => $page->content,
        //     'thumbnail' => $page->thumbnail ? asset('storage/' . $page->thumbnail) : null,
        //     'is_active' => $page->is_active,
        //     'created_at' => $page->created_at,
        //     'latestPosts' => $latestPosts,
        // ]);

    }
}
