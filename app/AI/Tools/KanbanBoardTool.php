<?php

declare(strict_types=1);

namespace App\AI\Tools;

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
use Prism\Prism\Tool;

class KanbanBoardTool extends Tool
{
    public function __construct(
        private readonly User $user
    ) {
        $this
            ->as('manage_kanban')
            ->for('Full kanban management: boards, columns, tasks, links, dependencies, notes, and attachments. Markdown supported in descriptions, implementation_plans, and notes.')
            ->withEnumParameter('action', 'The action to perform', [
                // Board actions
                'list_boards',
                'create_board',
                'update_board',
                'delete_board',
                // Column actions
                'list_columns',
                'create_column',
                'update_column',
                'move_column',
                'delete_column',
                // Task actions
                'list_tasks',
                'create_task',
                'update_task',
                'move_task',
                'delete_task',
                'show_task_implementation_plan',
                // Note actions
                'show_task_notes',
                'add_task_note',
                'update_task_note',
                'delete_task_note',
                // Link actions
                'add_task_link',
                'remove_task_link',
                // Dependency actions
                'add_task_dependency',
                'remove_task_dependency',
                // Attachment actions
                'list_task_attachments',
                'delete_task_attachment',
            ])
            // Board parameters
            ->withNumberParameter('board_id', 'Board ID (for: list_columns, list_tasks, update_board, delete_board, create_column)')
            ->withStringParameter('name', 'Name (for: create_board, update_board, create_column, update_column)')
            ->withStringParameter('project_name', 'Project name (for: create_board, update_board)')
            // Column parameters
            ->withNumberParameter('column_id', 'Column ID (for: list_tasks, create_task, update_column, move_column, delete_column, move_task)')
            ->withStringParameter('color', 'Hex color code (for: create_column, update_column)')
            // Task parameters
            ->withNumberParameter('task_id', 'Task ID (for: task operations, notes, links, dependencies)')
            ->withStringParameter('title', 'Task title (for: create_task, update_task)')
            ->withStringParameter('description', 'Description, markdown supported (for: create_board, update_board, create_task, update_task, create_column, update_column)')
            ->withStringParameter('implementation_plans', 'Implementation plans, markdown supported (for: create_task, update_task)')
            ->withStringParameter('due_date', 'Due date YYYY-MM-DD (for: create_task, update_task, "clear" to remove)')
            ->withStringParameter('priority', 'Priority: low, medium, high, or "clear" to remove')
            ->withNumberParameter('position', 'Position (for: move_task, move_column, create_task)')
            // Note parameters
            ->withNumberParameter('note_id', 'Note ID (for: update_task_note, delete_task_note)')
            ->withStringParameter('note', 'Note content, markdown supported (for: add_task_note, update_task_note)')
            // Link parameters
            ->withStringParameter('url', 'URL (for: add_task_link)')
            ->withStringParameter('label', 'Link label (for: add_task_link)')
            ->withNumberParameter('link_index', 'Link index 0-based (for: remove_task_link)')
            // Dependency parameters
            ->withNumberParameter('depends_on_task_id', 'Dependency task ID (for: add_task_dependency, remove_task_dependency)')
            // Attachment parameters
            ->withNumberParameter('attachment_id', 'Attachment ID (for: delete_task_attachment)')
            ->using($this);

        parent::__construct();
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
        ?string $priority = null,
        ?string $name = null,
        ?string $projectName = null,
        ?string $color = null,
        ?int $noteId = null,
        ?string $url = null,
        ?string $label = null,
        ?int $linkIndex = null,
        ?int $dependsOnTaskId = null,
        ?int $attachmentId = null
    ): string {
        return match ($action) {
            // Board actions
            'list_boards' => $this->listBoards(),
            'create_board' => $this->createBoard($name, $description, $projectName),
            'update_board' => $this->updateBoard($boardId, $name, $description, $projectName),
            'delete_board' => $this->deleteBoard($boardId),
            // Column actions
            'list_columns' => $this->listColumns($boardId),
            'create_column' => $this->createColumn($boardId, $name, $color, $description),
            'update_column' => $this->updateColumn($columnId, $name, $color, $description),
            'move_column' => $this->moveColumn($columnId, $position),
            'delete_column' => $this->deleteColumn($columnId),
            // Task actions
            'list_tasks' => $this->listTasks($boardId, $columnId),
            'create_task' => $this->createTask($columnId, $title, $description, $implementationPlans, $dueDate, $priority, $position),
            'update_task' => $this->updateTask($taskId, $title, $description, $implementationPlans, $dueDate, $priority),
            'move_task' => $this->moveTask($taskId, $columnId, $position),
            'delete_task' => $this->deleteTask($taskId),
            'show_task_implementation_plan' => $this->showTaskImplementationPlan($taskId),
            // Note actions
            'show_task_notes' => $this->showTaskNotes($taskId),
            'add_task_note' => $this->addTaskNote($taskId, $note),
            'update_task_note' => $this->updateTaskNote($noteId, $note),
            'delete_task_note' => $this->deleteTaskNote($noteId),
            // Link actions
            'add_task_link' => $this->addTaskLink($taskId, $url, $label),
            'remove_task_link' => $this->removeTaskLink($taskId, $linkIndex),
            // Dependency actions
            'add_task_dependency' => $this->addTaskDependency($taskId, $dependsOnTaskId),
            'remove_task_dependency' => $this->removeTaskDependency($taskId, $dependsOnTaskId),
            // Attachment actions
            'list_task_attachments' => $this->listTaskAttachments($taskId),
            'delete_task_attachment' => $this->deleteTaskAttachment($attachmentId),
            default => "Unknown action: {$action}.",
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

    // ========== Board Operations ==========

    private function createBoard(?string $name, ?string $description, ?string $projectName): string
    {
        if ($name === null) {
            return 'Error: name parameter is required for create_board action.';
        }

        $board = app(CreateBoardAction::class)->handle(
            $this->user,
            $name,
            $description,
            $projectName
        );

        $projectInfo = $projectName ? " (Project: {$projectName})" : '';

        return "Board #{$board->id} \"{$board->name}\"{$projectInfo} created successfully.";
    }

    private function updateBoard(?int $boardId, ?string $name, ?string $description, ?string $projectName): string
    {
        if ($boardId === null) {
            return 'Error: board_id parameter is required for update_board action.';
        }

        $board = $this->verifyBoardOwnership($boardId);

        if (! $board) {
            return "Error: Board #{$boardId} not found or you don't have access to it.";
        }

        $data = [];
        $changes = [];

        if ($name !== null) {
            $data['name'] = $name;
            $changes[] = "name to \"{$name}\"";
        }

        if ($description !== null) {
            $data['description'] = $description;
            $changes[] = 'description updated';
        }

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

    private function deleteBoard(?int $boardId): string
    {
        if ($boardId === null) {
            return 'Error: board_id parameter is required for delete_board action.';
        }

        $board = $this->verifyBoardOwnership($boardId);

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

    private function createColumn(?int $boardId, ?string $name, ?string $color, ?string $description): string
    {
        if ($boardId === null || $name === null) {
            return 'Error: board_id and name parameters are required for create_column action.';
        }

        $board = $this->verifyBoardOwnership($boardId);

        if (! $board) {
            return "Error: Board #{$boardId} not found or you don't have access to it.";
        }

        $column = app(CreateColumnAction::class)->handle($board, $name, $color, $description);

        $colorInfo = $color ? " [Color: {$color}]" : '';

        return "Column #{$column->id} \"{$column->name}\"{$colorInfo} created in Board #{$boardId} at position {$column->position}.";
    }

    private function updateColumn(?int $columnId, ?string $name, ?string $color, ?string $description): string
    {
        if ($columnId === null) {
            return 'Error: column_id parameter is required for update_column action.';
        }

        $column = $this->verifyColumnOwnership($columnId);

        if (! $column) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

        $data = [];
        $changes = [];

        if ($name !== null) {
            $data['name'] = $name;
            $changes[] = "name to \"{$name}\"";
        }

        if ($color !== null) {
            $data['color'] = $color;
            $changes[] = "color to {$color}";
        }

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

    private function moveColumn(?int $columnId, ?int $position): string
    {
        if ($columnId === null || $position === null) {
            return 'Error: column_id and position parameters are required for move_column action.';
        }

        $column = $this->verifyColumnOwnership($columnId);

        if (! $column) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

        $column = app(MoveColumnAction::class)->handle($column, $position);

        return "Column #{$columnId} \"{$column->name}\" moved to position {$column->position}.";
    }

    private function deleteColumn(?int $columnId): string
    {
        if ($columnId === null) {
            return 'Error: column_id parameter is required for delete_column action.';
        }

        $column = $this->verifyColumnOwnership($columnId);

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

    private function createTask(
        ?int $columnId,
        ?string $title,
        ?string $description,
        ?string $implementationPlans,
        ?string $dueDate,
        ?string $priority,
        ?int $position
    ): string {
        if ($columnId === null || $title === null) {
            return 'Error: column_id and title parameters are required for create_task action.';
        }

        $column = $this->verifyColumnOwnership($columnId);

        if (! $column) {
            return "Error: Column #{$columnId} not found or you don't have access to it.";
        }

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
        if ($position !== null && $position !== $task->position) {
            app(MoveTaskAction::class)->handle($task, $columnId, $position);
            $task->refresh();
        }

        return "Task #{$task->id} \"{$task->title}\" created in Column \"{$column->name}\" at position {$task->position}.";
    }

    // ========== Note Operations ==========

    private function updateTaskNote(?int $noteId, ?string $note): string
    {
        if ($noteId === null || $note === null) {
            return 'Error: note_id and note parameters are required for update_task_note action.';
        }

        $noteModel = $this->verifyNoteOwnership($noteId);

        if (! $noteModel) {
            return "Error: Note #{$noteId} not found or you don't have access to it.";
        }

        app(UpdateTaskNoteAction::class)->handle($noteModel, $note);

        $preview = Str::limit($note, 100);

        return "Note #{$noteId} updated successfully.\nPreview: {$preview}";
    }

    private function deleteTaskNote(?int $noteId): string
    {
        if ($noteId === null) {
            return 'Error: note_id parameter is required for delete_task_note action.';
        }

        $noteModel = $this->verifyNoteOwnership($noteId);

        if (! $noteModel) {
            return "Error: Note #{$noteId} not found or you don't have access to it.";
        }

        $taskTitle = $noteModel->task->title;

        app(DeleteTaskNoteAction::class)->handle($noteModel);

        return "Note #{$noteId} deleted from Task \"{$taskTitle}\".";
    }

    // ========== Link Operations ==========

    private function addTaskLink(?int $taskId, ?string $url, ?string $label): string
    {
        if ($taskId === null || $url === null) {
            return 'Error: task_id and url parameters are required for add_task_link action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

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

    private function removeTaskLink(?int $taskId, ?int $linkIndex): string
    {
        if ($taskId === null || $linkIndex === null) {
            return 'Error: task_id and link_index parameters are required for remove_task_link action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        /** @var array<int, array{url: string, label?: string}> $links */
        $links = $task->links ?? [];

        if (! isset($links[$linkIndex])) {
            return "Error: Link index {$linkIndex} not found on Task #{$taskId}. Valid indices: 0-".(count($links) - 1).'.';
        }

        $removedLink = $links[$linkIndex];
        $linkDisplay = $removedLink['label'] ?? $removedLink['url'];

        array_splice($links, $linkIndex, 1);
        $task->update(['links' => $links]);

        return "Link [{$linkIndex}] \"{$linkDisplay}\" removed from Task #{$taskId} \"{$task->title}\".";
    }

    // ========== Dependency Operations ==========

    private function addTaskDependency(?int $taskId, ?int $dependsOnTaskId): string
    {
        if ($taskId === null || $dependsOnTaskId === null) {
            return 'Error: task_id and depends_on_task_id parameters are required for add_task_dependency action.';
        }

        if ($taskId === $dependsOnTaskId) {
            return 'Error: A task cannot depend on itself.';
        }

        $task = $this->verifyTaskOwnership($taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        $dependsOnTask = $this->verifyTaskOwnership($dependsOnTaskId);

        if (! $dependsOnTask) {
            return "Error: Dependency task #{$dependsOnTaskId} not found or you don't have access to it.";
        }

        // Check same board
        if ($task->column->kanban_board_id !== $dependsOnTask->column->kanban_board_id) {
            return 'Error: Dependencies must be between tasks on the same board.';
        }

        // Check if already exists
        if ($task->dependencies()->where('depends_on_task_id', $dependsOnTaskId)->exists()) {
            return "Error: Task #{$taskId} already depends on Task #{$dependsOnTaskId}.";
        }

        $task->dependencies()->attach($dependsOnTaskId);

        return "Task #{$taskId} \"{$task->title}\" now depends on Task #{$dependsOnTaskId} \"{$dependsOnTask->title}\".";
    }

    private function removeTaskDependency(?int $taskId, ?int $dependsOnTaskId): string
    {
        if ($taskId === null || $dependsOnTaskId === null) {
            return 'Error: task_id and depends_on_task_id parameters are required for remove_task_dependency action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

        if (! $task) {
            return "Error: Task #{$taskId} not found or you don't have access to it.";
        }

        // Check if dependency exists
        if (! $task->dependencies()->where('depends_on_task_id', $dependsOnTaskId)->exists()) {
            return "Error: Task #{$taskId} does not depend on Task #{$dependsOnTaskId}.";
        }

        $dependsOnTask = KanbanTask::find($dependsOnTaskId);
        $task->dependencies()->detach($dependsOnTaskId);

        $depTaskTitle = $dependsOnTask ? $dependsOnTask->title : "#{$dependsOnTaskId}";

        return "Dependency removed: Task #{$taskId} \"{$task->title}\" no longer depends on Task #{$dependsOnTaskId} \"{$depTaskTitle}\".";
    }

    // ========== Attachment Operations ==========

    private function listTaskAttachments(?int $taskId): string
    {
        if ($taskId === null) {
            return 'Error: task_id parameter is required for list_task_attachments action.';
        }

        $task = $this->verifyTaskOwnership($taskId);

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

    private function deleteTaskAttachment(?int $attachmentId): string
    {
        if ($attachmentId === null) {
            return 'Error: attachment_id parameter is required for delete_task_attachment action.';
        }

        $attachment = $this->verifyAttachmentOwnership($attachmentId);

        if (! $attachment) {
            return "Error: Attachment #{$attachmentId} not found or you don't have access to it.";
        }

        $filename = $attachment->original_filename;
        $taskTitle = $attachment->task->title;

        app(DeleteTaskAttachmentAction::class)->handle($attachment);

        return "Attachment #{$attachmentId} \"{$filename}\" deleted from Task \"{$taskTitle}\".";
    }

    // ========== Ownership Verification ==========

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

    private function verifyNoteOwnership(int $noteId): ?KanbanTaskNote
    {
        $note = KanbanTaskNote::with('task.column.board')->find($noteId);

        if (! $note || $note->task->column->board->user_id !== $this->user->id) {
            return null;
        }

        return $note;
    }

    private function verifyAttachmentOwnership(int $attachmentId): ?KanbanTaskAttachment
    {
        $attachment = KanbanTaskAttachment::with('task.column.board')->find($attachmentId);

        if (! $attachment || $attachment->task->column->board->user_id !== $this->user->id) {
            return null;
        }

        return $attachment;
    }
}
