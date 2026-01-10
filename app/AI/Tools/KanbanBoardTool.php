<?php

declare(strict_types=1);

namespace App\AI\Tools;

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
use Prism\Prism\Tool;

class KanbanBoardTool extends Tool
{
    public function __construct(
        private User $user
    ) {
        $this
            ->as('manage_kanban')
            ->for('Manage kanban boards, columns, and tasks. Use to view boards, list tasks, update tasks, move tasks between columns, view implementation plans, view/add notes, and delete tasks. Markdown is supported in description, implementation_plans, and notes fields.')
            ->withEnumParameter('action', 'The action to perform', [
                'list_boards',
                'list_columns',
                'list_tasks',
                'show_task_implementation_plan',
                'show_task_notes',
                'move_task',
                'add_task_note',
                'update_task',
                'delete_task',
            ])
            ->withNumberParameter('board_id', 'Board ID (required for: list_columns, list_tasks)')
            ->withNumberParameter('column_id', 'Column ID (required for: list_tasks; destination for: move_task)')
            ->withNumberParameter('task_id', 'Task ID (required for: show_task_*, move_task, add_task_note, update_task, delete_task)')
            ->withNumberParameter('position', 'Position in column (optional for: move_task, defaults to end)')
            ->withStringParameter('note', 'Note content, markdown supported (required for: add_task_note)')
            ->withStringParameter('title', 'Task title (for: update_task)')
            ->withStringParameter('description', 'Task description, markdown supported (for: update_task)')
            ->withStringParameter('implementation_plans', 'Implementation plans, markdown supported (for: update_task)')
            ->withStringParameter('due_date', 'Due date YYYY-MM-DD format (for: update_task, use "clear" to remove)')
            ->withStringParameter('priority', 'Priority: low, medium, high, or "clear" to remove (for: update_task)')
            ->using($this);
    }

    public function __invoke(
        string $action,
        ?int $boardId = null,
        ?int $columnId = null,
        ?int $taskId = null,
        ?int $position = null,
        ?string $note = null,
        ?string $title = null,
        ?string $description = null,
        ?string $implementationPlans = null,
        ?string $dueDate = null,
        ?string $priority = null
    ): string {
        return match ($action) {
            'list_boards' => $this->listBoards(),
            'list_columns' => $this->listColumns($boardId),
            'list_tasks' => $this->listTasks($boardId, $columnId),
            'show_task_implementation_plan' => $this->showTaskImplementationPlan($taskId),
            'show_task_notes' => $this->showTaskNotes($taskId),
            'move_task' => $this->moveTask($taskId, $columnId, $position),
            'add_task_note' => $this->addTaskNote($taskId, $note),
            'update_task' => $this->updateTask($taskId, $title, $description, $implementationPlans, $dueDate, $priority),
            'delete_task' => $this->deleteTask($taskId),
            default => "Unknown action: {$action}. Valid actions are: list_boards, list_columns, list_tasks, show_task_implementation_plan, show_task_notes, move_task, add_task_note, update_task, delete_task.",
        };
    }

    private function listBoards(): string
    {
        $boards = $this->user->kanbanBoards()
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
                $lines[] = '  Description: '.Str::limit($board->description, 100);
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function listColumns(?int $boardId): string
    {
        if ($boardId === null) {
            return 'Error: board_id parameter is required for list_columns action.';
        }

        $board = $this->verifyBoardOwnership($boardId);

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

    private function listTasks(?int $boardId, ?int $columnId): string
    {
        if ($boardId === null || $columnId === null) {
            return 'Error: board_id and column_id parameters are required for list_tasks action.';
        }

        $board = $this->verifyBoardOwnership($boardId);

        if (! $board) {
            return "Error: Board #{$boardId} not found or you don't have access to it.";
        }

        $column = $board->columns()->find($columnId);

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
            /** @var \Carbon\Carbon|null $dueDate */
            $dueDate = $task->due_date;
            $dueDateInfo = $dueDate ? " [Due: {$dueDate->format('Y-m-d')}]" : '';

            $lines[] = "- Task #{$task->id}: {$task->title}{$priorityInfo}{$dueDateInfo}";

            if ($task->description) {
                $lines[] = '  Description: '.Str::limit($task->description, 150);
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

    private function showTaskImplementationPlan(?int $taskId): string
    {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for show_task_implementation_plan action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        if (! $task->implementation_plans) {
            return "Task #{$taskId} ({$task->title}) has no implementation plan.";
        }

        return "Implementation Plan for Task #{$taskId} ({$task->title}):\n\n{$task->implementation_plans}";
    }

    private function showTaskNotes(?int $taskId): string
    {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for show_task_notes action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

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
            /** @var \Carbon\Carbon $createdAt */
            $createdAt = $note->created_at;
            $timestamp = $createdAt->format('Y-m-d H:i');

            $lines[] = "[{$author} - {$timestamp}]";
            $lines[] = $note->note;
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function moveTask(?int $taskId, ?int $columnId, ?int $position): string
    {
        if ($taskId === null || $columnId === null) {
            return 'Error: task_id and column_id parameters are required for move_task action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $targetColumn = $this->verifyColumnOwnership($columnId);

        if (! $targetColumn) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

        $taskBoard = $task->column->board;

        if ($targetColumn->board->id !== $taskBoard->id) {
            return "Error: Cannot move task to a column in a different board. Task is in Board #{$taskBoard->id}, target column is in Board #{$targetColumn->board->id}.";
        }

        $finalPosition = $position ?? ($targetColumn->tasks()->max('position') ?? -1) + 1;

        $task = app(MoveTaskAction::class)->handle($task, $columnId, $finalPosition);

        return "Task #{$taskId} ({$task->title}) moved to Column #{$columnId} ({$targetColumn->name}) at position {$task->position}.";
    }

    private function addTaskNote(?int $taskId, ?string $note): string
    {
        if ($taskId === null || $note === null) {
            return 'Error: task_id and note parameters are required for add_task_note action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

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

    private function updateTask(
        ?int $taskId,
        ?string $title,
        ?string $description,
        ?string $implementationPlans,
        ?string $dueDate,
        ?string $priority
    ): string {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for update_task action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $data = [];
        $changes = [];

        if ($title !== null) {
            $data['title'] = $title;
            $changes[] = "title to \"{$title}\"";
        }

        if ($description !== null) {
            $data['description'] = $description;
            $changes[] = 'description updated';
        }

        if ($implementationPlans !== null) {
            $data['implementation_plans'] = $implementationPlans;
            $changes[] = 'implementation_plans updated';
        }

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

        return "Task #{$taskId} updated successfully. Changes: ".implode(', ', $changes).'.';
    }

    private function deleteTask(?int $taskId): string
    {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for delete_task action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $taskTitle = $task->title;
        $columnName = $task->column->name;
        $boardName = $task->column->board->name;

        app(DeleteTaskAction::class)->handle($task);

        return "Task #{$taskId} ({$taskTitle}) deleted from Column \"{$columnName}\" in Board \"{$boardName}\".";
    }

    private function verifyBoardOwnership(int $boardId): ?KanbanBoard
    {
        return $this->user->kanbanBoards()->find($boardId);
    }

    private function verifyTaskOwnership(int $taskId): ?KanbanTask
    {
        $task = KanbanTask::with('column.board')->find($taskId);

        if (! $task || $task->column->board->user_id !== $this->user->id) {
            return null;
        }

        return $task;
    }

    private function verifyColumnOwnership(int $columnId): ?KanbanColumn
    {
        $column = KanbanColumn::with('board')->find($columnId);

        if (! $column || $column->board->user_id !== $this->user->id) {
            return null;
        }

        return $column;
    }
}
