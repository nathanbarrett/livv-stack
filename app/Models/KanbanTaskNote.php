<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KanbanTaskNoteAuthor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanbanTaskNote extends Model
{
    /** @use HasFactory<\Database\Factories\KanbanTaskNoteFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'author' => KanbanTaskNoteAuthor::class,
        ];
    }

    /**
     * @return BelongsTo<KanbanTask, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(KanbanTask::class, 'kanban_task_id');
    }
}
