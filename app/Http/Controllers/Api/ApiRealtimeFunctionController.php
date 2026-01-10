<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\AI\RealtimeTools\RealtimeKanbanTool;
use App\AI\RealtimeTools\RealtimeMemoryTool;
use App\Http\Controllers\Controller;
use App\Http\Requests\Realtime\ExecuteFunctionRequest;
use Illuminate\Http\JsonResponse;

class ApiRealtimeFunctionController extends Controller
{
    public function __construct(
        private readonly RealtimeKanbanTool $kanbanTool,
        private readonly RealtimeMemoryTool $memoryTool,
    ) {}

    public function execute(ExecuteFunctionRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $output = match ($validated['function_name']) {
            'manage_kanban' => $this->kanbanTool->execute($user, $validated['arguments']),
            'manage_user_memory' => $this->memoryTool->execute($user, $validated['arguments']),
            default => 'Unknown function',
        };

        return response()->json([
            'call_id' => $validated['call_id'],
            'output' => $output,
        ]);
    }
}
