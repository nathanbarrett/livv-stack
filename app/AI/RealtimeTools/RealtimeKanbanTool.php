<?php

declare(strict_types=1);

namespace App\AI\RealtimeTools;

use App\Actions\Kanban\CreateTaskNoteAction;
use App\Actions\Kanban\DeleteTaskAction;
use App\Actions\Kanban\MoveTaskAction;
use App\Actions\Kanban\UpdateTaskAction;
use App\Enums\KanbanTaskNoteAuthor;
use App\Enums\KanbanTaskPriority;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RealtimeKanbanTool
{
    /**
     * @param array<string, mixed> $args
     */
    public function execute(User $user, array $args): string
    {
        $action = $args['action'] ?? null;

        return match ($action) {
            'list_boards' => $this->listBoards($user),
            'list_columns' => $this->listColumns($user, $args['board_id'] ?? null),
            'list_tasks' => $this->listTasks($user, $args['board_id'] ?? null, $args['column_id'] ?? null),
            'show_task_implementation_plan' => $this->showTaskImplementationPlan($user, $args['task_id'] ?? null),
            'show_task_notes' => $this->showTaskNotes($user, $args['task_id'] ?? null),
            'move_task' => $this->moveTask($user, $args['task_id'] ?? null, $args['column_id'] ?? null, $args['position'] ?? null),
            'add_task_note' => $this->addTaskNote($user, $args['task_id'] ?? null, $args['note'] ?? null),
            'update_task' => $this->updateTask($user, $args),
            'delete_task' => $this->deleteTask($user, $args['task_id'] ?? null),
            default => "Unknown action: {$action}. Valid actions are: list_boards, list_columns, list_tasks, show_task_implementation_plan, show_task_notes, move_task, add_task_note, update_task, delete_task.",
        };
    }

    private function listBoards(User $user): string
    {
        $boards = $user->kanbanBoards()
            ->withCount('columns')
            ->with('columns.tasks')
            ->get();

        if ($boards->isEmpty()) {
            return 'No kanban boards found. The user has not created any boards yet.';
        }

        $lines = ['Kanban Boards:', ''];

        foreach ($boards as $board) {
            $taskCount = $board->columns->sum(fn ($col) => $col->tasks->count());
            $projectInfo = $board->project_name ? " (Project: {$board->project_name})" : '';

            $lines[] = "- Board #{$board->id}: {$board->name}{$projectInfo}";
            $lines[] = "  Columns: {$board->columns_count} | Tasks: {$taskCount}";

            if ($board->description) {
                $lines[] = '  Description: ' . Str::limit($board->description, 100);
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function listColumns(User $user, int|float|null $boardId): string
    {
        if ($boardId === null) {
            return 'Error: board_id parameter is required for list_columns action.';
        }

        $board = $this->verifyBoardOwnership($user, (int) $boardId);

        if (! $board) {
            return "Error: Board #{$boardId} not found or you don't have access to it.";
        }

        $columns = $board->columns()->withCount('tasks')->orderBy('position')->get();

        if ($columns->isEmpty()) {
            return "Board #{$boardId} ({$board->name}) has no columns.";
        }

        $lines = ["Columns in Board #{$boardId} ({$board->name}):", ''];

        foreach ($columns as $column) {
            $colorInfo = $column->color ? " [Color: {$column->color}]" : '';
            $lines[] = "- Column #{$column->id}: {$column->name} (Position: {$column->position}){$colorInfo}";
            $lines[] = "  Tasks: {$column->tasks_count}";
        }

        return implode("\n", $lines);
    }

    private function listTasks(User $user, int|float|null $boardId, int|float|null $columnId): string
    {
        if ($boardId === null || $columnId === null) {
            return 'Error: board_id and column_id parameters are required for list_tasks action.';
        }

        $board = $this->verifyBoardOwnership($user, (int) $boardId);

        if (! $board) {
            return "Error: Board #{$boardId} not found or you don't have access to it.";
        }

        $column = $board->columns()->find((int) $columnId);

        if (! $column) {
            return "Error: Column #{$columnId} not found in Board #{$boardId}.";
        }

        $tasks = $column->tasks()
            ->with('dependencies:id,title')
            ->orderBy('position')
            ->get();

        if ($tasks->isEmpty()) {
            return "Column #{$columnId} ({$column->name}) has no tasks.";
        }

        $lines = ["Tasks in Column #{$columnId} ({$column->name}) of Board #{$boardId} ({$board->name}):", ''];

        foreach ($tasks as $task) {
            /** @var KanbanTaskPriority|null $priority */
            $priority = $task->priority;
            $priorityInfo = $priority ? " [Priority: {$priority->value}]" : '';

            /** @var Carbon|null $dueDate */
            $dueDate = $task->due_date;
            $dueDateInfo = $dueDate ? " [Due: {$dueDate->format('Y-m-d')}]" : '';

            $lines[] = "- Task #{$task->id}: {$task->title}{$priorityInfo}{$dueDateInfo}";

            if ($task->description) {
                $lines[] = '  Description: ' . Str::limit($task->description, 150);
            }

            if ($task->dependencies->isNotEmpty()) {
                $depIds = $task->dependencies->pluck('id')->join(', #');
                $lines[] = "  Depends on: #{$depIds}";
            }

            $lines[] = '';
        }

        $lines[] = 'Note: Use show_task_implementation_plan and show_task_notes actions to get full details for a specific task.';

        return implode("\n", $lines);
    }

    private function showTaskImplementationPlan(User $user, int|float|null $taskId): string
    {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for show_task_implementation_plan action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        if (! $task->implementation_plans) {
            return "Task #{$taskId} ({$task->title}) has no implementation plan.";
        }

        return "Implementation Plan for Task #{$taskId} ({$task->title}):\n\n{$task->implementation_plans}";
    }

    private function showTaskNotes(User $user, int|float|null $taskId): string
    {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for show_task_notes action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $notes = $task->notes()->orderBy('created_at')->get();

        if ($notes->isEmpty()) {
            return "Task #{$taskId} ({$task->title}) has no notes.";
        }

        $lines = ["Notes for Task #{$taskId} ({$task->title}):", ''];

        foreach ($notes as $note) {
            /** @var KanbanTaskNoteAuthor $noteAuthor */
            $noteAuthor = $note->author;
            $author = $noteAuthor === KanbanTaskNoteAuthor::Ai ? 'AI' : 'User';

            /** @var Carbon $createdAt */
            $createdAt = $note->created_at;
            $timestamp = $createdAt->format('Y-m-d H:i');

            $lines[] = "[{$author} - {$timestamp}]";
            $lines[] = $note->note;
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function moveTask(User $user, int|float|null $taskId, int|float|null $columnId, int|float|null $position): string
    {
        if ($taskId === null || $columnId === null) {
            return 'Error: task_id and column_id parameters are required for move_task action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $targetColumn = $this->verifyColumnOwnership($user, (int) $columnId);

        if (! $targetColumn) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

        $taskBoard = $task->column->board;

        if ($targetColumn->board->id !== $taskBoard->id) {
            return "Error: Cannot move task to a column in a different board. Task is in Board #{$taskBoard->id}, target column is in Board #{$targetColumn->board->id}.";
        }

        $finalPosition = $position !== null ? (int) $position : ($targetColumn->tasks()->max('position') ?? -1) + 1;

        $task = app(MoveTaskAction::class)->handle($task, (int) $columnId, $finalPosition);

        return "Task #{$taskId} ({$task->title}) moved to Column #{$columnId} ({$targetColumn->name}) at position {$task->position}.";
    }

    private function addTaskNote(User $user, int|float|null $taskId, ?string $note): string
    {
        if ($taskId === null || $note === null) {
            return 'Error: task_id and note parameters are required for add_task_note action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $createdNote = app(CreateTaskNoteAction::class)->handle(
            $task,
            $note,
            KanbanTaskNoteAuthor::Ai
        );

        $preview = Str::limit($note, 100);

        return "Note added to Task #{$taskId} ({$task->title}).\nNote ID: #{$createdNote->id}\nPreview: {$preview}";
    }

    /**
     * @param array<string, mixed> $args
     */
    private function updateTask(User $user, array $args): string
    {
        $taskId = $args['task_id'] ?? null;

        if ($taskId === null) {
            return 'Error: task_id parameter is required for update_task action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $data = [];
        $changes = [];

        $title = $args['title'] ?? null;
        if ($title !== null) {
            $data['title'] = $title;
            $changes[] = "title to \"{$title}\"";
        }

        $description = $args['description'] ?? null;
        if ($description !== null) {
            $data['description'] = $description;
            $changes[] = 'description updated';
        }

        $implementationPlans = $args['implementation_plans'] ?? null;
        if ($implementationPlans !== null) {
            $data['implementation_plans'] = $implementationPlans;
            $changes[] = 'implementation_plans updated';
        }

        $dueDate = $args['due_date'] ?? null;
        if ($dueDate !== null) {
            if ($dueDate === 'clear') {
                $data['due_date'] = null;
                $changes[] = 'due_date cleared';
            } else {
                try {
                    $parsedDate = Carbon::parse($dueDate);
                    $data['due_date'] = $parsedDate->format('Y-m-d');
                    $changes[] = "due_date to {$data['due_date']}";
                } catch (\Exception $e) {
                    return "Error: Invalid date format '{$dueDate}'. Use YYYY-MM-DD format or 'clear' to remove.";
                }
            }
        }

        $priority = $args['priority'] ?? null;
        if ($priority !== null) {
            if ($priority === 'clear') {
                $data['priority'] = null;
                $changes[] = 'priority cleared';
            } else {
                $priorityEnum = KanbanTaskPriority::tryFrom($priority);

                if (! $priorityEnum) {
                    return "Error: Invalid priority '{$priority}'. Valid values are: low, medium, high, or 'clear'.";
                }

                $data['priority'] = $priorityEnum->value;
                $changes[] = "priority to {$priority}";
            }
        }

        if (empty($data)) {
            return "Error: No fields provided to update for Task #{$taskId}. Provide at least one of: title, description, implementation_plans, due_date, priority.";
        }

        app(UpdateTaskAction::class)->handle($task, $data);

        return "Task #{$taskId} updated successfully. Changes: " . implode(', ', $changes) . '.';
    }

    private function deleteTask(User $user, int|float|null $taskId): string
    {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for delete_task action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $taskTitle = $task->title;
        $columnName = $task->column->name;
        $boardName = $task->column->board->name;

        app(DeleteTaskAction::class)->handle($task);

        return "Task #{$taskId} ({$taskTitle}) deleted from Column \"{$columnName}\" in Board \"{$boardName}\".";
    }

    private function verifyBoardOwnership(User $user, int $boardId): ?KanbanBoard
    {
        return $user->kanbanBoards()->find($boardId);
    }

    private function verifyTaskOwnership(User $user, int $taskId): ?KanbanTask
    {
        $task = KanbanTask::with('column.board')->find($taskId);

        if (! $task || $task->column->board->user_id !== $user->id) {
            return null;
        }

        return $task;
    }

    private function verifyColumnOwnership(User $user, int $columnId): ?KanbanColumn
    {
        $column = KanbanColumn::with('board')->find($columnId);

        if (! $column || $column->board->user_id !== $user->id) {
            return null;
        }

        return $column;
    }
}
