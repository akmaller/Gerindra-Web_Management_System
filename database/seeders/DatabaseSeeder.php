<?php

namespace Database\Seeders;

use App\Models\HomepageSetting;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Never create a privileged account with a hard-coded password.
        // Bootstrap users explicitly through the normal administration flow.
        if (Schema::hasTable('homepage_settings')) {
            HomepageSetting::current();
        }

        // panggil seeder lain
        $this->call([
            SiteSettingsSeeder::class,
            // NewsPostSeeder::class,
            // CompanyProfileSeeder::class,
            CategorySeeder::class,
            RolesSeeder::class,
        ]);
    }
}
