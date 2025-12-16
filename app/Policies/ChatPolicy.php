<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    /**
     * Определяет, может ли пользователь просматривать чат-бота.
     */
    public function view(User $user, Chat $chat): bool
    {
        return $user->id === $chat->user_id;
    }

    /**
     * Определяет, может ли пользователь обновлять чат-бота.
     */
    public function update(User $user, Chat $chat): bool
    {
        return $user->id === $chat->user_id;
    }

    /**
     * Определяет, может ли пользователь удалять чат-бота.
     */
    public function delete(User $user, Chat $chat): bool
    {
        return $user->id === $chat->user_id;
    }
}
