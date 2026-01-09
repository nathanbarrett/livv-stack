<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    /** @use HasFactory<\Database\Factories\KanbanColumnFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<KanbanBoard, $this>
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class, 'kanban_board_id');
    }

    /**
     * @return HasMany<KanbanTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(KanbanTask::class)->orderBy('position');
    }
}
