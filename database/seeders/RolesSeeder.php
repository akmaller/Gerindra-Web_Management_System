<?php

namespace Database\Seeders;

// database/seeders/RolesSeeder.php
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'editor', 'penulis'] as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

    }
}
