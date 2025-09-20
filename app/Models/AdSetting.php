<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSetting extends Model
{
    protected $table = 'ad_settings';

    protected $fillable = [
        'header_enabled',
        'header_html',
        'sidebar_enabled',
        'sidebar_html',
        'below_post_enabled',
        'below_post_html',
        'footer_enabled',
        'footer_html',
    ];

    protected $casts = [
        'header_enabled' => 'bool',
        'sidebar_enabled' => 'bool',
        'below_post_enabled' => 'bool',
        'footer_enabled' => 'bool',
    ];
}
