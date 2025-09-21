<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\HomepageSetting;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // buat user admin kalau belum ada, kalau sudah ada → update
        User::updateOrCreate(
            ['email' => 'admin@gerindrawebmanagement.com'],
            [
                'name' => 'Gerindra-Web_Management Admin',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        if (Schema::hasTable('homepage_settings')) {
            HomepageSetting::current();
        }

        // panggil seeder lain
        $this->call([
            SiteSettingsSeeder::class,
            PageSeeder::class,
            NewsPostSeeder::class,
            CompanyProfileSeeder::class,
            CategorySeeder::class,
            RolesSeeder::class,
        ]);
    }
}
