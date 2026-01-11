<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KanbanTaskPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KanbanTask extends Model
{
    /** @use HasFactory<\Database\Factories\KanbanTaskFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'priority' => KanbanTaskPriority::class,
            'links' => 'array',
        ];
    }

    /**
     * @return BelongsTo<KanbanColumn, $this>
     */
    public function column(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'kanban_column_id');
    }

    /**
     * Tasks that this task depends on (blockers).
     *
     * @return BelongsToMany<KanbanTask, $this>
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            KanbanTask::class,
            'kanban_task_dependencies',
            'task_id',
            'depends_on_task_id'
        )->withTimestamps();
    }

    /**
     * Tasks that depend on this task.
     *
     * @return BelongsToMany<KanbanTask, $this>
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            KanbanTask::class,
            'kanban_task_dependencies',
            'depends_on_task_id',
            'task_id'
        )->withTimestamps();
    }

    public function getBoard(): KanbanBoard
    {
        return $this->column->board;
    }

    /**
     * @return HasMany<KanbanTaskNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(KanbanTaskNote::class);
    }

    /**
     * @return HasMany<KanbanTaskAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(KanbanTaskAttachment::class);
    }
}
