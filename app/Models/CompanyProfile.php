<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'company_name',
        'address',
        'email',
        'phone',
        'npwp',
        'nib',
        'bank_account',
        'vision',
        'mission',
        'google_maps',
        'twitter',
        'facebook',
        'instagram',
        'youtube',
        'tiktok',
        'wikipedia',
    ];

    /** singleton helper */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }
}
