<?php

declare(strict_types=1);

namespace App\Services;

use App\AI\RealtimeTools\RealtimeToolDefinitions;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class RealtimeSessionService
{
    /**
     * Create an ephemeral token for OpenAI Realtime API.
     *
     * @return array{
     *     client_secret: array{value: string, expires_at: int},
     *     tools: array<int, array<string, mixed>>,
     *     instructions: string
     * }
     *
     * @throws RequestException
     */
    public function createEphemeralToken(User $user, string $mode): array
    {
        $response = Http::withToken(config('prism.providers.openai.api_key'))
            ->post('https://api.openai.com/v1/realtime/sessions', [
                'model' => 'gpt-4o-realtime-preview',
                'voice' => 'marin',
            ]);

        $response->throw();

        return [
            'client_secret' => $response->json('client_secret'),
            'tools' => RealtimeToolDefinitions::all(),
            'instructions' => $this->buildSystemPrompt($user, $mode),
        ];
    }

    public function buildSystemPrompt(User $user, string $mode): string
    {
        $memoryContext = $this->buildMemoryContext($user);
        $kanbanContext = $this->buildKanbanContext($user);

        if ($mode === 'kanban') {
            return <<<PROMPT
You are a project management voice assistant focused on helping with kanban boards and tasks.
Keep your responses concise and natural for voice conversation.

Proactively offer to help with:
- Listing and organizing tasks
- Moving tasks between columns
- Adding notes to tasks
- Updating task details like priority and due dates

{$kanbanContext}

{$memoryContext}
PROMPT;
        }

        return <<<PROMPT
You are a helpful voice assistant. Keep your responses concise and natural for voice conversation.

You have access to tools to manage the user's kanban boards and save memories about them.
Use these tools when relevant to help the user.

{$kanbanContext}

{$memoryContext}
PROMPT;
    }

    private function buildMemoryContext(User $user): string
    {
        $memories = UserMemory::where('user_id', $user->id)
            ->orderBy('type')
            ->orderBy('key')
            ->get();

        if ($memories->isEmpty()) {
            return '';
        }

        $lines = ['## User Memories', ''];
        $currentType = null;

        foreach ($memories as $memory) {
            if ($memory->type !== $currentType) {
                $currentType = $memory->type;
                $lines[] = "### {$currentType}";
            }

            $lines[] = "- {$memory->key}: {$memory->value}";
        }

        return implode("\n", $lines);
    }

    private function buildKanbanContext(User $user): string
    {
        $boards = $user->kanbanBoards()
            ->withCount('columns')
            ->with('columns:id,kanban_board_id,name')
            ->get();

        if ($boards->isEmpty()) {
            return '## Kanban Boards\nThe user has no kanban boards yet.';
        }

        $lines = ['## Kanban Boards', ''];

        foreach ($boards as $board) {
            $projectInfo = $board->project_name ? " (Project: {$board->project_name})" : '';
            $lines[] = "- Board #{$board->id}: {$board->name}{$projectInfo}";

            $columnNames = $board->columns->pluck('name')->join(', ');
            if ($columnNames) {
                $lines[] = "  Columns: {$columnNames}";
            }
        }

        return implode("\n", $lines);
    }
}
