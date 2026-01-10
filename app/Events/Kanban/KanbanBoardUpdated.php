<?php

declare(strict_types=1);

namespace App\Events\Kanban;

use App\Models\KanbanBoard;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KanbanBoardUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public KanbanBoard $board,
        public string $action,
        public ?string $entityType = null,
        public ?int $entityId = null,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("kanban.board.{$this->board->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'board.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'board_id' => $this->board->id,
            'action' => $this->action,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
