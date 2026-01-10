<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AiChatSession;
use App\Models\User;

class AiChatSessionPolicy
{
    public function view(User $user, AiChatSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function update(User $user, AiChatSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function delete(User $user, AiChatSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function sendMessage(User $user, AiChatSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function clear(User $user, AiChatSession $session): bool
    {
        return $user->id === $session->user_id;
    }
}
