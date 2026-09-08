<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        CompanyProfile::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'DPP Partai Gerindra',
                'address' => 'Jl. Harsono RM No. 54 Ragunan, Jakarta Selatan, DKI Jakarta - 12550',
                'email' => 'ppid@gerindra.id',
            ]
        );
    }
}
