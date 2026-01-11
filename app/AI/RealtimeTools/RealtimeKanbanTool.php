<?php

declare(strict_types=1);

namespace App\AI\RealtimeTools;

use App\Actions\Kanban\CreateBoardAction;
use App\Actions\Kanban\CreateColumnAction;
use App\Actions\Kanban\CreateTaskAction;
use App\Actions\Kanban\CreateTaskNoteAction;
use App\Actions\Kanban\DeleteBoardAction;
use App\Actions\Kanban\DeleteColumnAction;
use App\Actions\Kanban\DeleteTaskAction;
use App\Actions\Kanban\DeleteTaskAttachmentAction;
use App\Actions\Kanban\DeleteTaskNoteAction;
use App\Actions\Kanban\MoveColumnAction;
use App\Actions\Kanban\MoveTaskAction;
use App\Actions\Kanban\UpdateBoardAction;
use App\Actions\Kanban\UpdateColumnAction;
use App\Actions\Kanban\UpdateTaskAction;
use App\Actions\Kanban\UpdateTaskNoteAction;
use App\Enums\KanbanTaskNoteAuthor;
use App\Enums\KanbanTaskPriority;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use App\Models\KanbanTaskAttachment;
use App\Models\KanbanTaskNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RealtimeKanbanTool
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function execute(User $user, array $args): string
    {
        $action = $args['action'] ?? null;

        return match ($action) {
            // Board actions
            'list_boards' => $this->listBoards($user),
            'create_board' => $this->createBoard($user, $args),
            'update_board' => $this->updateBoard($user, $args),
            'delete_board' => $this->deleteBoard($user, $args['board_id'] ?? null),
            // Column actions
            'list_columns' => $this->listColumns($user, $args['board_id'] ?? null),
            'create_column' => $this->createColumn($user, $args),
            'update_column' => $this->updateColumn($user, $args),
            'move_column' => $this->moveColumn($user, $args['column_id'] ?? null, $args['position'] ?? null),
            'delete_column' => $this->deleteColumn($user, $args['column_id'] ?? null),
            // Task actions
            'list_tasks' => $this->listTasks($user, $args['board_id'] ?? null, $args['column_id'] ?? null),
            'create_task' => $this->createTask($user, $args),
            'update_task' => $this->updateTask($user, $args),
            'move_task' => $this->moveTask($user, $args['task_id'] ?? null, $args['column_id'] ?? null, $args['position'] ?? null),
            'delete_task' => $this->deleteTask($user, $args['task_id'] ?? null),
            'show_task_implementation_plan' => $this->showTaskImplementationPlan($user, $args['task_id'] ?? null),
            // Note actions
            'show_task_notes' => $this->showTaskNotes($user, $args['task_id'] ?? null),
            'add_task_note' => $this->addTaskNote($user, $args['task_id'] ?? null, $args['note'] ?? null),
            'update_task_note' => $this->updateTaskNote($user, $args['note_id'] ?? null, $args['note'] ?? null),
            'delete_task_note' => $this->deleteTaskNote($user, $args['note_id'] ?? null),
            // Link actions
            'add_task_link' => $this->addTaskLink($user, $args['task_id'] ?? null, $args['url'] ?? null, $args['label'] ?? null),
            'remove_task_link' => $this->removeTaskLink($user, $args['task_id'] ?? null, $args['link_index'] ?? null),
            // Dependency actions
            'add_task_dependency' => $this->addTaskDependency($user, $args['task_id'] ?? null, $args['depends_on_task_id'] ?? null),
            'remove_task_dependency' => $this->removeTaskDependency($user, $args['task_id'] ?? null, $args['depends_on_task_id'] ?? null),
            // Attachment actions
            'list_task_attachments' => $this->listTaskAttachments($user, $args['task_id'] ?? null),
            'delete_task_attachment' => $this->deleteTaskAttachment($user, $args['attachment_id'] ?? null),
            default => "Unknown action: {$action}.",
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
                $lines[] = '  Description: '.Str::limit($board->description, 100);
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
            ->with(['dependencies:id,title', 'attachments'])
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
                $lines[] = '  Description: '.Str::limit($task->description, 150);
            }

            if ($task->dependencies->isNotEmpty()) {
                $depIds = $task->dependencies->pluck('id')->join(', #');
                $lines[] = "  Depends on: #{$depIds}";
            }

            // Show links
            /** @var array<int, array{url: string, label?: string}>|null $links */
            $links = $task->links;
            if (! empty($links)) {
                $linkStrs = [];
                foreach ($links as $index => $link) {
                    $linkLabel = $link['label'] ?? $link['url'];
                    $linkStrs[] = "[{$index}] {$linkLabel}";
                }
                $lines[] = '  Links: '.implode(', ', $linkStrs);
            }

            // Show attachments
            if ($task->attachments->isNotEmpty()) {
                $attachmentStrs = [];
                foreach ($task->attachments as $attachment) {
                    $attachmentStrs[] = "#{$attachment->id}: {$attachment->original_filename}";
                }
                $lines[] = '  Attachments: '.implode(', ', $attachmentStrs);
            }

            $lines[] = '';
        }

        $lines[] = 'Note: Use show_task_implementation_plan, show_task_notes, or list_task_attachments for full details.';

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
     * @param  array<string, mixed>  $args
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

        return "Task #{$taskId} updated successfully. Changes: ".implode(', ', $changes).'.';
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

    // ========== Board Operations ==========

    /**
     * @param  array<string, mixed>  $args
     */
    private function createBoard(User $user, array $args): string
    {
        $name = $args['name'] ?? null;

        if ($name === null) {
            return 'Error: name parameter is required for create_board action.';
        }

        $description = $args['description'] ?? null;
        $projectName = $args['project_name'] ?? null;

        $board = app(CreateBoardAction::class)->handle(
            $user,
            $name,
            $description,
            $projectName
        );

        $projectInfo = $projectName ? " (Project: {$projectName})" : '';

        return "Board #{$board->id} \"{$board->name}\"{$projectInfo} created successfully.";
    }

    /**
     * @param  array<string, mixed>  $args
     */
    private function updateBoard(User $user, array $args): string
    {
        $boardId = $args['board_id'] ?? null;

        if ($boardId === null) {
            return 'Error: board_id parameter is required for update_board action.';
        }

        $board = $this->verifyBoardOwnership($user, (int) $boardId);

        if (! $board) {
            return "Error: Board #{$boardId} not found or you don't have access to it.";
        }

        $data = [];
        $changes = [];

        $name = $args['name'] ?? null;
        if ($name !== null) {
            $data['name'] = $name;
            $changes[] = "name to \"{$name}\"";
        }

        $description = $args['description'] ?? null;
        if ($description !== null) {
            $data['description'] = $description;
            $changes[] = 'description updated';
        }

        $projectName = $args['project_name'] ?? null;
        if ($projectName !== null) {
            $data['project_name'] = $projectName;
            $changes[] = "project_name to \"{$projectName}\"";
        }

        if (empty($data)) {
            return 'Error: No fields provided to update. Provide at least one of: name, description, project_name.';
        }

        app(UpdateBoardAction::class)->handle($board, $data);

        return "Board #{$boardId} updated successfully. Changes: ".implode(', ', $changes).'.';
    }

    private function deleteBoard(User $user, int|float|null $boardId): string
    {
        if ($boardId === null) {
            return 'Error: board_id parameter is required for delete_board action.';
        }

        $board = $this->verifyBoardOwnership($user, (int) $boardId);

        if (! $board) {
            return "Error: Board #{$boardId} not found or you don't have access to it.";
        }

        $boardName = $board->name;
        $columnCount = $board->columns()->count();
        $taskCount = $board->columns()->withCount('tasks')->get()->sum('tasks_count');

        app(DeleteBoardAction::class)->handle($board);

        return "Board #{$boardId} \"{$boardName}\" deleted with {$columnCount} columns and {$taskCount} tasks.";
    }

    // ========== Column Operations ==========

    /**
     * @param  array<string, mixed>  $args
     */
    private function createColumn(User $user, array $args): string
    {
        $boardId = $args['board_id'] ?? null;
        $name = $args['name'] ?? null;

        if ($boardId === null || $name === null) {
            return 'Error: board_id and name parameters are required for create_column action.';
        }

        $board = $this->verifyBoardOwnership($user, (int) $boardId);

        if (! $board) {
            return "Error: Board #{$boardId} not found or you don't have access to it.";
        }

        $color = $args['color'] ?? null;
        $description = $args['description'] ?? null;

        $column = app(CreateColumnAction::class)->handle($board, $name, $color, $description);

        $colorInfo = $color ? " [Color: {$color}]" : '';

        return "Column #{$column->id} \"{$column->name}\"{$colorInfo} created in Board #{$boardId} at position {$column->position}.";
    }

    /**
     * @param  array<string, mixed>  $args
     */
    private function updateColumn(User $user, array $args): string
    {
        $columnId = $args['column_id'] ?? null;

        if ($columnId === null) {
            return 'Error: column_id parameter is required for update_column action.';
        }

        $column = $this->verifyColumnOwnership($user, (int) $columnId);

        if (! $column) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

        $data = [];
        $changes = [];

        $name = $args['name'] ?? null;
        if ($name !== null) {
            $data['name'] = $name;
            $changes[] = "name to \"{$name}\"";
        }

        $color = $args['color'] ?? null;
        if ($color !== null) {
            $data['color'] = $color;
            $changes[] = "color to {$color}";
        }

        $description = $args['description'] ?? null;
        if ($description !== null) {
            $data['description'] = $description;
            $changes[] = 'description updated';
        }

        if (empty($data)) {
            return 'Error: No fields provided to update. Provide at least one of: name, color, description.';
        }

        app(UpdateColumnAction::class)->handle($column, $data);

        return "Column #{$columnId} updated successfully. Changes: ".implode(', ', $changes).'.';
    }

    private function moveColumn(User $user, int|float|null $columnId, int|float|null $position): string
    {
        if ($columnId === null || $position === null) {
            return 'Error: column_id and position parameters are required for move_column action.';
        }

        $column = $this->verifyColumnOwnership($user, (int) $columnId);

        if (! $column) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

        $column = app(MoveColumnAction::class)->handle($column, (int) $position);

        return "Column #{$columnId} \"{$column->name}\" moved to position {$column->position}.";
    }

    private function deleteColumn(User $user, int|float|null $columnId): string
    {
        if ($columnId === null) {
            return 'Error: column_id parameter is required for delete_column action.';
        }

        $column = $this->verifyColumnOwnership($user, (int) $columnId);

        if (! $column) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

        $columnName = $column->name;
        $taskCount = $column->tasks()->count();
        $boardName = $column->board->name;

        app(DeleteColumnAction::class)->handle($column);

        return "Column #{$columnId} \"{$columnName}\" deleted from Board \"{$boardName}\" with {$taskCount} tasks.";
    }

    // ========== Task Creation ==========

    /**
     * @param  array<string, mixed>  $args
     */
    private function createTask(User $user, array $args): string
    {
        $columnId = $args['column_id'] ?? null;
        $title = $args['title'] ?? null;

        if ($columnId === null || $title === null) {
            return 'Error: column_id and title parameters are required for create_task action.';
        }

        $column = $this->verifyColumnOwnership($user, (int) $columnId);

        if (! $column) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

        $description = $args['description'] ?? null;
        $implementationPlans = $args['implementation_plans'] ?? null;
        $dueDate = $args['due_date'] ?? null;
        $priority = $args['priority'] ?? null;
        $position = $args['position'] ?? null;

        $parsedDueDate = null;
        if ($dueDate !== null) {
            try {
                $parsedDueDate = Carbon::parse($dueDate);
            } catch (\Exception $e) {
                return "Error: Invalid date format '{$dueDate}'. Use YYYY-MM-DD format.";
            }
        }

        $priorityEnum = null;
        if ($priority !== null) {
            $priorityEnum = KanbanTaskPriority::tryFrom($priority);

            if (! $priorityEnum) {
                return "Error: Invalid priority '{$priority}'. Valid values are: low, medium, high.";
            }
        }

        $task = app(CreateTaskAction::class)->handle(
            $column,
            $title,
            $description,
            $implementationPlans,
            $parsedDueDate,
            $priorityEnum
        );

        // Handle custom position if provided
        if ($position !== null && (int) $position !== $task->position) {
            app(MoveTaskAction::class)->handle($task, (int) $columnId, (int) $position);
            $task->refresh();
        }

        return "Task #{$task->id} \"{$task->title}\" created in Column \"{$column->name}\" at position {$task->position}.";
    }

    // ========== Note Operations ==========

    private function updateTaskNote(User $user, int|float|null $noteId, ?string $note): string
    {
        if ($noteId === null || $note === null) {
            return 'Error: note_id and note parameters are required for update_task_note action.';
        }

        $noteModel = $this->verifyNoteOwnership($user, (int) $noteId);

        if (! $noteModel) {
            return "Error: Note #{$noteId} not found or you don't have access to it.";
        }

        app(UpdateTaskNoteAction::class)->handle($noteModel, $note);

        $preview = Str::limit($note, 100);

        return "Note #{$noteId} updated successfully.\nPreview: {$preview}";
    }

    private function deleteTaskNote(User $user, int|float|null $noteId): string
    {
        if ($noteId === null) {
            return 'Error: note_id parameter is required for delete_task_note action.';
        }

        $noteModel = $this->verifyNoteOwnership($user, (int) $noteId);

        if (! $noteModel) {
            return "Error: Note #{$noteId} not found or you don't have access to it.";
        }

        $taskTitle = $noteModel->task->title;

        app(DeleteTaskNoteAction::class)->handle($noteModel);

        return "Note #{$noteId} deleted from Task \"{$taskTitle}\".";
    }

    // ========== Link Operations ==========

    private function addTaskLink(User $user, int|float|null $taskId, ?string $url, ?string $label): string
    {
        if ($taskId === null || $url === null) {
            return 'Error: task_id and url parameters are required for add_task_link action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        /** @var array<int, array{url: string, label?: string}> $links */
        $links = $task->links ?? [];
        $newLink = ['url' => $url];
        if ($label !== null) {
            $newLink['label'] = $label;
        }
        $links[] = $newLink;

        $task->update(['links' => $links]);

        $linkDisplay = $label ?? $url;
        $index = count($links) - 1;

        return "Link [{$index}] \"{$linkDisplay}\" added to Task #{$taskId} \"{$task->title}\".";
    }

    private function removeTaskLink(User $user, int|float|null $taskId, int|float|null $linkIndex): string
    {
        if ($taskId === null || $linkIndex === null) {
            return 'Error: task_id and link_index parameters are required for remove_task_link action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        /** @var array<int, array{url: string, label?: string}> $links */
        $links = $task->links ?? [];
        $idx = (int) $linkIndex;

        if (! isset($links[$idx])) {
            return "Error: Link index {$linkIndex} not found on Task #{$taskId}. Valid indices: 0-".(count($links) - 1).'.';
        }

        $removedLink = $links[$idx];
        $linkDisplay = $removedLink['label'] ?? $removedLink['url'];

        array_splice($links, $idx, 1);
        $task->update(['links' => $links]);

        return "Link [{$linkIndex}] \"{$linkDisplay}\" removed from Task #{$taskId} \"{$task->title}\".";
    }

    // ========== Dependency Operations ==========

    private function addTaskDependency(User $user, int|float|null $taskId, int|float|null $dependsOnTaskId): string
    {
        if ($taskId === null || $dependsOnTaskId === null) {
            return 'Error: task_id and depends_on_task_id parameters are required for add_task_dependency action.';
        }

        if ((int) $taskId === (int) $dependsOnTaskId) {
            return 'Error: A task cannot depend on itself.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $dependsOnTask = $this->verifyTaskOwnership($user, (int) $dependsOnTaskId);

        if (! $dependsOnTask) {
            return "Error: Dependency task #{$dependsOnTaskId} not found or you don't have access to it.";
        }

        // Check same board
        if ($task->column->kanban_board_id !== $dependsOnTask->column->kanban_board_id) {
            return 'Error: Dependencies must be between tasks on the same board.';
        }

        // Check if already exists
        if ($task->dependencies()->where('depends_on_task_id', (int) $dependsOnTaskId)->exists()) {
            return "Error: Task #{$taskId} already depends on Task #{$dependsOnTaskId}.";
        }

        $task->dependencies()->attach((int) $dependsOnTaskId);

        return "Task #{$taskId} \"{$task->title}\" now depends on Task #{$dependsOnTaskId} \"{$dependsOnTask->title}\".";
    }

    private function removeTaskDependency(User $user, int|float|null $taskId, int|float|null $dependsOnTaskId): string
    {
        if ($taskId === null || $dependsOnTaskId === null) {
            return 'Error: task_id and depends_on_task_id parameters are required for remove_task_dependency action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        // Check if dependency exists
        if (! $task->dependencies()->where('depends_on_task_id', (int) $dependsOnTaskId)->exists()) {
            return "Error: Task #{$taskId} does not depend on Task #{$dependsOnTaskId}.";
        }

        $dependsOnTask = KanbanTask::find((int) $dependsOnTaskId);
        $task->dependencies()->detach((int) $dependsOnTaskId);

        $depTaskTitle = $dependsOnTask ? $dependsOnTask->title : "#{$dependsOnTaskId}";

        return "Dependency removed: Task #{$taskId} \"{$task->title}\" no longer depends on Task #{$dependsOnTaskId} \"{$depTaskTitle}\".";
    }

    // ========== Attachment Operations ==========

    private function listTaskAttachments(User $user, int|float|null $taskId): string
    {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for list_task_attachments action.';
        }

        $task = $this->verifyTaskOwnership($user, (int) $taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $attachments = $task->attachments;

        if ($attachments->isEmpty()) {
            return "Task #{$taskId} \"{$task->title}\" has no attachments.";
        }

        $lines = ["Attachments for Task #{$taskId} \"{$task->title}\":", ''];

        foreach ($attachments as $attachment) {
            $sizeKb = round($attachment->size / 1024, 1);
            $typeInfo = $attachment->isImage() ? ' [Image]' : ($attachment->isVideo() ? ' [Video]' : '');
            $lines[] = "- Attachment #{$attachment->id}: {$attachment->original_filename}{$typeInfo}";
            $lines[] = "  Type: {$attachment->mime_type} | Size: {$sizeKb} KB";
        }

        return implode("\n", $lines);
    }

    private function deleteTaskAttachment(User $user, int|float|null $attachmentId): string
    {
        if ($attachmentId === null) {
            return 'Error: attachment_id parameter is required for delete_task_attachment action.';
        }

        $attachment = $this->verifyAttachmentOwnership($user, (int) $attachmentId);

        if (! $attachment) {
            return "Error: Attachment #{$attachmentId} not found or you don't have access to it.";
        }

        $filename = $attachment->original_filename;
        $taskTitle = $attachment->task->title;

        app(DeleteTaskAttachmentAction::class)->handle($attachment);

        return "Attachment #{$attachmentId} \"{$filename}\" deleted from Task \"{$taskTitle}\".";
    }

    // ========== Ownership Verification ==========

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

    private function verifyNoteOwnership(User $user, int $noteId): ?KanbanTaskNote
    {
        $note = KanbanTaskNote::with('task.column.board')->find($noteId);

        if (! $note || $note->task->column->board->user_id !== $user->id) {
            return null;
        }

        return $note;
    }

    private function verifyAttachmentOwnership(User $user, int $attachmentId): ?KanbanTaskAttachment
    {
        $attachment = KanbanTaskAttachment::with('task.column.board')->find($attachmentId);

        if (! $attachment || $attachment->task->column->board->user_id !== $user->id) {
            return null;
        }

        return $attachment;
    }
}
