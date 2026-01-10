<?php

declare(strict_types=1);

use App\Models\AiChatSession;
use App\Models\KanbanBoard;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{sessionId}', function ($user, $sessionId) {
    $session = AiChatSession::find($sessionId);

    return $session && $user->id === $session->user_id;
});

Broadcast::channel('kanban.board.{boardId}', function ($user, $boardId) {
    $board = KanbanBoard::find($boardId);

    return $board && $user->id === $board->user_id;
});
