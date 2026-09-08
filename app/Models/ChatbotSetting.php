<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ChatbotSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_enabled',
        'endpoint',
        'default_title',
        'chat_button_text',
        'chat_button_position',
        'request_timeout',
        'auth_type',
        'auth_header_key',
        'auth_header_value',
        'auth_header_as_bearer',
        'default_avatar_path',
        'history_storage',
        'history_ttl_minutes',
        'auto_inject_enabled',
        'auto_inject_sitewide',
        'auto_inject_position',
        'auto_inject_pages',
        'auto_inject_posts',
        'extra_options',
    ];

    protected $casts = [
        'module_enabled' => 'boolean',
        'auth_header_as_bearer' => 'boolean',
        'auto_inject_enabled' => 'boolean',
        'auto_inject_sitewide' => 'boolean',
        'auto_inject_pages' => 'array',
        'auto_inject_posts' => 'array',
        'extra_options' => 'array',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'module_enabled' => false,
            'chat_button_position' => 'bottom-right',
            'request_timeout' => 180,
            'history_storage' => 'ttl',
            'history_ttl_minutes' => 360,
            'auto_inject_position' => 'below_content',
        ]);
    }

    protected function authHeaderValue(): Attribute
    {
        return Attribute::make(
            get: static fn (?string $value) => filled($value) ? Crypt::decryptString($value) : null,
            set: static fn (?string $value) => filled($value) ? Crypt::encryptString($value) : null,
        );
    }

    public function buildAuthHeaders(): array
    {
        if ($this->auth_type !== 'custom_header' || blank($this->auth_header_key) || blank($this->auth_header_value)) {
            return [];
        }

        $headers = [
            $this->auth_header_key => $this->auth_header_value,
        ];

        if ($this->auth_header_as_bearer) {
            $headers['Authorization'] = 'Bearer ' . $this->auth_header_value;
        }

        return $headers;
    }
}
