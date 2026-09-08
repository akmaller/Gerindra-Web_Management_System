<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    // Admin bebas semua
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['editor', 'penulis']);
    }

    public function view(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['editor', 'penulis']);
    }

    public function update(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['editor', 'penulis']) || $post->user_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['editor', 'penulis']) || $post->user_id === $user->id;
    }

    public function restore(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->hasRole('editor');
    }
}
