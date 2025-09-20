<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin'); // hanya admin boleh lihat index User
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('admin') || $user->id === $model->id; // boleh lihat dirinya
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, User $model): bool
    {
        // admin boleh mengubah siapa pun; user biasa hanya boleh ubah dirinya (tanpa role)
        return $user->hasRole('admin') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // hanya admin, dan jangan izinkan hapus dirinya sendiri
        return $user->hasRole('admin') && $user->id !== $model->id;
    }
}
