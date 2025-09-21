<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Menu;
use App\Models\SiteSetting;
use App\Models\CompanyProfile;
use App\Models\Category;
use App\Models\ChatbotSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.*', 'partials.*'], function ($view) {
            $settings = SiteSetting::first();
            $profile = CompanyProfile::first();
            $menus = Menu::tree('header');
            $chatbotSetting = Schema::hasTable('chatbot_settings')
                ? ChatbotSetting::current()
                : null;

            $view->with(compact('settings', 'profile', 'menus', 'chatbotSetting'));

        });
        View::composer(['layouts.*', 'home', 'partials.*'], function ($view) {
            $catStrip = Category::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']); // kolom color opsional

            $view->with('catStrip', $catStrip);
        });
    }
}
