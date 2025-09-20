<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Nasional', 'Internasional', 'Daerah', 'Gosip', 'Opini', 'Rehat', 'Ekonomi', 'Olahraga', 'Teknologi', 'Hiburan', 'Kesehatan'] as $name) {
            Category::firstOrCreate(['slug' => \Str::slug($name)], [
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }
}
