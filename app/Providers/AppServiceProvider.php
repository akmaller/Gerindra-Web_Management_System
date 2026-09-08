<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ChatbotSetting;
use App\Models\CompanyProfile;
use App\Models\Menu;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer(['layouts.app'], function ($view) {
            $settings = SiteSetting::first();
            $profile = CompanyProfile::first();
            $menus = Menu::tree('header');
            $chatbotSetting = Schema::hasTable('chatbot_settings')
                ? ChatbotSetting::current()
                : null;

            $view->with(compact('settings', 'profile', 'menus', 'chatbotSetting'));

        });
        View::composer(['layouts.app'], function ($view) {
            $catStrip = Category::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']); // kolom color opsional

            $view->with('catStrip', $catStrip);
        });
    }
}
