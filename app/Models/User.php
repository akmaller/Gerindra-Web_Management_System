<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles;
    protected $guard_name = 'web';
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'phone',
        'bio',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar_path) {
            return null;
        }

        // Opsi 1 (disarankan): gunakan Storage::url()
        return Storage::url($this->avatar_path);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // sementara: izinkan semua user yang sudah login
        return true;

        // atau lebih aman, jika punya kolom is_admin:
        // return (bool) $this->is_admin;

        // atau batasi by email:
        // return in_array($this->email, ['admin@rckmanagement.com']);
    }
}
