<?php

declare(strict_types=1);

namespace App\Actions\AiChat;

use App\AI\Enums\ProviderModel;
use App\AI\Tools\KanbanBoardTool;
use App\AI\Tools\UserMemoryTool;
use App\Enums\AiChatMessageRole;
use App\Jobs\GenerateChatTitleJob;
use App\Models\AiChatAttachment;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Document;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class SendMessageAction
{
    /**
     * @param  array<int>  $attachmentIds
     * @return array{user_message: AiChatMessage, assistant_message: AiChatMessage}
     */
    public function handle(
        AiChatSession $session,
        string $content,
        array $attachmentIds = []
    ): array {
        // Create user message
        $userMessage = $session->messages()->create([
            'role' => AiChatMessageRole::User,
            'content' => $content,
        ]);

        if (! empty($attachmentIds)) {
            AiChatAttachment::whereIn('id', $attachmentIds)
                ->whereNull('ai_chat_message_id')
                ->update(['ai_chat_message_id' => $userMessage->id]);
        }

        $userMessage->load('attachments');
        $session->touch();

        // Get AI response
        $assistantMessage = $this->getAiResponse($session, $userMessage);

        return [
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
        ];
    }

    private function getAiResponse(AiChatSession $session, AiChatMessage $userMessage): AiChatMessage
    {
        $session->load(['messages.attachments', 'user']);

        $modelString = $session->model ?? 'grok-4-fast-reasoning';
        $model = $this->resolveModel($modelString);

        if (! $model) {
            return $this->createErrorMessage($session, "Model '{$modelString}' not found");
        }

        try {
            /** @var User $user */
            $user = $session->user;

            $messages = $this->buildConversationHistory($session, $user);
            $tools = $this->buildTools($user);

            $prismRequest = Prism::text()
                ->using($model->provider(), $model->modelName())
                ->withMessages($messages)
                ->withTools($tools)
                ->withMaxSteps(5);

            /** @var array<string, mixed> $settings */
            $settings = is_array($session->settings) ? $session->settings : [];

            if (isset($settings['temperature']) && is_numeric($settings['temperature'])) {
                $prismRequest->usingTemperature((float) $settings['temperature']);
            }

            if (isset($settings['max_tokens']) && is_numeric($settings['max_tokens'])) {
                $prismRequest->withMaxTokens((int) $settings['max_tokens']);
            }

            $response = $prismRequest->asText();

            $toolCalls = collect($response->toolResults)->map(fn ($result) => [
                'tool' => $result->toolName,
                'result' => $result->result,
            ])->toArray();

            $assistantMessage = $session->messages()->create([
                'role' => AiChatMessageRole::Assistant,
                'content' => $response->text,
                'model' => $model->value,
                'usage' => [
                    'prompt_tokens' => $response->usage->promptTokens ?? 0,
                    'completion_tokens' => $response->usage->completionTokens ?? 0,
                ],
                'metadata' => ! empty($toolCalls) ? ['tool_calls' => $toolCalls] : null,
            ]);

            $this->maybeGenerateTitle($session);

            return $assistantMessage;
        } catch (\Throwable $e) {
            return $this->createErrorMessage($session, $e->getMessage());
        }
    }

    /**
     * @return array<SystemMessage|UserMessage|AssistantMessage>
     */
    private function buildConversationHistory(AiChatSession $session, User $user): array
    {
        $messages = [];

        $systemPrompt = $this->buildSystemPrompt($session, $user);

        if ($systemPrompt !== '') {
            $messages[] = new SystemMessage($systemPrompt);
        }

        foreach ($session->messages as $message) {
            $role = $message->role;

            if ($role === AiChatMessageRole::System) {
                continue;
            }

            if ($role === AiChatMessageRole::User) {
                $media = $this->buildMediaForMessage($message);
                $messages[] = new UserMessage($message->content, $media);
            }

            if ($role === AiChatMessageRole::Assistant) {
                $messages[] = new AssistantMessage($message->content);
            }
        }

        return $messages;
    }

    private function buildSystemPrompt(AiChatSession $session, User $user): string
    {
        $parts = [];

        $memoryPrompt = $this->buildMemorySystemPrompt($user);

        if ($memoryPrompt !== '') {
            $parts[] = $memoryPrompt;
        }

        $kanbanPrompt = $this->buildKanbanSystemPrompt($user);

        if ($kanbanPrompt !== '') {
            $parts[] = $kanbanPrompt;
        }

        /** @var array<string, mixed> $settings */
        $settings = is_array($session->settings) ? $session->settings : [];

        if (isset($settings['system_prompt']) && is_string($settings['system_prompt']) && $settings['system_prompt'] !== '') {
            $parts[] = $settings['system_prompt'];
        }

        return implode("\n\n", $parts);
    }

    private function buildMemorySystemPrompt(User $user): string
    {
        $memories = $user->memories()
            ->orderBy('type')
            ->orderBy('key')
            ->get();

        $lines = [
            'You have access to a memory tool (manage_user_memory) for storing and recalling information about the user.',
            'When the user shares important information (name, preferences, facts about themselves), save it using the tool.',
            'Use type "personal" for personal details. Create other types as needed (preferences, work, interests, etc.).',
            'Check stored memories when they might be relevant to the conversation.',
            'Update outdated information and delete when asked to forget.',
        ];

        if ($memories->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Current stored memories:';

            foreach ($memories as $memory) {
                $lines[] = "- [{$memory->type}] {$memory->key}: {$memory->value}";
            }
        }

        return implode("\n", $lines);
    }

    private function buildKanbanSystemPrompt(User $user): string
    {
        $boards = $user->kanbanBoards()
            ->withCount('columns')
            ->get();

        if ($boards->isEmpty()) {
            return '';
        }

        $lines = [
            'You have access to a kanban board management tool (manage_kanban) for interacting with the user\'s kanban boards.',
            'Use this tool to help the user manage their tasks, view board contents, update tasks, and track progress.',
            'Available actions: list_boards, list_columns, list_tasks, show_task_implementation_plan, show_task_notes, move_task, add_task_note, update_task, delete_task.',
            'Markdown is supported in description, implementation_plans, and notes fields.',
            '',
            'Current boards:',
        ];

        foreach ($boards as $board) {
            $projectInfo = $board->project_name ? " (Project: {$board->project_name})" : '';
            $lines[] = "- Board #{$board->id}: {$board->name}{$projectInfo} ({$board->columns_count} columns)";
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<UserMemoryTool|KanbanBoardTool>
     */
    private function buildTools(User $user): array
    {
        return [
            new UserMemoryTool($user),
            new KanbanBoardTool($user),
        ];
    }

    /**
     * @return array<Image|Document>
     */
    private function buildMediaForMessage(AiChatMessage $message): array
    {
        $media = [];

        foreach ($message->attachments as $attachment) {
            if ($attachment->isImage()) {
                $media[] = Image::fromStoragePath($attachment->path, $attachment->disk);
            } elseif ($attachment->isDocument()) {
                $media[] = Document::fromStoragePath($attachment->path, $attachment->disk);
            }
        }

        return $media;
    }

    private function resolveModel(string $modelString): ?ProviderModel
    {
        foreach (ProviderModel::cases() as $model) {
            if ($model->value === $modelString) {
                return $model;
            }
        }

        return null;
    }

    private function createErrorMessage(AiChatSession $session, string $error): AiChatMessage
    {
        return $session->messages()->create([
            'role' => AiChatMessageRole::Assistant,
            'content' => "Error: {$error}",
            'model' => null,
        ]);
    }

    private function maybeGenerateTitle(AiChatSession $session): void
    {
        if ($session->title !== null) {
            return;
        }

        $firstUserMessage = $session->messages()
            ->where('role', AiChatMessageRole::User->value)
            ->first();

        if (! $firstUserMessage) {
            return;
        }

        GenerateChatTitleJob::dispatch($session, $firstUserMessage->content);
    }
}
