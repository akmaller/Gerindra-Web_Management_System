<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!DB::table('site_settings')->exists()) {
            DB::table('site_settings')->insert([
                'site_name' => 'Gerindra-Web_Management',
                'site_description' => 'Portal WEB resmi Gerindra',
                'logo_path' => 'logo/logo.png',
                'favicon_path' => 'logo/favicon.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
